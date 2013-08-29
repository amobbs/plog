<?php
/**
 * User Service Factory
 * Fetches the User Service, with attached DB Adapter, Entity and Hydrator.
 */

namespace Preslog\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Preslog\Service\User as UserService;
use Preslog\Mapper\User  as UserMapper;

class UserServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Configure mapper
        $mapper = new UserMapper;
        $mapper->setDbAdapter($serviceLocator->get('config'));
        $mapper->setEntityPrototype($serviceLocator->get('Preslog\Entity\User'));
        $mapper->getHydrator()->setUnderscoreSeparatedKeys(false);
        $mapper->setServiceLocator($serviceLocator);

        // Configure Service
        $service = new UserService;
        $service->setMapper($mapper);

        // Use service
        return $service;

    }

}
