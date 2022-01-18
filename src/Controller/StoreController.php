<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Exception;

use GuzzleHttp\Client;
use SallePW\SlimApp\Model\Game;
use SallePW\SlimApp\Model\UserRepository;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class StoreController
{

    // test comment
    private Twig $twig;
    private UserRepository $userRepository;
    private Messages $flash;

    public function __construct(Twig $twig, UserRepository $userRepository, Messages $flash)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
        $this->flash = $flash;
    }

    /* TO-DO: CHECK IF USER ALREADY HAS THE GAME (PURCHASE AS A GIFT?)
     *        SHOW USER CURRENT WALLET BALANCE ON NAV
     *
     * */
    public function buyGame(Request $request, Response $response) : Response
    {

        // check if user is logged in
        if (isset($_SESSION['id'])) {

            // get the POST gameID
            $data = $request->getParsedBody();
            $gameID = $data['purchase'];

            // Ask game price to the API
            try {

                $client = new Client([
                    // Base URI is used with relative requests
                    'base_uri' => 'https://www.cheapshark.com/api/1.0/games?id=',
                    'timeout' => 3.0
                ]);

                $apiUrl = "games?id=$gameID";

                $search_response = $client->request('GET', $apiUrl);
                $decodedData = json_decode($search_response->getBody()->getContents(), true);

                // initial min value as retailPrice (same for every other store)
                $min_game_price = floatval($decodedData['deals'][0]['retailPrice']);


            } catch (Exception $exception) {
                // You could render a .twig template here to show the error
                $response->getBody()
                    ->write('Unexpected error: ' . $exception->getMessage());
                return $response->withStatus(500);
            }

            // Ask MySQL if enough funds
            $user_money = floatval($this->userRepository->searchId($_SESSION['id'])->wallet);

            if($user_money >= $min_game_price){
                // purchase and update tables
                $this->userRepository->removeMoney($min_game_price, $_SESSION['id']);
                $this->userRepository->addUserGame($gameID, $_SESSION['id']);
                if ($this->userRepository->isWishlisted($_SESSION['id'], $gameID)) {
                    $this->userRepository->deleteFromWishlist($gameID, $_SESSION['id']);

                }
                $row = $this->userRepository->searchId($_SESSION['id']);
                $_SESSION['wallet'] =  $row->wallet;


                return $this->twig->render($response, 'validation.twig', [
                    'message' => "Purchase Successful!", 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                    'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
            } else {
                // not enough funds
                return $response->withHeader('Location', '/store?funds=false')->withStatus(200);
            }
        }
        else {
            return $response->withHeader('Location', '/login?buy=false')->withStatus(200);
        }
    }

    public function showStorePage(Request $request, Response $response): Response
    {

        if (isset($_GET['funds'])) {
            echo "<script type='text/javascript'>alert('You don\'t have enough funds in your virtual Wallet!');</script>";
        }

        try {

            $client = new Client([
                // Base URI is used with relative requests
                'base_uri' => 'https://www.cheapshark.com/api/1.0/',
                'timeout' => 5.0
            ]);

            $apiUrl = "deals";

            $search_response = $client->request('GET', $apiUrl);
            $decodedData = json_decode($search_response->getBody()->getContents(), true);

            $games = [];
            foreach ($decodedData as $game) {
                $game_ID = $game["gameID"];
                $game_title = $game["title"];
                $game_normal_price = floatval($game["normalPrice"]);
                $game_thumbnail = $game["thumb"];
                $wishlisted = false;
                if (isset($_SESSION['id'])) {
                    if ($this->userRepository->isWishlisted($_SESSION['id'], $game_ID) != null) {
                        $wishlisted = true;
                    }
                }
                array_push($games, new Game($game_ID, $game_title, $game_normal_price, $game_thumbnail, $wishlisted));
            }

            /* MÁS ELEGANTE, lo estamos mostrando al usuario el método del form
            return $this->twig->render(
                $response,
                'store.twig',
                [
                    'formId' => $_SESSION['id'],
                    'formInfo' => $info,
                    'formMoney' => $_SESSION['money'],
                    'formPic' => $_SESSION['pic'],
                    'notifications' => $notifications,
                    'formMethod' => "POST"
                ]
            );

            buyAction' => $routeParser->urlFor('handle-store-buy',['gameId' => 1]),

            */

            return $this->twig->render($response, 'store.twig', ['games' => $games, 'logged' => $_SESSION['id'],
                'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);

        } catch (Exception $exception) {
            // You could render a .twig template here to show the error
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }
    }
}