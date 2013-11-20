/*jshint loopfunc: true */
/**
 * Log Display Directive
 */

angular.module('logWidget', [])
    .directive('logWidget', ['Restangular', function (Restangular) {

        /**
         * Establish Directive
         */
        return {
            restrict: "E",
            replace: true,
            transclude: true,
            templateUrl: 'modules/dashboard/widgets/logList/logWidget.tpl.html',
            scope: {
                params: '=',
                logData: '=',
                session: '='
            },
            link: function( scope, element, attrs, ctrl ) {
                scope.total = 0; //total number of logs
                scope.logs = []; //logs to be displayed

                scope.pages = []; //details of each page
                scope.totalPages = 1; //total number of pages
                scope.showing = 0; //how many logs are currently showing (may differ from prePage)

                scope.orderDirections = ['Asc', 'Desc'];

                scope.logsLoading = false; //flag to display loading widget or not

                //download a xls version of this dashboard
                scope.exportXLS = function(query, orderBy, asc, dashboard) {
                    var loc = '/api/search/export' +
                        '?query=' + encodeURIComponent(query) +
                        '&order=' + encodeURIComponent(orderBy) +
                        '&orderasc=' + encodeURIComponent(asc);

                    //sorry so very sorry, i have no other *quick* way of getting the session variables into here that covers dashboards and search page
                    if (dashboard && dashboard.session)
                    {
                        var startDate = new Date(dashboard.session.start).getTime();
                        startDate = parseInt( startDate / 1000, 10);
                        var endDate = new Date(dashboard.session.end);
                        endDate = parseInt( endDate / 1000, 10);

                        loc += '&variableStart=' + encodeURIComponent(startDate);
                        loc += '&variableEnd=' + encodeURIComponent(endDate);

                    }

                    window.location.href = loc;
                };

                scope.redirectToLog = function(logId)
                {
                    logId = logId.replace('#', '');
                    window.location.href = '/logs/' + logId;
                };

                //if any params change get new logs
                var init = true;
                scope.$watch(function() { return scope.params; }, function() {
                    if (init || !scope.params)
                    {
                        init = false;
                    }
                    else
                    {
                        init = false;
                        scope.params.forceUpdate = false;
                        scope.requestLogsFromServer();
                    }
                }, true);


                scope.firstRequest = true; //used to change display on search page so that we do not say no logs found when they have not seachred yet
                scope.search = false;
                scope.requestLogsFromServer = function()
                {
                    if (scope.search)
                    {
                        return;
                    }
                    scope.search = true;

                    var getRequest = {
                        query: scope.params.query,
                        limit: scope.params.perPage,
                        start: scope.params.offset,
                        order: scope.params.order,
                        orderasc: scope.params.orderDirection == 'Asc'
                    };

                    if (scope.session && scope.session.start && scope.session.end)
                    {
                        var startDate = new Date(scope.session.start).getTime();
                        startDate = parseInt( startDate / 1000, 10); //remove milliseconds for php
                        var endDate = new Date(scope.session.end).getTime();
                        endDate = parseInt( endDate / 1000, 10);


                        getRequest.variableStart = startDate;
                        getRequest.variableEnd = endDate;

                    }

                    scope.logsLoading = true;
                    scope.firstRequest = false;

                    Restangular.one('search')
                        .get(getRequest)
                        .then(function(result) {
                            scope.params.logs = result.logs;
                            scope.params.total = result.total;
                            scope.params.sorting = result.fields;

                            scope.getLogs();
                            scope.logsLoading = false;
                            setTimeout(function() { scope.search = false; }, 1000 );
                        }
                    );
                };

                //logs have been updated in params so display them
                scope.getLogs = function() {
                    if (!scope.params || !scope.params.logs || scope.params.logs.length === 0) {
                        scope.logs = [];
                        return;
                    }

                    //just so it is easier to work with
                    var logs = scope.params.logs;

                    var display = [];
                    var attrPerRow = 3;
                    var log = {};
                    for(var i = 0; i < logs.length; i++) {
                        log = {
                            id: logs[i].id,
                            rows: []
                        };

                        //loop through attributes and separate so there is 3 per row
                        var row = [];
                        for(var a = 0; a < logs[i].attributes.length; a++) {
                            if (a !== 0 && a % attrPerRow === 0) {
                                log.rows.push(row);
                                row =[];
                            }

                            row.push(logs[i].attributes[a]);
                        }

                        //pad out cells for last row
                        var rowLength = attrPerRow - row.length;
                        for(var b = 0; b < rowLength; b++) {
                            row.push({'title': '', 'value': ''});
                        }

                        //add the last row
                        log.rows.push(row);

                        display.push(log);
                    }

                    scope.logs = display;
                    scope.showing = scope.params.perPage;
                    if (scope.params.total < scope.params.perPage) {
                        scope.showing = scope.params.total;
                    }
                    if (logs.length < scope.showing) {
                        scope.showing = logs.length;
                    }
                    scope.updatePages();
                };

                //update display for pagination
                scope.updatePages = function() {
                    scope.totalPages = Math.ceil(scope.params.total / scope.params.perPage);

                    var pages = [];

                    //there will always be atleast one page.
                    var pageOne = {
                        number: 1,
                        display: '1',
                        current: false,
                        enabled: true
                    };

                    if (scope.params.page === 1) {
                        pageOne.current = true;
                        pageOne.enabled = false;
                    }
                    pages.push(pageOne);

                    //there is no data or one page. just make it look like one page
                    if (scope.totalPages === 0 || scope.totalPages === 1) {
                        scope.totalPages = 1;
                        scope.pages = pages;
                        return;
                    }

                    //there are to many pages, truncate the display
                    if (scope.params.page > 5) {
                        pages.push({
                            number: -1,
                            display: '...',
                            current: false,
                            enabled: false

                        });
                    }

                    //add in the pages we will display, there should be a max of 7 shown at any time
                    var maxAdd = scope.totalPages;
                    if (scope.totalPages > (scope.params.page + 2)) { //add 2 pages after
                        maxAdd = scope.params.page + 2;
                    } else if (scope.params.page < 3) { //
                        maxAdd = 5;
                    }

                    if (scope.totalPages === scope.params.page || scope.totalPages < 5) {
                        maxAdd = scope.totalPages;
                    }

                    var minAdd = 2;
                    if (scope.params.page > 3) {
                        minAdd = scope.params.page - 2;
                    }

                    for(var i = minAdd; i <= maxAdd; i++) {
                        var page = {
                            number: i,
                            display: i,
                            current: false,
                            enabled: true
                        };

                        if (i === scope.params.page) {
                            page.current = true;
                            page.enabled = false;
                        }

                        pages.push(page);
                    }

                    //if there are to many pages to show then truncate the end
                    if (scope.totalPages > (scope.params.page + 2)) {
                        pages.push({
                            number: -1,
                            display: '...',
                            current: false,
                            enabled: false
                        });

                        //add last page
                        pages.push({
                            number: scope.totalPages,
                            display: scope.totalPages,
                            current: false,
                            enabled: true
                        });
                    }

                    scope.pages = pages;
                };
            }
        };
    }]);