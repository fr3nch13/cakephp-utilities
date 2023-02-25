<?php
declare(strict_types=1);

namespace Fr3nch13\Utilities\Shell\Helper;

use Cake\Shell\Helper\ProgressHelper;
use Fr3nch13\Utilities\Exception\InvalidCharException;

class ProgressinfoHelper extends ProgressHelper
{
    /**
     * @var string The progress bar character, must be only 1 character.
     */
    protected $_progressChar = '=';

    /**
     * @var string The arrow character, must be only 1 character.
     */
    protected $_arrowChar = '>';

    /**
     * @var string The padding character, must be only 1 character.
     */
    protected $_padChar = ' ';

    /**
     * @var string The opening barrier, must be only 1 character.
     */
    protected $_openChar = '';

    /**
     * @var string The closing barrier, must be only 1 character.
     */
    protected $_closeChar = '';

    /**
     * @var bool The location of the info. True: Left, False: Right.
     */
    protected $_location = false;

    /**
     * @var string The information to show.
     */
    protected $_info = '';

    /**
     * @var bool If we should show the count
     */
    protected $_showcount = false;

    /**
     * @var string The count format that is compatibly with the __() function.
     */
    protected $_countformat = '{0}/{1}';

    /**
     * Initialize the progress bar for use.
     *
     * - `total` The total number of items in the progress bar. Defaults to 100.
     * - `width` The width of the progress bar. Defaults to 80.
     * - `progress` The progress character. Defaults to '='
     * - `arrow` The arrow character. Defaults to '>'.
     * - `pad` The padding character. Defaults to ' '.
     * - `open` The opening barrier character. Defaults to ''.
     * - `close` The closing barrier character. Defaults to ''.
     * - `location` Location of the info. True: Left, False: Right. Defaults tp False.
     * - `composer` If set and true, mimmicks the Composer progress bar.
     *
     * @param array<int|string, mixed> $args The initialization data.
     * @return $this
     */
    public function init(array $args = [])
    {
        // here is we get call directly.
        if (isset($args['composer'])) {
            $args += $this->makeComposer();
        }

        $args += ['total' => 100, 'width' => 80];

        $this->_progress = 0;
        $this->_width = $args['width'];
        $this->_total = $args['total'];
        if (isset($args['progress'])) {
            $this->setProgressChar($args['progress']);
        }
        if (isset($args['arrow'])) {
            $this->setArrowChar($args['arrow']);
        }
        if (isset($args['pad'])) {
            $this->setPadChar($args['pad']);
        }
        if (isset($args['open'])) {
            $this->setOpenChar($args['open']);
        }
        if (isset($args['close'])) {
            $this->setCloseChar($args['close']);
        }
        if (isset($args['location'])) {
            $this->setLocation($args['location']);
        }
        if (isset($args['showcount'])) {
            $this->setShowcount($args['showcount']);
        }
        if (isset($args['countformat'])) {
            $this->setCountformat($args['countformat']);
        }

        return $this;
    }

    /**
     * Makes it look like composer's progress bar.
     * Example: " 7/8 [========================>---]  87%"
     *
     * @return array<string, mixed> Settings to use in init to make a composer progress bar.
     */
    public function makeComposer(): array
    {
        $args = [
            'width' => 28,
            'open' => '[',
            'close' => ']',
            'progress' => '=',
            'arrow' => '>',
            'pad' => '-',
            'location' => true,
            'showcount' => true,
            'countformat' => '{0}/{1}',
        ];

        return $args;
    }

    /**
     * Output a progress bar.
     *
     * Takes a number of options to customize the behavior:
     *
     * - `total` The total number of items in the progress bar. Defaults to 100.
     * - `width` The width of the progress bar. Defaults to 80.
     * - `progress` The progress character. Defaults to '='
     * - `arrow` The arrow character. Defaults to '>'.
     * - `pad` The padding character. Defaults to ' '.
     * - `callback` The callback that will be called in a loop to advance the progress bar.
     * - `showcount` Boolean, whether or not to show the count.
     * - `countformat` The format to show the count, sould be in the format of the __() function.
     * - `composer` If set and true, mimmicks the Composer progress bar.
     *
     * @param array<int|string, mixed> $args The arguments/options to use when outputing the progress bar.
     * @return void
     */
    public function output(array $args): void
    {
        $args += ['callback' => null];
        if (isset($args[0])) {
            $args['callback'] = $args[0];
        }
        if (!$args['callback'] || !is_callable($args['callback'])) {
            throw new \RuntimeException('Callback option must be a callable.');
        }
        if (isset($args['composer'])) {
            unset($args['composer']);
            $args += $this->makeComposer();
        }
        $this->init($args);

        $callback = $args['callback'];

        $format = $this->getCountformat();
        if (isset($args['countformat'])) {
            $format = $args['countformat'];
        }

        $this->_io->out('', 0);
        while ($this->_progress < $this->_total) {
            $callback($this);

            $this->draw();
        }
        $this->_io->out('');
    }

    /**
     * Used to include extra info when drawing.
     *
     * @param null|string $info The string to include after the percent.
     * @return self An Instance of this class
     */
    public function draw(?string $info = null)
    {
        $bar = '';
        $showcount = '';

        $barPaths = [];
        if ($this->getShowcount()) {
            $barPaths[] = __($this->getCountformat(), [
                $this->_progress,
                $this->_total,
            ]);
        }
        if (!$info) {
            $info = $this->_info;
        }
        if ($info) {
            $barPaths[] = $info;
        }
        $barPaths = implode(' - ', $barPaths);

        if ($this->getLocation() && $barPaths) {
            $bar .= $barPaths . ' - ';
        }

        $bar .= $this->getOpenChar();

        $numberLen = strlen(' 100%');
        $complete = round($this->_progress / $this->_total, 2);
        $barLen = ($this->_width - $numberLen) * $this->_progress / $this->_total;
        if ($barLen > 1) {
            $bar .= str_repeat($this->getProgressChar(), (int)$barLen - 1) . $this->getArrowChar();
        }

        $pad = ceil($this->_width - $numberLen - $barLen);
        if ($pad > 0) {
            $bar .= str_repeat($this->getPadChar(), (int)$pad);
        }
        $bar .= $this->getCloseChar();
        $percent = ($complete * 100) . '%';
        $bar .= str_pad($percent, $numberLen, ' ', STR_PAD_LEFT);

        if (!$this->getLocation() && $barPaths) {
            $bar .= ' - ' . $barPaths;
        }

        $this->_io->overwrite($bar, 0);

        return $this;
    }

    /**
     * Sets the info to display with the progress bar.
     * Done using a method instead of an option so it can be used in the closure.
     *
     * @param null|string $info The info to display.
     * @return $this
     */
    public function info(?string $info = null)
    {
        $this->_info = $info;

        return $this;
    }

    /**
     * Sets the progress character
     *
     * @param string $char The progress character to use
     * @return $this
     * @throws \Fr3nch13\Utilities\Exception\InvalidCharException if the character isn't a string or isn't 1 character in length.
     */
    public function setProgressChar(string $char)
    {
        if (strlen($char) !== 1) {
            throw new InvalidCharException(__('The progress character isn\'t 1 character: `{0}`', [
                $char,
            ]));
        }

        $this->_progressChar = $char;

        return $this;
    }

    /**
     * Gets the progress character
     *
     * @return string The progress character
     */
    public function getProgressChar(): string
    {
        return $this->_progressChar;
    }

    /**
     * Sets the arrow character
     *
     * @param string $char The arrow character to use
     * @return $this
     * @throws \Fr3nch13\Utilities\Exception\InvalidCharException if the character isn't a string or isn't 1 character in length.
     */
    public function setArrowChar(string $char)
    {
        if (strlen($char) !== 1) {
            throw new InvalidCharException(__('The arrow character isn\'t 1 character: `{0}`', [
                $char,
            ]));
        }

        $this->_arrowChar = $char;

        return $this;
    }

    /**
     * Gets the arrow character
     *
     * @return string The arrow character
     */
    public function getArrowChar(): string
    {
        return $this->_arrowChar;
    }

    /**
     * Sets the padding character
     *
     * @param string $char The padding character to use
     * @return $this
     * @throws \Fr3nch13\Utilities\Exception\InvalidCharException if the character isn't a string or isn't 1 character in length.
     */
    public function setPadChar(string $char)
    {
        if (strlen($char) !== 1) {
            throw new InvalidCharException(__('The padding character isn\'t 1 character: `{0}`', [
                $char,
            ]));
        }

        $this->_padChar = $char;

        return $this;
    }

    /**
     * Gets the padding character
     *
     * @return string The padding character
     */
    public function getPadChar(): string
    {
        return $this->_padChar;
    }

    /**
     * Sets the opening barrier character
     *
     * @param string $char The opening barrier character to use
     * @return $this
     * @throws \Fr3nch13\Utilities\Exception\InvalidCharException if the character isn't a string or isn't 1 character in length.
     */
    public function setOpenChar(string $char)
    {
        if (strlen($char) !== 1) {
            throw new InvalidCharException(__('The opening barrier character isn\'t 1 character: `{0}`', [
                $char,
            ]));
        }

        $this->_openChar = $char;

        return $this;
    }

    /**
     * Gets the opening barrier character
     *
     * @return string The opening barrier character
     */
    public function getOpenChar(): string
    {
        return $this->_openChar;
    }

    /**
     * Sets the closing barrier character
     *
     * @param string $char The closing barrier character to use
     * @return $this
     * @throws \Fr3nch13\Utilities\Exception\InvalidCharException if the character isn't a string or isn't 1 character in length.
     */
    public function setCloseChar(string $char)
    {
        if (strlen($char) !== 1) {
            throw new InvalidCharException(__('The closing barrier character isn\'t 1 character: `{0}`', [
                $char,
            ]));
        }

        $this->_closeChar = $char;

        return $this;
    }

    /**
     * Gets the closing barrier character
     *
     * @return string The closing barrier character
     */
    public function getCloseChar(): string
    {
        return $this->_closeChar;
    }

    /**
     * Sets the location for the info
     *
     * @param bool $location If true, left, if false, right
     * @return $this
     */
    public function setLocation(bool $location)
    {
        $this->_location = $location;

        return $this;
    }

    /**
     * Gets location for the info
     *
     * @return bool If true, left, if false, right
     */
    public function getLocation(): bool
    {
        return $this->_location;
    }

    /**
     * Sets if we should show the count or not
     *
     * @param bool $showcount If true, show the count
     * @return $this
     */
    public function setShowcount(bool $showcount)
    {
        $this->_showcount = $showcount;

        return $this;
    }

    /**
     * Gets location for the info
     *
     * @return bool If true, left, if false, right
     */
    public function getShowcount(): bool
    {
        return $this->_showcount;
    }

    /**
     * Sets the count format. Should be compatible with the __() function.
     *
     * @param string $countformat The count format string.
     * @return $this
     */
    public function setCountformat(string $countformat)
    {
        $this->_countformat = $countformat;

        return $this;
    }

    /**
     * Gets location for the info
     *
     * @return string The count format string.
     */
    public function getCountformat(): string
    {
        return $this->_countformat;
    }
}
