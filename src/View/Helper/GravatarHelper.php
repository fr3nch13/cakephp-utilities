<?php
declare(strict_types=1);

/**
 * Gravatar Helper
 *
 * A helper that provides gravatar images for profile use in CakePHP.
 */

namespace Fr3nch13\Utilities\View\Helper;

use Cake\View\Helper;

/**
 * Gravatar Helper
 *
 * A helper that provides gravatar images for profile use in CakePHP.
 *
 * Based on: Gravatar Helper (https://github.com/PotatoPowered/gravatar-helper)
 */
class GravatarHelper extends Helper
{
    /**
     * Takes and email address and options and returns a gravatar
     *
     * This function takes in a users email address and options and then provides either a gravatar image html tag
     * or an image tag with the specified default options.
     *
     * @param string $email The gravatar email address.
     * @param array<string, mixed> $options An array specify overrides to the default options
     * - size: The width and height of the profile (150 default)
     * - default: The default gravatar image (mm default) [List Here](http://en.gravatar.com/site/implement/images/)
     * - class: The css class of the image tag (gravatar default)
     * @return string The HTML IMG tag for the gravatar
     */
    public function avatar(string $email, array $options = []): string
    {
        if (!isset($options['class'])) {
            $options['class'] = 'gravatar';
        }

        return '<img alt=" ' . __('Avatar for {0}', [$email]) .
            '" class="' . $options['class'] . '" src="' .
            $this->url($email, $options) . '"/>';
    }

    /**
     * Creates the url for the above image tag, or anywhere else it needs to be used.
     *
     * @param string $email The gravatar email address.
     * @param array<string, mixed> $options An array specify overrides to the default options
     * - size: The width and height of the profile (150 default)
     * - default: The default gravatar image (mm default) [List Here](http://en.gravatar.com/site/implement/images/)
     * @return string The HTML IMG tag for the gravatar
     */
    public function url(string $email, array $options = []): string
    {
        // The gravatar base URL
        $gravatar = 'https://www.gravatar.com/avatar/';

        if (!isset($options['size'])) {
            $options['size'] = 150;
        }
        if (!isset($options['default'])) {
            $options['default'] = 'mp';
        }

        $size = '?&s=' . $options['size'];
        $default = '&d=' . $options['default'];
        $emailHash = md5(strtolower(trim($email)));

        return $gravatar . $emailHash . $size . $default;
    }
}
