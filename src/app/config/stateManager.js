angular.module('stateManager', ['moduleManager'])
    .config(function(
        $stateProvider,
        statesHome,
        statesLogin,
        statesPages
        ) {
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

        var states = [].concat(
            statesHome,
            statesLogin,
            statesPages
        );
        angular.forEach(states, function(state) {
            $stateProvider.state(state.name, state.options);
        });
    })
;