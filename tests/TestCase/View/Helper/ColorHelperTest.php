<?php
declare(strict_types=1);

namespace Fr3nch13\Utilities\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use Fr3nch13\Utilities\Lib\Hlls;
use Fr3nch13\Utilities\View\Helper\ColorHelper;

class ColorHelperTest extends TestCase
{
    /**
     * @var \Cake\View\View
     */
    public $View;

    /**
     * @var \Fr3nch13\Utilities\View\Helper\ColorHelper
     */
    public $Color;

    public function setUp(): void
    {
        parent::setUp();
        $this->View = new View();
        $this->Color = new ColorHelper($this->View);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->View);
        unset($this->Color);
        parent::tearDown();
    }

    public function testHexToRgb(): void
    {
        $result = $this->Color->hexToRgb('#FFFFFF');
        $this->assertEquals('16777215', $result);
        $result = $this->Color->hexToRgb('#000000');
        $this->assertEquals('0', $result);

        $result = $this->Color->hexToRgb('#FFF');
        $this->assertEquals('16777215', $result);
        $result = $this->Color->hexToRgb('#000');
        $this->assertEquals('0', $result);
    }

    public function testRgbToHlls(): void
    {
        $expected = new Hlls(0, 100, 255, 0);
        $result = $this->Color->hexToRgb('#FFFFFF');
        $result = $this->Color->rgbToHlls($result);
        $this->assertEquals($expected->getHue(), $result->getHue());
        $this->assertEquals($expected->getLightness(), $result->getLightness());
        $this->assertEquals($expected->getLuminosity(), $result->getLuminosity());
        $this->assertEquals($expected->getSaturation(), $result->getSaturation());

        $expected = new Hlls(0, 0, 0, 0);
        $result = $this->Color->hexToRgb('#000000');
        $result = $this->Color->rgbToHlls($result);
        $this->assertEquals($expected->getHue(), $result->getHue());
        $this->assertEquals($expected->getLightness(), $result->getLightness());
        $this->assertEquals($expected->getLuminosity(), $result->getLuminosity());
        $this->assertEquals($expected->getSaturation(), $result->getSaturation());

        $expected = new Hlls(0, 21, 128, 255);
        $result = $this->Color->hexToRgb('#FF0000');
        $result = $this->Color->rgbToHlls($result);
        $this->assertEquals($expected->getHue(), $result->getHue());
        $this->assertEquals($expected->getLightness(), $result->getLightness());
        $this->assertEquals($expected->getLuminosity(), $result->getLuminosity());
        $this->assertEquals($expected->getSaturation(), $result->getSaturation());

        $expected = new Hlls(85, 72, 128, 255);
        $result = $this->Color->hexToRgb('#00FF00');
        $result = $this->Color->rgbToHlls($result);
        $this->assertEquals($expected->getHue(), $result->getHue());
        $this->assertEquals($expected->getLightness(), $result->getLightness());
        $this->assertEquals($expected->getLuminosity(), $result->getLuminosity());
        $this->assertEquals($expected->getSaturation(), $result->getSaturation());

        $expected = new Hlls(170, 7, 128, 255);
        $result = $this->Color->hexToRgb('#0000FF');
        $result = $this->Color->rgbToHlls($result);
        $this->assertEquals($expected->getHue(), $result->getHue());
        $this->assertEquals($expected->getLightness(), $result->getLightness());
        $this->assertEquals($expected->getLuminosity(), $result->getLuminosity());
        $this->assertEquals($expected->getSaturation(), $result->getSaturation());
    }
}
