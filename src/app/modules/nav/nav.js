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
    .controller( 'NavCtrl', function NavController( $scope, userService ) {

        /**
         * Init
         */

        // Set the currently selected client
        currentClient = {};


        /**
         * Switch the currently selected client
         * @param   client_id
         */
        $scope.switchClient = function( client_id )
        {
            console.log('Swithc Client');
        };

    })

;

