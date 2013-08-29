<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace User;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;


class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


    /**
     * Config hack for Register form
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        // Instigate loading of the user profile on EVENT_ROUTE
        // Prevents errors occurring inside onBootstrap which we can't recover from.
        $app = $e->getTarget();
        $app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, array($this, 'loadCurrentIdentity'));


        // Form

        $roles = array('super-admin'=>'Super Admin', 'operator'=>'Operator');

        $events = $e->getApplication()->getEventManager()->getSharedManager();

        // Attach Additional Form Elements
        $events->attach('ZfcUser\Form\Register','init', function($e) use ($roles) {

            $form = $e->getTarget();

            $form->add(array(
                'name' => 'firstname',
                'type' => 'text',
                'options' => array(
                    'label' => 'First Name'
                ),
            ));

            $form->add(array(
                'name' => 'lastname',
                'type' => 'text',
                'options' => array(
                    'label' => 'Last Name'
                ),
            ));

            $form->add(array(
                'name' => 'role',
                'type' => 'select',
                'options' => array(
                    'label' => 'Role',
                    'value_options' => $roles,
                    'selected' => '',
                ),
                'attributes' => array(
                    'id' => 'role'
                ),
            ));


        });

        // Attach Validator Events
        $events->attach('ZfcUser\Form\RegisterFilter','init', function($e) use ($roles) {
            $form = $e->getTarget();

            $form->add(array(
                'name'       => 'firstname', 'required'   => true,
                'validators' => array(array('name' => 'StringLength', 'options' => array( 'min' => 1, 'max' => 120,),),),
            ));

            $form->add(array(
                'name'       => 'role',
                'required'   => true,
            ));

            $form->add(array(
                'name'       => 'lastname', 'required'   => true, 'allowEmpty' => true,
                'validators' => array(array('name' => 'StringLength', 'options' => array( 'min' => 1, 'max' => 120,),),),
            ));


        });
    }


    public function loadCurrentIdentity( MvcEvent $e )
    {
        $sm = $e->getApplication()->getServiceManager();

        // If the user has identity in store, try to fetch it.
        if ($sm->get('zfcuser_auth_service')->hasIdentity()) {
            $rbac = $sm->get('ZfcRbac\Service\Rbac');
            $role = $sm->get('zfcuser_auth_service')->getIdentity()->getRoles();
            $rbac->setIdentity( $role );
        }
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
