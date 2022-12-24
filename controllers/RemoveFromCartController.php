<?php

class RemoveFromCartController extends BaseController {

    public function post(array $context) {
        if (($key = array_search($_POST['product_id'], $_SESSION['cart'])) !== false) {
            unset($_SESSION['cart'][$key]);
        }

        header("Location: /cart");
    }
}