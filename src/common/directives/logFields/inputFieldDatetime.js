/**
 * Log Date Directive
 * - Convert the ngModel entry for the Date (RFC 2822) to a simplified format
 */

angular.module('inputFieldDatetime', [])
    .directive('inputFieldDatetime', ['$templateCache', '$compile', '$filter', function ( $templateCache, $compile, $filter ) {


        // TODO: This linker
        // See $formatters: http://stackoverflow.com/questions/18061757/angular-js-and-html5-date-input-value-how-to-get-firefox-to-show-a-readable-d

        /**
         * Linker.
         * - Process logDate field into date/time picker
         * @param scope
         * @param element
         * @param attrs
         */
        var linker = function( scope, element, attrs, ctrl ) {

            // Prevent watch being triggered during field update, and causing recusive watch
            var denyWatch = false;

            // On source change
            scope.$watch(function() { return scope.ngModel; }, function(value) {

                // Abort if empty
                if (value === undefined || denyWatch)
                {
                    return;
                }

                denyWatch = true;

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

                denyWatch = false;
            });

            // On editor change
            scope.$watch(function() { return element[0].value; }, function(value) {

                // Abort if empty
                if (value === undefined || denyWatch)
                {
                    return;
                }

                denyWatch = true;

                // Fetch the data from the model
                var date = new Date(scope.ngModel);

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
                    scope.ngModel = $filter('date')(date, 'EEE, MM MMM yyyy hh:mm:ss Z');
                    console.log(scope.ngModel);
                }

                denyWatch = false;
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