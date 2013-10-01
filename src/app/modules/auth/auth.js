/**
 * Authentication Module
 */

angular.module( 'Preslog.auth', [
        'titleService',
        'ui.bootstrap'
    ])

    /**
     * Setup
     */
    .config(function(stateHelperProvider) {


        /**
         * Login Route
         */
        stateHelperProvider.addState('publicLayout.login', {
            url: '/login',
            views: {
                "main@publicLayout": {
                    controller: 'AuthLoginCtrl',
                    templateUrl: 'modules/auth/login.tpl.html'
                }
            },
            resolve: {
                loggedOut: ['$q', '$location', 'userService', function($q, $location, userService) {
                    var defer = $q.defer();

                    // If the user IS logged in, reject this action and redirect
                    userService.login().then(function()
                    {
                        defer.reject();
                        $location.path('/');
                    }, function()
                    {
                        // Assuming the user isn't logged in, resolve as OK.
                        defer.resolve();
                    });

                    return defer.promise;
                }]
            }
        });


        /**
         * Logout Route+Function
         * Never resolves to a view, simply logs out the user and sends them to the homepage.s
         */
        stateHelperProvider.addState('mainLayout.logout', {
            url: '/logout',
            resolve: {
                loggedOut: ['$q', '$location', 'userService', function($q, $location, userService) {
                    userService.logout().then(function()
                    {
                        $location.path('/');
                    });
                }]
            }
        });
    })


    /**
     * Executed at startup
     * - Checks users authentication
     */
    .run(function ($rootScope, $location, Restangular, userService) {
        if (typeof $rootScope.global === 'undefined') {
            $rootScope.global = {};
        }

        var requestedPath = null;

        // Will succeed if the users if logged in, otherwise will instigate the login process.
        userService.getUser().then(function (user) {
        });

        // On LoginRequired event; logout the user and go to /login
        $rootScope.$on('event:auth-loginRequired', function () {
            var path = $location.path();
            if ('/login' != path && '/' != path) {
                requestedPath = path;
            }

            // Logout this user properly
            userService.logout();

            // Redirect to login form
            $location.path('/login');
        });

        // On LoginConfirmed event; set the user, go back to requested path
        $rootScope.$on('event:auth-loginConfirmed', function (event, data) {

            // Send to requested page, otherwise go to Homepage which will redirect from there
            if (requestedPath === null) {
                requestedPath = '/';
            }

            // Redirect
            $location.path(requestedPath);
        });

        // Logout: Logout the user and broadcast this event (LoggedOut).
        $rootScope.global.logout = function () {
            userService.logout().then(function() {
                $rootScope.$broadcast('event:auth-loggedOut');
                $location.path('/login');
            });
        };
    })


    /**
     * Controller
     */
    .controller( 'AuthLoginCtrl', function AuthLoginController( $rootScope, $scope, $location, titleService, userService, $modal ) {

        // Title
        titleService.setTitle( 'Login' );

        /**
         * Submit Form
         * @param user
         */
        $scope.submit = function (user) {

            // Fire user login
            userService.login(user).then(function(ret) {

                // Successful login
                $rootScope.$broadcast('event:auth-loginConfirmed', ret.user);

            }, function(ret) {

                // Failed login
                $scope.errors = { message: ret.login.message};

                // Write error messages to fields
                for (var i in ret.login.data)
                {
                    $scope.errors[i] = ret.login.data[i][Object.keys(ret.login.data[i])[0]];
                }

            });
        };


        /**
         * Forgotten Password
         */
        $scope.forgottenPassword = function()
        {
            // Open the Modal
            var modal = $modal.open({
                templateUrl: 'modules/auth/forgotten-password.tpl.html',
                controller: 'AuthLoginForgottenPasswordCtrl'
            });
        };

    })


    /**
     *
     */
    .controller( 'AuthLoginForgottenPasswordCtrl', function AuthLoginController( $rootScope, $scope, $location, userService, $modalInstance ) {

        /**
         * Ok
         */
        $scope.ok = function()
        {
            console.log($scope.email);
            $modalInstance.dismiss();
        };


        /**
         * Cancel
         */
        $scope.cancel = function()
        {
            $modalInstance.dismiss();
        };

    })
;

