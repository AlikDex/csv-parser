<?php
namespace Csv;

/**
 * Interface ParserInterface
 * @package alikdex/csv-parser
 */
interface ParserInterface extends \Iterator
{
    /**
     * ParserInterface constructor.
     *
     * Wraps around a file, and accepts options
     *
     * @param StreamInterface $stream
     * @param array $options
     */
    public function __construct(\SplFileObject $csv, array $options = []);

    /**
     * Assigns a processor
     *
     * Assings a processor, which will be called whenever the
     * parser reads a line.
     *
     * @param RowHandlerInterface $processor
     * @return ParserInterface
     */
    public function setRowHandler(RowHandlerInterface $handler): ParserInterface;

    /**
     * Reads a line from the stream
     *
     * Reads a line from the stream, and puts it in a collection
     * for further manipulation.
     *
     * @return Item|false
     */
    public function readLine();

    /**
     * Rewinds the stream
     *
     * Moves the cursor to the beginning of the stream.
     *
     * @return ParserInterface
     */
    public function rewind(): ParserInterface;

    /**
     * Checks for the end of file
     *
     * Returns true when we reached the end of the stream.
     *
     * @return bool
     */
    public function eof(): bool;

    /**
     * Parses the resource document
     *
     * Parses the document, using the provided processor to further
     * manipulate CSV records found in the file or url.
     */
    public function run(): void;
}
