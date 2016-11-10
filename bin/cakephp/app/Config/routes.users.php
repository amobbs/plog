<?php

/**
 * Users Routes
 */

use Swagger\Annotations as SWG;


/**
 * TODO: DELETE ME
 * Debug task route
 */

Router::connect(
    '/users/task',
    array('controller' => 'Users', 'action' => 'debugTask', '[method]' => 'GET')
);

/**
 * @SWG\Resource(
 *      resourcePath="/users",
 *      @SWG\Api(
 *          path="/users/login",
 *          @SWG\Operation(
 *              @SWG\Partial("users.login"),
 *              nickname="users.login",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/users/login',
    array('controller' => 'Users', 'action' => 'login', '[method]' => 'POST')
);


/**
 * @SWG\Resource(
 *      resourcePath="/users",
 *      @SWG\Api(
 *          path="/users/logout",
 *          @SWG\Operation(
 *              @SWG\Partial("users.logout"),
 *              nickname="users.logout",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/users/logout',
    array('controller' => 'Users', 'action' => 'logout', '[method]' => 'POST')
);


/**
 * Read My-Profile Options
 * @SWG\Resource(
 *      resourcePath="/users",
 *      @SWG\Api(
 *          path="/users/my-profile",
 *          @SWG\Operation(
 *              @SWG\Partial("users.my-profile.options"),
 *              nickname="users.my-profile.options",
 *              method="OPTIONS"
 *          )
 *      )
 * )
 */
Router::connect(
    '/users/my-profile',
    array('controller' => 'Users', 'action' => 'myProfileOptions', '[method]' => 'OPTIONS')
);

/**
 * Read My-Profile
 * @SWG\Resource(
 *      resourcePath="/users",
 *      @SWG\Api(
 *          path="/users/my-profile",
 *          @SWG\Operation(
 *              @SWG\Partial("users.my-profile.read"),
 *              nickname="users.my-profile.read",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/users/my-profile',
    array('controller' => 'Users', 'action' => 'myProfileRead', '[method]' => 'GET')
);


/**
 * Write My-Profile
 * @SWG\Resource(
 *      resourcePath="/users",
 *      @SWG\Api(
 *          path="/users/my-profile",
 *          @SWG\Operation(
 *              @SWG\Partial("users.my-profile.update"),
 *              nickname="users.my-profile.update",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/users/my-profile',
    array('controller' => 'Users', 'action' => 'myProfileUpdate', '[method]' => 'POST')
);


/**
 * Read My-Notifications Options
 * @SWG\Resource(
 *      resourcePath="/users",
 *      @SWG\Api(
 *          path="/users/my-notifications",
 *          @SWG\Operation(
 *              @SWG\Partial("users.my-notifications.options"),
 *              nickname="users.my-notifications.options",
 *              method="OPTIONS"
 *          )
 *      )
 * )
 */
Router::connect('/users/my-notifications',
    array('controller' => 'Users', 'action' => 'myNotificationsOptions', '[method]' => 'OPTIONS')
);

/**
 * Read My-Notifications
 * @SWG\Resource(
 *      resourcePath="/users",
 *      @SWG\Api(
 *          path="/users/my-notifications",
 *          @SWG\Operation(
 *              @SWG\Partial("users.my-notifications.read"),
 *              nickname="users.my-notifications.read",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect('/users/my-notifications',
    array('controller' => 'Users', 'action' => 'myNotificationsRead', '[method]' => 'GET')
);

Router::connect(
    '/admin/clients/csv',
    array('controller' => 'Users', 'action' => 'adminListCSV', '[method]' => 'GET')
);


/**
 * Write My-Notifications
 * @SWG\Resource(
 *      resourcePath="/users",
 *      @SWG\Api(
 *          path="/users/my-notifications",
 *          @SWG\Operation(
 *              @SWG\Partial("users.my-notifications.update"),
 *              nickname="users.my-notifications.update",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/users/my-notifications',
    array('controller' => 'Users', 'action' => 'myNotificationsEdit', '[method]' => 'POST')
);


/**
 * Admin.Users.Read: GET (read list of users)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/users",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.users.read"),
 *              nickname="admin.users.read",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/admin/users',
    array('controller' => 'Users', 'action' => 'adminList', '[method]' => 'GET')
);


/**
 * Admin.Users.Options: OPTIONS (options for users)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/users/{user_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.users.options"),
 *              nickname="admin.users.options",
 *              method="OPTIONS"
 *          )
 *      )
 * )
 */
Router::connect(
    '/admin/users/:user_id',
    array('controller' => 'Users', 'action' => 'adminEditOptions', '[method]' => 'OPTIONS'),
    array('pass'=>array('user_id'), 'user_id'=>'[0-9a-z]*')
);
Router::connect(
    '/admin/users',
    array('controller' => 'Users', 'action' => 'adminEditOptions', '[method]' => 'OPTIONS')
);

/**
 * Admin.Users.Create: POST (create a user)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/users",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.users.create"),
 *              nickname="admin.users.create",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/admin/users',
    array('controller' => 'Users', 'action' => 'adminEdit', '[method]' => 'POST')
);


/**
 * Admin.Users.Specific.Read: GET (read a specific user)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/users/{user_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.users.specific.read"),
 *              nickname="admin.users.specific.read",
 *              method="GET"
 *          )
 *      )
 * )
 */
Router::connect(
    '/admin/users/:user_id',
    array('controller' => 'Users', 'action' => 'adminRead', '[method]' => 'GET'),
    array('pass'=>array('user_id'), 'user_id'=>'[0-9a-z]+')
);


/**
 * Admin.Users.Specific.Update: POST (update a specific user)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/users/{user_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.users.specific.update"),
 *              nickname="admin.users.specific.update",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/admin/users/:user_id',
    array('controller' => 'Users', 'action' => 'adminEdit', '[method]' => 'POST'),
    array('pass'=>array('user_id'), 'user_id'=>'[0-9a-z]+')
);


/**
 * Admin.Users.Specific.Delete: DELETE (delete a specific user)
 * @SWG\Resource(
 *      resourcePath="/admin",
 *      @SWG\Api(
 *          path="/admin/users/{user_id}",
 *          @SWG\Operation(
 *              @SWG\Partial("admin.users.specific.delete"),
 *              nickname="admin.users.specific.delete",
 *              method="DELETE"
 *          )
 *      )
 * )
 */
Router::connect(
    '/admin/users/:user_id',
    array('controller' => 'Users', 'action' => 'adminDelete', '[method]' => 'DELETE'),
    array('pass'=>array('user_id'), 'user_id'=>'[0-9a-z]+')
);


/**
 * Users.ResetPassword.Email: POST (Request a password reset by email)
 * @SWG\Resource(
 *      resourcePath="/users",
 *      @SWG\Api(
 *          path="/users/reset-password/email",
 *          @SWG\Operation(
 *              @SWG\Partial("users.reset-password.email"),
 *              nickname="users.reset-password.email",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/users/reset-password/email',
    array('controller' => 'Users', 'action' => 'resetPasswordEmail', '[method]' => 'POST')
);


/**
 * Users.ResetPassword: POST (Request a password reset)
 * @SWG\Resource(
 *      resourcePath="/users",
 *      @SWG\Api(
 *          path="/users/reset-password",
 *          @SWG\Operation(
 *              @SWG\Partial("users.reset-password"),
 *              nickname="users.reset-password",
 *              method="POST"
 *          )
 *      )
 * )
 */
Router::connect(
    '/users/reset-password',
    array('controller' => 'Users', 'action' => 'resetPassword', '[method]' => 'POST')
);