<?php
declare(strict_types=1);

/**
 * AppController
 */

namespace Fr3nch13\Utilities\Controller;

use Cake\View\JsonView;

/**
 * App Controller
 *
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \Cake\Controller\Component\FlashComponent $Flash
 */

class AppController extends \Cake\Controller\Controller
{
    /**
     * Sets what classes this controller uses.
     *
     * @return array<string>
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * Figures out the referer with a given default.
     *
     * @param mixed|null $referer the default referer.
     * @return mixed The determined referer to use.
     */
    public function getReferer($referer = null)
    {
        if (!$referer) {
            $referer = $this->getRequest()->referer();
        }

        if ($this->getRequest()->getQuery('referer')) {
            $referer = $this->getRequest()->getQuery('referer');
            if (is_string($referer) && strpos($referer, '%') !== false) {
                $referer = urldecode($referer);
            }
        }

        return $referer;
    }
}
