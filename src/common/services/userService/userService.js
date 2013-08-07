/**
 * Preslog User Service
 */

angular.module('userService', ['restangular'])
    .factory('userService', function (Restangular, $q) {
        var user,
            teams,
            currentTeam;

        var ret = {
            getUser: function () {
                var deferred = $q.defer();
                if (! user) {
                    Restangular.all('account/verifycredentials').getList().then(function (ret) {
                        user = ret.user;
                        deferred.resolve(user);
                    });
                } else {
                    deferred.resolve(user);
                }

                return deferred.promise;
            },
            getTeams: function () {
                var deferred = $q.defer();
                if (! teams) {
                    Restangular.all('teams').getList().then(function (ret) {
                        teams = ret.teams;
                        deferred.resolve(teams);
                    });
                } else {
                    deferred.resolve(teams);
                }

                return deferred.promise;

            },
            getCurrentTeam: function () {
                var deferred = $q.defer();

                if (! currentTeam) {
                    ret.getTeams().then(function(teams) {
                        currentTeam = teams[0];
                        deferred.resolve(currentTeam);
                    });
                } else {
                    deferred.resolve(currentTeam);
                }

                return deferred.promise;
            },
            setCurrentTeam: function(team) {
                currentTeam = team;
            }
        };

        return ret;
    });