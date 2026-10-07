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
 * Builds the available-courses dashboard fragment.
 */
final class dashboard_content {
    /**
     * Render the fragment for one user.
     *
     * @param int $userid User identifier.
     * @param string $firstname User first name.
     * @return string Rendered dashboard fragment.
     */
    public static function render(int $userid, string $firstname): string {
        global $PAGE;

        require_once(__DIR__ . '/../../lib_local.php');

        $renderer = $PAGE->get_renderer('local_coursecarousel');
        $repository = new repository\course_repository();
        $courses = $repository->get_available_self_enrol_courses($userid);
        course_metadata_service::preload(array_keys($courses));

        $cards = [];
        foreach ($courses as $course) {
            $card = course_card_factory::create_renderable($course);
            $cards[] = ['cardhtml' => $renderer->render_course_card($card)];
        }

        $slides = [];
        foreach (array_chunk($cards, local_coursecarousel_get_items_per_slide()) as $index => $group) {
            $slides[] = ['active' => $index === 0, 'courses' => $group];
        }

        $html = $renderer->render_from_template('local_coursecarousel/dashboard_intro', [
            'firstname' => format_string($firstname),
        ]);
        if ($slides) {
            $html .= $renderer->render_carousel(new \local_coursecarousel\output\carousel([
                'title' => get_string('availablecourses', 'local_coursecarousel'),
                'carouselid' => 'cursosdisponiveis',
                'itemsclass' => 'items-' . local_coursecarousel_get_items_per_slide(),
                'slides' => $slides,
                'showbutton' => false,
            ]));
        }
        // Keep dashboard cards inside the same design-token scope used on the homepage.
        return \html_writer::div($html, 'local-coursecarousel local-coursecarousel-dashboard');
    }
}
