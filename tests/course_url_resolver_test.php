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
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_coursecarousel;

/**
 * Tests for native course-card destinations.
 *
 * @package    local_coursecarousel
 * @category   test
 * @covers     \local_coursecarousel\local\course_url_resolver
 * @copyright  2026 Universidade Federal de Minas Gerais
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_url_resolver_test extends \advanced_testcase {
    /**
     * A user who is not enrolled must be sent to the enrolment page.
     */
    public function test_resolves_enrolment_page_for_available_course(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $url = \local_coursecarousel\local\course_url_resolver::resolve((int)$course->id);

        $this->assertSame('/enrol/index.php', $url->get_path());
        $this->assertSame((string)$course->id, $url->params()['id']);
    }

    /**
     * An enrolled user must be sent directly to the native course page.
     */
    public function test_resolves_course_page_for_enrolled_user(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $this->setUser($user);

        $url = \local_coursecarousel\local\course_url_resolver::resolve((int)$course->id);

        $this->assertSame('/course/view.php', $url->get_path());
        $this->assertSame((string)$course->id, $url->params()['id']);
    }

    /** Anonymous users and guests use the native enrolment page without a bridge. */
    public function test_anonymous_and_guest_destinations(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $this->setUser();
        $url = \local_coursecarousel\local\course_url_resolver::resolve((int)$course->id);
        $this->assertSame('/enrol/index.php', $url->get_path());
        $this->assertEquals($course->id, $url->params()['id']);

        $this->setGuestUser();
        $url = \local_coursecarousel\local\course_url_resolver::resolve((int)$course->id);
        $this->assertSame('/enrol/index.php', $url->get_path());
        $this->assertEquals($course->id, $url->params()['id']);
    }

    /** Obsolete settings must not route cards through a removed endpoint. */
    public function test_legacy_settings_do_not_change_native_destinations(): void {
        $this->resetAfterTest();
        set_config('courseaccessenabled', 1, 'local_coursecarousel');
        set_config('useufmgcadastro', 1, 'local_coursecarousel');
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertSame('/enrol/index.php',
            \local_coursecarousel\local\course_url_resolver::resolve((int)$course->id)->get_path());
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $this->assertSame('/course/view.php',
            \local_coursecarousel\local\course_url_resolver::resolve((int)$course->id)->get_path());
    }
}
