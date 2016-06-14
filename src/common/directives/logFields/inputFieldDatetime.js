/**
 * Log Date Directive
 * - Convert the ngModel entry for the Date (RFC 2822) to a simplified format
 */

angular.module('inputFieldDatetime', [])
    .directive('input', ['$templateCache', '$compile', '$filter', '$timeout', function ( $templateCache, $compile, $filter, $timeout ) {

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
             * DateTime Parser
             * Translate the value from YYYY-MM-DD HH:MM:SS to RFC2822
             * @param value
             */
            var datetimeParser = function(value)
            {
                var date = new Date( ctrl.$modelValue );
                var dateSplit = value.split(' ');
                var dateParts = dateSplit[0].split('/');
                var timeParts = dateSplit[1].split(':');

                // Bugfix: if the year is two digit, prefix with current century
                if (dateParts[2].length <= 2)
                {
                    dateParts[2] = "20"+dateParts[2];
                }
                // Bugfix: Month is 0-11
                dateParts[1] = (parseInt(dateParts[1], 10) -1);

                var newDate = new Date();
                newDate.setDate(dateParts[0]);
                newDate.setMonth(dateParts[1]);
                newDate.setYear(dateParts[2]);
                newDate.setHours(timeParts[0]);
                newDate.setMinutes(timeParts[1]);
                newDate.setSeconds(timeParts[2]);

                // Fix the source date if not set, undefined, etc
                if (isNaN( date.getTime()))
                {
                    date = new Date('0001-01-01 00:00:00');
                }

                // Apply, or fail and return original
                if (!isNaN( newDate.getTime()))
                {
                    ctrl.$setValidity('date', true);
                    return $filter('date')(newDate, 'EEE, dd MMM yyyy HH:mm:ss Z');
                }
                else
                {
                    ctrl.$setValidity('date', false);
                    return $filter('date')(date, 'EEE, dd MMM yyyy HH:mm:ss Z');
                }
            };


            /**
             * DateTime Formatter
             * Translate the value from RFC2822 to YYYY-MM-DD HH:MM:SS
             * @param value
             */
            var datetimeFormatter = function(value)
            {
                var date = new Date(value);

                if (!isNaN( date.getTime()))
                {
                    var x =  $filter('date')(date, 'dd/MM/yyyy HH:mm:ss');
                    return x;
                }
                else
                {
                    return null;
                }
            };


            /**
             * Date Parser
             * Translate the value from YYYY-MM-DD to RFC2822
             * @param value
             */
            var dateParser = function(value)
            {
                var date = new Date( ctrl.$modelValue );
                var dateParts = value.split('/');

                // Bugfix: if the year is two digit, prefix with current century
                if (dateParts[2].length <= 2)
                {
                    dateParts[2] = "20"+dateParts[2];
                }
                // Bugfix: Month is 0-11
                dateParts[1] = (parseInt(dateParts[1], 10) -1);

                var newDate = new Date('0001-01-01 00:00:00');
                newDate.setDate(dateParts[0]);
                newDate.setMonth(dateParts[1]);
                newDate.setYear(dateParts[2]);

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
                    return $filter('date')(newDate, 'EEE, dd MMM yyyy HH:mm:ss Z');
                }
                else
                {
                    ctrl.$setValidity('date', false);
                    return $filter('date')(date, 'EEE, dd MMM yyyy HH:mm:ss Z');
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
                var timeParts = value.split(':');

                //we will only accept the time format if it's exactly how we want it
                var res = /[0-2][0-9]:[0-5][0-9]:[0-5][0-9]/.test(value);
                if (!res) {
                    ctrl.$setValidity('time', false);
                    ctrl.$valid = false;
                    ctrl.$clientError = 'Time must be valid in the format of hh:mm:ss';
                    return $filter('date')(date, 'EEE, dd MMM yyyy HH:mm:ss Z');
                }

                var hours = parseInt(timeParts[0], 10);
                var mins = parseInt(timeParts[1], 10);
                var secs = parseInt(timeParts[2], 10);

                //check they don't extend the limits, otherwise they'll change the date
                if (hours >= 24 || mins >= 60 || secs >= 60){
                    ctrl.$setValidity('time', false);
                    ctrl.$valid = false;
                    ctrl.$clientError = 'Time must be valid in the format of hh:mm:ss';
                    return $filter('date')(date, 'EEE, dd MMM yyyy HH:mm:ss Z');
                }

                date.setHours(hours);
                date.setMinutes(mins);
                date.setSeconds(secs);

                // Fix the source date if not set, undefined, etc
                if (isNaN( date.getTime()))
                {
                    date = new Date();
                }
                ctrl.$clientError = undefined;
                // Apply, or error
                ctrl.$setValidity('time', true);
                return $filter('date')(date, 'EEE, dd MMM yyyy HH:mm:ss Z');
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
                    return $filter('date')(date, 'HH:mm:ss');
                }
                else
                {
                    return null;
                }
            };


            /**
             * Apply parser/formatter
             */

            // Only apply to "datetime" type
            if (attrs.datetime == 'datetime')
            {
                ctrl.$parsers.unshift(datetimeParser);
                ctrl.$formatters.unshift(datetimeFormatter);
            }
            // Only apply to "date" type
            else if (attrs.datetime == 'date')
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


            /**
             * Apply datepicker if specified
             */
            if (attrs.datetimeDatepicker !== undefined)
            {
                // Attach datepicker next frame. Avoid problems with dynamic forms.
                $timeout( function()
                {
                    // Remove any picker that exists
                    element.datepicker('destroy');

                    // Apply picker
                    element.datepicker({
                        dateFormat:'dd/mm/yy',
                        constrainInput:true,
                        shortYearCutoff: 0,
                        onSelect:function (value, picker)
                        {
                            scope.$apply(function() {
                                ctrl.$setViewValue(value);
                                element.blur();
                            });
                        }
                    });
                });
            }
        };


        /**
         * Establish Directive
         */
        return {
            restrict: "EA",
            require: "?ngModel",
            link: linker,
            transclude: true
        };
    }]);