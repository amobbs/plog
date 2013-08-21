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

        var requestedPath = '/';
        $rootScope.global.loggedIn = false;
        userService.getUser().then(function (user) {
            $rootScope.global.user = user;
            $rootScope.global.loggedIn = true;
        });

        $rootScope.$on('event:auth-loginRequired', function () {
            var path = $location.path();
            if ('/login' != path) {
                requestedPath = path;
            }
            $rootScope.global.loggedIn = false;
            $rootScope.global.user = {};
            $location.path('/login');
        });

        $rootScope.$on('event:auth-loginConfirmed', function (event, data) {
            $rootScope.global.user = data;
            $rootScope.global.loggedIn = true;
            $location.path(requestedPath);
        });

        $rootScope.global.logout = function () {
            $rootScope.global.user = {};
            $rootScope.global.loggedIn = false;
            Restangular.all('account').customGET('logoff').then(function() {
                $rootScope.$broadcast('event:auth-loggedOut');
                $location.path('/login');
            });
        };
    })


/**
 * Controller
 */
    .controller( 'AuthCtrl', function AuthController( $scope, titleService ) {
        console.log('auth');
        titleService.setTitle( 'Login' );
    })

;

