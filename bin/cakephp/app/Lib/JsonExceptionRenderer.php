<?php

App::uses('ExceptionRenderer', 'Error');

/**
 * Class AppError
 * Custom application error handler
 */

class JsonExceptionRenderer extends ExceptionRenderer
{

    // override
    public function error400($error) {
        $this->_prepareView($error, 'Not Found');
        $this->controller->response->statusCode($error->getCode());
        $this->_outputMessage('error400');
    }

    // override
    public function error500($error) {
        $this->_prepareView($error, 'An Internal Error Has Ocurred.');
        $code = ($error->getCode() > 500 && $error->getCode() < 506) ? $error->getCode() : 500;
        $this->controller->response->statusCode($code);

        $this->_outputMessage('error500');
    }

    private function _prepareView($error, $genericMessage) {
        $message = $error->getMessage();
        if(!Configure::read('debug') && !Configure::read('detailed_exceptions')) {
            $message = __d('cake', $genericMessage);
        }
        $url = $this->controller->request->here();
        $renderVars = array(
            'message' => h($message),
            'code' => h($error->getcode()),
        );
        $renderVars['_serialize'] = array_keys($renderVars);
        $this->controller->set($renderVars);
    }


    /**
     * Generic handler for the internal framework errors CakePHP can generate.
     * @param CakeException $error
     * @return void
     */
    protected function _cakeError(CakeException $error) {
        $url = $this->controller->request->here();
        $code = ($error->getCode() >= 400 && $error->getCode() < 506) ? $error->getCode() : 500;
        $this->controller->response->statusCode($code);
        $renderVars = array(
            'code' => $code,
            'message' => h($error->getMessage()),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'error' => array(
                'trace' => $error->getTrace(),
            ),
        );
        $renderVars['_serialize'] = array_keys($renderVars);

        $this->controller->set($renderVars);
        $this->controller->set($error->getAttributes());
        $this->_outputMessage($this->template);
    }


    /**
     * Display message
     * @param string $template
     */
    protected function _outputMessage($template) {
        try {
            $this->controller->render();
            $this->controller->afterFilter();
            $this->controller->response->send();
            $this->controller->shutdownProcess();
            exit();
        } catch (Exception $e) {
            $this->_outputMessageSafe('error500');
        }
    }

}