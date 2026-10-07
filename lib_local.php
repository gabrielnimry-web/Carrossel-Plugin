<?php
// This file is part of Moodle - http://moodle.org/
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
 * Local Course Carousel
 *
 * Quick Web Carousel Implementation with Course Carousel!
 *
 * @package local_coursecarousel
 * @copyright  2026 Universidade Federal de Minas Gerais - Diretoria de Educação à Distância e Educação Digital(DEDD)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Busca cursos recursivamente (categoria + subcategorias).
 *
 * @param core_course_category $category Categoria de cursos.
 * @return array Cursos encontrados, indexados pelo identificador.
 */
function local_coursecarousel_get_courses_recursive($category) {

    $courses = [];

    foreach ($category->get_courses() as $course) {
        $context = context_course::instance($course->id);
        if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', $context)) {
            continue;
        }
        $courses[$course->id] = $course;
    }

    foreach ($category->get_children() as $child) {
        $courses += local_coursecarousel_get_courses_recursive($child);
    }

    return $courses;
}

/**
 * Renderiza um carrossel de cursos.
 *
 * @param int $categoryid Identificador da categoria de cursos.
 * @param string $carouselid Identificador HTML do carrossel.
 * @param string|null $customtitle Título personalizado do carrossel.
 * @param int $limit Número máximo de cursos exibidos.
 * @param string $selectedcourses Lista de identificadores de cursos separados por vírgula.
 * @return void
 */
function local_coursecarousel_render_course_carousel(
    $categoryid,
    $carouselid,
    $customtitle = null,
    $limit = 0,
    $selectedcourses = ''
) {

    global $DB, $PAGE;

    $renderer = $PAGE->get_renderer('local_coursecarousel');

    $category = core_course_category::get($categoryid);

    $courses = array_values(
        local_coursecarousel_get_courses_recursive($category)
    );

    $selectedcourseids = array_values(array_filter(
        array_map('intval', explode(',', (string)$selectedcourses))
    ));
    if ($selectedcourseids) {
        $coursesbyid = $DB->get_records_list('course', 'id', $selectedcourseids);

        // A manual selection may combine courses from different categories.
        $courses = [];
        foreach ($selectedcourseids as $courseid) {
            if (!isset($coursesbyid[$courseid]) || $courseid === SITEID) {
                continue;
            }

            $course = $coursesbyid[$courseid];
            $context = context_course::instance($courseid);
            if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', $context)) {
                continue;
            }

            $courses[] = $course;
        }
    }

    if (!empty($limit)) {

        $courses = array_slice(
            $courses,
            0,
            $limit
        );
    }

    if (!empty($customtitle)) {

        $title = $customtitle;

    } else {

        $title = format_string(
            $category->get_formatted_name()
        );
    }

    if (empty($courses)) {
        return;
    }

    \local_coursecarousel\local\course_metadata_service::preload(array_map(static function($course): int {
        return (int)$course->id;
    }, $courses));

    // Mobile = 1 card | Desktop = 4 cards.
    $itemsperslide =
    local_coursecarousel_get_items_per_slide();

    $chunks = array_chunk(
        $courses,
        $itemsperslide
    );

    $categoryurl = new moodle_url(
        '/local/coursecarousel/catalogo.php',
        [
            'categoryid' => $categoryid,
        ]
    );

    $slides = [];

    foreach ($chunks as $i => $group) {

        $cards = [];

        foreach ($group as $course) {

            if ($course->id == SITEID) {
                continue;
            }

            $card = \local_coursecarousel\local\course_card_factory::create_renderable($course);

            $cards[] = [

                'cardhtml' => $renderer->render_course_card($card),
            ];
        }

        $slides[] = [
            'active' => ($i == 0),
            'courses' => $cards,
        ];

    }

    $data = [
        'title' => $title,
        'carouselid' => $carouselid,
        'itemsclass' => 'items-' . $itemsperslide,
        'categoryurl' => $categoryurl->out(false),
        'showbutton' => true,
        'slides' => $slides,
    ];

    echo $renderer->render_carousel(new \local_coursecarousel\output\carousel($data));
}

/**
 * Busca a imagem do curso.
 *
 * @param int $courseid Identificador do curso.
 * @return string URL da imagem do curso.
 */
function local_coursecarousel_get_image($courseid) {

    global $CFG;

    $context = context_course::instance($courseid);

    $fs = get_file_storage();

    $files = $fs->get_area_files(
        $context->id,
        'course',
        'overviewfiles',
        0,
        'filename',
        false
    );

    foreach ($files as $file) {

        if ($file->is_valid_image()) {

            return moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                null,
                $file->get_filepath(),
                $file->get_filename()
            )->out(false);
        }
    }

    return (new moodle_url(
        '/local/coursecarousel/pix/course-default.png'
    ))->out(false);

}

/**
 * Busca a tag do curso.
 *
 * @param int $courseid Identificador do curso.
 * @param int|null $categoryid Identificador da categoria do curso.
 * @return array Dados da tag do curso.
 */
function local_coursecarousel_get_tag($courseid, ?int $categoryid = null) {
    static $cache = [];

    $courseid = (int)$courseid;
    if (isset($cache[$courseid])) {
        return $cache[$courseid];
    }

    if ($categoryid === null) {
        $course = get_course($courseid);
        $categoryid = (int)$course->category;
    }
    $category = core_course_category::get($categoryid);
    $categoryids = array_values(array_filter(explode('/', trim($category->path, '/'))));

    // A tag e a segunda categoria da trilha; se ela nao existir, usa a raiz.
    $tagcategoryid = $categoryids[1] ?? $categoryids[0] ?? null;
    $rootcategoryid = $categoryids[0] ?? null;

    $tagname = get_string('course', 'local_coursecarousel');
    if ($tagcategoryid !== null) {
        $tagcategory = core_course_category::get((int)$tagcategoryid);
        $tagname = format_string($tagcategory->name);
    }

    $badgefield = strtolower(trim((string)get_config('local_coursecarousel', 'badgefield')));
    if ($badgefield !== '') {
        $fields = \local_coursecarousel\local\course_metadata_service::get_fields($courseid);
        if (isset($fields[$badgefield])) {
            $fieldvalue = \local_coursecarousel\local\course_metadata_service::get_display_value($fields[$badgefield]);
            if ($fieldvalue !== '') {
                $tagname = format_string($fieldvalue);
            }
        }
    }

    // Mantem a cor do badge associada a categoria raiz.
    $classname = $tagname;
    if ($rootcategoryid !== null) {
        $rootcategory = core_course_category::get((int)$rootcategoryid);
        $classname = format_string($rootcategory->name);
    }

    $tagclass = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $classname);
    $tagclass = strtolower($tagclass !== false ? $tagclass : $classname);
    $tagclass = trim((string)preg_replace('/[^a-z0-9]+/', '-', $tagclass), '-');

    // As classes de cor usam a primeira palavra da categoria principal:
    // cursos, formacoes, procedimentos, recursos ou ambientes.
    $tagclassparts = explode('-', $tagclass);
    $tagclass = $tagclassparts[0] ?: 'cursos';

    $cache[$courseid] = [
        'name' => $tagname,
        'class' => $tagclass,
    ];
    return $cache[$courseid];
}

/**
 * Busca a carga horária do curso.
 *
 * @param int $courseid Identificador do curso.
 * @return string Carga horária formatada.
 */
function local_coursecarousel_get_duration($courseid) {
    $fieldshortname = trim((string)get_config('local_coursecarousel', 'durationfield'));
    if ($fieldshortname === '') {
        $fieldshortname = 'cargahoraria';
    }

    $value = trim((string)\local_coursecarousel\local\course_metadata_service::get_value(
        (int)$courseid,
        $fieldshortname
    ));
    if ($value !== '') {
        return '⏱ ' . $value;
    }

    return '⏱ --';
}

/**
 * Define o número de itens exibidos por slide nos
 * carrosséis do plugin CourseCarousel.
 */
function local_coursecarousel_get_items_per_slide() {

    $devicetype =
        \core_useragent::get_device_type();

    switch ($devicetype) {

        case 'mobile':
            $settingname = 'itemspermobileslide';
            $default = 1;
            break;

        case 'tablet':
            $settingname = 'itemspertabletslide';
            $default = 2;
            break;

        default:
            $settingname = 'itemsperdesktopslide';
            $default = 4;
            break;
    }

    $configured = (int)get_config('local_coursecarousel', $settingname);
    return $configured > 0 ? min(12, $configured) : $default;
}

/**
 * Builds the Moodle custom menu from the visible top-level categories.
 *
 * @return string
 */
function local_coursecarousel_generate_custom_menu() {

    $rootcategory = core_course_category::top();

    $configuredids = array_values(array_filter(array_map(
        'intval',
        explode(',', (string)get_config('local_coursecarousel', 'menucategories'))
    )));
    if (!$configuredids) {
        foreach ($rootcategory->get_children() as $category) {
            if ($category->visible) {
                $configuredids[] = (int)$category->id;
            }
        }
    }

    $menutitle = trim((string)get_config('local_coursecarousel', 'menutitle'));
    if ($menutitle === '') {
        $menutitle = get_string('categories', 'local_coursecarousel');
    }
    $lines = [format_string($menutitle) . '|#'];

    foreach ($configuredids as $categoryid) {
        $category = \core_course_category::get($categoryid, IGNORE_MISSING);
        if (!$category || !$category->visible || (int)$category->parent !== 0) {
            continue;
        }
        $url = '/local/coursecarousel/catalogo.php?categoryid=' . $category->id;

        $lines[] = '-' . format_string($category->name) . '|' . $url;
    }

    $menuabout = get_config('local_coursecarousel', 'menuabout');
    if ($menuabout === false || !empty($menuabout)) {
        $lines[] = get_string('about', 'local_coursecarousel') . '|/local/coursecarousel/sobre.php';
    }
    $menufaq = get_config('local_coursecarousel', 'menufaq');
    if ($menufaq === false || !empty($menufaq)) {
        $lines[] = get_string('faq', 'local_coursecarousel') . '|/local/coursecarousel/faq.php';
    }
    return implode("\n", $lines);
}

/**
 * Update the Moodle custom menu with the generated plugin menu.
 *
 * @return string Generated custom menu.
 */
function local_coursecarousel_update_custom_menu() {

    $menu = local_coursecarousel_generate_custom_menu();

    set_config(
        'custommenuitems',
        $menu
    );

    return $menu;
}
