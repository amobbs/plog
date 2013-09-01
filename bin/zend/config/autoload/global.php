<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(


    /**
     * Service override
     * - Override the User module
     */
    'service_manager' => array(
        'invokables' => array(
            'UserService' => 'User\Service\User',
            'User\Form\LostPassword' => 'User\Form\LostPassword',
            'User\Form\LostPasswordFilter' => 'User\Form\LostPasswordFilter',
            'User\Form\ResetPassword' => 'User\Form\resetPassword',
            'User\Form\ResetPasswordFilter' => 'User\Form\ResetPasswordFilter',
            'User\Entity\User' => 'Preslog\Entity\User',
        ),
        'factories' => array(
            'lost_password_form' => 'User\Service\LostPasswordFormServiceFactory',
            'reset_password_form' => 'User\Service\ResetPasswordFormServiceFactory',
            'user_role' => function($sm) { return 'guest'; },        // User role cannot be set until Routes execution
            'zfcuser_user_mapper' => function ($sm) {
                return $sm->get('Preslog\Service\User')->getMapper();
            },
        ),
    ),
);
