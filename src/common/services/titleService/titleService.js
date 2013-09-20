/**
 * Title Service
 * Controls the page title, with array elements and delimiters
 */

angular.module('titleService', [])
    .factory('titleService', function ( $document ) {

        // Title suffix
        var suffix = '';

        // Title delimiter
        var delimiter = ' - ';

        var service = {


            /**
             * Set the Delimiter to use in the title
             * @param string
             */
            setDelimiter: function( string )
            {
                delimiter = string;
            },


            /**
             * Get the delimiter that has been set
             * @returns {string}
             */
            getDelimiter: function()
            {
                return delimiter;
            },


            /**
             * Set the suffix for the title
             * @param string
             */
            setSuffix: function( string )
            {
                suffix = string;
            },


            /**
             * Get the suffix for the title
             * @returns {string}
             */
            getSuffix: function()
            {
                return suffix;
            },


            /**
             * Set the current page title
             * @param title
             */
            setTitle: function( title )
            {
                // Put to an array if not one already
                if (!(title instanceof Array))
                {
                    title = [ title ];
                }

                // Add suffix, if suffix is set
                if (suffix.length) {
                    title.push(suffix);
                }

                // Add delimiters and cast to string
                title = title.join( delimiter );

                // Update title
                $document.prop('title', title);
            },


            /**
             * Get the current page title
             * @returns {*}
             */
            getTitle: function()
            {
                return $document.prop('title');
            }

        };

        return service;
    });