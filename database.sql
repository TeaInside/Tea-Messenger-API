-- Adminer 4.6.3 MySQL dump

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE `addresses` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `address` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `user_id` (`user_id`),
  FULLTEXT KEY `address` (`address`),
  CONSTRAINT `addresses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `emails`;
CREATE TABLE `emails` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `emails_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `emails` (`id`, `user_id`, `email`, `created_at`) VALUES
(1, 1,  'ammarfaizi2@gmail.com',  '2018-12-31 15:26:19');

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `file_type` varchar(64) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `sha1_checksum` varchar(40) NOT NULL,
  `md5_checksum` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sha1_checksum` (`sha1_checksum`),
  KEY `md5_checksum` (`md5_checksum`),
  KEY `created_at` (`created_at`),
  KEY `updated_at` (`updated_at`),
  KEY `file_type` (`file_type`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `phones`;
CREATE TABLE `phones` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `phone` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `phones_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `phones` (`id`, `user_id`, `phone`, `created_at`) VALUES
(1, 1,  '085861572777', '2018-12-31 15:26:19');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `username` varchar(64) DEFAULT NULL,
  `gender` enum('m','f') NOT NULL,
  `password` text NOT NULL,
  `primary_email` bigint(20) DEFAULT NULL,
  `primary_phone` bigint(20) DEFAULT NULL,
  `primary_address` bigint(20) DEFAULT NULL,
  `registered_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `first_name` (`first_name`),
  KEY `last_name` (`last_name`),
  KEY `registered_at` (`registered_at`),
  KEY `updated_at` (`updated_at`),
  KEY `primary_email` (`primary_email`),
  KEY `primary_address` (`primary_address`),
  KEY `primary_phone` (`primary_phone`),
  CONSTRAINT `users_ibfk_4` FOREIGN KEY (`primary_email`) REFERENCES `emails` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_5` FOREIGN KEY (`primary_address`) REFERENCES `addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `users_ibfk_6` FOREIGN KEY (`primary_phone`) REFERENCES `phones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `gender`, `password`, `primary_email`, `primary_phone`, `primary_address`, `registered_at`, `updated_at`) VALUES
(1, 'Ammar',  'Faizi',  NULL, 'm',  'q/98aoBP/5oSab4IRAOKIg==', NULL, NULL, NULL, '2018-12-31 15:26:19',  NULL);

DROP TABLE IF EXISTS `user_keys`;
CREATE TABLE `user_keys` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `ukey` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_keys_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user_keys` (`id`, `user_id`, `ukey`, `created_at`) VALUES
(1, 1,  '7p1LVjGP+f+f/EAWrv5erzJNP8Tg8gt4dKnIzs6beTyY5jbx/g==', '2018-12-31 15:26:19');

-- 2018-12-31 10:52:49
