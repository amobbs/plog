<?php
/**
 * Preslog Client Controller
 * - Manages clients within the Preslog system
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

class ClientController extends AbstractActionController
{
    public function indexAction()
    {
        return new JsonModel(array(
            'todo' => 'TODO',
        ));
    }
}
