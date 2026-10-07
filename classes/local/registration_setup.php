<?php
// SPDX-License-Identifier: GPL-3.0-or-later
namespace local_coursecarousel\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Install the integrated registration feature and migrate the former local plugin.
 *
 * This is the only production class that reads the former plugin configuration.
 * No old plugin files or classes are loaded. Profile field IDs and user values are retained.
 */
final class registration_setup {
    /** Copy legacy settings once, then create/adopt profile fields. */
    public static function install(): void {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        if (!get_config('local_coursecarousel', 'registrationmigrated')) {
            foreach (['customizelogin', 'identityproviderurl'] as $name) {
                self::copy_if_unset($name, get_config('local_ufmgcadastro', $name));
            }

            $legacy = json_decode((string)get_config('local_ufmgcadastro', 'managedfields'), true);
            $current = json_decode((string)get_config('local_coursecarousel', 'managedfields'), true);
            $legacy = is_array($legacy) ? $legacy : [];
            $current = is_array($current) ? $current : [];
            set_config('managedfields', json_encode(array_merge($legacy, $current)), 'local_coursecarousel');

            // The 1.4 callback honours this setting while its old files are being removed.
            // Older releases must be removed during deployment (see README).
            if (get_config('local_ufmgcadastro', 'version') !== false) {
                set_config('customizelogin', 0, 'local_ufmgcadastro');
            }
            profile_fields::install(true);
            set_config('registrationmigrated', 1, 'local_coursecarousel');
        } else {
            // Do not re-apply legacy policy to an administrator's later field changes.
            profile_fields::install();
        }
        // Retire the superseded redirect settings, also for already migrated sites.
        unset_config('useufmgcadastro', 'local_coursecarousel');
        unset_config('courseaccessenabled', 'local_coursecarousel');
        $transaction->allow_commit();
    }

    /** Preserve explicit settings already made in the consolidated plugin. */
    private static function copy_if_unset(string $name, $value): void {
        if ($value !== false && get_config('local_coursecarousel', $name) === false) {
            set_config($name, $value, 'local_coursecarousel');
        }
    }
}
