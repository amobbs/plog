/**
 * Preslog user managemenet module
 * -
 */

angular.module( 'Preslog.users', [
        'titleService',
        'ngTable',
        'hierarchyFields'
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
            url: '/admin/users/{user_id:[0-9a-z]+}',
            views: {
                "main@mainLayout": {
                    controller: 'AdminUserEditCtrl',
                    templateUrl: 'modules/users/admin-user-edit.tpl.html'
                }
            },
            resolve: {
                userSource: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    // Fetch edit opts
                    var deferred = $q.defer();
                    Restangular.one('admin/users', $stateParams.user_id).get().then(function(user) {
                        user.id = user.User._id;
                        deferred.resolve(user);
                    });

                    return deferred.promise;
                }],
                optionsSource: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    // Fetch edit opts
                    var deferred = $q.defer();
                    Restangular.one('admin/users').options().then(function(options) {
                        deferred.resolve(options);
                    });

                    return deferred.promise;
                }]
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

        // Set page title
        titleService.setTitle( ['Users', 'Admin'] );

        // Configure table
        $scope.tableParams = new ngTableParams({
            page: 1,                // show first page
            total: 0,               // length of data
            count: 10,              // count per page
            sorting: {
                lastName: 'asc'     // default order: last name A-z
            },
            filter: {               // default filter:
                deleted: 'false'    // do not show deleted users
            }
        });

        // Fetch users, then instigate table watcher
        Restangular.all('admin/users').getList().then(function (data) {
            $scope.loading = false;

            $scope.allUsers = data.users;

            // Some simple data processing
            for (var i = 0; i < $scope.allUsers.length ; i++) {
                // Enforce "deleted". False if not set
                if ($scope.allUsers[i].deleted === undefined) { $scope.allUsers[i].deleted = false; }
                $scope.allUsers[i].deleted = $scope.allUsers[i].deleted ? 'true' : 'false';
            }

            var tmp = $scope.allUsers;
            $scope.allUsers = [];

            for (var j = 0; j < tmp.length ; j++) {
                $scope.allUsers[j] = tmp[j];
            }

            // Watch table and perform actions
            $scope.$watch('tableParams', function(params) {

                console.log($scope.allUsers);
                console.log(params.filter);

                // Filter and order
                var orderedData = params.filter ? $filter('filter')($scope.allUsers, params.filter) : $scope.allUsers;

                // set total for pagination
                params.total = orderedData.length;

                // slice array data on pages
                $scope.users = orderedData.slice(
                    (params.page - 1) * params.count,
                    params.page * params.count
                );
            }, true);
        });


        /**
         * Toggle "show Deleted"
         */
        $scope.toggleDeleted = function() {
            if ($scope.tableParams.filter.deleted === undefined) {
                $scope.tableParams.filter.deleted = 'false';
            } else {
                delete $scope.tableParams.filter.deleted;
            }
        };


    })


    /**
     * Admin: User: Edit
     */
    .controller( 'AdminUserEditCtrl', function UserAdminEditController( $scope, titleService, userSource, optionsSource, $location ) {
        titleService.setTitle( 'Admin - Users ' );

        /**
         * Init
         */

        // Pass options to the form
        $scope.options = optionsSource;

        // Pass user to form
        $scope.user = userSource.User;


        /**
         * Save User
         */
        $scope.saveUser = function() {

            // Validate the form
            if ( $scope.userForm.$invalid ) {
                alert('Invalid!');

                return false;
            }

            // Fetch data from form
            userSource.User = $scope.user;

            // Post back to API
            userSource.post().then(function()
            {
                // Redirect to user list
                $location.path('/admin/users');
            });
        };


        /**
         * Delete User
         */
        $scope.deleteUser = function() {

            // Put scope user back
            userSource.User = $scope.user;

            // Apply delete
            userSource.remove().then(function()
            {
                // Back to list page
                $location.path('/admin/users');

            });
        };


        $scope.user.notifications = {};
        $scope.user.notifications.clients = [];
        $scope.user.notifications.clients['1'] = {};
        $scope.user.notifications.clients['1'].severity = [];
        $scope.user.notifications.clients['1'].attributes = [4,5];

        $scope.fields = {};
        $scope.fields.notifications = {};
        $scope.fields.notifications.clients = [
            {
                'name':'ONE',
                'id': 1,
                'severities':
                [
                    {
                        'name':'Severity 1',
                        'id':'1'
                    },
                    {
                        'name':'Severity 2',
                        'id':'2'
                    }
                ],
                'attributes':
                [
                    {
                        id: 1, name: "Networks", deleted: false, children: [
                            {id: 2, name:"ABC", deleted: false, children: [
                                {id: 3, name: "ABC", deleted: false},
                                {id: 4, name: "ABC 2", deleted: false},
                                {id: 5, name: "ABC 3", deleted: false},
                                {id: 6, name: "ABC News", deleted: false}
                            ]},
                            {id: 7, name:"WIN", deleted: false, children: [
                                {id: 8, name: "Win", deleted: false}
                            ]},
                            {id: 9, name:"Blah", deleted: false, children: [
                                {id: 10, name: "Win", deleted: false}
                            ]},
                            {id: 11, name:"Blah", deleted: false, children: [
                                {id: 12, name: "Win", deleted: false}
                            ]}
                        ]
                    }
                ]
            }
        ];

    })

;


