--
-- ---------------------------------------------------------------------
--
-- GLPI - Gestionnaire Libre de Parc Informatique
--
-- http://glpi-project.org
--
-- @copyright 2015-2025 Teclib' and contributors.
-- @licence   https://www.gnu.org/licenses/gpl-3.0.html
--
-- ---------------------------------------------------------------------
--
-- LICENSE
--
-- This file is part of GLPI.
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <https://www.gnu.org/licenses/>.
--
-- ---------------------------------------------------------------------
--

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
-- Table structure for table `glpi_plugin_formcreator_forms_groups`
--

DROP TABLE IF EXISTS `glpi_plugin_formcreator_forms_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `glpi_plugin_formcreator_forms_groups` (
  `id` int unsigned NOT NULL,
  `plugin_formcreator_forms_id` int unsigned NOT NULL,
  `groups_id` int unsigned NOT NULL,
  `uuid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_formcreator_forms_groups`
--

LOCK TABLES `glpi_plugin_formcreator_forms_groups` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_formcreator_forms_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `glpi_plugin_formcreator_forms_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_formcreator_questions`
--

DROP TABLE IF EXISTS `glpi_plugin_formcreator_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `glpi_plugin_formcreator_questions` (
  `id` int unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `plugin_formcreator_sections_id` int unsigned NOT NULL,
  `fieldtype` varchar(30) DEFAULT NULL,
  `required` tinyint(1) DEFAULT NULL,
  `show_empty` tinyint(1) DEFAULT NULL,
  `default_values` mediumtext,
  `itemtype` varchar(255) DEFAULT NULL,
  `values` mediumtext,
  `description` mediumtext,
  `row` int NOT NULL,
  `col` int NOT NULL,
  `width` int NOT NULL,
  `show_rule` int NOT NULL,
  `uuid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_formcreator_questions`
--

LOCK TABLES `glpi_plugin_formcreator_questions` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_formcreator_questions` DISABLE KEYS */;
INSERT INTO `glpi_plugin_formcreator_questions` VALUES (22,'Test form migration for questions - Actor',11,'actor',0,0,'[2]','',NULL,'',0,2,2,1,'13d0d449-91f5039d-67877fbc44eef4.16672745'),(23,'Test form migration for questions - Additional fields',11,'fields',0,0,NULL,'','{\"dropdown_fields_field\":\"gzfllfield\",\"blocks_field\":\"4\"}','',0,0,2,1,'13d0d449-91f5039d-678783beb1b4e5.64512762'),(24,'Test form migration for questions - Checkboxes',11,'checkboxes',0,0,'[\"Option 2\",\"Option 5\"]','','[\"Option 1\",\"Option 2\",\"Option 3\",\"Option 4\",\"Option 5\",\"Option 6\",\"Option 7\"]','',1,0,1,1,'13d0d449-91f5039d-678783e6187d37.70279252'),(25,'Test form migration for questions - Date',11,'date',0,0,'2025-01-29','',NULL,'',1,1,1,1,'13d0d449-91f5039d-678783fbef07e7.87750991'),(26,'Test form migration for questions - Date and time',11,'datetime',0,0,'2025-01-29 12:00:00','',NULL,'',1,2,1,1,'13d0d449-91f5039d-6787840a131039.05969649'),(27,'Test form migration for questions - Description',11,'description',0,0,NULL,'',NULL,'&#60;p&#62;This is a description question type&#60;/p&#62;',1,3,1,1,'13d0d449-91f5039d-678784229b82d7.42642948'),(28,'Test form migration for questions - Dropdown',11,'dropdown',0,0,'1','Location','{\"show_tree_depth\":\"0\",\"show_tree_root\":\"0\",\"selectable_tree_root\":\"0\",\"entity_restrict\":\"2\"}','',2,0,4,1,'13d0d449-91f5039d-67878445d795b6.07616686'),(29,'Test form migration for questions - Email',11,'email',0,0,'test@test.fr','','','',3,0,4,1,'13d0d449-91f5039d-6787845a8c0550.23541628'),(30,'Test form migration for questions - File',11,'file',0,0,NULL,'',NULL,'',4,0,4,1,'13d0d449-91f5039d-678789701851a0.76378229'),(31,'Test form migration for questions - Float',11,'float',0,0,'8,45','','','',5,0,4,1,'13d0d449-91f5039d-6787897e0703b1.90417706'),(32,'Test form migration for questions - Glpi Object',11,'glpiselect',0,0,'1','Computer','{\"entity_restrict\":\"2\"}','',6,0,4,1,'13d0d449-91f5039d-67878998584a27.11882972'),(33,'Test form migration for questions - Hidden field',11,'hidden',0,0,'test hidden field','',NULL,'',7,0,4,1,'13d0d449-91f5039d-678789a5a0f964.26980024'),(34,'Test form migration for questions - Hostname',11,'hostname',0,0,NULL,'',NULL,'',8,0,4,1,'13d0d449-91f5039d-678789b34997b1.33623123'),(35,'Test form migration for questions - IP Addresse',11,'ip',0,0,NULL,'',NULL,'',9,0,4,1,'13d0d449-91f5039d-678789c0104a75.96918904'),(36,'Test form migration for questions - Integer',11,'integer',0,0,'78','','','',10,0,4,1,'13d0d449-91f5039d-678789c988a473.24972235'),(37,'Test form migration for questions - LDAP Select',11,'ldapselect',0,0,NULL,'','{\"ldap_auth\":\"1\",\"ldap_attribute\":\"12\",\"ldap_filter\":\"(& (uid=*) )\"}','',11,0,4,1,'13d0d449-91f5039d-67878a05d3c326.35829700'),(38,'Test form migration for questions - Multiselect',11,'multiselect',0,0,'[\"Option 3\",\"Option 4\"]','','[\"Option 1\",\"Option 2\",\"Option 3\",\"Option 4\",\"Option 5\"]','',12,0,4,1,'13d0d449-91f5039d-67878a2d90cea2.48530344'),(39,'Test form migration for questions - Radios',11,'radios',0,0,'Option 2','','[\"Option 1\",\"Option 2\",\"Option 3\",\"Option 4\"]','',13,0,4,1,'13d0d449-91f5039d-67878a48cf71b3.62293600'),(40,'Test form migration for questions - Request type',11,'requesttype',0,0,'2','',NULL,'',14,0,4,1,'13d0d449-91f5039d-67878a53ac2c51.37272675'),(41,'Test form migration for questions - Select',11,'select',0,0,'Option 1','','[\"Option 1\",\"Option 2\"]','',15,0,4,1,'13d0d449-91f5039d-67878a69d3cf02.76840759'),(42,'Test form migration for questions - Tags',11,'tag',0,0,NULL,'',NULL,'',18,0,4,1,'13d0d449-91f5039d-67878ae3629375.79074002'),(43,'Test form migration for questions - Text',11,'text',0,0,'Test default text value','',NULL,'',17,0,4,1,'13d0d449-91f5039d-67878af59157f2.25429606'),(44,'Test form migration for questions - Textarea',11,'textarea',0,0,'&#60;p&#62;Test &#60;span style=\"color: #2dc26b; background-color: #843fa1;\"&#62;default value&#60;/span&#62; text &#60;strong&#62;area&#60;/strong&#62;&#60;/p&#62;','',NULL,'',16,0,4,1,'13d0d449-91f5039d-67878b0d55b865.54398926'),(45,'Test form migration for questions - Time',11,'time',0,0,'12:00:00','',NULL,'',19,0,4,1,'13d0d449-91f5039d-67878b1b2b2b86.77749904'),(46,'Test form migration for questions - Urgency',11,'urgency',0,0,'2','',NULL,'',20,0,4,1,'13d0d449-91f5039d-67878b28b26020.93425219');
/*!40000 ALTER TABLE `glpi_plugin_formcreator_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_formcreator_categories`
--

DROP TABLE IF EXISTS `glpi_plugin_formcreator_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `glpi_plugin_formcreator_categories` (
  `id` int unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `comment` mediumtext,
  `completename` varchar(255) DEFAULT NULL,
  `plugin_formcreator_categories_id` int unsigned NOT NULL,
  `level` int NOT NULL,
  `sons_cache` longtext,
  `ancestors_cache` longtext,
  `knowbaseitemcategories_id` int unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_formcreator_categories`
--

LOCK TABLES `glpi_plugin_formcreator_categories` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_formcreator_categories` DISABLE KEYS */;
INSERT INTO `glpi_plugin_formcreator_categories` VALUES (1,'My test form category','','Root form categorie > My test form category',3,2,'{\"1\":1}','{\"3\":3}',0),(3,'Root form category','','Root form categorie',0,1,NULL,'[]',0);
/*!40000 ALTER TABLE `glpi_plugin_formcreator_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_formcreator_forms_profiles`
--

DROP TABLE IF EXISTS `glpi_plugin_formcreator_forms_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `glpi_plugin_formcreator_forms_profiles` (
  `id` int unsigned NOT NULL,
  `plugin_formcreator_forms_id` int unsigned NOT NULL,
  `profiles_id` int unsigned NOT NULL,
  `uuid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_formcreator_forms_profiles`
--

LOCK TABLES `glpi_plugin_formcreator_forms_profiles` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_formcreator_forms_profiles` DISABLE KEYS */;
INSERT INTO `glpi_plugin_formcreator_forms_profiles` VALUES (1,10,1,'13d0d449-91f5039d-678f638f4973b2.97182859'),(2,10,4,'13d0d449-91f5039d-678f638f4b51a8.63447592');
/*!40000 ALTER TABLE `glpi_plugin_formcreator_forms_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_formcreator_forms_users`
--

DROP TABLE IF EXISTS `glpi_plugin_formcreator_forms_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `glpi_plugin_formcreator_forms_users` (
  `id` int unsigned NOT NULL,
  `plugin_formcreator_forms_id` int unsigned NOT NULL,
  `users_id` int unsigned NOT NULL,
  `uuid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_formcreator_forms_users`
--

LOCK TABLES `glpi_plugin_formcreator_forms_users` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_formcreator_forms_users` DISABLE KEYS */;
INSERT INTO `glpi_plugin_formcreator_forms_users` VALUES (1,10,2,'13d0d449-91f5039d-678f638f475179.94375485');
/*!40000 ALTER TABLE `glpi_plugin_formcreator_forms_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_formcreator_sections`
--

DROP TABLE IF EXISTS `glpi_plugin_formcreator_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `glpi_plugin_formcreator_sections` (
  `id` int unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `plugin_formcreator_forms_id` int unsigned NOT NULL,
  `order` int NOT NULL,
  `show_rule` int NOT NULL,
  `uuid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_formcreator_sections`
--

LOCK TABLES `glpi_plugin_formcreator_sections` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_formcreator_sections` DISABLE KEYS */;
INSERT INTO `glpi_plugin_formcreator_sections` VALUES (7,'Section',5,1,1,'13d0d449-91f5039d-67865f9ec21e00.64800503'),(8,'Section',4,1,1,'13d0d449-91f5039d-67868bb732f521.89613959'),(9,'First section',6,1,1,'13d0d449-91f5039d-67868bda15b5e4.42395948'),(10,'Second section',6,2,1,'13d0d449-91f5039d-67868bdf03a686.18185201'),(11,'Section',7,1,1,'13d0d449-91f5039d-67877f5c52efd1.07463389'),(12,'Section',8,1,1,'13d0d449-91f5039d-678f630f4c8891.38266445'),(13,'Section',9,1,1,'13d0d449-91f5039d-678f634495b3a4.95372894'),(14,'Section',10,1,1,'13d0d449-91f5039d-678f6355393520.90228005');
/*!40000 ALTER TABLE `glpi_plugin_formcreator_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `glpi_plugin_formcreator_forms`
--

DROP TABLE IF EXISTS `glpi_plugin_formcreator_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `glpi_plugin_formcreator_forms` (
  `id` int unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `entities_id` int unsigned NOT NULL,
  `is_recursive` tinyint(1) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `icon_color` varchar(255) DEFAULT NULL,
  `background_color` varchar(255) DEFAULT NULL,
  `access_rights` tinyint(1) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `content` longtext,
  `plugin_formcreator_categories_id` int unsigned NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  `helpdesk_home` tinyint(1) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT NULL,
  `validation_required` tinyint(1) DEFAULT NULL,
  `usage_count` int NOT NULL,
  `is_default` tinyint(1) DEFAULT NULL,
  `is_captcha_enabled` tinyint(1) DEFAULT NULL,
  `show_rule` int NOT NULL,
  `formanswer_name` varchar(255) DEFAULT NULL,
  `is_visible` tinyint NOT NULL,
  `uuid` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `glpi_plugin_formcreator_forms`
--

LOCK TABLES `glpi_plugin_formcreator_forms` WRITE;
/*!40000 ALTER TABLE `glpi_plugin_formcreator_forms` DISABLE KEYS */;
INSERT INTO `glpi_plugin_formcreator_forms` VALUES (4,'Test form migration for basic properties',0,0,'0','#999999','#e7e7e7',1,'','',0,1,'',0,0,0,0,0,0,1,'Test form migration for basic properties',1,'13d0d449-91f5039d-678638f86ff479.66501068'),(5,'Test form migration for basic properties with form category',0,0,'0','#999999','#e7e7e7',1,'','',1,1,'',0,0,0,0,0,0,1,'Test form migration for basic properties with form category',1,'13d0d449-91f5039d-67865f9ebd9f58.29603668'),(6,'Test form migration for sections',0,0,'0','#999999','#e7e7e7',1,'','',0,0,'',0,0,0,0,0,0,1,'Test form migration for sections',1,'13d0d449-91f5039d-67868bc224e124.20535500'),(7,'Test form migration for questions',0,0,'0','#999999','#e7e7e7',1,'','',0,0,'',0,0,0,0,0,0,1,'Test form migration for questions',1,'13d0d449-91f5039d-67877f5c4ee3c3.90813653'),(8,'Test form migration for access types with public access',0,0,'0','#999999','#e7e7e7',0,'','',0,0,'',0,0,0,0,0,0,1,'Test form migration for access types',1,'13d0d449-91f5039d-678f630f4a4737.71277458'),(9,'Test form migration for access types with private access',0,0,'0','#999999','#e7e7e7',1,'','',0,0,'',0,0,0,0,0,0,1,'Test form migration for access types with private access',1,'13d0d449-91f5039d-678f634493d8b7.87824440'),(10,'Test form migration for access types with restricted access',0,0,'0','#999999','#e7e7e7',2,'','',0,0,'',0,0,0,0,0,0,1,'Test form migration for access types with restricted access',1,'13d0d449-91f5039d-678f63553754a9.65767968');
/*!40000 ALTER TABLE `glpi_plugin_formcreator_forms` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-01-21 11:41:32
