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

    })


    /**
     * User: My Profile
     */
    .controller( 'UserMyProfileCtrl', function UserMyProfileController( $scope, titleService, userSource, optionsSource, $location, userService ) {
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

            // Fetch data from form
            userSource.User = $scope.user;

            // Post back to API
            userSource.post().then(

                // On success
                function()
                {
                    // Force a reload of the user data from the server
                    userService.getUser(true);

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

                    // If field exists, mark is as invalid
                    for (var i in $scope.serverErrors)
                    {
                        if ($scope.userForm[i] !== undefined) {
                            $scope.userForm[i].$setValidity('validateServer', false);
                        }
                    }
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

;


