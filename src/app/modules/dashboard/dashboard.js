/**
 * Each section of the site has its own module. It probably also has
 * submodules, though this boilerplate is too simple to demonstrate it. Within
 * `src/app/home`, however, could exist several additional folders representing
 * additional modules that would then be listed as dependencies of this one.
 * For example, a `note` section could have the submodules `note.create`,
 * `note.delete`, `note.edit`, etc.
 *
 * Regardless, so long as dependencies are managed correctly, the build process
 * will automatically take take of the rest.
 *
 * The dependencies block here is also where component dependencies should be
 * specified, as shown below.
 */
angular.module( 'Preslog.dashboard', [
        'titleService'
    ])


    .config(function(stateHelperProvider) {
        stateHelperProvider.addState('mainLayout.dashboard', {
            url: '/dashboard',
            views: {
                "main@mainLayout": {
                    controller: 'DashboardCtrl',
                    templateUrl: 'modules/dashboard/dashboard.tpl.html'
                }
            }
        });
    })


    /**
     * Dashboard Controller
     */
    .controller( 'DashboardCtrl', function DashboardController( $scope, $http, $window, titleService ) {
        titleService.setTitle( 'Dashboard' );

        $scope.id = 1;

        $scope.exportReport = function() {
            window.location = 'http://local.preslog/api/dashboards/' + $scope.id + '/export';
        };

        $http.get("/assets/testchart.json").success(function(data) {
            $scope.basicAreaChart = data;
        });

//        $http.get("/assets/testpie.json").success(function(data) {
//            $scope.chart2 = data;
//        });

        $http.get("/api/dashboards").success(function(data) {
            $scope.chart2 = JSON.parse(data.data);
        });
    })


    .directive('chart', function () {
        return {
            restrict: 'E',
            template: '<div></div>',
            scope: {
                chartData: "=value"
            },
            transclude:true,
            replace: true,

            link: function (scope, element, attrs) {
                var chartsDefaults = {
                    chart: {
                        renderTo: element[0],
                        type: attrs.type || null,
                        height: attrs.height || null,
                        width: attrs.width || null
                    }
                };

                //Update when charts data changes
                scope.$watch(function() { return scope.chartData; }, function(value) {
                    if(!value) {
                        return;
                    }
                    // We need deep copy in order to NOT override original chart object.
                    // This allows us to override chart data member and still the keep
                    // our original renderTo will be the same
                    var deepCopy = true;
                    var newSettings = {};
                    $.extend(deepCopy, newSettings, chartsDefaults, scope.chartData);
                    var chart = new Highcharts.Chart(newSettings);
                });
            }
        };
    });