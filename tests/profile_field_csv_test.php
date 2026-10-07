<?php
// SPDX-License-Identifier: GPL-3.0-or-later
namespace local_coursecarousel;

defined('MOODLE_INTERNAL') || die();

/** @covers \local_coursecarousel\local\profile_field_csv */
final class profile_field_csv_test extends \basic_testcase {
    private const HEADER = "category,shortname,name,datatype,required,signup,visible,sortorder,options,maxlength,startyear,endyear\n";

    /** The shipped template is the install source and preserves the eight existing definitions. */
    public function test_default_template(): void {
        $fields = \local_coursecarousel\local\profile_field_csv::read_file(__DIR__ . '/../install/cadastro.csv');
        $this->assertCount(8, $fields);
        $byname = array_column($fields, null, 'shortname');
        $this->assertSame('14', $byname['cpf']['param2']);
        $this->assertSame(0, $byname['nomesocial']['required']);
        $this->assertSame(date('Y'), $byname['datanascimento']['param2']);
        $this->assertSame("Feminino\nMasculino", $byname['sexo']['param1']);
        foreach ($fields as $field) {
            $this->assertSame(1, $field['visible']);
            $this->assertSame(1, $field['signup']);
        }
    }

    /** Quoted labels, UTF-8 BOM, CRLF and blank lines are accepted. */
    public function test_csv_formatting(): void {
        $csv = "\xEF\xBB\xBF" . str_replace("\n", "\r\n", self::HEADER) .
            "\r\nCustom,department,\"Área, setor\",text,0,1,1,1,,120,,\r\n";
        $fields = \local_coursecarousel\local\profile_field_csv::read_content($csv);
        $this->assertCount(1, $fields);
        $this->assertSame('Área, setor', $fields[0]['name']);
        $this->assertSame('120', $fields[0]['param2']);
    }

    /**
     * All invalid rows fail before the importer can write definitions.
     * @dataProvider invalid_csv_provider
     */
    public function test_rejects_invalid_csv(string $content): void {
        $this->expectException(\moodle_exception::class);
        \local_coursecarousel\local\profile_field_csv::read_content($content);
    }

    /** @return array Invalid schemas, values and duplicate identifiers. */
    public static function invalid_csv_provider(): array {
        $valid = "Custom,department,Department,text,0,1,1,1,,120,,\n";
        return [
            'empty' => [''],
            'header only' => [self::HEADER],
            'wrong headers' => ["shortname,name\ncpf,CPF\n"],
            'duplicate headers' => [str_replace('category,shortname', 'shortname,shortname', self::HEADER) . $valid],
            'unknown type' => [self::HEADER . str_replace(',text,', ',unknown,', $valid)],
            'invalid shortname' => [self::HEADER . str_replace(',department,', ',Bad name,', $valid)],
            'repeated shortname' => [self::HEADER . $valid . $valid],
            'invalid flag' => [self::HEADER . str_replace(',0,1,1,', ',2,1,1,', $valid)],
            'invalid visibility' => [self::HEADER . str_replace(',0,1,1,', ',0,1,9,', $valid)],
            'invalid length' => [self::HEADER . str_replace(',120,', ',999,', $valid)],
            'invalid order' => [self::HEADER . str_replace(',0,1,1,1,', ',0,1,1,0,', $valid)],
            'empty menu option' => [self::HEADER . "Custom,choice,Choice,menu,0,1,1,1,A||B,,,\n"],
            'duplicate menu option' => [self::HEADER . "Custom,choice,Choice,menu,0,1,1,1,A|A,,,\n"],
            'reversed years' => [self::HEADER . "Custom,birth,Birth,datetime,0,1,1,1,,,2020,1900\n"],
        ];
    }
}
