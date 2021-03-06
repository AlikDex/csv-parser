<?php
namespace Csv;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Validator as LaravelValidator;

/**
 * Class Validator
 * @package alikdex/csv-parser
 */
final class Validator
{
    /** @var array */
    private $rules = [];

    /**
     * Validator constructor.
     * @param array $rules
     */
    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * @param Item $data
     * @return bool
     */
    public function isValid(Item $data): bool
    {
        return (new LaravelValidator(
            $this->getTranslator(),
            $data->all(),
            $this->rules
        ))->passes();
    }

    /**
     * @return Translator
     * @codeCoverageIgnore
     */
    private function getTranslator(): Translator
    {
        return new class implements Translator
        {
            public function get($key, array $replace = [], $locale = null)
            {
                return $key;
            }

            public function choice($key, $number, array $replace = [], $locale = null)
            {
                return $key;
            }

            public function getLocale()
            {
                return 'en_US.UTF-8';
            }

            public function setLocale($locale)
            {
            }
        };
    }
}
