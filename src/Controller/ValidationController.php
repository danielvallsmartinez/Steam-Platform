<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use DateTime;
use Exception;
use phpDocumentor\Reflection\Types\Integer;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;
use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use function PHPUnit\Framework\isEmpty;

final class ValidationController
{

    private Twig $twig;
    private UserRepository $userRepository;

    public function __construct(Twig $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function tryToValidate(Request $request, Response $response): Response
    {
        try {
            $data = $_GET;
            $row = $this->userRepository->checkPendingValidation($data['token']);
            if (!$row) {
                $message = "This validation link is useless!";
                return $this->twig->render($response, 'validation.twig', ['message' => $message,
                    'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                    'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
            }
            else {
                $this->userRepository->validateUser($data['token']);
                $message = "The validation of your account has been successful!";
                $row = $this->userRepository->searchEmail($row->email);
                $this->userRepository->addMoney("50", $row->id);
                $this->sendConfirmRegistrationEmail($row->email);
                return $this->twig->render($response, 'validation.twig', ['message' => $message,
                    'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                    'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
            }
        } catch (Exception $exception) {
            // You could render a .twig template here to show the error
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }
    }

    public function showValidationMessage(Request $request, Response $response)
    {
        return $this->twig->render($response, 'validation.twig',
            ['logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'],
                'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
    }

    public function sendConfirmRegistrationEmail(string $email) {

        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP(); //Send using SMTP
            $mail->Host = 'mail.smtpbucket.com'; //Set the SMTP server to send through
            $mail->Port = 8025; //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('LSteam@gmail.com');
            $mail->addAddress($email);

            $body = "Congratulations, you have been registered! 50€ have been added to your wallet.
                Press the link below to go to the login page: <br><br>
                <a href='http://localhost:8031/login'>Login page</a>";

            //Content
            $mail->isHTML(true); //Set email format to HTML
            $mail->Subject = 'You have been registered';
            $mail->Body = $body;

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}