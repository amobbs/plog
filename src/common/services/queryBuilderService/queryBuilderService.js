/**
 * Preslog Query Builder Service
 * - Responsible for query Builder population and translation.
 */
angular.module('queryBuilderService', ['restangular'])

/**
 * User Service Object
 */
    .factory('queryBuilderService', function ($q, Restangular) {

        /**
         * Service
         */
        var service = {


            /**
             * Convert JQL statement to SQL
             * @param   jql
             * @return  promise
             */
            jqlToSql: function(jql)
            {
                var deferred = $q.defer();

                //convert jql to sql
                Restangular.one('search/wizard/params')
                    .get({'jql':jql})
                    .then(function(data)
                    {
                        // Bail if an error occurred
                        if (data === undefined) {
                            deferred.reject();
                        }

                        result = {};

                        // Show errors, if errors returned.
                        if (data.errors)
                        {
                            result.queryValid = false;
                            result.logWidgetParams = {'errors': data.errors};
                            deferred.reject(result);
                        }

                        // OK if no errors!
                        result.queryValid = true;
                        result.logWidgetParams = {errors:[]};

                        // Pass back SQL
                        result.sql = data.sql;

                        // Send back RQB meta-data for field population
                        result.queryMeta = data.fieldList;
                        result.selectOptions = data.selectOptions;

                        // Pass back arguments
                        result.args = [];
                        for (var i in data.args)
                        {
                            // :BUGFIX: Red Query Builder does not have dates implemented correctly.
                            // We need to parse/fudge the date into the internal format RQB expects.
                            if (data.args[i].type == 'date')
                            {
                                // "q" contains the Date object, while "cM" is used for type-casting detection.
                                // Warning: These are minified variable names, they're likely to change if you update RQB!
                                data.args[i].value = {q:new Date(data.args[i].value), cM:{97:1}};
                            }

                            // Put arg to array
                            result.args.push(data.args[i].value);
                        }

                        // Return result
                        return deferred.resolve(result);
                    });

                return deferred.promise;
            },


            /**
             * Convert SQL statement to JQL
             * @param   sql
             * @return  promise
             */
            sqlToJql: function(sql, args)
            {
                var deferred = $q.defer();

                // Parse arguments supplied by the query builder for use in the statement
                var parsedArgs = [];
                var getClassOf = Function.prototype.call.bind(Object.prototype.toString);
                for(var i in args)
                {
                    // :BUGFIX: Sometimes we get a date object, sometimes we get an RQB date object.
                    // parse these formats so only send the server the type of date it's expecting.
                    if (args[i].q || getClassOf(args[i]) == '[object Date]')
                    {
                        var date = args[i];
                        if (args[i].q)
                        {
                            date = args[i].q;
                        }

                        // pad with zeros
                        var month = ('0' + (date.getMonth() + 1)).slice(-2);
                        var day = ('0' + date.getDate()).slice(-2);

                        args[i] = date.getFullYear() + '-' + month + '-' + day;
                    }
                    // :BUGFIX: If the type is supplied, we need to rewrite the value
                    // Note that some of these will come in with just args[i] as the value.
                    else if ( args[i].value )
                    {
                        args[i] = args[i].value;
                    }

                    // Push to parsed arguments
                    parsedArgs.push(args[i]);
                }

                // Get the translation from the server
                Restangular.one('search/wizard/translate')
                    .get({
                        'sql':sql,
                        'args':JSON.stringify(parsedArgs)
                    })
                    .then(function(data) {

                        // If no data, reject the request
                        if (data === undefined) {
                            deferred.reject();
                        }

                        // Return the data
                        deferred.resolve(data);
                    });

                return deferred.promise;
            }
        };

        // Factory instance
        return service;
    })
;
