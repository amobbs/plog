<?php

namespace MongoUser;

class Module
{
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

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'zfcuser_user_mapper' => function ($sm) {
                    $options = $sm->get('zfcuser_module_options');
                    $mapper = new Mapper\User();
                    $mapper->setConfig($sm->get('config'));
                    $mapper->setEntityPrototype(new Entity\User);
                    $mapper->setHydrator(new \MongoUser\Mapper\UserHydrator(false));
                    return $mapper;
                },
            ),
        );
    }
}
