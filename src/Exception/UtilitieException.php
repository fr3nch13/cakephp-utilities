<?php
declare(strict_types=1);

/**
 * Exception
 */

namespace Fr3nch13\Utilities\Exception;

use Cake\Core\Exception\CakeException;

/**
 * Exception
 *
 * Throw when a config file is missing.
 */
class UtilitieException extends CakeException
{
    /**
     * Default exception code
     *
     * @var int
     */
    protected $_defaultCode = 500;
}
