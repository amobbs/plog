<?php

namespace User\View;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use User\View\UnauthorizedStrategy;

class UnauthorizedStrategyFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $sl
     * @return UnauthorizedStrategy
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $rbacService = $sl->get('ZfcRbac\Service\Rbac');

        $strategy = new UnauthorizedStrategy();

        return $strategy;
    }
}