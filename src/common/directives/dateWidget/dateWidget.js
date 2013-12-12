/**
 * Date Widget Directive
 * change date range on dashboard
 */

angular.module('dateWidget', [])
    .directive('dateWidget', [function () {
        return {
            restrict: "E",
            replace: true,
            transclude: true,
            templateUrl: 'modules/dashboard/widgets/date/dateWidget.tpl.html',
            scope: {
                session: '='
            },
            link: function( scope, element, attrs, ctrl ) {
                scope.startOfDay = function(start)
                {
                    var s = new Date(start);
                    s.setHours(0);
                    s.setMinutes(0);
                    s.setSeconds(0);

                    return s;
                };

                scope.endOfDay = function(end)
                {
                    var e = new Date(end);
                    e.setHours(23);
                    e.setMinutes(59);
                    e.setSeconds(59);

                    return e;
                };

                scope.forward = function()
                {
                    var start = scope.startOfDay(new Date(scope.session.start));
                    var end = scope.endOfDay(new Date(scope.session.end));

                    switch (scope.session.period)
                    {
                        case 'Week':
                            start.setDate(start.getDate() + 7); //take the current start and add 7 days
                            end = scope.endOfDay(new Date(start)); //get a copy of start but with time set to end
                            end.setDate(start.getDate() + 6); //set date to now plus 6 so it is at the very end of the week
                            break;
                        case 'Day':
                            start.setDate(start.getDate() + 1); //get now and add a day
                            end = scope.endOfDay(new Date(start)); //get a copy of start but time set to end of day
                            end.setDate(start.getDate()); //send date to same as start but time is at end
                            break;
                        case 'Month':
                            start.setMonth(start.getMonth() + 1); //add 1 to start month
                            end = scope.endOfDay(new Date(start)); //get copy of start but time set to end of day
                            end.setMonth(start.getMonth() + 1); //take the new start month and add one
                            end.setDate(start.getDate() - 1); //take off a day so it is on last day of that month's period
                            break;
                    }

                    scope.session.start = start;
                    scope.session.end = end;
                };

                /**
                 * take the current start date and change the end date to be 1 unit of the new period selected (eg: +1 week for week)
                 */
                scope.changePeriod = function()
                {

                    var start = scope.startOfDay(new Date(scope.session.start));
                    var end = new Date(start);


                    switch (scope.session.period)
                    {
                        case 'Week':
                            end.setDate(start.getDate() + 6);
                            end = scope.endOfDay(end);
                            break;
                        case 'Day':
                            end = scope.endOfDay(start);
                            break;
                        case 'Month':
                            end.setMonth(start.getMonth() + 1);
                            end.setDate(start.getDate() - 1);
                            end = scope.endOfDay(end);
                            break;
                    }

                    scope.session.start = start;
                    scope.session.end = scope.endOfDay(end);
                };

                scope.backward = function()
                {

                    var start = scope.startOfDay(new Date(scope.session.start));
                    var end = scope.endOfDay(new Date(scope.session.end));

                    switch (scope.session.period)
                    {
                        case 'Week':
                            start.setDate(start.getDate() - 7); //take the current start and add 7 days
                            end = scope.endOfDay(new Date(start)); //get a copy of start but with time set to end
                            end.setDate(start.getDate() + 6); //set date to now plus 6 so it is at the very end of the week
                            break;
                        case 'Day':
                            start.setDate(start.getDate() - 1); //get now and add a day
                            end = scope.endOfDay(new Date(start)); //get a copy of start but time set to end of day
                            end.setDate(start.getDate()); //send date to same as start but time is at end
                            break;
                        case 'Month':
                            start.setMonth(start.getMonth() - 1); //add 1 to start month
                            end = scope.endOfDay(new Date(start)); //get copy of start but time set to end of day
                            end.setMonth(start.getMonth() + 1); //take the new start month and add one
                            end.setDate(start.getDate() - 1); //take off a day so it is on last day of that month's period
                            break;
                    }

                    scope.session.start = start;
                    scope.session.end = end;
                };
            }
        };
    }]
)
;
