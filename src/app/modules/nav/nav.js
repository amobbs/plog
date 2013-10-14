/**
 * Nav Controller
 * Provides functionality for the Nav bar.
 */
angular.module( 'Preslog.nav', [])

    .config(function(stateHelperProvider) {

    })


/**
 * Nav Controller
 */
    .controller( 'NavCtrl', function NavController( $q, $scope, userService ) {

        /**
         * Init
         */

        // Fetch user
        userService.getUser().then(function(user)
        {
            $scope.user = user;
        });

        // Fetch current client
        userService.getClient().then(function(client)
        {
            $scope.client = client;
        });

        // Fetch current client list
        userService.getClients().then(function(clients)
        {
            $scope.clients = clients;
        });

        //
        userService.getDashboards().then(function(dashboards){
            $scope.presets = dashboards.preset;
            $scope.favourites = dashboards.favourites;
        });


        /**
         * Switch the currently selected client
         * @param   client_id
         */
        $scope.switchClient = function( client_id )
        {
            // Change the selected client in system
            userService.setClient( client_id );

            // Update client
            userService.getClient().then(function(client)
            {
                $scope.client = client;
            });

        };


        // Observe the User model
        // Change the user properties when they're modified on the user object
        $scope.$watch(function()
        {
            return userService.readUser();

        }, function(data)
        {
            $scope.user = data;
        }, true);

    })

;

