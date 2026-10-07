<?php
// SPDX-License-Identifier: GPL-3.0-or-later
namespace local_coursecarousel\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Create profile definitions without taking ownership of unrelated fields.
 *
 * User values remain in Moodle's profile subsystem; never delete them here.
 */
final class profile_fields {
    /** Legacy category name is also the migration boundary. */
    private const CATEGORY = 'Dados Obrigatórios UFMG';

    /**
     * Install missing fields and apply narrowly scoped legacy corrections.
     *
     * @param bool $upgrade Whether to recognise fields in the legacy category.
     */
    public static function install(bool $upgrade = false): void {
        $definitions = profile_field_csv::read_file(__DIR__ . '/../../install/cadastro.csv');
        self::apply($definitions, $upgrade);
    }

    /** Uploads only create absent fields, never adopt or overwrite an existing field. */
    public static function import_csv_content(string $content): array {
        return self::apply(profile_field_csv::read_content($content), false);
    }

    /** Apply fully validated definitions in one transaction. */
    private static function apply(array $definitions, bool $upgrade): array {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        $transaction = $DB->start_delegated_transaction();
        $counts = ['created' => 0, 'skipped' => 0];
        $managed = json_decode((string)get_config('local_coursecarousel', 'managedfields'), true);
        $managed = is_array($managed) ? $managed : [];
        foreach ($definitions as $definition) {
            $categoryname = $definition['category'];
            unset($definition['category']);
            $shortname = $definition['shortname'];
            $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
            if (!$field) {
                $category = $DB->get_record('user_info_category', ['name' => $categoryname]);
                if (!$category) {
                    $category = (object)[
                        'name' => $categoryname,
                        'sortorder' => 1 + (int)$DB->get_field_sql('SELECT MAX(sortorder) FROM {user_info_category}'),
                    ];
                    $category->id = $DB->insert_record('user_info_category', $category);
                }
                $record = (object)array_merge([
                    'description' => '', 'descriptionformat' => FORMAT_HTML,
                    'categoryid' => $category->id,
                    'sortorder' => 1 + (int)$DB->get_field_sql(
                        'SELECT MAX(sortorder) FROM {user_info_field} WHERE categoryid = ?', [$category->id]
                    ),
                    'required' => 1, 'locked' => 0, 'visible' => PROFILE_VISIBLE_PRIVATE,
                    'forceunique' => 0, 'signup' => 1, 'defaultdata' => '', 'defaultdataformat' => FORMAT_HTML,
                    'param1' => '', 'param2' => '', 'param3' => '', 'param4' => '', 'param5' => '',
                ], $definition);
                $managed[$shortname] = (int)$DB->insert_record('user_info_field', $record);
                $counts['created']++;
                continue;
            }

            $counts['skipped']++;
            $owned = isset($managed[$shortname]) && (int)$managed[$shortname] === (int)$field->id;
            $legacytypes = [
                'cpf' => 'text', 'nomesocial' => 'text', 'datanascimento' => 'datetime', 'sexo' => 'menu',
                'racacor' => 'menu', 'estado' => 'menu', 'cidade_custom' => 'text', 'escolaridade' => 'menu',
            ];
            $legacy = $upgrade && ($legacytypes[$shortname] ?? '') === $field->datatype &&
                $DB->record_exists('user_info_category', ['id' => $field->categoryid, 'name' => self::CATEGORY]);
            if ((!$owned && !$legacy) || $field->datatype !== $definition['datatype']) {
                // Preserve fields created elsewhere, even when shortnames collide.
                continue;
            }
            $managed[$shortname] = (int)$field->id;
            if (!$upgrade) {
                continue;
            }
            $changes = (object)['id' => $field->id];
            if ((int)$field->visible === (int)PROFILE_VISIBLE_ALL) {
                $changes->visible = PROFILE_VISIBLE_PRIVATE;
            }
            if ($shortname === 'nomesocial') {
                $changes->required = 0;
            }
            if ($shortname === 'datanascimento') {
                if ($field->defaultdata === '') {
                    $changes->defaultdata = '0';
                }
                $changes->param2 = (string)max((int)$field->param2, (int)date('Y'));
            }
            // Repair only the exact old escaped value; preserve customised menu options.
            if ($shortname === 'sexo' && $field->param1 === 'Feminino\\nMasculino') {
                $changes->param1 = "Feminino\nMasculino";
            }
            if (count((array)$changes) > 1) {
                $DB->update_record('user_info_field', $changes);
            }
        }
        set_config('managedfields', json_encode($managed), 'local_coursecarousel');
        $transaction->allow_commit();
        return $counts;
    }
}
