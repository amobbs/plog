angular.module( 'Preslog.about', [
  'ui.state',
  'placeholders',
  'ui.bootstrap',
  'titleService'
])

.config(function config( $stateProvider ) {
  $stateProvider.state( 'about', {
    url: '/what-is-this',
    views: {
      "main": {
        controller: 'AboutCtrl',
        templateUrl: 'what-is-this/about.tpl.html',
        resolve: {
            fooData: ['$q', 'Restangular', function($q, Restangular) {
                var deferred = $q.defer(),
                    ret;

                Restangular.one('foo', 3).get({group_id: 3}).then(function(fooData) {
                    ret = fooData;

                    ret.asdf = 'asdfasdf';

                    ret.foo = 'bar';

                    deferred.resolve(ret);
                });

                return deferred.promise;
            }]
        }
      }
    }
  });
})

.controller( 'AboutCtrl', function AboutCtrl( $scope, titleService, fooData ) {
  titleService.setTitle( 'What is It?' );
  
  // This is simple a demo for UI Boostrap.
  $scope.dropdownDemoItems = [
    "The first choice!",
    "And another choice for you.",
    "but wait! A third!"
  ];
})

;
