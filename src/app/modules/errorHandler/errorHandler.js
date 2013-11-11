angular.module('errorHandler', [])
    .config(function(stateHelperProvider, $urlRouterProvider, $httpProvider) {


        /**
         * Error State
         */
        stateHelperProvider.addState('modalLayout.errorHandler', {
            views: {
                "main@modalLayout": { // Points to the ui-view="main" in modal-layout.tpl.html
                    controller: 'ErrorHandlerCtrl as ErrorHandlerCtrl',
                    templateUrl: 'modules/errorHandler/template.tpl.html'
                }
            },
            params: ['title', 'message', 'details']
        });


        /**
         * 404 Error Handler
         * Executes the function on 404, which changes the state to an error state.
         */
        $urlRouterProvider.otherwise(function( $injector )
        {
            var $rootScope = $injector.get('$rootScope');
            $rootScope.$broadcast('event:error-generic', {
                title: "404 - Not Found",
                message: "This page does not exist",
                details: ""
            });
        });


        /**
         * Response Interceptor
         * Handles 403, 404, 500, 503 and 0 errors from HTTP requests.
         * Changes to the error state on error interception
         */
        $httpProvider.responseInterceptors.push(['$q', '$injector', '$rootScope', function($q, $injector, $rootScope) {
            return function (promise) {
                return promise.then(

                    // Request was OK: Do nothing
                    function(response) {
                        return response;
                    },
                    function(response) {

                        // Fetch state and filter
                        var $state = $injector.get('$state');
                        var $filter = $injector.get('$filter');

                            // Set a list of errors
                        var errors = {
                            403: '403 - Access Forbidden',
                            404: '404 - Not Found',
                            500: '500 - Internal Server Error',
                            503: '503 - Gateway Time-out',
                            0: 'CORS Error - API Not Accepting Request'
                        };

                        // Handle the specified error types
                        if (response.status in errors) {

                            // Broadcast the error
                            $rootScope.$broadcast('event:error-generic', {
                                title: errors[response.status],
                                message: response.data.message,
                                details: $filter('json')(response)
                            });
                        }

                        // Reject the deferred request
                        return $q.reject(response);
                    }
                );
            };
        }]);
    })


    /**
     * Startup Tasks
     * Register event handlers
     */
    .run(function ($rootScope, $location, $state) {
        if (typeof $rootScope.global === 'undefined') {
            $rootScope.global = {};
        }

        var errorActive = false;

        /**
         * Event: Error-Generic
         * Fires a generic error handler.
         */
        $rootScope.$on('event:error-generic', function (e, args) {

            // Only transition once
            if (!errorActive)
            {
                $state.transitionTo('modalLayout.errorHandler', args);
                errorActive = true;
            }
        });


        /**
         * Event: Error-Unauthorised
         * Fires a 401 Unauthorised page. Auto-populates message if none has been passed.
         */
        $rootScope.$on('event:error-unauthorised', function (e, args) {

            // Defaults
            args            = (args !== undefined ? args          : {} );
            args.title      = ('title' in args ? args.title       : '401 - Unauthorised');
            args.message    = ('message' in args ? args.message   : 'You have insufficient permissions to access this page.');

            // Fire error handler
            $rootScope.$broadcast('event:error-generic', args);
        });


        /**
         * Event: Error-404
         * Fires a 404 File Not Found page. Auto-populates message is none has been passed.
         */
        $rootScope.$on('event:error-fileNotFound', function (e, args) {

            // Defaults
            args            = (args !== undefined ? args          : {} );
            args.title      = ('title' in args ? args.title       : '404 - File not found');
            args.message    = ('message' in args ? args.message   : 'This resource could not be found.');

            // Fire error handler
            $rootScope.$broadcast('event:error-generic', args);
        });
    })


    /**
     * Error Handler Controller
     * - Applies passed properties to the view
     * - Allows the user to "go back"
     */
    .controller('ErrorHandlerCtrl', function ErrorHandlerCtrl(titleService, $stateParams, $window) {
        var Ctrl = this;

        titleService.setTitle($stateParams.title);

        Ctrl.title = $stateParams.title;
        Ctrl.message = $stateParams.message;
        Ctrl.details = $stateParams.details;

        Ctrl.goBack = function() {
            $window.history.back();
        };
    })
;

