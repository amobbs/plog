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
                scope.forward = function()
                {
                    var start = new Date(scope.session.start);
                    var end = new Date(scope.session.end);

                    switch (scope.session.period)
                    {
                        case 'Week':
                            start.setDate(start.getDate() + 7);
                            end = new Date(start);
                            end.setDate(start.getDate() + 7);
                            break;
                        case 'Day':
                            start.setDate(start.getDate() + 1);
                            end = new Date(start);
                            end.setDate(start.getDate() + 1);
                            break;
                        case 'Month':
                            start.setMonth(start.getMonth() + 1);
                            end = new Date(start);
                            end.setMonth(start.getMonth() + 1);
                            break;
                    }

                    scope.session.start = start;
                    scope.session.end = end;
                };

                scope.backward = function()
                {
                    var start = new Date(scope.session.start);
                    var end = new Date(scope.session.end);

                    switch (scope.session.period)
                    {
                        case 'Week':
                            start.setDate(start.getDate() - 7);
                            end = new Date(start);
                            end.setDate(start.getDate() + 7);
                            break;
                        case 'Day':
                            start.setDate(start.getDate() - 1);
                            end = new Date(start);
                            end.setDate(start.getDate() + 1);
                            break;
                        case 'Month':
                            start.setMonth(start.getMonth() - 1);
                            end = new Date(start);
                            end.setMonth(start.getMonth() + 1);
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
