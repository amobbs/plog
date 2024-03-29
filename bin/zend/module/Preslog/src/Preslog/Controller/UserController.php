<?php
/**
 * Preslog User Controller
 * - Create, Edit, Delete Users
 *
 * @author      4mation Technlogies
 * @link        http://www.4mation.com.au
 * @author      Dave Newson <dave@4mation.com.au>
 * @copyright   Copyright (c) MediaHub Australia
 * @link        http://mediahubaustralia.com.au
 */

namespace Preslog\Controller;

use Preslog\Controller\AbstractRestfulController;
use Zend\Form\Annotation\Hydrator;
use Zend\View\Model\JsonModel;
use Swagger\Annotations as SWG;
use Preslog\Form\UserForm;
use Preslog\Form\UserFilter;

class UserController extends AbstractRestfulController
{

    /**
     * Read data for My-Profile
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="users.my-profile.read",
     *      summary="Fetch My Profile data",
     *      notes="Any logged in user may load this data."
     * )
     */
    public function readMyProfileAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Read My Profile',
        ));
    }


    /**
     * Update data for My-Profile
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="users.my-profile.update",
     *      summary="Update My Profile data",
     *      notes="Updates are applied to the currently logged in user account."
     * )
     */
    public function updateMyProfileAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Update My Profile',
        ));
    }


    /**
     * Read data for My-Notifications
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="users.my-notifications.read",
     *      summary="Read My Notifications data",
     *      notes="Any logged in user may load this data."
     * )
     */
    public function readMyNotificationsAction()
    {
        // Fetch user service
        $userService = $this->getServiceLocator()->get('Preslog\Service\User');

        return new JsonModel(array(
            'todo' => 'TODO - Read My Notifications',
        ));
    }


    /**
     * Update data for My-Notifications
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="users.my-notifications.update",
     *      summary="Update My Notifications data",
     *      notes="Updates are applied to the currently logged in user account."
     * )
     */
    public function updateMyNotificationsAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Update My Notifications',
        ));
    }


    /**
     * List users
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.users.read",
     *      summary="List users",
     *      notes="User must be an Administrator"
     * )
     */
    public function readListAction()
    {
        // Validate: User must be administrator
        if ( !$this->getServiceLocator()->get('ZfcRbac\Service\Rbac')->isGranted('admin') ) {
            return $this->errorForbidden();
        }

        // Using the User Service, find all users.
        $userService = $this->getServiceLocator()->get('Preslog\Service\User');
        $users = $userService->findAll();

        return new JsonModel(array(
            'users'=>$users->toArray()
        ));
    }

    /**
     * Get form options for users
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.users.options",
     *      summary="Retrieves form options for users",
     *      notes="User must be an Administrator"
     * )
     */
    public function optionsAction()
    {

        // :TODO:
        return new JsonModel(array(
            'todo' => 'TODO: Admin user options',
            'user'=>array(
                'clients' => array(
                    '1'=>'derp',
                    '2'=>'derpderp',
                ),
                'roles' => array(
                    'super-admin',
                    'admin',
                    'operator',
                ),
                'notifications' => array(
                    'types' => array(
                        'email', 'sms'
                    ),
                    'clients' => array(
                        'win' => array(
                            '5'=>'WIN',
                            '6'=>'WIN 2',
                            '7'=>'WIN 3',
                        ),
                        'abc' => array(
                            '1'=>'ABC 1',
                            '2'=>'ABC 2',
                            '3'=>'ABC 3',
                        ),
                    ),
                ),
            ),
        ));
    }

    /**
     * Create a user
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.users.create",
     *      summary="Create a new user",
     *      notes="User must be an Administrator"
     * )
     */
    public function createAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO: Admin create user',
        ));
    }


    /**
     * Read a given user
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.users.specific.read",
     *      summary="Fetch data for a specific user",
     *      notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="user_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="User ID"
     *          )
     *      )
     * )
     */
    public function readAction()
    {
        $id = $this->params('user_id', null);

        // Validate: User must be administrator
        if ( !$this->getServiceLocator()->get('ZfcRbac\Service\Rbac')->isGranted('admin') ) {
            return $this->errorForbidden();
        }

        // Validate: ID must be supplied
        if ( !$id ) {
            return $this->errorGeneric(array(
                'message' => 'User ID required'
            ));
        }

        // Using the User Service, find the specific user
        $userService = $this->getServiceLocator()->get('Preslog\Service\User');
        $user = $userService->findById( $id );

        // Validate: Find must locate a user
        if ( !$user || !$user->get_id() ) {
            return $this->errorGeneric(array(
                'message'=>'Specified user does not exist'
            ));
        }

        // Output as array
        return new JsonModel(array(
            'user' => $userService->getMapper()->getHydrator()->extract($user)
        ));
    }


    /**
     * Update a given user
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.users.specific.update",
     *      summary="Update a specific user",
     *       notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="user_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="User ID"
     *          )
     *      )
     * )
     */
    public function updateAction()
    {
        $id = $this->params('user_id', 'none specified');

        // Validate: User must be administrator
        if ( !$this->getServiceLocator()->get('ZfcRbac\Service\Rbac')->isGranted('admin') ) {
            return $this->errorForbidden();
        }

        // Validate: ID must be supplied
        if ( !$id ) {
            return $this->errorGeneric(array(
                'message' => 'User ID required'
            ));
        }

        // Get UserService
        /** @var \Preslog\Service\User $userService */
        $userService = $this->getServiceLocator()->get('Preslog\Service\User');

        // Initialise validator
        $userForm = new UserForm();
        $userForm->setHydrator( $userService->getMapper()->getHydrator() );
        $userForm->bind( $userService->getEntity() );
        $userForm->setInputFilter( new UserFilter() );

        // Validate
        $userForm->setData( $this->params()->fromJson() );
        if ( !$userForm->isValid() )
        {
            return new JsonModel(array(
                'user'=>array(
                    'error'=>true,
                    'message'=>'There was a problem with your submission',
                    'data'=>$userForm->getMessages()
                )
            ));
        }

        $user = $userForm->getData();

        // Using the User Service, find the specific user
        $userService = $this->getServiceLocator()->get('Preslog\Service\User');
        $result = $userService->update( $user );

        return new JsonModel(array(
            'todo' => 'TODO: Admin update specific user ('.$id.')',
        ));
    }


    /**
     * Delete a given user
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.users.specific.delete",
     *      summary="Delete a specific user",
     *      notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="user_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="User ID"
     *          )
     *      )
     * )
     */
    public function deleteAction()
    {
        $id = $this->params('user_id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Admin delete specific user ('.$id.')',
        ));
    }

}
