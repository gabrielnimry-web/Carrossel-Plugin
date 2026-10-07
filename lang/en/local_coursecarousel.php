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
$string['about'] = 'About';
$string['faq'] = 'Frequently asked questions';
$string['contentpagesheading'] = 'About and FAQ pages';
$string['contentpagesheading_desc'] = 'Edit the institutional page content without changing the plugin code.';
$string['abouttitle_setting'] = 'About page title';
$string['abouttitle_setting_desc'] = 'Title displayed at the top of the About page.';
$string['aboutcontent_setting'] = 'About page content';
$string['aboutcontent_setting_desc'] = 'Use the editor to enter and format the About page content.';
$string['aboutcontent_default'] = '<p>Learn more about this platform, its goals and available content.</p>';
$string['faqtitle_setting'] = 'FAQ page title';
$string['faqtitle_setting_desc'] = 'Title displayed at the top of the frequently asked questions page.';
$string['faqcontent_setting'] = 'FAQ page content';
$string['faqcontent_setting_desc'] = 'Enter questions and answers using headings, paragraphs and lists.';
$string['faqcontent_default'] =
    '<h2>How do I access courses?</h2><p>Choose a course in the catalogue and use the access ' .
    'button on its page.</p><h2>Do I need to sign in?</h2><p>Some content may require ' .
    'authentication or enrolment.</p>';
$string['importcsv'] = 'Import categories, metadata and registration';
$string['openimportcsv'] = 'Open the CSV import page';
$string['importcsvinfo'] =
    'Upload at least one file. The import creates categories and fields that do not exist yet; ' .
    'existing records are preserved.';
$string['categoriescsv'] = 'categorias.csv file';
$string['categoriescsv_help'] = 'CSV with sequential headers categoria1,categoria2,... for as many hierarchy levels as required.';
$string['metadatacsv'] = 'metadados.csv file';
$string['metadatacsv_help'] = 'CSV with the headers shortname,name,type,description,sortorder,options.';
$string['selectatleastonecsv'] = 'Select at least one CSV file.';
$string['invalidcategoriescsv'] = 'The categories file must contain sequential headers categoria1,categoria2,... without gaps.';
$string['invalidcsvrow'] = 'CSV row {$a} contains more columns than the header.';
$string['invalidmetadatacsv'] = 'The metadata file does not contain all required headers.';
$string['categoriesimported'] = 'Categories imported successfully.';
$string['metadataimported'] = 'Metadata imported successfully.';
$string['csvreaderror'] = 'The CSV file could not be read.';
$string['pluginname'] = 'Course Carousel';
$string['coursecarousel:manage'] = 'Manage Course Carousel configuration and imports';
$string['updatemenu'] = 'Regenerate the custom menu';
$string['menuupdated'] = 'The Moodle custom menu was updated successfully.';
$string['generatedcontent'] = 'Generated content';
$string['generalsettings'] = 'General behaviour';
$string['generalsettings_desc'] = 'Control where the plugin is displayed and how its carousels behave.';
$string['enablehomepage'] = 'Display on the site home page';
$string['enablehomepage_desc'] = 'Display the configured course and video carousels on the site home page.';
$string['enabledashboardcarousel'] = 'Display available courses on the dashboard';
$string['enabledashboardcarousel_desc'] = 'Load the available self-enrolment courses at the top of the user dashboard.';
$string['enablevlibras'] = 'Enable VLibras';
$string['enablevlibras_desc'] = 'Load the external VLibras widget on Moodle pages.';
$string['itemspermobileslide'] = 'Cards per slide on mobile';
$string['itemspermobileslide_desc'] = 'Number of cards grouped into each carousel slide on mobile devices. Themes with custom presentation may replace this grouping with continuous scrolling.';
$string['itemspertabletslide'] = 'Cards per slide on tablets';
$string['itemspertabletslide_desc'] = 'Number of cards grouped into each carousel slide on tablet devices. Themes with custom presentation may replace this grouping with continuous scrolling.';
$string['itemsperdesktopslide'] = 'Cards per slide on desktop';
$string['itemsperdesktopslide_desc'] = 'Number of cards grouped into each carousel slide on desktop devices. Themes with custom presentation may replace this grouping with continuous scrolling.';
$string['durationfield'] = 'Course duration field shortname';
$string['durationfield_desc'] = 'Shortname of the course custom field used as the duration displayed on cards.';
$string['showbadges'] = 'Display course badges';
$string['showbadges_desc'] = 'Display the category or metadata badge on course cards and course detail pages.';
$string['badgefield'] = 'Course badge field shortname';
$string['badgefield_desc'] =
    'Optional course custom field used as the badge label. Leave empty to use the course ' .
    'category hierarchy.';
$string['requireloginforcatalog'] = 'Require login for the catalogue';
$string['requireloginforcatalog_desc'] = 'Only authenticated users can open the plugin catalogue when enabled.';
$string['requireloginforvideos'] = 'Require login for video pages';
$string['requireloginforvideos_desc'] = 'Only authenticated users can open the plugin video player pages when enabled.';
$string['menucategories'] = 'Categories in the generated custom menu';
$string['menucategories_desc'] =
    'Top-level categories included when an administrator explicitly regenerates the Moodle ' .
    'custom menu. If none are selected, all visible top-level categories are included.';
$string['categories'] = 'Categories';
$string['menutitle'] = 'Generated menu title';
$string['menutitle_desc'] = 'Text used for the parent item in the generated Moodle custom menu.';
$string['menuabout'] = 'Include the About page in the menu';
$string['menuabout_desc'] = 'Add a link to the plugin About page when regenerating the custom menu.';
$string['menufaq'] = 'Include the FAQ page in the menu';
$string['menufaq_desc'] = 'Add a link to the plugin FAQ page when regenerating the custom menu.';
$string['updatemenuwarning'] =
    'This action replaces the complete Moodle custom menu with the content generated below. ' .
    'Existing custom menu items from other sources will be removed.';
$string['carouselcourses'] = 'Displayed courses';
$string['carouselcourses_desc'] =
    'Select courses from one or more categories. The list shows Category / Course. If no course ' .
    'is selected, all courses in the carousel category are considered.';
$string['carouselcount'] = 'Number of course carousels';
$string['carouselcount_desc'] =
    'Choose how many configurable course carousel slots are processed on the site home page. ' .
    'Save and reload this page to edit newly added slots.';
$string['carouselheading'] = 'Course carousel {$a}';
$string['carouselenabled'] = 'Enable course carousel {$a}';
$string['carouseltitle'] = 'Title of course carousel {$a}';
$string['carouseltitle_desc'] = 'Leave empty to use the selected category name.';
$string['carouselcategory'] = 'Category of course carousel {$a}';
$string['carouselcategory_desc'] = 'Courses from this category and its subcategories are considered.';
$string['carousellimit'] = 'Maximum number of courses';
$string['carousellimit_desc'] = 'Maximum number of courses rendered in this carousel.';
$string['search'] = 'Search';
$string['searchplaceholder'] = 'Search by course or metadata';
$string['filterbymetadata'] = 'Filter by metadata';
$string['anyvalue'] = 'All';
$string['applyfilters'] = 'Apply filters';
$string['clearfilters'] = 'Clear filters';
$string['showall'] = 'Show all';
$string['availablecontent'] = 'Explore all available content.';
$string['aboutcourse'] = 'About this course';
$string['availablecourses'] = 'Available courses';
$string['noresults'] = 'No content found';
$string['noresultshelp'] = 'Try another search term or browse a different category.';
$string['welcomeback'] = 'Welcome back, {$a}!';
$string['dashboardintro'] = 'Continue where you left off and explore new content.';
$string['video'] = 'Video';
$string['videos'] = 'Videos';
$string['videosenabled'] = 'Enable video carousel';
$string['videosenabled_desc'] = 'Display the video carousel and retrieve videos from the configured YouTube and Eduplay playlists.';
$string['youtubeenabled'] = 'Enable YouTube videos';
$string['youtubeenabled_desc'] = 'Retrieve videos from the configured YouTube playlist.';
$string['eduplayenabled'] = 'Enable Eduplay videos';
$string['eduplayenabled_desc'] = 'Retrieve videos from the configured Eduplay playlist.';
$string['youtubeapikey'] = 'YouTube API key';
$string['youtubeapikey_desc'] = 'Credential for the YouTube Data API v3. It may be forced securely in config.php.';
$string['eduplayclientkey'] = 'Eduplay client key';
$string['eduplayclientkey_desc'] = 'Client credential for the Eduplay API. It may be forced securely in config.php.';
$string['youtubeplaylist'] = 'YouTube playlist';
$string['youtubeplaylist_desc'] = 'Identifier of the YouTube playlist displayed by the plugin.';
$string['eduplayplaylist'] = 'Eduplay playlist';
$string['eduplayplaylist_desc'] = 'Identifier of the Eduplay playlist displayed by the plugin.';
$string['youtubemaxvideos'] = 'Maximum YouTube videos';
$string['eduplaymaxvideos'] = 'Maximum Eduplay videos';
$string['providermaxvideos_desc'] = 'Maximum number of videos retrieved from this provider.';
$string['videotimeout'] = 'Video API timeout';
$string['videotimeout_desc'] = 'Maximum number of seconds allowed for each external video API request.';
$string['videostitle_setting'] = 'Video section title';
$string['videostitle_setting_desc'] = 'Title displayed above the video carousel.';
$string['watchvideo'] = 'Watch video {$a}';
$string['openvideofullscreen'] = 'Open video {$a} in wide view';
$string['openfullscreen'] = 'Watch in wide view';
$string['course'] = 'Course';
$string['catalogtitle'] = 'Course catalogue';
$string['catalogpagesize'] = 'Courses per catalogue page';
$string['catalogpagesize_desc'] = 'Maximum number of course cards rendered on each catalogue page.';
$string['catalogloaderror'] = 'Courses cannot be loaded at this time.';
$string['catalogloaddebug'] = 'Error loading catalogue courses: {$a}';

$string['institutionallogin'] = 'Sign in with MinhaUFMG';
$string['customizelogin'] = 'Customise MinhaUFMG sign-in';
$string['customizelogin_desc'] = 'Expose institutional provider configuration to the theme. Visual customization requires support from the active theme and preserves other sign-in methods.';
$string['identityproviderurl'] = 'MinhaUFMG provider URL';
$string['identityproviderurl_desc'] = 'Enter the exact institutional link shown on the login page, including query parameters. When empty, only /auth/shibboleth/index.php on this Moodle site is matched. If no link or multiple links match, the original interface is preserved.';
$string['privacy:metadata:core_user'] = 'Registration profile values are stored and managed by the Moodle user profile subsystem.';

$string['registrationsettings'] = 'UFMG registration and access';
$string['registrationsettings_desc'] = 'Registration, profile fields and login customisation are included in this plugin. Manage the fields under Users > Accounts > User profile fields.';

$string['profiletools'] = 'Registration fields from CSV';
$string['importprofilefields'] = 'Import registration fields';
$string['manageprofilefields'] = 'Manage user profile fields';
$string['downloadprofilecsv'] = 'Download cadastro.csv template';
$string['profilecsv'] = 'cadastro.csv file';
$string['profilecsv_help'] = 'Comma-separated UTF-8 CSV with headers category,shortname,name,datatype,required,signup,visible,sortorder,options,maxlength,startyear,endyear. Types: text, menu and datetime. required/signup: 0 or 1. visible: 0 (hidden), 1 (private), 2 (public), 3 (identity permission). sortorder: positive integer. Separate menu options with |. Text: maxlength between 1 and 255. Dates: years between 1900 and 2100; endyear also accepts current. Existing fields are preserved. Registration imports require site configuration permission.';
$string['invalidprofilecsv'] = 'The registration CSV must contain all 12 template headers and at least one field.';
$string['invalidprofilecsvrow'] = 'Registration CSV record {$a} is invalid. Check unique names, type, options, lengths, years and flags against the template.';
$string['profilefieldsimported'] = 'Registration: {$a->created} fields created; {$a->skipped} existing fields preserved.';
$string['invalidcsvheaders'] = 'The CSV contains empty or duplicate headers.';

$string['enrolcourse'] = 'Enrol';
$string['accesscourse'] = 'Access course';
$string['moreinformation'] = 'More information';
$string['close'] = 'Close';
$string['nosummary'] = 'No additional information is available for this course yet.';