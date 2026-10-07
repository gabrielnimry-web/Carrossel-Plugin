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

if ($hassiteconfig) {

    $settings = new admin_settingpage(
        'local_coursecarousel',
        get_string('pluginname', 'local_coursecarousel')
    );

    if ($ADMIN->fulltree) {

        $settings->add(new admin_setting_heading(
            'local_coursecarousel/generalheading',
            get_string('generalsettings', 'local_coursecarousel'),
            get_string('generalsettings_desc', 'local_coursecarousel')
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/enablehomepage',
            get_string('enablehomepage', 'local_coursecarousel'),
            get_string('enablehomepage_desc', 'local_coursecarousel'),
            1
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/enabledashboardcarousel',
            get_string('enabledashboardcarousel', 'local_coursecarousel'),
            get_string('enabledashboardcarousel_desc', 'local_coursecarousel'),
            1
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/enablevlibras',
            get_string('enablevlibras', 'local_coursecarousel'),
            get_string('enablevlibras_desc', 'local_coursecarousel'),
            1
        ));

        $itemoptions = array_combine(range(1, 12), range(1, 12));
        foreach ([
            'itemspermobileslide' => 1,
            'itemspertabletslide' => 2,
            'itemsperdesktopslide' => 4,
        ] as $settingname => $defaultvalue) {
            $settings->add(new admin_setting_configselect(
                'local_coursecarousel/' . $settingname,
                get_string($settingname, 'local_coursecarousel'),
                get_string($settingname . '_desc', 'local_coursecarousel'),
                $defaultvalue,
                $itemoptions
            ));
        }

        $settings->add(new admin_setting_configtext(
            'local_coursecarousel/durationfield',
            get_string('durationfield', 'local_coursecarousel'),
            get_string('durationfield_desc', 'local_coursecarousel'),
            'cargahoraria',
            PARAM_ALPHANUMEXT
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/showbadges',
            get_string('showbadges', 'local_coursecarousel'),
            get_string('showbadges_desc', 'local_coursecarousel'),
            1
        ));

        $settings->add(new admin_setting_configtext(
            'local_coursecarousel/badgefield',
            get_string('badgefield', 'local_coursecarousel'),
            get_string('badgefield_desc', 'local_coursecarousel'),
            '',
            PARAM_ALPHANUMEXT
        ));

        $settings->add(new admin_setting_heading(
            'local_coursecarousel/registrationheading',
            get_string('registrationsettings', 'local_coursecarousel'),
            get_string('registrationsettings_desc', 'local_coursecarousel')
        ));
        $settings->add(new admin_setting_heading(
            'local_coursecarousel/profiletools',
            get_string('profiletools', 'local_coursecarousel'),
            html_writer::link(new moodle_url('/local/coursecarousel/importar_csv.php'),
                get_string('importprofilefields', 'local_coursecarousel')) . ' | ' .
            html_writer::link(new moodle_url('/user/profile/index.php'),
                get_string('manageprofilefields', 'local_coursecarousel')) . ' | ' .
            html_writer::link(new moodle_url('/local/coursecarousel/install/cadastro.csv'),
                get_string('downloadprofilecsv', 'local_coursecarousel'))
        ));
        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/customizelogin',
            get_string('customizelogin', 'local_coursecarousel'),
            get_string('customizelogin_desc', 'local_coursecarousel'),
            1
        ));
        $settings->add(new admin_setting_configtext(
            'local_coursecarousel/identityproviderurl',
            get_string('identityproviderurl', 'local_coursecarousel'),
            get_string('identityproviderurl_desc', 'local_coursecarousel'),
            '',
            PARAM_URL
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/requireloginforcatalog',
            get_string('requireloginforcatalog', 'local_coursecarousel'),
            get_string('requireloginforcatalog_desc', 'local_coursecarousel'),
            0
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/requireloginforvideos',
            get_string('requireloginforvideos', 'local_coursecarousel'),
            get_string('requireloginforvideos_desc', 'local_coursecarousel'),
            0
        ));

        $settings->add(new admin_setting_configselect(
            'local_coursecarousel/catalogpagesize',
            get_string('catalogpagesize', 'local_coursecarousel'),
            get_string('catalogpagesize_desc', 'local_coursecarousel'),
            24,
            [12 => 12, 24 => 24, 36 => 36, 48 => 48, 72 => 72, 100 => 100]
        ));

        $settings->add(
            new admin_setting_heading(
                'local_coursecarousel/importcsvheading',
                get_string('importcsv', 'local_coursecarousel'),
                html_writer::link(
                    new moodle_url('/local/coursecarousel/importar_csv.php'),
                    get_string('openimportcsv', 'local_coursecarousel')
                )
            )
        );

        // Montando automaticamente a lista de categorias.
        $categories = [];
        foreach (\core_course_category::make_categories_list() as $id => $name) {
            $categories[$id] = $name;
        }

        $topcategories = [];
        foreach (\core_course_category::top()->get_children() as $category) {
            if ($category->visible) {
                $topcategories[$category->id] = $category->get_formatted_name();
            }
        }

        $settings->add(new admin_setting_configmultiselect(
            'local_coursecarousel/menucategories',
            get_string('menucategories', 'local_coursecarousel'),
            get_string('menucategories_desc', 'local_coursecarousel'),
            [],
            $topcategories
        ));

        $settings->add(new admin_setting_configtext(
            'local_coursecarousel/menutitle',
            get_string('menutitle', 'local_coursecarousel'),
            get_string('menutitle_desc', 'local_coursecarousel'),
            get_string('categories', 'local_coursecarousel'),
            PARAM_TEXT
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/menuabout',
            get_string('menuabout', 'local_coursecarousel'),
            get_string('menuabout_desc', 'local_coursecarousel'),
            1
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/menufaq',
            get_string('menufaq', 'local_coursecarousel'),
            get_string('menufaq_desc', 'local_coursecarousel'),
            1
        ));

        // Course IDs remain stable when an administrator renames a course.
        $courses = [];
        foreach (get_courses('all', 'c.sortorder ASC', 'c.id,c.fullname,c.category') as $course) {
            if ((int)$course->id === SITEID) {
                continue;
            }
            $categoryname = $categories[$course->category] ?? ('#' . $course->category);
            $courses[$course->id] = $categoryname . ' / ' . format_string($course->fullname);
        }

        $settings->add(new admin_setting_configselect(
            'local_coursecarousel/carouselcount',
            get_string('carouselcount', 'local_coursecarousel'),
            get_string('carouselcount_desc', 'local_coursecarousel'),
            3,
            array_combine(range(1, 10), range(1, 10))
        ));

        // Keep the original keys and only build the number of slots currently selected.
        $configuredcarouselcount = get_config('local_coursecarousel', 'carouselcount');
        $configuredcarouselcount = $configuredcarouselcount === false ? 3 : (int)$configuredcarouselcount;
        $configuredcarouselcount = max(1, min(10, $configuredcarouselcount));
        for ($i = 1; $i <= $configuredcarouselcount; $i++) {
            $settings->add(new admin_setting_heading(
                'local_coursecarousel/carousel' . $i . 'heading',
                get_string('carouselheading', 'local_coursecarousel', $i),
                ''
            ));
            $settings->add(new admin_setting_configcheckbox(
                'local_coursecarousel/carousel' . $i . 'enabled',
                get_string('carouselenabled', 'local_coursecarousel', $i),
                '',
                $i <= 3 ? 1 : 0
            ));
            $settings->add(new admin_setting_configtext(
                'local_coursecarousel/carousel' . $i . 'title',
                get_string('carouseltitle', 'local_coursecarousel', $i),
                get_string('carouseltitle_desc', 'local_coursecarousel'),
                '',
                PARAM_TEXT
            ));
            $settings->add(new admin_setting_configselect(
                'local_coursecarousel/carousel' . $i . 'category',
                get_string('carouselcategory', 'local_coursecarousel', $i),
                get_string('carouselcategory_desc', 'local_coursecarousel'),
                '',
                $categories
            ));
            $settings->add(new admin_setting_configmultiselect(
                'local_coursecarousel/carousel' . $i . 'courses',
                get_string('carouselcourses', 'local_coursecarousel'),
                get_string('carouselcourses_desc', 'local_coursecarousel'),
                [],
                $courses
            ));
            $settings->add(new admin_setting_configselect(
                'local_coursecarousel/carousel' . $i . 'limit',
                get_string('carousellimit', 'local_coursecarousel'),
                get_string('carousellimit_desc', 'local_coursecarousel'),
                12,
                array_combine(range(1, 50), range(1, 50))
            ));
        }
        // YouTube settings.

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/videosenabled',
            get_string('videosenabled', 'local_coursecarousel'),
            get_string('videosenabled_desc', 'local_coursecarousel'),
            1
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/youtubeenabled',
            get_string('youtubeenabled', 'local_coursecarousel'),
            get_string('youtubeenabled_desc', 'local_coursecarousel'),
            1
        ));

        $settings->add(
            new admin_setting_configpasswordunmask(
                'local_coursecarousel/youtubeapikey',
                get_string('youtubeapikey', 'local_coursecarousel'),
                get_string('youtubeapikey_desc', 'local_coursecarousel'),
                ''
            )
        );
        // Eduplay settings.

        $settings->add(new admin_setting_configcheckbox(
            'local_coursecarousel/eduplayenabled',
            get_string('eduplayenabled', 'local_coursecarousel'),
            get_string('eduplayenabled_desc', 'local_coursecarousel'),
            1
        ));

        $settings->add(
            new admin_setting_configpasswordunmask(
                'local_coursecarousel/eduplayclientkey',
                get_string('eduplayclientkey', 'local_coursecarousel'),
                get_string('eduplayclientkey_desc', 'local_coursecarousel'),
                ''
            )
        );
        // YouTube playlist.

        $settings->add(
            new admin_setting_configtext(
                'local_coursecarousel/youtubeplaylist',
                get_string('youtubeplaylist', 'local_coursecarousel'),
                get_string('youtubeplaylist_desc', 'local_coursecarousel'),
                '',
                PARAM_TEXT
            )
        );
        // Eduplay settings. playlist.

        $settings->add(
            new admin_setting_configtext(
                'local_coursecarousel/eduplayplaylist',
                get_string('eduplayplaylist', 'local_coursecarousel'),
                get_string('eduplayplaylist_desc', 'local_coursecarousel'),
                '',
                PARAM_TEXT
            )
        );
        // Quantidade de vídeos.

        $videoamounts = array_combine(range(1, 50), range(1, 50));
        $settings->add(new admin_setting_configselect(
            'local_coursecarousel/youtubemaxvideos',
            get_string('youtubemaxvideos', 'local_coursecarousel'),
            get_string('providermaxvideos_desc', 'local_coursecarousel'),
            20,
            $videoamounts
        ));
        $settings->add(new admin_setting_configselect(
            'local_coursecarousel/eduplaymaxvideos',
            get_string('eduplaymaxvideos', 'local_coursecarousel'),
            get_string('providermaxvideos_desc', 'local_coursecarousel'),
            20,
            $videoamounts
        ));

        $settings->add(new admin_setting_configselect(
            'local_coursecarousel/videotimeout',
            get_string('videotimeout', 'local_coursecarousel'),
            get_string('videotimeout_desc', 'local_coursecarousel'),
            10,
            [5 => 5, 10 => 10, 15 => 15, 20 => 20, 30 => 30]
        ));
        // Título da seção de vídeos.
        $settings->add(
            new admin_setting_configtext(
                'local_coursecarousel/videostitle',
                get_string('videostitle_setting', 'local_coursecarousel'),
                get_string('videostitle_setting_desc', 'local_coursecarousel'),
                get_string('videos', 'local_coursecarousel'),
                PARAM_TEXT
            )
        );

        // Páginas institucionais editáveis pelo administrador.
        $settings->add(new admin_setting_heading(
            'local_coursecarousel/contentpagesheading',
            get_string('contentpagesheading', 'local_coursecarousel'),
            get_string('contentpagesheading_desc', 'local_coursecarousel')
        ));
        $settings->add(new admin_setting_configtext(
            'local_coursecarousel/abouttitle', get_string('abouttitle_setting', 'local_coursecarousel'),
            get_string('abouttitle_setting_desc', 'local_coursecarousel'), get_string('about', 'local_coursecarousel'), PARAM_TEXT
        ));
        $settings->add(new admin_setting_confightmleditor(
            'local_coursecarousel/aboutcontent', get_string('aboutcontent_setting', 'local_coursecarousel'),
            get_string('aboutcontent_setting_desc', 'local_coursecarousel'),
            get_string('aboutcontent_default', 'local_coursecarousel')
        ));
        $settings->add(new admin_setting_configtext(
            'local_coursecarousel/faqtitle', get_string('faqtitle_setting', 'local_coursecarousel'),
            get_string('faqtitle_setting_desc', 'local_coursecarousel'), get_string('faq', 'local_coursecarousel'), PARAM_TEXT
        ));
        $settings->add(new admin_setting_confightmleditor(
            'local_coursecarousel/faqcontent', get_string('faqcontent_setting', 'local_coursecarousel'),
            get_string('faqcontent_setting_desc', 'local_coursecarousel'), get_string('faqcontent_default', 'local_coursecarousel')
        ));

    }

    $ADMIN->add(
        'localplugins',
        $settings
    );

    $ADMIN->add(
        'localplugins',
        new admin_externalpage(
            'local_coursecarousel_importcsv',
            get_string('importcsv', 'local_coursecarousel'),
            new moodle_url('/local/coursecarousel/importar_csv.php'),
            'local/coursecarousel:manage'
        )
    );

    $ADMIN->add(
        'localplugins',
        new admin_externalpage(
            'local_coursecarousel_updatemenu',
            get_string('updatemenu', 'local_coursecarousel'),
            new moodle_url('/local/coursecarousel/atualizar_menu.php'),
            'local/coursecarousel:manage'
        )
    );
}
