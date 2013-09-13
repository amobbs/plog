/**
 * Authentication Module
 */

angular.module( 'Preslog.auth', [
        'titleService'
    ])

/**
 * Login Route
 */
    .config(function(stateHelperProvider) {
        stateHelperProvider.addState('publicLayout.login', {
            url: '/login',
            views: {
                "main@publicLayout": {
                    controller: 'AuthCtrl',
                    templateUrl: 'modules/auth/login.tpl.html'
                }
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
    .controller( 'AuthCtrl', function AuthController( $rootScope, $scope, titleService, userService ) {

        // Title
        titleService.setTitle( 'Login' );

        /**
         * Submit Form
         * @param user
         */
        $scope.submit = function (user) {

            // Fire user login
            userService.login(user).then(function(ret) {

                // OK?
                if ( ret.login.success ) {

                    console.log('ok!');

                    // Logged in!
                    $rootScope.$broadcast('event:auth-loginConfirmed', user);
                }
                else {

                    $scope.errors = { message: ret.login.message};

                    for (var i in ret.login.data)
                    {
                        $scope.errors[i] = ret.login.data[i][Object.keys(ret.login.data[i])[0]];
                    }

                }

            });




        };
    })

;

