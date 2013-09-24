<?php

use Swagger\Annotations as SWG;

/**
 * Class LogController
 *
 * @property    Log                         $Log
 * @property    LogNotificationComponent    $LogNotification
 */
class LogsController extends AppController
{
    public $uses = array('Log');

    public $components = array('LogNotification');


    /**
     * Create or Update the given log
     * @param   int         Log ID
     *
     * @SWG\Operation(
     *      partial="logs.create",
     *      summary="Create new Log",
     *      notes="User must have permission to access the Client to which this Log belongs. User must have permissions to create logs."
     * )
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
    public function edit( $id = null )
    {
        // TODO

        $log = $this->request->data['Log'];

        //  If we have an ID, we need to validate the original log first before it can be updated
        if ( !empty($id) )
        {
            // Try to fetch the log
            $sourceLog = $this->Log->findByHrid( (int) $id );

            // Validate: does the log exist?
            if (false)
            {
                $this->errorNotFound(array('message'=>'Log could not be found'));
            }

            // Validate: Does this user have permissions to see this log?
            // if SingleClient, the Client ID should match the current users.
            if (false)
            {
                $this->errorUnauthorised(array('message'=>'You do not have permission to edit this log'));
            }

            // Set the ID log the log to this ID, to be sure.
            $log['_id'] = $sourceLog['_id'];
            $log['version'] = $sourceLog['version'] + 1;
        }
        else
        {
            // Not loading an existing log. Ensure the ID is cleared
            unset($log['_id']);
            $log['version'] = 1;
            $log['created'] = new MongoDate( time() );
            //$log['created_user_id'] = 'user_log_id_here';
        }

        // Validate: Different user permissions are allowed to update restricted series of fields.
        // Apply this restriction below.

        // Validate: Comment-only permissions
        if (false) // User permissions == comment-only
        {
            // If NOT editing a log, bail out. comment-only can't create new logs.
            if (!isset($sourceLog))
            {
                $this->errorUnauthorised(array('message'=>'You do not have permission to create new logs'));
            }

            // Remove any submitted data EXCEPT the comments field.
            //$log = array( 'comments' => $log['comments'] );
        }

        // Validate: Can edit the accountability-and-status fields?
        if (false) // User role != accountability-and-status
        {
            //unset($log['accountability']);
            //unset($log['status']);
        }

        // Update: Last Modified fields
        $log['modified'] = new MongoDate( time() );
        //$log['modified_user_id'] = 'This users ID';

        // Validate field options
        $this->Log->set( $log );
        if ( !$this->Log->validates() )
        {
            $this->errorBadRequest( array('data'=>$this->Log->validationErrors, 'message'=>'Validation failed') );
        }

        // Save
        $ret = $this->Log->save( $log );

        // Notifications: Issue Email and SMS notifications relevant to this logs update.
        $this->LogNotification->issueNotifications( $log );

        // Return success
        $return = array('Success'=>$ret);
        $this->set($return);
        $this->set('_serialize', array_keys($return));
    }


    /**
     * Read specified log
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
     */
    public function read( $id )
    {
        // TODO
        $this->LogNotification->issueNotifications( array() );
        die();

        // Fetch log from DB
        $log = array($id);

        // Validate: does this log exist
        if (false)
        {
            $this->errorNotFound(array('message'=>'Log could not be found'));
        }

        // Validate: does the given user have access to this log? - Client IDs are a match if used has single-client
        if (false)
        {
            $this->errorUnauthorised(array('message'=>'You do not have permission to view this log'));
        }

        // Validate: Does the client have permission to see Accountability fields?
        if (false)
        {
            unset($log['Log']['accountability']);
        }

        // Output
        $this->set($log);
        $this->set('_serialize', array_keys($log));
    }


    /**
     * Fetch log options
     * Fetch the options specific to the client if specified, or the default chosen client.
     * @param       int     Log ID
     *
     * @SWG\Operation(
     *      partial="logs.options",
     *      summary="Fetch options for log create/edit",
     *      notes="User must be logged in",
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
    public function getOptions()
    {
        // TODO

        // Load: Log format from specific Client
        $options = array();

        // Validate: Do we NOT have permissions to see the Accountability types?
        if (false)
        {
            unset( $options['accountability_field'] );
            unset( $options['status_field'] );
        }


        // OK Response
        $this->set('options', $options);
        $this->set('_serialize', array_keys($options));
    }


    /**
     * Delete log by URL param "id"
     * @param   int     Log ID
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
    public function delete( $id )
    {
        // TODO
        // Validate: Does the user have permissiont to delete anything?
        if (false)
        {
            $this->errorUnauthorised(array('message'=>'You do not have permission to delete this log'));
        }

        // user must exist
        if (!$this->Log->findByHrid($id)) {
            $this->errorNotFound('Log could not be found');
        }

        // Simple delete save
        $log = array(
            'id'=>$id,
            'deleted'=>true,
        );

        // Delete
        $this->Log->save( array('Log'=>$log) );

        // OK Response
        $this->set('success', true);
        $this->set('_serialize', array('success'));
    }

}
