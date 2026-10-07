<?php
// SPDX-License-Identifier: GPL-3.0-or-later
namespace local_coursecarousel\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;

/** Profile values are owned and processed by core_user, not a plugin table. */
final class provider implements \core_privacy\local\metadata\provider {
    /** Describe the Moodle subsystem receiving the profile values. */
    public static function get_metadata(collection $collection): collection {
        return $collection->add_subsystem_link('core_user', [], 'privacy:metadata:core_user');
    }
}
