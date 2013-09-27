angular.module('logElement', [])
    .directive('logElement', ['$templateCache', function ( $templateCache ) {

        /**
         * Field types
         * @type {Array}
         */
        var fieldTypes = [
            {'type':'select',           'template':'modules/log/fields/select.tpl.html'},
            {'type':'select-severity',  'template':'modules/log/fields/select-severity.tpl.html'},
            {'type':'select-impact',    'template':'modules/log/fields/select-impact.tpl.html'},
            {'type':'textarea',         'template':'modules/log/fields/textarea.tpl.html'},
            {'type':'textbig',          'template':'modules/log/fields/textbig.tpl.html'},
            {'type':'textsmall',        'template':'modules/log/fields/textsmall.tpl.html'},
            {'type':'loginfo',          'template':'modules/log/fields/loginfo.tpl.html'},
            {'type':'datetime',         'template':'modules/log/fields/datetime.tpl.html'},
            {'type':'duration',         'template':'modules/log/fields/duration.tpl.html'},
            {'type':'loginfo',          'template':'modules/log/fields/loginfo.tpl.html'}
        ];


        /**
         * Linker
         * @param scope
         * @param element
         * @param attrs
         */
        var linker = function( scope, element, attrs ) {

            console.log(scope);

            // Field type
            var type = $.map(fieldTypes, function(v,k){ if (v.type == attrs.type) { return v; } });
            type = type[0];

            // Fetch template from cache
            var tpl = $templateCache.get(type.template);

            // Replace element
            element.html(tpl).show();

            // Compile to gain scope
            $compile(element.contents())(scope);
        };


        /**
         * Establish Directive
         */
        return {
            restrict: "E",
            replace: true,
            link: linker,
            scope: {
                content:'='
            }
        };
    }]);