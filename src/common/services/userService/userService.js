/**
 * Preslog User Service
 * Singleton that houses all of the users credentials.
 */

angular.module('userService', ['restangular'])

    /**
     * User Service Object
     */
    .factory('userService', function (Restangular, $q, $rootScope, $log) {
        var user,
            permissions,
            clients,
            currentClient,
            dashboards,
            loginPromise;

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
            getUser: function (forceReload) {
                var deferred = $q.defer();

                // Default "force" to false if net set
                forceReload = (forceReload !== undefined ? forceReload : false);

                // If user is not set, attempt to login
                if (! user || forceReload) {

                    // Attempt to login in order to fetch the user
                    service.login().then(function () {

                        // Login OK - Resolve this promise with the user variable set by login()
                        deferred.resolve(user);

                    }, function()
                    {
                        // Abandon the promise
                        deferred.reject();
                    });
                } else {
                    deferred.resolve(user);
                }

                // Promise to complete this request
                return deferred.promise;
            },


            /**
             * Directly read the user from this class.
             * Does not call the user via Rest, merely monitors and returns the currently stored data.
             * Usually used in conjunction with $watch
             */
            readUser: function() {
                return user;
            },


            /**
             * Login the given user
             * @param username
             * @param password
             */
            login: function(userCredentials) {
                var deferred = $q.defer();

                // Send back cached promise if login request is active
                if (loginPromise !== undefined)
                {
                    return loginPromise;
                }

                // setup default user object
                var credentials = (userCredentials === undefined ? {} : {'User':userCredentials});

                // Attempt to login
                // Note: A 301 will never be issued for auto-login attempts.
                //       This prevents the auto-login firing an "unauthorised" error in the interceptor.
                Restangular.all('users/login').post( credentials ).then(function (ret) {

                    // Login OK? Log us in!
                    if (ret.login.success)
                    {
                        // Save user details to service
                        user = ret.login.user;
                        permissions = ret.login.permissions;
                        clients = ret.login.clients;
                        currentClient = clients[0]._id;

                        // Mark as logged in
                        $rootScope.global.loggedIn = true;

                        // resolve the promise
                        loginPromise = undefined;
                        deferred.resolve(ret);
                    }

                    // Failure to login.
                    else
                    {
                        // If not specifically logging in, raise the loginRequired broadcast.
                        if (userCredentials === undefined)
                        {
                            $rootScope.$broadcast('event:auth-loginRequired');
                        }

                        // Reject the promose on failure
                        loginPromise = undefined;
                        deferred.reject(ret);
                    }
                });

                // Promise to complete this request
                loginPromise = deferred.promise;
                return loginPromise;
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
                    service.getUser().then(function (user) {

                        // Resolve clients from the value set by getUser()
                        deferred.resolve(clients);
                    });
                } else {
                    deferred.resolve(clients);
                }

                // Promise to complete this request
                return deferred.promise;
            },


            /**
             * Fetch the currently active client
             */
            getClient: function() {
                var deferred = $q.defer();

                // Get clients
                service.getClients().then(function(clients) {

                    // Set current client if not already chosen
                    if (currentClient === undefined)
                    {
                        currentClient = clients[0]._id;
                    }

                    // Fetch this active client
                    var client = $.map(clients, function(v,k){ if (v._id == currentClient) { return v; } });
                    client = client[0];

                    deferred.resolve(client);
                });

                return deferred.promise;
            },


            /**
             * Set the client
             */
            setClient: function( client_id ) {

                // Set the active client
                currentClient = client_id;
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
                        dashboards = ret;

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
             * - checkPermission works off the data that's available AT THE TIME.
             *   If you want precise user info (eg. synchronous) then run getUser() before you call checkPermission.
             *   See resolvePermission for a resolvable version.
             * @return  boolean     True if permission is set
             */
            checkPermission: function( key ) {

                // Permissions list must exist
                if (permissions === undefined)
                {
                    return false;
                }

                // Perform permissions check
                var isAllowed = (permissions.indexOf(key) !== -1);
                return isAllowed;
            },


            /**
             * Check for this permission to access this resource
             * - Makes the request in a synchonous manner, calling getUser before checkPermission.
             * - If the check fails, the auth-loginRequired event will be triggered.
             * - Only check for negative permission (fail if the user DOESN'T have the key)
             * @param   string      Permission to check
             */
            resolvePermission: function( key ) {
                var deferred = $q.defer();

                // Resolve user first
                service.getUser().then(function()
                {
                    // Check permission
                    if (service.checkPermission( key ))
                    {
                        // Ok
                        deferred.resolve(true);
                    }
                    else
                    {
                        // Failed
                        deferred.reject(false);
                    }
                });

                // Defer to promise
                return deferred.promise;
            },


            /**
             * Check for this permission to access this resource
             * - Makes the request in a synchonous manner, calling getUser before checkPermission.
             * - If the check fails, the auth-loginRequired event will be triggered.
             * - Only check for negative permission (fail if the user DOESN'T have the key)
             * @param   string      Permission to check
             */
            checkAccessPermission: function( key ) {
                var deferred = $q.defer();

                // Resolve user first
                service.resolvePermission( key ).then(
                    function()
                    {
                        // Success
                        deferred.resolve(true);
                    },
                    function()
                    {
                        // Failed
                        $rootScope.$broadcast('event:error-unauthorised');
                        deferred.reject();
                    }
                );

                // Defer to promise
                return deferred.promise;
            },


            /**
             * Instigate a request for Forgotten Password
             * @param   string      Email address for reset
             */
            forgottenPassword: function( email ) {
                var deferred = $q.defer();

                var postData = {
                    "email":email
                };

                // Try to get the OK message
                Restangular.all('users/reset-password/email').post( postData ).then(
                    function (data) {
                        deferred.resolve(data);
                    },
                    function (data) {
                        deferred.reject(data);
                    });

                return deferred.promise;
            },


            /**
             * Perform a password reset using the Token and new Password.
             * @param   string      newPassword
             * @param   string      token
             */
            resetPassword: function( newPassword, token ) {
                var deferred = $q.defer();

                var postData = {
                    "password":newPassword,
                    "token":token
                };

                // Try to get the OK message
                Restangular.all('users/reset-password').post( postData ).then(
                    function (data) {
                        deferred.resolve(data);
                    },
                    function (data) {
                        deferred.reject(data);
                    });


                return deferred.promise;
            }

        };

        // Factory finish
        return service;
    })
;