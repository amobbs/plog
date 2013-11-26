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
angular.module( 'Preslog.search', [
        'Preslog.search.sqlModal',
        'redQueryBuilder'
    ])

    .config(function(stateHelperProvider) {
        stateHelperProvider.addState('mainLayout.search', {
            url: '/search',
            views: {
                "main@mainLayout": {
                    controller: 'SearchCtrl',
                    templateUrl: 'modules/search/search.tpl.html'
                }
            },
            resolve: {
                query: ['$stateParams', function($stateParams) {
                    return '';
                }]
            }
        });
        stateHelperProvider.addState('mainLayout.quickSearch', {
            url: '/search/{search_text}',
            views: {
                "main@mainLayout": {
                    controller: 'SearchCtrl',
                    templateUrl: 'modules/search/search.tpl.html'
                }
            },
            resolve: {
                query: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    var deferred = $q.defer();

                    Restangular.one('search/wizard/quick')
                        .get({
                            search_text: $stateParams.search_text
                        })
                        .then(function(result) {
                            deferred.resolve(result.jql);
                        });

                    return deferred.promise;
                }]
            }
        });
    })

/**
 * And of course we define a controller for our route.
 */
    .controller( 'SearchCtrl', function SearchCtrl( $scope, $http, $modal, titleService, Restangular, query ) {
       titleService.setTitle( 'Search' );

        $scope.jql = query;

        /**
         * Log Widget
         */
        //search results
        $scope.results = {
            data: [], //actual returned logs
            fields: [] //fields that are available in the logs (used for ordering)
        };

        $scope.queryValid = true;

        //define how logs should be displayed/details for pagination
        $scope.logWidgetParams = {
            page: 1,
            total: 0,
            perPageOptions: [3, 5, 10, 25],
            perPage: 3,
            sorting: [],
            order: 'Created',
            orderDirection: 'Desc',
            query: '',
            logs: [],
            lastUpdated: new Date(),
            errors: []
        };

        $scope.updating = false;
        $scope.firstChange = true;
        //if params change then we need to get new logs
        $scope.$watch('logWidgetParams', function(params) {
            if ( ! $scope.firstChange)
            {
                $scope.search();
            }
        }, true);

        //new search query, start on page one and get logs
        $scope.doSearch = function() {
            $scope.logWidgetParams.page = 1;
            $scope.search();
        };


        /**
         * general search to get logs
         */
        $scope.search = function() {
            //find start of page
            var offset = (($scope.logWidgetParams.page - 1) * $scope.logWidgetParams.perPage);
            if ($scope.logWidgetParams.page === 1) {
                offset = 0;
            }

            //used to make sure only one request is sent per update
            if ( ! $scope.updating )
            {
                $scope.updating = true;

                $scope.logWidgetParams.query = $scope.jql;

                Restangular.one('search').get({
                    query: $scope.jql,
                    limit: $scope.logWidgetParams.perPage,
                    start: offset,
                    order: $scope.logWidgetParams.order,
                    orderasc: $scope.logWidgetParams.orderDirection == 'Asc'
                })
                    .then(function(result) {
                        if (!result)
                        {
                            return;
                        }

                        $scope.results = result;
                        var params = angular.copy($scope.logWidgetParams);

                        if (result.errors)
                        {
                            $scope.queryValid = false;
                            params.errors = result.errors;
                        }
                        else
                        {
                            $scope.queryValid = true;
                            params.errors = [];
                        }

                        params.total = result.total;
                        params.logs = result.logs;
                        params.sorting = result.fields;
                        $scope.logWidgetParams = params;
                        setTimeout(function() {
                            $scope.updating = false;
                        }, 500);
                    }
                );
            }
        };


        /**
         * Red Query Builder
         */
        //describe tale/columns used for query builder
        $scope.queryMeta = {};
        //options available for fields with a drop down box
        $scope.selectOptions = {};
        //sql that query builder uses
        $scope.sql = '';
        //values for each clause populated by the query builder.
        $scope.args = [];


        /**
         * Sql to Jql
         * given sql (from query builder) convert to jql to populate display
         */
        $scope.sqlToJql = function(doSearch) {
            if ($scope.sql === "") {
                return;
            }

            var parsedArgs = [];
            var getClassOf = Function.prototype.call.bind(Object.prototype.toString);
            for(var i = 0; i < $scope.args.length; i++)
            {
                //some times we get a date object, sometimes we get a rqb date object returned.
                //parse it so we get the date format we want to send to the server
                if ($scope.args[i].q || getClassOf($scope.args[i]) == '[object Date]')
                {
                    var date = $scope.args[i];
                    if ($scope.args[i].q)
                    {
                        date = $scope.args[i].q;
                    }

                    //pad with 0
                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                    var day = ('0' + date.getDate()).slice(-2);

                    var dateString = date.getFullYear() + '-' + month + '-' + day;
                    parsedArgs.push(dateString);
                }
                else //something other then a date just add it.
                {
                    parsedArgs.push($scope.args[i]);
                }
            }

            Restangular.one('search/wizard/translate').get({sql : $scope.sql, args : JSON.stringify(parsedArgs)}).then(function(data) {
                if (data) {
                    $scope.jql = data.jql;
                    $scope.args = data.args;

                    if (doSearch)
                    {
                        $scope.doSearch();
                    }
                }
            });
        };


        /**
         * Jql to Sql
         * given jql, get sql query and options needed to use red query builder
         */
        $scope.queryBuilderModalOpen = false;
        $scope.jqlToSql = function() {
            if ($scope.queryBuilderModalOpen) {
                return;
            }

            //make sure the user can only open this modal once at a time
            $scope.queryBuilderModalOpen = true;
            //convert jql to sql
            Restangular.one('search/wizard/params')
                .get({jql : $scope.jql})
                .then(function(data) {
                    //populate fields
                    if (data) {
                        if (data.errors)
                        {
                            $scope.queryValid = false;
                            $scope.logWidgetParams.errors = data.errors;
                            return;
                        }

                        $scope.queryValid = true;
                        $scope.logWidgetParams.errors = [];

                        $scope.sql = data.sql;
                        $scope.args = [];

                        //red query builder does not have dates implemented correctly. we need to pass the date in the
                        // internal format rqb uses. which seems to have type casting/detection in it
                        for (var i = 0; i < data.args.length; i++)
                        {
                            if (data.args[i].type == 'date')
                            {
                                $scope.args.push({q:new Date(data.args[i].value), cM:{97:1}}); //format rqb is expecting
                            }
                            else
                            {
                                $scope.args.push(data.args[i].value);
                            }
                        }

                        $scope.queryMeta = data.fieldList;
                        $scope.selectOptions = data.selectOptions;

                        //create modal and pass required data through.
                        var modal = $modal.open({
                            templateUrl: 'modules/search/queryModal/sqlQueryModal.tpl.html',
                            controller: 'SqlModalCtrl',
                            backdrop: 'static',
                            resolve: {
                                sql: function() { return $scope.sql; },
                                args: function() { return $scope.args; },
                                queryMeta: function() { return $scope.queryMeta; },
                                selectOptions: function() { return $scope.selectOptions; }
                            }
                        });

                        //clear it now because the modal is open and they can't click the link again
                        $scope.queryBuilderModalOpen = false;

                        //apply sql changes as jql on screen
                        modal.result.then(function(result) {
                            $scope.sql = result.sql;
                            $scope.args = result.args;
                            $scope.sqlToJql(result.search);

                        });
                    }
                });

        };

        //if a query is passed in then do a search otherwise wait for user to click button
        if ($scope.jql !== '')
        {
            $scope.search();
        }
    })


    /**
     * Help modal
     */
    .controller('HelpModalCtrl', function ($scope, $modalInstance) {

        $scope.ok = function() {
            $modalInstance.dismiss();
        };

        $scope.cancel = function() {
            $modalInstance.dismiss();
        };
    })
;