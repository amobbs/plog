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

    /**
     * Search Config
     */
    .config(function(stateHelperProvider) {

        // Search
        stateHelperProvider.addState('mainLayout.quickSearch', {
            url: '/search/{search_text}',
            views: {
                "main@mainLayout": {
                    controller: 'SearchCtrl',
                    templateUrl: 'modules/search/search.tpl.html'
                }
            },
            resolve: {
                searchParams: ['$q', 'Restangular', '$stateParams', 'queryBuilderService', function($q, Restangular, $stateParams, queryBuilderService) {
                    var deferred = $q.defer();

                    // Resolve search parameters (in URL) to a JQL statement
                    Restangular.one('search/wizard/quick')
                        .get({
                            search_text: $stateParams.search_text
                        })
                        .then(function(queryData) {

                            // Resolve RedQueryBuilder from given JQL
                            // Also loads the RBQ parameters
                            queryBuilderService
                                .jqlToSql(queryData.jql)
                                .then(function(sqlData)
                                {
                                    // Put to returnable object
                                    result = sqlData;
                                    result.jql = queryData.jql;

                                    // Return data
                                    deferred.resolve(result);
                                });
                        });

                    return deferred.promise;
                }]
            }
        });
    })

    /**
     * Search Controller
     */
    .controller( 'SearchCtrl', function SearchCtrl( $scope, $http, $modal, titleService, Restangular, searchParams, queryBuilderService ) {

        // Title
        titleService.setTitle( 'Search' );

        // Search mode
        $scope.searchMode = 'wizard';

        // Pre-fill JQL from Query Resolve
        $scope.jql = searchParams.jql;


        /**
         * Prepare Red Query Builder
         */
        $scope.queryMeta = searchParams.queryMeta;          // describe tale/columns used for query builder
        $scope.selectOptions = searchParams.selectOptions;  // options available for fields with a drop down box
        $scope.sql = searchParams.sql;      // sql that query builder uses
        $scope.args = searchParams.args;    // values for each clause populated by the query builder.


        /**
         * Prepare the Log Widget
         */

        // Define how logs should be displayed/details for pagination
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

        //search results placeholders
        $scope.results = {
            data: [], //actual returned logs
            fields: [] //fields that are available in the logs (used for ordering)
        };

        // Various
        $scope.queryValid = true;
        $scope.updating = false;
        $scope.firstChange = true;

        // Watch: If params are changed then we need to get new logs
        $scope.$watch('logWidgetParams', function(params) {
            if ( ! $scope.firstChange)
            {
                $scope.PerformSearch();
            }
        }, true);


        /**
         * Switch search mode
         * Toggles between "wizard" and "advanced" search modes.
         */
        $scope.ChangeSearchMode = function()
        {
            // TODO: Changeing search mode not yet implemented.
            alert('Change mode not yet implemented!');
        };


        /**
         * Search Button - Do any prep required before generic search
         * @constructor
         */
        $scope.Search = function() {

            // Switch widget to Page 1
            $scope.logWidgetParams.page = 1;

            // Running in Wizard mode?
            if ($scope.searchMode == 'wizard')
            {
                // Translate RQB's SQL to JQL.
                queryBuilderService
                    .sqlToJql($scope.sql, $scope.args)
                    .then(function(data)
                    {
                        // Apply JQL string
                        $scope.jql = data.jql;

                        // Search
                        $scope.PerformSearch();
                    });
            }
            else
            {
                // Straight JQL Search
                $scope.PerformSearch();
            }
        };


        /**
         * Actual generic search function; fetch the logs from the server using JQL.
         */
        $scope.PerformSearch = function()
        {
            // find start of page
            var offset = (($scope.logWidgetParams.page - 1) * $scope.logWidgetParams.perPage);
            if ($scope.logWidgetParams.page === 1) {
                offset = 0;
            }

            // Used to make sure only one request is sent per update
            if ( $scope.updating )
            {
                return;
            }
            $scope.updating = true;

            // Set query
            $scope.logWidgetParams.query = $scope.jql;

            // Do search using JQL
            Restangular.one('search')
                .get({
                    query: $scope.jql,
                    limit: $scope.logWidgetParams.perPage,
                    start: offset,
                    order: $scope.logWidgetParams.order,
                    orderasc: $scope.logWidgetParams.orderDirection == 'Asc'
                })
                .then(function(result)
                {
                    // No result? Stop.
                    if (!result)
                    {
                        return;
                    }

                    // Get results
                    $scope.results = result;
                    var params = angular.copy($scope.logWidgetParams);

                    // If errors, display them.
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

                    // Reset the status and options
                    params.total = result.total;
                    params.logs = result.logs;
                    params.sorting = result.fields;
                    $scope.logWidgetParams = params;

                    // :BUGFIX: Clear the "updating" reference after 500ms.
                    // Prevents the watch of logWidgetParams re-firing the search.
                    setTimeout(function() {
                        $scope.updating = false;
                    }, 500);
                });
        };


        /**
         * Initial Load
         * Perform a search on page load if a query is supplied.
         */
        if ($scope.jql !== '')
        {
            $scope.PerformSearch();
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