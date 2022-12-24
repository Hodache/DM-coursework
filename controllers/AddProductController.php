<?php
require_once "BaseGameShopTwigController.php";

class AddProductController extends BaseGameShopTwigController {
    public $template = "add_product.twig";

    public function get(array $context) {
        $query = $this->pdo->query("SELECT G_NAME as genre from Genres");
        $context['genres'] = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $this->pdo->query("SELECT D_NAME as developer from Developers");
        $context['developers'] = $query->fetchAll(PDO::FETCH_ASSOC);

        $context['title'] = "Добавление продукта";

        parent::get($context);
    }

    public function post(array $context) {

        $name = isset($_POST['name']) ? $_POST['name'] : "";
        $genre = isset($_POST['genre']) ? $_POST['genre'] : "";
        $price = isset($_POST['price']) ? $_POST['price'] : "";
        $developer = isset($_POST['developer']) ? $_POST['developer'] : "";
        $description = isset($_POST['description']) ? $_POST['description'] : "";

        $context['name'] = $name;
        $context['active_genre'] = $genre;
        $context['active_developer'] = $developer;
        $context['price'] = $price;
        $context['description'] = $description;

        // Валидация
        if (empty($name) | empty($genre) | (empty($price) & $price != 0) | empty($developer)) {
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

            $query_statement = "INSERT INTO Products(P_NAME, P_PRICE, P_DESCRIPTION, D_ID) VALUES(:name, :price, :description, :developer)";
            $query = $this->pdo->prepare($query_statement);
            $query->bindValue("name", $name);
            $query->bindValue("price", $price);
            $query->bindValue("developer", $developer_id);
            $query->bindValue("description", $description);
            $query->execute();
            $product_id = $this->pdo->lastInsertId();

            $genre_query = $this->pdo->prepare("SELECT G_ID FROM Genres WHERE G_NAME = :genre");
            $genre_query->bindValue("genre", $genre);
            $genre_query->execute();
            $genre_id = $genre_query->fetch()[0];

            $query_statement = "INSERT INTO ProductsGenres(P_ID, G_ID) VALUES (:pid, :gid)";
            $query = $this->pdo->prepare($query_statement);
            $query->bindValue("pid", $product_id);
            $query->bindValue("gid", $genre_id);
            $query->execute();

            $context['product_id'] = $product_id;
            $this->pdo->commit();
        } catch (Exception $e) {
            $context['message'] = "Во время добавления произошла ошибка";
            $this->pdo->rollback();
        }

        $this->get($context);
    }
}