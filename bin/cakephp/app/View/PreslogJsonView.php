<?php

App::uses('JsonView', 'View');

class PreslogJsonView extends JsonView {

	public function render($view = null, $layout = null) {

        // Custom headers
        header('Cache-Control: no-cache');

        // Standard render
        return parent::render($view, $layout);
	}
}
