angular.module('stateManager', ['moduleManager', 'stateHelper'])
    .config(function(
        $stateProvider,
        stateHelperProvider,
        $urlRouterProvider,
        $locationProvider
        ) {

        // HTML Templates
        $locationProvider.html5Mode(true);

        // 404 error
        $urlRouterProvider.otherwise('/errors/404');

        // Layouts
        $stateProvider
            .state('publicLayout', {
                abstract: true,
                views: {
                    'app@': { // Points to the ui-view in the index.html
                        templateUrl: 'layouts/public.tpl.html'
                    }
                }
            })
            .state('mainLayout', {
                abstract: true,
                views: {
                    'app@': { // Points to the ui-view in the index.html
                        templateUrl: 'layouts/main.tpl.html'
                    }
                }
            })
            .state('modalLayout', {
                abstract: true,
                views: {
                    'app@': { // Points to the ui-view="app" in the index.html
                        templateUrl: 'layouts/modal.tpl.html'
                    }
                }
            })
        ;

        // Inject States
        angular.forEach(stateHelperProvider.getStates(), function(state) {
            $stateProvider.state(state.name, state.options);
        });
    })
;