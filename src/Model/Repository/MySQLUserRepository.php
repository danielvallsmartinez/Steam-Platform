<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model\Repository;

use DateTime;
use PDO;
use PhpParser\Node\Expr\Cast\Object_;
use Psr\Http\Message\UploadedFileInterface;

use SallePW\SlimApp\Model\Search;
use SallePW\SlimApp\Model\User;
use SallePW\SlimApp\Model\UserRepository;

final class MysqlUserRepository implements UserRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private PDOSingleton $database;

    public function __construct(PDOSingleton $database)
    {
        $this->database = $database;
    }

    public function save(User $user): void
    {
        $query = <<<'QUERY'
        INSERT INTO User(email, password, username, birthday, phone, created_at)
        VALUES(:email, :password, :username, :birthday, :phone, :created_at)
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $email = $user->email();
        $password = $user->password();
        $username = $user->username();
        $birthday = $user->birthday();
        $phone = $user->phone();
        $createdAt = $user->createdAt()->format(self::DATE_FORMAT);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('password', $passwordHash, PDO::PARAM_STR);
        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->bindParam('birthday', $birthday, PDO::PARAM_STR);
        $statement->bindParam('phone', $phone, PDO::PARAM_STR);
        $statement->bindParam('created_at', $createdAt, PDO::PARAM_STR);

        $statement->execute();
    }

    public function savePendingValidation(User $user) {
        $query = <<<'QUERY'
        INSERT INTO Petition(email, created_at)
        VALUES(:email, :created_at)
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $email = $user->email();
        $createdAt = $user->createdAt()->format(self::DATE_FORMAT);

        $statement->bindParam('email', $email, PDO::PARAM_STR);
        $statement->bindParam('created_at', $createdAt, PDO::PARAM_STR);

        $statement->execute();

        $query = <<<'QUERY'
        SELECT token FROM Petition WHERE email = :email
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $email = $user->email();

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        return $row;
    }

    public function checkPendingValidation(string $token) {
        $query = <<<'QUERY'
        SELECT * FROM Petition WHERE token = :token
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('token',$token, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        return $row;
    }

    public function validateUser(string $token) {
        $query = <<<'QUERY'
        SELECT * FROM Petition WHERE token = :token
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('token', $token, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        $query = <<<'QUERY'
        DELETE FROM Petition WHERE token = :token
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('token', $token, PDO::PARAM_STR);

        $statement->execute();

        $query = <<<'QUERY'
        UPDATE User SET is_validated = TRUE WHERE email = :email
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('email', $row->email, PDO::PARAM_STR);

        $statement->execute();
    }

    //For the login
    public function searchUser(User $user) {
        $query = <<<'QUERY'
        SELECT * FROM User WHERE  (email = :email OR username = :username) AND is_validated = TRUE
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $username = $user->username();
        $email = $user->email();

        $statement->bindParam('username', $username, PDO::PARAM_STR);
        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        return $row;
    }

    public function searchId(string $id) {
        $query = <<<'QUERY'
        SELECT * FROM User WHERE id = :id
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        return $row;
    }

    public function searchUsername(string $username) {
        $query = <<<'QUERY'
        SELECT * FROM User WHERE username = :username
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('username', $username, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        return $row;
    }

    public function searchEmail(string $email) {
        $query = <<<'QUERY'
        SELECT * FROM User WHERE email = :email
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('email', $email, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        return $row;
    }

    public function searchFriends(string $id) {
        $query = <<<'QUERY'
        SELECT f.userId, f.user2Id, u.username, f.accept_date 
        FROM Friend AS f, User AS u WHERE u.id = :id AND (u.id = f.userId OR u.id = f.user2Id)
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetchAll(PDO::FETCH_ASSOC);

        return $row;
    }


    public function searchFriendRequests(string $id) {
        $query = <<<'QUERY'
        SELECT f.requestId, f.created_at, f.senderId, f.recipientId 
        FROM FriendRequest AS f, User AS u WHERE u.id = :id AND u.id = f.recipientId
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetchAll(PDO::FETCH_ASSOC);

        return $row;
    }

    public function searchRequest(string $id) {
        $query = <<<'QUERY'
        SELECT * FROM FriendRequest WHERE requestId = :id
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        return $row;
    }

    public function removeRequest(string $id) {
        $query = <<<'QUERY'
        DELETE FROM FriendRequest WHERE requestId = :id
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);

        $statement->execute();
    }

    public function addFriend(string $userId, string $user2Id, DateTime $accept_date) {
        $query = <<<'QUERY'
        INSERT INTO Friend(userId, user2Id, accept_date)
        VALUES(:userId, :user2Id, :accept_date)
        QUERY;
        $statement = $this->database->connection()->prepare($query);
        $accept_date = $accept_date->format(self::DATE_FORMAT);

        $statement->bindParam('userId', $userId, PDO::PARAM_STR);
        $statement->bindParam('user2Id', $user2Id, PDO::PARAM_STR);
        $statement->bindParam('accept_date', $accept_date, PDO::PARAM_STR);

        $statement->execute();
    }

    public function addFriendRequest(string $recipientId, string $senderId, DateTime $created_at) {
        $query = <<<'QUERY'
        INSERT INTO FriendRequest(senderId, recipientId, created_at)
        VALUES(:senderId, :recipientId, :created_at)
        QUERY;
        $statement = $this->database->connection()->prepare($query);
        $created_at = $created_at->format(self::DATE_FORMAT);

        $statement->bindParam('senderId', $senderId, PDO::PARAM_STR);
        $statement->bindParam('recipientId', $recipientId, PDO::PARAM_STR);
        $statement->bindParam('created_at', $created_at, PDO::PARAM_STR);

        $statement->execute();
    }

        /* function registerSearch(Search $search) {
            $query = <<<'QUERY'
            INSERT INTO Search(user_id, search, created_at)
            VALUES(:user_id, :search, :created_at)
            QUERY;
            $statement = $this->database->connection()->prepare($query);

            $userId = intval($search->userId());

            $searchAux = $search->search();
            $createdAt = $search->createdAt()->format(self::DATE_FORMAT);

            $statement->bindParam('user_id', $userId, PDO::PARAM_STR);
            $statement->bindParam('search', $searchAux, PDO::PARAM_STR);
            $statement->bindParam('created_at', $createdAt, PDO::PARAM_STR);

            $statement->execute();
        }*/

    public function addMoney(string $money, string $id) {
        $query = <<<'QUERY'
        SELECT * FROM User WHERE id = :id
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        $query = <<<'QUERY'
        UPDATE User SET wallet = :wallet WHERE id = :id
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $addToWallet = floatval($money) + floatval($row->wallet);

        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->bindParam('wallet', $addToWallet, PDO::PARAM_STR);

        $statement->execute();
    }

    public function removeMoney(float $money, string $id) {
        $query = <<<'QUERY'
        SELECT * FROM User WHERE id = :id
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        $query = <<<'QUERY'
        UPDATE User SET wallet = :wallet WHERE id = :id
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $addToWallet = floatval($row->wallet) - $money;

        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->bindParam('wallet', $addToWallet, PDO::PARAM_STR);

        $statement->execute();
    }

    public function searchMyGames(string $userId) {
        $query = <<<'QUERY'
        SELECT * FROM UserGames WHERE userId = :userId
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('userId', $userId, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetchAll(PDO::FETCH_ASSOC);

        return $row;
    }

    public function addUserGame(string $gameID, string $id) {

        $query = <<<'QUERY'
        INSERT INTO UserGames (userId, gameApiId) VALUES (:userId, :gameApiId)
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('userId', $id, PDO::PARAM_STR);
        $statement->bindParam('gameApiId', $gameID, PDO::PARAM_STR);

        $statement->execute();
    }

    public function updateUser(string $phone, string $id, string $profile_picture, UploadedFileInterface $uploadedFiles) {
        $name = $uploadedFiles->getClientFilename();
        if ($name != NULL) {
            $query = <<<'QUERY'
            UPDATE User SET phone = :phone, profile_picture = :profile_picture WHERE id = :id
            QUERY;
            $statement = $this->database->connection()->prepare($query);

            $statement->bindParam('id', $id, PDO::PARAM_STR);
            $statement->bindParam('phone', $phone, PDO::PARAM_STR);
            $statement->bindParam('profile_picture', $profile_picture, PDO::PARAM_STR);
        }
        else {
            $query = <<<'QUERY'
            UPDATE User SET phone = :phone WHERE id = :id
            QUERY;
            $statement = $this->database->connection()->prepare($query);
            $statement->bindParam('id', $id, PDO::PARAM_STR);
            $statement->bindParam('phone', $phone, PDO::PARAM_STR);
        }

        $statement->execute();
    }

    public function updatePassword(string $password, string $id) {
        $query = <<<'QUERY'
        UPDATE User SET password = :password WHERE id = :id
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('id', $id, PDO::PARAM_STR);
        $statement->bindParam('password', $password, PDO::PARAM_STR);

        $statement->execute();
    }

    // WISHLIST RELATED QUERIES

    public function isWishlisted(string $userId, $gameID) {
        $query = <<<'QUERY'
        SELECT * FROM Wishlist WHERE userId = :userId AND gameApiId = :gameApiId
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('userId', $userId, PDO::PARAM_STR);
        $statement->bindParam('gameApiId', $gameID, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetch(PDO::FETCH_OBJ);

        return $row;
    }

    public function searchWishlistedGames(string $userId){
        $query = <<<'QUERY'
        SELECT * FROM Wishlist WHERE userId = :userId
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('userId', $userId, PDO::PARAM_STR);

        $statement->execute();

        $row =  $statement->fetchAll(PDO::FETCH_ASSOC);

        return $row;
    }

    public function addToWishlist(string $gameID, string $id) {

        $query = <<<'QUERY'
        INSERT INTO Wishlist (userId, gameApiId) VALUES (:userId, :gameApiId)
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('userId', $id, PDO::PARAM_STR);
        $statement->bindParam('gameApiId', $gameID, PDO::PARAM_STR);

        $statement->execute();
    }

    public function deleteFromWishlist(string $gameID, string $id) {

        $query = <<<'QUERY'
        DELETE FROM Wishlist WHERE userId = :userId AND gameApiId = :gameApiId
        QUERY;
        $statement = $this->database->connection()->prepare($query);

        $statement->bindParam('userId', $id, PDO::PARAM_STR);
        $statement->bindParam('gameApiId', $gameID, PDO::PARAM_STR);

        $statement->execute();
    }
}