/**
 * Log Field Directive
 */

angular.module('inputFieldDuration', [])
    .directive('input', ['$templateCache', '$compile', '$parse', function ( $templateCache, $compile, $parse ) {

        // Unit types
        var unitList = {
            'h': 3600,
            'm': 60,
            's': 1
        };

        /**
         * Linker.
         * - Process durationin Hrs Mins Secs fields.
         * @param scope
         * @param element
         * @param attrs
         */
        var linker = function( scope, element, attrs, ctrl ) {

            // Skip non-match types
            if (attrs.inputduration === undefined)
            {
                return;
            }

            // Fetch model
            var model = $parse(attrs.ngModel);

            // Apply a placeholder for duration value
            if (model.$sharedValue === undefined)
            {
                model.$sharedValue = {h:0, m:0, s:0};
            }


            /**
            * Hour Parser
            * Convert from View to Model
            * @param value
            */
            var hourParser = function(value)
            {
                return calculateSeconds('h', value, model.$sharedValue);
            };

            /**
             * Hour Formatter. Convert from Model to View
             * @param value
             */
            var hourFormatter = function(value)
            {
                return calculatePart('h', value, model.$sharedValue);
            };


            /**
            * Minute Parser
            * Convert from View to Model
            * @param value
            */
            var minuteParser = function(value)
            {
                return calculateSeconds('m', value, model.$sharedValue);
            };

            /**
             * Minute Formatter. Convert from Model to View
             * @param value
             */
            var minuteFormatter = function(value)
            {
                return calculatePart('m', value, model.$sharedValue);
            };

            /**
            * Second Parser
            * Convert from View to Model
            * @param value
            */
            var secondParser = function(value)
            {
                return calculateSeconds('s', value, model.$sharedValue);
            };

            /**
             * Second Formatter. Convert from Model to View
             * @param value
             */
            var secondFormatter = function(value)
            {
                return calculatePart('s', value, model.$sharedValue);
            };


            /**
             * Calculate the seconds from the H/M/S supplied
             * obj contains an object with updated reference.
             * A statically available object contains the H/M/S
             * @param   obj
             * @param   obj
             */
            var calculateSeconds = function( part, value, source)
            {
                var total = 0;

                // Update seconds in each section
                source[part] = parseInt(value, 10);

                if ( isNaN(source[part]) )
                {
                    source[part] = 0;
                }

                // Update the model with the seconds
                for (var i in source)
                {
                    // Convert to seconds
                    total += parseInt(source[i] * unitList[i], 10);
                }

                // Return summary for model
                return total;
            };


            /**
             * Calculate the $part from the $seconds.
             * h = hour
             * m = minute
             * s = second
             * @param part
             */
            var calculatePart = function( part, seconds, source )
            {
                if ( isNaN(seconds) || seconds === undefined)
                {
                    seconds = 0;
                }

                // Convert to units
                // Also write to storage
                source.h = Math.floor(seconds / unitList.h);
                seconds %= unitList.h;
                source.m = Math.floor(seconds / unitList.m);
                source.s = seconds % unitList.m;

                return parseInt(source[part], 10);
            };


            /**
             * Apply parser/formatter
             */

            // Only apply to "date" type
            if (attrs.inputduration == 'h')
            {
                ctrl.$parsers.unshift(hourParser);
                ctrl.$formatters.unshift(hourFormatter);
            }
            // Only apply to "time" type
            else if (attrs.inputduration == 'm')
            {
                ctrl.$parsers.unshift(minuteParser);
                ctrl.$formatters.unshift(minuteFormatter);
            }
            // Only apply to "time" type
            else if (attrs.inputduration == 's')
            {
                ctrl.$parsers.unshift(secondParser);
                ctrl.$formatters.unshift(secondFormatter);
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
            require: '?ngModel',
            link: linker
        };
    }]);