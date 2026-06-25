CREATE DATABASE IF NOT EXISTS taakbeheer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taakbeheer;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `idActivityLog` int(11) NOT NULL AUTO_INCREMENT,
  `actie` varchar(50) NOT NULL,
  `entiteit` varchar(50) NOT NULL,
  `entiteit_id` int(11) DEFAULT NULL,
  `User_idUser` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idActivityLog`),
  KEY `fk_log_user` (`User_idUser`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`User_idUser`) REFERENCES `users` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `idCategory` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `kleurcode` varchar(7) NOT NULL DEFAULT '#cccccc',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idCategory`),
  UNIQUE KEY `uq_categories_naam` (`naam`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `idProject` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(150) NOT NULL,
  `beschrijving` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idProject`),
  UNIQUE KEY `uq_projects_naam` (`naam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `task_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_progress` (
  `idTaskProgress` int(11) NOT NULL AUTO_INCREMENT,
  `beschrijving` text NOT NULL,
  `Task_idTask` int(11) NOT NULL,
  `User_idUser` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idTaskProgress`),
  KEY `fk_progress_task` (`Task_idTask`),
  KEY `fk_progress_user` (`User_idUser`),
  CONSTRAINT `fk_progress_task` FOREIGN KEY (`Task_idTask`) REFERENCES `tasks` (`idTask`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_progress_user` FOREIGN KEY (`User_idUser`) REFERENCES `users` (`idUser`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `task_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_user` (
  `idTaskUser` int(11) NOT NULL AUTO_INCREMENT,
  `Task_idTask` int(11) NOT NULL,
  `User_idUser` int(11) NOT NULL,
  PRIMARY KEY (`idTaskUser`),
  UNIQUE KEY `uq_task_user` (`Task_idTask`,`User_idUser`),
  KEY `fk_taskuser_task` (`Task_idTask`),
  KEY `fk_taskuser_user` (`User_idUser`),
  CONSTRAINT `fk_taskuser_task` FOREIGN KEY (`Task_idTask`) REFERENCES `tasks` (`idTask`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_taskuser_user` FOREIGN KEY (`User_idUser`) REFERENCES `users` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `idTask` int(11) NOT NULL AUTO_INCREMENT,
  `titel` varchar(150) NOT NULL,
  `beschrijving` text DEFAULT NULL,
  `prioriteit` enum('laag','gemiddeld','hoog') NOT NULL DEFAULT 'gemiddeld',
  `status` enum('open','in_progress','done') NOT NULL DEFAULT 'open',
  `deadline` date DEFAULT NULL,
  `Category_idCategory` int(11) DEFAULT NULL,
  `Project_idProject` int(11) DEFAULT NULL,
  `User_idUser` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idTask`),
  KEY `fk_tasks_category` (`Category_idCategory`),
  KEY `fk_tasks_user` (`User_idUser`),
  KEY `fk_tasks_project` (`Project_idProject`),
  CONSTRAINT `fk_tasks_category` FOREIGN KEY (`Category_idCategory`) REFERENCES `categories` (`idCategory`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_project` FOREIGN KEY (`Project_idProject`) REFERENCES `projects` (`idProject`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tasks_user` FOREIGN KEY (`User_idUser`) REFERENCES `users` (`idUser`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `idUser` int(11) NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `wachtwoord_hash` varchar(255) NOT NULL,
  `rol` enum('admin','gebruiker') NOT NULL DEFAULT 'gebruiker',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idUser`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

