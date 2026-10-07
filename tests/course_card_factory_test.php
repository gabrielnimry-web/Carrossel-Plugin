<?php
// This file is part of Moodle - http://moodle.org/.

namespace local_coursecarousel;

/**
 * Tests course actions and the public course summary.
 *
 * @package local_coursecarousel
 * @category test
 * @covers \local_coursecarousel\local\course_card_factory
 * @copyright 2026 Universidade Federal de Minas Gerais
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_card_factory_test extends \advanced_testcase {
    /** The action label and destination change together after enrolment. */
    public function test_action_follows_enrolment(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/coursecarousel/lib_local.php');
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $data = \local_coursecarousel\local\course_card_factory::create($course);
        $this->assertSame(get_string('enrolcourse', 'local_coursecarousel'), $data['actionlabel']);
        $this->assertStringContainsString('/enrol/index.php', $data['url']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $data = \local_coursecarousel\local\course_card_factory::create($course);
        $this->assertSame(get_string('accesscourse', 'local_coursecarousel'), $data['actionlabel']);
        $this->assertStringContainsString('/course/view.php', $data['url']);
    }

    /** Summary HTML is cleaned and embedded file URLs are resolved. */
    public function test_summary_is_formatted_and_cleaned(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/coursecarousel/lib_local.php');
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course([
            'summary' => '<p>Course details</p><img src="@@PLUGINFILE@@/image.png">'
                . '<script>alert(1)</script>',
            'summaryformat' => FORMAT_HTML,
        ]);
        $data = \local_coursecarousel\local\course_card_factory::create($course);
        $this->assertStringContainsString('Course details', $data['summary']);
        $this->assertStringNotContainsString('<script', $data['summary']);
        $this->assertStringNotContainsString('@@PLUGINFILE@@', $data['summary']);
        $this->assertStringContainsString('pluginfile.php', $data['summary']);
    }

    /** Partial records can still produce a summary with an empty-state message. */
    public function test_partial_course_and_empty_summary(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/coursecarousel/lib_local.php');
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['summary' => '']);
        unset($course->summary, $course->summaryformat);
        $data = \local_coursecarousel\local\course_card_factory::create($course);
        $this->assertSame(get_string('nosummary', 'local_coursecarousel'), $data['summary']);
    }
}