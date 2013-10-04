/*jshint loopfunc: true */
/**
 * Log Field Directive
 */

angular.module('logFields', [])
    .directive('logFields', ['$templateCache', '$compile', function ( $templateCache, $compile ) {

        /**
         * Field types
         * @type {Array}
         */
        var fieldTemplates = [
            {'type':'select',           'width': 0.5,   'template':'modules/log/fields/select.tpl.html'},
            {'type':'select-severity',  'width': 0.5,   'template':'modules/log/fields/select-severity.tpl.html'},
            {'type':'select-impact',    'width': 0.5,   'template':'modules/log/fields/select-impact.tpl.html'},
            {'type':'textarea',         'width': 1,     'template':'modules/log/fields/textarea.tpl.html'},
            {'type':'textbig',          'width': 1,     'template':'modules/log/fields/textbig.tpl.html'},
            {'type':'textsmall',        'width': 0.5,   'template':'modules/log/fields/textsmall.tpl.html'},
            {'type':'datetime',         'width': 0.5,   'template':'modules/log/fields/datetime.tpl.html'},
            {'type':'checkbox',         'width': 0.5,   'template':'modules/log/fields/checkbox.tpl.html'},
            {'type':'duration',         'width': 0.5,   'template':'modules/log/fields/duration.tpl.html'},
            {'type':'loginfo',          'width': 1,     'template':'modules/log/fields/loginfo.tpl.html'}
        ];


        /**
         * Linker.
         * - Go through all the
         * @param scope
         * @param element
         * @param attrs
         */
        var linker = function( scope, element, attrs, ctrl ) {

            // TODO: DELETE ME
            if (scope.data === undefined)
            {
                scope.data = [];
            }

            // Sort the FIELDS data into the correct order.
            scope.fields = scope.fields.sort(function(a,b) {
                return (a.order < b.order) ? -1 : (a.order > b.order) ? 1 : 0;
            });

            // Placeholder
            var field = {};

            // Track current width. Used for creating row containers
            var currentWidth = null;

            // Html containers
            var rowElement = null;
            var rows = [];

            for (var i in scope.fields)
            {
                // Quick ref for this field
                field = scope.fields[i];

                // Fetch the data from the data array by the ID of the field
                var data = $.map( scope.data, function(v,k){ if (v.field_id == field._id) { return v; } });

                // Validate: Do we need to display this field?
                // Skip if it's deleted and does not contain data.
                if (field.deleted === true && data === undefined)
                {
                    console.log('skipped Field '+field.name); // TODO: DELETE ME
                    continue;
                }

                // Match the field type from template list
                var type = $.map(fieldTemplates, function(v,k){ if (v.type == field.type) { return v; } });

                // Unmatched field types get skipped so as not to produce errors
                if (type.length < 1)
                {
                    console.log('skipped Type: '+field.type);   // TODO: DELETE ME
                    continue;
                }

                // Reduce type array to one item
                type = type[0];

                // Reduce data array to one item
                if (data.length)
                {
                    data = data[0];
                }

                // Fetch template from cache by type
                var tpl = $templateCache.get(type.template);

                // Construct TplScope for use with new template item
                var tplScope = scope.$new();
                tplScope.log = data;
                tplScope.options = field;

                // Compile to gain scope
                tpl = $compile(tpl)(tplScope);

                // Start a new row if the width is out of range
                if (currentWidth + type.width > 1 || currentWidth === null)
                {
                    // Store current
                    if (rowElement !== null)
                    {
                        rows.push(rowElement);
                    }

                    // Make new row
                    rowElement = angular.element( '<div class="row-fluid"></div>' );
                    currentWidth = 0;
                }

                // Append to row
                rowElement.append(tpl);
                currentWidth += type.width;
            }


            // Condense rows to html
            for (i in rows)
            {
                // each row ..
                element.append( rows[i] );
            }

        };


        /**
         * Establish Directive
         */
        return {
            restrict: "E",
            replace: true,
            transclude: true,
            link: linker,
            scope: {
                fields: '=',
                data: '='
            }
        };
    }]);