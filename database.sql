-- MySQL dump 10.13  Distrib 8.0.25, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: adamrms
-- ------------------------------------------------------
-- Server version	8.0.25

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `actions`
--

DROP TABLE IF EXISTS `actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actions` (
  `actions_id` int NOT NULL AUTO_INCREMENT,
  `actions_name` varchar(255) NOT NULL,
  `actionsCategories_id` int NOT NULL,
  `actions_dependent` varchar(500) DEFAULT NULL,
  `actions_incompatible` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`actions_id`),
  KEY `actions_actionsCategories_actionsCategories_id_fk` (`actionsCategories_id`),
  CONSTRAINT `actions_actionsCategories_actionsCategories_id_fk` FOREIGN KEY (`actionsCategories_id`) REFERENCES `actionscategories` (`actionsCategories_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actions`
--

LOCK TABLES `actions` WRITE;
/*!40000 ALTER TABLE `actions` DISABLE KEYS */;
INSERT INTO `actions` VALUES (2,'Access a list of users',1,NULL,NULL),(4,'Create a new user',1,'2',NULL),(5,'Edit details about a user',1,'2,4',NULL),(6,'View mailing for a user',1,'2',NULL),(7,'View audit log',3,NULL,NULL),(8,'Create a new instance',4,'',NULL),(9,'Suspend a user',1,'2',NULL),(10,'View site as a user',1,'2,3,5,6,9',NULL),(11,'Access a list of permissions',2,NULL,NULL),(12,'Edit list of permissions',2,NULL,NULL),(13,'Change a user\'s permissions',2,'5,16',NULL),(14,'Set a user\'s thumbnail',1,'5',NULL),(15,'Delete a user',1,'2',NULL),(16,'View own positions',2,'',NULL),(17,'Use the Development Site',4,'',NULL),(18,'View PHP Info',4,NULL,NULL),(19,'Edit any asset type - even those written by AdamRMS',5,NULL,NULL),(20,'Access a list of instances',4,NULL,NULL),(21,'Log in to any instance with full permissions',4,'20',NULL),(22,'Change another users notification settings',1,'5',NULL);
/*!40000 ALTER TABLE `actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actionscategories`
--

DROP TABLE IF EXISTS `actionscategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actionscategories` (
  `actionsCategories_id` int NOT NULL AUTO_INCREMENT,
  `actionsCategories_name` varchar(500) NOT NULL,
  `actionsCategories_order` int DEFAULT '0',
  PRIMARY KEY (`actionsCategories_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actionscategories`
--

LOCK TABLES `actionscategories` WRITE;
/*!40000 ALTER TABLE `actionscategories` DISABLE KEYS */;
INSERT INTO `actionscategories` VALUES (1,'User Management',0),(2,'Permissions Management',1),(3,'General sys admin',2),(4,'Instances',3),(5,'Assets',4);
/*!40000 ALTER TABLE `actionscategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assetcategories`
--

DROP TABLE IF EXISTS `assetcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assetcategories` (
  `assetCategories_id` int NOT NULL AUTO_INCREMENT,
  `assetCategories_name` varchar(200) NOT NULL,
  `assetCategories_fontAwesome` varchar(100) DEFAULT NULL,
  `assetCategories_rank` int NOT NULL DEFAULT '999',
  `assetCategoriesGroups_id` int NOT NULL,
  `instances_id` int DEFAULT NULL,
  `assetCategories_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`assetCategories_id`),
  KEY `assetCategories_instances_instances_id_fk` (`instances_id`),
  KEY `assetCategories_Groups_id_fk` (`assetCategoriesGroups_id`),
  CONSTRAINT `assetCategories_Groups_id_fk` FOREIGN KEY (`assetCategoriesGroups_id`) REFERENCES `assetcategoriesgroups` (`assetCategoriesGroups_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assetCategories_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assetcategories`
--

LOCK TABLES `assetcategories` WRITE;
/*!40000 ALTER TABLE `assetcategories` DISABLE KEYS */;
INSERT INTO `assetcategories` VALUES (1,'Conventionals','far fa-lightbulb',11,1,NULL,0),(2,'Moving Lights','fas fa-robot',12,1,NULL,0),(3,'LEDs','fas fa-traffic-light',13,1,NULL,0),(4,'Colour Changers','fas fa-swatchbook',14,1,NULL,0),(5,'Accessories and Effects','fas fa-fire',18,1,NULL,0),(7,'Mixing Desks','fas fa-headphones',22,2,NULL,0),(8,'Amplifiers','fas fa-bullhorn',24,2,NULL,0),(9,'Microphones & DI','fas fa-microphone',21,2,NULL,0),(10,'Accessories','fas fa-headset',26,2,NULL,0),(11,'Speakers','fas fa-volume-up',23,2,NULL,0),(12,'Cables','fas fa-network-wired',70,999,NULL,0),(14,'Rigging','fas fa-balance-scale-left',51,4,NULL,0),(15,'Dimmers','fas fa-bolt',17,1,NULL,0),(16,'Computers','fas fa-desktop',40,5,NULL,0),(17,'Drapes, Curtains & Cloths','far fa-eye-slash',53,4,NULL,0),(19,'Accessories','fas fa-video',33,3,NULL,0),(20,'Outboard','fas fa-assistive-listening-systems',25,2,NULL,0),(21,'Vision Mixers and Media Servers','fas fa-server',32,3,NULL,0),(22,'Control','fas fa-microchip',15,1,NULL,0),(23,'Cases, Boxes and Trolleys','fas fa-truck-loading',60,999,NULL,0),(24,'Tools, Safety & Access','fas fa-wrench',52,4,NULL,0),(25,'Displays, Panels & Projectors','fas fa-tv',30,3,NULL,0),(26,'Accessories','far fa-keyboard',41,5,NULL,0),(27,'Radios','fas fa-satellite-dish',27,6,NULL,0),(28,'Networking','fas fa-ethernet',42,5,NULL,0),(29,'Mains Distribution','fas fa-plug',81,999,NULL,0),(30,'Systems','fas fa-headset',999,6,NULL,0),(31,'Cameras','fas fa-camera',31,3,NULL,0),(33,'Tablets & Mobile Phones','fas fa-mobile-alt',40,5,NULL,0);
/*!40000 ALTER TABLE `assetcategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assetcategoriesgroups`
--

DROP TABLE IF EXISTS `assetcategoriesgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assetcategoriesgroups` (
  `assetCategoriesGroups_id` int NOT NULL AUTO_INCREMENT,
  `assetCategoriesGroups_name` varchar(200) NOT NULL,
  `assetCategoriesGroups_fontAwesome` varchar(300) DEFAULT NULL,
  `assetCategoriesGroups_order` int NOT NULL DEFAULT '999',
  PRIMARY KEY (`assetCategoriesGroups_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assetcategoriesgroups`
--

LOCK TABLES `assetcategoriesgroups` WRITE;
/*!40000 ALTER TABLE `assetcategoriesgroups` DISABLE KEYS */;
INSERT INTO `assetcategoriesgroups` VALUES (1,'Lighting','far fa-lightbulb',1),(2,'Sound','fas fa-volume-up',2),(3,'Video','fas fa-tv',3),(4,'Rigging','fas fa-balance-scale-left',4),(5,'Computers & Networks','fas fa-server',6),(6,'Communication','fas fa-headset',5),(10,'Costume',NULL,10),(11,'Props',NULL,11),(12,'Scenery',NULL,12),(999,'Miscellaneous','fas fa-question',999);
/*!40000 ALTER TABLE `assetcategoriesgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assetgroups`
--

DROP TABLE IF EXISTS `assetgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assetgroups` (
  `assetGroups_id` int NOT NULL AUTO_INCREMENT,
  `assetGroups_name` varchar(200) NOT NULL,
  `assetGroups_description` text,
  `assetGroups_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `users_userid` int DEFAULT NULL,
  `instances_id` int NOT NULL,
  PRIMARY KEY (`assetGroups_id`),
  KEY `assetGroups_instances_instances_id_fk` (`instances_id`),
  KEY `assetGroups_users_users_userid_fk` (`users_userid`),
  CONSTRAINT `assetGroups_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assetGroups_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assetgroups`
--

LOCK TABLES `assetgroups` WRITE;
/*!40000 ALTER TABLE `assetgroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `assetgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `assets_id` int NOT NULL AUTO_INCREMENT,
  `assets_tag` varchar(200) DEFAULT NULL COMMENT 'The ID/Tag that the asset carries marked onto it',
  `assetTypes_id` int NOT NULL,
  `assets_notes` text,
  `instances_id` int NOT NULL,
  `asset_definableFields_1` varchar(200) DEFAULT NULL,
  `asset_definableFields_2` varchar(200) DEFAULT NULL,
  `asset_definableFields_3` varchar(200) DEFAULT NULL,
  `asset_definableFields_4` varchar(200) DEFAULT NULL,
  `asset_definableFields_5` varchar(200) DEFAULT NULL,
  `asset_definableFields_6` varchar(200) DEFAULT NULL,
  `asset_definableFields_7` varchar(200) DEFAULT NULL,
  `asset_definableFields_8` varchar(200) DEFAULT NULL,
  `asset_definableFields_9` varchar(200) DEFAULT NULL,
  `asset_definableFields_10` varchar(200) DEFAULT NULL,
  `assets_inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `assets_dayRate` int DEFAULT NULL,
  `assets_linkedTo` int DEFAULT NULL,
  `assets_weekRate` int DEFAULT NULL,
  `assets_value` int DEFAULT NULL,
  `assets_mass` decimal(55,5) DEFAULT NULL,
  `assets_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `assets_endDate` timestamp NULL DEFAULT NULL,
  `assets_archived` varchar(200) DEFAULT NULL,
  `assets_assetGroups` varchar(500) DEFAULT NULL,
  `assets_storageLocation` int DEFAULT NULL,
  `assets_showPublic` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`assets_id`),
  KEY `assets_assetTypes_assetTypes_id_fk` (`assetTypes_id`),
  KEY `assets_assets_assets_id_fk` (`assets_linkedTo`),
  KEY `assets_instances_instances_id_fk` (`instances_id`),
  KEY `assets_locations_locations_id_fk` (`assets_storageLocation`),
  CONSTRAINT `assets_assets_assets_id_fk` FOREIGN KEY (`assets_linkedTo`) REFERENCES `assets` (`assets_id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `assets_assetTypes_assetTypes_id_fk` FOREIGN KEY (`assetTypes_id`) REFERENCES `assettypes` (`assetTypes_id`),
  CONSTRAINT `assets_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assets_locations_locations_id_fk` FOREIGN KEY (`assets_storageLocation`) REFERENCES `locations` (`locations_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2409 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
INSERT INTO `assets` VALUES (2408,'A-1',19,'',1,'','','','','','','','','','','2021-05-12 14:53:53',NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,'',NULL,1);
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assetsassignments`
--

DROP TABLE IF EXISTS `assetsassignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assetsassignments` (
  `assetsAssignments_id` int NOT NULL AUTO_INCREMENT,
  `assets_id` int NOT NULL,
  `projects_id` int NOT NULL,
  `assetsAssignments_comment` varchar(500) DEFAULT NULL,
  `assetsAssignments_customPrice` int NOT NULL DEFAULT '0',
  `assetsAssignments_discount` float NOT NULL DEFAULT '0',
  `assetsAssignments_timestamp` timestamp NULL DEFAULT NULL,
  `assetsAssignments_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `assetsAssignmentsStatus_id` int DEFAULT NULL COMMENT '0 = None applicable\n10 = Pending pick\n20 = Picked\n30 = Prepping\n40 = Tested\n50 = Packed\n60 = Dispatched\n70 = Awaiting Check-in\n80 = Case opened\n90 = Unpacked\n100 = Tested\n110 = Stored',
  `assetsAssignments_linkedTo` int DEFAULT NULL,
  PRIMARY KEY (`assetsAssignments_id`),
  KEY `assetsAssignments_assets_assets_id_fk` (`assets_id`),
  KEY `assetsAssignments_projects_projects_id_fk` (`projects_id`),
  KEY `assetsAssignments_assetsAssignments_assetsAssignments_id_fk` (`assetsAssignments_linkedTo`),
  CONSTRAINT `assetsAssignments_assets_assets_id_fk` FOREIGN KEY (`assets_id`) REFERENCES `assets` (`assets_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assetsAssignments_assetsAssignments_assetsAssignments_id_fk` FOREIGN KEY (`assetsAssignments_linkedTo`) REFERENCES `assetsassignments` (`assetsAssignments_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assetsAssignments_projects_projects_id_fk` FOREIGN KEY (`projects_id`) REFERENCES `projects` (`projects_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10047 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assetsassignments`
--

LOCK TABLES `assetsassignments` WRITE;
/*!40000 ALTER TABLE `assetsassignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `assetsassignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assetsassignmentsstatus`
--

DROP TABLE IF EXISTS `assetsassignmentsstatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assetsassignmentsstatus` (
  `assetsAssignmentsStatus_id` int NOT NULL AUTO_INCREMENT,
  `instances_id` int NOT NULL,
  `assetsAssignmentsStatus_name` varchar(200) NOT NULL,
  `assetsAssignmentsStatus_order` int DEFAULT '999',
  PRIMARY KEY (`assetsAssignmentsStatus_id`),
  KEY `assetsAssignmentsStatus_instances_instances_id_fk` (`instances_id`),
  CONSTRAINT `assetsAssignmentsStatus_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assetsassignmentsstatus`
--

LOCK TABLES `assetsassignmentsstatus` WRITE;
/*!40000 ALTER TABLE `assetsassignmentsstatus` DISABLE KEYS */;
INSERT INTO `assetsassignmentsstatus` VALUES (78,1,'Pending pick',1),(79,1,'Picked',2),(80,1,'Prepping',3),(81,1,'Tested',4),(82,1,'Packed',5),(83,1,'Dispatched',6),(84,1,'Awaiting Check-in',7),(85,1,'Case opened',8),(86,1,'Unpacked',9),(87,1,'Tested',10),(88,1,'Stored',11);
/*!40000 ALTER TABLE `assetsassignmentsstatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assetsbarcodes`
--

DROP TABLE IF EXISTS `assetsbarcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assetsbarcodes` (
  `assetsBarcodes_id` int NOT NULL AUTO_INCREMENT,
  `assets_id` int DEFAULT NULL,
  `assetsBarcodes_value` varchar(500) NOT NULL,
  `assetsBarcodes_type` varchar(500) NOT NULL,
  `assetsBarcodes_notes` text,
  `assetsBarcodes_added` timestamp NOT NULL,
  `users_userid` int DEFAULT NULL COMMENT 'Userid that added it',
  `assetsBarcodes_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`assetsBarcodes_id`),
  KEY `assetsBarcodes_assets_assets_id_fk` (`assets_id`),
  KEY `assetsBarcodes_users_users_userid_fk` (`users_userid`),
  CONSTRAINT `assetsBarcodes_assets_assets_id_fk` FOREIGN KEY (`assets_id`) REFERENCES `assets` (`assets_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assetsBarcodes_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3209 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assetsbarcodes`
--

LOCK TABLES `assetsbarcodes` WRITE;
/*!40000 ALTER TABLE `assetsbarcodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `assetsbarcodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assetsbarcodesscans`
--

DROP TABLE IF EXISTS `assetsbarcodesscans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assetsbarcodesscans` (
  `assetsBarcodesScans_id` int NOT NULL AUTO_INCREMENT,
  `assetsBarcodes_id` int NOT NULL,
  `assetsBarcodesScans_timestamp` timestamp NOT NULL,
  `users_userid` int DEFAULT NULL,
  `locationsBarcodes_id` int DEFAULT NULL,
  `location_assets_id` int DEFAULT NULL,
  `assetsBarcodes_customLocation` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`assetsBarcodesScans_id`),
  KEY `assetsBarcodesScans_assetsBarcodes_assetsBarcodes_id_fk` (`assetsBarcodes_id`),
  KEY `assetsBarcodesScans_users_users_userid_fk` (`users_userid`),
  KEY `assetsBarcodesScans_locationsBarcodes_locationsBarcodes_id_fk` (`locationsBarcodes_id`),
  KEY `assetsBarcodesScans_assets_assets_id_fk` (`location_assets_id`),
  CONSTRAINT `assetsBarcodesScans_assets_assets_id_fk` FOREIGN KEY (`location_assets_id`) REFERENCES `assets` (`assets_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `assetsBarcodesScans_assetsBarcodes_assetsBarcodes_id_fk` FOREIGN KEY (`assetsBarcodes_id`) REFERENCES `assetsbarcodes` (`assetsBarcodes_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assetsBarcodesScans_locationsBarcodes_locationsBarcodes_id_fk` FOREIGN KEY (`locationsBarcodes_id`) REFERENCES `locationsbarcodes` (`locationsBarcodes_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assetsBarcodesScans_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=985 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assetsbarcodesscans`
--

LOCK TABLES `assetsbarcodesscans` WRITE;
/*!40000 ALTER TABLE `assetsbarcodesscans` DISABLE KEYS */;
/*!40000 ALTER TABLE `assetsbarcodesscans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assettypes`
--

DROP TABLE IF EXISTS `assettypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assettypes` (
  `assetTypes_id` int NOT NULL AUTO_INCREMENT,
  `assetTypes_name` varchar(500) NOT NULL,
  `assetCategories_id` int NOT NULL,
  `manufacturers_id` int NOT NULL,
  `instances_id` int DEFAULT NULL,
  `assetTypes_description` varchar(1000) DEFAULT NULL,
  `assetTypes_productLink` varchar(500) DEFAULT NULL,
  `assetTypes_definableFields` varchar(500) DEFAULT NULL,
  `assetTypes_mass` decimal(55,5) DEFAULT NULL,
  `assetTypes_inserted` timestamp NULL DEFAULT NULL,
  `assetTypes_dayRate` int NOT NULL,
  `assetTypes_weekRate` int NOT NULL,
  `assetTypes_value` int NOT NULL,
  PRIMARY KEY (`assetTypes_id`),
  KEY `assetTypes_assetCategories_assetCategories_id_fk` (`assetCategories_id`),
  KEY `assetTypes_manufacturers_manufacturers_id_fk` (`manufacturers_id`),
  KEY `assetTypes_instances_instances_id_fk` (`instances_id`),
  CONSTRAINT `assetTypes_assetCategories_assetCategories_id_fk` FOREIGN KEY (`assetCategories_id`) REFERENCES `assetcategories` (`assetCategories_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assetTypes_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assetTypes_manufacturers_manufacturers_id_fk` FOREIGN KEY (`manufacturers_id`) REFERENCES `manufacturers` (`manufacturers_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=782 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assettypes`
--

LOCK TABLES `assettypes` WRITE;
/*!40000 ALTER TABLE `assettypes` DISABLE KEYS */;
INSERT INTO `assettypes` VALUES (1,'Source Four Jr Zoom',1,88,NULL,'The power of Source Four in a compact and economical size! The smaller-scale Source Four jr uses the same technology that made the standard Source Four famous',NULL,'Current (w),Zoom Range',4.50000,NULL,900,2700,45000),(3,'Source Four Revolution',2,88,NULL,'High performance Source FourÂ® Zoomâ„¢ ellipsoidal equipped with motorized pan, tilt, zoom and on-board dimming\r\n\r\n750W, 77V QXL lamp has the proven characteristics of the 750W HPL with increased lumen output and patented quarter-turn insertion\r\nBuilt-in Pulse Width Modulated dimmer\r\nAuto-ranging 100V to 240V power supply and on-board dimming for global convenience\r\n24-frame integrated color scroller with convenient quick change gel-string cartridge\r\n540Âº Pan / 270Âº Tilt\r\n15Âº-35Âº Zoom\r\nAutomated crisp to soft focus\r\nInternal Media Frame for diffusion or other user media choice\r\nTwo plug-and-play module bays for optional beam control modules\r\nRugged die-cast aluminum construction\r\nSuitable for hanging in any orientation\r\n',NULL,',,,,,,,,,',33.59400,NULL,16000,48000,800000),(4,'ETCnomad USB Dongle - Licence Key',22,88,NULL,'Computer-based lighting control software, USB key allows output from Eos, Cobalt, or Hog 4 PC software on your computer\r\nOutput DMX via Ethernet (sACN/Net3/ArtNet), or optional Gadget USB to DMX Interface\r\nWorks with Programming Wings, Fader Wings (Universal Wings are PC only), and OSC Controllers\r\nSupports iRFR, aRFR, Net3 Show Control and I/O Gateways\r\nUp to three fader wings may be used',NULL,'Universes',0.10000,NULL,900,2700,45000),(16,'lxkey for Eos',22,354,NULL,'Keyboard designed for use with the ETCnomad lighting platform','http://lxkey.co.uk',',,,,,,,,,',1.10000,NULL,670,2009,33480),(17,'SM58',9,337,NULL,'Dynamic Vocal Microphone','https://www.shure.com/products/microphones/sm58',',,,,,,,,,',0.29800,NULL,180,540,9000),(18,'S945',9,355,NULL,'Dymanic Vocal Microphone','https://www.studiospares.com/Microphones/Mics-Vocalist/Studiospares-S945-Dynamic-Mic_449630.htm',',,,,,,,,,',0.50000,NULL,40,120,1999),(19,'3pin XLR Cable',12,1,NULL,'',NULL,'Length (m),,,,,,,,,',NULL,NULL,0,0,0),(20,'Boom Microphone Stand',10,384,NULL,'','https://www.amazon.co.uk/gp/product/B005I57GMM/ref=ppx_yo_dt_b_asin_title_o00_s00?ie=UTF8&psc=1','Style,,,,,,,,,',2.16000,NULL,40,120,2000),(21,'Gadget II',22,88,NULL,'Portable USB to five-pin XLR interface',NULL,',,,,,,,,,',0.22000,NULL,516,1548,25800),(22,'DBR10',11,336,NULL,'Active Full-Range Speaker',NULL,',,,,,,,,,',10.50000,NULL,666,1997,33286),(23,'DBR15',11,336,NULL,'Active Full-Range Speaker',NULL,',,,,,,,,,',19.30000,NULL,925,2776,46264),(24,'nanoKONTROL Studio',22,356,NULL,'Mobile USB Midi-Controller',NULL,',,,,,,,,,',0.45900,NULL,258,774,12900),(25,'Ultra-DI DI100',9,339,NULL,'DI Box','',',,,,,,,,,',0.65000,NULL,42,126,2100),(26,'Cable Trunk Road Trunk Flight Case (700mm)',23,357,NULL,'Hinged lid - 9mm black hexa board - 100mm castors.\r\nInt dims: w700mm x d400mm x h400mm\r\nExt dims: w725mm x d442mm x h575mm (inc castors)',NULL,',,,,,,,,,',23.00000,'2019-12-22 17:39:13',338,1014,16900),(27,'Cable Trunk Road Trunk Pro Flight Case (1070mm)',23,357,NULL,'Hinged lid - 9mm black laminate - 100mm castors.\r\nInt Dims: w1070mm x d515mm x h460mm\r\nExt Dims: w1105mm x d555mm x h650mm',NULL,',,,,,,,,,',40.00000,'2019-12-22 17:41:38',500,1500,24999),(28,'6 Drawer Production Flightcase',23,357,NULL,'Ext Dims: 500mm x 380mm x 780mm (inc castors)\r\nTop tray 480mm x 300mm x 80mm (inc lid)\r\n4 drawers at 415mm x 275mm x 50mm\r\n2 Drawers at 415mm x 275mm x 110mm',NULL,',,,,,,,,,',18.00000,'2019-12-22 17:43:08',300,900,15000),(54,'iMac',16,353,NULL,'',NULL,'OS,Year,Size,CPU,RAM,Disc,,,,',10.50000,'2020-01-11 12:55:41',3000,9000,150000),(55,'Extended USB Keyboard',26,353,NULL,'','',',,,,,,,,,',0.39000,'2020-01-12 14:44:58',300,800,12900),(76,'ThinkPad T440s',16,367,NULL,'',NULL,'OS,Year,CPU,RAM,Disk,Size,,,,',1.85000,'2020-02-05 13:00:33',4000,12000,199800),(77,'MacMini',16,353,NULL,'',NULL,'OS,Year,Size,CPU,RAM,Disc,,,,',1.30000,'2020-02-07 19:53:55',5000,15000,250000),(95,'CCTV Camera',19,1,NULL,NULL,'https://www.amazon.co.uk/gp/product/B01G368Z0Q/ref=ppx_yo_dt_b_search_asin_title?ie=UTF8&psc=1',',,,,,,,,,',NULL,'2020-02-07 20:51:46',400,1200,20000),(103,'RJ45 Patch (Cat5/5e/6/7) Cable',12,1,NULL,'',NULL,'Length (m),Category,Color,,,,,,,',0.10000,'2020-02-28 21:58:09',0,0,1000),(138,'Computer Mouse',26,388,NULL,'','https://amazon.co.uk/gp/product/B005EJH6RW/',',,,,,,,,,',0.10000,'2020-03-16 14:42:11',0,0,1000),(443,'Nunchuck Controller for ETC Consoles',22,335,NULL,'The Joystick Controller for ETC EOS combines a Wii-style Nunchuck Joystick (with two buttons for Next/Last) as well as a illuminated button which when held activates an extra slow mode for fine positioning. It connects automatically to ETC\'s EOS Windows 7 consoles ( Element / Element2 / ION / ION XE / Gio / Gio @5 / Ti) as well as to ETC Nomad on PCs/Macs over USB.\r\n\r\nUnder the hood the devices are Arduino based, in a 3d printed enclosure, and use OSC over USB to communicate with EOS.','https://www.etsy.com/uk/listing/903786872/joystick-controller-for-etc-eos',',,,,,,,,,',0.25000,'2020-11-21 22:30:43',0,0,6999),(447,'Fader Wing for EOS',22,335,NULL,NULL,NULL,',,,,,,,,,',NULL,'2020-12-04 16:43:10',0,0,13470);
/*!40000 ALTER TABLE `assettypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditlog`
--

DROP TABLE IF EXISTS `auditlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditlog` (
  `auditLog_id` int NOT NULL AUTO_INCREMENT,
  `auditLog_actionType` varchar(500) DEFAULT NULL,
  `auditLog_actionTable` varchar(500) DEFAULT NULL,
  `auditLog_actionData` longtext,
  `auditLog_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `users_userid` int DEFAULT NULL,
  `auditLog_actionUserid` int DEFAULT NULL,
  `projects_id` int DEFAULT NULL,
  `auditLog_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `auditLog_targetID` int DEFAULT NULL,
  PRIMARY KEY (`auditLog_id`),
  KEY `auditLog_users_users_userid_fk` (`users_userid`),
  KEY `auditLog_users_users_userid_fk_2` (`auditLog_actionUserid`),
  CONSTRAINT `auditLog_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `auditLog_users_users_userid_fk_2` FOREIGN KEY (`auditLog_actionUserid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditlog`
--

LOCK TABLES `auditlog` WRITE;
/*!40000 ALTER TABLE `auditlog` DISABLE KEYS */;
INSERT INTO `auditlog` VALUES (1,'INSERT','users','{\"users_email\":\"test@example.com\",\"users_username\":\"username\",\"users_name1\":\"UserF\",\"users_name2\":\"UserL\",\"users_salty1\":\"8smqAFD9\",\"users_salty2\":\"uOhfrOCW\",\"users_hash\":\"sha256\",\"users_password\":\"fa5a51baef12914c7f2e0e1176a030bf086d26edae298c25d5f84c90bc72ecd7\"}','2021-05-12 13:46:06',NULL,1,NULL,0,NULL);
/*!40000 ALTER TABLE `auditlog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `authtokens`
--

DROP TABLE IF EXISTS `authtokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `authtokens` (
  `authTokens_id` int NOT NULL AUTO_INCREMENT,
  `authTokens_token` varchar(500) NOT NULL,
  `authTokens_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `authTokens_ipAddress` varchar(500) DEFAULT NULL,
  `users_userid` int NOT NULL,
  `authTokens_valid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 for true. 0 for false',
  `authTokens_adminId` int DEFAULT NULL,
  `authTokens_deviceType` varchar(1000) NOT NULL,
  PRIMARY KEY (`authTokens_id`),
  UNIQUE KEY `token` (`authTokens_token`),
  KEY `authTokens_users_users_userid_fk` (`users_userid`),
  KEY `authTokens_users_users_userid_fk_2` (`authTokens_adminId`),
  CONSTRAINT `authTokens_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`),
  CONSTRAINT `authTokens_users_users_userid_fk_2` FOREIGN KEY (`authTokens_adminId`) REFERENCES `users` (`users_userid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authtokens`
--

LOCK TABLES `authtokens` WRITE;
/*!40000 ALTER TABLE `authtokens` DISABLE KEYS */;
INSERT INTO `authtokens` VALUES (3,'ddca93ace82b33d1d84b853f4a3fc1c4','2021-05-12 13:47:14','192.168.1.143',1,0,NULL,'Web'),(4,'81014458172b9992ecfd48a858088859','2021-05-12 13:53:35','192.168.1.143',1,1,NULL,'Web');
/*!40000 ALTER TABLE `authtokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `clients_id` int NOT NULL AUTO_INCREMENT,
  `clients_name` varchar(500) NOT NULL,
  `instances_id` int NOT NULL,
  `clients_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `clients_website` varchar(500) DEFAULT NULL,
  `clients_email` varchar(500) DEFAULT NULL,
  `clients_notes` text,
  `clients_address` varchar(500) DEFAULT NULL,
  `clients_phone` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`clients_id`),
  KEY `clients_instances_instances_id_fk` (`instances_id`),
  CONSTRAINT `clients_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cmspages`
--

DROP TABLE IF EXISTS `cmspages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmspages` (
  `cmsPages_id` int NOT NULL AUTO_INCREMENT,
  `instances_id` int NOT NULL,
  `cmsPages_showNav` tinyint(1) NOT NULL DEFAULT '0',
  `cmsPages_showPublic` tinyint(1) NOT NULL DEFAULT '0',
  `cmsPages_showPublicNav` tinyint(1) NOT NULL DEFAULT '1',
  `cmsPages_visibleToGroups` varchar(1000) DEFAULT NULL,
  `cmsPages_navOrder` int NOT NULL DEFAULT '999',
  `cmsPages_fontAwesome` varchar(500) DEFAULT NULL,
  `cmsPages_name` varchar(500) NOT NULL,
  `cmsPages_description` text,
  `cmsPages_archived` tinyint(1) NOT NULL DEFAULT '0',
  `cmsPages_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `cmsPages_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cmsPages_subOf` int DEFAULT NULL,
  PRIMARY KEY (`cmsPages_id`),
  KEY `cmsPages_instances_instances_id_fk` (`instances_id`),
  KEY `cmsPages_cmsPages_cmsPages_id_fk` (`cmsPages_subOf`),
  CONSTRAINT `cmsPages_cmsPages_cmsPages_id_fk` FOREIGN KEY (`cmsPages_subOf`) REFERENCES `cmspages` (`cmsPages_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `cmsPages_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cmspages`
--

LOCK TABLES `cmspages` WRITE;
/*!40000 ALTER TABLE `cmspages` DISABLE KEYS */;
/*!40000 ALTER TABLE `cmspages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cmspagesdrafts`
--

DROP TABLE IF EXISTS `cmspagesdrafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmspagesdrafts` (
  `cmsPagesDrafts_id` int NOT NULL AUTO_INCREMENT,
  `cmsPages_id` int NOT NULL,
  `users_userid` int DEFAULT NULL,
  `cmsPagesDrafts_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cmsPagesDrafts_data` json DEFAULT NULL,
  `cmsPagesDrafts_changelog` text,
  `cmsPagesDrafts_revisionID` int NOT NULL,
  PRIMARY KEY (`cmsPagesDrafts_id`),
  KEY `cmsPagesDrafts_cmsPages_cmsPages_id_fk` (`cmsPages_id`),
  KEY `cmsPagesDrafts_users_users_userid_fk` (`users_userid`),
  KEY `cmsPagesDrafts_cmsPagesDrafts_timestamp_index` (`cmsPagesDrafts_timestamp` DESC),
  CONSTRAINT `cmsPagesDrafts_cmsPages_cmsPages_id_fk` FOREIGN KEY (`cmsPages_id`) REFERENCES `cmspages` (`cmsPages_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cmsPagesDrafts_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cmspagesdrafts`
--

LOCK TABLES `cmspagesdrafts` WRITE;
/*!40000 ALTER TABLE `cmspagesdrafts` DISABLE KEYS */;
/*!40000 ALTER TABLE `cmspagesdrafts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cmspagesviews`
--

DROP TABLE IF EXISTS `cmspagesviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cmspagesviews` (
  `cmsPagesViews_id` int NOT NULL AUTO_INCREMENT,
  `cmsPages_id` int NOT NULL,
  `cmsPagesViews_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `users_userid` int DEFAULT NULL,
  `cmsPages_type` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cmsPagesViews_id`),
  KEY `cmsPagesViews_cmsPages_cmsPages_id_fk` (`cmsPages_id`),
  KEY `cmsPagesViews_users_users_userid_fk` (`users_userid`),
  KEY `cmsPagesViews_cmsPagesViews_timestamp_index` (`cmsPagesViews_timestamp`),
  CONSTRAINT `cmsPagesViews_cmsPages_cmsPages_id_fk` FOREIGN KEY (`cmsPages_id`) REFERENCES `cmspages` (`cmsPages_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `cmsPagesViews_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=258 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cmspagesviews`
--

LOCK TABLES `cmspagesviews` WRITE;
/*!40000 ALTER TABLE `cmspagesviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `cmspagesviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crewassignments`
--

DROP TABLE IF EXISTS `crewassignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crewassignments` (
  `crewAssignments_id` int NOT NULL AUTO_INCREMENT,
  `users_userid` int DEFAULT NULL,
  `projects_id` int NOT NULL,
  `crewAssignments_personName` varchar(500) DEFAULT NULL,
  `crewAssignments_role` varchar(500) NOT NULL,
  `crewAssignments_comment` varchar(500) DEFAULT NULL,
  `crewAssignments_deleted` tinyint(1) DEFAULT '0',
  `crewAssignments_rank` int DEFAULT '99',
  PRIMARY KEY (`crewAssignments_id`),
  KEY `crewAssignments_projects_projects_id_fk` (`projects_id`),
  KEY `crewAssignments_users_users_userid_fk` (`users_userid`),
  CONSTRAINT `crewAssignments_projects_projects_id_fk` FOREIGN KEY (`projects_id`) REFERENCES `projects` (`projects_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `crewAssignments_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crewassignments`
--

LOCK TABLES `crewassignments` WRITE;
/*!40000 ALTER TABLE `crewassignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `crewassignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailsent`
--

DROP TABLE IF EXISTS `emailsent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emailsent` (
  `emailSent_id` int NOT NULL AUTO_INCREMENT,
  `users_userid` int NOT NULL,
  `emailSent_html` longtext NOT NULL,
  `emailSent_subject` varchar(255) NOT NULL,
  `emailSent_sent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `emailSent_fromEmail` varchar(200) NOT NULL,
  `emailSent_fromName` varchar(200) NOT NULL,
  `emailSent_toName` varchar(200) NOT NULL,
  `emailSent_toEmail` varchar(200) NOT NULL,
  PRIMARY KEY (`emailSent_id`),
  KEY `emailSent_users_users_userid_fk` (`users_userid`),
  CONSTRAINT `emailSent_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailsent`
--

LOCK TABLES `emailsent` WRITE;
/*!40000 ALTER TABLE `emailsent` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailsent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailverificationcodes`
--

DROP TABLE IF EXISTS `emailverificationcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emailverificationcodes` (
  `emailVerificationCodes_id` int NOT NULL AUTO_INCREMENT,
  `emailVerificationCodes_code` varchar(1000) NOT NULL,
  `emailVerificationCodes_used` tinyint(1) NOT NULL DEFAULT '0',
  `emailVerificationCodes_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `emailVerificationCodes_valid` int NOT NULL DEFAULT '1',
  `users_userid` int NOT NULL,
  PRIMARY KEY (`emailVerificationCodes_id`),
  KEY `emailVerificationCodes_users_users_userid_fk` (`users_userid`),
  CONSTRAINT `emailVerificationCodes_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailverificationcodes`
--

LOCK TABLES `emailverificationcodes` WRITE;
/*!40000 ALTER TABLE `emailverificationcodes` DISABLE KEYS */;
INSERT INTO `emailverificationcodes` VALUES (1,'e7d5c259426f3b9cceccae66141d8db21620830766',0,'2021-05-12 13:46:06',1,1);
/*!40000 ALTER TABLE `emailverificationcodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instanceactions`
--

DROP TABLE IF EXISTS `instanceactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instanceactions` (
  `instanceActions_id` int NOT NULL AUTO_INCREMENT,
  `instanceActions_name` varchar(255) NOT NULL,
  `instanceActionsCategories_id` int NOT NULL,
  `instanceActions_dependent` varchar(200) DEFAULT NULL,
  `instanceActions_incompatible` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`instanceActions_id`),
  KEY `categories_fk` (`instanceActionsCategories_id`),
  CONSTRAINT `categories_fk` FOREIGN KEY (`instanceActionsCategories_id`) REFERENCES `instanceactionscategories` (`instanceActionsCategories_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instanceactions`
--

LOCK TABLES `instanceactions` WRITE;
/*!40000 ALTER TABLE `instanceactions` DISABLE KEYS */;
INSERT INTO `instanceactions` VALUES (2,'Access a list of users',1,NULL,NULL),(3,'Add a user to a business by EMail',1,'2\r\n',NULL),(5,'Remove a user from a business',1,'2',NULL),(6,'Change the role of a user in a business',1,'2',NULL),(11,'Access list of roles and their permissions',2,NULL,NULL),(12,'Edit roles permissions [SUPER ADMIN]',2,NULL,NULL),(13,'Change a user\'s permissions',2,'5',NULL),(14,'Set a user\'s thumbnail',1,'5',NULL),(15,'Delete a user',1,'2',NULL),(16,'Add new roles',2,'11',NULL),(17,'Create new Asset',3,NULL,NULL),(18,'Create new Asset Type',3,'17',NULL),(19,'Delete Asset',3,NULL,NULL),(20,'View Projects',4,NULL,NULL),(21,'Create Project',4,'20,104,23',NULL),(22,'Change Project Client',4,'20',NULL),(23,'Change Project Lead',4,'20',NULL),(24,'Change Project Description',4,'20',''),(25,'Archive Project',4,'20',NULL),(26,'Delete Project',4,'20',NULL),(27,'Change Project Dates',4,'20',NULL),(28,'Change Project Name',4,'20',NULL),(29,'Change Project Status',4,'20',NULL),(30,'Change Project Address',4,'20',NULL),(31,'Assign/unassign asset to Project',4,'20',NULL),(32,'Assign all assets to Project',4,'31',NULL),(33,'View Project Payments',5,'20',NULL),(34,'Add new Project Payment',5,'33',NULL),(35,'Delete a Project Payment',5,'33',NULL),(36,'View Clients List',6,NULL,NULL),(37,'Add new Client',6,'36\r\n',NULL),(38,'Add new Manufacturer',3,NULL,NULL),(39,'Edit Client',6,'36',NULL),(40,'View Ledger',5,NULL,NULL),(41,'Edit asset assignment comment',4,NULL,NULL),(42,'Edit asset assignment custom price',5,NULL,NULL),(43,'Edit asset assignment discount',5,NULL,NULL),(44,'Edit Project Notes',4,'20',NULL),(45,'Add Project Notes',4,'44',NULL),(46,'Change Project Invoice Notes',4,'20',NULL),(47,'View Project Crew',5,NULL,NULL),(48,'Add Crew to Project',5,'47',NULL),(49,'Delete Crew Assignment',5,'47',''),(50,'Email Project Crew',5,'47',NULL),(51,'Edit Crew Ranks',5,'47',NULL),(52,'View User details',1,NULL,NULL),(53,'Change the assignment status for an asset (e.g. mark as packed)',5,NULL,NULL),(54,'View Asset Type File Attachments',7,NULL,NULL),(55,'Upload Asset Type File Attachments',7,'54',NULL),(56,'Re-name a file',7,NULL,NULL),(57,'Delete a file',7,NULL,NULL),(58,'Edit Asset Type',3,NULL,NULL),(59,'Edit Asset',3,NULL,NULL),(61,'View Asset File Attachments',7,NULL,NULL),(62,'Upload Asset File Attachments',7,NULL,NULL),(63,'Access Maintenance',8,NULL,NULL),(67,'Change job due date',8,'63',NULL),(68,'Change user assigned to job',8,'63',NULL),(69,'Edit users tagged in job',8,'63',NULL),(70,'Edit Job Name',8,'63',NULL),(71,'Add message to job',8,'63',NULL),(72,'Delete Job\r\n',8,'63',NULL),(73,'Change job status',8,'63',NULL),(74,'Add Assets to Job',8,'63',NULL),(75,'Remove Assets from Job',8,'63',NULL),(76,'Upload files to Job',8,'63,71',NULL),(77,'Change job priority',8,'63',NULL),(78,'Make job flag against assets',8,NULL,NULL),(79,'Make job block asset assignments',8,NULL,NULL),(80,'View business stats',9,NULL,NULL),(81,'View business settings page',9,NULL,NULL),(82,'Edit Asset Overrides',3,'59',NULL),(83,'Edit business settings',9,'81',NULL),(84,'View Asset Barcodes',3,NULL,NULL),(85,'Scan Barcodes in the App',10,NULL,NULL),(86,'Delete Asset Barcodes',3,'84',NULL),(87,'View a list of locations',11,NULL,NULL),(88,'Associate any unassociated barcode with an asset',10,NULL,NULL),(89,'View a list of custom categories',9,'',NULL),(90,'Add a new custom category',9,'89,91',NULL),(91,'Edit a custom category',9,'89',NULL),(92,'Delete a custom category',9,'89',NULL),(93,'Create new Group',12,NULL,NULL),(94,'Edit an existing Group',12,NULL,NULL),(95,'Delete a Group',12,NULL,NULL),(96,'Add/Remove group members',12,NULL,NULL),(97,'Archive Asset',3,NULL,NULL),(98,'Add a new location',11,'87',''),(99,'Edit a location',11,'87',NULL),(100,'View Location File Attachments',7,'87',NULL),(101,'Upload Location File Attachments',7,'100',NULL),(102,'Upload Project Files',7,NULL,NULL),(103,'View location barcodes',11,'87',NULL),(104,'Change Project Type',13,'',NULL),(105,'View list of Project Types',13,NULL,NULL),(106,'Add new Project Type',13,'105',NULL),(107,'Edit Project Type',13,'105',NULL),(108,'Delete Project Type',13,'107,105',NULL),(109,'View list of Signup Codes',1,NULL,NULL),(110,'Add new Signup Code',1,'109',NULL),(111,'Edit Signup Code',1,'109',NULL),(112,'Delete Signup Code',1,'109,112',NULL),(113,'Access Training',14,NULL,NULL),(114,'View draft training modules',14,'113',NULL),(115,'Add Training module',14,'116',NULL),(116,'Edit Training modules',14,'114',NULL),(117,'View a list of users that have completed a training module',14,'113,2',NULL),(118,'Archive a user',1,'2',NULL),(119,'Certify a user\'s training',14,'117',NULL),(120,'Revoke a user\'s training',14,'119',NULL),(121,'View Payment File Attachments',5,'40',NULL),(122,'Upload Payment File Attachments',5,'40',NULL),(123,'Manage Crew Recruitment for a Project',5,'47',NULL),(124,'View & Apply for Crew Roles',4,'20',NULL),(125,'Manage CMS Pages',15,NULL,NULL),(126,'Edit any CMS Pages',15,'125',NULL);
/*!40000 ALTER TABLE `instanceactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instanceactionscategories`
--

DROP TABLE IF EXISTS `instanceactionscategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instanceactionscategories` (
  `instanceActionsCategories_id` int NOT NULL AUTO_INCREMENT,
  `instanceActionsCategories_name` varchar(255) NOT NULL,
  `instanceActionsCategories_order` int NOT NULL DEFAULT '999',
  PRIMARY KEY (`instanceActionsCategories_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instanceactionscategories`
--

LOCK TABLES `instanceactionscategories` WRITE;
/*!40000 ALTER TABLE `instanceactionscategories` DISABLE KEYS */;
INSERT INTO `instanceactionscategories` VALUES (1,'User Management',110),(2,'Permissions Management',120),(3,'Assets',10),(4,'Projects',30),(5,'Finance',40),(6,'Clients',20),(7,'Files',50),(8,'Maintenance',60),(9,'Business',70),(10,'App',100),(11,'Locations',80),(12,'Groups',90),(13,'Project Types',31),(14,'Training',75),(15,'CMS',71);
/*!40000 ALTER TABLE `instanceactionscategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instancepositions`
--

DROP TABLE IF EXISTS `instancepositions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instancepositions` (
  `instancePositions_id` int NOT NULL AUTO_INCREMENT,
  `instances_id` int NOT NULL,
  `instancePositions_displayName` varchar(500) NOT NULL,
  `instancePositions_rank` int NOT NULL DEFAULT '999',
  `instancePositions_actions` varchar(5000) DEFAULT NULL,
  `instancePositions_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`instancePositions_id`),
  KEY `instancePositions_instances_instances_id_fk` (`instances_id`),
  CONSTRAINT `instancePositions_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instancepositions`
--

LOCK TABLES `instancepositions` WRITE;
/*!40000 ALTER TABLE `instancepositions` DISABLE KEYS */;
INSERT INTO `instancepositions` VALUES (24,1,'Administrator',1,'1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173,174,175,176,177,178,179,180,181,182,183,184,185,186,187,188,189,190,191,192,193,194,195,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230,231,232,233,234,235,236,237,238,239,240,241,242,243,244,245,246,247,248,249,250,251,252,253,254,255,256,257,258,259,260,261,262,263,264,265,266,267,268,269,270,271,272,273,274,275,276,277,278,279,280,281,282,283,284,285,286,287,288,289,290,291,292,293,294,295,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,317,318,319,320,321,322,323,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338,339,340,341,342,343,344,345,346,347,348,349,350,351,352,353,354,355,356,357,358,359,360,361,362,363,364,365,366,367,368,369,370,371,372,373,374,375,376,377,378,379,380,381,382,383,384,385,386,387,388,389,390,391,392,393,394,395,396,397,398,399,400,401,402,403,404,405,406,407,408,409,410,411,412,413,414,415,416,417,418,419,420,421,422,423,424,425,426,427,428,429,430,431,432,433,434,435,436,437,438,439,440,441,442,443,444,445,446,447,448,449,450,451,452,453,454,455,456,457,458,459,460,461,462,463,464,465,466,467,468,469,470,471,472,473,474,475,476,477,478,479,480,481,482,483,484,485,486,487,488,489,490,491,492,493,494,495,496,497,498,499,500,501,502,503,504,505,506,507,508,509,510,511,512,513,514,515,516,517,518,519,520,521,522,523,524,525,526,527,528,529,530,531,532,533,534,535,536,537,538,539,540,541,542,543,544,545,546,547,548,549,550,551,552,553,554,555,556,557,558,559,560,561,562,563,564,565,566,567,568,569,570,571,572,573,574,575,576,577,578,579,580,581,582,583,584,585,586,587,588,589,590,591,592,593,594,595,596,597,598,599,600,601,602,603,604,605,606,607,608,609,610,611,612,613,614,615,616,617,618,619,620,621,622,623,624,625,626,627,628,629,630,631,632,633,634,635,636,637,638,639,640,641,642,643,644,645,646,647,648,649,650,651,652,653,654,655,656,657,658,659,660,661,662,663,664,665,666,667,668,669,670,671,672,673,674,675,676,677,678,679,680,681,682,683,684,685,686,687,688,689,690,691,692,693,694,695,696,697,698,699,700,701,702,703,704,705,706,707,708,709,710,711,712,713,714,715,716,717,718,719,720,721,722,723,724,725,726,727,728,729,730,731,732,733,734,735,736,737,738,739,740,741,742,743,744,745,746,747,748,749,750,751,752,753,754,755,756,757,758,759,760,761,762,763,764,765,766,767,768,769,770,771,772,773,774,775,776,777,778,779,780,781,782,783,784,785,786,787,788,789,790,791,792,793,794,795,796,797,798,799,800,801,802,803,804,805,806,807,808,809,810,811,812,813,814,815,816,817,818,819,820,821,822,823,824,825,826,827,828,829,830,831,832,833,834,835,836,837,838,839,840,841,842,843,844,845,846,847,848,849,850,851,852,853,854,855,856,857,858,859,860,861,862,863,864,865,866,867,868,869,870,871,872,873,874,875,876,877,878,879,880,881,882,883,884,885,886,887,888,889,890,891,892,893,894,895,896,897,898,899,900,901,902,903,904,905,906,907,908,909,910,911,912,913,914,915,916,917,918,919,920,921,922,923,924,925,926,927,928,929,930,931,932,933,934,935,936,937,938,939,940,941,942,943,944,945,946,947,948,949,950,951,952,953,954,955,956,957,958,959,960,961,962,963,964,965,966,967,968,969,970,971,972,973,974,975,976,977,978,979,980,981,982,983,984,985,986,987,988,989,990,991,992,993,994,995,996,997,998,999',0);
/*!40000 ALTER TABLE `instancepositions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instances`
--

DROP TABLE IF EXISTS `instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instances` (
  `instances_id` int NOT NULL AUTO_INCREMENT,
  `instances_name` varchar(200) NOT NULL,
  `instances_deleted` tinyint(1) DEFAULT '0',
  `instances_plan` varchar(500) DEFAULT NULL,
  `instances_address` varchar(1000) DEFAULT NULL,
  `instances_phone` varchar(200) DEFAULT NULL,
  `instances_email` varchar(200) DEFAULT NULL,
  `instances_website` varchar(200) DEFAULT NULL,
  `instances_weekStartDates` text,
  `instances_logo` int DEFAULT NULL,
  `instances_emailHeader` int DEFAULT NULL COMMENT 'A 1200x600 image to be the header on their emails',
  `instances_termsAndPayment` text,
  `instances_storageLimit` bigint NOT NULL DEFAULT '524288000' COMMENT 'In bytes - 500mb is default',
  `instances_config_linkedDefaultDiscount` double DEFAULT '100',
  `instances_config_currency` varchar(200) NOT NULL DEFAULT 'GBP',
  `instances_cableColours` text,
  `instances_publicConfig` text,
  PRIMARY KEY (`instances_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instances`
--

LOCK TABLES `instances` WRITE;
/*!40000 ALTER TABLE `instances` DISABLE KEYS */;
INSERT INTO `instances` VALUES (1,'Test Business',0,'trial','Cherrytreelane, Cherryville','','','',NULL,NULL,NULL,NULL,524288000,100,'GBP',NULL,NULL);
/*!40000 ALTER TABLE `instances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locations` (
  `locations_id` int NOT NULL AUTO_INCREMENT,
  `locations_name` varchar(500) NOT NULL,
  `clients_id` int DEFAULT NULL,
  `instances_id` int NOT NULL,
  `locations_address` text,
  `locations_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `locations_subOf` int DEFAULT NULL,
  `locations_notes` text,
  PRIMARY KEY (`locations_id`),
  KEY `locations_clients_clients_id_fk` (`clients_id`),
  KEY `locations_instances_instances_id_fk` (`instances_id`),
  KEY `locations_locations_locations_id_fk` (`locations_subOf`),
  CONSTRAINT `locations_clients_clients_id_fk` FOREIGN KEY (`clients_id`) REFERENCES `clients` (`clients_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `locations_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `locations_locations_locations_id_fk` FOREIGN KEY (`locations_subOf`) REFERENCES `locations` (`locations_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locationsbarcodes`
--

DROP TABLE IF EXISTS `locationsbarcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locationsbarcodes` (
  `locationsBarcodes_id` int NOT NULL AUTO_INCREMENT,
  `locations_id` int NOT NULL,
  `locationsBarcodes_value` varchar(500) NOT NULL,
  `locationsBarcodes_type` varchar(500) NOT NULL,
  `locationsBarcodes_notes` text,
  `locationsBarcodes_added` timestamp NOT NULL,
  `users_userid` int DEFAULT NULL COMMENT 'Userid that added it',
  `locationsBarcodes_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`locationsBarcodes_id`),
  KEY `locationsBarcodes_users_users_userid_fk` (`users_userid`),
  CONSTRAINT `locationsBarcodes_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locationsbarcodes`
--

LOCK TABLES `locationsbarcodes` WRITE;
/*!40000 ALTER TABLE `locationsbarcodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `locationsbarcodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loginattempts`
--

DROP TABLE IF EXISTS `loginattempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loginattempts` (
  `loginAttempts_id` int NOT NULL AUTO_INCREMENT,
  `loginAttempts_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `loginAttempts_textEntered` varchar(500) NOT NULL,
  `loginAttempts_ip` varchar(500) DEFAULT NULL,
  `loginAttempts_blocked` tinyint(1) NOT NULL,
  `loginAttempts_successful` tinyint(1) NOT NULL,
  PRIMARY KEY (`loginAttempts_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loginattempts`
--

LOCK TABLES `loginattempts` WRITE;
/*!40000 ALTER TABLE `loginattempts` DISABLE KEYS */;
INSERT INTO `loginattempts` VALUES (8,'2021-05-12 13:53:35','username','192.168.1.143',0,1);
/*!40000 ALTER TABLE `loginattempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenancejobs`
--

DROP TABLE IF EXISTS `maintenancejobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenancejobs` (
  `maintenanceJobs_id` int NOT NULL AUTO_INCREMENT,
  `maintenanceJobs_assets` varchar(500) NOT NULL,
  `maintenanceJobs_timestamp_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `maintenanceJobs_timestamp_due` timestamp NULL DEFAULT NULL,
  `maintenanceJobs_user_tagged` varchar(500) DEFAULT NULL,
  `maintenanceJobs_user_creator` int NOT NULL,
  `maintenanceJobs_user_assignedTo` int DEFAULT NULL,
  `maintenanceJobs_title` varchar(500) DEFAULT NULL,
  `maintenanceJobs_faultDescription` varchar(500) DEFAULT NULL,
  `maintenanceJobs_priority` tinyint NOT NULL DEFAULT '5' COMMENT '1 to 10',
  `instances_id` int NOT NULL,
  `maintenanceJobs_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `maintenanceJobsStatuses_id` int DEFAULT NULL,
  `maintenanceJobs_flagAssets` tinyint(1) NOT NULL DEFAULT '0',
  `maintenanceJobs_blockAssets` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`maintenanceJobs_id`),
  KEY `maintenanceJobs_users_users_userid_fk` (`maintenanceJobs_user_creator`),
  CONSTRAINT `maintenanceJobs_users_users_userid_fk` FOREIGN KEY (`maintenanceJobs_user_creator`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenancejobs`
--

LOCK TABLES `maintenancejobs` WRITE;
/*!40000 ALTER TABLE `maintenancejobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenancejobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenancejobsmessages`
--

DROP TABLE IF EXISTS `maintenancejobsmessages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenancejobsmessages` (
  `maintenanceJobsMessages_id` int NOT NULL AUTO_INCREMENT,
  `maintenanceJobs_id` int DEFAULT NULL,
  `maintenanceJobsMessages_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `users_userid` int NOT NULL,
  `maintenanceJobsMessages_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `maintenanceJobsMessages_text` text,
  `maintenanceJobsMessages_file` int DEFAULT NULL,
  PRIMARY KEY (`maintenanceJobsMessages_id`),
  KEY `maintenanceJobsMessages___files` (`maintenanceJobsMessages_file`),
  KEY `maintenanceJobsMessages_maintenanceJobs_maintenanceJobs_id_fk` (`maintenanceJobs_id`),
  CONSTRAINT `maintenanceJobsMessages___files` FOREIGN KEY (`maintenanceJobsMessages_file`) REFERENCES `s3files` (`s3files_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `maintenanceJobsMessages_maintenanceJobs_maintenanceJobs_id_fk` FOREIGN KEY (`maintenanceJobs_id`) REFERENCES `maintenancejobs` (`maintenanceJobs_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenancejobsmessages`
--

LOCK TABLES `maintenancejobsmessages` WRITE;
/*!40000 ALTER TABLE `maintenancejobsmessages` DISABLE KEYS */;
/*!40000 ALTER TABLE `maintenancejobsmessages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenancejobsstatuses`
--

DROP TABLE IF EXISTS `maintenancejobsstatuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenancejobsstatuses` (
  `maintenanceJobsStatuses_id` int NOT NULL AUTO_INCREMENT,
  `instances_id` int DEFAULT NULL,
  `maintenanceJobsStatuses_name` varchar(200) NOT NULL,
  `maintenanceJobsStatuses_order` tinyint(1) NOT NULL DEFAULT '99',
  `maintenanceJobsStatuses_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `maintenanceJobsStatuses_showJobInMainList` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`maintenanceJobsStatuses_id`),
  KEY `maintenanceJobsStatuses_instances_instances_id_fk` (`instances_id`),
  CONSTRAINT `maintenanceJobsStatuses_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenancejobsstatuses`
--

LOCK TABLES `maintenancejobsstatuses` WRITE;
/*!40000 ALTER TABLE `maintenancejobsstatuses` DISABLE KEYS */;
INSERT INTO `maintenancejobsstatuses` VALUES (1,NULL,'Received',1,0,1),(2,NULL,'Closed',99,0,0);
/*!40000 ALTER TABLE `maintenancejobsstatuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manufacturers`
--

DROP TABLE IF EXISTS `manufacturers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `manufacturers` (
  `manufacturers_id` int NOT NULL AUTO_INCREMENT,
  `manufacturers_name` varchar(500) NOT NULL,
  `instances_id` int DEFAULT NULL,
  `manufacturers_internalAdamRMSNote` varchar(500) DEFAULT NULL,
  `manufacturers_website` varchar(200) DEFAULT NULL,
  `manufacturers_notes` text,
  PRIMARY KEY (`manufacturers_id`),
  KEY `manufacturers_instances_instances_id_fk` (`instances_id`),
  CONSTRAINT `manufacturers_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=468 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manufacturers`
--

LOCK TABLES `manufacturers` WRITE;
/*!40000 ALTER TABLE `manufacturers` DISABLE KEYS */;
INSERT INTO `manufacturers` VALUES (1,'Unknown/Generic ',NULL,'Manual Add',NULL,'For when manufacturer isn\'t known'),(2,'Ablelite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2020',NULL,NULL),(3,'Abstract',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2021',NULL,NULL),(4,'Acclaim',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2022',NULL,NULL),(5,'Acme',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2023',NULL,NULL),(6,'AC Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2024',NULL,NULL),(7,'ADB',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2025',NULL,NULL),(8,'Aeson',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2026',NULL,NULL),(9,'Alkalite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2027',NULL,NULL),(10,'Altman',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2028',NULL,NULL),(11,'American DJ',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2029',NULL,NULL),(12,'American Pro',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2030',NULL,NULL),(13,'Amptown',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2031',NULL,NULL),(14,'Anolis',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2032',NULL,NULL),(15,'Antari',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2033',NULL,NULL),(16,'Ape Labs',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2034',NULL,NULL),(17,'Apollo',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2035',NULL,NULL),(18,'ArKaos',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2036',NULL,NULL),(19,'Arri',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2037',NULL,NULL),(20,'Artistic License',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2038',NULL,NULL),(21,'Art Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2039',NULL,NULL),(22,'Asymptech',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2040',NULL,NULL),(23,'Audio Visual Eng',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2041',NULL,NULL),(24,'Aurorae',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2042',NULL,NULL),(25,'Avolites',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2043',NULL,NULL),(26,'Ayrton',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2044',NULL,NULL),(27,'A & O Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2045',NULL,NULL),(28,'Bandit Lites',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2046',NULL,NULL),(29,'Barco',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2047',NULL,NULL),(30,'Barge Heights',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2048',NULL,NULL),(31,'BeamZ',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2049',NULL,NULL),(32,'Beam Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2050',NULL,NULL),(33,'Birket Eng',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2051',NULL,NULL),(34,'Black Tank',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2052',NULL,NULL),(35,'Blizzard Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2053',NULL,NULL),(36,'Botion Lighting Equipment',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2054',NULL,NULL),(37,'Briteq',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2055',NULL,NULL),(38,'Brite Shot',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2056',NULL,NULL),(39,'Brother Brother and Sons',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2057',NULL,NULL),(40,'Canara',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2058',NULL,NULL),(41,'Centerline Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2059',NULL,NULL),(42,'Chauvet',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2060',NULL,NULL),(43,'Christie',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2061',NULL,NULL),(44,'Chromaviso',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2062',NULL,NULL),(45,'Chroma Q',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2063',NULL,NULL),(46,'Chromlech',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2064',NULL,NULL),(47,'Cindy',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2065',NULL,NULL),(48,'Cineo Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2066',NULL,NULL),(49,'City Theatrical',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2067',NULL,NULL),(50,'CKC',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2068',NULL,NULL),(51,'Clay Paky',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2069',NULL,NULL),(52,'Coef',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2070',NULL,NULL),(53,'Coemar',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2071',NULL,NULL),(54,'ColorKey',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2072',NULL,NULL),(55,'ColorVerse',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2073',NULL,NULL),(56,'Color Imagin',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2074',NULL,NULL),(57,'Compulite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2075',NULL,NULL),(58,'Contest',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2076',NULL,NULL),(59,'Coolux',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2077',NULL,NULL),(60,'Creative Lighting Fixtures',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2078',NULL,NULL),(61,'Creative Lighting Solutions',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2079',NULL,NULL),(62,'Custom',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2080',NULL,NULL),(63,'CY Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2081',NULL,NULL),(64,'Daisy Lighting Equipment Factory',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2082',NULL,NULL),(65,'Darklight Precision Lighting Sys',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2083',NULL,NULL),(66,'Deliya',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2084',NULL,NULL),(67,'Derksen',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2085',NULL,NULL),(68,'DIALighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2086',NULL,NULL),(69,'Digital Sputnik Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2087',NULL,NULL),(70,'Diversi Tronics',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2088',NULL,NULL),(71,'Divine Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2089',NULL,NULL),(72,'DML',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2090',NULL,NULL),(73,'Dortron Showtec',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2091',NULL,NULL),(74,'Doug Fleenor Designs',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2092',NULL,NULL),(75,'DTS',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2093',NULL,NULL),(76,'Dune',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2094',NULL,NULL),(77,'Eagle Fai',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2095',NULL,NULL),(78,'Eddylight',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2096',NULL,NULL),(79,'Ehrgeiz',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2097',NULL,NULL),(80,'Elation',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2098',NULL,NULL),(81,'Elation Arch',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2099',NULL,NULL),(82,'Elektralite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2100',NULL,NULL),(83,'Elements',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2101',NULL,NULL),(84,'Element Labs',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2102',NULL,NULL),(85,'Eliminator',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2103',NULL,NULL),(86,'Elite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2104',NULL,NULL),(87,'elumen8',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2105',NULL,NULL),(88,'ETC',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2106','https://www.etcconnect.com',NULL),(89,'Eurolite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2107',NULL,NULL),(90,'Evolight',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2108',NULL,NULL),(91,'Expolite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2109',NULL,NULL),(92,'Fal',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2110',NULL,NULL),(93,'FiberLamp',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2111',NULL,NULL),(94,'Fiilex',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2112',NULL,NULL),(95,'Fine Art',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2113',NULL,NULL),(96,'Flash Butrym',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2114',NULL,NULL),(97,'Flix',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2115',NULL,NULL),(98,'Fort',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2116',NULL,NULL),(99,'Fountain People',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2117',NULL,NULL),(100,'Fritz N',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2118',NULL,NULL),(101,'Futurelight',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2119',NULL,NULL),(102,'G-Lec',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2120',NULL,NULL),(103,'G-Lites',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2121',NULL,NULL),(104,'GAM',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2122',NULL,NULL),(105,'Gekko Technology',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2123',NULL,NULL),(106,'Geni',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2124',NULL,NULL),(107,'Gerriets',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2125',NULL,NULL),(108,'Global Design Solutions (GDS)',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2126',NULL,NULL),(109,'GLP',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2127',NULL,NULL),(110,'Golden Sea',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2128',NULL,NULL),(111,'Grande Eng',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2129',NULL,NULL),(112,'Greenlight Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2130',NULL,NULL),(113,'Green Hippo',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2131',NULL,NULL),(114,'Griven',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2132',NULL,NULL),(115,'GRNLite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2133',NULL,NULL),(116,'GTech',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2134',NULL,NULL),(117,'G 13 Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2135',NULL,NULL),(118,'Haya Light Equipment',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2136',NULL,NULL),(119,'Hazebase',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2137',NULL,NULL),(120,'High End Systems',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2138',NULL,NULL),(121,'Howard Eaton Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2139',NULL,NULL),(122,'HQ Power',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2140',NULL,NULL),(123,'HTL',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2141',NULL,NULL),(124,'Hungaro flash',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2142',NULL,NULL),(125,'i-Pix',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2143',NULL,NULL),(126,'Idealight Industrial',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2144',NULL,NULL),(127,'Ignition',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2145',NULL,NULL),(128,'iLED',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2146',NULL,NULL),(129,'Illumivision',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2147',NULL,NULL),(130,'Iluminarc',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2148',NULL,NULL),(131,'ImageCue',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2149',NULL,NULL),(132,'Insight Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2150',NULL,NULL),(133,'interlite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2151',NULL,NULL),(134,'Involight',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2152',NULL,NULL),(135,'Irradiant',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2153',NULL,NULL),(136,'iSolutions',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2154',NULL,NULL),(137,'JB Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2155',NULL,NULL),(138,'JB Systems',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2156',NULL,NULL),(139,'K9 Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2157',NULL,NULL),(140,'Kam',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2158',NULL,NULL),(141,'Kino Flo',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2159',NULL,NULL),(142,'KLS Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2160',NULL,NULL),(143,'Lampo Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2161',NULL,NULL),(144,'LanBolight Technology',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2162',NULL,NULL),(145,'Lanling',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2163',NULL,NULL),(146,'Lanta Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2164',NULL,NULL),(147,'Laserworld',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2165',NULL,NULL),(148,'Laser Imagin',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2166',NULL,NULL),(149,'Laser UK',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2167',NULL,NULL),(150,'LDDE',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2168',NULL,NULL),(151,'LDR',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2169',NULL,NULL),(152,'Leader Light',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2170',NULL,NULL),(153,'Leddux',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2171',NULL,NULL),(154,'Le Maitre',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2172',NULL,NULL),(155,'Licht Technik',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2173',NULL,NULL),(156,'Lighting Innovation',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2174',NULL,NULL),(157,'Lighting Technology',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2175',NULL,NULL),(158,'Lightmaxx',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2176',NULL,NULL),(159,'Lightning Strikes',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2177',NULL,NULL),(160,'Lightronics',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2178',NULL,NULL),(161,'LightSky',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2179',NULL,NULL),(162,'Light Converse',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2180',NULL,NULL),(163,'Light Emotion',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2181',NULL,NULL),(164,'Light Graphix',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2182',NULL,NULL),(165,'Litecraft',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2183',NULL,NULL),(166,'Litecraft AP',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2184',NULL,NULL),(167,'Litepanels',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2185',NULL,NULL),(168,'Live Light',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2186',NULL,NULL),(169,'Logvinov',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2187',NULL,NULL),(170,'Longman',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2188',NULL,NULL),(171,'Look Solutions',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2189',NULL,NULL),(172,'Lumen Pulse',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2190',NULL,NULL),(173,'Lumisia',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2191',NULL,NULL),(174,'Lumi Pro',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2192',NULL,NULL),(175,'Lumonic Limited',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2193',NULL,NULL),(176,'Madrix',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2194',NULL,NULL),(177,'Martin',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2195',NULL,NULL),(178,'mbT Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2196',NULL,NULL),(179,'MDG',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2197',NULL,NULL),(180,'Megalite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2198',NULL,NULL),(181,'Megastage',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2199',NULL,NULL),(182,'Mega Diode',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2200',NULL,NULL),(183,'Meteor',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2201',NULL,NULL),(184,'Microh',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2202',NULL,NULL),(185,'Milford Ins',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2203',NULL,NULL),(186,'Mint forbers',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2204',NULL,NULL),(187,'Monoprice',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2205',NULL,NULL),(188,'Morpheus',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2206',NULL,NULL),(189,'Movie Fixtures',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2207',NULL,NULL),(190,'Movitec',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2208',NULL,NULL),(191,'MT Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2209',NULL,NULL),(192,'Multiform Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2210',NULL,NULL),(193,'Mushroom Lighting Technology',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2211',NULL,NULL),(194,'NAT',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2212',NULL,NULL),(195,'NeoNeon',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2213',NULL,NULL),(196,'Nicols',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2214',NULL,NULL),(197,'Nightsun',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2215',NULL,NULL),(198,'NJD Electronics',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2216',NULL,NULL),(199,'Novalight',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2217',NULL,NULL),(200,'NW Lighting FX',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2218',NULL,NULL),(201,'Ocean Optics',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2219',NULL,NULL),(202,'Omez Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2220',NULL,NULL),(203,'Omni Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2221',NULL,NULL),(204,'Omni Sistems',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2222',NULL,NULL),(205,'Optima',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2223',NULL,NULL),(206,'Osiris',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2224',NULL,NULL),(207,'Osram',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2225',NULL,NULL),(208,'Outsight',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2226',NULL,NULL),(209,'OXO Show Solutions',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2227',NULL,NULL),(210,'Panasonic Corporation',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2228',NULL,NULL),(211,'Panoramic Lasers',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2229',NULL,NULL),(212,'Pasef',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2230',NULL,NULL),(213,'PC Lights',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2231',NULL,NULL),(214,'Philips',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2232',NULL,NULL),(215,'Philips Color Kinetics',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2233',NULL,NULL),(216,'Photon Star',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2234',NULL,NULL),(217,'Picturall',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2235',NULL,NULL),(218,'PixelRange',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2236',NULL,NULL),(219,'PixMob',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2237',NULL,NULL),(220,'Precision Projection Systems',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2238',NULL,NULL),(221,'PRG',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2239',NULL,NULL),(222,'Prism Projection',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2240',NULL,NULL),(223,'Pro-SL',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2241',NULL,NULL),(224,'Proel',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2242',NULL,NULL),(225,'Prolight',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2243',NULL,NULL),(226,'Prolights',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2244',NULL,NULL),(227,'PR Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2245',NULL,NULL),(228,'PSL',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2246',NULL,NULL),(229,'Pulsar',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2247',NULL,NULL),(230,'Pulse',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2248',NULL,NULL),(231,'Qmaxz',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2249',NULL,NULL),(232,'QTX Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2250',NULL,NULL),(233,'Rainbow',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2251',NULL,NULL),(234,'Rave Laser',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2252',NULL,NULL),(235,'RC4',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2253',NULL,NULL),(236,'Red Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2254',NULL,NULL),(237,'Remote Controlled Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2255',NULL,NULL),(238,'Renewed Vision',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2256',NULL,NULL),(239,'Resolume',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2257',NULL,NULL),(240,'Rige Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2258',NULL,NULL),(241,'Robe',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2259',NULL,NULL),(242,'Robert Juliat',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2260',NULL,NULL),(243,'Roblon',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2261',NULL,NULL),(244,'Rosco',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2262',NULL,NULL),(245,'Rosebrand',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2263',NULL,NULL),(246,'SAMSC',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2264',NULL,NULL),(247,'Schnick Schnack Systems',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2265',NULL,NULL),(248,'Screen Monkey',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2266',NULL,NULL),(249,'Selador',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2267',NULL,NULL),(250,'Selecon',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2268',NULL,NULL),(251,'SGM',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2269',NULL,NULL),(252,'ShenZhen Becen',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2270',NULL,NULL),(253,'Shinp',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2271',NULL,NULL),(254,'Showco',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2272',NULL,NULL),(255,'Showled',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2273',NULL,NULL),(256,'Showline',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2274',NULL,NULL),(257,'Showtec',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2275',NULL,NULL),(258,'Show Series',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2276',NULL,NULL),(259,'Show Technology',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2277',NULL,NULL),(260,'Silver Star',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2278',NULL,NULL),(261,'Smoke Factory',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2279',NULL,NULL),(262,'Snowmasters Special Effects',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2280',NULL,NULL),(263,'Space Cannon',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2281',NULL,NULL),(264,'Spark Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2282',NULL,NULL),(265,'Sping Light',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2283',NULL,NULL),(266,'Spotlight',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2284',NULL,NULL),(267,'Stage Ape Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2285',NULL,NULL),(268,'Stage Line',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2286',NULL,NULL),(269,'Stage Tech',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2287',NULL,NULL),(270,'Stagg',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2288',NULL,NULL),(271,'StairVille',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2289',NULL,NULL),(272,'Starway',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2290',NULL,NULL),(273,'Stellar Labs',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2291',NULL,NULL),(274,'Strand',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2292',NULL,NULL),(275,'Studio Due',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2293',NULL,NULL),(276,'Suntech',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2294',NULL,NULL),(277,'Swefog',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2295',NULL,NULL),(278,'Swelite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2296',NULL,NULL),(279,'Syncrolite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2297',NULL,NULL),(280,'T8 Tech',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2298',NULL,NULL),(281,'Targetti',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2299',NULL,NULL),(282,'TAS',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2300',NULL,NULL),(283,'TBF Pyrotec',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2301',NULL,NULL),(284,'Technilux',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2302',NULL,NULL),(285,'Technylight',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2303',NULL,NULL),(286,'Teclumen',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2304',NULL,NULL),(287,'Terbly',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2305',NULL,NULL),(288,'TE Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2306',NULL,NULL),(289,'Theatrixx Tech',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2307',NULL,NULL),(290,'Thorn',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2308',NULL,NULL),(291,'TIR',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2309',NULL,NULL),(292,'TL Laser',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2310',NULL,NULL),(293,'TMB',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2311',NULL,NULL),(294,'Tokistar',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2312',NULL,NULL),(295,'Toplite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2313',NULL,NULL),(296,'TPR Enterprises',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2314',NULL,NULL),(297,'Transtun',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2315',NULL,NULL),(298,'Traxon Tech',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2316',NULL,NULL),(299,'Triangle Lights',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2317',NULL,NULL),(300,'Triton Blue',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2318',NULL,NULL),(301,'TyLED',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2319',NULL,NULL),(302,'Ultratec',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2320',NULL,NULL),(303,'Universal Fibre Optics',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2321',NULL,NULL),(304,'VariLite',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2322',NULL,NULL),(305,'Varytec',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2323',NULL,NULL),(306,'VAS Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2324',NULL,NULL),(307,'Velleman',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2325',NULL,NULL),(308,'Vello',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2326',NULL,NULL),(309,'Venue',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2327',NULL,NULL),(310,'Venue Effects Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2328',NULL,NULL),(311,'Versa-Light',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2329',NULL,NULL),(312,'Viking Stage Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2330',NULL,NULL),(313,'Vincent',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2331',NULL,NULL),(314,'Vision Light',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2332',NULL,NULL),(315,'Visual Effects',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2333',NULL,NULL),(316,'Visual Prod',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2334',NULL,NULL),(317,'Volkslaser',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2335',NULL,NULL),(318,'wdm lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2336',NULL,NULL),(319,'Wecan',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2337',NULL,NULL),(320,'White Light',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2338',NULL,NULL),(321,'Wiedamark',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2339',NULL,NULL),(322,'Wildfire',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2340',NULL,NULL),(323,'Wilgex',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2341',NULL,NULL),(324,'Work',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2342',NULL,NULL),(325,'Wybron',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2343',NULL,NULL),(326,'X-Laser',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2344',NULL,NULL),(327,'XAL',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2345',NULL,NULL),(328,'Xilver',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2346',NULL,NULL),(329,'Yaohai Electronics',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2347',NULL,NULL),(330,'Yellow River',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2348',NULL,NULL),(331,'Yorkville',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2349',NULL,NULL),(332,'Zap Technology',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2350',NULL,NULL),(333,'Zylight',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2351',NULL,NULL),(334,'0energy Lighting',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2352',NULL,NULL),(335,'Bithell Studios',NULL,NULL,'https://studios.jbithell.com/',NULL),(336,'Yamaha',NULL,'JBithell Manual Add','https://yamaha.com',NULL),(337,'Shure',NULL,'JBithell Manual Add','https://shure.com',NULL),(338,'Sennheiser',NULL,'JBithell Manual Add','https://sennheiser.com',NULL),(339,'Behringer',NULL,'JBithell Manual Add','https://www.behringer.com',NULL),(340,'Allen & Heath',NULL,'JBithell Manual Add','https://www.allen-heath.com',NULL),(341,'DiGiCo',NULL,'JBithell Manual Add','https://digico.biz',NULL),(342,'Midas',NULL,'JBithell Manual Add','https://www.midasgroup.co.uk',NULL),(343,'Meyer',NULL,'JBithell Manual Add','https://meyersound.com/',NULL),(344,'DPA',NULL,'JBithell Manual Add','https://www.dpamicrophones.com/',NULL),(345,'Clear-Com',NULL,'JBithell Manual Add','https://www.clearcom.com/',NULL),(346,'Beyerdynamic',NULL,'JBithell Manual Add','https://beyerdynamic.com/',NULL),(347,'Audio-Technica ',NULL,'JBithell Manual Add','https://www.audio-technica.com/',NULL),(348,'AKG',NULL,'JBithell Manual Add','https://akg.com/',NULL),(349,'Figure 53',NULL,'JBithell Manual Add','http://figure53.com/',NULL),(350,'KV2 Audio',NULL,'JBithell Manual Add','https://www.kv2audio.com/',NULL),(351,'d&b audiotechnik',NULL,'JBithell Manual Add','https://www.dbaudio.com/',NULL),(352,'AAdynTech',NULL,'Added by JBithell by copying ETC EOS Fixture Library November 2019',NULL,NULL),(353,'Apple',NULL,'JBithell Manual Add','https://apple.com',''),(354,'The Stage Group',NULL,'JBithell Manual Add','http://www.thestagegroup.co.uk',NULL),(355,'StudioSpares',NULL,'JBithell Manual Add',NULL,NULL),(356,'Korg',NULL,'JBithell Manual Add','\r\nhttps://www.korg.com/',NULL),(357,'Spider Cases',NULL,'JBithell Manual Add',NULL,NULL),(358,'Zero88',NULL,'JBithell Manual Add','https://zero88.com/',''),(359,'LIVA',NULL,NULL,NULL,NULL),(360,'CCT',NULL,NULL,NULL,NULL),(364,'Thomann',NULL,NULL,NULL,NULL),(365,'Global Truss',NULL,NULL,NULL,NULL),(366,'Millenium',NULL,'JBithell Manual Add','https://www.thomann.de/gb/millenium.html',NULL),(367,'Lenovo',NULL,NULL,NULL,NULL),(368,'Microsoft',NULL,NULL,NULL,NULL),(369,'TP Link',NULL,NULL,NULL,NULL),(370,'Pyle',NULL,NULL,NULL,NULL),(371,'Konig & Meyer',NULL,NULL,NULL,NULL),(372,'Seagate',NULL,NULL,NULL,NULL),(373,'Apeman',NULL,NULL,NULL,NULL),(374,'Binatone',NULL,NULL,NULL,NULL),(375,'JOBY',NULL,NULL,NULL,NULL),(377,'gnr',NULL,NULL,NULL,NULL),(378,'HP',NULL,NULL,NULL,NULL),(379,'Netgear',NULL,NULL,NULL,NULL),(380,'ViewSonic',NULL,NULL,NULL,NULL),(381,'JSP',NULL,NULL,NULL,NULL),(382,'Werner',NULL,NULL,NULL,NULL),(383,'Zarges',NULL,NULL,NULL,NULL),(384,'Tiger',NULL,NULL,NULL,NULL),(385,'LEDJ',NULL,NULL,NULL,NULL),(386,'StarTech',NULL,NULL,NULL,NULL),(387,'Magnusson',NULL,NULL,NULL,NULL),(388,'Amazon Basics',NULL,NULL,NULL,NULL),(389,'CiT',NULL,NULL,NULL,NULL),(390,'RS Pro',NULL,NULL,NULL,NULL),(391,'Canon ',NULL,NULL,NULL,NULL),(393,'Panasonic',NULL,NULL,NULL,NULL),(394,'BlackMagic',NULL,NULL,NULL,NULL),(395,'GoPro',NULL,NULL,NULL,NULL),(396,'Lowell',NULL,NULL,NULL,NULL),(397,'Sigma',NULL,NULL,NULL,NULL),(398,'Tamron',NULL,NULL,NULL,NULL),(399,'Decimator Design',NULL,NULL,NULL,NULL),(400,'RODE',NULL,NULL,NULL,NULL),(401,'Libec',NULL,NULL,NULL,NULL),(402,'Camlink',NULL,NULL,NULL,NULL),(403,'Manfrotto',NULL,NULL,NULL,NULL),(404,'Photon Beard',NULL,NULL,NULL,NULL),(405,'Rycote',NULL,NULL,NULL,NULL),(406,'Lastolite ',NULL,NULL,NULL,NULL),(407,'Teradek',NULL,NULL,NULL,NULL),(408,'Rhino',NULL,NULL,NULL,NULL),(409,'Sony',NULL,NULL,NULL,NULL),(410,'Sandisk',NULL,NULL,NULL,NULL),(412,'Yongnuo ',NULL,NULL,NULL,NULL),(413,'E-Image',NULL,NULL,NULL,NULL),(414,'Pro-Elec',NULL,NULL,NULL,NULL),(415,'Neewer ',NULL,NULL,NULL,NULL),(416,'Gator ',NULL,NULL,NULL,NULL),(417,'My Hair Dresser',NULL,NULL,'https://myhairdressers.com/',NULL),(418,'UGREEN',NULL,NULL,'https://www.ugreen.com/',NULL),(419,'Honeywell',NULL,NULL,NULL,NULL),(420,'OnePlus',NULL,NULL,NULL,NULL),(421,'Logitech',NULL,NULL,NULL,NULL),(422,'IKEA',NULL,NULL,NULL,NULL),(423,'Samsung',NULL,NULL,NULL,NULL),(424,'Aukey',NULL,NULL,NULL,NULL),(425,'Alcatel',NULL,NULL,NULL,NULL),(426,'Woox',NULL,NULL,NULL,NULL),(427,'Google',NULL,NULL,NULL,NULL),(428,'Plantronics',NULL,NULL,NULL,NULL),(429,'Braun',NULL,NULL,NULL,NULL),(430,'Sanyo',NULL,NULL,NULL,NULL),(431,'TECKNET',NULL,NULL,NULL,NULL),(432,'Portwest',NULL,NULL,NULL,NULL),(433,'APC',NULL,NULL,NULL,NULL),(434,'LogiLink',NULL,NULL,NULL,NULL),(435,'TECPRO',NULL,NULL,NULL,NULL),(436,'Superlux',NULL,NULL,NULL,NULL),(437,'Dell',NULL,NULL,NULL,NULL),(438,'Zoom',NULL,'YSTV Add',NULL,NULL),(440,'Swift',NULL,'YSTV Add',NULL,NULL),(441,'Headbox',NULL,'YSTV Add',NULL,NULL),(442,'Small rig',NULL,'YSTV Add',NULL,NULL),(443,'Came TV',NULL,'YSTV Add',NULL,NULL),(444,'Aperture',NULL,'YSTV Add',NULL,NULL),(445,'B&W',NULL,'YSTV Add',NULL,NULL),(446,'Pelican',NULL,'YSTV Add',NULL,NULL),(447,'Seasure',NULL,'YSTV Add',NULL,NULL),(448,'dBTechnologies',NULL,'Manual Add','https://www.dbtechnologies.com',NULL),(449,'RoadCrew',NULL,'JBithell Manual Add',NULL,NULL),(450,'Wireless Solution',NULL,'JBithell Manual Add',NULL,NULL),(451,'MacAllister',NULL,'JBithell Manual Add',NULL,NULL),(452,'Workzone',NULL,'JBithell Manual Add',NULL,NULL),(453,'Raspberry Pi',NULL,'JBithell Manual Add',NULL,NULL),(454,'Huion',NULL,'JBithell Manual Add',NULL,NULL),(455,'Anytronics',NULL,'JBithell Manual Add',NULL,NULL),(456,'J&C Joel',NULL,'JBithell Manual Add',NULL,NULL),(457,'Melba Swintex',NULL,'JBithell Manual Add','https://www.melbaswintex.co.uk',NULL),(458,'HKAudio',NULL,NULL,NULL,NULL),(459,'Logik',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `manufacturers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `modules_id` int NOT NULL AUTO_INCREMENT,
  `instances_id` int NOT NULL,
  `users_userid` int NOT NULL COMMENT '"Author"',
  `modules_name` varchar(500) NOT NULL,
  `modules_description` text,
  `modules_learningObjectives` text,
  `modules_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `modules_show` tinyint(1) NOT NULL DEFAULT '0',
  `modules_thumbnail` int DEFAULT NULL,
  `modules_type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`modules_id`),
  KEY `modules_instances_instances_id_fk` (`instances_id`),
  KEY `modules_users_users_userid_fk` (`users_userid`),
  KEY `modules_s3files_s3files_id_fk` (`modules_thumbnail`),
  CONSTRAINT `modules_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `modules_s3files_s3files_id_fk` FOREIGN KEY (`modules_thumbnail`) REFERENCES `s3files` (`s3files_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `modules_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modulessteps`
--

DROP TABLE IF EXISTS `modulessteps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modulessteps` (
  `modulesSteps_id` int NOT NULL AUTO_INCREMENT,
  `modules_id` int NOT NULL,
  `modulesSteps_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `modulesSteps_show` tinyint(1) NOT NULL DEFAULT '1',
  `modulesSteps_name` varchar(500) NOT NULL,
  `modulesSteps_type` tinyint(1) NOT NULL,
  `modulesSteps_content` longtext,
  `modulesSteps_completionTime` int DEFAULT '0',
  `modulesSteps_internalNotes` longtext,
  `modulesSteps_order` int NOT NULL DEFAULT '999',
  `modulesSteps_locked` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'When set this is a like system level step that can''t be edited',
  PRIMARY KEY (`modulesSteps_id`),
  KEY `modulesSteps_modules_modules_id_fk` (`modules_id`),
  CONSTRAINT `modulesSteps_modules_modules_id_fk` FOREIGN KEY (`modules_id`) REFERENCES `modules` (`modules_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modulessteps`
--

LOCK TABLES `modulessteps` WRITE;
/*!40000 ALTER TABLE `modulessteps` DISABLE KEYS */;
/*!40000 ALTER TABLE `modulessteps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passwordresetcodes`
--

DROP TABLE IF EXISTS `passwordresetcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `passwordresetcodes` (
  `passwordResetCodes_id` int NOT NULL AUTO_INCREMENT,
  `passwordResetCodes_code` varchar(1000) NOT NULL,
  `passwordResetCodes_used` tinyint(1) NOT NULL DEFAULT '0',
  `passwordResetCodes_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `passwordResetCodes_valid` int NOT NULL DEFAULT '1',
  `users_userid` int NOT NULL,
  PRIMARY KEY (`passwordResetCodes_id`),
  KEY `passwordResetCodes_users_users_userid_fk` (`users_userid`),
  CONSTRAINT `passwordResetCodes_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passwordresetcodes`
--

LOCK TABLES `passwordresetcodes` WRITE;
/*!40000 ALTER TABLE `passwordresetcodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `passwordresetcodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `payments_id` int NOT NULL AUTO_INCREMENT,
  `payments_amount` int NOT NULL,
  `payments_quantity` int NOT NULL DEFAULT '1',
  `payments_type` tinyint(1) NOT NULL COMMENT '1 = Payment Recieved\n2 = Sales item\n3 = SubHire item\n4 = Staff cost',
  `payments_reference` varchar(500) DEFAULT NULL,
  `payments_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `payments_supplier` varchar(500) DEFAULT NULL,
  `payments_method` varchar(500) DEFAULT NULL,
  `payments_comment` varchar(500) DEFAULT NULL,
  `projects_id` int NOT NULL,
  `payments_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`payments_id`),
  KEY `payments_projects_projects_id_fk` (`projects_id`),
  CONSTRAINT `payments_projects_projects_id_fk` FOREIGN KEY (`projects_id`) REFERENCES `projects` (`projects_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=221 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `positions` (
  `positions_id` int NOT NULL AUTO_INCREMENT,
  `positions_displayName` varchar(255) NOT NULL,
  `positions_positionsGroups` varchar(500) DEFAULT NULL,
  `positions_rank` tinyint unsigned NOT NULL DEFAULT '4' COMMENT 'Rank of the position - so that the most senior position for a user is shown as their "main one". 0 is the most senior',
  PRIMARY KEY (`positions_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positions`
--

LOCK TABLES `positions` WRITE;
/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
INSERT INTO `positions` VALUES (1,'Super-admin','1',1),(999,'User','999',99);
/*!40000 ALTER TABLE `positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `positionsgroups`
--

DROP TABLE IF EXISTS `positionsgroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `positionsgroups` (
  `positionsGroups_id` int NOT NULL AUTO_INCREMENT,
  `positionsGroups_name` varchar(255) NOT NULL,
  `positionsGroups_actions` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`positionsGroups_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positionsgroups`
--

LOCK TABLES `positionsgroups` WRITE;
/*!40000 ALTER TABLE `positionsgroups` DISABLE KEYS */;
INSERT INTO `positionsgroups` VALUES (1,'Administrator','1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22'),(999,'User',',8');
/*!40000 ALTER TABLE `positionsgroups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `projects_id` int NOT NULL AUTO_INCREMENT,
  `projects_name` varchar(500) NOT NULL,
  `instances_id` int NOT NULL,
  `projects_manager` int NOT NULL,
  `projects_description` text,
  `projects_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `clients_id` int DEFAULT NULL,
  `projects_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `projects_archived` tinyint(1) NOT NULL DEFAULT '0',
  `projects_dates_use_start` timestamp NULL DEFAULT NULL,
  `projects_dates_use_end` timestamp NULL DEFAULT NULL,
  `projects_dates_deliver_start` timestamp NULL DEFAULT NULL,
  `projects_dates_deliver_end` timestamp NULL DEFAULT NULL,
  `projects_status` tinyint NOT NULL DEFAULT '0' COMMENT 'Provisional',
  `locations_id` int DEFAULT NULL,
  `projects_invoiceNotes` text,
  `projects_defaultDiscount` double NOT NULL DEFAULT '0',
  `projectsTypes_id` int NOT NULL,
  PRIMARY KEY (`projects_id`),
  KEY `projects_clients_clients_id_fk` (`clients_id`),
  KEY `projects_instances_instances_id_fk` (`instances_id`),
  KEY `projects_users_users_userid_fk` (`projects_manager`),
  KEY `projects_locations_locations_id_fk` (`locations_id`),
  KEY `projects_projectsTypes_projectsTypes_id_fk` (`projectsTypes_id`),
  CONSTRAINT `projects_clients_clients_id_fk` FOREIGN KEY (`clients_id`) REFERENCES `clients` (`clients_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `projects_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `projects_locations_locations_id_fk` FOREIGN KEY (`locations_id`) REFERENCES `locations` (`locations_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `projects_users_users_userid_fk` FOREIGN KEY (`projects_manager`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projectsfinancecache`
--

DROP TABLE IF EXISTS `projectsfinancecache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projectsfinancecache` (
  `projectsFinanceCache_id` int NOT NULL AUTO_INCREMENT,
  `projects_id` int NOT NULL,
  `projectsFinanceCache_timestamp` timestamp NOT NULL,
  `projectsFinanceCache_timestampUpdated` timestamp NULL DEFAULT NULL,
  `projectsFinanceCache_equipmentSubTotal` int DEFAULT NULL,
  `projectsFinanceCache_equiptmentDiscounts` int DEFAULT NULL,
  `projectsFinanceCache_equiptmentTotal` int DEFAULT NULL,
  `projectsFinanceCache_salesTotal` int DEFAULT NULL,
  `projectsFinanceCache_staffTotal` int DEFAULT NULL,
  `projectsFinanceCache_externalHiresTotal` int DEFAULT NULL,
  `projectsFinanceCache_paymentsReceived` int DEFAULT NULL,
  `projectsFinanceCache_grandTotal` int DEFAULT NULL,
  `projectsFinanceCache_value` int DEFAULT NULL,
  `projectsFinanceCache_mass` decimal(55,5) DEFAULT NULL,
  PRIMARY KEY (`projectsFinanceCache_id`),
  KEY `projectsFinanceCache_projects_projects_id_fk` (`projects_id`),
  KEY `projectFinnaceCacheTimestamp` (`projectsFinanceCache_timestamp` DESC),
  CONSTRAINT `projectsFinanceCache_projects_projects_id_fk` FOREIGN KEY (`projects_id`) REFERENCES `projects` (`projects_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=265 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projectsfinancecache`
--

LOCK TABLES `projectsfinancecache` WRITE;
/*!40000 ALTER TABLE `projectsfinancecache` DISABLE KEYS */;
/*!40000 ALTER TABLE `projectsfinancecache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projectsnotes`
--

DROP TABLE IF EXISTS `projectsnotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projectsnotes` (
  `projectsNotes_id` int NOT NULL AUTO_INCREMENT,
  `projectsNotes_title` varchar(200) NOT NULL,
  `projectsNotes_text` text,
  `projectsNotes_userid` int NOT NULL,
  `projects_id` int NOT NULL,
  `projectsNotes_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`projectsNotes_id`),
  KEY `projectsNotes_projects_projects_id_fk` (`projects_id`),
  KEY `projectsNotes_users_users_userid_fk` (`projectsNotes_userid`),
  CONSTRAINT `projectsNotes_projects_projects_id_fk` FOREIGN KEY (`projects_id`) REFERENCES `projects` (`projects_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `projectsNotes_users_users_userid_fk` FOREIGN KEY (`projectsNotes_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projectsnotes`
--

LOCK TABLES `projectsnotes` WRITE;
/*!40000 ALTER TABLE `projectsnotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `projectsnotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projectstypes`
--

DROP TABLE IF EXISTS `projectstypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projectstypes` (
  `projectsTypes_id` int NOT NULL AUTO_INCREMENT,
  `projectsTypes_name` varchar(200) NOT NULL,
  `instances_id` int NOT NULL,
  `projectsTypes_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `projectsTypes_config_finance` tinyint(1) NOT NULL DEFAULT '1',
  `projectsTypes_config_files` int NOT NULL DEFAULT '1',
  `projectsTypes_config_assets` int NOT NULL DEFAULT '1',
  `projectsTypes_config_client` int NOT NULL DEFAULT '1',
  `projectsTypes_config_venue` int NOT NULL DEFAULT '1',
  `projectsTypes_config_notes` int NOT NULL DEFAULT '1',
  `projectsTypes_config_crew` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`projectsTypes_id`),
  KEY `projectsTypes_instances_instances_id_fk` (`instances_id`),
  CONSTRAINT `projectsTypes_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projectstypes`
--

LOCK TABLES `projectstypes` WRITE;
/*!40000 ALTER TABLE `projectstypes` DISABLE KEYS */;
INSERT INTO `projectstypes` VALUES (24,'Full Project',1,0,1,1,1,1,1,1,1);
/*!40000 ALTER TABLE `projectstypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projectsvacantroles`
--

DROP TABLE IF EXISTS `projectsvacantroles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projectsvacantroles` (
  `projectsVacantRoles_id` int NOT NULL AUTO_INCREMENT,
  `projects_id` int NOT NULL,
  `projectsVacantRoles_name` varchar(500) NOT NULL,
  `projectsVacantRoles_description` text,
  `projectsVacantRoles_personSpecification` text,
  `projectsVacantRoles_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `projectsVacantRoles_open` tinyint(1) NOT NULL DEFAULT '0',
  `projectsVacantRoles_showPublic` tinyint(1) NOT NULL DEFAULT '0',
  `projectsVacantRoles_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectsVacantRoles_deadline` timestamp NULL DEFAULT NULL,
  `projectsVacantRoles_firstComeFirstServed` tinyint(1) NOT NULL DEFAULT '0',
  `projectsVacantRoles_fileUploads` tinyint(1) NOT NULL DEFAULT '1',
  `projectsVacantRoles_slots` int NOT NULL DEFAULT '1',
  `projectsVacantRoles_slotsFilled` int NOT NULL DEFAULT '0',
  `projectsVacantRoles_questions` json DEFAULT NULL,
  `projectsVacantRoles_collectPhone` tinyint(1) NOT NULL DEFAULT '0',
  `projectsVacantRoles_privateToPM` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`projectsVacantRoles_id`),
  KEY `projectsVacantRoles_projects_projects_id_fk` (`projects_id`),
  CONSTRAINT `projectsVacantRoles_projects_projects_id_fk` FOREIGN KEY (`projects_id`) REFERENCES `projects` (`projects_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projectsvacantroles`
--

LOCK TABLES `projectsvacantroles` WRITE;
/*!40000 ALTER TABLE `projectsvacantroles` DISABLE KEYS */;
/*!40000 ALTER TABLE `projectsvacantroles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projectsvacantrolesapplications`
--

DROP TABLE IF EXISTS `projectsvacantrolesapplications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projectsvacantrolesapplications` (
  `projectsVacantRolesApplications_id` int NOT NULL AUTO_INCREMENT,
  `projectsVacantRoles_id` int NOT NULL,
  `users_userid` int NOT NULL,
  `projectsVacantRolesApplications_files` text,
  `projectsVacantRolesApplications_phone` varchar(255) DEFAULT NULL,
  `projectsVacantRolesApplications_applicantComment` text,
  `projectsVacantRolesApplications_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `projectsVacantRolesApplications_withdrawn` tinyint(1) NOT NULL DEFAULT '0',
  `projectsVacantRolesApplications_submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `projectsVacantRolesApplications_questionAnswers` json DEFAULT NULL,
  `projectsVacantRolesApplications_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = Success\n2 = Rejected',
  PRIMARY KEY (`projectsVacantRolesApplications_id`),
  KEY `projectsVacantRolesApplications_projectsVacantRolesid_fk` (`projectsVacantRoles_id`),
  KEY `projectsVacantRolesApplications_users_users_userid_fk` (`users_userid`),
  CONSTRAINT `projectsVacantRolesApplications_projectsVacantRolesid_fk` FOREIGN KEY (`projectsVacantRoles_id`) REFERENCES `projectsvacantroles` (`projectsVacantRoles_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `projectsVacantRolesApplications_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projectsvacantrolesapplications`
--

LOCK TABLES `projectsvacantrolesapplications` WRITE;
/*!40000 ALTER TABLE `projectsvacantrolesapplications` DISABLE KEYS */;
/*!40000 ALTER TABLE `projectsvacantrolesapplications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `s3files`
--

DROP TABLE IF EXISTS `s3files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `s3files` (
  `s3files_id` int NOT NULL AUTO_INCREMENT,
  `instances_id` int NOT NULL,
  `s3files_path` varchar(255) DEFAULT NULL COMMENT 'NO LEADING /',
  `s3files_name` varchar(1000) DEFAULT NULL,
  `s3files_filename` varchar(255) NOT NULL,
  `s3files_extension` varchar(255) NOT NULL,
  `s3files_original_name` varchar(500) DEFAULT NULL COMMENT 'What was this file originally called when it was uploaded? For things like file attachments\n',
  `s3files_region` varchar(255) NOT NULL,
  `s3files_endpoint` varchar(255) NOT NULL,
  `s3files_cdn_endpoint` varchar(255) DEFAULT NULL,
  `s3files_bucket` varchar(255) NOT NULL,
  `s3files_meta_size` bigint NOT NULL COMMENT 'Size of the file in bytes',
  `s3files_meta_public` tinyint(1) NOT NULL DEFAULT '0',
  `s3files_meta_type` tinyint NOT NULL DEFAULT '0' COMMENT '0 = undefined\nRest are set out in corehead\n',
  `s3files_meta_subType` int DEFAULT NULL COMMENT 'Depends what it is - each module that uses the file handler will be setting this for themselves',
  `s3files_meta_uploaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `users_userid` int DEFAULT NULL COMMENT 'Who uploaded it?',
  `s3files_meta_deleteOn` timestamp NULL DEFAULT NULL COMMENT 'Delete this file on this set date (basically if you hit delete we will kill it after say 30 days)',
  `s3files_meta_physicallyStored` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'If we have the file it''s 1 - if we deleted it it''s 0 but the "deleteOn" is set. If we lost it it''s 0 with a null "delete on"',
  `s3files_compressed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`s3files_id`),
  KEY `s3files_instances_instances_id_fk` (`instances_id`),
  KEY `s3files_users_users_userid_fk` (`users_userid`),
  CONSTRAINT `s3files_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `s3files_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1150 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `s3files`
--

LOCK TABLES `s3files` WRITE;
/*!40000 ALTER TABLE `s3files` DISABLE KEYS */;
/*!40000 ALTER TABLE `s3files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `signupcodes`
--

DROP TABLE IF EXISTS `signupcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `signupcodes` (
  `signupCodes_id` int NOT NULL AUTO_INCREMENT,
  `signupCodes_name` varchar(200) NOT NULL,
  `instances_id` int NOT NULL,
  `signupCodes_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `signupCodes_valid` tinyint(1) NOT NULL DEFAULT '1',
  `signupCodes_notes` text,
  `signupCodes_role` varchar(500) NOT NULL,
  `instancePositions_id` int DEFAULT NULL,
  PRIMARY KEY (`signupCodes_id`),
  UNIQUE KEY `signupCodes_signupCodes_name_uindex` (`signupCodes_name`),
  KEY `signupCodes_instances_instances_id_fk` (`instances_id`),
  KEY `signupCodes_instancePositions_instancePositions_id_fk` (`instancePositions_id`),
  CONSTRAINT `signupCodes_instancePositions_instancePositions_id_fk` FOREIGN KEY (`instancePositions_id`) REFERENCES `instancepositions` (`instancePositions_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `signupCodes_instances_instances_id_fk` FOREIGN KEY (`instances_id`) REFERENCES `instances` (`instances_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `signupcodes`
--

LOCK TABLES `signupcodes` WRITE;
/*!40000 ALTER TABLE `signupcodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `signupcodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userinstances`
--

DROP TABLE IF EXISTS `userinstances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `userinstances` (
  `userInstances_id` int NOT NULL AUTO_INCREMENT,
  `users_userid` int NOT NULL,
  `instancePositions_id` int NOT NULL,
  `userInstances_extraPermissions` varchar(5000) DEFAULT NULL,
  `userInstances_label` varchar(500) DEFAULT NULL,
  `userInstances_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `signupCodes_id` int DEFAULT NULL,
  `userInstances_archived` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`userInstances_id`),
  KEY `userInstances_instancePositions_instancePositions_id_fk` (`instancePositions_id`),
  KEY `userInstances_users_users_userid_fk` (`users_userid`),
  KEY `userInstances_signupCodes_signupCodes_id_fk` (`signupCodes_id`),
  CONSTRAINT `userInstances_instancePositions_instancePositions_id_fk` FOREIGN KEY (`instancePositions_id`) REFERENCES `instancepositions` (`instancePositions_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `userInstances_signupCodes_signupCodes_id_fk` FOREIGN KEY (`signupCodes_id`) REFERENCES `signupcodes` (`signupCodes_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `userInstances_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userinstances`
--

LOCK TABLES `userinstances` WRITE;
/*!40000 ALTER TABLE `userinstances` DISABLE KEYS */;
INSERT INTO `userinstances` VALUES (125,1,24,NULL,'Admin',0,NULL,NULL);
/*!40000 ALTER TABLE `userinstances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usermodules`
--

DROP TABLE IF EXISTS `usermodules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usermodules` (
  `userModules_id` int NOT NULL AUTO_INCREMENT,
  `modules_id` int NOT NULL,
  `users_userid` int NOT NULL,
  `userModules_stepsCompleted` varchar(1000) DEFAULT NULL,
  `userModules_currentStep` int DEFAULT NULL,
  `userModules_started` timestamp NOT NULL,
  `userModules_updated` timestamp NOT NULL,
  PRIMARY KEY (`userModules_id`),
  KEY `userModules_modules_modules_id_fk` (`modules_id`),
  KEY `userModules_users_users_userid_fk` (`users_userid`),
  KEY `userModules_modulesSteps_modulesSteps_id_fk` (`userModules_currentStep`),
  CONSTRAINT `userModules_modules_modules_id_fk` FOREIGN KEY (`modules_id`) REFERENCES `modules` (`modules_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `userModules_modulesSteps_modulesSteps_id_fk` FOREIGN KEY (`userModules_currentStep`) REFERENCES `modulessteps` (`modulesSteps_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `userModules_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usermodules`
--

LOCK TABLES `usermodules` WRITE;
/*!40000 ALTER TABLE `usermodules` DISABLE KEYS */;
/*!40000 ALTER TABLE `usermodules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usermodulescertifications`
--

DROP TABLE IF EXISTS `usermodulescertifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usermodulescertifications` (
  `userModulesCertifications_id` int NOT NULL AUTO_INCREMENT,
  `modules_id` int NOT NULL,
  `users_userid` int NOT NULL,
  `userModulesCertifications_revoked` tinyint(1) NOT NULL DEFAULT '0',
  `userModulesCertifications_approvedBy` int NOT NULL,
  `userModulesCertifications_approvedComment` varchar(2000) DEFAULT NULL,
  `userModulesCertifications_timestamp` timestamp NOT NULL,
  PRIMARY KEY (`userModulesCertifications_id`),
  KEY `userModulesCertifications_users_users_userid_fk` (`users_userid`),
  KEY `userModulesCertifications_users_users_userid_fk_2` (`userModulesCertifications_approvedBy`),
  KEY `userModulesCertifications_modules_modules_id_fk` (`modules_id`),
  CONSTRAINT `userModulesCertifications_modules_modules_id_fk` FOREIGN KEY (`modules_id`) REFERENCES `modules` (`modules_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `userModulesCertifications_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `userModulesCertifications_users_users_userid_fk_2` FOREIGN KEY (`userModulesCertifications_approvedBy`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usermodulescertifications`
--

LOCK TABLES `usermodulescertifications` WRITE;
/*!40000 ALTER TABLE `usermodulescertifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `usermodulescertifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userpositions`
--

DROP TABLE IF EXISTS `userpositions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `userpositions` (
  `userPositions_id` int NOT NULL AUTO_INCREMENT,
  `users_userid` int DEFAULT NULL,
  `userPositions_start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `userPositions_end` timestamp NULL DEFAULT NULL,
  `positions_id` int DEFAULT NULL COMMENT 'Can be null if you like - as long as you set the relevant other fields',
  `userPositions_displayName` varchar(255) DEFAULT NULL,
  `userPositions_extraPermissions` varchar(500) DEFAULT NULL COMMENT 'Allow a few extra permissions to be added just for this user for that exact permissions term\n',
  `userPositions_show` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`userPositions_id`),
  KEY `userPositions_positions_positions_id_fk` (`positions_id`),
  KEY `userPositions_users_users_userid_fk` (`users_userid`),
  CONSTRAINT `userPositions_positions_positions_id_fk` FOREIGN KEY (`positions_id`) REFERENCES `positions` (`positions_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `userPositions_users_users_userid_fk` FOREIGN KEY (`users_userid`) REFERENCES `users` (`users_userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userpositions`
--

LOCK TABLES `userpositions` WRITE;
/*!40000 ALTER TABLE `userpositions` DISABLE KEYS */;
INSERT INTO `userpositions` VALUES (1,1,'2021-05-12 14:49:06',NULL,1,NULL,NULL,1);
/*!40000 ALTER TABLE `userpositions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `users_username` varchar(200) DEFAULT NULL,
  `users_name1` varchar(100) DEFAULT NULL,
  `users_name2` varchar(100) DEFAULT NULL,
  `users_userid` int NOT NULL AUTO_INCREMENT,
  `users_salty1` varchar(30) DEFAULT NULL,
  `users_password` varchar(150) DEFAULT NULL,
  `users_salty2` varchar(50) DEFAULT NULL,
  `users_hash` varchar(255) NOT NULL,
  `users_email` varchar(257) DEFAULT NULL,
  `users_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When user signed up',
  `users_notes` text COMMENT 'Internal Notes - Not visible to user',
  `users_thumbnail` int DEFAULT NULL,
  `users_changepass` tinyint(1) NOT NULL DEFAULT '0',
  `users_selectedProjectID` int DEFAULT NULL,
  `users_selectedInstanceIDLast` int DEFAULT NULL COMMENT 'What is the instance ID they most recently selected? This will be the one we use next time they login',
  `users_suspended` tinyint(1) NOT NULL DEFAULT '0',
  `users_deleted` tinyint(1) DEFAULT '0',
  `users_emailVerified` tinyint(1) NOT NULL DEFAULT '0',
  `users_social_facebook` varchar(100) DEFAULT NULL,
  `users_social_twitter` varchar(100) DEFAULT NULL,
  `users_social_instagram` varchar(100) DEFAULT NULL,
  `users_social_linkedin` varchar(100) DEFAULT NULL,
  `users_social_snapchat` varchar(100) DEFAULT NULL,
  `users_calendarHash` varchar(200) DEFAULT NULL,
  `users_widgets` varchar(500) DEFAULT NULL,
  `users_notificationSettings` text,
  `users_assetGroupsWatching` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`users_userid`),
  UNIQUE KEY `users_users_email_uindex` (`users_email`),
  UNIQUE KEY `users_users_username_uindex` (`users_username`),
  KEY `username_2` (`users_userid`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('username','UserF','UserL',1,'8smqAFD9','fa5a51baef12914c7f2e0e1176a030bf086d26edae298c25d5f84c90bc72ecd7','uOhfrOCW','sha256','test@example.com','2021-05-12 14:46:06',NULL,NULL,0,NULL,1,0,0,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-05-12 15:56:45
