<?php
require_once "../controllers/BaseGameShopTwigController.php";

class AccountController extends BaseGameShopTwigController
{
    public $template = "account.twig";

    public function getContext(): array
    {
        $context = parent::getContext();

        $uid = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

        $query_statement = "SELECT U_ID as id, U_NICKNAME as nickname, U_BALANCE as balance, U_PLAYTIME as playtime
                             FROM Users WHERE U_ID = :uid";

        $query = $this->pdo->prepare($query_statement);
        $query->bindValue("uid", $uid);
        $query->execute();
        $user_data = $query->fetch();

        $context['id'] = $user_data['id'];
        $context['nickname'] = $user_data['nickname'];
        $context['balance'] = $user_data['balance'];
        $context['playtime'] = $user_data['playtime'];

        $context['title'] = "Личный кабинет";

        return $context;
    }
}
