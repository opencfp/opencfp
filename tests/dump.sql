-- MySQL dump 10.13  Distrib 5.7.20, for osx10.10 (x86_64)
--
-- Host: localhost    Database: cfp_test
-- ------------------------------------------------------
-- Server version	5.7.20

DROP DATABASE IF EXISTS`cfp_test` ;
CREATE DATABASE `cfp_test`;
USE `cfp_test`;

--
-- Table structure for table `airports`
--

DROP TABLE IF EXISTS `airports`;
CREATE TABLE `airports` (
  `code` varchar(3) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `airports`
--

LOCK TABLES `airports` WRITE;
/*!40000 ALTER TABLE `airports` DISABLE KEYS */;
INSERT INTO `airports` VALUES ('AAC','Al Arish','Egypt'),('AAE','Annaba','Algeria'),('AAL','Alborg','Denmark');
/*!40000 ALTER TABLE `airports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) NOT NULL,
  `talk_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `permissions` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groups_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'Speakers','{\"users\":1}','2017-11-13 14:54:21','2017-11-13 14:54:21'),(2,'Admin','{\"admin\":1}','2017-11-13 14:54:21','2017-11-13 14:54:21'),(3,'Reviewer','{\"reviewer\":1}','2017-11-13 14:54:22','2017-11-13 14:54:22');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `talk_comments`
--

DROP TABLE IF EXISTS `talk_comments`;
CREATE TABLE `talk_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `talk_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `talk_comment__user_id__talk_id` (`user_id`,`talk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `talk_meta`
--

DROP TABLE IF EXISTS `talk_meta`;
CREATE TABLE `talk_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) NOT NULL,
  `talk_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '0',
  `viewed` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `talk_meta__admin_user_id__talk_id` (`admin_user_id`,`talk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `talks`
--

DROP TABLE IF EXISTS `talks`;
CREATE TABLE `talks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `other` text,
  `type` varchar(50) NOT NULL,
  `level` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `slides` varchar(255) DEFAULT NULL,
  `desired` tinyint(1) NOT NULL DEFAULT '0',
  `sponsor` tinyint(1) NOT NULL DEFAULT '0',
  `favorite` tinyint(1) NOT NULL DEFAULT '0',
  `selected` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `permissions` text,
  `activated` tinyint(1) NOT NULL DEFAULT '0',
  `activation_code` varchar(255) DEFAULT NULL,
  `persist_code` varchar(255) DEFAULT NULL,
  `reset_password_code` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `airport` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `transportation` int(11) DEFAULT NULL,
  `hotel` int(11) DEFAULT NULL,
  `info` text,
  `bio` text,
  `photo_path` varchar(255) DEFAULT NULL,
  `has_made_profile` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
CREATE TABLE `users_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dump completed on 2017-11-13 14:54:36
