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
        'Preslog.dashboard.dashboardModal',
        'Preslog.dashboard.widgetModal',
        'permission'
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
                source: ['$q', 'Restangular', '$stateParams', 'userService', 'dashboard_live_logs', function($q, Restangular, $stateParams, userService, dashboard_live_logs) {
                    // Fetch dashboard
                    var defered = $q.defer();

                    userService.getDashboards().then(function (dashboards) {
                        var dashId = dashboard_live_logs;
                        if (dashboards.favourites.length > 0)
                        {
                            dashId = dashboards.favourites[0]._id;
                        }

                        defered.resolve(Restangular.one('dashboards', dashId));
                    });

                    return defered.promise;
                }],
                dashboard: ['$q', 'Restangular', '$stateParams', 'userService', 'dashboard_live_logs', function($q, Restangular, $stateParams, userService, dashboard_live_logs) {
                    var deferred = $q.defer();

                    userService.getDashboards().then(function (dashboards) {
                        var dashId = dashboard_live_logs;
                        if (dashboards.favourites.length > 0)
                        {
                            dashId = dashboards.favourites[0]._id;
                        }

                        Restangular.one('dashboards', dashId)
                            .get()
                            .then(function(dashboard) {
                                deferred.resolve(dashboard);
                            });
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
                   return Restangular.one('dashboards', $stateParams.dashboard_id);
                }],
                dashboard: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    var deferred = $q.defer();

                    Restangular.one('dashboards', $stateParams.dashboard_id)
                        .get()
                        .then(function(dashboard) {
                            deferred.resolve(dashboard);
                        });

                    return deferred.promise;
                }]
            }
        });
    })


    /**
     * Dashboard Controller
     */
    .controller( 'DashboardCtrl', function DashboardController( $scope, $http, $window, $location, $timeout, $modal, userService, titleService, Restangular, source, dashboard) {
        titleService.setTitle( 'Dashboard' );

        $scope.id = dashboard.dashboard.id;//mongoid for dashboard
        $scope.dashboard = dashboard.dashboard;
        $scope.favourites = dashboard.favourites;
        $scope.clients = dashboard.clients;
        $scope.allDashboards = [];
        $scope.presetDashboards = [];

        $scope.refreshTimers= [];
        $scope.name = '';
        $scope.addDashboard = undefined;

        //get list of dashboards for navigation on left
        Restangular.one('dashboards').get().then(function(result) {
            $scope.allDashboards = result.dashboards;
            $scope.presetDashboards = result.preset;
        });

        //update favourite list as it changes
        $scope.$watch(
            function() { return $scope.addDashboard; },
            function(id) {
            if (id) {
                $scope.addToFavourite(id);
            }
        });

        //move the widgets around
        $scope.sortableOptions = {
            placeholder: 'placeholder',
            items: '.widget',
            revert: 150,
            tolerance: 'pointer',
            handle: '.widget-handle',
            cursorAt: {
                left: 10,
                top: 10
            },
            start: function(event, ui) {
                if (ui.item.hasClass('col3')) {
                    ui.placeholder.css('width', '98%');
                }

                if (ui.item.hasClass('col2')) {
                    ui.placeholder.css('width', '62%');
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

               source.post('', {'widgets': $scope.dashboard.widgets});
            }
        };

        if ($scope.dashboard.preset)
        {
            $scope.sortableOptions.handle = '.no-handle';
            $scope.sortableOptions.update = function(event, ui) { };
        }

        $scope.deleteWidget = function(widgetId) {
            source.one('widgets', widgetId)
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
            var promise = $timeout($scope.refreshCallback(widgetId), ((interval * 1000) * 60));

            $scope.refreshTimers.push({
                widgetId: widgetId,
                promise: promise
            });
        };

        $scope.removeRefreshTimers = function()
        {
            for(var id in $scope.refreshTimers)
            {
                var timeout = $scope.refreshTimers[id];
                $timeout.cancel(timeout.promise);
                $scope.refreshTimers.splice(id, 1);
            }
        };
        $scope.$on('$destroy', function() {
            $scope.removeRefreshTimers();
        });

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
            if ( newInterval > 0 )
            {
                $scope.setRefreshTimer(widgetId, newInterval);
            }
        };

        $scope.refreshWidget = function(widgetId) {
            variables = {};
            if ($scope.dashboard.session)
            {
                variables = $scope.dashboard.session;
            }

            for(var id in $scope.dashboard.widgets)
            {
                if ($scope.dashboard.widgets[id]._id == widgetId)
                {
                    if ($scope.dashboard.widgets[id].type !== 'date' && $scope.dashboard.widgets[id].type !== 'list')
                    {
                        $scope.dashboard.widgets[id].loading = true;
                        break;
                    }
                    else if ($scope.dashboard.widgets[id].type == 'list')
                    {
                        $scope.dashboard.widgets[id].params.forceUpdate = true;
                    }
                }
            }

            if (!$scope.$$phase)
            {
                $scope.$apply();
            }

            source.one('widgets', widgetId)
                .get(variables)
                .then(function(result) {
                    if (result && result.widget) {
                        //find the widget in memory and update display
                        for(var wId in $scope.dashboard.widgets) {
                            var widget = $scope.dashboard.widgets[wId];
                            if (widget._id == widgetId) {
                                //specific to dateWidget, we want to be able to change the period without affecting the default.
                                if (widget.type == 'date')
                                {
                                    $scope.dashboard.session = {
                                        start: new Date(result.widget.details.start),
                                        end: new Date(result.widget.details.end),
                                        period: result.widget.details.period
                                    };
                                }
                                else if (widget.type == 'list' && $scope.dashboard.widgets[wId].params)
                                {
                                    $scope.dashboard.widgets[wId].params.forceUpdate = true;
                                }
                                else
                                {
                                    $scope.dashboard.widgets[wId] = result.widget;
                                    $scope.dashboard.widgets[wId].loading = false;
                                }

                                $scope.updateRefreshTimer(widgetId, result.widget.details.refresh);
                                break;
                            }
                        }
                    }
                });
        };

        //download a docx version of this dashboard
        $scope.exportReport = function() {
            var loc = '/api/dashboards/' + $scope.id + '/export';

            if ($scope.dashboard.session && $scope.dashboard.session.start && $scope.dashboard.session.end)
            {
                var startDate = new Date($scope.dashboard.session.start).getTime();
                startDate = parseInt( startDate / 1000, 10); //remove milliseconds for php
                var endDate = new Date($scope.dashboard.session.end).getTime();
                endDate = parseInt( endDate / 1000, 10);

                loc += '?variableStart=' + encodeURIComponent(startDate);
                loc += '&variableEnd=' + encodeURIComponent(endDate);
            }

            window.location.href = loc;
        };

        //create new dashboard
        $scope.openCreateModal = function () {
            var createModal = $modal.open({
                templateUrl: 'modules/dashboard/dashboardModal/createDashboardModal.tpl.html',
                controller: 'DashboardModalCtrl',
                backdrop: 'static',
                resolve: {
                    name: function() { return ''; },
                    isCreate: function() { return true; },
                    clients: function() { return $scope.splitClients(); }
                }
            });
            createModal.result.then(function(details) {
                var dashboard = Restangular.all('dashboards');
                //find which clients we want to share with
                var shares = [];
                for(var id in details.shares) {
                    if (details.shares[id]) {
                        shares.push(id);
                    }
                }

                dashboard.post({name: details.name, shares: shares})
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
                backdrop: 'static',
                resolve: {
                    name: function() { return $scope.dashboard.name; },
                    isCreate: function() { return false; },
                    clients: function() { return []; }
                }
            });
            editModal.result.then(function(details) {
                source.post('', details)
                    .then(function(result) {
                        $scope.dashboard.name = details.name;
                    });
            });
        };

        $scope.openAddWidgetModal = function() {
            var addWidgetModal = $modal.open({
                templateUrl: 'modules/dashboard/widgetModal/addWidgetModal.tpl.html',
                controller: 'WidgetCtrl',
                resolve: {
                    widget: function() { return {}; },
                    clients: function() { return []; }
                }
            });
            addWidgetModal.result.then(function(data) {
                Restangular.one('dashboards', $scope.id)
                    .post('widgets', {'widget': data})
                    .then(function(data) {
                        $scope.dashboard.widgets.push(data.widget);
                        if (data.widget.type == 'list')
                        {
                            $scope.setUpLogList(data.widget._id);
                        }
                        $scope.openEditWidgetModal(data.widget);
                    });
            });
        };

        $scope.openEditWidgetModal = function(widget) {
            var editWidgetModal = $modal.open({
                templateUrl: $scope.getEditTemplate(widget.type),
                controller: 'WidgetCtrl',
                backdrop: 'static',
                resolve: {
                    widget: function() { return angular.copy(widget); },
                    clients: function() { return $scope.clients; }
                }
            });
            editWidgetModal.result.then(function(data) {
                Restangular.one('dashboards', $scope.id)
                    .one('widgets', widget._id)
                    .post('',{'widget': data})
                    .then(function(result) {
                        for(var index = 0; index < $scope.dashboard.widgets.length; index++) {
                            if ($scope.dashboard.widgets[index]._id == result.widget._id) {
                                if (result.widget.type == 'list')
                                {
                                    $scope.updateLogList(result.widget);
                                }
                                else
                                {
                                    $scope.dashboard.widgets[index] = result.widget;
                                }


                                //specific to dateWidget, we want to be able to change the period without affecting the default.
                                if (result.widget.type == 'date')
                                {
                                    $scope.dashboard.session = {
                                        start: new Date(result.widget.details.start),
                                        end: new Date(result.widget.details.end),
                                        period: result.widget.details.period
                                    };
                                }

                                $scope.refreshWidget(result.widget._id);
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
                    tmpl +=  'BarWidgetModal.tpl.html';
                    break;
                case 'line':
                    tmpl +=  'LineWidgetModal.tpl.html';
                    break;
                case 'pie':
                    tmpl +=  'PieWidgetModal.tpl.html';
                    break;
                case 'list':
                    tmpl +=  'ListWidgetModal.tpl.html';
                    break;
                case 'benchmark':
                    tmpl +=  'BenchmarkWidgetModal.tpl.html';
                    break;
                case 'date':
                    tmpl +=  'DateWidgetModal.tpl.html';
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

        //add current dashboard on to the list of favourites for the logged in user
        $scope.addToFavourite = function(id) {
            Restangular.one('dashboards/favourites')
                .post('', {dashboard_id: id})
                .then(function(result) {
                    $scope.favourites = result.favourites;
                    userService.getDashboards();
                });
        };

        //remove current dashboard on to the list of favourites for the logged in user
        $scope.removeFromFavourite = function() {
            Restangular.one('dashboards/favourites', $scope.id)
                .remove()
                .then(function(result) {
                    $scope.favourites = result.favourites;
                    userService.getDashboards();
                });
        };

        //is the current dashboard a favourite for this user?
        $scope.isFavourite = function() {
            var found = false;
            for(var id in $scope.favourites) {
                if ($scope.favourites[id]._id == $scope.id) {
                    found = true;
                }
            }
            return found;
        };

        //setup the properties needed to get the log list widget working.
        $scope.setUpLogList = function(widgetId) {

            for(var w in $scope.dashboard.widgets) {
                if (widgetId && $scope.dashboard.widgets[w]._id !== widgetId)
                {
                    continue;
                }

                var widget = $scope.dashboard.widgets[w];

                if (widget.type != 'list')
                {
                    continue;
                }

                //log list widget
                widget.params = {
                    page: 1,
                    total: 0,
                    perPageOptions: [3, 5, 10, 25],
                    perPage: widget.details.perPage,
                    sorting: [],
                    order: 'Created',
                    orderDirection: 'Desc',
                    query: widget.details.query,
                    logs: widget.display,
                    forceUpdate: false
                };
                $scope.dashboard.widgets[w] = widget;
                break;
            }
        };

        $scope.updateLogList = function(widget)
        {
            for (var wId in $scope.dashboard.widgets)
            {
                if ($scope.dashboard.widgets[wId]._id == widget._id)
                {
                    var workingWidget = $scope.dashboard.widgets[wId];

                    workingWidget.params = {
                        page: 1,
                        total: 0,
                        perPageOptions: [3, 5, 10, 25],
                        perPage: widget.details.perPage,
                        sorting: [],
                        order: 'Created',
                        orderDirection: 'Desc',
                        query: widget.details.query,
                        logs: widget.display,
                        forceUpdate: false
                    };
                }
            }
        };

        $scope.updatingWidgets = [];
        $scope.isWidgetUpdating = function(widgetId)
        {
            for(var id in $scope.updatingWidgets)
            {
                if ($scope.updatingWidgets[id] == widgetId)
                {
                    return true;
                }
            }
            return false;
        };
        $scope.finishedUpdating = function(widgetId)
        {
            setTimeout(function() {
                for(var id in $scope.updatingWidgets)
                {
                    if ($scope.updatingWidgets[id] == widgetId)
                    {
                        $scope.updatingWidgets.splice(id, 1);
                    }
                }
            }, 500);

        };

        //debounce only run once the user stop spamming
        $scope.refreshAllWidgets = _.debounce(function()
        {
            for(var id in $scope.dashboard.widgets)
            {
                if ($scope.dashboard.widgets[id].type !== 'date')
                {
                    $scope.refreshWidget($scope.dashboard.widgets[id]._id);
                }
            }
        },
        1000);

        $scope.initWidgets = function()
        {
            //set the sessions start/end variables, sorry about the double loop. need to make sure date gets picked up first
            for (var id in $scope.dashboard.widgets)
            {
                var widget = $scope.dashboard.widgets[id];
                if (widget.type == 'date')
                {
                    //specific to dateWidget, we want to be able to change the period without affecting the default.
                    $scope.dashboard.session =
                    {
                        start: new Date(widget.details.start),
                        end: new Date(widget.details.end),
                        period: widget.details.period
                    };
                }

                else if (widget.type == 'list')
                {
                    $scope.setUpLogList(widget._id);
                }
            }

            //add watch to update widgets when date range changes
            $scope.$watch(
                function() { return $scope.dashboard.session; },
                function() {
                    $scope.refreshAllWidgets();
                },
                true
            );
        };
        $scope.initWidgets();

        //start all the timers to refresh widgets regularly
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