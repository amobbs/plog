<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace User\Controller;

//use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfcUser\Controller\UserController as ZfcUser;

class UserController extends ZfcUser
{
    public function __construct()
    {
        $this->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
            $controller = $e->getTarget();
            $controller->layout('user/layout');
        });

        $roles = array('salesperson'=>'Salesperson', 'approver'=>'Approver', 'estimator'=>'Estimator', 'office_admin'=>'Office Admin', 'admin'=>'Administrator');
        $events = $this->getEventManager()->getSharedManager();

        $events->attach('ZfcUser\Form\Register','init', function($e) use ($roles) {
            $form = $e->getTarget();
            $form->add(array(
                'type' => 'select',
                'name' => 'user_type',
                'options' => array(
                    'label' => 'User Type',
                    'value_options' => $roles,
                    'selected' => 'salesperson',
                ),
                'value'=>'salesperson',
                'attributes' => array(
                    'id' => 'user_type'
                ),
                'default'=>'salesperson'
            ));
        });

        $events->attach('ZfcUser\Form\RegisterFilter','init', function($e) use ($roles) {
            $form = $e->getTarget();
            $form->add(array(
                'name'       => 'user_type',
                'required'   => true,
            ));
        });
    }

    public function lostPasswordAction()
    {
        $request = $this->getRequest();
        $form = $this->getServiceLocator()->get('lost_password_form');
        $notification = '';

        if ($request->isPost()) {
            $postData = $this->params()->fromPost();
            $user = $this->getServiceLocator()->get('UserService');
            $user->sendReset($postData, $this->url()->fromRoute('zfcuser/reset-password'));
            $notification = 'A password reset e-mail has been e-mailed to you.';
        }

        return new ViewModel(
            array('form' => $form, 'notification' => $notification)
        );
    }

    public function resetPasswordAction()
    {
        $request = $this->getRequest();
        $form = $this->getServiceLocator()->get('reset_password_form');
        $key = $this->params()->fromRoute('reset_id');
        $user = $this->getServiceLocator()->get('UserService');

        // User has attempted to change the password
        if ($request->isPost()) {
            $postData = $this->params()->fromPost();

            $notification = '';
            $form->setData($postData);

            if (!$form->isValid()) {
                // Passwords don't match
                $notification = 'The password and confirmation do not match.';

                return new ViewModel(
                    array('form' => $form, 'notification'=>$form->getMessages(), 'user_id'=>$postData['userId'])
                );
            } else {
                $validatedData = $form->getData();

                $user->resetPassword($validatedData['newCredential'], $validatedData['userId']);
                return $this->redirect()->toRoute('login');
            }

        } else {
            $checkReset = $user->checkReset($key);
            if (!is_null($key) AND $checkReset) {
                $user = $this->getServiceLocator()->get('UserService');

                return new ViewModel(
                    array('form' => $form, 'user_id' => $checkReset)
                );
            }
        }

        return $this->redirect()->toRoute('zfcuser/invalid');
    }

    public function invalidAction()
    {
        return new ViewModel();
    }
}
