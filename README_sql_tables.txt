CREATE DATABASE test;
USE test;

CREATE TABLE `User` (
 `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
 `email` VARCHAR(255) NOT NULL DEFAULT '',
 `password` VARCHAR(255) NOT NULL DEFAULT '',
 `username` VARCHAR(255) NOT NULL DEFAULT '',
 `birthday` DATE,
 `phone` VARCHAR(255) NOT NULL DEFAULT '',
 `wallet` DOUBLE(11,2) unsigned NOT NULL DEFAULT 0,
  profile_picture VARCHAR(255) NOT NULL DEFAULT 'e79d0db1-c799-4c3b-a559-b2dbf4f84122.jpg',
 `is_validated` boolean DEFAULT FALSE,
 `created_at` DATETIME NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `Petition` (
 `token` INT(11) unsigned NOT NULL AUTO_INCREMENT,
 `email` VARCHAR(255) NOT NULL DEFAULT '',
 `created_at` DATETIME NOT NULL,
 PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `UserGames` (
 `transactionId` INT(11) unsigned NOT NULL AUTO_INCREMENT,
 `userId` INT(11) unsigned,
 `gameApiId` INT(11),
 PRIMARY KEY (`transactionId`),
 FOREIGN KEY (`userId`) REFERENCES User(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `FriendRequest` (
 `requestId` INT(11) unsigned NOT NULL AUTO_INCREMENT,
 `senderId` INT(11) unsigned,
 `recipientId` INT(11) unsigned,
 `created_at` DATETIME NOT NULL,
 PRIMARY KEY (`requestId`),
 FOREIGN KEY (`senderId`) REFERENCES User(`id`),
 FOREIGN KEY (`recipientId`) REFERENCES User(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Friend` (
 `friendshipId` INT(11) unsigned NOT NULL AUTO_INCREMENT,
 `userId` INT(11) unsigned,
 `user2Id` INT(11) unsigned,
 `accept_date` DATETIME NOT NULL,
 PRIMARY KEY (`friendshipId`),
 FOREIGN KEY (`userId`) REFERENCES User(`id`),
 FOREIGN KEY (`user2Id`) REFERENCES User(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `Wishlist` (
 `wishlistId` INT(11) unsigned NOT NULL AUTO_INCREMENT,
 `userId` INT(11) unsigned,
 `gameApiId` INT(11),
 PRIMARY KEY (`wishlistId`),
 FOREIGN KEY (`userId`) REFERENCES User(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


