angular.module('Preslog.pages', [])
    .config(function config($stateProvider) {
        $stateProvider
            .state( 'slug1', {
                url: '/:slug1',
                views: {
                    "main": {
                        templateUrl: 'pages/index.tpl.html',
                        controller: 'PagesCtrl'
                    }
                }
            })
            .state( 'slug2', {
                url: '/:slug1/:slug2',
                views: {
                    "main": {
                        templateUrl: 'pages/index.tpl.html',
                        controller: 'PagesCtrl'
                    }
                }
            })
            .state( 'slug3', {
                url: '/:slug1/:slug2/:slug3',
                views: {
                    "main": {
                        templateUrl: 'pages/index.tpl.html',
                        controller: 'PagesCtrl'
                    }
                }
            })
        ;
    })
    .controller('PagesCtrl', function ($scope, $stateParams, $templateCache) {
        console.log($stateParams);
        // Build the uri structure based on the 3 slugs, if they exist.
        var uri = $stateParams.slug1;
        if (typeof $stateParams.slug2 !== 'undefined') {
            uri += '/' + $stateParams.slug2;
        }
        if (typeof $stateParams.slug3 !== 'undefined') {
            uri += '/' + $stateParams.slug3;
        }

        $scope.template = false;

        var template = 'pages/partials/' + uri + ".tpl.html";
        if (typeof $templateCache.get(template) !== 'undefined') {
            $scope.template = template;
        }
    })
;