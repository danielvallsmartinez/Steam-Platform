<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Exception;

use GuzzleHttp\Client;
use SallePW\SlimApp\Model\Game;
use SallePW\SlimApp\Model\UserRepository;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


final class WishlistController
{

    private Twig $twig;
    private UserRepository $userRepository;

    public function __construct(Twig $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }



    public function deleteFromWishlist(Request $request, Response $response){
        if (isset($_SESSION['id'])) {
            $data = $request->getParsedBody();
            $gameID = $data['addToWishlist'];

            $this->userRepository->deleteFromWishlist($gameID, $_SESSION['id']);
            return $response->withHeader('Location', '/wishlist')->withStatus(200);
        }
    }

    public function wishlistGame(Request $request, Response $response) : Response
    {

        // check if user is logged in
        if (isset($_SESSION['id'])) {

            // get the POST gameID
            $data = $request->getParsedBody();
            $gameID = $data['addToWishlist'];


            if(!$this->userRepository->isWishlisted($_SESSION['id'], $gameID)){

                $this->userRepository->addToWishlist($gameID, $_SESSION['id']);

                return $this->twig->render($response, 'validation.twig', [
                    'message' => "Game Wishlisted!", 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                    'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
            }
        }
        else {
            return $response->withHeader('Location', '/login?buy=false')->withStatus(200);
        }
    }

    public function showWishlist(Request $request, Response $response): Response
    {

        if (isset($_SESSION['id'])) {
            $gamesMysql = $this->userRepository->searchWishlistedGames($_SESSION['id']);
            $games = [];
            foreach ($gamesMysql as $game) {
                try {

                    $client = new Client([
                        // Base URI is used with relative requests
                        'base_uri' => 'https://www.cheapshark.com/api/1.0/',
                        'timeout' => 2.0
                    ]);
                    $gameId = $game['gameApiId'];
                    $apiUrl = "games?id=$gameId";

                    $search_response = $client->request('GET', $apiUrl);
                    $decodedData = json_decode($search_response->getBody()->getContents(), true);

                    if (isset($decodedData['info']['title'])) {
                        $game_ID = $game['gameApiId'];
                        $game_title = $decodedData['info']['title'];
                        $game_normal_price = floatval($decodedData['deals'][0]['retailPrice']);
                        $game_thumbnail = $decodedData['info']['thumb'];
                        array_push($games, new Game($game_ID, $game_title, $game_normal_price, $game_thumbnail, true));
                    }

                } catch (Exception $exception) {
                    // You could render a .twig template here to show the error
                    $response->getBody()
                        ->write('Unexpected error: ' . $exception->getMessage());
                    return $response->withStatus(500);
                }
            }
            return $this->twig->render($response, 'wishlist.twig',
                ['games' => $games, 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                    'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
        }
        else {
            return $response->withHeader('Location', '/login?logged=false')->withStatus(200);
        }
    }


    public function showGame(Request $request, Response $response): Response
    {

        if (isset($_SESSION['id'])) {

            // Get Game ID from URI
            $data = $request->getParsedBody();
            $gameId = preg_split("/[\\/]/", $_SERVER['REQUEST_URI'])[3];

                try {

                    $client = new Client([
                        // Base URI is used with relative requests
                        'base_uri' => 'https://www.cheapshark.com/api/1.0/',
                        'timeout' => 2.0
                    ]);

                    $apiUrl = "games?id=$gameId";

                    $search_response = $client->request('GET', $apiUrl);
                    $decodedData = json_decode($search_response->getBody()->getContents(), true);

                    //if (isset($decodedData['info']['title'])) {
                        $game_title = $decodedData['info']['title'];
                        $game_normal_price = floatval($decodedData['deals'][0]['retailPrice']);
                        $game_thumbnail = $decodedData['info']['thumb'];
                        echo $gameId;
                        $game = new Game($gameId, $game_title, $game_normal_price, $game_thumbnail, true);
                   // }

                } catch (Exception $exception) {
                    // You could render a .twig template here to show the error
                    $response->getBody()
                        ->write('Unexpected error: ' . $exception->getMessage());
                    return $response->withStatus(500);
                }

            return $this->twig->render($response, 'wishlistGame.twig',
                ['game' => $game, 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                    'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
        }
        else {
            return $response->withHeader('Location', '/login?logged=false')->withStatus(200);
        }
    }
}