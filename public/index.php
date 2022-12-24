<?php
// подключаем пакеты которые установили через composer
require_once '../vendor/autoload.php';
require_once '../framework/autoload.php';
require_once '../controllers/Controller404.php';
require_once '../controllers/CatalogController.php';
require_once '../controllers/ProductController.php';
require_once '../controllers/AddProductController.php';
require_once '../controllers/UpdateProductController.php';
require_once '../controllers/DeleteProductController.php';
require_once '../controllers/AccountController.php';
require_once '../controllers/CartController.php';
require_once '../controllers/AddToCartController.php';
require_once '../controllers/RemoveFromCartController.php';
require_once '../controllers/LoginController.php';
require_once '../controllers/LogoutController.php';

require_once '../middlewares/LoginRequiredMiddleware.php';
require_once '../middlewares/AdminRequiredMiddleware.php';

$loader = new \Twig\Loader\FilesystemLoader('../views');
$twig = new \Twig\Environment($loader, [
        "debug" => true
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

$pdo = new PDO("sqlsrv:Server=localhost;Database=GameShop", "yandex", "greencraft62");

session_set_cookie_params(60 * 60 * 10);
session_start();

$router = new Router($twig, $pdo);

$router->add("/", CatalogController::class);

$router->add("/product/(?P<id>\d+)", ProductController::class);

$router->add("/account", AccountController::class)
        ->middleware(new LoginRequiredMiddleware);

$router->add("/cart", CartController::class)
        ->middleware(new LoginRequiredMiddleware);

$router->add("/cart/add", AddToCartController::class)
        ->middleware(new LoginRequiredMiddleware);

$router->add("/cart/remove", RemoveFromCartController::class)
        ->middleware(new LoginRequiredMiddleware);

$router->add("/add", AddProductController::class)
        ->middleware(new LoginRequiredMiddleware)
        ->middleware(new AdminRequiredMiddleware);

$router->add("/product/(?P<id>\d+)/delete", DeleteProductController::class)
        ->middleware(new LoginRequiredMiddleware)
        ->middleware(new AdminRequiredMiddleware);

$router->add("/product/(?P<id>\d+)/update", UpdateProductController::class)
        ->middleware(new LoginRequiredMiddleware)
        ->middleware(new AdminRequiredMiddleware);

$router->add("/login", LoginController::class);
        
$router->add("/logout", LogoutController::class);

$router->get_or_default(Controller404::class);
