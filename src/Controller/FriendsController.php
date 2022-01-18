<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use DateTime;
use Exception;

use GuzzleHttp\Client;
use SallePW\SlimApp\Model\Friend;
use SallePW\SlimApp\Model\Game;
use SallePW\SlimApp\Model\UserRepository;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


final class FriendsController
{

    // test comment
    private Twig $twig;
    private UserRepository $userRepository;

    public function __construct(Twig $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }


    public function showFriendsPage(Request $request, Response $response): Response
    {
        if (isset($_SESSION['id'])) {
            try {
                $search_response = $this->userRepository->searchFriends($_SESSION['id']);
                $friends = [];
                foreach ($search_response as $friend) {
                    if ($friend["user2Id"] != $_SESSION['id']) {
                        $user = $this->userRepository->searchId($friend["user2Id"]);
                        $username = $user->username;
                        $id = $friend["user2Id"];
                    }
                    else {
                        $user = $this->userRepository->searchId($friend["userId"]);
                        $username = $user->username;
                        $id = $friend["userId"];
                    }
                    $accept_date = $friend["accept_date"];
                    array_push($friends, new Friend($id, $username, $accept_date));
                }

                return $this->twig->render($response, 'friends.twig', ['friends' => $friends,
                    'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'],
                    'avatar' => $_SESSION['avatar']]);

            } catch (Exception $exception) {
                // You could render a .twig template here to show the error
                $response->getBody()
                    ->write('Unexpected error: ' . $exception->getMessage());
                return $response->withStatus(500);
            }
        }
        else {
            return $response->withHeader('Location', '/login?logged_profile=false')->withStatus(200);
        }
    }
}