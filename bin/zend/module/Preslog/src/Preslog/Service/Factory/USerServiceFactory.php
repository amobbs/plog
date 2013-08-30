<?php
/**
 * User Service Factory
 * Fetches the User Service, with attached DB Adapter, Entity and Hydrator.
 */

namespace Preslog\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Mongo\Hydrator\Strategy\MongoIdStrategy;
use Zend\Stdlib\Hydrator\Filter\MethodMatchFilter;
use Zend\Stdlib\Hydrator\Filter\FilterComposite;

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
        $mapper->setServiceLocator($serviceLocator);

        // Configure hydrator
        $hydrator = $mapper->getHydrator();
        $hydrator->setUnderscoreSeparatedKeys(false);
        $hydrator->addStrategy("id", new MongoIdStrategy());            // Handle mongo IDs

        // Filters
        // :WARN: I do not understand why this works. Surely these should be OR filters??!
        $myFilters = new FilterComposite();
        $myFilters->addFilter('get_id', new MethodMatchFilter('get_id'), FilterComposite::CONDITION_AND);
        $myFilters->addFilter('getRoles', new MethodMatchFilter('getRoles'), FilterComposite::CONDITION_AND);
        $hydrator->addFilter("get", $myFilters );

        // Configure Service
        $service = new UserService;
        $service->setMapper($mapper);

        // Use service
        return $service;

    }

}
