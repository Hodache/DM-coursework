<?php

class AddToCartController extends BaseController
{

    public function post(array $context)
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (!in_array($_POST["product_id"], $_SESSION['cart'])) {
            array_push($_SESSION['cart'], $_POST["product_id"]);
        }

        $last_page = $_SERVER['HTTP_REFERER'];

        header("Location: $last_page");
    }
}
