<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace User\Controller;

use Zend\Json\Json;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use ZfcRbac\Service\Rbac;
use ZfcUser\Controller\UserController as ZfcUser;
use Swagger\Annotations as SWG;

class UserController extends ZfcUser
{

    /**
     * Login form
     * - Modified: Now uses a JSON view
     *
     *
     * @SWG\Operation(
     *      partial="users.login",
     *      summary="Attempt to login the user.",
     *      notes="Attempts to login with existing Session, then form data.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="identity",
     *              paramType="form",
     *              dataType="string",
     *              required="false",
     *              description="Users username"
     *          ),
     *          @SWG\Parameter(
     *              name="credential",
     *              paramType="form",
     *              dataType="string",
     *              required="false",
     *              description="Users password"
     *          )
     *      )
     * )
     */
    public function loginAction()
    {
        // Check if user is logged in already
        // This works off the Session variables
        if ($this->zfcUserAuthentication()->getAuthService()->hasIdentity()) {
            return $this->getUsersIdentity();
        }

        // Set up the login "form", used to validate the login fields
        $request = $this->getRequest();
        $form    = $this->getLoginForm();
        $form->setData($this->params()->fromJson());

        // Check the form validates
        if (!$form->isValid()) {
            return new JsonModel(array(
                'login'=>array(
                    'error'=>true,
                    'message'=>'Login details failed to validate',
                    'data'=>$form->getMessages()
                )
            ));
        }

        // clear adapters
        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        // Pass to the authentication method
        return $this->authenticateAction();
    }


    /**
     * Logout and clear the identity
     *
     * @SWG\Operation(
     *      partial="users.logout",
     *      summary="Logout the current user.",
     *      notes=""
     * )
     */
    public function logoutAction()
    {
        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthAdapter()->logoutAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        return new JsonModel(array(
            'success'=>true
        ));
    }


    /**
     * General-purpose authentication action
     */
    public function authenticateAction()
    {
        // Check if user is logged in already
        if ($this->zfcUserAuthentication()->getAuthService()->hasIdentity()) {
            return $this->getUsersIdentity();
        }

        // Get auth adapter
        // Set auth adapter up with the request
        $adapter = $this->zfcUserAuthentication()->getAuthAdapter();
        $result = $adapter->prepareForAuthentication($this->getRequest());

        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return new JsonModel(array(
                'login' => array(
                    'error'=>true,
                    'message' => 'Unknown authentication error'
                )
            ));
        }

        // Athenticate using the adapter
        $auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);

        // If not valid..
        if (!$auth->isValid()) {

            $adapter->resetAdapters();
            return new JsonModel(array(
                'login'=> array(
                    'error'=>true,
                    'message' => 'Invalid email or password'
                )
            ));
        }

        // Logged in!
        return $this->getUsersIdentity();
    }


    /**
     *
     */
    protected function getUsersIdentity()
    {
        // Fetch user
        $user = array();

        // Fetch rbac Service so we can harvest Permissions
        $rbacService = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        $x = $rbacService->getOptions()->getProviders();

        foreach ($x as $type)
        {
            $item = current($type);
            $types[ key($type) ] = $item;
        }

        foreach ($types['roles'] as $k=>$v)
        {
            $roles[] = $k;
        }

        // Fetch permissions list
        $perms = array();

        // get user roles inheretance?
        // $this->getIdentity()->getRoles();

        // get rbac?
        // v$rbacService = $controller->getServiceLocator()->get('ZfcRbac\Service\Rbac');



        return new JsonModel(array(
            'login'=> array(
                'success'=>true,
                'user' => $user,
                'permissions' => $perms,
            )
        ));
    }


    /**
     * Lost Password
     * @return ViewModel
     */
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


    /**
     * Reset Password
     * @return \Zend\Http\Response|ViewModel
     */
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


    /**
     * ??
     * @return ViewModel
     */
    public function invalidAction()
    {
        return new JsonModel();
    }
}
