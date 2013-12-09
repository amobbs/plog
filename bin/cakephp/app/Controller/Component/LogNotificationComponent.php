<?php

App::import('Component', 'Component');

use Preslog\Logs\Entities\LogEntity;
use Preslog\Notifications\Types\TypeAbstract;


/**
 * Preslog Log Notificiation component
 * - Handles notification for logs
 *
 * @property    Client  $Client
 * @property    User    $User
 * @property    Controller $controller
 */

class LogNotificationComponent extends Component
{
    protected $controller;

    /**
     * Initialize Component
     * - Prepare required models
     *
     * @param Controller $controller
     */
    public function initialize(Controller $controller)
    {
        // Get Models
        $this->User = ClassRegistry::init('User');
        $this->Client = ClassRegistry::init('Client');

        $this->controller = &$controller;

        // Parent Init
        parent::initialize($controller);
    }


    /**
     * Issue notifications to users about $log
     * - Fetch list of users who might be interested in this log
     * - Process each notification type, and prep issuing of notifications by type.
     * - Send notifications
     * @param   array   $log    Log to notify about
     */
    public function issueNotifications( $log )
    {
        // Logs are issued by Attributes - bail if no attributes are supplied
        if (empty($log['attributes'])) {
            return;
        }

        // Ensure format of attributes
        $attributes = array();
        foreach ($log['attributes'] as &$attr) {
            $attributes[] = (string) $attr;
        }

        // Aggregate criteria to find users
        // Returns just ONE client notification structure (notifications.clients.client_id)
        $criteria = array(
            array('$unwind'=>'$notifications.clients'),
            array('$match'=>array(
                'notifications.clients.attributes'=>array('$in'=>$attributes),
                'notifications.clients.client_id'=>new MongoId( $log['client_id'] )),
            )
        );
        $users = $this->User->getMongoDb()->selectCollection('users')->aggregate($criteria);
        $users = $users['result'];

        // Build log data into Log Entity to make our lives easier
        $logEntity = new LogEntity;
        $logEntity->setDataSource($this->Client->getDataSource());
        $logEntity->setClientEntity($this->Client->getClientEntityById($log['client_id']));
        $logEntity->fromArray($log);

        // Find all notification types
        $notificationTypes = Configure::read('Preslog.Notifications');

        // Skim all notification types
        // - Ensure this notification type even applies to the log
        // - Skim the users and find who's interested in this kind of notification
        foreach ($notificationTypes as $notifyTypeKey=>&$notifyType)
        {
            /**
             * @var TypeAbstract $notifyType
             */

            // Apply log
            $notifyType->setLog( $logEntity );

            // Skip if this Notification Type doesn't fit the log
            if ( !$notifyType->checkCriteria() )
            {
                continue;
            }

            // Skim users and check if they're interested
            foreach ($users as $user)
            {
                // Include with notification if they're interested
                if ($this->isUserInterested( $user, $notifyTypeKey ))
                {
                    $notifyType->addRecipient( $user );
                }
            }
        }

        // Attach log and issue notifications where possible
        foreach ($notificationTypes as $notifyTypeKey=>&$notifyType)
        {
            // Notify types
            $this->sendSms( $notifyType );
            $this->sendEmail( $notifyType );
        }
    }


    /**
     * If this $user interested in $notifyTypeKey?
     * - Check the notifyTypeKey against the user's chosen types
     * @param   array   $user               User
     * @param   array   $notifyTypeKey      Notify Type Key
     * @return  bool                        True if they're interested
     */
    public function isUserInterested( $user, $notifyTypeKey )
    {
        // Does it pass?
        if (isset($user['notifications']['clients']['types']) &&
            isset($user['notifications']['clients']['types'][ $notifyTypeKey ]) &&
            $user['notifications']['clients']['types'][ $notifyTypeKey ] == true )
        {
            return true;
        }

        // Not interested
        return false;
    }


    /**
     * Issue SMS Notification from $notifyType
     * @param TypeAbstract  $notifyType
     */
    protected function sendSms( $notifyType )
    {
        // Must have method
        if (!$notifyType->hasMethod('sms'))
        {
            return;
        }

        // Must have users
        $users = $notifyType->getRecipients('sms');
        if (!sizeof($users))
        {
            return;
        }

        // notify settings
        $settings = $notifyType->getSettings('sms');

        // Construct SMS list
        $list = array();
        foreach ($users as $user)
        {
            // Skip users with no number, or invalid number
            if (!isset($user['phoneNumber']) || empty($user['phoneNumber']) || strlen($user['phoneNumber']) < 6)
            {
                continue;
            }

            // Strip all chars except numbers
            $number = preg_replace("/[^0-9.]*/", "", (string) $user['phoneNumber']);
            $list[] = urlencode( $number );
        }

        // Use debug email if in debug mode
        if (Configure::read('debug') > 0)
        {
            $list = array(Configure::read('Preslog.Debug.sms'));
        }

        // Get view
        $view = new View;
        $view->set( $notifyType->getSmsTemplateData() );
        $message = $view->render('/Sms/Notifications/'.$settings['template'], 'ajax');

        // Construct API
        $smsService = Configure::read('Preslog.SmsService');
        $url = sprintf($smsService['address'],
            urlencode($smsService['username']),
            urlencode($smsService['password']),
            urlencode($smsService['from']));

        // Append number and message
        $url .= "&mobilenumber=%s&message=%s";
        $url = sprintf($url,
            implode(',', $list),
            urlencode($message)
        );

        // Send SMS
        file_get_contents($url);
    }


    /**
     * Issue Email Notification from $notifyType
     * @param TypeAbstract  $notifyType
     */
    protected function sendEmail( $notifyType )
    {
        // Must have method
        if (!$notifyType->hasMethod('email'))
        {
            return;
        }

        // Must have users
        $users = $notifyType->getRecipients('email');
        if (!sizeof($users))
        {
            return;
        }

        // notify settings
        $settings = $notifyType->getSettings('email');

        // Construct "to" list
        $list = array();
        foreach ($users as $user)
        {
            $list[ $user['email'] ] = "{$user['firstName']} {$user['lastName']}";
        }

        // Use debug email if in debug mode
        if (Configure::read('debug') > 0)
        {
            $list = array(Configure::read('Preslog.Debug.email'));
        }

        // Email object
        $email = new CakeEmail('instant_notification');
        $email->helpers( array('Html') );
        $email->template( 'Notifications/'.$settings['template'] );

        // Subject
        $data = $notifyType->getEmailTemplateData();

        // From (IncRpt_CLIENT)
        $from = $email->from();
        $fromEmail = current(array_keys($from));
        $fromName = current($from).$data['clientShortName'];

        // Author email to the user for their reset
        $email->from( $fromEmail, $fromName );

        // Arrange BCC/TO list
        foreach ($list as $toEmail=>$toName)
        {
            // Bad emails throw exceptions!
            try
            {
                $email->bcc( $toEmail, $toName );
            }
            catch (Exception $e)
            {
                // Do nothing with bad emails
            }
        }


        // Compose
        $email->subject( $data['subject'] );
        $email->viewVars( $data );

        // Send the email
        $email->send();
    }

}
