/**
 * Preslog user managemenet module
 * -
 */

angular.module( 'Preslog.users', [
        'titleService'
    ])

    .config(function(stateHelperProvider) {

        stateHelperProvider.addState('mainLayout.myProfile', {
            url: '/my-profile',
            views: {
                "main@mainLayout": {
                    controller: 'UserMyProfileCtrl',
                    templateUrl: 'modules/users/my-profile.tpl.html'
                }
            }
        });

        stateHelperProvider.addState('mainLayout.myNotify', {
            url: '/my-notifications',
            views: {
                "main@mainLayout": {
                    controller: 'HomeCtrl',
                    templateUrl: 'modules/users/my-notify.tpl.html'
                }
            }
        });

        stateHelperProvider.addState('mainLayout.AdminUserList', {
            url: '/admin/users',
            views: {
                "main@mainLayout": {
                    controller: 'AdminUserListCtrl',
                    templateUrl: 'modules/users/admin-user-list.tpl.html'
                }
            }
        });

        stateHelperProvider.addState('mainLayout.AdminUserEdit', {
            url: '/admin/users/{user_id:.+}',
            views: {
                "main@mainLayout": {
                    controller: 'AdminUserEditCtrl',
                    templateUrl: 'modules/users/admin-user-edit.tpl.html'
                }
            }
        });

    })


    /**
     * User: My Profile
     */
    .controller( 'UserMyProfileCtrl', function UserMyProfileController( $scope, titleService ) {
        titleService.setTitle( 'My Profile' );
    })


    /**
     * User: My Notifications
     */
    .controller( 'UserMyNotifyCtrl', function UserMyNotifyController( $scope, titleService ) {
        titleService.setTitle( 'My Notifications' );
    })


    /**
     * Admin: User: List
     */
    .controller( 'UserAdminListCtrl', function UserAdminListController( $scope, titleService ) {
        titleService.setTitle( 'Admin - Users ' );
    })

    /**
     * Admin: User: Edit
     */
    .controller( 'UserAdminEditCtrl', function UserAdminEditController( $scope, titleService ) {
        titleService.setTitle( 'Admin - Users ' );
    })

;


