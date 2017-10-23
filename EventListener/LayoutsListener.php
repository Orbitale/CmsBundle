<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class LayoutsListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $layouts;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(array $layouts, Environment $twig)
    {
        $this->layouts = $layouts;
        $this->twig    = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['setRequestLayout', 1],
        ];
    }

    public function setRequestLayout(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // Get the necessary informations to check them in layout configurations
        $path = $request->getPathInfo();
        $host = $request->getHost();

        // As a layout must be set, we force it to be empty if no layout is properly configured.
        // Then this will throw an exception, and the user will be warned of the "no-layout" config problem.
        $finalLayout = null;

        foreach ($this->layouts as $layoutConfig) {
            $match = false;

            // First check host
            if ($layoutConfig['host'] && $host === $layoutConfig['host']) {
                $match = true;
            }

            // Check pattern
            if ($layoutConfig['pattern'] && preg_match('~'.$layoutConfig['pattern'].'~', $path)) {
                $match = true;
            }

            if ($match) {
                $finalLayout = $layoutConfig;
                break;
            }
        }

        // If nothing matches, we take the first layout that has no "host" or "pattern" configuration.
        if (null === $finalLayout) {
            $layouts = $this->layouts;
            do {
                $finalLayout = array_shift($layouts);
                if ($finalLayout['host'] || $finalLayout['pattern']) {
                    $finalLayout = null;
                }
            } while (null === $finalLayout && count($layouts));
        }

        if (null === $finalLayout || !$this->twig->getLoader()->exists($finalLayout['resource'])) {
            throw new \Twig_Error_Loader(sprintf(
                'Unable to find template %s for layout %s. The "layout" parameter must be a valid twig view to be used as a layout.',
                $finalLayout['resource'], $finalLayout['name']
            ), 0, $finalLayout['resource']);
        }

        $event->getRequest()->attributes->set('_orbitale_cms_layout', $finalLayout);
    }
}
