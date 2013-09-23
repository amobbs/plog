/**
 * Preslog client management module
 * -
 */

angular.module( 'Preslog.clients', [
        'titleService',
        'ngTable'
    ])

    .config(function(stateHelperProvider) {

        /**
         * Client List
         */
        stateHelperProvider.addState('mainLayout.adminClientList', {
            url: '/admin/clients',
            views: {
                "main@mainLayout": {
                    controller: 'AdminClientListCtrl',
                    templateUrl: 'modules/clients/admin-client-list.tpl.html'
                }
            },
            resolve: {

                // Fetch the list of clients
                clientList: ['$q', 'Restangular', '$stateParams', function($q, Restangular) {
                    var deferred = $q.defer();
                    Restangular.one('admin/clients').getList().then(function(clientList) {
                        deferred.resolve(clientList);
                    });
                    return deferred.promise;
                }]
            }
        });


        /**
         * Client Edit
         */
        stateHelperProvider.addState('mainLayout.adminClientEdit', {
            url: '/admin/clients/{client_id:[0-9a-z]*}',
            views: {
                "main@mainLayout": {
                    controller: 'AdminClientEditCtrl',
                    templateUrl: 'modules/clients/admin-client-edit.tpl.html'
                }
            },
            resolve: {

                // Fetch client data
                clientData: ['$q', 'Restangular', '$stateParams', function($q, Restangular, $stateParams) {
                    var deferred = $q.defer();

                    // If editing an existing client
                    if ($stateParams.client_id.length == 24) {
                        Restangular.one('admin/clients', $stateParams.client_id).get().then(function(client) {
                            client.id = client.Client._id;
                            deferred.resolve(client);
                        });
                    }
                    // Not editing a user, just pass back an empty Client
                    else {
                        deferred.resolve({
                            Client: {
                                id: '',
                                deleted:false
                            }
                        });
                    }
                    return deferred.promise;
                }],

                // Fetch client options
                clientOptions: ['$q', 'Restangular', '$stateParams', function($q, Restangular) {
                    var deferred = $q.defer();
                    Restangular.one('admin/clients').options().then(function(options) {
                        deferred.resolve(options);
                    });
                    return deferred.promise;
                }]
            }
        });
    })


    /**
     * Admin Client List
     */
    .controller( 'AdminClientListCtrl', function AdminClientListController( $scope, titleService, ngTableParams, Restangular, $filter, clientList ) {

        /**
         * On Load
         */

        // Title
        titleService.setTitle( ['Clients', 'Admin'] );

        // Apply the resolved client list
        $scope.allClients = clientList.clients;

        // Configure table
        $scope.tableParams = new ngTableParams({
            page: 1,                // show first page
            total: 0,               // length of data
            count: 10,              // count per page
            sorting: {
                created: 'asc'     // default order: last name A-z
            },
            filter: {               // default filter:
                deleted: 'false'    // do not show deleted users
            }
        });

        // Watch table and perform actions on change
        $scope.$watch('tableParams', function(params) {

            // Filter and order
            var orderedData = params.filter ? $filter('filter')($scope.allClients, params.filter) : $scope.allClients;

            // set total for pagination
            params.total = orderedData.length;

            // slice array data on pages
            $scope.clients = orderedData.slice(
                (params.page - 1) * params.count,
                params.page * params.count
            );
        }, true);


        /**
         * Toggle "show Deleted"
         */
        $scope.toggleDeleted = function() {
            if ($scope.tableParams.filter.deleted === undefined) {
                $scope.tableParams.filter.deleted = 'false';
            } else {
                delete $scope.tableParams.filter.deleted;
            }
        };



    })


    /**
     * Admin Client Edit
     */
    .controller( 'AdminClientEditCtrl', function AdminClientEditController( $scope, titleService, clientData, clientOptions ) {

        /**
         * Init
         */

        // Title
        titleService.setTitle( ['Clients', 'Admin'] );

        // Client Data
        $scope.client = clientData.Client;

        console.log($scope.client);

        // Options Data
        $scope.options = clientOptions;


        /**
         * Save Client
         */
        $scope.saveClient = function() {

            // Will not submit without validation passing
            if ( $scope.clientForm.$invalid ) {
                alert('Your submission is not valid. Please check for errors.');
                return false;
            }

            // Fetch data from form
            clientData.Client = $scope.client;

            // Post back to API
            clientData.post().then(

                // On success
                function()
                {
                    // Redirect to user list
                    $location.path('/admin/clients');
                },

                // On failure
                function(response)
                {
                    // Extrapolate all fields to the scope
                    $scope.validation = response.data.data;

                    // If field exists, mark is as invalid
                    for (var i in $scope.validation)
                    {
                        if ($scope.clientForm[i] !== undefined) {
                            $scope.clientForm[i].$setValidity('validateServer', false);
                        }
                    }

                }
            );
        };


        /**
         * Delete Client
         */
        $scope.deleteClient = function() {

            // Put scope user back
            clientData.Client = $scope.client;

            // Apply delete
            clientData.remove().then(function()
            {
                // Back to list page
                $location.path('/admin/clients');

            });
        };
    })


;


