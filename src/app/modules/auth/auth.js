/**
 * Authentication Module
 */

angular.module( 'Preslog.auth', [
        'titleService'
    ])

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
        $rootScope.global.loggedIn = false;
        userService.getUser().then(function (user) {
            $rootScope.global.user = user;
            $rootScope.global.loggedIn = true;
        });

        // On LoginRequired event; logout the user and go to /login
        $rootScope.$on('event:auth-loginRequired', function () {
            var path = $location.path();
            if ('/login' != path && '/' != path) {
                requestedPath = path;
            }
            $rootScope.global.loggedIn = false;
            $rootScope.global.user = {};
            $location.path('/login');
        });

        // On LoginConfirmed event; set the user, go back to requested path
        $rootScope.$on('event:auth-loginConfirmed', function (event, data) {
            $rootScope.global.user = data;
            $rootScope.global.loggedIn = true;

            // Send to requested page, otherwise go to Homepage which will redirect from there
            if (requestedPath === null) {
                requestedPath = '/';
            }

            // Redirect
            $location.path(requestedPath);
        });

        // Logout: Logout the user and broadcast this event (LoggedOut).
        $rootScope.global.logout = function () {
            $rootScope.global.user = {};
            $rootScope.global.loggedIn = false;
            userService.logout().then(function() {
                $rootScope.$broadcast('event:auth-loggedOut');
                $location.path('/login');
            });
        };
    })


/**
 * Controller
 */
    .controller( 'AuthLoginCtrl', function AuthLoginController( $rootScope, $scope, $location, titleService, userService ) {

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
                $rootScope.$broadcast('event:auth-loginConfirmed', user);

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
    })
;

