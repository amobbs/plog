angular.module('Preslog', [
        // Template Modules
        'templates-app',
        'templates-common',

        // Modules and configs
        'moduleManager',
        'stateManager'
    ])

    .config(function myAppConfig($stateProvider, $urlRouterProvider, $locationProvider, RestangularProvider) {
        RestangularProvider.setBaseUrl('/api');
    })

    .run(function run(titleService) {
        titleService.setSuffix('Preslog');
        titleService.setDelimiter(' - ');
    })

    .controller('AppCtrl', function AppCtrl($scope, $location) {
    })

;

