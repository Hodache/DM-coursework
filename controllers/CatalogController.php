<?php
require_once "../controllers/BaseGameShopTwigController.php";

class CatalogController extends BaseGameShopTwigController {
    public $template = "catalog.twig";
    public $title = "Каталог";

    public function getContext(): array {
        $context = parent::getContext();

        // Переменные пагинации
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $to_show = 15;
        $offset = (int)(($page - 1) * $to_show);

        // Переменные фильтрации
        $name = isset($_GET['name']) ? $_GET['name'] : "";
        $genre = isset($_GET['genre']) ? $_GET['genre'] : "";
        $lowest_price = isset($_GET['lowest_price']) ? $_GET['lowest_price'] : 0;

        if(isset($_GET['highest_price'])) {
            $highest_price = $_GET['highest_price'];
        } else {
            $query = $this->pdo->query("SELECT MAX(P_PRICE) FROM Products");
            $highest_price = $query->fetch()[0];
        }

        // Запрос с учетом пагинации
        $query_statement = <<<EOL
SELECT DISTINCT p.P_ID as id, p.P_NAME as name, p.P_PRICE as price
FROM Products as p
    LEFT JOIN ProductsGenres as pg
        ON p.P_ID = pg.P_ID
    LEFT JOIN Genres as g
        ON g.G_ID = pg.G_ID
WHERE p.P_NAME like :name
    AND (g.G_NAME = :genre1 OR :genre2 = '')
    AND p.P_PRICE >= :lowest_price
    AND p.P_PRICE <= :highest_price
ORDER BY p.P_ID
OFFSET :offset ROWS FETCH NEXT :to_show ROWS ONLY
EOL;

        $query = $this->pdo->prepare($query_statement);
        $query->bindValue("name", "%".$name."%");
        $query->bindValue("genre1", $genre);
        $query->bindValue("genre2", $genre); 
        $query->bindValue("lowest_price", $lowest_price);
        $query->bindValue("highest_price", $highest_price);
        $query->bindValue("offset", $offset, PDO::PARAM_INT);
        $query->bindValue("to_show", $to_show, PDO::PARAM_INT);
        $query->execute();

        $context['name'] = $name;
        $context['active_genre'] = $genre;
        $context['lowest_price'] = $lowest_price;
        $context['highest_price'] = $highest_price;
        $context['products'] = $query->fetchAll();

        // Запрос на количество отфильтрованных данных для пагинации
        $query_statement = <<<EOL
SELECT COUNT(DISTINCT P_NAME) as count
FROM Products as p
    LEFT JOIN ProductsGenres as pg
        ON p.P_ID = pg.P_ID
    LEFT JOIN Genres as g
        ON g.G_ID = pg.G_ID
WHERE p.P_NAME like :name
    AND (g.G_NAME = :genre1 OR :genre2 = '')
    AND p.P_PRICE >= :lowest_price
    AND p.P_PRICE <= :highest_price
EOL;
        
        $query = $this->pdo->prepare($query_statement);
        $query->bindValue("name", "%".$name."%");
        $query->bindValue("genre1", $genre);
        $query->bindValue("genre2", $genre);
        $query->bindValue("lowest_price", $lowest_price);
        $query->bindValue("highest_price", $highest_price);
        $query->execute();

        $products_count = $query->fetchColumn();
        $pages_count = intdiv($products_count, $to_show);

        if ($products_count / $to_show > $pages_count) {
            $pages_count += 1;
        }

        $context['pages_count'] = $pages_count > 0 ? $pages_count : 1;
        $context['offset'] = $offset;
        $context['to_show'] = $to_show;
        $context['current_page'] = $page;
        $context['query_string'] = $_SERVER['QUERY_STRING'];

        $query = $this->pdo->query("SELECT G_NAME as genre from Genres");

        $context['genres'] = $query->fetchAll(PDO::FETCH_ASSOC);

        return $context;
    }
}

?>