<?php
declare(strict_types=1);

/**
 * InvalidCharException
 */

namespace Fr3nch13\Utilities\Exception;

/**
 * Invalid Character Exception
 *
 * Throw when a character is invalid.
 */
class MissingMethodException extends UtilitieException
{
    /**
     * Template string that has attributes sprintf()'ed into it.
     *
     * @var string
     */
    protected $_messageTemplate = 'Missing the `%s::%s()` method.%s';
}
