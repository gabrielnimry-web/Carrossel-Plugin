<?php
// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Course card data.
 *
 * @package local_coursecarousel
 * @copyright 2026 Universidade Federal de Minas Gerais
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursecarousel\local;

/** Builds the context consumed by course_card.mustache. */
final class course_card_factory {
    /**
     * Build card data without rendering HTML.
     *
     * @param object $course Course record or core_course_list_element.
     * @return array
     */
    public static function create(object $course): array {
        $context = \context_course::instance($course->id);
        $url = course_url_resolver::resolve((int)$course->id);
        $tag = local_coursecarousel_get_tag($course->id, isset($course->category) ? (int)$course->category : null);
        $showbadges = get_config('local_coursecarousel', 'showbadges');
        // Category listings may omit summary fields.
        $details = isset($course->summary, $course->summaryformat) ? $course : get_course($course->id);
        $summary = file_rewrite_pluginfile_urls(
            (string)$details->summary, 'pluginfile.php', $context->id, 'course', 'summary', null
        );
        $summary = trim($summary) === ''
            ? get_string('nosummary', 'local_coursecarousel')
            : format_text($summary, $details->summaryformat, ['context' => $context, 'noclean' => false]);

        return [
            'url' => $url->out(false),
            'actionlabel' => get_string(
                $url->get_path() === '/course/view.php' ? 'accesscourse' : 'enrolcourse',
                'local_coursecarousel'
            ),
            'summary' => $summary,
            'imageurl' => local_coursecarousel_get_image($course->id),
            'tagname' => $tag['name'],
            'tagclass' => $tag['class'],
            'hastag' => $showbadges === false || !empty($showbadges),
            'fullname' => format_string($course->fullname),
            'duration' => local_coursecarousel_get_duration($course->id),
        ];
    }

    /**
     * Build a renderable course card.
     *
     * @param object $course Course record.
     * @return \local_coursecarousel\output\course_card
     */
    public static function create_renderable(object $course): \local_coursecarousel\output\course_card {
        return new \local_coursecarousel\output\course_card(self::create($course));
    }
}