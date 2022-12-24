<?php
require_once "BaseGameShopTwigController.php";

class LoginController extends BaseGameShopTwigController {
    public $template = "login.twig";

    public function post(array $context) {
        $user = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        $query = $this->pdo->prepare("SELECT U_ID as id, U_ROLE as role, U_NICKNAME as username, U_PASSWORD as password FROM Users WHERE U_NICKNAME = :user");
        $query->bindValue("user", $user);
        $query->execute();

        $user_auth_data = $query->fetch();

        $valid_user = $user_auth_data['username'] ?? '';
        $valid_password = $user_auth_data['password'] ?? '';
        
        if (!$user_auth_data || ($valid_user != $user || $valid_password != $password)) {
            $context['message'] = 'Неверный логин или пароль';
        } else {
            $_SESSION['is_logged'] = true;
            $_SESSION['user_id'] = $user_auth_data['id'];
            $_SESSION['role'] = $user_auth_data['role'];
            header('Location: /');
        }

        $this->get($context);
    }
}