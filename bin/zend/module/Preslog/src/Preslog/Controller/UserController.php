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
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/users/my-profile",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Fetch My Profile data",
     *              httpMethod="GET"
     *          )
     *      )
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
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/users/my-notifications",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Update My Profile data",
     *              httpMethod="POST",
     *              responseClass="User",
     *              @SWG\Parameter(name="email",dataType="string")
     *          )
     *      )
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
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/users/my-notifications",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Fetch My Notifications data",
     *              httpMethod="GET",
     *              responseClass="User"
     *          )
     *      )
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
     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/users/my-notifications",
     *          @SWG\Operation(
     *              nickname="index",
     *              summary="Update My Notifications data",
     *              httpMethod="POST",
     *              responseClass="User"
     *          )
     *      )
     * )
     */
    public function updateMyNotificationsAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Update My Notifications',
        ));
    }
}
