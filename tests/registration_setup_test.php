<?php
// SPDX-License-Identifier: GPL-3.0-or-later
namespace local_coursecarousel;

defined('MOODLE_INTERNAL') || die();

/** @covers \local_coursecarousel\local\registration_setup */
final class registration_setup_test extends \advanced_testcase {
    /** Prepare a pre-consolidation configuration without changing core profile data. */
    private function prepare_legacy_upgrade(): void {
        $this->resetAfterTest();
        foreach (['registrationmigrated', 'customizelogin', 'identityproviderurl',
                'courseaccessenabled', 'useufmgcadastro', 'managedfields'] as $name) {
            unset_config($name, 'local_coursecarousel');
        }
        foreach (['version', 'customizelogin', 'identityproviderurl', 'managedfields'] as $name) {
            unset_config($name, 'local_ufmgcadastro');
        }
    }

    /** Settings and profile IDs/values survive even when old code has been removed. */
    public function test_migrates_settings_and_preserves_profile_data(): void {
        global $DB;
        $this->prepare_legacy_upgrade();
        \local_coursecarousel\local\profile_fields::install(true);
        $managed = get_config('local_coursecarousel', 'managedfields');
        set_config('managedfields', $managed, 'local_ufmgcadastro');
        unset_config('managedfields', 'local_coursecarousel');
        set_config('version', 2026091804, 'local_ufmgcadastro');
        set_config('customizelogin', 1, 'local_ufmgcadastro');
        $providerurl = 'https://moodle.example/auth/oauth2/login.php?id=42';
        set_config('identityproviderurl', $providerurl, 'local_ufmgcadastro');
        set_config('useufmgcadastro', 0, 'local_coursecarousel');

        $field = $DB->get_record('user_info_field', ['shortname' => 'cpf'], '*', MUST_EXIST);
        // An administrator may have moved a managed field to a different category.
        $categoryid = $DB->insert_record('user_info_category', (object)['name' => 'Moved', 'sortorder' => 99]);
        $DB->set_field('user_info_field', 'categoryid', $categoryid, ['id' => $field->id]);
        $user = $this->getDataGenerator()->create_user();
        $data = (object)['userid' => $user->id, 'fieldid' => $field->id, 'data' => '01234567890', 'dataformat' => 0];
        $data->id = $DB->insert_record('user_info_data', $data);
        $before = $DB->get_records('user_info_field');

        \local_coursecarousel\local\registration_setup::install();

        $this->assertSame('1', get_config('local_coursecarousel', 'customizelogin'));
        $this->assertSame($providerurl, get_config('local_coursecarousel', 'identityproviderurl'));
        $this->assertFalse(get_config('local_coursecarousel', 'courseaccessenabled'));
        $this->assertFalse(get_config('local_coursecarousel', 'useufmgcadastro'));
        $this->assertSame('0', get_config('local_ufmgcadastro', 'customizelogin'));
        $this->assertEquals($before, $DB->get_records('user_info_field'));
        $this->assertEquals($data, $DB->get_record('user_info_data', ['id' => $data->id]));
        $this->assertEquals(json_decode($managed, true),
            json_decode(get_config('local_coursecarousel', 'managedfields'), true));
    }

    /** Current settings win, and a repeated upgrade does not replay the legacy policy. */
    public function test_preserves_current_settings_and_is_repeatable(): void {
        global $DB;
        $this->prepare_legacy_upgrade();
        set_config('customizelogin', 1, 'local_ufmgcadastro');
        set_config('identityproviderurl', 'https://old.example/login', 'local_ufmgcadastro');
        set_config('useufmgcadastro', 1, 'local_coursecarousel');
        set_config('customizelogin', 0, 'local_coursecarousel');
        set_config('identityproviderurl', 'https://new.example/login', 'local_coursecarousel');
        set_config('courseaccessenabled', 0, 'local_coursecarousel');
        \local_coursecarousel\local\registration_setup::install();

        $this->assertSame('0', get_config('local_coursecarousel', 'customizelogin'));
        $this->assertSame('https://new.example/login', get_config('local_coursecarousel', 'identityproviderurl'));
        $this->assertFalse(get_config('local_coursecarousel', 'courseaccessenabled'));
        $field = $DB->get_record('user_info_field', ['shortname' => 'nomesocial'], '*', MUST_EXIST);
        $DB->set_field('user_info_field', 'required', 1, ['id' => $field->id]);
        $before = $DB->get_records('user_info_field');
        unset_config('identityproviderurl', 'local_coursecarousel');
        set_config('courseaccessenabled', 1, 'local_coursecarousel');

        \local_coursecarousel\local\registration_setup::install();
        $this->assertFalse(get_config('local_coursecarousel', 'courseaccessenabled'));

        $this->assertFalse(get_config('local_coursecarousel', 'identityproviderurl'));
        $this->assertEquals($before, $DB->get_records('user_info_field'));
    }

    /** Fresh installations do not depend on a legacy plugin or valid legacy JSON. */
    public function test_installation_without_legacy_configuration(): void {
        $this->prepare_legacy_upgrade();
        set_config('managedfields', '"invalid scalar"', 'local_ufmgcadastro');
        \local_coursecarousel\local\registration_setup::install();
        $this->assertSame('1', get_config('local_coursecarousel', 'registrationmigrated'));
        $this->assertCount(8, json_decode(get_config('local_coursecarousel', 'managedfields'), true));
        $this->assertFalse(get_config('local_ufmgcadastro', 'version'));
    }
}
