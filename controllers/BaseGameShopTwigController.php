<?php

class BaseGameShopTwigController extends TwigBaseController {
    public function getContext() : array {
        $context = parent::getContext();

        $context['is_logged'] = isset($_SESSION['is_logged']) ? $_SESSION['is_logged'] : false;
        $context['role'] = isset($_SESSION['role']) ? $_SESSION['role'] : false;

        return $context;
    }
}

?>