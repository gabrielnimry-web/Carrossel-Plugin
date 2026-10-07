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
// This file is part of Moodle - http://moodle.org/.

namespace local_coursecarousel\local;

/**
 * Resolves the native Moodle destination for a course card.
 *
 * @package    local_coursecarousel
 * @copyright  2026 Universidade Federal de Minas Gerais
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_url_resolver {
    /**
     * Return the native course page for enrolled users and enrolment page otherwise.
     *
     * @param int $courseid
     * @return \moodle_url
     */
    public static function resolve(int $courseid): \moodle_url {
        $context = \context_course::instance($courseid);
        $path = is_enrolled($context) ? '/course/view.php' : '/enrol/index.php';

        return new \moodle_url($path, ['id' => $courseid]);
    }
}
