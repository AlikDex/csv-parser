<?php
declare(strict_types=1);

namespace Csv\Tests;

use Csv\Parser;
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

    public function testParseRowWithHeaderButSkipIt(): void
    {
        $file = new \SplFileObject(__DIR__ . '/csv/with_header.csv');
        $parser = new Parser($file, [
            'hasHeader' => true,
            'stopWhenError' => false,
            'skipFirstLine' => true,
        ]);

        $result = [];
        while (!$parser->eof()) {
            $result[] = $parser->readLine();
        }

        $this->assertEquals('test "string" two', $result[2]->get(2));
        $this->assertEquals('col4', $result[3]->get(1));
        $this->assertEquals('test, string', $result[4]->get(2));
    }

    public function testReplaceHeader(): void
    {
        $file = new \SplFileObject(__DIR__ . '/csv/with_header.csv');
        $parser = new Parser($file, [
            'stopWhenError' => false,
        ]);
        $parser->setHeader(['h1', 'h2', 'h3']);

        $result = [];
        while (!$parser->eof()) {
            $result[] = $parser->readLine();
        }

        $this->assertEquals('test "string" two', $result[2]->get('h3'));
        $this->assertEquals('col4', $result[3]->get('h2'));
        $this->assertEquals('test, string', $result[4]->get('h3'));
    }

    public function testReplaceHeaderAndSkipFirst(): void
    {
        $file = new \SplFileObject(__DIR__ . '/csv/with_header.csv');
        $parser = new Parser($file, [
            'stopWhenError' => false,
            'skipFirstLine' => true,
        ]);
        $parser->setHeader(['h1', 'h2', 'h3']);

        $result = [];
        while (!$parser->eof()) {
            $result[] = $parser->readLine();
        }

        $this->assertEquals('test "string" two', $result[2]->get('h3'));
        $this->assertEquals('col4', $result[3]->get('h2'));
        $this->assertEquals('test, string', $result[4]->get('h3'));
    }

    public function testSetHeaderWithoutHeader(): void
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
}
