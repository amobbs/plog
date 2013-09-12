angular.module('errorHandler', [])
    .config(function(stateHelperProvider, $urlRouterProvider) {


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
            var $state = $injector.get('$state');

            $state.transitionTo('modalLayout.errorHandler', {
                title: "404 - Not Found",
                message: "This page does not exist",
                details: ""
            });
        });

    })
    .config(function($httpProvider) {

        /**
         * Response Interceptor
         * Handles 403, 404, 500, 503 and 0 errors from HTTP requests.
         * Changes to the error state on failure
         */
        $httpProvider.responseInterceptors.push(['$q', '$injector', function($q, $injector) {
            return function (promise) {
                return promise.then(function(response) {
                    // Nothing needed here - request was OK
                    return response;
                }, function(response) {
                    var $state = $injector.get('$state'),
                        $filter = $injector.get('$filter'),
                        errors = {
                            403: '403 - Access Forbidden',
                            404: '404 - Not Found',
                            500: '500 - Internal Server Error',
                            503: '503 - Gateway Time-out',
                            0: 'CORS Error - API Not Accepting Request'
                        };

                    // Handle the specified error types
                    if (response.status in errors) {
                        $state.transitionTo('modalLayout.errorHandler', {
                            title: errors[response.status],
                            message: response.data.message,
                            details: $filter('json')(response)
                        });
                    }

                    // Reject the deferred request
                    return $q.reject(response);
                });
            };
        }]);
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

