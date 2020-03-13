<?php
namespace Csv;

/**
 * Interface RowHandlerInterface
 * @package alikdex/csv-parser
 */
interface RowHandlerInterface
{
    /**
     * @param Item $record
     */
    public function success(Item $item): void;

    /**
     * @param Item $record
     */
    public function failure(Item $item): void;

    /**
     * Called when the parser hit the end of the file.
     */
    public function eof(): void;
}
