<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

use DateTime;
use Exception;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;

final class WalletController
{
    // test comment
    private Twig $twig;
    private UserRepository $userRepository;

    public function __construct(Twig $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function addMoney(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $error = $this->validateFields($data['addToWallet']);
            if (empty($error)) {
                $this->userRepository->addMoney($data['addToWallet'], $_SESSION['id']);
            }
            $wallet = $this->userRepository->searchId($_SESSION['id'])->wallet;
            $_SESSION['wallet'] = $wallet;
            return $this->twig->render($response, 'wallet.twig',
                ['error' => $error, 'wallet' => $wallet, 'logged' => $_SESSION['id'],
                    'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
        } catch (Exception $exception) {
            // You could render a .twig template here to show the error
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }
    }

    public function showWalletForm(Request $request, Response $response)
    {
        if (isset($_SESSION['id'])) {
            $wallet = $this->userRepository->searchId($_SESSION['id'])->wallet;
            return $this->twig->render($response, 'wallet.twig', ['wallet' => $wallet,
                'logged' => $_SESSION['id'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
        }
        else {

            //echo "<script type='text/javascript'>alert('You have to be logged to see your wallet!');</script>";
            return $response->withHeader('Location', '/login?logged=false')->withStatus(200);
        }
    }

    function validateFields(string $money): string
    {
        $error = '';
        if (!is_numeric($money)) {
            $error = "The quantity to add can only be numeric!";
        }
        else {
            if (floatval($money) <= 0) {
                $error = "The quantity to add has to be greater than 0";
            }
        }
        return $error;
    }
}