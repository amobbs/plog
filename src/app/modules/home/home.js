/**
 * Home Module
 * Provides a simple redirect on "/" to preset target locations.
 */
angular.module( 'Preslog.home', [])

    .config(function(stateHelperProvider) {

        /**
         * Home State
         */
        stateHelperProvider.addState('mainLayout.home', {
            url: '/',
            views: {
                "main@mainLayout": {
                    controller: 'HomeCtrl'
                }
            },

            /**
             * Resolve prior to state load
             */
            resolve: {

                // User must have permissions to access this resource
                permissions: ['$q', 'userService', function($q, userService) {
                    var defer = $q.defer();

                    userService.checkAccessPermission('user').then(function()
                    {
                        defer.resolve();
                    }, function()
                    {
                        defer.reject();
                    });

                    return defer.promise;
                }],

                // Force a redirect. This isn't an actual page, just a redirect.
                redirect: ['$q', 'userService', '$location', 'dashboard_live_logs', 'dashboard_unqualified', function($q, userService, dashboard_live_logs, dashboard_unqualified) {
                    var defer = $q.defer();

                    // If no user, auth will be executed. Otherwise we get the role.
                    userService.getUser().then(function(user)
                    {
                        // Default path
                        var requestedPath = '/dashboard';

                        // Certain roles have certain destinations
                        switch( user.role ) {
                            case 'engineer':
                                requestedPath = '/dashboard/' + dashboard_live_logs;
                                break;
                            case 'supervisor':
                                requestedPath = '/dashboard/' + dashboard_unqualified;
                                break;
                            case 'operator':
                                requestedPath = '/logs/';
                                break;
                        }

                        // Resolve to close this request
                        defer.resolve( requestedPath );
                    });

                    // Reject this state change as a matter of course, which will cause a halt.
                    return defer.promise;
                }]
            }
        });
    })


    /**
     * Home Controller
     */
    .controller( 'HomeCtrl', function HomeController( $scope, $location, redirect ) {

        // Redirect
        $location.path(redirect);

    })

;

