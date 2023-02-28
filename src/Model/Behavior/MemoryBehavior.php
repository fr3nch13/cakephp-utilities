<?php
declare(strict_types=1);

/**
 * MemoryBehavior
 */

namespace Fr3nch13\Utilities\Model\Behavior;

use Cake\ORM\Behavior;
use Fr3nch13\Utilities\Lib\Memory;

/**
 * Memory Behavior
 *
 * Tracks Memory and time usage, mainly for cron jobs.
 * This is just a wrapper around the Lib\Memory class.
 * This may not even be needed.
 */
class MemoryBehavior extends Behavior
{
    /**
     * The memory tracking object.
     *
     * @var null|\Fr3nch13\Utilities\Lib\Memory;
     */
    protected $Memory = null;

    /**
     * Reports the memory usage at the time it is called.
     *
     * @param bool $nice If we should return the bytes (false), of the calculated amount in a nice format (true).
     * @param float|null $mem_usage The memory number to be made nice.
     * @return string the memory usage stat.
     */
    public function memoryUsage(bool $nice = true, ?float $mem_usage = null): string
    {
        $this->ensureMemory();

        return $this->Memory->usage($nice, $mem_usage);
    }

    /**
     * Reports the highest memory usage.
     *
     * @param bool $nice If we should return the bytes (false), of the calculated amount in a nice format (true).
     * @return string the highest memory usage stat.
     */
    public function memoryUsageHighest($nice = true): string
    {
        $this->ensureMemory();

        return $this->Memory->usageHighest($nice);
    }

    /**
     * Makes sure there is a Memory object created.
     *
     * @return void
     */
    public function ensureMemory(): void
    {
        if (!$this->Memory) {
            $this->Memory = new Memory();
        }
    }
}
