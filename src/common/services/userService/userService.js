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
                    service.login().then(function (ret) {

                        // Login OK?
                        if (ret.login.success)
                        {
                            // Resolve this promise
                            deferred.resolve(user);
                        }

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
            login: function(user) {
                var deferred = $q.defer();

                // default user object
                user = (user === undefined ? {} : user);

                // Attempt to login
                Restangular.all('users/login').post( user ).then(function (ret) {

                    // Login OK?
                    if (ret.login.success)
                    {
                        // Save user details
                        user = ret.login.user;
                        permissions = ret.login.permissions;
                    }

                    // resolve the promise
                    // We always resolve this process, even in error cases.
                    deferred.resolve(ret);
                });

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

                // Ensures the user object is loaded, which populates permissions
                getUser().then(function(ret) {
                    checkPermissions( key );
                });
            }

        };


        /**
         * Permissions check
         * @param   key         String name of the permission
         * @return  boolean     True if user had this permission access
         */
        var checkPermissions = function( key ) {
            return (permissions.indexOf(key) !== -1);
        };


        // Factory finish
        return service;
    });