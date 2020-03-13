<?php
namespace Csv;

use Illuminate\Support\Collection;

/**
 * A record in a CSV file
 *
 * Extends Laravel collections to mark them as valid / invalid.
 * An item is valid by default, and will only be marked invalid
 * if a validator has determined so.
 *
 * @package alikdex/csv-parser
 */
final class Item extends Collection
{
    /** @var bool */
    private $isValid = true;

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @param bool $isValid
     * @return Item
     */
    public function setIsValid(bool $isValid): Item
    {
        $this->isValid = $isValid;

        return $this;
    }
}
