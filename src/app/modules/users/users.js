/**
 * Preslog user managemenet module
 * -
 */

angular.module( 'Preslog.users', [
        'titleService', 'ngTable'
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
                    controller: 'UserMyNotifyCtrl',
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
    .controller( 'AdminUserListCtrl', function UserAdminListController( $scope, titleService, ngTableParams, Restangular, $filter ) {
        titleService.setTitle( 'Admin - Users ' );

        $scope.tableParams = new ngTableParams({
            page: 1,            // show first page
            total: 0,           // length of data
            count: 10,          // count per page
            sorting: {
                name: 'asc'     // initial sorting
            },
            filter: {           // initial filter
                deleted: false  // do not show deleted users
            }
        });

        $scope.loading = true;

        Restangular.all('admin/users').getList().then(function (data) {
            $scope.loading = false;
            $scope.allUsers = data.users;

            // Watch table and perform actions
            $scope.$watch('tableParams', function(params) {

                var data = $scope.allUsers;

                // Filter and order
                var orderedData = params.filter ?
                    $filter('filter')(data, params.filter) :
                    data;

                // set total for pagination
                params.total = orderedData.length;

                // slice array data on pages
                $scope.users = data.slice(
                    (params.page - 1) * params.count,
                    params.page * params.count
                );

            }, true);

        });




    })

    /**
     * Admin: User: Edit
     */
    .controller( 'AdminUserEditCtrl', function UserAdminEditController( $scope, titleService ) {
        titleService.setTitle( 'Admin - Users ' );
    })

;


