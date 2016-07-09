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

use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LayoutsListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $layouts;

    /**
     * @var TwigEngine
     */
    private $templating;

    public function __construct(array $layouts, TwigEngine $templating)
    {
        $this->layouts    = $layouts;
        $this->templating = $templating;
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

        $layouts = $this->layouts;

        // As a layout must be set, we force it to be empty if no layout is properly configured.
        // Then this will throw an exception, and the user will be warned of the "no-layout" config problem.
        $finalLayout = null;

        foreach ($layouts as $layoutConfig) {
            $match = false;
            if ($layoutConfig['host'] && $host === $layoutConfig['host']) {
                $match = true;
            }
            if ($layoutConfig['pattern'] && preg_match('~'.$layoutConfig['pattern'].'~', $path)) {
                $match = true;
            }
            if ($match) {
                $finalLayout = $layoutConfig;
                break;
            }
        }

        if (null === $finalLayout || !$this->templating->exists($finalLayout['resource'])) {
            throw new \Twig_Error_Loader(sprintf(
                'Unable to find template %s for layout %s. The "layout" parameter must be a valid twig view to be used as a layout.',
                $finalLayout['resource'], $finalLayout['name']
            ), 0, $finalLayout['resource']);
        }

        $event->getRequest()->attributes->set('_orbitale_cms_layout', $finalLayout);
    }
}
