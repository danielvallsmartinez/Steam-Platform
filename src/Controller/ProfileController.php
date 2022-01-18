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

final class ProfileController
{
    // test comment
    private Twig $twig;
    private UserRepository $userRepository;
    private const UPLOADS_DIR = __DIR__ . '/../../public/uploads';

    private const UNEXPECTED_ERROR = "An unexpected error occurred uploading the file '%s'...";

    private const INVALID_EXTENSION_ERROR = "The received file extension '%s' is not valid";

    private const INVALID_SIZE_ERROR = "The file '%s' is bigger than 1MB";

    // We use this const to define the extensions that we are going to allow
    private const ALLOWED_EXTENSIONS = ['jpg', 'png'];

    public function __construct(Twig $twig, UserRepository $userRepository)
    {
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function apply(Request $request, Response $response): Response
    {
        try {

            $uploadedFiles = $request->getUploadedFiles();
            $data = $request->getParsedBody();
            $name = $uploadedFiles['file']->getClientFilename();
            if($name != NULL) {
                list($width, $height) = getimagesize($_FILES["file"]["tmp_name"]);
            }else {
                $width = 0;
                $height = 0;
            }
            $errors = $this->validateFields($data['phone'], $uploadedFiles, $width, $height);

            $search_response = $this->userRepository->searchId($_SESSION['id']);

            if (!empty($errors[0]) || !empty($errors[1])) {
                return $this->twig->render($response, 'profile.twig',
                    ['errors' => $errors, 'username' => $search_response->username,
                        'opacity' => 0, 'email' => $search_response->email, 'birthday' => $search_response->birthday,
                        'phone' => $search_response->phone, 'logged' => $_SESSION['id'],
                        'wallet' => $_SESSION['wallet'], 'avatar' => $_SESSION['avatar']]);
            } else {
                $name = $uploadedFiles['file']->getClientFilename();
                if ($name != NULL) {
                    $fileInfo = pathinfo($name);
                    $format = $fileInfo['extension'];
                    $new_name = Uuid::uuid4()->toString();

                    // We generate a custom name here instead of using the one coming form the form
                    $uploadedFiles['file']->moveTo(self::UPLOADS_DIR . DIRECTORY_SEPARATOR . $new_name . "." . $format);
                    $this->userRepository->updateUser($data['phone'], $_SESSION['id'], $new_name . "." . $format, $uploadedFiles['file']);
                    $updated = $this->userRepository->searchId($_SESSION['id']);
                    $_SESSION['avatar'] = $updated->profile_picture;
                }
                else {
                    $this->userRepository->updateUser($data['phone'], $_SESSION['id'], '', $uploadedFiles['file']);
                }
                return $this->twig->render($response, 'profile.twig',
                    ['errors' => $errors, 'username' => $search_response ->username,
                        'opacity' => 1, 'email' => $search_response->email, 'birthday' => $search_response->birthday,
                        'phone' => $data['phone'], 'logged' => $_SESSION['id'],
                        'wallet' => $_SESSION['wallet'], 'avatar' => $_SESSION['avatar']]);
            }
        } catch (Exception $exception) {
            // You could render a .twig template here to show the error
            $response->getBody()
                ->write('Unexpected error: ' . $exception->getMessage());
            return $response->withStatus(500);
        }
    }

    public function showProfilePage(Request $request, Response $response)
    {
        if (isset($_SESSION['id'])) {
            $search_response = $this->userRepository->searchId($_SESSION['id']);

            return $this->twig->render($response, 'profile.twig',
                ['username' => $search_response->username, 'email' => $search_response->email,
                    'opacity' => 0, 'birthday' => $search_response->birthday, 'phone' => $search_response->phone,
                    'logged' => $_SESSION['id'], 'wallet' => $_SESSION['wallet'], 'avatar' => $_SESSION['avatar']]);
        }
        else {
            return $response->withHeader('Location', '/login?logged_profile=false')->withStatus(200);
        }
    }

    function validateFields($phone, $uploadedFiles, $width, $height)
    {
        $u = 0;
        $errors = [];

        for ($i = 0; $i < 2; $i++) {
            $errors[$i] = [];
        }

        if (!empty($phone)) {
            $u = 0;
            $phoneNoSpaces = str_replace(' ', '', $phone);
            if (str_starts_with($phoneNoSpaces, "+34")) {
                $phoneNoSpaces = str_replace("+34", "",$phoneNoSpaces);
                if (is_numeric($phoneNoSpaces)) {
                    if ($phoneNoSpaces[0] != '9' && $phoneNoSpaces[0] != '8' && $phoneNoSpaces[0] != '7' && $phoneNoSpaces[0] != '6') {
                        $errors[0][$u] = sprintf('The phone number can only start with 9/8 (land phone) or 7/6 (mobile phone)');
                        $u++;
                    }

                    if (strlen($phoneNoSpaces) != 9) {
                        $errors[0][$u] = sprintf('The phone number has to contain exactly 9 numbers (plus +34 in case you put the Spanish prefix)');
                    }

                }
                else {
                    $errors[0][$u] = sprintf('A phone number can only contain numeric characters');
                }
            }
            else {
                if (is_numeric($phoneNoSpaces)) {
                    if ($phoneNoSpaces[0] != '9' && $phoneNoSpaces[0] != '8' && $phoneNoSpaces[0] != '7' && $phoneNoSpaces[0] != '6') {
                        $errors[0][$u] = sprintf('The phone number can only start with 9/8 (land phone) or 7/6 (mobile phone)');
                        $u++;
                    }

                    if (strlen($phoneNoSpaces) != 9) {
                        $errors[0][$u] = sprintf('The phone number has to contain exactly 9 numbers (plus +34 in case you put the Spanish prefix)');
                    }

                }
                else {
                    $errors[0][$u] = sprintf('A phone number can only contain numeric characters');
                }
            }
        }

        $u = 0;
        $name = $uploadedFiles['file']->getClientFilename();
        if($name != NULL) {
                if ($uploadedFiles['file']->getError() !== UPLOAD_ERR_OK) {
                    $errors[1][$u] = sprintf(
                        self::UNEXPECTED_ERROR,
                        $uploadedFiles['file']->getClientFilename()
                    );
                    $u++;
                }

                $name = $uploadedFiles['file']->getClientFilename();

                $fileInfo = pathinfo($name);

                if ($uploadedFiles['file']->getSize() >= 2097152) {
                    $errors[1][$u] = sprintf(self::INVALID_SIZE_ERROR, $name);
                    $u++;
                }

                $format = $fileInfo['extension'];

                if (!$this->isValidFormat($format)) {
                    $errors[1][$u] = sprintf(self::INVALID_EXTENSION_ERROR, $format);
                    $u++;
                }

                if ($width > 500 || $height > 500) {
                    $errors[1][$u] = sprintf("Image size must be 500x500 or smaller");
                }

        }

        return $errors;
    }

    private function isValidFormat(string $extension): bool
    {
        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }
}