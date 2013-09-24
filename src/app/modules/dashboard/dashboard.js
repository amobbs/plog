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
        'titleService',
        'ui.bootstrap'
    ])


    .config(function(stateHelperProvider) {
        stateHelperProvider.addState('mainLayout.dashboardList', {
            url: '/dashboard',
            views: {
                "main@mainLayout": {
                    controller: 'DashboardCtrl',
                    templateUrl: 'modules/dashboard/dashboard.tpl.html'
                }
            },
            resolve: {
                source: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    // Fetch dashboard
                    var deferred = $q.defer();
                    Restangular.one('dashboards').get().then(function(data) {
                        deferred.resolve(data);
                    });

                    return deferred.promise;
                }]
            }
        });
        stateHelperProvider.addState('mainLayout.dashboard', {
            url: '/dashboard/{dashboard_id:[0-9a-z]+}',
            views: {
                "main@mainLayout": {
                    controller: 'DashboardCtrl',
                    templateUrl: 'modules/dashboard/dashboard.tpl.html'
                }
            },
            resolve: {
                source: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    // Fetch dashboard
                    var deferred = $q.defer();
                    Restangular.one('dashboards', $stateParams.dashboard_id).get().then(function(data) {
                        deferred.resolve(data);
                    });

                    return deferred.promise;
                }]
            }
        });
    })


    /**
     * Dashboard Controller
     */
    .controller( 'DashboardCtrl', function DashboardController( $scope, $http, $window, $location, $modal, titleService, Restangular, source) {
        titleService.setTitle( 'Dashboard' );

        $scope.id = '523b9faf09cc5e623f8b51da';
        $scope.dashboard = source.dashbaord;
        $scope.favourites = source.favourites;

        $(".widget-area").shapeshift({
            align: 'left'
        });

        $scope.exportReport = function() {
            window.location = 'http://local.preslog/api/dashboards/' + $scope.id + '/export';
        };

        $scope.openCreateModal = function () {
            var createModal = $modal.open({
                templateUrl: 'modules/dashboard/createModal/createModal.tpl.html',
                controller: 'CreateModalCtrl'
            });


            createModal.result.then(function(name) {
                var dashboard = Restangular.all('dashboards');
                dashboard.post({'name' : name})
                    .then(function(result) {
                        $scope.dashboard = result.dashboard;
                        $scope.id = result.dashboard._id.$id;
                        $location.path('/dashboard/' + $scope.id);
                    });
            });
        };



        $http.get("/assets/testchart.json").success(function(data) {
            $scope.basicAreaChart = data;
        });

//        $http.get("/assets/testpie.json").success(function(data) {
//            $scope.chart2 = data;
//        });

//        $http.get("/api/dashboards").success(function(data) {
//            $scope.chart2 = JSON.parse(data.data);
//        });
    })

    .controller( 'CreateModalCtrl', function CreateModalController($scope, $modalInstance) {
        $scope.ok = function() {
            console.log($scope.hats);
            $modalInstance.close($scope.hats);
        };

        $scope.cancel = function() {
            $modalInstance.dismiss('cancel');
        };
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