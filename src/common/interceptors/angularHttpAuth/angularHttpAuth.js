/**
 * @license HTTP Auth Interceptor Module for AngularJS
 * (c) 2012 Witold Szczerba
 * License: MIT
 */
angular.module('httpAuthInterceptor', [])
/**
 * $http interceptor.
 * On 403 response - it broadcasts 'event:angular-auth-loginRequired'.
 */
    .config(function($httpProvider) {
        $httpProvider.responseInterceptors.push('authInterceptor');
    })

    .factory('authService', function($rootScope) {
        return {
            loginConfirmed: function(user) {
                $rootScope.$broadcast('event:auth-loginConfirmed', user);
            }
        };
    })

    .factory('authInterceptor', function($rootScope, $q) {
        return function (promise) {
            return promise.then(function(response) { // Success Callback
                return response;
            }, function(response) { // Error Callback
                if (401 === response.status) {
                    $rootScope.$broadcast('event:auth-loginRequired');
                }
                // otherwise
                return $q.reject(response);
            });
        };
    })
;