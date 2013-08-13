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
use Swagger\Annotations as SWG;


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

     * @SWG\Resource(
     *      resourcePath="/",
     *      @SWG\Api(
     *          path="/docs",
     *          @SWG\Operation(
     *              nickname="api/docs",
     *              summary="Provides this JSON API Documentation",
     *              httpMethod="GET"
     *          )
     *      )
     * )
     */
    public function swaggerDocsAction()
    {
        $swagger = $this->getServiceLocator()->get('service.swagger');
        $swagger->flushCache();
        $json = $swagger->getResource('/', true, true);
        $array = (array) Json::decode($json);

        return new JsonModel( $array );

    }


}

