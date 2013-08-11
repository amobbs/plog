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

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class UserController extends AbstractActionController
{
    /**
     * Read data for My-Profile
     * @return JsonModel
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
     */
    public function updateMyNotificationsAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO - Update My Notifications',
        ));
    }
}
