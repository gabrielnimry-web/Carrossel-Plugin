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

namespace local_coursecarousel\local;

/**
 * Provides cached access to course custom fields during a request.
 */
final class course_metadata_service {
    /** @var array<int, array<string, \core_customfield\data>> */
    private static array $cache = [];

    /**
     * Load custom fields for several courses in one operation.
     *
     * @param int[] $courseids
     * @return void
     */
    public static function preload(array $courseids): void {
        $courseids = array_values(array_unique(array_map('intval', $courseids)));
        $missingids = array_values(array_filter($courseids, static function(int $courseid): bool {
            return $courseid > 0 && !array_key_exists($courseid, self::$cache);
        }));
        if (!$missingids) {
            return;
        }

        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        $instancesdata = $handler->get_instances_data($missingids, true);
        foreach ($missingids as $courseid) {
            self::$cache[$courseid] = self::index_by_shortname($instancesdata[$courseid] ?? []);
        }
    }

    /**
     * Return custom field data indexed by shortname.
     *
     * @param int $courseid
     * @return array
     */
    public static function get_fields(int $courseid): array {
        if (isset(self::$cache[$courseid])) {
            return self::$cache[$courseid];
        }

        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        self::$cache[$courseid] = self::index_by_shortname($handler->get_instance_data($courseid, true));
        return self::$cache[$courseid];
    }

    /**
     * Index field data by normalised shortname.
     *
     * @param array $instancesdata
     * @return array
     */
    private static function index_by_shortname(array $instancesdata): array {
        $fields = [];
        foreach ($instancesdata as $data) {
            $shortname = strtolower(trim((string)$data->get_field()->get('shortname')));
            if ($shortname !== '') {
                $fields[$shortname] = $data;
            }
        }
        return $fields;
    }

    /**
     * Return a raw custom field value.
     *
     * @param int $courseid
     * @param string $shortname
     * @return mixed|null
     */
    public static function get_value(int $courseid, string $shortname) {
        $fields = self::get_fields($courseid);
        $shortname = strtolower(trim($shortname));
        return array_key_exists($shortname, $fields)
            ? $fields[$shortname]->get_value()
            : null;
    }

    /**
     * Resolve the selected option of a select field.
     *
     * @param \core_customfield\data $data
     * @param string $fallback
     * @return string
     */
    public static function get_selected_option($data, string $fallback = ''): string {
        $index = (int)$data->get_value() - 1;
        $config = $data->get_field()->get('configdata');
        if (is_string($config)) {
            $config = json_decode($config, true);
        }
        if (!is_array($config)) {
            return $fallback;
        }

        $rawoptions = $config['options'] ?? ($config['selectoptions'] ?? '');
        $options = preg_split('/\r\n|\r|\n/', $rawoptions);
        $options = array_values(array_filter(array_map('trim', $options), 'strlen'));
        return $options[$index] ?? $fallback;
    }

    /**
     * Return the human-readable value of a custom field.
     *
     * @param \core_customfield\data $data Custom field data.
     * @return string Human-readable field value.
     */
    public static function get_display_value($data): string {
        $value = trim((string)$data->get_value());
        if ($data->get_field()->get('type') === 'select') {
            return trim(self::get_selected_option($data, $value));
        }
        return $value;
    }

    /**
     * Build catalogue filters from metadata values used by the courses.
     *
     * @param array $courses Courses used to build the filters.
     * @param array $selected Currently selected filter values.
     * @return array Catalogue filter definitions.
     */
    public static function get_catalogue_filters(array $courses, array $selected = []): array {
        $fields = [];
        foreach ($courses as $course) {
            foreach (self::get_fields((int)$course->id) as $shortname => $data) {
                if ($data->get_field()->get('type') === 'textarea') {
                    continue;
                }
                $value = self::get_display_value($data);
                if ($value === '') {
                    continue;
                }
                if (!isset($fields[$shortname])) {
                    $fields[$shortname] = [
                        'shortname' => $shortname,
                        'label' => format_string($data->get_field()->get('name')),
                        'sortorder' => (int)$data->get_field()->get('sortorder'),
                        'values' => [],
                    ];
                }
                $fields[$shortname]['values'][$value] = $value;
            }
        }
        uasort($fields, static function(array $left, array $right): int {
            return $left['sortorder'] <=> $right['sortorder'];
        });
        $filters = [];
        foreach ($fields as $shortname => $field) {
            natcasesort($field['values']);
            $selectedvalue = trim((string)($selected[$shortname] ?? ''));
            $options = [];
            foreach ($field['values'] as $value) {
                $options[] = [
                    'value' => $value,
                    'label' => $value,
                    'selected' => $value === $selectedvalue,
                ];
            }
            $filters[] = [
                'shortname' => $shortname,
                'label' => $field['label'],
                'options' => $options,
            ];
        }
        return $filters;
    }

    /**
     * Check whether a course matches every selected metadata filter.
     *
     * @param int $courseid Course identifier.
     * @param array $filters Selected metadata filters.
     * @return bool Whether the course matches all filters.
     */
    public static function matches_filters(int $courseid, array $filters): bool {
        $fields = self::get_fields($courseid);
        foreach ($filters as $shortname => $expected) {
            $expected = trim((string)$expected);
            if ($expected === '') {
                continue;
            }
            if (!isset($fields[$shortname]) || self::get_display_value($fields[$shortname]) !== $expected) {
                return false;
            }
        }
        return true;
    }
}
