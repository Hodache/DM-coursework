<?php

class DeleteProductController extends BaseController {
    public function post(array $context)
    {
        $to_delete_id = $this->params['id'];

        $query = $this->pdo->prepare("DELETE FROM Products WHERE P_ID = :id");
        $query->bindValue("id", $to_delete_id);
        $query->execute();

        $url = $_SERVER['HTTP_REFERER'];

        header("Location: $url");
        exit;
    }
}