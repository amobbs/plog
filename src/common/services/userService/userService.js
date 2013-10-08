/**
 * Preslog User Service
 * Singleton that houses all of the users credentials.
 */

angular.module('userService', ['restangular'])
    .factory('userService', function (Restangular, $q, $rootScope) {
        var user,
            permissions,
            clients,
            dashboards;

        // Define the service
        var service = {

            /**
             * Fetch this logged in users details
             * - Attempt to login this user with an empty POST request
             * - This will be fulfilled server-side by Sessions if the user is already logged in, like "remember me".
             * - Failure will trigger an Auth-LoginRequired event.
             *
             * @returns User details object
             */
            getUser: function () {
                var deferred = $q.defer();

                // If user is not set, attempt to login
                if (! user) {

                    // Attempt to login in order to fetch the user
                    service.login().then(function () {

                        // Login OK - Resolve this promise with the user variable set by login()
                        deferred.resolve(user);

                    }, function()
                    {
                        // Abandon the promise, and fire the Auth-LoginRequired event
                        deferred.reject();
                        $rootScope.$broadcast('event:auth-loginRequired');
                    });
                } else {
                    deferred.resolve(user);
                }

                // Promise to complete this request
                return deferred.promise;
            },


            /**
             * Login the given user
             * @param username
             * @param password
             */
            login: function(userCredentials) {
                var deferred = $q.defer();

                // Only try to login if we're not already logged in.
                if (user === undefined)
                {
                    // setup default user object
                    userCredentials = (userCredentials === undefined ? {} : {'User':userCredentials});

                    // Attempt to login
                    Restangular.all('users/login').post( userCredentials ).then(function (ret) {

                        // Login OK?
                        if (ret.login.success)
                        {
                            // Save user details to service
                            user = ret.login.user;
                            permissions = ret.login.permissions;
                            clients = ret.login.clients;

                            // Push details to scopes
                            $rootScope.global.user = user;
                            $rootScope.global.clients = clients;
                            $rootScope.global.userService = service;
                            $rootScope.global.loggedIn = true;

                            // resolve the promise
                            deferred.resolve(ret);
                        }

                        // Reject the promose on failure
                        deferred.reject(ret);
                    });

                }
                else
                {
                    deferred.resolve();
                }

                // Promise to complete this request
                return deferred.promise;
            },


            /**
             * Logout the given user
             */
            logout: function() {
                var deferred = $q.defer();

                // Post-request a logout from the server
                Restangular.all('users/logout').post({}).then(function(ret) {

                    // clear current user
                    user = undefined;
                    permissions = undefined;
                    clients = undefined;
                    dashboards = undefined;

                    // Clear scope vars
                    $rootScope.global.user = {};
                    $rootScope.global.userService = this;
                    $rootScope.global.loggedIn = false;

                    // complete request
                    deferred.resolve(ret);
                });

                // Promise to complete this request
                return deferred.promise;
            },


            /**
             * Fetch the list of this users accessible clients
             * @returns Client list
             */
            getClients: function () {
                var deferred = $q.defer();

                // Fetch client list if not loaded
                if (! clients) {
                    Restangular.all('clients').getList().then(function (ret) {

                        // Set clients
                        clients = ret.clients;

                        deferred.resolve(clients);
                    });
                } else {
                    deferred.resolve(clients);
                }

                // Promise to complete this request
                return deferred.promise;
            },


            /**
             * Fetch this users accessible dashboards
             * @returns Dashboard list
             */
            getDashboards: function () {
                var deferred = $q.defer();

                // Fetch the Dashboard list if not already set
                if (! dashboards) {
                    Restangular.all('dashboards').getList().then(function (ret) {

                        // Set Dashboards
                        dashboards = ret.dashboards;

                        deferred.resolve(dashboards);
                    });
                } else {
                    deferred.resolve(dashboards);
                }

                // Promise to complete this request
                return deferred.promise;
            },


            /**
             * Check for this permission against this users permissions
             * @return  boolean     True if permission is set
             */
            checkPermission: function( key ) {
                var deferred = $q.defer();

                // Need user data for this to work
                service.getUser().then(function()
                {
                    // Permissions list must exist
                    if (permissions === undefined)
                    {
                        deferred.resolve( false );
                    }

                    // Perform permissions check
                    var isAllowed = (permissions.indexOf(key) !== -1);
                    deferred.resolve( isAllowed );
                });

                // Return the promise
                return deferred.promise;
            },


            /**
             * Check for this permission to access this resource
             * If this fails, the auth-loginRequired event will be triggered.
             * @param   string      Permission to check
             */
            checkAccessPermission: function( key ) {
                var deferred = $q.defer();

                // Check the permissions as per normal
                service.checkPermission( key ).then(function( result )
                {
                    if (result)
                    {
                        deferred.resolve();
                    }
                    else
                    {
                        // Failure to have permissions results in a 403 error
                        $rootScope.$broadcast('event:error-unauthorised');
                        deferred.reject();
                    }
                });

                return deferred.promise;
            },


            /**
             * Instigate a request for Forgotten Password
             * @param   string      Email address for reset
             */
            forgottenPassword: function( email ) {
                var deferred = $q.defer();

                // Try to get the OK message
                Restangular.all('users/reset-password/email').getList().then(function (ret) {

                });
            },


            /**
             * Perform a password reset using the Token and new Password.
             * @param   string      newPassword
             * @param   string      token
             */
            resetPassword: function( newPassword, token ) {

            }

        };


        // Factory finish
        return service;
    });