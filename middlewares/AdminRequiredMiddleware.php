<?php

class AdminRequiredMiddleware extends BaseMiddleware
{
    public function apply(BaseController $controller, array $context)
    {
        $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'client';
        if (!strcmp($role, 'admin') == 0) {
            header("Location: /");
            exit;
        }
    }
}
