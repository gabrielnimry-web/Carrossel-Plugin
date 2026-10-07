<?php
// SPDX-License-Identifier: GPL-3.0-or-later
namespace local_coursecarousel\local;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../SpreadsheetReader.php');

/** Validate CSV definitions before any profile field is written. */
final class profile_field_csv {
    /** Same schema for bundled installation and administrator uploads. */
    private const HEADERS = [
        'category', 'shortname', 'name', 'datatype', 'required', 'signup', 'visible',
        'sortorder', 'options', 'maxlength', 'startyear', 'endyear',
    ];

    /** Parse a file shipped with the plugin. */
    public static function read_file(string $path): array {
        return self::definitions(\local_coursecarousel\SpreadsheetReader::readcsv($path));
    }

    /** Parse uploaded CSV content. */
    public static function read_content(string $content): array {
        return self::definitions(\local_coursecarousel\SpreadsheetReader::readcsvcontent($content));
    }

    /** Validate every record first, including duplicates within the upload. */
    private static function definitions(array $rows): array {
        $headers = $rows ? array_keys(reset($rows)) : [];
        if (count($headers) !== count(self::HEADERS) || array_diff(self::HEADERS, $headers)) {
            throw new \moodle_exception('invalidprofilecsv', 'local_coursecarousel');
        }
        $definitions = [];
        $seen = [];
        foreach ($rows as $index => $row) {
            $row = array_map(static function($value): string {
                return trim((string)$value);
            }, $row);
            if (!array_filter($row, static function($value): bool { return $value !== ''; })) {
                continue;
            }
            $number = $index + 1;
            $shortname = $row['shortname'];
            if (!preg_match('/^[a-z][a-z0-9_]{0,99}$/D', $shortname) || isset($seen[$shortname]) ||
                    $row['name'] === '' || \core_text::strlen($row['name']) > 255 ||
                    $row['category'] === '' || \core_text::strlen($row['category']) > 255 ||
                    !in_array($row['datatype'], ['text', 'menu', 'datetime'], true) ||
                    !in_array($row['required'], ['0', '1'], true) ||
                    !in_array($row['signup'], ['0', '1'], true) ||
                    !in_array($row['visible'], ['0', '1', '2', '3'], true) ||
                    !ctype_digit($row['sortorder']) || (int)$row['sortorder'] < 1 ||
                    (int)$row['sortorder'] > 999999) {
                self::invalid($number);
            }
            $seen[$shortname] = true;
            $definition = [
                'category' => $row['category'], 'shortname' => $shortname, 'name' => $row['name'],
                'datatype' => $row['datatype'], 'required' => (int)$row['required'],
                'signup' => (int)$row['signup'], 'visible' => (int)$row['visible'],
                'sortorder' => (int)$row['sortorder'],
            ];
            if ($row['datatype'] === 'text') {
                $maxlength = $row['maxlength'] === '' ? '255' : $row['maxlength'];
                if (!ctype_digit($maxlength) || (int)$maxlength < 1 || (int)$maxlength > 255) {
                    self::invalid($number);
                }
                $definition['param1'] = '30';
                $definition['param2'] = $maxlength;
            } else if ($row['datatype'] === 'menu') {
                $options = array_map('trim', explode('|', $row['options']));
                if (in_array('', $options, true) || count(array_unique($options)) !== count($options) ||
                        strpos($row['options'], "\n") !== false || strpos($row['options'], "\r") !== false) {
                    self::invalid($number);
                }
                $definition['param1'] = implode("\n", $options);
            } else {
                $start = $row['startyear'];
                $end = $row['endyear'] === 'current' ? date('Y') : $row['endyear'];
                if (!ctype_digit($start) || !ctype_digit($end) || (int)$start < 1900 ||
                        (int)$end > 2100 || (int)$end < (int)$start) {
                    self::invalid($number);
                }
                $definition['param1'] = $start;
                $definition['param2'] = $end;
                $definition['defaultdata'] = '0';
            }
            $definitions[] = $definition;
        }
        if (!$definitions) {
            throw new \moodle_exception('invalidprofilecsv', 'local_coursecarousel');
        }
        return $definitions;
    }

    /** Report a logical CSV record number, not a physical line within quoted text. */
    private static function invalid(int $number): void {
        throw new \moodle_exception('invalidprofilecsvrow', 'local_coursecarousel', '', $number);
    }
}
