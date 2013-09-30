<?php

App::import('Component', 'Component');

/**
 * Preslog Log Notificiation component
 * - Handles notification for logs
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

        // Parent Init
        parent::initialize($controller);
    }


    /**
     * Issues notifications
     * @param $log
     * @return bool
     */
    public function issueNotifications( $log )
    {
        // Fetch notification configuration
        $notificationTypeList = Configure::read('Preslog.Notifications');

        // Skim all types that might apply, and discard those that don't
        $keys = array();
        foreach ($notificationTypeList as $k=>$type)
        {
            // Discard this type if it fails to apply
            if ( !$type->checkCriteria( $log ) )
            {
                unset($notificationTypeList[$k]);
            }

            // Fetch the key that would be used on the user
            $keys[] = $type->getKey();
        }

        // Nothing to send?
        if (!sizeof($notificationTypeList))
        {
            return 0;
        }

        // Skim all types and the selected attributes for this Log, and get all the users who care.
        // TODO
        $users = $this->User->findUserByNotifications($keys, $log['attributes']);

        // Nobody interested?
        if (!sizeof($users))
        {
            return 0;
        }

        // Collate user details into notification addresses
        foreach ($users as $user)
        {
            // Does the user want email notifications?
            if (false)
            {
                $list['email'][]    = "{$user['firstName']} {$user['lastName']} <{$user['email']}>";
            }

            // Does the user want SMS notifications?
            if (false)
            {
                $list['sms'][]      = urlencode( (int) $user['phoneNumber'] );
            }
        }

        // Attempt to send notifications
        foreach ($notificationTypeList as $type)
        {
            // Send batch SMS
            if ($type->hasMethod('sms'))
            {
                // Fetch SMS settings
                $settings = $type->getSettings();

                // Construct message
                // TODO
                $message = 'i am a teapot : '.$settings['template'];

                // Construct API
                $smsService = Configure::read('Preslog.SmsService');
                $url = sprintf($smsService['address'],
                    urlencode($smsService['username']),
                    urlencode($smsService['password']),
                    urlencode($smsService['from']));

                // Append number and message
                $url .= "&mobilenumber=%s&message=%s";
                $url = sprintf($url,
                    implode(',', $list['sms']),
                    urlencode($message));

                // Send
                file_get_contents($url);
            }

            // Send batch Email
            if ($type->hasMethod('email'))
            {
                // Fetch email settings
                $settings = $type->getSettings();

                // Construct email details
                // TODO
                $headers = 'bcc: someones@somewhere.com';
                $message = 'i am a teapot : '.$settings['template'];
                $subject = sprintf($settings['subject'], 'subj');

                // Send Email
                mail('', $subject, $message, $headers);
            }
        }

        return true;
    }

}
