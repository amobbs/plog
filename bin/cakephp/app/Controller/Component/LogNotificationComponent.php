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
    protected $log;
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

        $this->logger = Logger::getLogger(__CLASS__);

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
                'notifications.clients.client_id'=>new MongoId( $log['client_id'] ),
                '$or'=>array(
                    array('notifications.clients.methods.email'=>true),
                    array('notifications.clients.methods.sms'=>true),
                ),
            )),
        );
        $users = $this->User->getMongoDb()->selectCollection('users')->aggregate($criteria);

        // Abort on error
        if (!isset($users['result']))
        {
            return;
        }

        // Continue to be useful
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

            // Skim users and check if they're interested in this notification
            foreach ($users as &$user)
            {
                // Include with notification if they're interested
                if ($this->isUserInterested( $user, $notifyTypeKey ))
                {
                    // If notification not set, or existing notification is of lower priority
                    if (!isset($user['notify']) || $user['notify']->getPriority() > $notifyType->getPriority())
                    {
                        // Link the user to the notification
                        // Users can only possess one notification
                        $user['notify'] = &$notifyType;
                    }
                }
            }
        }

        // Now users have one notification attached, for those who are interested and it applied
        // Skim through the users and attach the user to that notification.
        foreach ($users as &$user)
        {
            // Must have notification
            if (!isset($user['notify']))
            {
                continue;
            }

            // Correct the user object
            $notify = $user['notify'];
            unset($user['notify']);

            // Add user as recipient.
            $notify->addRecipient($user);
        }


        // Issue all notifications.
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
            $user['notifications']['clients']['types'][ $notifyTypeKey ] == true &&
            $user['deleted'] !== true
        )
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
        $users = $notifyType->getRecipients();
        $this->logger->info('initial sms user count: ' . sizeOf($users));
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
            // If user doesn't care for SMS
            if (!isset($user['notifications']['clients']['methods']['sms'])
                || $user['notifications']['clients']['methods']['sms'] == false)
            {
                continue;
            }

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
            $this->logger->warn('debug mode is on: throw out user list and send to [' . Configure::read('Preslog.Debug.sms') . ']');
            $list = array(Configure::read('Preslog.Debug.sms'));
        }

        // Abort if no interested parties
        if (!sizeof($list))
        {
            $this->logger->info('SMS is not sending since no users have these notifications selected.');
            return;
        }

        $this->logger->info('actual sms user count: ' . sizeOf($list));

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

        $this->logger->info('Sending SMS request sent: ' . $url);
        // Send SMS

        $ch = curl_init($url);

        $response = curl_exec($ch);
        $failed = curl_errno($ch) != false;
        $info = curl_getinfo($ch);
        curl_close($ch);

        // Response should contain an 'OK'
        if (strpos($response, 'OK') === false) {
            $failed = true;
        }

        // In the event it fails we send an email notification
        if ($failed) {
            $this->logger->error('SMS request failed to send.' . PHP_EOL . 'Response: ' . json_encode($info));

            $content = $message;
            $Email = new CakeEmail();
            $Email->config('development')
                ->subject('\'SMS Has failed to send\' ')
                ->template('default')
                ->viewVars(compact('content'))
                ->emailFormat('html')
                ->to(array(
                    'letigre@4mation.com.au',
                    'derek.curtis@mediahub.tv'
                ))
                ->send();

            return false;
        }

        $this->logger->info('SMS request sent. ' . PHP_EOL . 'Response: ' . json_encode($response));
        return true;
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
        $users = $notifyType->getRecipients();
        if (!sizeof($users))
        {
            return;
        }
        $this->logger->info('initial email user count: ' . sizeOf($users));

        // notify settings
        $settings = $notifyType->getSettings('email');

        // Construct "to" list
        $list = array();
        foreach ($users as $user)
        {
            // If user doesn't care for Emails
            if (!isset($user['notifications']['clients']['methods']['email'])
                || $user['notifications']['clients']['methods']['email'] == false)
            {
                continue;
            }

            // Add to recipient list
            $list[ $user['email'] ] = "{$user['firstName']} {$user['lastName']} <{$user['email']}>";
        }

        $this->logger->info('email list: ' . implode(',', $list));

        // Use debug email if in debug mode
        if (Configure::read('debug') > 0)
        {
            $this->logger->warn('Debug mode is on, throw out list and sending to Debug user');
            $list = array(Configure::read('Preslog.Debug.email')=>'Debug User');
        }

        // Abort if no interested parties
        if (!sizeof($list))
        {
            return;
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
                $email->addBcc( $toEmail, $toName );
            }
            catch (Exception $e)
            {
                $this->logger->error("Email [$toName <$toEmail>] is invalid", $e);
            }
        }


        // Compose
        $email->subject( $data['subject'] );
        $email->viewVars( $data );

        // Send the email
        $email->send();

        $this->logger->info('email with subject: [' . $data['subject'] . '] sent from [' . $fromEmail . '] sent to [' . implode(',', $list) . ']');
        return;
    }

}
