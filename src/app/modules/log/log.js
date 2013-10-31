/**
 * Preslog Log Module
 */
angular.module( 'Preslog.log', [
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

                // Load log data
                logData: ['$q', 'LogRestangular', '$stateParams', 'userService', function($q, LogRestangular, $stateParams, userService) {
                    var deferred = $q.defer();

                    // If editing an existing log
                    if ($stateParams.log_id) {
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
                            log.Log.fields = [
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
                    if ($stateParams.log_id) {

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
    .controller( 'LogCtrl', function LogController( $scope, titleService, logData, logOptions, LogRestangular, $location ) {

        // Set title
        titleService.setTitle( 'Create Log' );

        // Apply to scope
        $scope.log = logData.Log;
        $scope.options = logOptions;
        $scope.serverErrors = {};
        $scope.Object = Object;


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


        /**
         * Save Log
         */
        $scope.saveLog = function()
        {
            // Data Fudge
            logData.Log = $scope.log;

            // Submit
            logData.post().then(

                // On success
                function(response)
                {
                    // Redirect to homepage
                    $location.path('/');
                },

                // On failure
                function(response)
                {
                    // Extrapolate all fields to the scope
                    $scope.serverErrors = response.data.data;

                    // If field exists, mark is as invalid
                    for (var i in $scope.serverErrors)
                    {
                        if ($scope.logForm[i] !== undefined) {
                            $scope.logForm[i].$setValidity('validateServer', false);
                        }
                    }

                }
            );
        };

        /**
         * Delete Log
         */
        $scope.deleteLog = function()
        {
            logData.remove().then(
                function()
                {
                    $location.path('/');
                },
                function()
                {
                    alert('There was a problem deleting this log.');
                }
            );
        };
    })


;

