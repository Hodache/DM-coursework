<?php

class CartController extends BaseGameShopTwigController {
    public $template = "cart.twig";

    public function getContext() : array {
        $context = parent::getContext();

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        return $context;
    }

    public function get(array $context) {
        $cart_ids = '(NULL)';
        if (isset($_SESSION['cart']) & sizeof($_SESSION['cart']) > 0) {
            array_filter($_SESSION['cart'], static function($var) {return $var !== null;} );
            $cart_ids = '(' . implode(",", $_SESSION['cart']) . ')';
        }

        $query_statement = "SELECT P_ID as id, P_NAME as name, P_PRICE as price FROM Products WHERE P_ID IN $cart_ids";
        $query = $this->pdo->query($query_statement);
        $cart_products = $query->fetchAll();
        
        $context['products'] = $cart_products;

        parent::get($context);
    }

    public function post(array $context) {
        $balance_query = "SELECT U_BALANCE as balance FROM Users WHERE U_ID = :user_id";
        $query = $this->pdo->prepare($balance_query);
        $query->bindValue("user_id", $_SESSION['user_id']);
        $query->execute();
        $balance = $query->fetch();

        $cart_ids = '(NULL)';
        if (isset($_SESSION['cart']) & sizeof($_SESSION['cart']) > 0) {
            array_filter($_SESSION['cart'], static function ($var) {
                return $var !== null;
            });
            $cart_ids = '(' . implode(",", $_SESSION['cart']) . ')';
        }

        $query_statement = "SELECT P_PRICE as price FROM Products WHERE P_ID IN $cart_ids";
        $query = $this->pdo->query($query_statement);
        $cart_prices = $query->fetchAll();
        $prices_sum = 0;
        foreach ($cart_prices as $price) {
            $prices_sum += $price[0];
        }

        if ($prices_sum > $balance[0]) {
            $context['message'] = "Недостаточно средств";
            $this->get($context);
            exit;
        }

        try {
            $this->pdo->beginTransaction();
            $query = $this->pdo->prepare('INSERT INTO Orders(U_ID, O_RECEPIENT_ID) VALUES (:uid, :oid)');
            $query->bindValue("uid", $_SESSION["user_id"]);
            $query->bindValue("oid", $_SESSION["user_id"]);
            $query->execute();
            $order_id = $this->pdo->lastInsertId();

            foreach ($_SESSION['cart'] as $product_id) {
                $query = $this->pdo->prepare('INSERT INTO OrderProducts(O_ID, P_ID, OP_DISCOUNT) VALUES(:order_id, :product_id, 0)');
                $query->bindValue("order_id", $order_id);
                $query->bindValue("product_id", $product_id);
                $query->execute();
            }

            $new_balance = $balance[0] - $prices_sum;
            $query = $this->pdo->prepare('UPDATE Users SET U_BALANCE = :new_balance WHERE U_ID = :uid');
            $query->bindValue("new_balance", $new_balance);
            $query->bindValue("uid", $_SESSION["user_id"]);
            $query->execute();

            $this->pdo->commit();

            $context['message'] = "Товары приобретены";
            $_SESSION['cart'] = [];
        } catch (Exception $e) {
            echo "<pre>";
            print_r($e);
            echo "</pre>";
            $context['message'] = "Во время покупки произошла ошибка";
            $this->pdo->rollback();
        }

        $this->get($context);
    }
}