<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

use DateTime;
use Exception;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;

final class LoginController
{
    private Twig $twig;
    private UserRepository $userRepository;

    public function __construct(Twig $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function apply(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = new User(
                $data['usernameOrEmail'] ?? '',
                $data['password'] ?? '',
                $data['usernameOrEmail'] ?? '',
                '',
                '',
                new DateTime()
            );
            $error = $this->validateFields($data['usernameOrEmail'], $data['password']);
            if (!empty($error)) {
                return $this->twig->render($response, 'login.twig',
                    ['error' => $error, 'usernameOrEmail' => $data['usernameOrEmail'], 'password' => $data['password'],
                        'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
            }
            else {
                $search_response = $this->userRepository->searchUser($user);
                if ($search_response) {
                    if (password_verify($user->password(), $search_response->password)) {
                        session_unset(); //We clear last session if exists
                        $_SESSION['id'] = $search_response->id;
                        $_SESSION['wallet'] = $search_response->wallet;
                        $_SESSION['username'] = $search_response->username;
                        $_SESSION['avatar'] = $search_response->profile_picture;
                        return $response->withHeader('Location', '/store')->withStatus(200);
                    }
                    else {
                        $error = 'Invalid user/mail and/or password db';
                        return $this->twig->render($response, 'login.twig',
                            ['error' => $error, 'usernameOrEmail' => $data['usernameOrEmail'], 'password' => $data['password'],
                                'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
                    }
                }
                else {
                    $error = 'Invalid user/mail and/or password';
                    return $this->twig->render($response, 'login.twig',
                        ['error' => $error, 'usernameOrEmail' => $data['usernameOrEmail'], 'password' => $data['password'],
                            'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
                }
            }
        } catch (Exception $exception) {
            // You could render a .twig template here to show the error
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }
    }

    public function logout(Request $request, Response $response): Response
    {
        try {
            session_unset(); //We clear last session
            return $response->withHeader('Location', '/home')->withStatus(200);
        } catch (Exception $exception) {
            // You could render a .twig template here to show the error
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }
    }

    public function showLoginForm(Request $request, Response $response)
    {

        if (isset($_GET['logged'])) {
            echo "<script type='text/javascript'>alert('You have to be logged to see your wallet!');</script>";
        }
        if (isset($_GET['logged_profile'])){
            echo "<script type='text/javascript'>alert('You have to be logged to see your profile!');</script>";
        }
        if (isset($_GET['buy'])){
            echo "<script type='text/javascript'>alert('You have to be logged to buy a game!');</script>";
        }
        return $this->twig->render($response, 'login.twig',
            ['logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
    }

    function validateFields($usernameOrEmail, $password): string
    {
        $error = '';

        if ((!ctype_alnum($usernameOrEmail) &&
            (false === filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL) ||
            (!str_ends_with($usernameOrEmail, '@salle.url.edu') &&
            !str_ends_with($usernameOrEmail, '@students.salle.url.edu')))) ||
            (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)) || strlen($password) <= 6)
        {
            $error = sprintf('Invalid user/mail and/or password');
        }

        return $error;
    }
}