<?php

namespace CoreHelpers;

class RouteException extends \Exception {
    public $redirect;
    public $msg;
    public $flashType;

    public function __construct($params, $code=0) {
        $this->redirect = $params[0];
        $this->msg = $params[1];
        $this->flashType = !empty($params[2])?$params[2]:'danger';
        parent::__construct($this->msg, $code);
    }
    public function __toString() {
        return $this->message;
    }
}