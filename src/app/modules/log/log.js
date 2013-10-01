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
                                newLog: true
                            }
                        });
                    }

                    return deferred.promise;
                }],

                // Load log options
                logOptions: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    var deferred = $q.defer();

                    var request = Restangular.one('logs');

                    if ($stateParams.log_id) {
                        request = Restangular.one('logs', $stateParams.log_id);
                    }

                    request.options().then(function(options) {
                        deferred.resolve(options);
                    });
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

