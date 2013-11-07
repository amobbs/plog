/**
 * Loading Handler
 * Count requests/responses. When there are active requests, start a timer.
 * If the timer goes above X seconds, send a broadcast for (loadingHandler.loading", true) (show dialog)
 * When the requests are completed, wait X milliseconds.
 * After X milliseconds, if there are no more requests, send a broadcast for (loadingHandler.loading", false) (close dialog)
 */
angular.module('loadingHandler', [])

    /**
     * LOADING_MIN_THRESHOLD: Minimum duration that must pass before the loading message is displayed
     * LOADING_MIN_DISPLAY_DURATION: Minimum time the loading message must be shown for. Prevents "flashes" of the loading screen.
     * LOADING_EXIT_GRACE: Grace period for Loading dialog dismissal. Prevents sequential requests resulting in the dialog being cleared.
     *
     * @integer - milliseconds
     */
    .constant('LOADING_ENTRY_GRACE', 1000)    // 1s
    .constant('LOADING_MIN_DISPLAY_DURATION', 1000)     // 1s
    .constant('LOADING_EXIT_GRACE', 100)// 0.2s

    /**
     * Request handler
     */
    .config(function($httpProvider, LOADING_ENTRY_GRACE, LOADING_MIN_DISPLAY_DURATION, LOADING_EXIT_GRACE) {
        var loading = false,            // Loading process active?
            loadingDisplayed = false,   // Loading panel visible?
            loadingGrace = false,       // Grace period for dialog still active?
            numberOfRequests = 0,       // Request # tracker
            entryTimer,                 // Timer for pending display
            graceTimer,                 // Timer for grace period; loader must be open for this period
            exitTimer,                  // Timer for pending hide

            /**
             * Increst requests
             * @param $rootScope
             * @param $timeout
             */
            increaseRequest = function($rootScope, $timeout)
            {
                numberOfRequests++;

                // Only instigate the timer if
                if (!loading)
                {
                    loading = true;

                    // If visible
                    if (!loadingDisplayed)
                    {
                        // Timer for entry activity
                        entryTimer = $timeout(function()
                        {
                            // Mark as displayed, and grace period active.
                            loadingDisplayed = true;
                            loadingGrace = true;

                            // Broadcast
                            $rootScope.$broadcast('loadingHandler.loading', loading);

                            // Instigate Exit Timer
                            // Hide the dialog after grace period, if no longer loading.
                            graceTimer = $timeout(function()
                            {
                                // Clear this timer
                                loadingGrace = false;

                                // If the dialog should be cleared (no load pending)
                                if (!loading)
                                {
                                    hideLoadingDialog($rootScope, $timeout);
                                }
                            }, LOADING_MIN_DISPLAY_DURATION);

                        }, LOADING_ENTRY_GRACE);
                    }
                }
            },

            /**
             * Decrease requests until empty.
             * @param $rootScope
             * @param $timeout
             */
            decreaseRequest = function($rootScope, $timeout) {
                if (loading)
                {
                    numberOfRequests--;
                    if (numberOfRequests === 0)
                    {
                        // cancel loader
                        loading = false;

                        // Clear loading timer if present
                        $timeout.cancel(entryTimer);

                        // If the dialog is still visible
                        if (loadingDisplayed)
                        {
                            // If the grace period has passed already
                            if (!loadingGrace)
                            {
                                // Clear loader
                                hideLoadingDialog($rootScope, $timeout);
                            }
                        }
                    }
                }
            };


            /**
             * Hide the loading dialog
             * - Instigates a timer with a grace period. When grace passes, loader will be cleared.
             * @param $rootScope
             * @param $timeout
             */
            hideLoadingDialog = function( $rootScope, $timeout )
            {
                // Restart the timer
                $timeout.cancel(exitTimer);

                // Create timer
                exitTimer = $timeout(function()
                {
                    // If the dialog should be cleared (no load pending)
                    if (!loading)
                    {
                        // Hide display
                        loadingDisplayed = false;
                        $rootScope.$broadcast('loadingHandler.loading', loading);
                    }
                }, LOADING_EXIT_GRACE);
            };


            /**
             * Test for if the Loader should be executed for this url
             * Specify regex here for URLS you wish to exclude from the global loader.
             */
            allowRequest = function (url)
            {
                var found = 0;
                found += RegExp('dashboards/[a-zA-Z0-9]+/widgets/[a-zA-Z0-9]+$').test(url);
                found += RegExp('search$').test(url);

                return !found;
            };


        /**
         * HTTP Interceptor
         * - Add count of HTTP activity when a request is commenced
         * - Remove count of HTTP activity when a request completes or fails.
         * Note: $timeout is passed as it's unavailable where the functions are defined.
         */
        $httpProvider.interceptors.push(['$q', '$rootScope', '$timeout', function($q, $rootScope, $timeout) {
            return {
                'request': function(config) {

                    if (allowRequest(config.url))
                    {
                        increaseRequest($rootScope, $timeout);
                    }

                    return config || $q.when(config);
                },
                'requestError': function(rejection) {

                    if (allowRequest(rejection.url))
                    {
                        decreaseRequest($rootScope, $timeout);
                    }

                    return $q.reject(rejection);
                },
                'response': function(response) {

                    if (allowRequest(response.url))
                    {
                        decreaseRequest($rootScope, $timeout);
                    }

                    return response || $q.when(response);
                },
                'responseError': function(rejection) {

                    if (allowRequest(rejection.url))
                    {
                        decreaseRequest($rootScope, $timeout);
                    }

                    return $q.reject(rejection);
                }
            };
        }]);
    })
;