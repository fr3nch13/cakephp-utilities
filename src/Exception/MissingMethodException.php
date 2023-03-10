<?php
declare(strict_types=1);

/**
 * Missing Method Exception
 */

namespace Fr3nch13\Utilities\Exception;

/**
 * Throws when a method is missing.
 * This is used in Traits when they're included in a Class
 * that doesn't have a method that the trit is expecting.
 */
class MissingMethodException extends UtilitiesException
{
    /**
     * Template string that has attributes sprintf()'ed into it.
     *
     * @var string
     */
    protected $_messageTemplate = 'Missing the `%s::%s()` method.%s';
}
