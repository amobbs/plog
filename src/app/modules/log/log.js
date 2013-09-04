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
angular.module( 'Preslog.log', [
        'titleService'
    ])

    .config(function(stateHelperProvider) {
        stateHelperProvider.addState('mainLayout.log', {
            url: '/log',
            views: {
                "main@mainLayout": {
                    controller: 'LogCtrl',
                    templateUrl: 'modules/log/log.tpl.html'
                }
            }
        });
    })


    .directive('hierachyFields', function() {
        return function(scope, element, attrs) {
            scope.$watch(attrs.hierachyFields, function (value) {
                drawHierachy(value);
            });

            function drawHierachy(fields) {
                $(element).dynatree({
                    generateIds: true,
                    idPrefix: 'hf',
                    imagePath: 'assets/vendor/dynatree/src/skin/',
                    checkbox: true,
                    selectMode: 3,
                    onSelect: function(selected, dtnode) {

                        scope.$apply(function() {
                            var id = parseInt(dtnode.data.key, 10);

                            if (selected && (scope.selectedIds.indexOf(id) == -1 )) {
                                scope.selectedIds.push(id);
                            } else if (!selected && (scope.selectedIds.indexOf(id) != -1)) {
                                var index = scope.selectedIds.indexOf(id);
                                scope.selectedIds.splice(index, 1);
                            }
                        });
                    },
                    children: parseHierachyToDyna(fields)
                });
            }

            function parseHierachyToDyna(field) {
                var dynaField = {};
                var isInitiallySelected = (scope.selectedIds.indexOf(field.id) > -1);
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
                        dynaField.children.push(parseHierachyToDyna(child));
                    });
                }

                return dynaField;
            }


        };
    })

/**
 * And of course we define a controller for our route.
 */
    .controller( 'LogCtrl', function LogController( $scope, titleService ) {
        console.log('log');
        titleService.setTitle( 'Log' );

        $scope.groups = [
            {
                id: 1, name: "Networks", deleted: false, children: [
                {id: 2, name:"ABC", deleted: false, children: [
                    {id: 3, name: "ABC", deleted: false},
                    {id: 4, name: "ABC 2", deleted: false},
                    {id: 5, name: "ABC 3", deleted: false},
                    {id: 6, name: "ABC News", deleted: false}
                ]},
                {id: 7, name:"WIN", deleted: false, children: [
                    {id: 8, name: "Win", deleted: false}
                ]},
                {id: 9, name:"Blah", deleted: false, children: [
                    {id: 10, name: "Win", deleted: false}
                ]},
                {id: 11, name:"Blah", deleted: false, children: [
                    {id: 12, name: "Win", deleted: false}
                ]}
            ]
            },
            {
                id: 13, name: "States", deleted: false, children: [
                {id: 14, name: "", deleted: false, children: [
                    {id: 15, name: 'NSW', deleted: false},
                    {id: 16, name: 'VIC', deleted: false},
                    {id: 17, name: 'QLD', deleted: false},
                    {id: 18, name: 'WA', deleted: false}
                ]
                }
            ]
            }
        ];

        $scope.selectedIds = [];
    })


;

