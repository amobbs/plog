/**
 * Preslog client management module
 * -
 */

angular.module( 'Preslog.clients', [
        'titleService',
        'ngTable',
        'ui.bootstrap'
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
    .controller( 'AdminClientEditCtrl', function AdminClientEditController( $scope, titleService, clientData, clientOptions, $filter, $modal ) {

        /**
         * Init
         */

        // Title
        titleService.setTitle( ['Clients', 'Admin'] );

        // Client + Options Data
        $scope.client = clientData.Client;
        $scope.options = clientOptions;

        // Page opts
        $scope.newField = {};
        $scope.showDeleted = false;
        $scope.newGroup = {};
        $scope.showDeletedGroups = false;


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


        /**
         * Add Field
         * Validates the form data and then opens an edit modal
         */
        $scope.addField = function()
        {
            // Fetch type of modal
            var options = $.grep($scope.options.fieldTypes, function(e){ return e.alias === $scope.newField.type; });
            options = options[0];

            // Set up the field
            var field = {
                '_id': null,
                'name': $scope.newField.name,
                'type': $scope.newField.type,
                'order': $scope.client.format.length,
                'data': {},
                'newField': true
            };

            // Open the Modal
            var modal = $modal.open({
                templateUrl: 'modules/clients/modals/admin-client-edit-field.tpl.html',
                controller: 'AdminClientEditFieldCtrl',
                resolve: {
                    field: function() { return field; },
                    index: function() { return null; },
                    options: function() { return options; }
                }
            });


            /**
             * Modal Save
             */
            modal.result.then(function(ret) {

                // remove marker
                delete ret.field.newField;

                // Append the new element
                $scope.client.format.push(ret.field);

                // Clear the field items
                $scope.newField = {};
            });

        };


        /**
         * Edit Fields
         * Opens a modal where the field content can be edited.
         */
        $scope.editField = function( field_id ) {

            // Fetch the object in the array
            var field = $.grep($scope.client.format, function(e){ return e._id === field_id; });
            field = field[0];

            // Get the array index of this item
            var index = $scope.client.format.indexOf(field);

            // Create a copt of the item so we break the binding
            var fieldCopy = angular.copy(field);

            // Fetch the type of field
            var options = $.map($scope.options.fieldTypes, function(v,k){ if (v.alias == field.type) { return v; } });
            options = options[0];

            // Open the Modal
            var modal = $modal.open({
                templateUrl: 'modules/clients/modals/admin-client-edit-field.tpl.html',
                controller: 'AdminClientEditFieldCtrl',
                resolve: {
                    field: function() { return fieldCopy; },
                    index: function() { return index; },
                    options: function() { return options; }
                }
            });


            /**
             * Modal Save
             */
            modal.result.then(function(ret) {

                // Replace the element
                $scope.client.format[ret.index] = ret.field;
            },

            /**
             * Modal Dismiss
             */
            function()
            {
                // Do nothing on cancel
            });

        };



        /**
         * Add Attribute Group
         */
        $scope.addGroup = function()
        {
            // Set up the field
            var group = {
                '_id': null,
                'name': $scope.newGroup.name,
                'order': $scope.client.attributes.length,
                'children': [],
                'newGroup': true
            };

            // Open the Modal
            var modal = $modal.open({
                templateUrl: 'modules/clients/modals/admin-client-edit-attribute.tpl.html',
                controller: 'AdminClientEditAttributeCtrl',
                resolve: {
                    group: function() { return group; },
                    index: function() { return null; }
                }
            });


            /**
             * Modal Save
             */
            modal.result.then(function(ret) {

                // remove marker
                delete ret.group.newGroup;

                // Append the new element
                $scope.client.attributes.push(ret.group);

                // Clear the field items
                $scope.newGroup = {};
            });

        };

        /**
         * Edit Client Attributes (as groups)
         * @param attribute_id
         */
        $scope.editGroup = function( group_id )
        {
            // Fetch the object in the array
            var group = $.grep($scope.client.attributes, function(e){ return e._id === group_id; });
            group = group[0];

            // Get the array index of this item
            var index = $scope.client.attributes.indexOf(group);

            // Create a copt of the item so we break the binding
            var groupCopy = angular.copy(group);

            // Open the Modal
            var modal = $modal.open({
                templateUrl: 'modules/clients/modals/admin-client-edit-attribute.tpl.html',
                controller: 'AdminClientEditAttributeCtrl',
                resolve: {
                    group: function() { return groupCopy; },
                    index: function() { return index; }
                }
            });

            /**
             * Modal Save
             */
            modal.result.then(function(ret) {

                // Replace the element
                $scope.client.attributes[ret.index] = ret.group;
            });

        };

    })



    /**
     * Client Edit: Field Editor Modal
     */
    .controller( 'AdminClientEditFieldCtrl', function AdminClientEditFieldController( $scope, $modalInstance, field, options, index ) {

        /**
         * On Load
         */

        // Assign scope vars
        $scope.index = index;
        $scope.field = field;
        $scope.options = options;
        $scope.showDeleted = false;
        $scope.newOption = {};


        /**
         * Add an option to Select items
         */
        $scope.addOption = function()
        {
            // Set up field
            var option = $scope.newOption;
            option._id = null;
            option.deleted = false;
            option.order = $scope.field.data.options.length;

            // Put to array
            $scope.field.data.options.push( option );

            // Reset form
            $scope.newOption = {};
        };


        /**
         * Save changes
         */
        $scope.save = function()
        {
            // Close, and pass back the Field we've edited with the new changes.
            $modalInstance.close({
                'index':$scope.index,
                'field':$scope.field
            });
        };


        /**
         * Delete field
         */
        $scope.remove = function()
        {
            // Set as deleted
            $scope.field.deleted = true;

            // Pass back changes
            $modalInstance.close({
                'index':$scope.index,
                'field':$scope.field
            });
        };


        /**
         * Dismiss modal, cancelling changes
         */
        $scope.dismiss = function()
        {
            $modalInstance.dismiss();
        };
    })


    /**
     * Client Edit: Field Editor Modal
     */
    .controller( 'AdminClientEditAttributeCtrl', function AdminClientEditAttributeController( $scope, $modalInstance, group, index ) {

        // Assign scope vars
        $scope.index = index;
        $scope.group = group;
        $scope.showDeleted = false;
        $scope.withSelectedAction = 'Set Deleted';
        $scope.selectionActions = ['Do nothing', 'Set Deleted'];

        // list of nodes selected in hierarchy fields
        $scope.hierarchySelected = [];

        //used as a counter for temp keys when adding attrabutes to list but not committing to api yet
        $scope.newId = 10;

        $scope.newAttrName = '';

        /**
         * when writing out group to hierarchy field directive the $id value in the object is lost.
         */
        $scope.fixGroupIds = function() {
            if ($scope.group._id == null && $scope.group.children.length === 0) {
                return;
            }

            if ($scope.group._id != null && $scope.group._id.$id) {
                $scope.group._id = $scope.group._id.$id;
            }

            for(var child in $scope.group.children) {
                if ($scope.group.children[child]._id.$id) {
                    $scope.group.children[child]._id = $scope.group.children[child]._id.$id;
                    for(var subChild in $scope.group.children[child].children) { //this is stupid should be some kind of recursion
                        if ($scope.group.children[child].children[subChild]._id.$id) {
                            $scope.group.children[child].children[subChild]._id = $scope.group.children[child].children[subChild]._id.$id;
                        }
                    }
                } else if ($scope.group.children[child]._id !== undefined && !isNaN($scope.group.children[child]._id)) {
                    $scope.newId = parseInt($scope.group.children[child]._id, 10) + 1;
                }
            }
        };
        $scope.fixGroupIds();

        /**
         * Save changes
         */
        $scope.save = function()
        {
            if ($scope.withSelectedAction == 'Set Deleted') {
                //find selected ids and mark as deleted also undelete any that are no longer selected
                for(var itemId in $scope.group.children) {
                    if ($scope.hierarchySelected.indexOf($scope.group.children[itemId]._id) != -1) {
                        $scope.group.children[itemId].deleted = true;
                    } else {
                        $scope.group.children[itemId].deleted = false;
                    }
                    //dont forget to check the children
                    for(var subItemId in $scope.group.children[itemId].children) {
                        if ($scope.hierarchySelected.indexOf($scope.group.children[itemId].children[subItemId]._id) != -1) {
                            $scope.group.children[itemId].children[subItemId].deleted = true;
                        } else {
                            $scope.group.children[itemId].children[subItemId].deleted = false;
                        }
                    }
                }
            }

            // Close, and pass back the Field we've edited with the new changes.
            $modalInstance.close({
                'index':$scope.index,
                'group':$scope.group
            });
        };

        $scope.addAttr = function() {
            var field = {
                name: $scope.newAttrName,
                _id: $scope.newId++ + "", //we need an id so when the modal is reloaded but has not been saved yet we can see the added elements,
                deleted: false,
                children: [],
                newChild: true
            };

            $scope.group.children.push(field);
            $scope.newAttrName = '';
        };

        /**
         * Delete field
         */
        $scope.remove = function()
        {
            // Set as deleted
            $scope.group.deleted = true;

            // Pass back changes
            $modalInstance.close({
                'index':$scope.index,
                'group':$scope.group
            });
        };


        /**
         * Dismiss modal, cancelling changes
         */
        $scope.dismiss = function()
        {
            $modalInstance.dismiss();
        };

    })

;


