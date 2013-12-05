/**
 * Click Wait Directive
 * Add the ClickWait functionality to any button.
 * The function passed to Click Wait [click-wait="func()"] will be executed when the button is pressed, and the button will be disabled.
 * The function should return a Promise. when the promise is fulfilled (reject or resolve) the button will be enabled.
 *
 */
angular.module('clickWait', [])
    .directive('clickWait', [ '$parse', function($parse) {

            // Linker
            var link = function(scope, element, attr)
            {
                // Get the function to be instigated on click
                var triggerFunction = $parse(scope.clickWait);

                /**
                 * Task to perform when the precess is made active
                 * eg. Button is clicked
                 */
                var onActivateAction = function()
                {
                    // Disable button
                    attr.$set('disabled', true);

                    // Swap icons
                    element.find('.click-wait-loader').show();
                    element.find('.click-wait-static').hide();
                };


                /**
                 * Task to perform when the press is made inactive
                 * eg. Promise is fulfilled.
                 */
                var onDeactivateAction = function()
                {
                    // enable button
                    attr.$set('disabled', false);

                    // Swap icons
                    element.find('.click-wait-loader').hide();
                    element.find('.click-wait-static').show();
                };


                /**
                 * On-Click for the submit button
                 * Instigates the process, and attaches .then to the promise
                 */
                element.on('click', function(event)
                {
                    // This has a binding variable?
                    if (scope.clickWaitBind === undefined)
                    {
                        scope.clickWaitBind = false;
                    }

                    // Run the request function in the parent scope
                    scope.$parent.$apply(function()
                    {
                        // Change the shared watch variable
                        scope.clickWaitBind = true;

                        // Execute the request function
                        var promise = triggerFunction(scope.$parent, { $event: event });

                        // If a promise is returned, we can do things after that promise.
                        if (promise !== undefined)
                        {
                            promise.then(function()
                            {
                                scope.clickWaitBind = false;
                            }, function()
                            {
                                scope.clickWaitBind = false;
                            });
                        }
                    });
                });

                /**
                 * Hide loader icon if supplied
                 */
                element.find('.click-wait-loader').hide();


                /**
                 * Watch of the Toggle variable
                 * Ensures we can pair buttons together, that might repeat on the page.
                 */
                scope.$watch('clickWaitBind', function()
                {
                    if (scope.clickWaitBind === true)
                    {
                        onActivateAction();
                    }
                    else
                    {
                        onDeactivateAction();
                    }
                });
            };


            /**
             * Directive
             */
            return {
                priority: 100,
                restrict: 'A',
                scope: {
                    clickWait: '&',
                    clickWaitBind: '=?'
                },
                require: '?ngModel',
                link: link
            };
        }
    ]);