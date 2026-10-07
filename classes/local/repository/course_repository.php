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

namespace local_coursecarousel\local\repository;

/**
 * Centralises course queries used by the dashboard.
 *
 * @package    local_coursecarousel
 * @copyright  2026 Universidade Federal de Minas Gerais
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_repository {
    /**
     * Return visible courses that currently allow self enrolment.
     *
     * @param int $userid
     * @return array<int, \stdClass>
     */
    public function get_available_self_enrol_courses(int $userid): array {
        global $DB;

        $sql = "SELECT c.*
                  FROM {course} c
                 WHERE c.id <> :siteid
                   AND c.visible = 1
                   AND EXISTS (
                       SELECT 1
                         FROM {enrol} e
                        WHERE e.courseid = c.id
                          AND e.enrol = :enrolmethod
                          AND e.status = :enrolstatus
                   )
              ORDER BY c.fullname";
        $courses = $DB->get_records_sql($sql, [
            'siteid' => SITEID,
            'enrolmethod' => 'self',
            'enrolstatus' => ENROL_INSTANCE_ENABLED,
        ]);

        if (!$courses) {
            return [];
        }

        $enrolled = enrol_get_users_courses($userid, false, 'id');
        return array_diff_key($courses, $enrolled);
    }
}
