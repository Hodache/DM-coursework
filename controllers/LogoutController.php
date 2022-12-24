<?php

class LogoutController extends BaseController {
    public function get(array $context) {
        header("Location: /");
    }

    public function post(array $context) {
        session_destroy();
        header("Location: login");
    }
}