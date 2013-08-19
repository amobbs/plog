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
use Zend\View\Model\JsonModel;
use Swagger\Annotations as SWG;

class UserController extends AbstractRestfulController
{

    /**
     * Read data for My-Profile
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="users.my-profile.read",
     *      summary="Fetch My Profile data"
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
     *      summary="Update My Profile data"
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
     *      summary="Read My Notifications data"
     * )
     */
    public function readMyNotificationsAction()
    {
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
     *      summary="Update My Notifications data"
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
     *      summary="List users"
     * )
     */
    public function readListAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO: Admin list users',
        ));
    }


    /**
     * Create a user
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.users.create",
     *      summary="Create a new user"
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
     *      summary="Fetch data for a specific user"
     * )
     */
    public function readAction()
    {
        $id = $this->params('id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Admin read specific user ('.$id.')',
        ));
    }


    /**
     * Update a given user
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="admin.users.specific.update",
     *      summary="Update a specific user"
     * )
     */
    public function updateAction()
    {
        $id = $this->params('id', 'none specified');

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
     *      summary="Delete a specific user"
     * )
     */
    public function deleteAction()
    {
        $id = $this->params('id', 'none specified');

        return new JsonModel(array(
            'todo' => 'TODO: Admin delete specific user ('.$id.')',
        ));
    }

}
