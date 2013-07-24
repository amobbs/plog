angular.module('Preslog', [
        // Template Modules
        'templates-app',
        'templates-common',

        // Modules and configs
        'moduleManager',
        'stateManager'
    ])

    .config(function myAppConfig($stateProvider, $urlRouterProvider, $locationProvider, RestangularProvider) {
        $urlRouterProvider.otherwise('/');
        $locationProvider.html5Mode(true);
        RestangularProvider.setBaseUrl('/api');
    })

    .run(function run(titleService) {
        titleService.setSuffix(' | Preslog');
    })

    .controller('AppCtrl', function AppCtrl($scope, $location) {
    })

;

