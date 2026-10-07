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

namespace local_coursecarousel\local;

/**
 * Matches courses against their standard attributes and custom fields.
 */
final class course_search_service {
    /**
     * Check whether a course matches a search term.
     *
     * @param object $course A stdClass or core_course_list_element instance.
     * @param string $search
     * @return bool
     */
    public static function matches(object $course, string $search): bool {
        $search = self::normalise($search);
        if ($search === '') {
            return true;
        }

        $values = [
            $course->fullname ?? '',
            $course->shortname ?? '',
            strip_tags((string)($course->summary ?? '')),
        ];

        foreach (course_metadata_service::get_fields((int)$course->id) as $data) {
            $value = $data->get_value();
            if ($value !== null && $value !== '') {
                $values[] = (string)$value;
            }

            if ($data->get_field()->get('type') === 'select') {
                $values[] = course_metadata_service::get_selected_option($data);
            }
        }

        foreach ($values as $value) {
            if (strpos(self::normalise((string)$value), $search) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalise text for a case-insensitive UTF-8 comparison.
     *
     * @param string $value
     * @return string
     */
    private static function normalise(string $value): string {
        return \core_text::strtolower(trim($value));
    }
}
