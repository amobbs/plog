/*jshint loopfunc: true */
/**
 * Log Field Directive
 */

angular.module('logFields', [])
    .directive('logFields', ['$templateCache', '$compile', '$interpolate', '$filter', function ( $templateCache, $compile, $interpolate, $filter ) {


        /**
         * Field types
         * @type {Array}
         */
        var fieldTemplates = [
            {'type':'select',                   'width': 0.5,   'template':'modules/log/fields/select.tpl.html'},
            {'type':'select-severity',          'width': 0.5,   'template':'modules/log/fields/select-severity.tpl.html'},
            {'type':'select-impact',            'width': 0.5,   'template':'modules/log/fields/select-impact.tpl.html'},
            {'type':'select-accountability',    'width': 0.5,   'template':'modules/log/fields/select-accountability.tpl.html'},
            {'type':'textarea',                 'width': 1,     'template':'modules/log/fields/textarea.tpl.html'},
            {'type':'textbig',                  'width': 1,     'template':'modules/log/fields/textbig.tpl.html'},
            {'type':'textsmall',                'width': 0.5,   'template':'modules/log/fields/textsmall.tpl.html'},
            {'type':'datetime',                 'width': 0.5,   'template':'modules/log/fields/datetime.tpl.html'},
            {'type':'checkbox',                 'width': 0.5,   'template':'modules/log/fields/checkbox.tpl.html'},
            {'type':'duration',                 'width': 0.5,   'template':'modules/log/fields/duration.tpl.html'},
            {'type':'loginfo',                  'width': 1,     'template':'modules/log/fields/loginfo.tpl.html'}
        ];


        /**
         * Linker.
         * - Go through all the
         * @param scope
         * @param element
         * @param attrs
         */
        var linker = function( scope, element, attrs, ctrl ) {

            if (scope.logdata === undefined)
            {
                scope.logdata = [];
            }

            // Sort the FIELDS data into the correct order.
            scope.logfields = scope.logfields.sort(function(a,b) {
                return (parseInt(a.order, 10) < parseInt(b.order, 10)) ? -1 : (parseInt(a.order, 10) > parseInt(b.order, 10)) ? 1 : 0;
            });

            // Placeholder
            var field = {};

            // Track current width. Used for creating row containers
            var currentWidth = 1;

            // Html containers
            var rowElementTemplate = angular.element( '<div class="row-fluid"></div>' );
            var rowElement = rowElementTemplate;
            var rows = [];

            for (var i in scope.logfields)
            {
                // Quick ref for this field
                field = scope.logfields[i];

                // Fetch the data from the data array by the ID of the field
                var data = $.map( scope.logdata, function(v,k){ if (v.field_id == field._id) { return v; } });

                if ( !data.length )
                {
                    // instigate the data object onto scope.data if it's missing
                    data = {
                        'field_id':field._id,
                        'data':{}
                    };

                    //set the default value for the datetime field to the current value (in RFC2822 format)
                    if (field.type == 'datetime')
                    {
                        data.data.datetime = $filter('date')(new Date(), 'EEE, dd MMM yyyy HH:mm:ss Z');
                    }

                    scope.logdata.push(data);
                }
                else
                {
                    // Reduce data array to one item
                    data = data[0];
                }

                // Validate: Do we need to display this field?
                // Skip if it's deleted and does not contain data.
                if (field.deleted === true && data === undefined || field.is_hidden)
                {
                    continue;
                }

                // Match the field type from template list
                var type = $.map(fieldTemplates, function(v,k){ if (v.type == field.type) { return v; } });

                // Unmatched field types get skipped so as not to produce errors
                if (type.length < 1)
                {
                    continue;
                }

                // Reduce type array to one item
                type = type[0];

                // Fetch template from cache by type
                var tpl = $templateCache.get(type.template);

                // Construct TplScope for use with new template item
                var tplScope = scope.$new();
                tplScope.log = data;
                tplScope.options = field;
                tplScope.logForm = scope.$parent.logForm;

                // Compile to gain scope
                tpl = $compile(tpl)(tplScope);

                // Start a new row if the width is out of range
                if (currentWidth + type.width > 1)
                {
                    // Prep to object
                    rowElement = rowElementTemplate.clone();
                    rows.push(rowElement);

                    // Reset width
                    currentWidth = 0;
                }

                // Append our element to the row
                rowElement.append(tpl);
                currentWidth += type.width;
            }

            // Condense each row to actual html
            for (i in rows)
            {
                // each row ..
                element.append( rows[i] );
            }

            // Attach dynamic elements to the parent form
            // Find all ng-model references under this item
            element.find("*[ng-model]").each(function()
            {
                // Get the element
                var fieldElement = angular.element(this);
                var modelController = fieldElement['inheritedData']('$ngModelController');
                var formController = fieldElement['inheritedData']('$formController');
                var id = '';

                // use element if present
                if (fieldElement[0].id !== undefined)
                {
                    id = fieldElement[0].id;
                }

                // use element if not empty
                if (id !== '')
                {
                    // Interpolate the fieldElement.id using the parent element scope.
                    // This created a dynamic "name" field by which the ngModel is attached to the form.
                    // Giving us something to validate against later on.
                    var elementScope = fieldElement.scope();

                    // Use the parent scope if the "options" value isn't present on this scope.
                    // Varies from element to element depending on the directive used.
                    if(elementScope.options === undefined)
                    {
                        elementScope = elementScope.$parent;
                    }

                    // Interpolate the name
                    modelController.$name = $interpolate(id)(elementScope);

                    // Tie to the form
                    formController.$addControl(modelController);
                }
            });
        };


        /**
         * Establish Directive
         */
        return {
            restrict: "EA",
            priority: -1,
            transclude: true,
            link: linker,
            scope: {
                logfields: '=',
                logdata: '='
            }
        };
    }]);