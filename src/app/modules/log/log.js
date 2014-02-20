/**
 * Preslog Log Module
 */
angular.module( 'Preslog.log', [
        'Preslog.log.confirmModal',
        'titleService',
        'hierarchyFields'
    ])

    /**
     * Restangular for Logs
     */
    .factory('LogRestangular', function (Restangular) {
        return Restangular.withConfig(function (RestangularConfigurer) {
            RestangularConfigurer.setRestangularFields({
                id: 'Log.slug'
            });
        });
    })


    .config(function(stateHelperProvider) {

        /**
         * Log Editor
         */
        stateHelperProvider.addState('mainLayout.log', {
            url: '/logs/{log_id}',
            views: {
                "main@mainLayout": {
                    controller: 'LogCtrl',
                    templateUrl: 'modules/log/log.tpl.html'
                }
            },
            resolve: {

                // Change the LogID if not compatible
                logId: ['$stateParams', function($stateParams) {
                    if ($stateParams.log_id !== undefined)
                    {
                        if (!RegExp('[a-zA-Z]+_[0-9]+').test($stateParams.log_id))
                        {
                            $stateParams.log_id = undefined;
                        }
                    }
                }],

                // Load log data
                logData: ['$q', 'LogRestangular', '$stateParams', 'userService', function($q, LogRestangular, $stateParams, userService) {
                    var deferred = $q.defer();

                    // If editing an existing log
                    if ($stateParams.log_id !== undefined) {
                        LogRestangular.one('logs', $stateParams.log_id).get().then(function(log) {
                            deferred.resolve(log);
                        });
                    }
                    // Creating a log instead - set up base log object.
                    else {

                        userService.getClient().then(function(client) {
                            var log = LogRestangular.one('logs');
                            log.Log._id = null;
                            log.Log.client_id = client._id;
                            log.Log.slug = '';
                            log.Log.deleted = false;
                            log.Log.fields = [];
                            log.Log.attributes = [];
                            log.Log.newLog = true;

                            deferred.resolve(log);
                        });
                    }

                    return deferred.promise;
                }],

                // Load log options
                logOptions: ['$q', 'Restangular', '$stateParams', 'userService', function($q, Restangular, $stateParams, userService) {
                    var deferred = $q.defer();

                    // If an existing log, use that as a basis
                    if ($stateParams.log_id !== undefined) {

                        // Fetch the options based off the Log ID
                        var request = Restangular.one('logs', $stateParams.log_id).options().then(function(options) {
                            deferred.resolve(options);
                        });

                    }
                    // If for a specific client, use that as a basis
                    else
                    {
                        // Get the client ID
                        userService.getClient().then(function(client){

                            // Add the Client ID as part of the query params to fetch the opts
                            var request = Restangular.one('logs').options({'client_id':client._id}).then(function(options) {
                                deferred.resolve(options);
                            });
                        });
                    }

                    return deferred.promise;
                }]

            }
        });

    })

/**
 * And of course we define a controller for our route.
 */
    .controller( 'LogCtrl', function LogController( $scope, $q, $location, $modal, titleService, logData, logOptions, LogRestangular, stateHistory, userService ) {

        // Set title
        titleService.setTitle( 'Create Log' );

        // Apply to scope
        $scope.log = logData.Log;
        $scope.options = logOptions;
        $scope.serverErrors = {};
        $scope.Object = Object;
        $scope.displayAttributes = [];
        $scope.serverErrorsPresent = false;

        //Either get the clients name from the api when getting the log, or if a new log get it via userservice
        $scope.clientName = undefined;
        if (!logData.Client)
        {
            userService.getClient().then(function(client)
            {
                $scope.clientName = client.name;
            });
        }
        else
        {
            $scope.clientName = logData.Client.name;
        }


        /**
         * Display attributes
         * @param children
         * @returns {Array}
         */
        $scope.attributesDisplay = function(children) {
            var columns = [[], []];
            if (children.length === 0) {
                return columns;
            }

            //if there are only 4 then just show one column
            if (children.length < 5) {
                return [children, []];
            }

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

        $scope.convertAttributesForDisplay = function()
        {
            $scope.displayAttributes = [];
            for(var id in $scope.options.attributes)
            {
                var group = $scope.options.attributes[id];
                var attr = {
                    name: group.name,
                    label: group.label,
                    children: []
                };

                if (group.children !== undefined)
                {
                    attr.children = $scope.attributesDisplay(group.children);
                }

                $scope.displayAttributes.push(attr);
            }
        };
        $scope.convertAttributesForDisplay();

        /**
         * Save Log
         */
        $scope.saveLog = function()
        {
            var deferred = $q.defer();

            $scope.serverErrorsPresent = false;

            // Data Fudge
            logData.Log = $scope.log;

            // Reset validation
            for (var f in $scope.logForm)
            {
                if ( $scope.logForm[f].$invalid === undefined)
                {
                    continue;
                }

                $scope.logForm[f].$setValidity('validateServer', true);
            }

            // Submit
            logData.post().then(

                // On success
                function(response)
                {
                    // Resolve
                    deferred.resolve();

                    // Redirect to previous state, or homepage
                    if (stateHistory.goBack() === false)
                    {
                        $location.path('/');
                    }
                },

                // On failure
                function(response)
                {
                    // Extrapolate all fields to the scope
                    $scope.serverErrors = response.data.data;

                    // If field exists, mark is as invalid
                    var keys = _.keys($scope.serverErrors);
                    for (var i in keys)
                    {
                        if ($scope.logForm[ keys[i] ] !== undefined)
                        {
                            $scope.logForm[ keys[i] ].$setValidity('validateServer', false);
                            $scope.serverErrorsPresent = true;
                        }
                    }

                    // Reject the promise.
                    deferred.reject();
                }
            );

            // Return a promise that the log will be saved
            return deferred.promise;
        };

        $scope.openDeleteModal = function () {
            var modal = $modal.open({
                templateUrl: 'modules/log/confirm/confirmDelete.tpl.html',
                controller: 'ConfirmModalCtrl',
                backdrop: 'static',
                resolve: {
                    message: function() { return $scope.message; }
                }
            });
            modal.result.then(function() {
                $scope.deleteLog();
            });
        };


        /**
         * Delete Log
         */
        $scope.deleteLog = function()
        {
            logData.remove().then(
                function()
                {
                    if (stateHistory.goBack() === false)
                    {
                        $location.path('/');
                    }
                }
            );
        };
    })


;

