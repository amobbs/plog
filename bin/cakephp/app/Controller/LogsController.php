<?php

/**
 * Class LogController
 */

use Swagger\Annotations as SWG;

class LogController extends AppController
{
    public $uses = array();



    /**
     * Create log
     * Create the specified log
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="logs.create",
     *      summary="Createa new Log",
     *      notes="User must have permission to access the Client to which this Log belongs. User must have permissions to create logs."
     * )
     *
     * @SWG\Operation(
     *      partial="logs.read",
     *      summary="Returns a log where requested, and loggable field criteria",
     *      notes="User must have permission to access the Client to which the Log belongs.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="log_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Log ID"
     *          )
     *      )
     * )
     *
     * @SWG\Operation(
     *      partial="logs.update",
     *      summary="Updates the specified log using POST data",
     *      notes="User must have permission to access the Client to which this Log belongs. Some clients have restricted update rights.",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="log_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Log ID"
     *          )
     *      )
     * )
     */
    public function edit()
    {

    }




    public function getOptions()
    {

    }


    /**
     * Delete log by URL param "id"
     * @return JsonModel
     *
     * @SWG\Operation(
     *      partial="logs.delete",
     *      summary="Deletes the specified log",
     *      notes="User must be an Administrator",
     *      @SWG\Parameters(
     *          @SWG\Parameter(
     *              name="log_id",
     *              paramType="path",
     *              dataType="int",
     *              required="true",
     *              description="Log ID"
     *          )
     *      )
     * )
     */
    public function delete()
    {

    }


}
