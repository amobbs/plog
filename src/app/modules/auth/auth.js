/**
 * Authentication Module
 */

angular.module( 'Preslog.auth', [
        'titleService'
    ])

    /**
     * Login Route
     */
    .constant('statesLogin', [{
        name: 'publicLayout.login',
        options: {
            url: '/login',
            views: {
                "main@publicLayout": {
                    controller: 'AuthCtrl',
                    templateUrl: 'modules/auth/login.tpl.html'
                }
            }
        }
    }])


    /**
     * Controller
     */
    .controller( 'AuthCtrl', function AuthController( $scope, titleService ) {
        console.log('auth');
        titleService.setTitle( 'Login' );
    })

;

