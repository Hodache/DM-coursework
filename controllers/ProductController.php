<?php
require_once "../controllers/BaseGameShopTwigController.php";

class ProductController extends BaseGameShopTwigController
{
    public $template = "__product.twig";

    public function getContext(): array
    {
        $context = parent::getContext();

        $query_statement = "SELECT * FROM Products WHERE P_ID = :product_id";

        $query = $this->pdo->prepare($query_statement);
        $query->bindValue("product_id", $this->params['id']);
        $query->execute();
        $data = $query->fetch();

        $context['title'] = $data['P_NAME'];
        $context['description'] = $data['P_DESCRIPTION'];
        $context['id'] = $this->params['id'];

        $genres_statement = <<<EOL
SELECT G_NAME as genre FROM Genres as g
    JOIN ProductsGenres as pg
        ON pg.G_ID = g.G_ID
    JOIN Products as p
        ON p.P_ID = pg.P_ID
WHERE p.P_ID = :product_id
EOL;

        $genres_query = $this->pdo->prepare($genres_statement);
        $genres_query->bindValue("product_id", $this->params['id']);
        $genres_query->execute();
        $genres = $genres_query->fetchAll();

        $context['genres'] = $genres;

        $developer_statement = <<<EOL
SELECT D_NAME as developer FROM Developers as d
    JOIN Products as p
        ON p.D_ID = d.D_ID
WHERE P_ID = :product_id
EOL;

        $developer_query = $this->pdo->prepare($developer_statement);
        $developer_query->bindValue("product_id", $this->params['id']);
        $developer_query->execute();
        $developer = $developer_query->fetch();

        $context['developer'] = $developer;

        return $context;
    }
}
