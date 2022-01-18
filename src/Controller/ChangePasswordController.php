<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Ramsey\Uuid\Uuid;
use Slim\Views\Twig;
use Psr\Http\Message;
use DateTime;
use Exception;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;

final class ChangePasswordController
{
    // test comment
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
            if (isset($_SESSION['id'])) {
                $data = $request->getParsedBody();
                $search_response = $this->userRepository->searchId($_SESSION['id']);
                $errors = $this->validateFields($data['old_password'], $search_response->password, $data['new_password'], $data['confirm_password']);

                if (!empty($errors[0])) {
                    return $this->twig->render($response, 'changePassword.twig',
                        ['errors' => $errors, 'opacity' => 0, 'logged' => $_SESSION['id'],
                            'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
                } else {
                    $hashed_password = password_hash($data['new_password'], PASSWORD_DEFAULT);
                    $update_response = $this->userRepository->updatePassword($hashed_password, $_SESSION['id']);

                    return $this->twig->render($response, 'changePassword.twig',
                        ['opacity' => 1, 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                            'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
                }
            }
            else {
                return $response->withHeader('Location', '/login?logged=false')->withStatus(200);
            }
        } catch (Exception $exception) {
            // You could render a .twig template here to show the error
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }
    }

    public function showChangePasswordPage(Request $request, Response $response)
    {
        return $this->twig->render($response, 'changePassword.twig',
            ['opacity' => 0, 'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
    }

    function validateFields($old_password, $user_password, $new_password, $confirm_password)
    {
        $errors = [];
        $u = 0;
        for ($i = 0; $i < 4; $i++) {
            $errors[$i] = '';
        }

        if (!password_verify($old_password, $user_password)) {
            $errors[$u] = sprintf('Wrong password');
            $u++;
        }

        if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password))
        {
            $errors[$u] = sprintf('The password has to contain, at least, an upper case letter, a lower case letter and a number');
            $u++;
        }

        if (strlen($new_password) <= 6) {
            $errors[$u] = sprintf('The password is not long enough (must contain more than 6 characters)');
            $u++;
        }

        if (strcmp($new_password, $confirm_password) !== 0) {
            $errors[$u] = sprintf('Passwords do not match');
            $u++;
        }

        return $errors;
    }
}