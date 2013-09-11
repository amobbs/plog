angular.module('4mValidation', [])
    .directive('passwordConfirm', [function () {
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, ctrl) {
                var firstPassword = '#' + attrs.passwordConfirm;
                elem.add(firstPassword).on('keyup', function () {
                    scope.$apply(function () {
                        var v = elem.val()===$(firstPassword).val();
                        ctrl.$setValidity('passwordConfirm', v);
                    });
                });
            }
        };
    }]);