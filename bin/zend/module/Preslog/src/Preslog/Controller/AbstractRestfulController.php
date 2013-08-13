<?php
/**
 * Preslog Abstract Restful Controller
 * Provides functions for Authorisation and functional denial
 *
 * @author      4mation Technlogies
 * @link        http://www.4mation.com.au
 * @author      Dave Newson <dave@4mation.com.au>
 * @copyright   Copyright (c) MediaHub Australia
 * @link        http://mediahubaustralia.com.au
 */

namespace Preslog\Controller;

use Zend\Mvc\Controller\AbstractRestfulController as arc;
use Zend\Http\Response as Response;
use Zend\View\Model\JsonModel;


class AbstractRestfulController extends arc
{

    /**
     * Used to send a 401 Unauthorised response - User has insufficient privileges
     * Shortcut for createError
     * @param   array   $options    Custom response variables
     * @return  JsonModel
     */
    function errorForbidden( $options=array() )
    {
        // 401: Unauthorised - you must have appropriate permissions
        return $this->createError( Response::STATUS_CODE_401, $options );
    }


    /**
     * Used to send a 500 Internal Server Error response - Request has gone wrong
     * Shortcut for createError
     * @param   array   $options    Custom response variables
     * @return  JsonModel
     */
    function errorGeneric( $options=array() )
    {
        // 500: Internal Server Error - generic error
        return $this->createError( Response::STATUS_CODE_500, $options );
    }


    /**
     * Used to send a 504 Gateway Timeout response - System this request relies on is unavailable
     * Alias for CreateError
     * @param   array   $options    Custom response variables
     * @return JsonModel
     */
    function errorGateway( $options=array() )
    {
        return $this->createError( Response::STATUS_CODE_504, $options );
    }


    /**
     * Builds an error response to send to the browser
     * - Expects an HTTP code
     * - Includes facilities for custom messages
     * @param   int     $code       HTTP code
     * @param   array   $options    Custom response variables
     * @return  JsonModel
     */
    public function createError( $code=500, $options=array() )
    {
        // Set the error code
        $response = $this->getResponse();
        $response->setStatusCode( $code );

        // Use preferred phrase if no message supplied
        $options['message'] = ( isset($options['message']) && !empty($options['message']) ? $options['message'] : $response->getReasonPhrase() );

        // Code
        $options['code'] = $code;

        // Response
        return new JsonModel($options);
    }


}
