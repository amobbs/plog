<?php

namespace User\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use User\Mapper\User as UserMapper;
use ZfcUser\Mapper\UserHydrator;

class UserMapperServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator;
        $options = $sm->get('zfcuser_module_options');
        $mapper = new UserMapper();
        $mapper->setDbAdapter($sm->get('zfcuser_zend_db_adapter'));
        $entityClass = $options->getUserEntityClass();
        $mapper->setEntityPrototype(new $entityClass);
        $mapper->setHydrator(new UserHydrator());
        $mapper->setTableName($options->getTableName());

        return $mapper;
    }
}
