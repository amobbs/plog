/**
 * File upload workaround
 */

angular.module('fileUpload', [])
    .directive('fileUpload', function() {
    return {
        scope: {
            'fileUpload' : '='
        },
        link: function (scope, el, attrs) {
            el.bind('change', function (event)
            {
                var files = event.target.files;

                for (var i = 0;i<files.length;i++) {
                    scope.fileUpload.push(files);
                }

            });
        }
    };
});