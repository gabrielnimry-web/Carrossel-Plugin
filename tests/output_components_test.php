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
 * Contract tests for the plugin output components.
 *
 * @package    local_coursecarousel
 * @category   test
 * @covers     \local_coursecarousel\output\carousel
 * @covers     \local_coursecarousel\output\course_card
 * @covers     \local_coursecarousel\privacy\provider
 * @covers     \local_coursecarousel\local\video\endpoints
 * @copyright  2026 Universidade Federal de Minas Gerais
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class output_components_test extends \basic_testcase {

    /**
     * Course card data must reach Mustache without being changed.
     */
    public function test_course_card_preserves_template_context(): void {
        $context = [
            'url' => 'https://example.test/course',
            'imageurl' => 'https://example.test/image.png',
            'tagname' => 'Curso',
            'tagclass' => 'cursos',
            'fullname' => 'Curso de teste',
            'duration' => '10 horas',
        ];
        $output = $this->createMock(\renderer_base::class);
        $card = new \local_coursecarousel\output\course_card($context);

        $this->assertSame($context, $card->export_for_template($output));
    }

    /**
     * Carousel data must reach Mustache without being changed.
     */
    public function test_carousel_preserves_template_context(): void {
        $context = [
            'title' => 'Cursos',
            'carouselid' => 'carousel1',
            'showbutton' => true,
            'slides' => [],
        ];
        $output = $this->createMock(\renderer_base::class);
        $carousel = new \local_coursecarousel\output\carousel($context);

        $this->assertSame($context, $carousel->export_for_template($output));
    }

    /**
     * Registration metadata must describe storage in the core user subsystem.
     */
    public function test_privacy_provider_describes_profile_storage(): void {
        $collection = new \core_privacy\local\metadata\collection('local_coursecarousel');
        $result = \local_coursecarousel\privacy\provider::get_metadata($collection);
        $this->assertSame($collection, $result);
        $metadata = $result->get_collection();
        $this->assertCount(1, $metadata);
        $this->assertInstanceOf(\core_privacy\local\metadata\types\subsystem_link::class, reset($metadata));
    }

    /**
     * Provider endpoint helpers must encode identifiers and use HTTPS.
     */
    public function test_video_endpoint_helpers(): void {
        $this->assertSame(
            'https://www.youtube-nocookie.com/embed/abcdefghijk',
            \local_coursecarousel\local\video\endpoints::youtube_embed('abcdefghijk', true)
        );
        $this->assertSame(
            'https://eduplay.rnp.br/app/video/embed/id%20with%20space',
            \local_coursecarousel\local\video\endpoints::eduplay_embed('app', 'id with space')
        );
    }
}
