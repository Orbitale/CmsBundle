<?php

namespace Pierstoval\Bundle\CmsBundle\Listeners;


use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LayoutsListener implements EventSubscriberInterface
{

    /**
     * @var array
     */
    private $config;

    /**
     * @var TwigEngine
     */
    private $templating;

    public function __construct(array $config, TwigEngine $templating)
    {
        $this->config     = $config;
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('setRequestLayout', 0),
        );
    }

    public function setRequestLayout(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // Get the route and pathinfo to check respectively the "route" and "pattern" attributes in layouts config
        $route = $request->attributes->get('_route');
        $path  = $request->getPathInfo();

        $layouts = $this->config['layouts'];

        // As a layout must be set, we force it to be empty if no layout is properly configured.
        // Then this will throw an exception, and the user will be warned of the "no-layout" config problem.
        $finalLayout = '';

        foreach ($layouts as $layoutConfig) {
            if (isset($layoutConfig['route']) && $layoutConfig['route'] && $route === $layoutConfig['route']) {
                $finalLayout = $layoutConfig['resource'];
                break;
            }
            if (isset($layoutConfig['pattern']) && $layoutConfig['pattern'] && preg_match('~'.$layoutConfig['pattern'].'~', $path)) {
                $finalLayout = $layoutConfig['resource'];
                break;
            }
        }

        if (!$this->templating->exists($finalLayout)) {
            throw new \Twig_Error_Loader(sprintf(
                'Unable to find template %s. The "layout" parameter must be a valid twig view to be used as a layout.',
                $finalLayout
            ), 0, $finalLayout, null);
        }

        $event->getRequest()->attributes->set('_cms_layout', $finalLayout);

    }
}
