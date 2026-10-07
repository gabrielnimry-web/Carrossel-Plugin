<?php
// SPDX-License-Identifier: GPL-3.0-or-later
namespace local_coursecarousel;

defined('MOODLE_INTERNAL') || die();

/**
 * @covers \local_coursecarousel\installer
 * @covers \local_coursecarousel\FieldCreator
 * @covers \local_coursecarousel\CategoryCreator
 */
final class installer_test extends \advanced_testcase {
    /** Missing fields return to the existing metadata category, even after it is renamed. */
    public function test_restores_missing_metadata_and_preserves_existing_fields(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        installer::install();
        $categoryid = (int)get_config('local_coursecarousel', 'metadatacategoryid');
        $DB->set_field('customfield_category', 'name', 'Renamed metadata', ['id' => $categoryid]);
        $retained = $DB->get_record('customfield_field',
            ['categoryid' => $categoryid, 'shortname' => 'ano'], '*', MUST_EXIST);
        $DB->set_field('customfield_field', 'name', 'Custom year label', ['id' => $retained->id]);
        $missing = $DB->get_record('customfield_field',
            ['categoryid' => $categoryid, 'shortname' => 'cargahoraria'], '*', MUST_EXIST);
        \core_customfield\api::delete_field_configuration(
            \core_customfield\field_controller::create((int)$missing->id)
        );
        $before = $DB->get_record('customfield_field', ['id' => $retained->id], '*', MUST_EXIST);
        $categorycount = $DB->count_records('customfield_category');
        installer::install();

        $restored = $DB->get_record('customfield_field',
            ['categoryid' => $categoryid, 'shortname' => 'cargahoraria'], '*', MUST_EXIST);
        $this->assertNotEquals($missing->id, $restored->id);
        $this->assertEquals($categoryid, get_config('local_coursecarousel', 'metadatacategoryid'));
        $this->assertEquals($categorycount, $DB->count_records('customfield_category'));
        $this->assertSame('Renamed metadata', $DB->get_field('customfield_category', 'name', ['id' => $categoryid]));
        $after = $DB->get_record('customfield_field', ['id' => $retained->id], '*', MUST_EXIST);
        $this->assertSame($before->name, $after->name);
        $this->assertSame($before->configdata, $after->configdata);

        $snapshot = $DB->get_records('customfield_field', ['categoryid' => $categoryid]);
        installer::install();
        $this->assertEquals($snapshot, $DB->get_records('customfield_field', ['categoryid' => $categoryid]));
    }

    /** Deleting the whole metadata category does not leave a stale configured ID. */
    public function test_restores_deleted_metadata_category(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        installer::install();
        $oldid = (int)get_config('local_coursecarousel', 'metadatacategoryid');
        \core_customfield\api::delete_category(\core_customfield\category_controller::create($oldid));
        installer::install();
        $newid = (int)get_config('local_coursecarousel', 'metadatacategoryid');
        $this->assertNotEquals($oldid, $newid);
        $this->assertTrue($DB->record_exists('customfield_category', ['id' => $newid, 'component' => 'core_course']));
        $expected = SpreadsheetReader::readcsv(__DIR__ . '/../install/metadados.csv');
        foreach ($expected as $row) {
            $this->assertTrue($DB->record_exists('customfield_field', [
                'categoryid' => $newid, 'shortname' => $row['shortname'],
            ]));
        }
    }

    /** A missing leaf is recreated below the existing parent; unrelated categories survive. */
    public function test_restores_course_category_hierarchy_without_duplicates(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        installer::install();
        $rows = SpreadsheetReader::readcsv(__DIR__ . '/../install/categorias.csv');
        $path = array_values(array_filter(array_map('trim', reset($rows))));
        $parentid = 0;
        $leaf = null;
        foreach ($path as $name) {
            $leaf = $DB->get_record('course_categories', ['name' => $name, 'parent' => $parentid], '*', MUST_EXIST);
            $parentid = (int)$leaf->id;
        }
        $this->assertFalse($DB->record_exists('course_categories', ['parent' => $leaf->id]));
        $category = \core_course_category::get($leaf->id);
        $this->assertTrue($category->can_delete_full());
        $category->delete_full(false);
        $unrelated = $this->getDataGenerator()->create_category(['name' => 'Independent category']);
        installer::install();

        $restored = $DB->get_record('course_categories',
            ['name' => $leaf->name, 'parent' => $leaf->parent], '*', MUST_EXIST);
        $this->assertNotEquals($leaf->id, $restored->id);
        $this->assertTrue($DB->record_exists('course_categories', ['id' => $unrelated->id]));
        $before = $DB->get_records('course_categories');
        installer::install();
        $this->assertEquals($before, $DB->get_records('course_categories'));
    }
}
