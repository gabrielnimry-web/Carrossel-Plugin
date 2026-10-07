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
namespace local_coursecarousel\output;

/**
 * Plugin renderer.
 *
 * The class is intentionally extensible so Moodle themes can override it.
 *
 * @package    local_coursecarousel
 * @copyright  2026 Universidade Federal de Minas Gerais
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {
    /**
     * Render a course card.
     *
     * @param course_card $card Course card to render.
     * @return string Rendered course card HTML.
     */
    public function render_course_card(course_card $card): string {
        return $this->render_from_template(
            'local_coursecarousel/course_card',
            $card->export_for_template($this)
        );
    }
    /**
     * Render a carousel.
     *
     * @param carousel $carousel Carousel to render.
     * @return string Rendered carousel HTML.
     */
    public function render_carousel(carousel $carousel): string {
        return $this->render_from_template(
            'local_coursecarousel/carousel',
            $carousel->export_for_template($this)
        );
    }
}
