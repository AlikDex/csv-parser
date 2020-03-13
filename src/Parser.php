<?php
namespace Csv;

use Psr\Http\Message\StreamInterface;

/**
 * A CSV Parser
 *
 * This parser reads a stream, validates and processes contained CSV data.
 *
 * @package alikdex/csv-parser
 */
final class Parser implements ParserInterface
{
    /** @var SplFileObject */
    private $csv;

    /** @var Validator|null */
    private $validator;

    /** @var RowHandlerInterface */
    private $rowHandler;

    /** @var string[] */
    private $header = [];

    /** @var bool */
    private $hasHeader = true;

    /** @var bool */
    private $skipFirstLine = false;

    /** @var bool */
    private $stopWhenError = true;

    /** @var Item|false */
    private $currentLine = false;

    /**
     * Wraps around a stream, and accepts options
     *
     * @param StreamInterface $stream
     * @param array $options
     */
    public function __construct(\SplFileObject $csv, array $options = [])
    {
        $this->parseOptions($options);
        $this->csv = $csv;
        $this->buffer = '';
    }

    /**
     * Assigns a validator
     *
     * Makes sure that the input is validated before further manipulating it.
     *
     * @param Validator $validator
     * @return ParserInterface
     */
    public function setValidator(Validator $validator): ParserInterface
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Assign a row handler
     *
     * Provides an easy to use feedback loop for CSV records.
     *
     * @param RowHandlerInterface $handler
     * @return ParserInterface
     */
    public function setRowHandler(RowHandlerInterface $handler): ParserInterface
    {
        $this->rowHandler = $handler;

        return $this;
    }

    public function setHeader(array $header = []): ParserInterface
    {
        $this->hasHeader = true;
        $this->header = $header;

        return $this;
    }

    /**
     * Rewinds the underlying stream
     *
     * @return ParserInterface
     */
    public function rewind(): ParserInterface
    {
        $this->csv->rewind();
        $this->currentLine = false;

        return $this;
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return Item|false
     * @since 5.0.0
     */
    public function current()
    {
        if (!$this->currentLine) {
            $this->currentLine = $this->readLine();
        }

        return $this->currentLine;
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->currentLine = $this->readLine();
        $this->csv->next();
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return int scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key(): int
    {
        return $this->csv->key();
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return !$this->eof() || ($this->currentLine instanceof Item);
    }

    /**
     * Check for a header
     *
     * Searches for a header, ignoring any blank lines.
     */
    private function parseHeader()
    {
        if ($this->hasHeader && empty($this->header)) {
            $this->header = $this->csv->fgetcsv();
        }
    }

    /**
     * Check for the end of the stream
     *
     * @return bool
     */
    public function eof(): bool
    {
        return $this->csv->eof();
    }

    /**
     * Runs the parser
     *
     * Runs the parser on the underlying stream. If a validator has been assigned,
     * the parser will validate each record, except for the header. The records
     * found in the stream are then passed to a row handler, if one has been assigned.
     */
    public function run(): void
    {
        if (!$this->csv->isReadable()) {
            throw new \RuntimeException('Resource document is not readable!');
        }

        $this->rewind();

        while (!$this->eof()) {
            $this->readLine();
        }

        if ($this->rowHandler instanceof RowHandlerInterface) {
            $this->rowHandler->eof();
        }
    }

    /**
     * Reads a line from the steam
     *
     * Reads a string from the stream, up to the defined delimiter. Empty lines
     * are skipped. Headers are read automatically, if tge option was set.
     *
     * @return Item|false
     */
    public function readLine()
    {
        $this->parseHeader();

        $rowData = $this->csv->fgetcsv();

        if ($this->skipFirstLine && $this->key() === 0) {
            return false;
        }

        if (empty($rowData)) {
            return false;
        }

        return $this->populateItem($rowData);
    }

    /**
     * Parses a line
     *
     * Transforms a string to a collection, using headers if available. The collection
     * is then passed to the underlying row handler for further manipulation.
     *
     * @param array|false $line
     * @return Item|false
     */
    private function populateItem(array $line)
    {
        $record = $this->createItem($line);

        if (!$record) {
            return false;
        }

        if ($this->validator instanceof Validator) {
            if ($this->validator->isValid($record)) {
                $this->success($record);
            } else {
                $record->setIsValid(false);
                $this->failure($record);
            }
        } else {
            $this->success($record);
        }

        return $record;
    }

    /**
     * Builds a collection from a line
     *
     * @param array|false $line
     * @return Item|false
     */
    private function createItem($line)
    {
        if (!\is_array($line)) {
            return false;
        }

        if ($this->hasHeader) {
            if (\count($this->header) !== \count($line)) {
                if ($this->stopWhenError) {
                    throw new \RuntimeException('Invalid item count');
                }

                return $this->createItem($this->readLine());
            }

            $record = new Item(\array_combine($this->header, $line));
        } else {
            $record = new Item($line);
        }

        return $record;
    }

    /**
     * Passes a result to the underlying row handler
     *
     * Also passes failed results to the row handler, if no validator was assigned.
     *
     * @param Item $item
     */
    private function success(Item $item): void
    {
        if ($this->rowHandler instanceof RowHandlerInterface) {
            $this->rowHandler->success($item);
        }
    }

    /**
     * Passes a failed result to the underlying row handler
     *
     * @param Item $item
     */
    private function failure(Item $item): void
    {
        if ($this->stopWhenError) {
            throw new \RuntimeException('Invalid record found in stream!');
        }

        if ($this->rowHandler instanceof RowHandlerInterface) {
            $this->rowHandler->failure($item);
        }
    }

    /**
     * Parses options
     *
     * @param array $options
     */
    private function parseOptions(array $options)
    {
        /*$this->bufferSize = $this->arrayGet($options, 'integer', static::OPTION_BUFSIZE, 1024);
        $this->delimiter = $this->arrayGet($options, 'string', static::OPTION_DELIMITER, ',');
        $this->stringEnclosure = $this->arrayGet($options, 'string', static::OPTION_STRING_ENCLOSURE, '"');
        $this->escapeChar = $this->arrayGet($options, 'string', static::OPTION_ESCAPE_CHAR, '\\');
        $this->lineEnding = $this->arrayGet($options, 'string', static::OPTION_EOL, "\n");
        $this->hasHeader = $this->arrayGet($options, 'boolean', static::OPTION_HEADER, true);*/
        $this->hasHeader = (bool) ($options['hasHeader'] ?? true);
        $this->stopWhenError = (bool) ($options['stopWhenError'] ?? true);
        $this->skipFirstLine = (bool) ($options['skipFirstLine'] ?? false);
    }
}
