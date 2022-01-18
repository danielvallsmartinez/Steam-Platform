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

final class CreateUserController
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
            $errors = $this->validateFields($data['username'], $data['email'], $data['password1'],
                $data['password2'], $data['birthday'], $data['phone']);
            if (empty(!$errors[0]) || !empty($errors[1]) || !empty($errors[2]) || !empty($errors[3]) || !empty($errors[4]) || !empty($errors[5])) {
                return $this->twig->render($response, 'register.twig',
                    ['errors' => $errors, 'newusername' => $data['username'], 'email' => $data['email'], 'password1' => $data['password1'],
                        'password2' => $data['password2'], 'birthday' => $data['birthday'], 'phone' => $data['phone'], 'currentDate' => date('Y-m-d'),
                        'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
            }
            else {
                if (!$this->userRepository->searchEmail($data['email'])) {
                    if (!$this->userRepository->searchUsername($data['username'])) {
                        $user = new User(
                            $data['email'] ?? '',
                            $data['password1'] ?? '',
                            $data['username'] ?? '',
                            $data['birthday'] ?? '',
                            $data['phone'] ?? '',
                            new DateTime()
                        );
                    }
                    else {
                        $errors[0][sizeof($errors[0])] = 'This username is already registered!';
                        return $this->twig->render($response, 'register.twig',
                            ['errors' => $errors, 'newusername' => $data['username'], 'email' => $data['email'], 'password1' => $data['password1'],
                                'password2' => $data['password2'], 'birthday' => $data['birthday'], 'phone' => $data['phone'], 'currentDate' => date('Y-m-d'),
                                'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
                    }
                }
                else {
                    if ($this->userRepository->searchUsername($data['username'])) {
                        $errors[0][sizeof($errors[0])] = 'This username is already registered!';
                    }
                    $errors[1][sizeof($errors[1])] = 'This email is already registered!';
                    return $this->twig->render($response, 'register.twig',
                        ['errors' => $errors, 'newusername' => $data['username'], 'email' => $data['email'], 'password1' => $data['password1'],
                            'password2' => $data['password2'], 'birthday' => $data['birthday'], 'phone' => $data['phone'], 'currentDate' => date('Y-m-d'),
                            'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
                }
            }

            $this->userRepository->save($user);
            $token = $this->userRepository->savePendingValidation($user)->token;
            $this->sendValidationEmail($token, $user);

        } catch (Exception $exception) {
            // You could render a .twig template here to show the error
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }

        return $this->twig->render($response, 'registrationSuccessful.twig');
    }

    public function showRegisterForm(Request $request, Response $response)
    {
        return $this->twig->render($response, 'register.twig',
            ['logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'username' => $_SESSION['username'], 'avatar' => $_SESSION['avatar']]);
    }

    function validateFields($username, $email, $password1, $password2, $birthday, $phone)
    {
        $u = 0;
        $errors = [];

        for ($i = 0; $i < 6; $i++) {
            $errors[$i] = [];
        }

        if (!ctype_alnum($username)) {
            $errors[0][$u] = sprintf('The username has to be alphanumeric');
        }

        $u = 0;
        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[1][$u] = sprintf('The email %s is not valid', $email);
            $u++;
        }

        if (!str_ends_with($email, '@salle.url.edu') && !str_ends_with($email, '@students.salle.url.edu')) {
            $errors[1][$u] = sprintf('The email does not belong to neither @salle.url.edu nor @students.salle.url.edu domains');
        }

        $u = 0;
        if (!preg_match('/[A-Z]/', $password1) || !preg_match('/[a-z]/', $password1) || !preg_match('/[0-9]/', $password1))
        {
            $errors[2][$u] = sprintf('The password has to contain, at least, an upper case letter, a lower case letter and a number');
            $u++;
        }

        if (strlen($password1) <= 6) {
            $errors[2][$u] = sprintf('The password is not long enough (must contain more than 6 characters)');
        }

        $u = 0;
        if ($password1 != $password2) {
            $errors[3][$u] = sprintf('The passwords do not match');
        }


        $dateNow = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
        $birthdayDate = DateTime::createFromFormat('Y-m-d', $birthday);
        if ($birthdayDate !== false && !array_sum($birthdayDate::getLastErrors())) {
            if ($dateNow->diff($birthdayDate)->y < 18) {
                $errors[4][$u] = sprintf('You need to be an adult (18 years old or more) to be able to register');
            }
        }
        else {
            $errors[4][$u] = sprintf('The birthday date has to be dd/mm/yyyy and a real date');
        }

        if (!empty($phone)) {
            $u = 0;
            $phoneNoSpaces = str_replace(' ', '', $phone);
            if (str_starts_with($phoneNoSpaces, "+34")) {
                $phoneNoSpaces = str_replace("+34", "",$phoneNoSpaces);
                if (is_numeric($phoneNoSpaces)) {
                    if ($phoneNoSpaces[0] != '9' && $phoneNoSpaces[0] != '8' && $phoneNoSpaces[0] != '7' && $phoneNoSpaces[0] != '6') {
                        $errors[5][$u] = sprintf('The phone number can only start with 9/8 (land phone) or 7/6 (mobile phone)');
                        $u++;
                    }

                    if (strlen($phoneNoSpaces) != 9) {
                        $errors[5][$u] = sprintf('The phone number has to contain exactly 9 numbers (plus +34 in case you put the Spanish prefix)');
                    }

                }
                else {
                    $errors[5][$u] = sprintf('A phone number can only contain numeric characters');
                }
            }
            else {
                if (is_numeric($phoneNoSpaces)) {
                    if ($phoneNoSpaces[0] != '9' && $phoneNoSpaces[0] != '8' && $phoneNoSpaces[0] != '7' && $phoneNoSpaces[0] != '6') {
                        $errors[5][$u] = sprintf('The phone number can only start with 9/8 (land phone) or 7/6 (mobile phone)');
                        $u++;
                    }

                    if (strlen($phoneNoSpaces) != 9) {
                        $errors[5][$u] = sprintf('The phone number has to contain exactly 9 numbers (plus +34 in case you put the Spanish prefix)');
                    }

                }
                else {
                    $errors[5][$u] = sprintf('A phone number can only contain numeric characters');
                }
            }
        }

        return $errors;
    }

    public function sendValidationEmail(string $token, User $user) {

        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP(); //Send using SMTP
            $mail->Host = 'mail.smtpbucket.com'; //Set the SMTP server to send through
            $mail->Port = 8025; //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('LSteam@gmail.com');
            $mail->addAddress($user->email());

            $body = "Click on the link below to validate your account: <br><br>
                <a href='http://localhost:8031/activate?token=$token'>Validation link</a>";
            //Content
            $mail->isHTML(true); //Set email format to HTML
            $mail->Subject = 'Validate your LSteam account';
            $mail->Body = $body;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
