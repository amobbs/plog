<?php

namespace User\View;

use ZfcRbac\Service\Rbac;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\JsonModel;

class UnauthorizedStrategy implements ListenerAggregateInterface
{

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'prepareUnauthorizedViewModel'));
    }

    /**
     * Detach aggregate listeners from the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Create an unauthorized view model, and set the HTTP status code
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareUnauthorizedViewModel(MvcEvent $e)
    {
        // Do nothing if no error in the event
        $error = $e->getError();
        if (empty($error)) {
            return;
        }

        // Do nothing if the result is a response object
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        switch ($error) {
            case Rbac::ERROR_CONTROLLER_UNAUTHORIZED:
                $model = new JsonModel(array(
                    'error'      => $e->getParam('error'),
                    'controller' => $e->getParam('controller'),
                    'action'     => $e->getParam('action'),
                    'identity'   => $e->getParam('identity'),
                ));

                break;
            case Rbac::ERROR_ROUTE_UNAUTHORIZED:
                $model = new JsonModel(array(
                    'error'    => $e->getParam('error'),
                    'route'    => $e->getParam('route'),
                    'identity' => $e->getParam('identity')
                ));

                break;
            default:
                return;
                break;
        }

        $e->setResult($model);

        $response = $e->getResponse();
        if (!$response) {
            $response = new HttpResponse();
            $e->setResponse($response);
        }
        $response->setStatusCode(403);
    }
}
