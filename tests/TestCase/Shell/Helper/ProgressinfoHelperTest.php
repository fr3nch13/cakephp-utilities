<?php
declare(strict_types=1);

/**
 * ProgressinfoHelperTest
 */

namespace Fr3nch13\Utilities\Test\TestCase\Shell\Helper;

use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\StubConsoleOutput;
use Cake\TestSuite\TestCase;
use Fr3nch13\Utilities\Exception\InvalidCharException;
use Fr3nch13\Utilities\Shell\Helper\ProgressinfoHelper;

/**
 * ProgressinfoHelper Test Class
 *
 * This class contains the main tests for the ProgressinfoHelper Class.
 */
class ProgressinfoHelperTest extends TestCase
{
    /**
     * @var \Fr3nch13\Utilities\Shell\Helper\ProgressinfoHelper
     */
    protected $helper;

    /**
     * @var \Cake\Console\TestSuite\StubConsoleOutput
     */
    protected $stub;

    /**
     * @var \Cake\Console\ConsoleIo
     */
    protected $io;

    /**
     * setUp method
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->stub = new StubConsoleOutput();
        $this->io = new ConsoleIo($this->stub);
        /** @var \Fr3nch13\Utilities\Shell\Helper\ProgressinfoHelper $helper */
        $helper = $this->io->helper('Fr3nch13/Utilities.Progressinfo');
        $this->helper = $helper;
    }

    /**
     * Test using the helper manually.
     */
    public function testInit(): void
    {
        $helper = $this->helper->init([
            'total' => 200,
            'width' => 50,
        ]);
        $this->assertSame($helper, $this->helper, 'Should be chainable');
    }

    /**
     * Test using the helper manually.
     */
    public function testInitChars(): void
    {
        $helper = $this->helper->init([
            'total' => 200,
            'width' => 50,
            'progress' => ':',
            'arrow' => '~',
            'pad' => '.',
        ]);
        $this->assertSame(':', $helper->getProgressChar());
        $this->assertSame('~', $helper->getArrowChar());
        $this->assertSame('.', $helper->getPadChar());
    }

    /**
     * Test setting characters.
     */
    public function testSetProgressChar1(): void
    {
        $this->expectException(InvalidCharException::class);
        $this->helper->setProgressChar('');
    }

    /**
     * Test setting characters.
     */
    public function testSetProgressChar2(): void
    {
        $this->expectException(InvalidCharException::class);
        $this->helper->setProgressChar('--');
    }

    /**
     * Test setting characters.
     */
    public function testSetArrowChar1(): void
    {
        $this->expectException(InvalidCharException::class);
        $this->helper->setArrowChar('');
    }

    /**
     * Test setting characters.
     */
    public function testSetArrowChar2(): void
    {
        $this->expectException(InvalidCharException::class);
        $this->helper->setArrowChar('--');
    }

    /**
     * Test setting characters.
     */
    public function testSetPadChar1(): void
    {
        $this->expectException(InvalidCharException::class);
        $this->helper->setPadChar('');
    }

    /**
     * Test setting characters.
     */
    public function testSetPadChar2(): void
    {
        $this->expectException(InvalidCharException::class);
        $this->helper->setPadChar('--');
    }

    /**
     * Test setting characters.
     */
    public function testSetOpenChar1(): void
    {
        $this->expectException(InvalidCharException::class);
        $this->helper->setOpenChar('');
    }

    /**
     * Test setting characters.
     */
    public function testSetOpenChar2(): void
    {
        $this->expectException(InvalidCharException::class);
        $this->helper->setOpenChar('--');
    }

    /**
     * Test setting characters.
     */
    public function testSetCloseChar1(): void
    {
        $this->expectException(InvalidCharException::class);
        $this->helper->setCloseChar('');
    }

    /**
     * Test setting characters.
     */
    public function testSetCloseChar2(): void
    {
        $this->expectException(InvalidCharException::class);
        $this->helper->setCloseChar('--');
    }

    /**
     * Test that a callback is required.
     */
    public function testOutputFailure(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->helper->output(['not a callback']);
    }

    /**
     * Test that the callback is invoked until 100 is reached.
     */
    public function testOutputSuccess(): void
    {
        $this->helper->output([function (ProgressinfoHelper $progress): void {
            $progress->increment(20);
        }]);
        $expected = [
            '',
            '',
            '==============>                                                              20%',
            '',
            '=============================>                                               40%',
            '',
            '============================================>                                60%',
            '',
            '===========================================================>                 80%',
            '',
            '==========================================================================> 100%',
            '',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test output with options
     */
    public function testOutputSuccessOptions(): void
    {
        $this->helper->output([
            'total' => 10,
            'width' => 20,
            'callback' => function (ProgressinfoHelper $progress): void {
                $progress->increment(2);
            },
        ]);
        $expected = [
            '',
            '',
            '==>              20%',
            '',
            '=====>           40%',
            '',
            '========>        60%',
            '',
            '===========>     80%',
            '',
            '==============> 100%',
            '',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test output with options
     */
    public function testOutputSuccessOptionsShowCount(): void
    {
        $this->helper->output([
            'total' => 10,
            'width' => 20,
            'callback' => function (ProgressinfoHelper $progress): void {
                $progress->increment(2);
            },
            'showcount' => true,
        ]);
        $expected = [
            '',
            '',
            '==>              20% - 2/10',
            '',
            '=====>           40% - 4/10',
            '',
            '========>        60% - 6/10',
            '',
            '===========>     80% - 8/10',
            '',
            '==============> 100% - 10/10',
            '',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test output with options
     */
    public function testOutputSuccessOptionsShowCountFormat(): void
    {
        $this->helper->output([
            'total' => 10,
            'width' => 20,
            'callback' => function (ProgressinfoHelper $progress): void {
                $progress->increment(2);
            },
            'showcount' => true,
            'countformat' => '{0} of {1}',
        ]);
        $expected = [
            '',
            '',
            '==>              20% - 2 of 10',
            '',
            '=====>           40% - 4 of 10',
            '',
            '========>        60% - 6 of 10',
            '',
            '===========>     80% - 8 of 10',
            '',
            '==============> 100% - 10 of 10',
            '',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test output with options
     */
    public function testOutputSuccessCallbackInfo(): void
    {
        $number = 0;
        $this->helper->output([
            'callback' => function (ProgressinfoHelper $progress) use (&$number): void {
                $number++;
                $progress->info(__('Info: {0}', [
                    $number,
                ]));
                $progress->increment(2);
            },
            'total' => 10,
            'width' => 20,
        ]);
        $expected = [
            '',
            '',
            '==>              20% - Info: 1',
            '',
            '=====>           40% - Info: 2',
            '',
            '========>        60% - Info: 3',
            '',
            '===========>     80% - Info: 4',
            '',
            '==============> 100% - Info: 5',
            '',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    public function testAllOptionsCallback(): void
    {
        $number = 0;
        $this->helper->output([
            'callback' => function (ProgressinfoHelper $progress) use (&$number): void {
                $number++;
                $progress->info(__('Info: {0} - {1}', [
                    $number,
                    'File: TestFileName.txt',
                ]));
                $progress->increment(2);
            },
            'total' => 10,
            'width' => 50,
            'progress' => ':',
            'arrow' => '~',
            'pad' => '.',
            'open' => '[',
            'close' => ']',
            'location' => true,
            'showcount' => true,
            'countformat' => '({0} of {1})',
        ]);

        $expected = [
            '',
            '',
            '(2 of 10) - Info: 1 - File: TestFileName.txt - [::::::::~....................................]  20%',
            '',
            '(4 of 10) - Info: 2 - File: TestFileName.txt - [:::::::::::::::::~...........................]  40%',
            '',
            '(6 of 10) - Info: 3 - File: TestFileName.txt - [::::::::::::::::::::::::::~..................]  60%',
            '',
            '(8 of 10) - Info: 4 - File: TestFileName.txt - [:::::::::::::::::::::::::::::::::::~.........]  80%',
            '',
            '(10 of 10) - Info: 5 - File: TestFileName.txt - [::::::::::::::::::::::::::::::::::::::::::::~] 100%',
            '',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    public function testOptionComposerCallback(): void
    {
        $this->helper->output([
            'callback' => function (ProgressinfoHelper $progress): void {
                $progress->increment(2);
            },
            'total' => 10,
            'composer' => true,
        ]);

        $expected = [
            '',
            '',
            '2/10 - [===>-------------------]  20%',
            '',
            '4/10 - [========>--------------]  40%',
            '',
            '6/10 - [============>----------]  60%',
            '',
            '8/10 - [=================>-----]  80%',
            '',
            '10/10 - [======================>] 100%',
            '',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test using the helper manually.
     */
    public function testIncrementAndRender(): void
    {
        $this->helper->init();

        $this->helper->increment(20);
        $this->helper->draw();

        $this->helper->increment(40.0);
        $this->helper->draw();

        $this->helper->increment(40);
        $this->helper->draw();

        $expected = [
            '',
            '==============>                                                              20%',
            '',
            '============================================>                                60%',
            '',
            '==========================================================================> 100%',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test using the helper manually.
     */
    public function testIncrementAndRenderWithInfo(): void
    {
        $this->helper->init();

        $this->helper->increment(20);
        $this->helper->draw(__('20 / 100'));

        $this->helper->increment(40.0);
        $this->helper->draw(__('60 / 100'));

        $this->helper->increment(40);
        $this->helper->draw(__('100 / 100'));

        $expected = [
            '',
            '==============>                                                              20% - 20 / 100',
            '',
            '============================================>                                60% - 60 / 100',
            '',
            '==========================================================================> 100% - 100 / 100',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test using the helper chained.
     */
    public function testIncrementAndRenderChained(): void
    {
        $this->helper->init()
            ->increment(20)
            ->draw()
            ->increment(40)
            ->draw()
            ->increment(40)
            ->draw();

        $expected = [
            '',
            '==============>                                                              20%',
            '',
            '============================================>                                60%',
            '',
            '==========================================================================> 100%',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test negative numbers
     */
    public function testIncrementWithNegatives(): void
    {
        $this->helper->init();

        $this->helper->increment(40);
        $this->helper->draw();

        $this->helper->increment(-60);
        $this->helper->draw();

        $this->helper->increment(80);
        $this->helper->draw();

        $expected = [
            '',
            '=============================>                                               40%',
            '',
            '                                                                              0%',
            '',
            '===========================================================>                 80%',
        ];
        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test increment and draw with options
     */
    public function testIncrementWithOptions(): void
    {
        $this->helper->init([
            'total' => 10,
            'width' => 20,
        ]);
        $expected = [
            '',
            '=====>           40%',
            '',
            '===========>     80%',
            '',
            '==============> 100%',
        ];
        $this->helper->increment(4);
        $this->helper->draw();
        $this->helper->increment(4);
        $this->helper->draw();
        $this->helper->increment(4);
        $this->helper->draw();

        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test increment and draw with options
     */
    public function testIncrementWithComposer(): void
    {
        $this->helper->init([
            'total' => 10,
            'composer' => true,
        ]);
        $expected = [
            '',
            '4/10 - [========>--------------]  40%',
            '',
            '8/10 - [=================>-----]  80%',
            '',
            '10/10 - [======================>] 100%',
        ];
        $this->helper->increment(4);
        $this->helper->draw();
        $this->helper->increment(4);
        $this->helper->draw();
        $this->helper->increment(4);
        $this->helper->draw();

        $this->assertEquals($expected, $this->stub->messages());
    }

    /**
     * Test increment and draw with value that makes the pad
     * be a float
     */
    public function testIncrementFloatPad(): void
    {
        $this->helper->init([
            'total' => 50,
        ]);
        $expected = [
            '',
            '=========>                                                                   14%',
            '',
            '====================>                                                        28%',
            '',
            '==============================>                                              42%',
            '',
            '=========================================>                                   56%',
            '',
            '===================================================>                         70%',
            '',
            '========================================================>                    76%',
            '',
            '==============================================================>              84%',
            '',
            '==========================================================================> 100%',
        ];
        $this->helper->increment(7);
        $this->helper->draw();
        $this->helper->increment(7);
        $this->helper->draw();
        $this->helper->increment(7);
        $this->helper->draw();
        $this->helper->increment(7);
        $this->helper->draw();
        $this->helper->increment(7);
        $this->helper->draw();
        $this->helper->increment(3);
        $this->helper->draw();
        $this->helper->increment(4);
        $this->helper->draw();
        $this->helper->increment(8);
        $this->helper->draw();

        $this->assertEquals($expected, $this->stub->messages());
    }
}
