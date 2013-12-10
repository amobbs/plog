/**
 * Log Date Directive
 * - Convert the ngModel entry for the Date (RFC 2822) to a simplified format
 */

angular.module('inputFieldLoginfo', [])
    .directive('inputFieldLoginfoDatetime', ['$templateCache', '$compile', '$filter', function ( $templateCache, $compile, $filter ) {

        /**
         * Linker.
         * - Process logDate field into date/time picker
         * @param scope
         * @param element
         * @param attrs
         */
        var linker = function( scope, element, attrs, ctrl ) {

            scope.$watch(function()
            {
                return scope.ngModel;
            },
            function(v)
            {
                // If value, translate to Ymd H:i:s
                if (v !== undefined)
                {
                    // Convert date to desired string
                    var date = new Date(v);
                    element[0].value = $filter('date')(date, 'dd/MM/yyyy HH:mm:ss');
                }

            });
        };


        /**
         * Establish Directive
         */
        return {
            restrict: "E",
            replace: true,
            template: '<input />',
            scope: {
                ngModel: '='
            },
            link: linker
        };
    }]);