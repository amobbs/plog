/**
 * Title Service
 * Controls the page title, with array elements and delimiters
 */

angular.module('titleService', [])
    .factory('titleService', function ( $document ) {

        var suffix = '';
        var delimiter = '';

        var service = {

            setDelimiter: function( string )
            {
                delimiter = string;
            },

            getDelimiter: function()
            {
                return delimiter;
            },

            setSuffix: function( string )
            {
                suffix = string;
            },

            getSuffix: function()
            {
                return suffix;
            },

            setTitle: function( title )
            {
                // Put to an array if not one already
                if (!title instanceof Array)
                {
                    title = [ title ];
                }

                // Add suffix
                title.push(suffix);

                console.log(title);

                title = title.join( delimiter );

                $document.prop('title', title);
            },

            getTitle: function()
            {
                return $document.prop('title');
            }


        };

        return service;
    });