<?php
/**
 * Preslog API Controller
 * - No system processing; displays API help only.
 *
 * @author      4mation Technlogies
 * @link        http://www.4mation.com.au
 * @author      Dave Newson <dave@4mation.com.au>
 * @copyright   Copyright (c) MediaHub Australia
 * @link        http://mediahubaustralia.com.au
 */

namespace Preslog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Stdlib\ArrayUtils;

class ApiController extends AbstractActionController
{
    /**
     * Display the API Doc homepage, using Swagger.
     * @return array|ViewModel
     */
    public function swaggerViewAction()
    {
        return new ViewModel();
    }


    /**
     * Output the API Documentation JSON for Swagger API Documentor
     * @return JsonModel
     */
    public function swaggerDocsAction()
    {

        $x = 1;
        $router = $this->getServiceLocator()->get('Router');

        //$router = new \Zend\Mvc\Router\Http\TreeRouteStack;
        $routes = $router->getRoutes();

        $structure = $this->extrapolateRoutes( $routes );

        return new JsonModel(array(
            'apiVersion' => '1.0.0',
            'swaggerVersion' => '1.2',
            'basePath' => '',
            'resourcePath' => '',
            'produces' => array('application\json'),
            'apis' => $structure,
        ));

    }


    // This should probably be a service item
    // WARNNING: This won't work, because te "operations" work off a single URI.
    // It's not a one-uri-per-op arrangement. Gah.
    public function extrapolateRoutes( $routes )
    {
        $ret = array();

        foreach ($routes as $route)
        {
            $item = new stdClass;
            $item->path = '/test';
            $item->nickname = 'test';


            // Add children
            if ( isset($route['child_routes']) && sizeof($route['child_routes']) )
            {
                $ret += $this->extrapolateRoutes($route['child_routes']);
            }
        }

        return $ret;
    }
    /**
            array(
                (object) array(
                    'path' => '/logs/{logId}',
                    'operations' => array(
                        (object) array(
                            "responseClass" => "Logs",
                            'nickname' => 'Read Log',
                            "method" => "GET",
                            "summary" => "Get a log by id",
                            "notes" => "Must be logged in, will only return logs the user has permissions to access.",
                            'parameters' => array(
                                (object) array(
                                    "name" => "logId",
                                    "description"=> "ID of log that needs to be fetched",
                                    "required" => false,
                                    "allowMultiple" => false,
                                    "dataType" => "int",
                                    "paramType" => "path",
                                ),
                            ),
                        ),
                    ),
                ),


            ),
        ));
    }
    */
}

