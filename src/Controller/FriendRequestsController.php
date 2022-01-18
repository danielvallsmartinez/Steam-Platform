<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use DateTime;
use Exception;

use GuzzleHttp\Client;
use SallePW\SlimApp\Model\FriendRequest;
use SallePW\SlimApp\Model\Game;
use SallePW\SlimApp\Model\UserRepository;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


final class FriendRequestsController
{

    // test comment
    private Twig $twig;
    private UserRepository $userRepository;

    public function __construct(Twig $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function acceptRequest(Request $request, Response $response) : Response
    {

        $data = $request->getParsedBody();
        $requestId = $data['accept'];
        $search_response = $this->userRepository->searchRequest($requestId);
        $user = $this->userRepository->searchId($search_response->senderId);
        $usernameAddedUser = $user->username;

        if (!isset($_SESSION['id']) || $_SESSION['id'] != $search_response->recipientId) {
            if (!isset($_SESSION['id'])) {
                return $response->withHeader('Location', '/login?logged_profile=false')->withStatus(200);
            }
            else {
                return $this->twig->render($response, 'friendRequests.twig',
                    ['logged' => $_SESSION['id'], 'message' => "You are not allowed to access to this page",
                        'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
            }
        }
        else {
            $this->userRepository->removeRequest($requestId);
            $this->userRepository->addFriend($search_response->senderId, $search_response->recipientId, new DateTime());

            $search_requests = $this->userRepository->searchFriendRequests($_SESSION['id']);

            $requests = [];
            foreach ($search_requests as $request) {
                $requestId = $request["requestId"];
                $user = $this->userRepository->searchUser($request["senderId"]);
                $username = $user["username"];
                $created_at = $request["created_at"];
                array_push($requests, new FriendRequest($requestId, $username, $created_at));
            }

            return $this->twig->render($response, 'friendRequests.twig', ['requests' => $requests,
                'logged' => $_SESSION['id'], 'message' => "$usernameAddedUser is now your friend!",
                'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
        }



    }

    public function showFriendRequestsPage(Request $request, Response $response): Response
    {

        if (isset($_SESSION['id'])) {
            try {
                $search_response = $this->userRepository->searchFriendRequests($_SESSION['id']);
                $requests = [];
                foreach ($search_response as $request) {
                    $requestId = $request['requestId'];

                    $user = $this->userRepository->searchId($request['senderId']);
                    $username = $user->username;
                    $created_at = $request['created_at'];
                    $datetime = new DateTime();
                    $created_at = $datetime->createFromFormat('Y-m-d H:i:s', $created_at);

                    array_push($requests, new FriendRequest($requestId, $username, $created_at));
                }

                return $this->twig->render($response, 'friendRequests.twig', ['requests' => $requests,
                    'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username']]);

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