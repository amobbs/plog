/**
 * Log Date Directive
 * - Convert the ngModel entry for the Date (RFC 2822) to a simplified format
 */

angular.module('inputFieldDatetime', [])
    .directive('input', ['$templateCache', '$compile', '$filter', function ( $templateCache, $compile, $filter ) {

        /**
         * Linker.
         * - Process element into field types
         * @param scope
         * @param element
         * @param attrs
         * @param ctrl
         */
        var linker = function( scope, element, attrs, ctrl ) {

            // Skip non-match types
            if (attrs.datetime === undefined)
            {
                return;
            }


            /**
             * Date Parser
             * Translate the value from YYYY-MM-DD to RFC2822
             * @param value
             */
            var dateParser = function(value)
            {
                var date = new Date( ctrl.$modelValue );
                var dateParts = value.split('/');
                var newDate = new Date(dateParts[2]+'-'+dateParts[1]+'-'+dateParts[0]+' 00:00:00');

                // Fix the source date if not set, undefined, etc
                if (isNaN( date.getTime()))
                {
                   date = new Date('0001-01-01 00:00:00');
                }

                // Update data
                newDate.setHours( date.getHours() );
                newDate.setMinutes( date.getMinutes() );
                newDate.setSeconds( date.getSeconds() );

                // Apply, or fail and return original
                if (!isNaN( newDate.getTime()))
                {
                    ctrl.$setValidity('date', true);
                    return $filter('date')(newDate, 'EEE, dd MMM yyyy hh:mm:ss Z');
                }
                else
                {
                    ctrl.$setValidity('date', false);
                    return $filter('date')(date, 'EEE, dd MMM yyyy hh:mm:ss Z');
                }
            };


            /**
             * Date Formatter
             * Translate the value from RFC2822 to YYYY-MM-DD
             * @param value
             */
            var dateFormatter = function(value)
            {
                var date = new Date(value);

                if (!isNaN( date.getTime()))
                {
                    return $filter('date')(date, 'dd/MM/yyyy');
                }
                else
                {
                    return null;
                }
            };


            /**
             * Parser
             * Translate the value from HH:MM:SS to RFC2822
             * @param value
             */
            var timeParser = function(value)
            {
                var date = new Date( ctrl.$modelValue );
                var newDate = new Date('0001-01-01 '+value);

                // Fix the source date if not set, undefined, etc
                if (isNaN( date.getTime()))
                {
                    date = new Date();
                }

                // Update data
                newDate.setDate( date.getDate() );
                newDate.setMonth( date.getMonth() );
                newDate.setYear( date.getFullYear() );

                // Apply, or error
                if (!isNaN( date.getTime()))
                {
                    ctrl.$setValidity('time', true);
                    return $filter('date')(newDate, 'EEE, dd MMM yyyy hh:mm:ss Z');
                }
                else
                {
                    ctrl.$setValidity('time', false);
                    return $filter('date')(date, 'EEE, dd MMM yyyy hh:mm:ss Z');
                }



            };


            /**
             * Formatter
             * Translate the value from RFC2822 to HH:MM:SS
             * @param value
             */
            var timeFormatter = function(value)
            {
                var date = new Date(value);

                if (!isNaN( date.getTime()))
                {
                    return $filter('date')(date, 'hh:mm:ss');
                }
                else
                {
                    return null;
                }
            };


            /**
             * Apply parser/formatter
             */

            // Only apply to "date" type
            if (attrs.datetime == 'date')
            {
                ctrl.$parsers.unshift(dateParser);
                ctrl.$formatters.unshift(dateFormatter);
            }
            // Only apply to "time" type
            else if (attrs.datetime == 'time')
            {
                ctrl.$parsers.unshift(timeParser);
                ctrl.$formatters.unshift(timeFormatter);
            }
            // Abort this directives actions
            else
            {
                return;
            }
        };


        /**
         * Establish Directive
         */
        return {
            restrict: "E",
            require: "?ngModel",
            link: linker
        };
    }]);