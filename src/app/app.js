angular.module('Preslog', [
        // Template Modules
        'templates-app',
        'templates-common',
        // Vendor Modules
        'ui.state',
        'ui.route',
        'restangular',
        // Site Modules
        'Preslog.home',
        'Preslog.about',
        'Preslog.pages'
    ])

    .config(function myAppConfig($stateProvider, $urlRouterProvider, $locationProvider, RestangularProvider) {
        $urlRouterProvider.otherwise('/home');
        $locationProvider.html5Mode(true);
        RestangularProvider.setBaseUrl('/api');
    })

    .run(function run(titleService) {
        titleService.setSuffix(' | Preslog');
    })

    .controller('AppCtrl', function AppCtrl($scope, $location) {
    })

;

