/**
 * Log Date Directive
 * - Convert the ngModel entry for the Date (RFC 2822) to a simplified format
 */

angular.module('inputFieldDatetime', [])
    .directive('inputFieldDatetime', ['$templateCache', '$compile', '$filter', function ( $templateCache, $compile, $filter ) {

        /**
         * Linker.
         * - Process logDate field into date/time picker
         * @param scope
         * @param element
         * @param attrs
         */
        var linker = function( scope, element, attrs, ctrl ) {

            scope.denyWatch = false;

            // Deny watched of the model when the user is changing the data
            element.bind('focus', function() { scope.denyWatch = true; });
            element.bind('blur',  function() { scope.denyWatch = false; });

            // On source change
            scope.$watch(function() { return scope.ngModel; }, function(value) {

                // Abort if empty
                if (value === undefined || scope.denyWatch)
                {
                    return;
                }

                // Convert to object
                var date = new Date(value);

                // Date
                if (scope.part == 'date')
                {
                    element[0].value =  $filter('date')(date, 'yyyy-MM-dd');
                }

                // Time
                else if (scope.part == 'time')
                {
                    element[0].value =  $filter('date')(date, 'hh:mm:ss');
                }

            });

            // On editor change
            scope.$watch(function() { return element[0].value; }, function(value) {

                // Abort if empty
                if (value === undefined)
                {
                    return;
                }

                // Fetch the data from the model
                var date = new Date(scope.ngModel);
                var newDate = null;

                // if part == date, update the Date section only.
                if (scope.part == 'date')
                {
                    newDate = new Date(value+' 00:00:00');
                    date.setDate( newDate.getDate() );
                    date.setMonth( newDate.getMonth() );
                    date.setYear( newDate.getFullYear() );
                }

                // if part == time, update the Time section only.
                else if (scope.part == 'time')
                {
                    newDate = new Date('0001-01-01 '+value);
                    date.setHours( newDate.getHours() );
                    date.setMinutes( newDate.getMinutes() );
                    date.setSeconds( newDate.getSeconds() );
                }

                // Only update the data if the date is a real date
                if (!isNaN( date.getTime()))
                {
                    // RFC 2822
                    scope.ngModel = $filter('date')(date, 'EEE, dd MMM yyyy hh:mm:ss Z');
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
                part: '@',
                ngModel: '='
            },
            link: linker
        };
    }]);