/**
 * Hierarchy fields using DynaTree
 */

angular.module('hierarchyFields', [])
    .directive('hierarchyFields', function() {
        return {

            restrict:'A',
            transclude: true,

            // Data binding on two fields
            scope: {
                'hierarchyFields' : '@',
                'hierarchySelected' : '@'
            },

            // Create link with the element
            link: function(scope, element, attrs) {

                // Draw the hierarchy on scope change
                scope.$watch(scope.hierarchyFields, function (value) {
                    drawHierarchy(value);
                });

                // Draw dynaTree
                function drawHierarchy(fields) {
                    $(element).dynatree({
                        generateIds: true,
                        idPrefix: 'hf',
                        imagePath: 'assets/vendor/dynatree/src/skin/',
                        checkbox: true,
                        selectMode: 3,
                        onSelect: function(selected, dtnode) {

                            // On dynaTree change, pass selection back to model
                            scope.$apply(function() {
                                var id = parseInt(dtnode.data.key, 10);

                                if (selected && (scope.hierarchySelected.indexOf(id) == -1 )) {
                                    scope.hierarchySelected.push(id);
                                } else if (!selected && (scope.hierarchySelected.indexOf(id) != -1)) {
                                    var index = scope.hierarchySelected.indexOf(id);
                                    scope.hierarchySelected.splice(index, 1);
                                }
                            });
                        },
                        children: parseHierarchyToDyna(fields)
                    });
                }

                // Parse field population and selection
                function parseHierarchyToDyna(field) {
                    var dynaField = {};
                    var isInitiallySelected = (scope.hierarchySelected.indexOf(field.id) > -1);
                    if (field.deleted && isInitiallySelected) {
                        return;
                    }
                    dynaField.title = field.name;
                    dynaField.expand = true;
                    dynaField.select = isInitiallySelected;
                    dynaField.key = field.id;
                    if (field.children) {
                        dynaField.isFolder = (field.children.length > 0);
                        dynaField.children = [];
                        field.children.forEach(function(child) {
                            dynaField.children.push(parseHierarchyToDyna(child));
                        });
                    }

                    return dynaField;
                }
            }
        };
    });