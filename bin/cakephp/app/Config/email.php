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

    // Development Config
    public $development = array(
        'transport' => 'Smtp',
        'from' => array('preslog@mediahub.tv' => 'Mediahub Preslog'),
        'host' => 'ssl://smtp.gmail.com',
        'port' => 465,
        'timeout' => 30,
        'username' => 'dave@4mation.com.au',
        'password' => 'poiu1qaz',
        'log' => false,
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
        'emailFormat'=>'both',
    );

    // Production Config
    public $default = array();


    /**
     * Assign production or development
     */
    public function __construct()
    {
        if ('development' == APPLICATION_ENV)
        {
            $this->default = $this->development;
        }
    }
}
