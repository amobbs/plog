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
                    controller: 'HomeCtrl',
                    templateUrl: 'modules/home/home.tpl.html'
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
                redirect: ['$q', 'userService', '$location', function($q, userService, $location) {

                    // If no user, auth will be executed. Otherwise we get the role.
                    var role = userService.getUser().role;

                    // Default path
                    var requestedPath = '/dashboard';

                    // Certain roles have certain destinations
                    switch( role ) {
                        case 'supervisor':
                            requestedPath = '/dashboards/to-be-released';
                            break;
                        case 'operator':
                            requestedPath = '/log';
                            break;
                    }

                    // Redirect
                    $location.path(requestedPath);

                    // Reject this state change as a matter of course.
                    var defer = $q.defer();
                    defer.reject();
                    return defer.promise;

                }]
            }
        });
    })


    /**
     * Home Controller
     */
    .controller( 'HomeCtrl', function HomeController( $scope ) {
        // Not a real thing
    })

;

