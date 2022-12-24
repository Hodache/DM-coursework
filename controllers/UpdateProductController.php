<?php
require_once "BaseGameShopTwigController.php";

class UpdateProductController extends BaseGameShopTwigController {
    public $template = "update_product.twig";

    public function get(array $context) {
        $query = $this->pdo->query("SELECT D_NAME as developer from Developers");
        $context['developers'] = $query->fetchAll(PDO::FETCH_ASSOC);

        $query_statement = <<<EOL
SELECT P_NAME as name, P_PRICE as price, P_DESCRIPTION as description, D_NAME as developer 
FROM Products as p 
    JOIN Developers as d 
        ON p.D_ID = d.D_ID 
WHERE P_ID = :id
EOL;
        $query = $this->pdo->prepare($query_statement);
        $query->bindValue("id", $this->params['id']);
        $query->execute();
        $product = $query->fetch(PDO::FETCH_ASSOC);

        $context['name'] = isset($context['name']) ? $context['name'] : $product['name'];
        $context['active_developer'] = isset($context['active_developer']) ? $context['active_developer'] : $product['developer'];
        $context['price'] = isset($context['price']) ? $context['price'] : $product['price'];
        $context['description'] = isset($context['description']) ? $context['description'] : $product['description'];

        $context['title'] = "Обновление продукта";

        parent::get($context);
    }

    public function post(array $context) {
        $name = isset($_POST['name']) ? $_POST['name'] : "";
        $price = isset($_POST['price']) ? $_POST['price'] : "";
        $developer = isset($_POST['developer']) ? $_POST['developer'] : "";
        $description = isset($_POST['description']) ? $_POST['description'] : "";

        $context['name'] = $name;
        $context['active_developer'] = $developer;
        $context['price'] = $price;
        $context['description'] = $description;

        //Validation
        if (empty($name) | empty($price) | empty($developer)) {
            $context['message'] = "Все поля должны быть заполнены";
            $this->get($context);
            exit;
        }

        if ($price < 0) {
            $context['message'] = "Цена не может быть отрицательной";
            $this->get($context);
            exit;
        }

        try {
            $this->pdo->beginTransaction();

            $developer_query = $this->pdo->prepare("SELECT D_ID FROM Developers WHERE D_NAME = :developer");
            $developer_query->bindValue("developer", $developer);
            $developer_query->execute();
            $developer_id = $developer_query->fetch()[0];

            $sql = <<<EOL
UPDATE Products
SET P_NAME = :name, P_PRICE = :price, P_DESCRIPTION = :description, D_ID = :developer
WHERE P_ID = :product_id
EOL;

            $query = $this->pdo->prepare($sql);
            $query->bindValue("name", $name);
            $query->bindValue("price", $price);
            $query->bindValue("description", $description);
            $query->bindValue("developer", $developer_id);
            $query->bindValue("product_id", $this->params['id']);
            $query->execute();

            $context['product_id'] = $this->params['id'];
            $this->pdo->commit();
        } catch (Exception $e) {
            $context['message'] = "Во время обновления произошла ошибка";
            $this->pdo->rollback();
        }

        $this->get($context);
    }
}