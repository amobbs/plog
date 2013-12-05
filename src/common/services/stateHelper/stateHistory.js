angular.module('stateHistory', ['ui.router'])

    .factory('stateHistory', function ($state) {
        var history = [];

        return {
            addHistory: function(state, params) {
                history.push({
                    state: state,
                    params: params
                });
            },
            setCurrent: function(state) {
                current = state;
            },
            goBack: function() {
                if (history.length === 1)
                {
                    return false;
                }

                var state = history.pop();

                // Can't "go back" to the same
                if (state.state.url == current.url)
                {
                    return false;
                }

                $state.transitionTo(state.state, state.params);
            },
            goForward: function() {
                console.log('stateHistory.goForward not implemented');
            }
        };
    })

    .run(function($rootScope, stateHistory) {
        $rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams) {
            stateHistory.addHistory(fromState, fromParams);
            stateHistory.setCurrent(toState);
        });
    })
;