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


final class MyGamesController
{

    // test comment
    private Twig $twig;
    private UserRepository $userRepository;

    public function __construct(Twig $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function showMyGames(Request $request, Response $response): Response
    {
        // return
        if (isset($_SESSION['id'])) {
            $gamesMysql = $this->userRepository->searchMyGames($_SESSION['id']);
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
                        array_push($games, new Game($game_ID, $game_title, $game_normal_price, $game_thumbnail, $this->userRepository->isWishlisted($_SESSION['id'], $game_ID)));
                    }

                } catch (Exception $exception) {
                    // You could render a .twig template here to show the error
                    $response->getBody()
                        ->write('Unexpected error: ' . $exception->getMessage());
                    return $response->withStatus(500);
                }
            }
            return $this->twig->render($response, 'myGames.twig',
                ['games' => $games, 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                    'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
        }
        else {
            return $response->withHeader('Location', '/login?logged=false')->withStatus(200);
        }
    }
}