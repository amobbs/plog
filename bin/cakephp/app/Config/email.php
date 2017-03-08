<?php
/**
 * This is email configuration file.
 *
 * Use it to configure email transports of Cake.
 *
 * PHP 5
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 2.0.0
 */

/**
 * Email configuration class.
 * You can specify multiple configurations for production, development and testing.
 *
 * transport => The name of a supported transport; valid options are as follows:
 *		Mail		- Send using PHP mail function
 *		Smtp		- Send using SMTP
 *		Debug		- Do not send the email, just return the result
 *
 * You can add custom transports (or override existing transports) by adding the
 * appropriate file to app/Network/Email. Transports should be named 'YourTransport.php',
 * where 'Your' is the name of the transport.
 *
 * from =>
 * The origin email. See CakeEmail::from() about the valid values
 *
 */
class EmailConfig {

    // Development Config: Standard emails
    public $development = array(
        'transport' => 'Smtp',
        'from' => array('preslog@mediahub.tv' => 'Mediahub Preslog'),
        'host' => '',
        'port' => 465,
        'timeout' => 30,
        'username' => '',
        'password' => '',
        'log' => false,
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
        'emailFormat'=>'both',
    );

    // Development Config: Instant Notifications
    public $development_in = array(
        'transport' => 'Smtp',
        'from' => array('preslog@mediahub.tv' => 'IncRpt_'),
        'host' => 'ssl://smtp.gmail.com',
        'port' => 465,
        'timeout' => 30,
        'username' => '@4mation.com.au',
        'password' => '',
        'log' => false,
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
        'emailFormat'=>'both',
    );


    // Staging Config: Standard emails
    public $staging = array(
        'transport' => 'Smtp',
        'from' => array('preslog-noreply@mediahub.tv' => 'Mediahub Preslog'),
        'host' => '192.168.0.2',
        'port' => 25,
        'timeout' => 30,
        'username' => 'preslog',
        'password' => 'mediahub',
        'log' => false,
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
        'emailFormat'=>'both',
    );

    // Staging Config: Instant Notifications
    public $staging_notification = array(
        'transport' => 'Smtp',
        'from' => array('preslog-noreply@mediahub.tv' => 'IncRpt_'),
        'host' => '192.168.0.2',
        'port' => 25,
        'timeout' => 30,
        'username' => 'preslog',
        'password' => 'mediahub',
        'log' => false,
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
        'emailFormat'=>'both',
    );

    // Production Config: Standard emails
    public $default = array(
        'transport' => 'Smtp',
        'from' => array('preslog-noreply@mediahub.tv' => 'Mediahub Preslog'),
        'host' => '192.168.0.2',
        'port' => 25,
        'timeout' => 30,
        'username' => 'preslog',
        'password' => 'mediahub',
        'log' => false,
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
        'emailFormat'=>'both',
    );

    // Production Config: Instant Notifications
    public $instant_notification = array(
        'transport' => 'Smtp',
        'from' => array('preslog-noreply@mediahub.tv' => 'IncRpt_'),
        'host' => '192.168.0.2',
        'port' => 25,
        'timeout' => 30,
        'username' => 'preslog',
        'password' => 'mediahub',
        'log' => false,
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
        'emailFormat'=>'both',
    );

    /**
     * Assign production or development
     */
    public function __construct()
    {
        // Dev
        if ('development' == APPLICATION_ENV)
        {
            $this->default = $this->development;
            $this->instant_notification = $this->development_in;
        }

//         Staging
        if ('staging' == APPLICATION_ENV)
        {
            // Take defaults
        }
    }
}
