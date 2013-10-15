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
                id: 'Log.hrid'
            });
        });
    })


    .config(function(stateHelperProvider) {

        /**
         * Log Editor
         */
        stateHelperProvider.addState('mainLayout.log', {
            url: '/logs/{log_id:[0-9]*}',
            views: {
                "main@mainLayout": {
                    controller: 'LogCtrl',
                    templateUrl: 'modules/log/log.tpl.html'
                }
            },
            resolve: {

                // Load log data
                logData: ['$q', 'LogRestangular', '$stateParams', function($q, LogRestangular, $stateParams) {
                    var deferred = $q.defer();

                    // If editing an existing log
                    if ($stateParams.log_id) {
                        LogRestangular.one('logs', $stateParams.log_id).get().then(function(log) {
                            deferred.resolve(log);
                        });
                    }
                    // Creating a log instead - set up base log object.
                    else {
                        deferred.resolve({
                            Log: {
                                _id: null,
                                deleted: false,
                                fields: [],
                                attributes: [],
                                newLog: true
                            }
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
    .controller( 'LogCtrl', function LogController( $scope, titleService, logData, logOptions, LogRestangular ) {

        // Set title
        titleService.setTitle( 'Create Log' );

        // Apply to scope
        $scope.log = logData.Log;
        $scope.options = logOptions;

        $scope.attributesDisplay = function(children) {
            var columns = [[], []];
            if (children.length === 0) {
                return columns;
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


        $scope.saveLog = function()
        {
            // Will not submit without validation passing
            if ( $scope.logForm.$invalid ) {
                alert('Your submission is not valid. Please check for errors.');
                return false;
            }

            // Data Fudge
            logData.Log = $scope.log;

            // Submit
            logData.post().then(

                // On success
                function()
                {
                    // Redirect to homepage
                    $location.path('/');
                },

                // On failure
                function(response)
                {
                    // Extrapolate all fields to the scope
                    $scope.validation = response.data.data;

                    // If field exists, mark is as invalid
                    for (var i in $scope.validation)
                    {
                        if ($scope.logForm[i] !== undefined) {
                            $scope.logForm[i].$setValidity('validateServer', false);
                        }
                    }

                }
            );
        };

    })


;

