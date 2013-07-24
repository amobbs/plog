angular.module('pages', ['ui.bootstrap'])
    .constant('statesPages', [{
        name: 'mainLayout.pages',
        options: {
            url: '/{uri:.*}',
            views: {
                "main@mainLayout": {
                    templateUrl: 'modules/pages/index.tpl.html',
                    controller: [
                        '$scope',
                        '$stateParams',
                        '$templateCache',
                        '$state',
                        function($scope, $stateParams, $templateCache, $state) {
                            $scope.template = false;

                            var template = 'modules/pages/templates/' + $stateParams.uri + ".tpl.html";

                            if (typeof $templateCache.get(template) !== 'undefined') {
                                $scope.template = template;
                                console.log(template);
                            }
                        }
                    ]
                }
            }
        }
    }])
;