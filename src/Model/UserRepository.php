<?php
declare(strict_types=1);

namespace SallePW\SlimApp\Model;

use DateTime;
use Psr\Http\Message\UploadedFileInterface;

interface UserRepository
{
    public function save(User $user): void;

    public function savePendingValidation(User $user);

    public function checkPendingValidation(string $token);

    public function validateUser(string $token);

    public function searchId(string $id);

    public function searchEmail(string $email);

    public function searchUser(User $user);

    public function searchUsername(string $username);

    public function addMoney(string $money, string $id);

    public function removeMoney(float $money, string $id);

    public function searchMyGames(string $userId);

    public function addUserGame(string $gameID, string $id);

    public function updateUser(string $phone, string $email, string $profile_picture, UploadedFileInterface $uploadedFiles);

    public function updatePassword(string $password, string $email);

    public function deleteFromWishlist(string $gameID, string $id);

    public function addToWishlist(string $gameID, string $id);

    public function searchWishlistedGames(string $userId);

    public function isWishlisted(string $userId, $gameID);

    public function addFriendRequest(string $recipientId, string $senderId, DateTime $created_at);

    public function addFriend(string $userId, string $user2Id, DateTime $accept_date);

    public function removeRequest(string $id);

    public function searchRequest(string $id);

    public function searchFriendRequests(string $id);

    public function searchFriends(string $id);
}