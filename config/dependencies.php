<?php
declare(strict_types=1);

use DI\Container;
use Psr\Container\ContainerInterface;
use SallePW\SlimApp\Controller\ChangePasswordController;
use SallePW\SlimApp\Controller\CreateUserController;
use SallePW\SlimApp\Controller\FriendRequestsController;
use SallePW\SlimApp\Controller\FriendsController;
use SallePW\SlimApp\Controller\FlashController;
use SallePW\SlimApp\Controller\HomeController;
use SallePW\SlimApp\Controller\LoginController;
use SallePW\SlimApp\Controller\ProfileController;
use SallePW\SlimApp\Controller\SendFriendRequestController;
use SallePW\SlimApp\Controller\WalletController;
use SallePW\SlimApp\Controller\WishlistController;
use SallePW\SlimApp\Controller\StoreController;
use SallePW\SlimApp\Controller\ValidationController;
use SallePW\SlimApp\Controller\MyGamesController;
use SallePW\SlimApp\Model\Repository\MysqlUserRepository;
use SallePW\SlimApp\Model\Repository\PDOSingleton;
use SallePW\SlimApp\Model\UserRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;

$container = new Container();

$container->set(
    'view',
    function () {
        return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
    }
);

$container->set(
    HomeController::class,
    function (ContainerInterface $c) {
        $controller = new HomeController($c->get("view"));
        return $controller;
    }
);

$container->set('db', function () {
    return PDOSingleton::getInstance(
        $_ENV['MYSQL_ROOT_USER'],
        $_ENV['MYSQL_ROOT_PASSWORD'],
        $_ENV['MYSQL_HOST'],
        $_ENV['MYSQL_PORT'],
        $_ENV['MYSQL_DATABASE']
    );
});

$container->set(UserRepository::class, function (ContainerInterface $container) {
    return new MySQLUserRepository($container->get('db'));
});

$container->set(
    CreateUserController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new CreateUserController($c->get("view"), $c->get(UserRepository::class)); // Not SURE
        return $controller;
    }
);

$container->set(
        LoginController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new LoginController($c->get("view"), $c->get(UserRepository::class)); // Not SURE
        return $controller;
    }
);

$container->set(
    ValidationController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new ValidationController($c->get("view"), $c->get(UserRepository::class)); // Not SURE
        return $controller;
    }
);

$container->set(
    StoreController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new StoreController($c->get("view"), $c->get(UserRepository::class), $c->get("flash"));
        return $controller;
    }
);

$container->set(
    WalletController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new WalletController($c->get("view"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    ProfileController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new ProfileController($c->get("view"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    ChangePasswordController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new ChangePasswordController($c->get("view"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    MyGamesController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new MyGamesController($c->get("view"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    'flash',
    function () {
        return new Messages();
    }
);

$container->set(
    FlashController::class,
    function (Container $c) {
        $controller = new FlashController($c->get("view"), $c->get("flash"));
        return $controller;
    }
);


$container->set(
    FriendsController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new FriendsController($c->get("view"), $c->get(UserRepository::class));
        return $controller;
    }
);

$container->set(
    FriendRequestsController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new FriendRequestsController($c->get("view"), $c->get(UserRepository::class));
        return $controller;
    }
);


$container->set(
    SendFriendRequestController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new SendFriendRequestController($c->get("view"), $c->get(UserRepository::class));
        return $controller;
    }
);


$container->set(
    WishlistController::class,
    function (Container $c) {
        //LAST LINE OF EXECUTION
        $controller = new WishlistController($c->get("view"), $c->get(UserRepository::class));
        return $controller;
    }
);