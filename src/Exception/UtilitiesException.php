<?php
declare(strict_types=1);

/**
 * Utilities Exception
 *
 * The base/generic exception for this plugin.
 * The other exceptions inherit this class.
 */

namespace Fr3nch13\Utilities\Exception;

use Cake\Core\Exception\CakeException;

/**
 * The base/generic exception for this plugin.
 * The other exceptions inherit this class.
 */
class UtilitiesException extends CakeException
{
    /**
     * Default exception code
     *
     * @var int
     */
    protected $_defaultCode = 500;
}
