/**
 * Each section of the site has its own module. It probably also has
 * submodules, though this boilerplate is too simple to demonstrate it. Within
 * `src/app/home`, however, could exist several additional folders representing
 * additional modules that would then be listed as dependencies of this one.
 * For example, a `note` section could have the submodules `note.create`,
 * `note.delete`, `note.edit`, etc.
 *
 * Regardless, so long as dependencies are managed correctly, the build process
 * will automatically take take of the rest.
 *
 * The dependencies block here is also where component dependencies should be
 * specified, as shown below.
 */
angular.module( 'Preslog.home', [])

    .config(function(stateHelperProvider) {
        stateHelperProvider.addState('mainLayout.home', {
            url: '/',
            views: {
                "main@mainLayout": {
                    controller: 'HomeCtrl',
                    templateUrl: 'modules/home/home.tpl.html'
                }
            }
        });
    })


    /**
     * Home Controller
     */
    .controller( 'HomeCtrl', function HomeController( $scope, userService, $location ) {

        // Load path depending on route if none established
        // If no user, auth will be executed.
        var role = userService.getUser().role;

        // Direct to the appropriate role
        switch( role ) {
            case 'supervisor':
                requestedPath = '/dashboards/to-be-released';
                break;
            case 'operator':
                requestedPath = '/log';
                break;
            default:
                requestedPath = '/dashboard';
        }

        // Redirect
        $location(requestedPath);

    })

;

