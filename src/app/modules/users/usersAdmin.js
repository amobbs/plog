/**
 * Preslog user managemenet module
 * -
 */

angular.module( 'Preslog.usersAdmin', [
        'titleService',
        'ngTable',
        'hierarchyFields'
    ])

    .config(function(stateHelperProvider) {


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
                        var user = Restangular.one('admin/users');
                        user.User = {
                            id:'',
                            deleted:false,
                            notifications: {
                                methods:{},
                                clients:[]
                            }
                        };

                        deferred.resolve(user);
                    }

                    return deferred.promise;
                }],

                // Fetch edit opts
                optionsSource: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    var deferred = $q.defer();

                    // Resolve with user ID (client limited) or free-for-all?
                    if ($stateParams.user_id)
                    {
                        Restangular.one('admin/users', $stateParams.user_id).options().then(function(options) {
                            deferred.resolve(options);
                        });
                    }
                    else
                    {
                        Restangular.one('admin/users').options().then(function(options) {
                            deferred.resolve(options);
                        });
                    }

                    return deferred.promise;
                }]
            }
        });
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

        $scope.clientAttributes = {};
        $scope.selectedAttributes = {};

        /**
         * Fix the client notifications so they can display properly in the form and then be saved to
         * MongoDB with relative ease.
         * Utilising:
         * _.where() - http://lodash.com/docs#where
         * _.defaults() - http://lodash.com/docs#defaults
         */
        var clientNotifications = [];
        angular.forEach(optionsSource.notifications.clients, function(client) {
            var search = _.where($scope.user.notifications.clients, {"client_id": client._id}),
                currentVals = {client_id: client._id};
            if (search.length > 0) {
                currentVals.attributes = search[0].attributes;
                currentVals.types = Array.isArray(search[0].types) ? {} : search[0].types;
            }
            clientNotifications.push(_.defaults(currentVals, {"attributes": [], "types": {}}));
        });

        // Save the new notification back to the client.
        $scope.user.notifications.clients = clientNotifications;

        $scope.$watch('user.notifications.clients', function() {
            console.log($scope.user.notifications.clients);
        }, true);

        /**
         * format the attribute children so we can display hierachy fields in 2 columns
         * @param children
         * @returns {Array}
         */
        $scope.attributesDisplay = function(children) {
            var columns = [[], []];
            //there is nothing to show
            if (children.length === 0) {
                return columns;
            }

            //if there are only 4 then just show one column
            if (children.length < 5) {
                return [children, []];
            }

            //split the children in the middle so they are even across to columns
            var colSize1 = Math.floor(children.length / 2);

            for(var i = 0; i < children.length; i++)
            {
                if (i < colSize1) {
                    columns[0].push(children[i]);
                } else {
                    columns[1].push(children[i]);
                }
            }

            return columns;
        };

        /**
         *
         */
        $scope.setClientAttributes = function() {
            for(var id in $scope.options.notifications.clients) {
                var client = $scope.options.notifications.clients[id];
                for(var attrId in client.attributes) {
                    client.attributes[attrId].children = $scope.attributesDisplay(client.attributes[attrId].children);
                }
                $scope.options.notifications.clients[id] = client;
            }
        };
        $scope.setClientAttributes();

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

            //set attributes back onto user


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