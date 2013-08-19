<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Preslog;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Preslog\View\ExceptionStrategy;
use Preslog\View\RouteNotFoundStrategy;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // attach special listeners
        $this->attachListeners( $e );
    }


    /**
     * Override details strategies for certain render events, and override with new JSON solution.
     * @param MvcEvent $event
     */
    protected function attachListeners( MvcEvent $event )
    {
        // Get services
        $application = $event->getApplication();
        $sm = $application->getServiceManager();
        $em = $application->getEventManager();

        //Attach the JSON ExceptionStrategy to handle Exceptions
        $exceptionStrategy = new ExceptionStrategy();
        $exceptionStrategy->attach($em);

        //Attach the JSON RouteNotFoundStrategy to handle 404
        $routeNotFoundStrategy = new RouteNotFoundStrategy();
        $routeNotFoundStrategy->attach($em);

        // Detach default ExceptionStrategy
        $sm->get('Zend\Mvc\View\Http\ExceptionStrategy')->detach($em);

        // Detach default RouteNotFoundStrategy
        $sm->get('Zend\Mvc\View\Http\RouteNotFoundStrategy')->detach($em);
    }


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

}
