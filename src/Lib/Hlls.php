<?php
declare(strict_types=1);

/**
 * hue, lightness, luminosity, and saturation object for colors
 */

namespace Fr3nch13\Utilities\Lib;

/**
 * hue, lightness, luminosity, and saturation object for color
 */
class Hlls
{
    /**
     * @var int Hue
     */
    protected $hue = 0;

    /**
     * @var int Lightness
     */
    protected $lightness = 0;

    /**
     * @var int Luminosity
     */
    protected $luminosity = 0;

    /**
     * @var int Saturation
     */
    protected $saturation = 0;

    /**
     * The constructor.
     *
     * Setup the default settings, and overwrite them if the config key is defined in the paramater.
     *
     * @param int $h Hue
     * @param int $li Lightness
     * @param int $l Lumonisity
     * @param int $s Saturation
     */
    public function __construct(int $h, int $li, int $l, int $s)
    {
        $this->hue = $h;
        $this->lightness = $li;
        $this->luminosity = $l;
        $this->saturation = $s;
    }

    /**
     * Gets the Hue
     *
     * @return int Hue value
     */
    public function getHue(): int
    {
        return $this->hue;
    }

    /**
     * Gets the Lightness
     *
     * @return int Lightness value
     */
    public function getLightness(): int
    {
        return $this->lightness;
    }

    /**
     * Gets the Luminosity
     *
     * @return int Luminosity value
     */
    public function getLuminosity(): int
    {
        return $this->luminosity;
    }

    /**
     * Gets the Saturation
     *
     * @return int Saturation value
     */
    public function getSaturation(): int
    {
        return $this->saturation;
    }
}
