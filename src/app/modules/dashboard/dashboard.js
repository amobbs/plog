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
        'ui.bootstrap',
        'Preslog.dashboard.dashboardModal',
        'Preslog.dashboard.widgetModal'
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
                    Restangular.one('dashboards', '5244e28309cc5eeb498b4567').get().then(function(data) {
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
    .controller( 'DashboardCtrl', function DashboardController( $scope, $http, $window, $location, $timeout, $modal, titleService, Restangular, source) {
        titleService.setTitle( 'Dashboard' );

        $scope.id = source.dashboard.id; //mongoid for dashboard
        $scope.dashboard = source.dashboard;
        $scope.favourites = source.favourites;
        $scope.clients = source.clients;

        $scope.refreshTimers= [];
        $scope.name = '';

        //move the widgets around
        $scope.sortableOptions = {
            placeholder: 'placeholder',
            items: '.widget',
            revert: 150,
            tolerance: 'pointer',
            cursorAt: {
                left: 10,
                top: 10
            },
            start: function(event, ui) {
                if (ui.item.hasClass('col3')) {
                    ui.placeholder.css('width', '98%');
                }
            },
            change: function(event, ui) {
                ui.placeholder.before('\n').after('\n');
            },
            stop: function(event, ui) {
                ui.item.before('\n').after('\n');
            },
            update: function(event, ui) {
                var order = $(event.target).sortable("toArray");

                for(var i = 0; i < order.length; i++) {
                   for(var w = 0; w < $scope.dashboard.widgets.length; w++) {
                       if ($scope.dashboard.widgets[w]._id == order[i]) {
                           $scope.dashboard.widgets[w].order = i;
                       }
                   }
                }

                Restangular.one('dashboards', $scope.id)
                    .post('', {'widgets': $scope.dashboard.widgets})
                    .then(function(data) {
                        console.log('widget sort saved');
                    }
                );
            }
        };

        $scope.deleteWidget = function(widgetId) {
            Restangular.one('dashboards', $scope.id)
                .one('widgets', widgetId)
                .remove()
                .then(function (result){
                    console.log(result);
                    for(var index = 0; index < $scope.dashboard.widgets.length; index++) {
                        if ($scope.dashboard.widgets[index]._id == widgetId) {
                            $scope.dashboard.widgets.splice(index, 1);
                            $scope.updateRefreshTimer(widgetId, 0);
                            break;
                        }
                    }
                });
        };

        $scope.refreshCallback = function(widgetId) { return function() { $scope.refreshWidget(widgetId); }; };
        $scope.startWidgetRefresh = function() {
            for(var wId in $scope.dashboard.widgets) {
                var widget = $scope.dashboard.widgets[wId];
                if (widget.details.refresh && widget.details.refresh > 0) {
                    $scope.setRefreshTimer(widget._id, widget.details.refresh);
                }
            }
        };

        $scope.setRefreshTimer = function(widgetId, interval) {
            var promise = $timeout($scope.refreshCallback(widgetId), (interval * 1000));

            $scope.refreshTimers.push({
                widgetId: widgetId,
                promise: promise
            });
        };

        $scope.updateRefreshTimer = function(widgetId, newInterval) {
            for(var timeoutId in $scope.refreshTimers) {
                var timeout = $scope.refreshTimers[timeoutId];
                if (timeout.widgetId == widgetId) {
                    $timeout.cancel(timeout.promise);
                    if (newInterval > 0) {
                        $scope.setRefreshTimer(timeout.widgetId, newInterval);
                        $scope.refreshTimers.splice(timeoutId, 1);
                    }
                    //we found the timer so nothing left to do
                    return;
                }
            }

            //add new times
            $scope.setRefreshTimer(widgetId, newInterval);
        };

        $scope.refreshWidget = function(widgetId) {
            Restangular.one('dashboards', $scope.id)
                .one('widgets', widgetId)
                .get()
                .then(function(result) {
                    if (result && result.widget) {
                        for(var wId in $scope.dashboard.widgets) {
                            var widget = $scope.dashboard.widgets[wId];
                            if (widget._id == widgetId) {
                                $scope.dashboard.widgets[wId] = result.widget;
                                $scope.updateRefreshTimer(widgetId, result.widget.details.refresh);
                                break;
                            }
                        }
                    }
                });
        };

        //download a docx version of this dashboard
        $scope.exportReport = function() {
            window.location = 'http://local.preslog/api/dashboards/' + $scope.id + '/export';
        };

        //create new dashboard
        $scope.openCreateModal = function () {
            var createModal = $modal.open({
                templateUrl: 'modules/dashboard/dashboardModal/createDashboardModal.tpl.html',
                controller: 'DashboardModalCtrl',
                resolve: {
                    name: function() { return ''; },
                    isCreate: function() { return true; },
                    clients: function() { return $scope.splitClients(); }
                }
            });
            createModal.result.then(function(name) {
                var dashboard = Restangular.all('dashboards');
                dashboard.post({'name' : name})
                    .then(function(result) {
                        $scope.dashboard = result.dashboard;
                        $scope.id = result.dashboard.id;
                        $location.path('/dashboard/' + $scope.id);
                    });
            });
        };

        //edit dashboard
        $scope.openEditDashboardModal = function() {
            var editModal = $modal.open({
                templateUrl: 'modules/dashboard/dashboardModal/createDashboardModal.tpl.html',
                controller: 'DashboardModalCtrl',
                resolve: {
                    name: function() { return $scope.dashboard.name; },
                    isCreate: function() { return false; },
                    clients: function() { return []; }
                }
            });
            editModal.result.then(function(name) {
                Restangular.one('dashboards', $scope.id)
                    .post('', {'name': name})
                    .then(function(result) {
                        $scope.dashboard.name = name;
                    });
            });
        };

        $scope.openAddWidgetModal = function() {
            var addWidgetModal = $modal.open({
                templateUrl: 'modules/dashboard/widgetModal/addWidgetModal.tpl.html',
                controller: 'WidgetCtrl',
                resolve: {
                    widget: function() { return {}; }
                }
            });
            addWidgetModal.result.then(function(data) {
                Restangular.one('dashboards', $scope.id)
                    .post('widgets', {'widget': data})
                    .then(function(data) {
                        $scope.dashboard.widgets.push(data.widget);
                        $scope.openEditWidgetModal(data.widget);
                    });
            });
        };

        $scope.openEditWidgetModal = function(widget) {
            var editWidgetModal = $modal.open({
                templateUrl: $scope.getEditTemplate(widget.type),
                controller: 'WidgetCtrl',
                resolve: {
                    widget: function() { return angular.copy(widget); }
                }
            });
            editWidgetModal.result.then(function(data) {
                Restangular.one('dashboards', $scope.id)
                    .one('widgets', widget._id)
                    .post('',{'widget': data})
                    .then(function(result) {
                        for(var index = 0; index < $scope.dashboard.widgets.length; index++) {
                            if ($scope.dashboard.widgets[index]._id == result.widget._id) {
                                $scope.dashboard.widgets[index] = result.widget;
                                $scope.updateRefreshTimer(result.widget._id, result.widget.details.refresh);
                            }
                        }
                    });
            });
        };

        //each widget has its own edit template
        $scope.getEditTemplate = function(type) {
            tmpl = 'modules/dashboard/widgetModal/edit';

            switch(type.toLowerCase()) {
                case 'bar':
                case 'line':
                    tmpl +=  'LineBarWidgetModal.tpl.html';
                    break;
                case 'pie':
                    tmpl +=  'PieWidgetModal.tpl.html';
                    break;
                case 'list':
                    tmpl +=  'ListWidgetModal.tpl.html';
                    break;
                default:
                    tmpl +=  'LineWidgetModal.tpl.html';
            }

            return tmpl;
        };

        $scope.splitClients = function() {
            if ($scope.clients.length === 0) {
                return [];
            }

            var columns = [[], []];
            var colSize1 = Math.floor($scope.clients.length / 2);

            for(var i = 0; i < $scope.clients.length; i++)
            {
                if (i < colSize1) {
                    columns[0].push($scope.clients[i]);
                } else {
                    columns[1].push($scope.clients[i]);
                }
            }

            return columns;
        };

        $scope.setUpLogList = function() {

            for(var w in $scope.dashboard.widgets) {
                var widget = $scope.dashboard.widgets[w];

                if (widget.type != 'list') {
                    continue;
                }
//TODO make this work

                //log list widget
                widget.params = {
                    page: 1,
                    total: 0,
                    perPageOptions: [3, 5, 10, 20],
                    perPage: 3,
                    sorting: {
                        name: 'created'
                    },
                    query: widget.details.query,
                    logs: widget.display,
                    lastUpdated: new Date()
                };
                $scope.dashboard.widgets[w] = widget;

                $scope.$watch('dashboard.widgets[w].params', $scope.updateLogList(widget.params), true);
            }
        };

        $scope.updateLogList = function(params) {
            if (params.query.length === 0) {
                return;
            }

            var offset = ((params.page - 1) * params.perPage);
            if (params.page === 1) {
                offset = 0;
            }
            Restangular.one('search').get({query: params.query, limit: params.perPage, start: offset}).then(function(result) {
                $scope.results = result;
                params.total = result.total;
                params.logs = result.logs;
            });
        };

        $scope.setUpLogList();
        $scope.startWidgetRefresh();
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
                    if(!value || typeof value == 'object') {
                        return;
                    }
                    // We need deep copy in order to NOT override original chart object.
                    // This allows us to override chart data member and still the keep
                    // our original renderTo will be the same
                    var deepCopy = true;
                    var newSettings = {};
                    scope.chartData = JSON.parse(scope.chartData);
                    $.extend(deepCopy, newSettings, chartsDefaults, scope.chartData);
                    var chart = new Highcharts.Chart(newSettings);
                });
            }
        };
    });