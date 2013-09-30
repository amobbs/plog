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
                id: 'Log._id'
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
                    if ($stateParams.log_id !== undefined) {
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

                    if ($stateParams.log_id !== undefined) {
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


        $scope.hierarchySelected = [1,2];
        $scope.hierarchyFields = [
            {
                id: 1, name: "Networks", deleted: false, children: [
                {id: 2, name:"ABC", deleted: false, children: [
                    {id: 3, name: "ABC", deleted: false},
                    {id: 4, name: "ABC 2", deleted: false},
                    {id: 5, name: "ABC 3", deleted: false},
                    {id: 6, name: "ABC News", deleted: false}
                ]},
                {id: 7, name:"WIN", deleted: false, children: [
                    {id: 8, name: "Win", deleted: false}
                ]},
                {id: 9, name:"Blah1", deleted: false, children: [
                    {id: 10, name: "Win", deleted: false}
                ]},
                {id: 11, name:"Blah1", deleted: false, children: [
                    {id: 12, name: "Win", deleted: false}
                ]}
            ]
            },
            {
                id: 13, name: "States", deleted: false, children: [
                {id: 14, name: "", deleted: false, children: [
                    {id: 15, name: 'NSW', deleted: false},
                    {id: 16, name: 'VIC', deleted: false},
                    {id: 17, name: 'QLD', deleted: false},
                    {id: 18, name: 'WA', deleted: false}
                ]
                }
            ]
            }
        ];

    })


;

