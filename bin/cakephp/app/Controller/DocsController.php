<?php

/**
 * Class DocsController
 * Provides API documentation via Swagger-PHP JSON interface
 */

use Swagger\Swagger as Swagger;


class DocsController extends AppController
{
    public $uses = array();


    /**
     * Swagger-UI View
     */
    public function index()
    {
        $this->viewClass = 'View';
    }


    /**
     * Generates Swagger-Php JSON documentation for the REST API.
     * - Produces a specific resource if "resource" is specified.
     * - Produces a ResourceList if no "resource" is specified
     *
     * @return string
     */
    function generateDocumentation( $resource=null )
    {
        // Prevent regular output method
        $this->autoRender = false;

        // Load Swagger
        $pathList = Configure::read('swagger.paths');
        $fileList = array();

        // Get all files in directories
        foreach ($pathList as $path) {
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {

                    // Get the full path of this file
                    $filePath = $path.DS.$entry;

                    // If not a directory - use it!
                    if ($entry != "." && $entry != ".." && !is_dir($filePath)) {
                        $fileList[] = $filePath;
                    }
                }
            }
        }

        // Start swagger with the file list
        $swagger = new Swagger();
        $swagger->setFileList( $fileList );

        // Force swagger to reindex everything
        $swagger->flushCache();

        // Fetching a specific resource?
        if ( !empty($resource) ) {
            return $swagger->getResource('/'.$resource, true, true);
        }

        // Fetch the resource listing
        return $swagger->getResourceList(true, true);
    }

}
