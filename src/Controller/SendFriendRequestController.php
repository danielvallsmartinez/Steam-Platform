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


final class SendFriendRequestController
{

    // test comment
    private Twig $twig;
    private UserRepository $userRepository;

    public function __construct(Twig $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function sendFriendRequest(Request $request, Response $response): Response
    {

        $data = $request->getParsedBody();
        $username = $data["username"];
        $error = $this->validateFields($username);

        if (!empty($error)) {
            return $this->twig->render($response, 'sendFriendRequest.twig',
                ['username' => $_SESSION['username'], 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                    'avatar' => $_SESSION['avatar'], 'error' => "The username has to be alphanumeric"]);
        }
        else {
            $search_response = $this->userRepository->searchUsername($username);
            if (!empty($search_response)) {
                $friends_response = $this->userRepository->searchFriends($_SESSION['id']);
                $isFriend = false;
                foreach ($friends_response as $friend) {
                    if ($friend["user2Id"] == $search_response->id || $friend["userId"] == $search_response->id) {
                        $isFriend = true;
                        break;
                    }
                }

                if ($isFriend){
                    return $this->twig->render($response, 'sendFriendRequest.twig',
                        ['username' => $_SESSION['username'], 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                            'avatar' => $_SESSION['avatar'], 'error' => "This user is already your friend"]);
                }
                else {
                    $requests_response = $this->userRepository->searchFriendRequests($_SESSION['id']);
                    $requested = false;
                    foreach ($requests_response as $request) {
                        if ($request["recipientId"] == $search_response->id) {
                            $requested = true;
                            break;
                        }
                    }
                    if ($requested){
                        return $this->twig->render($response, 'sendFriendRequest.twig',
                            ['username' => $_SESSION['username'], 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                                'avatar' => $_SESSION['avatar'], 'error' => "A friend request has already been sent to this user"]);
                    }
                    else {
                        $this->userRepository->addFriendRequest($search_response->id, $_SESSION['id'], new DateTime());

                        return $this->twig->render($response, 'sendFriendRequest.twig',
                            ['username' => $_SESSION['username'], 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                                'avatar' => $_SESSION['avatar'], 'message' => "Request sent to $username"]);
                    }
                }
            }
            else {
                return $this->twig->render($response, 'sendFriendRequest.twig',
                    ['username' => $_SESSION['username'], 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                        'avatar' => $_SESSION['avatar'], 'error' => "This user doesn't have an active account"]);
            }
        }
    }

    public function showSendFriendRequestPage(Request $request, Response $response): Response
    {

        if (isset($_SESSION['id'])) {

            return $this->twig->render($response, 'sendFriendRequest.twig',
                ['username' => $_SESSION['username'], 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                    'avatar' => $_SESSION['avatar']]);
        } else {
            return $response->withHeader('Location', '/login?logged_profile=false')->withStatus(200);
        }
    }

    function validateFields($username)
    {
        $error = '';
        if (!ctype_alnum($username)) {
            $error = sprintf('The username has to be alphanumeric');
        }

        return $error;
    }
}