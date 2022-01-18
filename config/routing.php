<?php
declare(strict_types=1);

use SallePW\SlimApp\Controller\ChangePasswordController;
use SallePW\SlimApp\Controller\CreateUserController;
use SallePW\SlimApp\Controller\FriendRequestsController;
use SallePW\SlimApp\Controller\FriendsController;
use SallePW\SlimApp\Controller\HomeController;
use SallePW\SlimApp\Controller\LoginController;
use SallePW\SlimApp\Controller\ProfileController;
use SallePW\SlimApp\Controller\SendFriendRequestController;
use SallePW\SlimApp\Controller\WalletController;
use SallePW\SlimApp\Controller\StoreController;
use SallePW\SlimApp\Controller\WishlistController;
use SallePW\SlimApp\Controller\ValidationController;
use SallePW\SlimApp\Controller\MyGamesController;
use SallePW\SlimApp\Middleware\BeforeMiddleware;

$app->get('/', HomeController::class . ":showHome")->setName('home');
$app->get('/home', HomeController::class . ":showHome")->setName('home');
$app->get('/register', CreateUserController::class . ":showRegisterForm")->setName('register');
$app->post('/register',CreateUserController::class . ":apply")->setName('create-user');
$app->get('/login', LoginController::class . ":showLoginForm")->setName('login');
$app->post('/login', LoginController::class . ":apply")->setName('login-user');
$app->post('/logout', LoginController::class . ":logout")->setName('logout');
$app->get('/activate', ValidationController::class . ":tryToValidate")->setName('validation');
$app->get('/store', StoreController::class . ":showStorePage")->setName('store');


$app->post('/store/buy/{gameID}',StoreController::class . ":buyGame")->setName('buy');

$app->get('/user/wishlist',WishlistController::class . ":showWishlist")->setName('myWishlist');
$app->get('/user/wishlist/{gameID}',WishlistController::class . ":showGame")->setName('showGame');
$app->post('/user/wishlist/{gameID}',WishlistController::class . ":wishlistGame")->setName('addToWishlist');
$app->delete('/user/wishlist/{gameID}',WishlistController::class . ":deleteFromWishlist")->setName('removeFromWishlist');

$app->get('/user/wallet', WalletController::class . ":showWalletForm")->setName('wallet');
$app->post('/user/wallet', WalletController::class . ":addMoney")->setName('addMoney');
$app->get('/profile', ProfileController::class . ":showProfilePage")->setName('profile');
$app->post('/profile', ProfileController::class . ":apply")->setName('profile-user');
$app->get('/profile/changePassword', ChangePasswordController::class . ":showChangePasswordPage")->setName('profile');
$app->post('/profile/changePassword', ChangePasswordController::class . ":apply")->setName('profile-user');

$app->get('/user/myGames', MyGamesController::class . ":showMyGames")->setName('myGames');

$app->get('/user/friends', FriendsController::class . ":showFriendsPage")->setName('friends');
$app->get('/user/friendRequests', FriendRequestsController::class . ":showFriendRequestsPage")->setName('friends-requests');
$app->post('/user/friendRequests/accept/{requestId}',FriendRequestsController::class . ":acceptRequest")->setName('accept');
$app->get('/user/friendRequests/send', SendFriendRequestController::class . ":showSendFriendRequestPage")->setName('friend-requests');
$app->post('/user/friendRequests/send', SendFriendRequestController::class . ":sendFriendRequest")->setName('friends-requests');