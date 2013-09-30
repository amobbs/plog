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
                'hierarchyFields' : '=',
                'hierarchySelected' : '=',
                'dragAndDrop' : '@',
                'hideDeleted' : '@',
                'allowEdit': '@'
            },

            // Create link with the element
            link: function(scope, element, attrs) {

                // Draw the hierarchy on scope change
                scope.$watch(scope.hierarchyFields, function () {
                    drawHierarchy(scope.hierarchyFields, scope.hideDeleted, scope.dragAndDrop, scope.allowEdit);
                }, true);

                // Draw dynaTree
                function drawHierarchy(fields, hideDeleted, enableDnD, allowEdit) {
                    options = {
                        extensions: ["dnd"],
                        generateIds: true,
                        idPrefix: 'hf-',
                        imagePath: 'assets/vendor/dynatree/src/skin/',
                        checkbox: true,
                        selectMode: 3,
                        cookieId: "hf",
                        onSelect: function(selected, dtnode) {

                            // On dynaTree change, pass selection back to model
                            scope.$apply(function() {
                                var id = dtnode.data.key;

                                if (selected && (scope.hierarchySelected.indexOf(id) == -1 )) {
                                    scope.hierarchySelected.push(id);
                                } else if (!selected && (scope.hierarchySelected.indexOf(id) != -1)) {
                                    var index = scope.hierarchySelected.indexOf(id);
                                    scope.hierarchySelected.splice(index, 1);
                                }
                            });
                        },
                        onDblClick: function(node, event) {
                            if (allowEdit) {
                                editNode(node);
                            }
                            return false;
                        },
                        children: parseHierarchyToDyna(fields, hideDeleted)
                    };

                    if (enableDnD) {
                        options.dnd = {
                            onDragStart: function(node) {
                                /** This function MUST be defined to enable dragging for the tree.
                                 *  Return false to cancel dragging of node.
                                 */
                                return true;
                            },
                            onDragStop: function(node) {
                                // This function is optional.
                            },
                            autoExpandMS: 1000,
                            preventVoidMoves: true, // Prevent dropping nodes 'before self', etc.
                            onDragEnter: function(node, sourceNode) {
                                /** sourceNode may be null for non-dynatree droppables.
                                 *  Return false to disallow dropping on node. In this case
                                 *  onDragOver and onDragLeave are not called.
                                 *  Return 'over', 'before, or 'after' to force a hitMode.
                                 *  Return ['before', 'after'] to restrict available hitModes.
                                 *  Any other return value will calc the hitMode from the cursor position.
                                 */
                                return true;
                            },
                            onDragOver: function(node, sourceNode, hitMode) {
                                /** Return false to disallow dropping this node.
                                 *
                                 */
                                logMsg("tree.onDragOver(%o, %o, %o)", node, sourceNode, hitMode);
                                // Prevent dropping a parent below it's own child
                                if(node.isDescendantOf(sourceNode)){
                                    return false;
                                }
                                // Prohibit creating childs in non-folders (only sorting allowed)
                                if( !node.data.isFolder && hitMode === "over" ){
                                    return "after";
                                }
                            },
                            onDrop: function(node, sourceNode, hitMode, ui, draggable) {
                                //move the node in the tree for display
                                sourceNode.move(node, hitMode);
                                var nodeKey = node.data.key.replace('_', ''); //get rid of _ added by dynaTree
                                var sourceNodeKey = sourceNode.data.key.replace('_', ''); //get rid of _ added by dynaTree

                                //find where the nodes are now
                                var movingFrom = {};
                                var movingFromIndex = -1;
                                var movingTo = {};
                                var movingToIndex = -1;
                                for (var i in scope.hierarchyFields) {
                                    //remember where the sourceNode is
                                    if (scope.hierarchyFields[i]._id == sourceNodeKey) {
                                        movingFrom = scope.hierarchyFields;
                                        movingFromIndex = i;
                                    }
                                    //remember where the node is
                                    if (scope.hierarchyFields[i]._id == nodeKey) {
                                        if (hitMode == 'over') {
                                            movingTo = scope.hierarchyFields[i];
                                        } else {
                                            movingTo = scope.hierarchyFields;
                                        }
                                        movingToIndex = i;
                                    }

                                    for(var c in scope.hierarchyFields[i].children) {
                                        //remember where the sourceNode is
                                        if (scope.hierarchyFields[i].children[c]._id == sourceNodeKey) {
                                            movingFrom = scope.hierarchyFields[i];
                                            movingFromIndex = c;
                                        }

                                        //remember where the node is
                                        if (scope.hierarchyFields[i].children[c]._id == nodeKey) {
                                            if (hitMode == 'over') {
                                                movingTo = scope.hierarchyFields[i].children[c];
                                            } else {
                                                movingTo = scope.hierarchyFields[i];
                                            }
                                            movingToIndex = c;
                                        }
                                    }
                                }

                                if (hitMode == 'after') {
                                    movingToIndex++;
                                }

                                //make the move
                                var moveNode = {};

                                //the top node is just an array not an object
                                if (movingFrom.children) {
                                    moveNode = movingFrom.children.splice(movingFromIndex, 1)[0];
                                } else {
                                    moveNode = movingFrom.splice(movingFromIndex, 1)[0];
                                }

                                if (movingTo.children) {
                                    movingTo.children.splice(movingToIndex, 0, moveNode);
                                } else {
                                    movingTo.splice(movingToIndex, 0, moveNode);
                                }

                                // expand the drop target
                                //sourceNode.expand(true);
                            },
                            onDragLeave: function(node, sourceNode) {
                                /** Always called if onDragEnter was called.
                                 */
                            }
                        };
                    }

                     $(element).dynatree(options);
                }

                function editNode(node) {
                    var prevTitle = node.data.title,
                        tree = node.tree;
                    // Disable dynatree mouse- and key handling
                    tree.$widget.unbind();
                    // Replace node with <input>
                    $('.dynatree-title', node.span).html("<input id='editNode' value='" + prevTitle + "'>");
                    // Focus <input> and bind keyboard handler
                    $('input#editNode')
                        .focus()
                        .keydown(function(event){
                            switch( event.which ) {
                            case 27: // [esc]
                                // discard changes on [esc]
                                $("input#editNode").val(prevTitle);
                                $(this).blur();
                                break;
                            case 13: // [enter]
                                // simulate blur to accept new value
                                $(this).blur();
                                break;
                            }
                        })
                        .blur(function(event){
                            // Accept new value, when user leaves <input>
                            var title = $("input#editNode").val();
                            node.setTitle(title);
                            //find node and update title
                            for(var i in scope.hierarchyFields) {
                                if (scope.hierarchyFields[i]._id == node.data.key) {
                                    scope.hierarchyFields[i].name = title;
                                    break;
                                }
                                for(var c in scope.hierarchyFields[i].children) {
                                    if (scope.hierarchyFields[i].children[c]._id == node.data.key) {
                                        scope.hierarchyFields[i].children[c].name = title;
                                        break;
                                    }
                                }
                            }
                            // Re-enable mouse and keyboard handlling
                            tree.$widget.bind();
                            node.focus();
                        });
                }

                // Parse field population and selection
                function parseHierarchyToDyna(fields, hideDeleted) {
                    var dynaFields = [];

                    for(var i in fields) {
                        var dynaField = {};
                        var isInitiallySelected = (scope.hierarchySelected.indexOf(fields[i].id) > -1);
                        dynaField.select = isInitiallySelected;
                        if (fields[i].deleted && fields[i].deleted === true) {
                            if (hideDeleted === false) {
                                continue;
                            } else {
                                dynaField.select = true;
                            }
                        }

                        dynaField.title = fields[i].name;
                        dynaField.expand = false;
                        dynaField.key = fields[i]._id;
                        if (fields[i].children && (fields[i].children.length > 0)) {

                            dynaField.expand = true;
                            dynaField.isFolder = true;
                            dynaField.children = [];
                            dynaField.children = parseHierarchyToDyna(fields[i].children);
                        }
                        dynaFields.push(dynaField);
                    }


                    return dynaFields;
                }
            }
        };
    });