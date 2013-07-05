<?php

namespace User\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use User\Form\LostPassword;
use User\Form\LostPasswordFilter;

class LostPasswordFormServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $form = new LostPassword;
        $form->setInputFilter(new LostPasswordFilter);

        return $form;
    }
}
