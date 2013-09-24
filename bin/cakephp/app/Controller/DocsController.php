<?php

use Swagger\Swagger as Swagger;

/**
 * Class DocsController
 * Provides API documentation via Swagger-PHP JSON interface
 */
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
     * @param   string      Resource label
     * @return   string
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
        $data = $swagger->getResourceList(false, false);
        $data['apiVersion'] = '1.0.0';
        $data['swaggerVersion'] = '1.2';
        $data['basePath'] = Router::url('/', true);

        // Affix "docs" link
        foreach ($data['apis'] as &$api)
        {
            $api['path'] = '/docs'.$api['path'];
        }

        // output
        $this->set($data);
        $this->set('_serialize', array_keys($data));
        $this->render();
    }

}
