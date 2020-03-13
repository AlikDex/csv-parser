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
    public function testParseRowWithoutHeader(): void
    {
        $file = new \SplFileObject(__DIR__ . '/csv/without_header.csv');
        $parser = new Parser($file, [
            'hasHeader' => false,
            'stopWhenError' => false,
        ]);

        $result = [];
        while (!$parser->eof()) {
            $result[] = $parser->readLine();
        }

        $this->assertEquals('test "string" two', $result[2]->get(2));
        $this->assertEquals('col4', $result[3]->get(1));
        $this->assertEquals('test, string', $result[4]->get(2));
    }

    public function testParseRowWithHeader(): void
    {
        $file = new \SplFileObject(__DIR__ . '/csv/with_header.csv');
        $parser = new Parser($file, [
            'hasHeader' => true,
            'stopWhenError' => false,
        ]);

        $result = [];
        while (!$parser->eof()) {
            $result[] = $parser->readLine();
        }

        $this->assertEquals('test "string" two', $result[2]->get('text'));
        $this->assertEquals('col4', $result[3]->get('col'));
        $this->assertEquals('test, string', $result[4]->get('text'));
    }
}
