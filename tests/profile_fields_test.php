<?php
// SPDX-License-Identifier: GPL-3.0-or-later
namespace local_coursecarousel;

defined('MOODLE_INTERNAL') || die();

/**
 * @covers \local_coursecarousel\local\profile_fields
 */
final class profile_fields_test extends \advanced_testcase {
    /** Installation is repeatable and new fields are private. */
    public function test_installation_is_idempotent(): void {
        global $DB;
        $this->resetAfterTest();
        \local_coursecarousel\local\profile_fields::install();
        $before = $DB->get_records('user_info_field');
        \local_coursecarousel\local\profile_fields::install();
        $this->assertEquals($before, $DB->get_records('user_info_field'));
        $managed = json_decode(get_config('local_coursecarousel', 'managedfields'), true);
        $this->assertCount(8, $managed);
        foreach ($managed as $id) {
            $field = $DB->get_record('user_info_field', ['id' => $id], '*', MUST_EXIST);
            $this->assertEquals(1, $field->visible);
            $this->assertEquals(1, $field->signup);
        }
    }

    /** Legacy migration must preserve user values, country and unrelated date fields. */
    public function test_upgrade_preserves_values_and_unrelated_fields(): void {
        global $DB;
        $this->resetAfterTest();
        \local_coursecarousel\local\profile_fields::install();
        $birth = $DB->get_record('user_info_field', ['shortname' => 'datanascimento'], '*', MUST_EXIST);
        $DB->set_field('user_info_field', 'defaultdata', '', ['id' => $birth->id]);
        $DB->set_field('user_info_field', 'visible', 2, ['id' => $birth->id]);
        $unrelated = clone $birth;
        unset($unrelated->id);
        $unrelated->shortname = 'external_date';
        $unrelated->defaultdata = '';
        $unrelated->visible = 2;
        $externalid = $DB->insert_record('user_info_field', $unrelated);
        $country = clone $unrelated;
        $country->shortname = 'pais_custom';
        $countryid = $DB->insert_record('user_info_field', $country);
        $user = $this->getDataGenerator()->create_user();
        $dataid = $DB->insert_record('user_info_data', (object)[
            'userid' => $user->id, 'fieldid' => $countryid, 'data' => 'Brasil', 'dataformat' => 0,
        ]);
        unset_config('managedfields', 'local_coursecarousel');
        \local_coursecarousel\local\profile_fields::install(true);
        $this->assertSame('0', $DB->get_field('user_info_field', 'defaultdata', ['id' => $birth->id]));
        $this->assertEquals(1, $DB->get_field('user_info_field', 'visible', ['id' => $birth->id]));
        $this->assertSame('', $DB->get_field('user_info_field', 'defaultdata', ['id' => $externalid]));
        $this->assertEquals(2, $DB->get_field('user_info_field', 'visible', ['id' => $externalid]));
        $this->assertTrue($DB->record_exists('user_info_field', ['id' => $countryid]));
        $this->assertSame('Brasil', $DB->get_field('user_info_data', 'data', ['id' => $dataid]));
    }

    /** A colliding shortname outside the legacy category must not be claimed or changed. */
    public function test_upgrade_preserves_external_shortname_collision(): void {
        global $DB;
        $this->resetAfterTest();
        \local_coursecarousel\local\profile_fields::install();
        $categoryid = $DB->insert_record('user_info_category', (object)['name' => 'External', 'sortorder' => 99]);
        $field = $DB->get_record('user_info_field', ['shortname' => 'cpf'], '*', MUST_EXIST);
        $DB->set_field('user_info_field', 'categoryid', $categoryid, ['id' => $field->id]);
        $DB->set_field('user_info_field', 'visible', 2, ['id' => $field->id]);
        $before = $DB->get_record('user_info_field', ['id' => $field->id]);
        unset_config('managedfields', 'local_coursecarousel');
        \local_coursecarousel\local\profile_fields::install(true);
        $this->assertEquals($before, $DB->get_record('user_info_field', ['id' => $field->id]));
        $managed = json_decode(get_config('local_coursecarousel', 'managedfields'), true);
        $this->assertArrayNotHasKey('cpf', $managed);
    }

    /** Uploaded fields are created once; repeat imports never overwrite settings or user data. */
    public function test_csv_import_preserves_existing_fields_and_values(): void {
        global $DB;
        $this->resetAfterTest();
        $header = "category,shortname,name,datatype,required,signup,visible,sortorder,options,maxlength,startyear,endyear\n";
        $csv = $header . "Custom,department,Department,text,0,1,1,1,,120,,\n";
        $counts = \local_coursecarousel\local\profile_fields::import_csv_content($csv);
        $this->assertSame(['created' => 1, 'skipped' => 0], $counts);
        $field = $DB->get_record('user_info_field', ['shortname' => 'department'], '*', MUST_EXIST);
        $this->assertEquals(0, $field->required);
        $user = $this->getDataGenerator()->create_user();
        $data = (object)['userid' => $user->id, 'fieldid' => $field->id, 'data' => 'Research', 'dataformat' => 0];
        $data->id = $DB->insert_record('user_info_data', $data);
        $counts = \local_coursecarousel\local\profile_fields::import_csv_content(
            str_replace('Custom,department,Department,text,0,1,1', 'Other,department,Renamed,text,1,1,2', $csv)
        );
        $this->assertSame(['created' => 0, 'skipped' => 1], $counts);
        $this->assertEquals($field, $DB->get_record('user_info_field', ['id' => $field->id]));
        $this->assertEquals($data, $DB->get_record('user_info_data', ['id' => $data->id]));
        $this->assertFalse($DB->record_exists('user_info_category', ['name' => 'Other']));
    }

    /** A later invalid row must not leave an earlier valid row partially imported. */
    public function test_csv_validation_precedes_database_writes(): void {
        global $DB;
        $this->resetAfterTest();
        $csv = "category,shortname,name,datatype,required,signup,visible,sortorder,options,maxlength,startyear,endyear\n" .
            "Custom,csvnewfield,New field,text,0,1,1,1,,120,,\n" .
            "Custom,broken,Broken,unsupported,0,1,1,2,,,,\n";
        try {
            \local_coursecarousel\local\profile_fields::import_csv_content($csv);
            $this->fail('Invalid CSV must be rejected.');
        } catch (\moodle_exception $exception) {
            $this->assertFalse($DB->record_exists('user_info_field', ['shortname' => 'csvnewfield']));
            $this->assertFalse($DB->record_exists('user_info_category', ['name' => 'Custom']));
        }
    }

    /** Upgrade-mode recreation must retain edited settings and values of remaining fields. */
    public function test_recreates_missing_default_without_overwriting_existing_data(): void {
        global $DB;
        $this->resetAfterTest();
        \local_coursecarousel\local\profile_fields::install();
        $missing = $DB->get_record('user_info_field', ['shortname' => 'nomesocial'], '*', MUST_EXIST);
        $retained = $DB->get_record('user_info_field', ['shortname' => 'cpf'], '*', MUST_EXIST);
        $DB->set_field('user_info_field', 'name', 'Edited CPF label', ['id' => $retained->id]);
        $DB->set_field('user_info_field', 'required', 0, ['id' => $retained->id]);
        $DB->set_field('user_info_field', 'visible', 2, ['id' => $retained->id]);
        $before = $DB->get_record('user_info_field', ['id' => $retained->id], '*', MUST_EXIST);
        $user = $this->getDataGenerator()->create_user();
        $data = (object)['userid' => $user->id, 'fieldid' => $retained->id, 'data' => '01234567890', 'dataformat' => 0];
        $data->id = $DB->insert_record('user_info_data', $data);

        // Simulate a removed definition in this isolated database fixture.
        $DB->delete_records('user_info_data', ['fieldid' => $missing->id]);
        $DB->delete_records('user_info_field', ['id' => $missing->id]);
        \local_coursecarousel\local\profile_fields::install();

        $restored = $DB->get_record('user_info_field', ['shortname' => 'nomesocial'], '*', MUST_EXIST);
        $this->assertNotEquals($missing->id, $restored->id);
        $this->assertEquals(0, $restored->required);
        $this->assertEquals($before, $DB->get_record('user_info_field', ['id' => $retained->id]));
        $this->assertEquals($data, $DB->get_record('user_info_data', ['id' => $data->id]));
        $managed = json_decode(get_config('local_coursecarousel', 'managedfields'), true);
        $this->assertEquals($restored->id, $managed['nomesocial']);

        $snapshot = $DB->get_records('user_info_field');
        \local_coursecarousel\local\profile_fields::install();
        $this->assertEquals($snapshot, $DB->get_records('user_info_field'));
    }
}
