<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

use Swagger\Annotations as SWG;

return array(
    'router' => array(
        'routes' => array(

            'zfcuser' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/users',
                ),
                'child_routes' => array(

                    /**
                     * @SWG\Resource(
                     *      resourcePath="/users",
                     *      @SWG\Api(
                     *          path="/users/login",
                     *          @SWG\Operation(
                     *              @SWG\Partial("users.login"),
                     *              nickname="users.login",
                     *              httpMethod="POST"
                     *          )
                     *      )
                     * )
                     */
                    'login' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/login',
                            'defaults' => array(
                                'controller' => 'user',
                                'action'     => 'login',
                            ),
                        ),
                    ),

                    /**
                     * @SWG\Resource(
                     *      resourcePath="/users",
                     *      @SWG\Api(
                     *          path="/users/logout",
                     *          @SWG\Operation(
                     *              @SWG\Partial("users.logout"),
                     *              nickname="users.logout",
                     *              httpMethod="POST"
                     *          )
                     *      )
                     * )
                     */
                    'logout' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/logout',
                            'child_routes' => array(
                                'post'=>array(
                                    'may_terminate'=>true,
                                    'verb'=>'post',
                                    'defaults' => array(
                                        'controller' => 'user',
                                        'action'     => 'logout',
                                    ),
                                ),
                            ),
                        ),
                    ),


                    /**************************************************************
                     * OLD SYSTEM ROUTES: WARNING
                     * These should be deleted before release.
                     */

                    'register' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/register',
                            'defaults' => array(
                                'controller' => 'user',
                                'action'     => 'register',
                            ),
                        ),
                    ),
                    'lost-password' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/lost-password',
                            'defaults' => array(
                                'controller' => 'user',
                                'action'     => 'lostPassword',
                            ),
                        ),
                    ),
                    'reset-password' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/reset-password[/:reset_id][/]',
                            'defaults' => array(
                                'controller' => 'user',
                                'action'     => 'resetPassword',
                            ),
                        ),
                    ),
                    'invalid' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/invalid[/]',
                            'defaults' => array(
                                'controller' => 'user',
                                'action'     => 'invalid',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'user' => 'User\Controller\UserController',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'params' => 'Preslog\Controller\Plugin\Params',
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'template_map' => array(
            'user/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'email/reset'           => __DIR__ . '/../view/email/reset.phtml',
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'UserService' => 'User\Service\User',
            'User\Form\LostPassword' => 'User\Form\LostPassword',
            'User\Form\LostPasswordFilter' => 'User\Form\LostPasswordFilter',
            'User\Form\ResetPassword' => 'User\Form\resetPassword',
            'User\Form\ResetPasswordFilter' => 'User\Form\ResetPasswordFilter',
            'User\Entity\User' => 'User\Entity\User',
        ),
        'factories' => array(
            'lost_password_form' => 'User\Service\LostPasswordFormServiceFactory',
            'reset_password_form' => 'User\Service\ResetPasswordFormServiceFactory',
            'user_role' => function ($sm) {
                if ($sm->get('zfcuser_auth_service')->hasIdentity()) {
                    return $sm->get('zfcuser_auth_service')->getIdentity()->getRoles();
                } else {
                    return 'guest';
                }
            },
            'zfcuser_user_mapper' => function ($sm) {
                $mapper = new User\Mapper\User();
                $mapper->setConfig($sm->get('config'));

                $mapper->setEntityPrototype(new User\Entity\User);
                $mapper->setHydrator(new \MongoUser\Mapper\UserHydrator(false));

                return $mapper;
            },
        ),
    ),
);
