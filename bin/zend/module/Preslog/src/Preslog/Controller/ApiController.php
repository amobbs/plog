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
use Zend\Json\Json;
use Zend\Http\Response as Response;
use Swagger\Annotations as SWG;
use Swagger\Swagger as Swagger;


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
        // Initialize Swagger
        $swagger = $this->getServiceLocator()->get('service.swagger');
        $swagger->flushCache();

        // Get Params
        $resource = $this->params('resource', null);

        // Try to get a specific resource where specified
        if ( !empty($resource) )
        {
            // Check it exists
            $resources = $swagger->getResourceNames();
            if ( !in_array( $resource, $resources ))
            {
                $this->getResponse()->setStatusCode( Response::STATUS_CODE_404 );
                return new JsonModel();
            }

            // Get resource
            $res = $swagger->getResource($resource, false, false);
            $data = Swagger::export($res);
        }
        // Show all available resources
        else
        {
            // Fetch resource list
            $data = $swagger->getResourceList(false, false);

            foreach ($data['apis'] as &$api)
            {
                $api['path'] = '/docs'.$api['path'];
            }

        }

        // Versions
        $data['apiVersion'] = '1.0.0';
        $data['swaggerVersion'] = '1.2';

        // Specify basepath
        $event = $this->getEvent();
        $request = $event->getRequest();
        $router = $event->getRouter();
        $uri = $router->getRequestUri();
        $data['basePath'] = sprintf('%s://%s%s', $uri->getScheme(), $uri->getHost(), $request->getBaseUrl());

        // Return model
        return new JsonModel( $data );

    }


}

