<?php
/**
 * The Offdev Project
 *
 * Offdev/Csv - Reads, parses and validates CSV files using streams
 *
 * @author      Pascal Severin <pascal@offdev.net>
 * @copyright   Copyright (c) 2018, Pascal Severin
 * @license     Apache License 2.0
 */
declare(strict_types=1);

namespace Csv\Tests;

use Illuminate\Support\Collection;
use Csv\Item;
use Csv\Parser;
use Csv\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Class ParserTest
 * @package alikdex/csv-parser
 */
final class ParserTest extends TestCase
{
    public function testReadLineWorks(): void
    {
        $file = new \SplFileObject(__DIR__ . '/data/without_header.csv');
        $parser = new Parser($file, [
            'stopWhenError' => false,
        ]);

        $result = [];
        while (!$parser->eof()) {
            $result[] = $parser->readLine();
        }

        $this->assertEquals('𠜎𠜱𠝹𠱓𠱸𠲖𠳏𠳕𩶘', $result[3]->get('column1'));
    }
}
