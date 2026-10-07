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

namespace local_coursecarousel;

/**
 * Reads CSV spreadsheets.
 */
class SpreadsheetReader {

    /**
     * Read a CSV file.
     *
     * @param string $arquivo CSV file path.
     * @return array Parsed rows.
     */
    public static function readcsv(string $arquivo): array {

        if (!file_exists($arquivo)) {
            throw new \moodle_exception("Arquivo não encontrado: {$arquivo}");
        }

        $fp = fopen($arquivo, 'r');

        if ($fp === false) {
            throw new \moodle_exception('csvreaderror', 'local_coursecarousel');
        }

        return self::readstream($fp);
    }

    /**
     * Reads CSV data received through a Moodle file picker.
     *
     * @param string $content CSV file contents.
     * @return array Parsed CSV rows.
     */
    public static function readcsvcontent(string $content): array {
        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $content);
        rewind($fp);

        return self::readstream($fp);
    }

    /**
     * Converts an open CSV stream to associative rows.
     *
     * @param resource $fp
     */
    private static function readstream($fp): array {
        $dados = [];

        $cabecalho = fgetcsv($fp);

        if ($cabecalho === false) {
            fclose($fp);
            return [];
        }

        $cabecalho[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$cabecalho[0]);
        $cabecalho = array_map(static function($header): string {
            return trim((string)$header);
        }, $cabecalho);
        if (in_array('', $cabecalho, true) || count(array_unique($cabecalho)) !== count($cabecalho)) {
            fclose($fp);
            throw new \moodle_exception('invalidcsvheaders', 'local_coursecarousel');
        }

        $linenumber = 1;
        while (($linha = fgetcsv($fp)) !== false) {
            $linenumber++;
            if (count($linha) === 1 && trim((string)$linha[0]) === '') {
                continue;
            }

            if (count($linha) > count($cabecalho)) {
                fclose($fp);
                throw new \moodle_exception('invalidcsvrow', 'local_coursecarousel', '', $linenumber);
            }

            while (count($linha) < count($cabecalho)) {
                $linha[] = '';
            }

            $dados[] = array_combine($cabecalho, $linha);
        }

        fclose($fp);

        return $dados;
    }
}
