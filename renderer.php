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
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/lib_local.php');
require_once(__DIR__.'/videos.php');

/**
 * Render the plugin content for the front page.
 *
 * @return string Rendered plugin HTML.
 */
function local_coursecarousel_render() {

    ob_start();

    echo '<div id="coursecarousel-home" class="local-coursecarousel">';

    // Preserve the three legacy slots when the new setting has not been saved yet.
    $carouselcount = (int)get_config('local_coursecarousel', 'carouselcount');
    $carouselcount = $carouselcount > 0 ? min(10, $carouselcount) : 3;
    for ($i = 1; $i <= $carouselcount; $i++) {

        if (!get_config(
            'local_coursecarousel',
            'carousel'.$i.'enabled'
        )) {
            continue;
        }

        $categoryid = get_config(
            'local_coursecarousel',
            'carousel'.$i.'category'
        );

        if (empty($categoryid)) {
            continue;
        }

        $customtitle = get_config(
            'local_coursecarousel',
            'carousel'.$i.'title'
        );

        $limit = get_config(
            'local_coursecarousel',
            'carousel'.$i.'limit'
        );

        $selectedcourses = get_config(
            'local_coursecarousel',
            'carousel'.$i.'courses'
        );

        local_coursecarousel_render_course_carousel(
            $categoryid,
            'carousel'.$i,
            $customtitle,
            $limit,
            $selectedcourses
        );
    }

    // Exibe vídeos do YouTube e Eduplay somente quando habilitado.
    $videosenabled = get_config('local_coursecarousel', 'videosenabled');
    if ($videosenabled === false || !empty($videosenabled)) {
        $youtubeplaylist = get_config('local_coursecarousel', 'youtubeplaylist');
        $eduplayplaylist = get_config('local_coursecarousel', 'eduplayplaylist');
        $videostitle = get_config('local_coursecarousel', 'videostitle');

        if (empty($videostitle)) {
            $videostitle = get_string('videos', 'local_coursecarousel');
        }

        $sources = [];
        $youtubeenabled = get_config('local_coursecarousel', 'youtubeenabled');
        if ($youtubeenabled === false || !empty($youtubeenabled)) {
            $sources[] = ['type' => 'youtube', 'playlist' => $youtubeplaylist];
        }
        $eduplayenabled = get_config('local_coursecarousel', 'eduplayenabled');
        if ($eduplayenabled === false || !empty($eduplayenabled)) {
            $sources[] = ['type' => 'eduplay', 'playlist' => $eduplayplaylist];
        }
        if ($sources) {
            local_coursecarousel_render_video_carousel($sources, $videostitle, 'carouselvideos');
        }
    }

    echo '</div>';

    return ob_get_clean();
}
