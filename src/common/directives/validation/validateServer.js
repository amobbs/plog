/**
 * Server Validated field
 * Clears the validation error upon modification
 */

angular.module('validateServer', [])
    .directive('validateServer', [function () {
        return {
            link: function (scope, element, attrs, ctrl) {

                // Grab the form
                var form = element.inheritedData('$formController');

                // Discontinue if we don't have a form.
                if (!form)
                {
                    return;
                }

                // Set default attrs to avoid problems
                if (attrs.validateServer === undefined)
                {
                    attrs.validateServer = {};
                }

                // Watch for changes on the validate pointer
                scope.$watch(attrs.validateServer, function(errors)
                {

                    // A change to the validation variable will clear the validation errors.
                    angular.forEach(form.$serverError, function(error, i)
                    {
                        // Clear the element-level error
                        form[i].$setValidity('validateServer', true);
                    });

                    // Clear stores errors from the last try
                    form.$serverErrors = { };

                    // If there's no new error, we don't need to continue.
                    if (!errors)
                    {
                        form.$serverErrors = false;
                        return;
                    }

                    // Go through all the errors and populate the fields and the form.
                    angular.forEach(errors, function(error, i)
                    {
                        form.$serverErrors[i] = { $invalid: true, message: error };
                        form[i].$setValidity('validateServer', false);

                        // Form element listener
                        element.find("[name='"+i+"']").one('focus', function()
                        {
                            form[i].$setValidity('validateServer', true);
                        });
                    });
                });
            }
        };
    }]);