/**
 * Log Field Directive
 */

angular.module('inputFieldDuration', [])
    .directive('inputFieldDuration', ['$templateCache', '$compile', function ( $templateCache, $compile ) {

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

            // Prep scope
            scope.duration = {h:0, m:0, s:0};

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

                var seconds = scope.ngModel;

                // Split to units and populate field
                for (var i in unitList)
                {
                    units = Math.floor( seconds / unitList[i] );

                    // If split was OK
                    if (units)
                    {
                        seconds -= (units * unitList[i]);

                        // Populate field for this unit type
                        scope.duration[i] = units;
                    }
                    else
                    {
                        scope.duration[i] = 0;
                    }
                }

                denyWatch = false;
            });


            // On field change
            scope.$watch(function() { return ' '+scope.duration.h+scope.duration.m+scope.duration.s; }, function(duration)
            {
                // Abort if empty
                if (duration === undefined || denyWatch)
                {
                    return;
                }

                denyWatch = true;

                var seconds = 0;

                // Cycle units
                for (var i in unitList)
                {
                    // Calc
                    seconds += parseInt(scope.duration[i],10) * unitList[i];

                    // Pass to model
                    scope.ngModel = seconds;
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
            transclude: true,
            template: '<div>'+
                      '<input type="text" class="input-time" ng-model="duration.h" /> H &nbsp;'+
                      '<input type="text" class="input-time" ng-model="duration.m" /> M &nbsp;'+
                      '<input type="text" class="input-time" ng-model="duration.s" /> S &nbsp;'+
                      '</div>',
            link: linker,
            scope: {
                ngModel: '='
            }
        };
    }]);