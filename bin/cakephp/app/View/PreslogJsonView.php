<?php

App::uses('JsonView', 'View');

class PreslogJsonView extends JsonView {

	public function render($view = null, $layout = null) {

        // Custom headers
        if (!headers_sent())
        {
            header('Cache-Control: no-cache');
        }

        // Standard render
        return parent::render($view, $layout);
	}
}
