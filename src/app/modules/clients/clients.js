/**
 * Preslog client management module
 * -
 */

angular.module( 'Preslog.clients', [
        'titleService',
        'ngTable',
        'ui.bootstrap',
        'ui.sortable'
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

                // Resource permissions
                permissions: ['$q', 'userService', function($q, userService) {
                    var defer = $q.defer();

                    userService.checkAccessPermission('client-manager').then(function()
                    {
                        defer.resolve();
                    });

                    return defer.promise;
                }],

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

                // Resource permissions
                permissions: ['$q', 'userService', function($q, userService) {
                    var defer = $q.defer();

                    userService.checkAccessPermission('client-manager').then(function()
                    {
                        defer.resolve();
                    }, function()
                    {
                        defer.reject();
                    });

                    return defer.promise;
                }],

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
                        var client = Restangular.one('admin/clients');
                        client.Client = {
                            id: '',
                            deleted:false,
                            fields:[],
                            attributes:[]
                        };

                        deferred.resolve(client);
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

        // Title
        titleService.setTitle( ['Clients', 'Admin'] );

        // Apply the resolved client list
        $scope.allClients = clientList.clients;

        // Modify clients
        _.forEach($scope.allClients, function(client)
        {
            var datetime = new Date(client.activationDate);
            client.activationDate = datetime.getTime();
        });

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
    .controller( 'AdminClientEditCtrl', function AdminClientEditController( $q, $scope, titleService, clientData, clientOptions, $location, $filter, $modal ) {

         // ID Pool. Increment a unique ID for field names when created, for their ID.
        var idPool = 1;

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

        // Sortable options on Fields
        $scope.fieldSortableOptions = {
            update: function(e, ui) {
                // Resolve order of items to data
                // 10ms delay required as update happens before order change
                _.delay(function(scope)
                {
                    for (var i in scope.client.fields)
                    {
                        scope.client.fields[i].order = i;
                    }
                }, 100, $scope);
            },
            handle: '.order'
        };

        // Sortable options on Attributes
        $scope.attributeSortableOptions = {
            update: function(e, ui) {
                // Resolve order of items to data
                // 10ms delay required as update happens before order change
                _.delay(function(scope)
                {
                    for (var i in scope.client.attributes)
                    {
                        scope.client.attributes[i].order = i;
                    }
                }, 100, $scope);
            },
            handle: '.order'
        };

        /**
         * Save Client
         */
        $scope.saveClient = function() {
            var deferred = $q.defer();

            // Fetch data from form
            clientData.Client = $scope.client;

            // Post back to API
            clientData.post().then(

                // On success
                function()
                {
                    // Redirect to user list
                    $location.path('/admin/clients');
                    deferred.resolve();
                },

                // On failure
                function(response)
                {
                    // Extrapolate all fields to the scope
                    $scope.serverErrors = response.data.data;

                    // If field exists, mark is as invalid
                    for (var i in $scope.serverErrors)
                    {
                        if ($scope.clientForm[i] !== undefined) {
                            $scope.clientForm[i].$setValidity('validateServer', false);
                        }
                    }

                    deferred.resolve();
                }
            );

            return deferred.promise;
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
            // Get the field opts
            var options = $scope.options.fieldTypes[ $scope.newField.type ];

            // Set up the field
            var field = {
                '_id': idPool++,
                'name': $scope.newField.name,
                'label': $scope.newField.label,
                'type': $scope.newField.type,
                'order': $scope.client.fields.length,
                'data': {},
                'newField': true
            };

            // Open the Modal
            var modal = $modal.open({
                templateUrl: 'modules/clients/modals/admin-client-edit-field.tpl.html',
                controller: 'AdminClientEditFieldCtrl',
                backdrop: 'static',
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
                $scope.client.fields.push(ret.field);

                // Clear the field items
                $scope.newField = {};
                $scope.newFieldForm.$setPristine();
            });

        };


        /**
         * Edit Fields
         * Opens a modal where the field content can be edited.
         */
        $scope.editField = function( field_id ) {

            // Fetch the object in the array
            var field = $.grep($scope.client.fields, function(e){ return e._id === field_id; });
            field = field[0];

            // Get the array index of this item
            var index = _.indexOf($scope.client.fields, field);

            // Create a copt of the item so we break the binding
            var fieldCopy = angular.copy(field);

            // Fetch the type of field
            var options = $.map($scope.options.fieldTypes, function(v,k){ if (v.alias == field.type) { return v; } });
            options = options[0];

            // Open the Modal
            var modal = $modal.open({
                templateUrl: 'modules/clients/modals/admin-client-edit-field.tpl.html',
                controller: 'AdminClientEditFieldCtrl',
                backdrop: 'static',
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
                $scope.client.fields[ret.index] = ret.field;
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
                '_id': idPool++,
                'name': $scope.newGroup.name,
                'label': $scope.newGroup.label,
                'order': $scope.client.attributes.length,
                'children': [],
                'newGroup': true
            };

            // Open the Modal
            var modal = $modal.open({
                templateUrl: 'modules/clients/modals/admin-client-edit-attribute.tpl.html',
                controller: 'AdminClientEditAttributeCtrl',
                backdrop: 'static',
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

                // Clear the newGroup form
                $scope.newGroup = {};
                $scope.newGroupForm.$setPristine();
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
            var index = _.indexOf($scope.client.attributes, group);

            // Create a copt of the item so we break the binding
            var groupCopy = angular.copy(group);

            // Open the Modal
            var modal = $modal.open({
                templateUrl: 'modules/clients/modals/admin-client-edit-attribute.tpl.html',
                controller: 'AdminClientEditAttributeCtrl',
                backdrop: 'static',
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


        // Sortable options on Fields
        $scope.fieldEditSortableOptions = {
            update: function(e, ui) {
                // Resolve order of items to data
                // 10ms delay required as update happens before order change
                _.delay(function(scope)
                {
                    for (var i in scope.field.data.options)
                    {
                        scope.field.data.options[i].order = i;
                    }
                }, 100, $scope);
            },
            handle: '.order'
        };

        /**
         * Add an option to Select items
         */
        $scope.addOption = function()
        {
            // Set empty array for options if options not set
            if ($scope.field.data.options === undefined)
            {
                $scope.field.data.options = [];
            }

            // Set up field
            var option = $scope.newOption;
            option._id = null;
            option.deleted = false;
            option.order = $scope.field.data.length;

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
    .controller( 'AdminClientEditAttributeCtrl', function AdminClientEditAttributeController( $scope, $modalInstance, $modal, group, index ) {

        // Assign scope vars
        $scope.index = index;
        $scope.group = group;
        $scope.showDeleted = false;

        // list of nodes selected in hierarchy fields
        $scope.hierarchySelected = [];

        //used as a counter for temp keys when adding attrabutes to list but not committing to api yet
        $scope.newId = 10;

        $scope.newAttrName = '';

        /**
         * when writing out group to hierarchy field directive the $id value in the object is lost.
         */
        $scope.fixGroupIds = function() {
            //this is a new element with no idea yet
            if ($scope.group._id == null && $scope.group.children.length === 0) {
                return;
            }

            //fix the parent
            if ($scope.group._id != null && $scope.group._id.$id) {
                $scope.group._id = $scope.group._id.$id;
            }

            //fix the children and grandchildren
            for(var child in $scope.group.children)
            {
                if ($scope.group.children[child]._id !== undefined && !isNaN($scope.group.children[child]._id))
                {
                    $scope.newId = parseInt($scope.group.children[child]._id, 10) + 1;
                }
            }
        };

        /**
         * make sure we fix the id's right away
         */
        $scope.fixGroupIds();

        /**
         * Save changes
         */
        $scope.save = function()
        {
            // Close, and pass back the Field we've edited with the new changes.
            $modalInstance.close({
                'index':$scope.index,
                'group':$scope.group
            });
        };

        /**
         * add a new attribute to the list
         */
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
         * set all selected elements to deleted
         */
        $scope.deleteAttr = function()
        {
            $scope.setDeletedOnChildren(true);
            $scope.hierarchySelected = [];
        };

        /**
         * undelete any selected elements
         */
        $scope.restoreAttr = function()
        {
            $scope.setDeletedOnChildren(false);
            $scope.hierarchySelected = [];
        };

        $scope.setLiveDate = function()
        {
            var modal = $modal.open({
                templateUrl: 'modules/clients/modals/admin-client-network-live-date.tpl.html',
                controller: 'AdminClientLiveDateCtrl',
                backdrop: 'static'
            });

            /**
             * Modal Save
             */
            modal.result.then(function(liveDate) {

                //find any ids that are selected and deleted state
                for(var itemId in $scope.group.children) {
                    if (_.indexOf($scope.hierarchySelected, $scope.group.children[itemId]._id) != -1) {
                        $scope.group.children[itemId].live_date = liveDate;
                    }
                    //TODO do we want to display an error or something if someone selects to set a live date on a sub child
                    // don't forget to check the children
                    for(var subItemId in $scope.group.children[itemId].children) {
                        if (_.indexOf($scope.hierarchySelected, $scope.group.children[itemId].children[subItemId]._id) != -1) {
                            $scope.group.children[itemId].children[subItemId].live_date = liveDate;
                        }
                    }
                }
            });
        };


        /**
         * find any selected elements and set the deleted option to passed in value
         * @param deleted
         */
        $scope.setDeletedOnChildren = function(deleted) {
            //find any ids that are selected and deleted state
            for(var itemId in $scope.group.children) {
                if (_.indexOf($scope.hierarchySelected, $scope.group.children[itemId]._id) != -1) {
                    $scope.group.children[itemId].deleted = deleted;
                }
                //don't forget to check the children
                for(var subItemId in $scope.group.children[itemId].children) {
                    if (_.indexOf($scope.hierarchySelected, $scope.group.children[itemId].children[subItemId]._id) != -1) {
                        $scope.group.children[itemId].children[subItemId].deleted = deleted;
                    }
                }
            }
        };

        /**
         * Delete attribute group
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

    /**
     *  set go live date for selected network ( from edit attribute screen )
     */
    .controller( 'AdminClientLiveDateCtrl', function AdminClientLiveDateController( $scope, $modalInstance ) {

        /**
         * Save changes
         */
        $scope.save = function()
        {
            // Close, and pass back the Field we've edited with the new changes.
            $modalInstance.close({
                'liveDate': $scope.liveDate
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
