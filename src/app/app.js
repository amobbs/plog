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
        $scope.$on('loadingHandler.loading', function(event, loading) {
            $scope.waitingOnHTTP = loading;
        });
    })

    .constant('dashboard_live_logs', '5260a7d7ad7cc5441b00002b')
    .constant('dashboard_unqualified', '5260bf91ad7cc5782600002a')

;

