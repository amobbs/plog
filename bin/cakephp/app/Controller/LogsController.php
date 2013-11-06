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
    public $uses = array('Log', 'Client');

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
        // Get log data from request
        $log = $this->request->data['Log'];

        //  If we have an ID, we need to validate the original log first before it can be updated
        if ( !empty($id) )
        {
            // Try to fetch the log
            $sourceLog = $this->Log->findByHrid( $id );

            // Validate: does the log exist?
            if (empty($sourceLog))
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
            $log['_id'] = $sourceLog['Log']['_id'];
        }
        else
        {
            // Validate: Does this user have permission to create logs
            if ( !$this->isAuthorized('log-create') )
            {
                $this->errorUnauthorised(array('message'=>'You do not have permission to create new logs.'));
            }

            // Not loading an existing log. Ensure the ID is cleared
            unset($log['_id']);
        }

        // Validate field options
        $this->Log->set( $log );
        if ( !$this->Log->validates() )
        {
            $this->errorBadRequest( array('data'=>$this->Log->validationErrors, 'message'=>'Validation failed') );
        }

        // Save
        $ret = $this->Log->save( $log );

        // Notifications: Issue Email and SMS notifications relevant to this logs update.
        // TODO: Enable this when notifications are better
        //$this->LogNotification->issueNotifications( $log );

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
        // Fetch log from DB
        $log = $this->Log->findByHrid( $id );

        // Validate: does this log exist
        if (!$log)
        {
            $this->errorNotFound(array('message'=>'Log could not be found'));
        }

        // Validate: does the given user have access to this log? - Client IDs are a match if user has single-client
        if ($this->isAuthorized('single-client'))
        {
            if ($this->PreslogAuth->user('client_id') != $log['client_id'])
            {
                $this->errorUnauthorised(array('message'=>'You do not have permission to view this log'));
            }
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
    public function options( $id=null )
    {
        // IF ID given, try to load that given log
        if (!empty($id))
        {
            // Fetch log
            $log = $this->Log->findByHrid( $id );

            // Validate: Does log exist?
            if (!$log)
            {
                $this->errorNotFound(array('message'=>'Log could not be found'));
            }

            // Client ID is..
            $clientId = (string) $log['Log']['client_id'];
        }
        else
        {
            // Try to fetch the client_id from query string
            if (!$clientId = $this->request->query('client_id'))
            {
                $this->errorBadRequest(array('message'=>'A valid Client ID must be specified in query string parameter "client_id".'));
            }
        }

        // Validate: is this user able to see this clientId?
        if ($this->isAuthorized('single-client'))
        {
            // Check the user client_id matches this client_id
            if ($this->PreslogAuth->user('client_id') != $clientId)
            {
                $this->errorBadRequest(array('message'=>'Invalid Client ID, or Client is not accessible to this user.'));
            }
        }

        // Load: Log format from specific Client
        $options = $this->Log->getOptionsByClientId( $clientId );

        // Return options
        $this->set($options);
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
        // Validate: Does the user have permission to delete anything?
        if (!$this->isAuthorized('log-delete'))
        {
            $this->errorUnauthorised(array('message'=>'You do not have permission to delete logs'));
        }

        // user must exist
        if (!$log = $this->Log->findByHrid($id)) {
            $this->errorNotFound('Log could not be found');
        }

        // Validate: Log must be visible to this client
        if ($this->isAuthorized('single-client'))
        {
            if ($this->PreslogAuth->user('client_id') != $log['Log']['client_id'])
            {
                $this->errorUnauthorised(array('message'=>'You do not have permission to delete logs of other clients.'));
            }
        }

        // Simple delete save
        $log['Log']['deleted'] = true;

        // Delete
        $ret = $this->Log->save( $log );

        // OK Response
        $return = array('Success'=>$ret);
        $this->set($return);
        $this->set('_serialize', array_keys($return));    }

}
