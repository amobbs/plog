angular.module('Preslog', [
        // Template Modules
        'templates-app',
        'templates-common',

        // Modules and configs
        'moduleManager',
        'stateManager'
    ])

    .config(function myAppConfig($stateProvider, $urlRouterProvider, $locationProvider, RestangularProvider) {

        // APi directory
        RestangularProvider.setBaseUrl('/api');
    })

    .run(function run(titleService, $rootScope, userService, $log) {

        // Title configuration
        titleService.setSuffix('Preslog');
        titleService.setDelimiter(' - ');

        // accessible global services
        $rootScope.$log = $log;
        $rootScope.userSvc = userService;      // Bugfix: Naming the rootScope var the same as the service seems to break things

    })

    .controller('AppCtrl', function AppCtrl($scope) {
        $scope.$on('loadingHandler.loading', function(event, loading) {
            $scope.waitingOnHTTP = loading;
        });
    })

    .constant('dashboard_live_logs', '5260a7d7ad7cc5441b00002b')
    .constant('dashboard_unqualified', '5260bf91ad7cc5782600002a')

;

