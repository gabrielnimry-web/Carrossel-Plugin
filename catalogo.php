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

require('../../config.php');
require_once($CFG->dirroot . '/local/coursecarousel/lib_local.php');

if (get_config('local_coursecarousel', 'requireloginforcatalog')) {
    require_login();
}

global $PAGE, $OUTPUT;

$PAGE->set_url(
    new moodle_url('/local/coursecarousel/catalogo.php')
);

$PAGE->set_context(
    context_system::instance()
);

$PAGE->set_pagelayout('standard');

$PAGE->set_title(get_string('catalogtitle', 'local_coursecarousel'));

$search = optional_param(
    'search',
    '',
    PARAM_TEXT
);

$categoryid = optional_param(
    'categoryid',
    0,
    PARAM_INT
);

$metadatafilters = optional_param_array('metadata', [], PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);

echo $OUTPUT->header();

try {

    $renderer = $PAGE->get_renderer('local_coursecarousel');

    $coursesdata = [];
    $matchedcourses = [];

    /*
     * Categoria inicial
     */
    if ($categoryid > 0) {

        $category = core_course_category::get(
            $categoryid
        );

    } else {

        $category = core_course_category::top();

    }

    /*
     * Busca recursivamente os cursos
     */
    $courses = local_coursecarousel_get_courses_recursive(
        $category
    );

    \local_coursecarousel\local\course_metadata_service::preload(array_keys($courses));

    // Only accept filter keys and values that exist in the current catalogue.
    $availablefilters = \local_coursecarousel\local\course_metadata_service::get_catalogue_filters(
        $courses,
        $metadatafilters
    );
    $validfilters = [];
    foreach ($availablefilters as $filter) {
        $shortname = $filter['shortname'];
        $requestedvalue = trim((string)($metadatafilters[$shortname] ?? ''));
        foreach ($filter['options'] as $option) {
            if ($requestedvalue !== '' && $option['value'] === $requestedvalue) {
                $validfilters[$shortname] = $requestedvalue;
                break;
            }
        }
    }

    uasort(
        $courses,
        function($a, $b) {

            return strcmp(
                $a->fullname,
                $b->fullname
            );

        }
    );

    foreach ($courses as $course) {

        if ($course->id == SITEID) {
            continue;
        }

        if (empty($course->visible)) {
            continue;
        }

        if (!\local_coursecarousel\local\course_search_service::matches($course, $search)) {
            continue;
        }

        if (!\local_coursecarousel\local\course_metadata_service::matches_filters(
            (int)$course->id,
            $validfilters
        )) {
            continue;
        }

        $matchedcourses[] = $course;
    }

    $totalcourses = count($matchedcourses);
    $pagesize = (int)get_config('local_coursecarousel', 'catalogpagesize');
    $pagesize = $pagesize > 0 ? min(100, $pagesize) : 24;
    $page = max(0, min($page, max(0, (int)ceil($totalcourses / $pagesize) - 1)));
    $pagedcourses = array_slice($matchedcourses, $page * $pagesize, $pagesize);

    foreach ($pagedcourses as $course) {
        $card = \local_coursecarousel\local\course_card_factory::create_renderable($course);
        $coursesdata[] = ['cardhtml' => $renderer->render_course_card($card)];
    }

    $pagingparams = [
        'categoryid' => $categoryid,
        'search' => $search,
    ];
    foreach ($validfilters as $shortname => $value) {
        $pagingparams['metadata[' . $shortname . ']'] = $value;
    }
    $pagingurl = new moodle_url('/local/coursecarousel/catalogo.php', $pagingparams);
    $pagingbar = new paging_bar($totalcourses, $page, $pagesize, $pagingurl);

    $data = [

        'search' =>
            s($search),

        'categoryid' =>
            $categoryid,

        'metadatafilters' => $availablefilters,

        'hasmetadatafilters' => !empty($availablefilters),

        'hasactivefilters' => $search !== '' || !empty($validfilters),

        'clearurl' => (new moodle_url(
            '/local/coursecarousel/catalogo.php',
            $categoryid > 0 ? ['categoryid' => $categoryid] : []
        ))->out(false),

        'categoryname' =>
            format_string(
                $category->name
            ),

        'courses' =>
            $coursesdata,

        'hascourses' =>
            !empty($coursesdata),

        'coursecount' => $totalcourses,

        'paginghtml' => $OUTPUT->render($pagingbar),

    ];

    echo $OUTPUT->render_from_template(
        'local_coursecarousel/catalog_page',
        $data
    );

} catch (Exception $e) {

    debugging(
        get_string('catalogloaddebug', 'local_coursecarousel', $e->getMessage()),
        DEBUG_DEVELOPER
    );

    echo $OUTPUT->notification(
        get_string('catalogloaderror', 'local_coursecarousel'),
        'notifyproblem'
    );
}

echo $OUTPUT->footer();
