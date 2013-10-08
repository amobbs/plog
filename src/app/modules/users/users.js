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

        /**
         * My Profile
         */
        stateHelperProvider.addState('mainLayout.myProfile', {
            url: '/my-profile',
            views: {
                "main@mainLayout": {
                    controller: 'UserMyProfileCtrl',
                    templateUrl: 'modules/users/my-profile.tpl.html'
                }
            },
            resolve: {
                userSource: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    // Fetch user details
                    var deferred = $q.defer();
                    Restangular.one('/users/my-profile').get().then(function(user) {
                        user.id = user.User._id;
                        deferred.resolve(user);
                    });

                    return deferred.promise;
                }],
                optionsSource: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    // Fetch edit opts
                    var deferred = $q.defer();
                    Restangular.one('/users/my-profile').options().then(function(options) {
                        deferred.resolve(options);
                    });

                    return deferred.promise;
                }]
            }
        });

        /**
         * My Notifications
         */
        stateHelperProvider.addState('mainLayout.myNotify', {
            url: '/my-notifications',
            views: {
                "main@mainLayout": {
                    controller: 'UserMyNotifyCtrl',
                    templateUrl: 'modules/users/my-notify.tpl.html'
                }
            },
            resolve: {
                userSource: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    // Fetch notify details
                    var deferred = $q.defer();
                    Restangular.one('/users/my-notifications').get().then(function(user) {
                        user.id = user.User._id;
                        deferred.resolve(user);
                    });

                    return deferred.promise;
                }],
                optionsSource: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    // Fetch edit opts
                    var deferred = $q.defer();
                    Restangular.one('/users/my-notifications').options().then(function(options) {
                        deferred.resolve(options);
                    });

                    return deferred.promise;
                }]
            }
        });

        /**
         * Admin User List
         */
        stateHelperProvider.addState('mainLayout.AdminUserList', {
            url: '/admin/users',
            views: {
                "main@mainLayout": {
                    controller: 'AdminUserListCtrl',
                    templateUrl: 'modules/users/admin-user-list.tpl.html'
                }
            },
            resolve: {
                userData: ['$q', 'Restangular', function($q, Restangular) {
                    var deferred = $q.defer();

                    // Fetch user list
                    Restangular.all('admin/users').getList().then(function (data) {
                        deferred.resolve(data);
                    });

                    return deferred.promise;
                }]
            }
        });

        /**
         * Admin User Edit
         */
        stateHelperProvider.addState('mainLayout.AdminUserEdit', {
            url: '/admin/users/{user_id:[0-9a-z]*}',
            views: {
                "main@mainLayout": {
                    controller: 'AdminUserEditCtrl',
                    templateUrl: 'modules/users/admin-user-edit.tpl.html'
                }
            },
            resolve: {
                // Fetch user and notification details
                userSource: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    var deferred = $q.defer();

                    // If loading a user
                    if ($stateParams.user_id.length == 24)
                    {
                        Restangular.one('admin/users', $stateParams.user_id).get().then(function(user) {
                            user.id = user.User._id;
                            deferred.resolve(user);
                        });
                    }
                    // Creating a user instead. Load a blank form.
                    else
                    {
                        deferred.resolve({
                            User:{
                                id:'',
                                deleted:false
                            }
                        });
                    }

                    return deferred.promise;
                }],

                // Fetch edit opts
                optionsSource: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    var deferred = $q.defer();
                    Restangular.one('admin/users', $stateParams.user_id).options().then(function(options) {
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
    .controller( 'UserMyProfileCtrl', function UserMyProfileController( $scope, titleService, userSource, optionsSource, $location ) {
        titleService.setTitle( 'My Profile' );

        // Pass resolves to the scope
        $scope.options = optionsSource;
        $scope.user = userSource.User;

        /**
         * Save My Profile
         */
        $scope.saveProfile = function()
        {
            // Clear flash message
            $scope.flashMessage = {
                error: false,
                success: false
            };

            // Will not submit without validation passing
            if ( $scope.userForm.$invalid ) {
                alert('Your submission is not valid. Please check for errors.');
                return false;
            }

            // Fetch data from form
            userSource.User = $scope.user;

            // Post back to API
            userSource.post().then(

                // On success
                function()
                {
                    // Show a success message
                    $scope.flashMessage.success = true;
                },

                // On failure
                function(response)
                {
                    // Show an error message
                    $scope.flashMessage.error = true;

                    // Extrapolate all fields to the scope
                    $scope.serverErrors = response.data.data;
                }
            );
        };
    })


    /**
     * User: My Notifications
     */
    .controller( 'UserMyNotifyCtrl', function UserMyNotifyController( $scope, titleService, userSource, optionsSource, $location ) {
        titleService.setTitle( 'My Notifications' );

        // Pass resolves to the scope
        $scope.options = optionsSource;
        $scope.user = userSource.User;

        /**
         * Save My Notifications
         */
        $scope.saveNotifications = function()
        {
            // Clear flash message
            $scope.flashMessage = {
                error: false,
                success: false
            };

            // Will not submit without validation passing
            if ( $scope.userForm.$invalid ) {
                alert('Your submission is not valid. Please check for errors.');
                return false;
            }

            // Fetch data from form
            userSource.User = $scope.user;

            // Post back to API
            userSource.post().then(

                // On success
                function()
                {
                    // Flash
                    $scope.flashMessage.success = true;
                },

                // On failure
                function(response)
                {
                    // Flash
                    $scope.flashMessage.error = true;

                    // Extrapolate all fields to the scope
                    $scope.serverErrors = response.data.data;
                }
            );
        };
    })


    /**
     * Admin: User: List
     */
    .controller( 'AdminUserListCtrl', function UserAdminListController( $scope, userData, titleService, ngTableParams, Restangular, $filter ) {

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

        // Get user data and put to scope
        $scope.allUsers = userData.users;

        // Watch table and perform actions
        $scope.$watch('tableParams', function(params) {

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
        titleService.setTitle( ['Edit User', 'Admin'] );

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

            // Will not submit without validation passing
            if ( $scope.userForm.$invalid ) {
                alert('Your submission is not valid. Please check for errors.');
                return false;
            }

            // Fetch data from form
            userSource.User = $scope.user;

            // Post back to API
            userSource.post().then(

                // On success
                function()
                {
                    // Redirect to user list
                    $location.path('/admin/users');
                },

                // On failure
                function(response)
                {
                    // Extrapolate all fields to the scope
                    $scope.serverErrors = response.data.data;
                }
            );
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

    })


;


