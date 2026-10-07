<?php
// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Local Course Carousel plugin.
 *
 * @package    local_coursecarousel
 * @copyright  2026 Universidade Federal de Minas Gerais
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade steps for local_coursecarousel.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_coursecarousel_upgrade(int $oldversion): bool {
    // This presentation metadata release requires no data/schema migration.
    if ($oldversion >= 2026092802) {
        if ($oldversion < 2026100601) {
            upgrade_plugin_savepoint(true, 2026100601, 'local', 'coursecarousel');
        }
        return true;
    }
    // Reconcile bundled course metadata/categories on every real plugin upgrade.
    // Importers create missing items only; do not replay profile migration policies.
    \local_coursecarousel\installer::install();

    if ($oldversion < 2026081801) {
        upgrade_plugin_savepoint(true, 2026081801, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026081900) {
        // Historical release marker. Menu changes are now explicit admin actions.
        upgrade_plugin_savepoint(true, 2026081900, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026081901) {
        upgrade_plugin_savepoint(true, 2026081901, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026081902) {
        upgrade_plugin_savepoint(true, 2026081902, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082000) {
        upgrade_plugin_savepoint(true, 2026082000, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082001) {
        // Forces Moodle to invalidate compiled templates after adding the
        // course duration to the shared course card.
        upgrade_plugin_savepoint(true, 2026082001, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082002) {
        upgrade_plugin_savepoint(true, 2026082002, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082100) {
        // Registers the catalogue search by course attributes and custom fields.
        upgrade_plugin_savepoint(true, 2026082100, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082800) {
        // Registers the performance and privacy refactoring release.
        upgrade_plugin_savepoint(true, 2026082800, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082801) {
        // Invalidate compiled templates after switching videos to embedded players.
        upgrade_plugin_savepoint(true, 2026082801, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082802) {
        // Invalidate the cached iframe template after navigation restrictions.
        upgrade_plugin_savepoint(true, 2026082802, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082803) {
        // Register the dedicated internal video player page.
        upgrade_plugin_savepoint(true, 2026082803, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082804) {
        // Keep the card player interactive and link its details to the wide view.
        upgrade_plugin_savepoint(true, 2026082804, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082805) {
        // Point the video back action to the Moodle site home page.
        upgrade_plugin_savepoint(true, 2026082805, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026082806) {
        // Remove the duplicate heading from the internal video page.
        upgrade_plugin_savepoint(true, 2026082806, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026083100) {
        // Historical release marker. Never overwrite the site menu during upgrade.
        upgrade_plugin_savepoint(true, 2026083100, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026083101) {
        // Invalidate compiled templates after removing the duplicate page heading.
        upgrade_plugin_savepoint(true, 2026083101, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026090102) {
        // Register the maintainability and administrator configuration refactoring.
        upgrade_plugin_savepoint(true, 2026090102, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026090103) {
        // Register the video carousel visibility setting.
        upgrade_plugin_savepoint(true, 2026090103, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026090200) {
        $legacymaxvideos = get_config('local_coursecarousel', 'maxvideos');
        if ($legacymaxvideos !== false) {
            if (get_config('local_coursecarousel', 'youtubemaxvideos') === false) {
                set_config('youtubemaxvideos', min(50, max(1, (int)$legacymaxvideos)), 'local_coursecarousel');
            }
            if (get_config('local_coursecarousel', 'eduplaymaxvideos') === false) {
                set_config('eduplaymaxvideos', min(50, max(1, (int)$legacymaxvideos)), 'local_coursecarousel');
            }
        }
        upgrade_plugin_savepoint(true, 2026090200, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026090201) {
        // Register the catalogue pagination URL parameter fix and purge plugin caches on upgrade.
        upgrade_plugin_savepoint(true, 2026090201, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026091800) {
        // Optional integration; do not install or change another plugin's profile fields here.
        upgrade_plugin_savepoint(true, 2026091800, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026091801) {
        // Adopt profile fields and settings without requiring the former plugin's code.
        \local_coursecarousel\local\registration_setup::install();
        upgrade_plugin_savepoint(true, 2026091801, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026091802) {
        // Restore native card destinations without replaying profile-field migrations.
        unset_config('useufmgcadastro', 'local_coursecarousel');
        unset_config('courseaccessenabled', 'local_coursecarousel');
        upgrade_plugin_savepoint(true, 2026091802, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026091803) {
        // Add CSV import support without reapplying defaults to existing profile fields.
        upgrade_plugin_savepoint(true, 2026091803, 'local', 'coursecarousel');
    }

    if ($oldversion < 2026091804) {
        upgrade_plugin_savepoint(true, 2026091804, 'local', 'coursecarousel');
    }

    // Recreate missing cadastro.csv definitions on every upgrade, preserving
    // existing field settings and values instead of replaying legacy corrections.
    \local_coursecarousel\local\profile_fields::install();

    if ($oldversion < 2026091805) {
        upgrade_plugin_savepoint(true, 2026091805, 'local', 'coursecarousel');
    }

    return true;
}
