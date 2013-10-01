/**
 * This file/module contains all configuration for the build process.
 */
module.exports = {
    /**
     * The `build_dir` folder is where our projects are compiled during
     * development and the `compile_dir` folder is where our app resides once it's
     * completely built.
     */
    build_dir: 'build/webroot',
    compile_dir: 'bin/webroot',

    /**
     * This is a collection of file patterns that refer to our app code (the
     * stuff in `src/`). These file paths are used in the configuration of
     * build tasks. `js` is all project javascript, less tests. `ctpl` contains
     * our reusable components' (`src/common`) template HTML files, while
     * `atpl` contains the same, but for our app's code. `html` is just our
     * main HTML file, `less` is our main stylesheet, and `unit` contains our
     * app's unit tests.
     */
    app_files: {

        /** JS files and unit tests **/
        js: [ 'src/**/*.js', '!src/**/*.spec.js', '!src/assets/api/**' ],
        jsunit: [ 'src/**/*.spec.js' ],

        /** Coffee Script and unit tests **/
        coffee: [ 'src/**/*.coffee', '!src/**/*.spec.coffee' ],
        coffeeunit: [ 'src/**/*.spec.coffee' ],

        /** Template files */
        atpl: [ 'src/app/**/*.tpl.html' ],
        ctpl: [ 'src/common/**/*.tpl.html' ],
        html: [ 'src/index.html' ],

        /** Less CSS */
        less: 'src/less/main.less',
        app_less: ['src/global.less', 'src/app/**/*.less'],

        /** Files that must be copied */
        files_from_src: [
            '.htaccess'
        ]
    },

    /**
     * This is the same as `app_files`, except it contains patterns that
     * reference vendor code (`vendor/`) that we need to place into the build
     * process somewhere. While the `app_files` property ensures all
     * standardized files are collected for compilation, it is the user's job
     * to ensure non-standardized (i.e. vendor-related) files are handled
     * appropriately in `vendor_files.js`.
     */
    vendor_files: {

        /**
         * Files to be concatenated and minified into the project
         */
        js: [
            'vendor/jquery/jquery.min.js',
            'vendor/jquery-ui/ui/minified/jquery-ui.min.js',

            'vendor/select2/select2.js',

            'vendor/angular-unstable/angular.js',
            'vendor/angular-bootstrap/ui-bootstrap-tpls.min.js',
            'vendor/angular-ui-router/release/angular-ui-router.js',
            'vendor/angular-ui-utils/modules/route/route.js',
            'vendor/angular-ui-select2/src/select2.js',
            'vendor/angular-ui-sortable/src/sortable.js',
            'vendor/angular-resource-unstable/angular-resource.js',
            'vendor/angular-highcharts-directive/src/directives/highcharts.js',
            'vendor/ng-table/ng-table.js',

            'vendor/lodash/dist/lodash.js',
            'vendor/restangular/dist/restangular.js',

            'vendor/dynatree/dist/jquery.dynatree-1.2.4.js',
        ],

        /**
         * Files to NOT be concatenated or minified, but will still be included in the project.
         */
        js_separate: [
            'vendor/RedQueryBuilder/RedQueryBuilderFactory.nocache.js',
            'vendor/RedQueryBuilder/RedQueryBuilder.nocache.js'
        ],

        /**
         * CSS files to be explicitly included in the concatenated CSS
         */
        css: [
            'vendor/select2/select2.css'
        ],

        /**
         * Directories to straight copy into the project
         */
        files: [
            'vendor/RedQueryBuilder/**'
        ],

        /**
         * Directories to copy into the /assets build folder
         */
        files_to_assets: [
            'vendor/select2/select2.png',
            'vendor/select2/select2-spinner.gif',
            'vendor/select2/select2x2.png',

            'vendor/dynatree/src/skin/*.gif',

        ]


    }
};
