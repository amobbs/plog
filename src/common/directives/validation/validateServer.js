/**
 * Server Validated field
 * Clears the validation error upon modification
 */

angular.module('validateServer', [])
    .directive('validateServer', [function () {
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, ctrl) {

                // On key up, clear the validation failure.
                elem.on('keyup', function ()
                {
                    scope.$apply(function ()
                    {
                        ctrl.$setValidity('validateServer', true);
                    });
                });
            }
        };
    }]);