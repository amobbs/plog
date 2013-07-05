<?php

namespace User\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResetPasswordFormServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $form = new \User\Form\ResetPassword;
        $form->setInputFilter(new \User\Form\ResetPasswordFilter);

        return $form;
    }
}
