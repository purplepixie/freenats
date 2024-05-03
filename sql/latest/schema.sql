-- FreeNATS freenats-1.30.0a Schema
-- No DROP TABLES - suitable for upgrade
-- MySQL dump 10.13  Distrib 5.7.34, for osx11.0 (x86_64)
--
-- Host: localhost    Database: freenats
-- ------------------------------------------------------
-- Server version	5.7.34

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `fnalert`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnalert` (
  `alertid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nodeid` varchar(64) NOT NULL,
  `alertlevel` int(11) NOT NULL DEFAULT '0',
  `openedx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `closedx` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`alertid`),
  KEY `nodeid` (`nodeid`),
  KEY `closedx` (`closedx`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnalertaction`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnalertaction` (
  `aaid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `atype` varchar(32) NOT NULL,
  `efrom` varchar(250) NOT NULL,
  `etolist` text,
  `esubject` int(11) NOT NULL DEFAULT '0',
  `etype` int(11) NOT NULL DEFAULT '0',
  `awarnings` tinyint(1) NOT NULL DEFAULT '0',
  `adecrease` tinyint(1) NOT NULL DEFAULT '0',
  `mdata` text,
  `aname` varchar(120) NOT NULL,
  `ctrdate` varchar(8) DEFAULT NULL,
  `ctrlimit` int(10) unsigned NOT NULL DEFAULT '0',
  `ctrtoday` int(10) unsigned NOT NULL DEFAULT '0',
  `scheduleid` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnalertlog`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnalertlog` (
  `alid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `alertid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `postedx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `logentry` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`alid`),
  KEY `alertid` (`alertid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnconfig`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnconfig` (
  `fnc_var` varchar(64) NOT NULL,
  `fnc_val` varchar(64) NOT NULL,
  PRIMARY KEY (`fnc_var`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fneval`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fneval` (
  `evalid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `testid` varchar(128) NOT NULL,
  `weight` int(11) NOT NULL DEFAULT '0',
  `eoperator` varchar(32) NOT NULL,
  `evalue` varchar(128) NOT NULL,
  `eoutcome` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`evalid`),
  KEY `testid` (`testid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fngroup`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fngroup` (
  `groupid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(128) NOT NULL,
  `groupdesc` varchar(250) NOT NULL,
  `groupicon` varchar(64) NOT NULL,
  `weight` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fngrouplink`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fngrouplink` (
  `glid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `groupid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `nodeid` varchar(64) NOT NULL,
  PRIMARY KEY (`glid`),
  KEY `groupid` (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnlocaltest`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnlocaltest` (
  `localtestid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nodeid` varchar(64) NOT NULL,
  `alertlevel` int(11) NOT NULL DEFAULT '-1',
  `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `testtype` varchar(128) NOT NULL,
  `testparam` varchar(250) DEFAULT NULL,
  `testrecord` tinyint(1) NOT NULL DEFAULT '0',
  `simpleeval` tinyint(1) NOT NULL DEFAULT '1',
  `testname` varchar(64) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT '0',
  `timeout` int(11) NOT NULL DEFAULT '0',
  `testenabled` tinyint(1) NOT NULL DEFAULT '1',
  `testparam1` varchar(250) DEFAULT NULL,
  `testparam2` varchar(250) DEFAULT NULL,
  `testparam3` varchar(250) DEFAULT NULL,
  `testparam4` varchar(250) DEFAULT NULL,
  `testparam5` varchar(250) DEFAULT NULL,
  `testparam6` varchar(250) DEFAULT NULL,
  `testparam7` varchar(250) DEFAULT NULL,
  `testparam8` varchar(250) DEFAULT NULL,
  `testparam9` varchar(250) DEFAULT NULL,
  `lastvalue` float NOT NULL DEFAULT '0',
  `testinterval` int(10) unsigned NOT NULL DEFAULT '0',
  `nextrunx` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`localtestid`),
  KEY `nodeid` (`nodeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnlog`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnlog` (
  `logid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `postedx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `modid` varchar(32) NOT NULL,
  `catid` varchar(32) NOT NULL,
  `username` varchar(64) NOT NULL,
  `loglevel` int(11) NOT NULL DEFAULT '1',
  `logevent` varchar(250) NOT NULL,
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnnalink`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnnalink` (
  `nalid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nodeid` varchar(64) NOT NULL,
  `aaid` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`nalid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnnode`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnnode` (
  `nodeid` varchar(64) NOT NULL,
  `nodename` varchar(128) NOT NULL,
  `nodedesc` varchar(254) NOT NULL,
  `hostname` varchar(254) NOT NULL,
  `nodeenabled` tinyint(1) NOT NULL DEFAULT '0',
  `pingtest` tinyint(1) NOT NULL DEFAULT '0',
  `pingfatal` tinyint(1) NOT NULL DEFAULT '0',
  `alertlevel` int(11) NOT NULL DEFAULT '-1',
  `nodeicon` varchar(64) NOT NULL,
  `weight` int(10) unsigned NOT NULL DEFAULT '0',
  `nodealert` tinyint(1) NOT NULL DEFAULT '1',
  `scheduleid` bigint(20) NOT NULL DEFAULT '0',
  `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `testinterval` int(10) unsigned NOT NULL DEFAULT '5',
  `nextrunx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `nsenabled` tinyint(1) NOT NULL DEFAULT '0',
  `nsurl` varchar(254) NOT NULL,
  `nskey` varchar(250) NOT NULL,
  `nspullenabled` tinyint(1) NOT NULL DEFAULT '0',
  `nspushenabled` tinyint(1) NOT NULL DEFAULT '0',
  `nspuship` varchar(128) NOT NULL,
  `nsinterval` int(10) unsigned NOT NULL DEFAULT '15',
  `nslastx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `nsnextx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `nspullalert` tinyint(1) NOT NULL DEFAULT '0',
  `nsfreshpush` tinyint(1) NOT NULL DEFAULT '0',
  `masterid` varchar(64) NOT NULL,
  `masterjustping` tinyint(1) NOT NULL DEFAULT '1',
  `ulink0` tinyint(1) NOT NULL DEFAULT '0',
  `ulink0_title` varchar(254) NOT NULL DEFAULT 'VNC',
  `ulink0_url` varchar(254) NOT NULL DEFAULT 'http://{HOSTNAME}:5800/',
  `ulink1` tinyint(1) NOT NULL DEFAULT '0',
  `ulink1_title` varchar(254) NOT NULL DEFAULT 'SSH',
  `ulink1_url` varchar(254) NOT NULL DEFAULT 'ssh://{HOSTNAME}',
  `ulink2` tinyint(1) NOT NULL DEFAULT '0',
  `ulink2_title` varchar(254) NOT NULL DEFAULT 'Web',
  `ulink2_url` varchar(254) NOT NULL DEFAULT 'http://{HOSTNAME}',
  PRIMARY KEY (`nodeid`),
  KEY `masterid` (`masterid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnrecord`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnrecord` (
  `recordid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `testid` varchar(128) NOT NULL,
  `alertlevel` int(11) NOT NULL DEFAULT '0',
  `testvalue` float NOT NULL DEFAULT '0',
  `recordx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `nodeid` varchar(64) NOT NULL,
  PRIMARY KEY (`recordid`),
  KEY `testid` (`testid`),
  KEY `recordx` (`recordx`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnsession`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnsession` (
  `sessionid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sessionkey` varchar(128) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `username` varchar(64) NOT NULL,
  `startx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `updatex` bigint(20) unsigned NOT NULL DEFAULT '0',
  `userlevel` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sessionid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fntestrun`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fntestrun` (
  `trid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `startx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `finishx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `routput` text,
  `fnode` varchar(64) NOT NULL,
  PRIMARY KEY (`trid`),
  KEY `finishx` (`finishx`),
  KEY `fnode` (`fnode`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnuser`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnuser` (
  `username` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `realname` varchar(128) NOT NULL,
  `userlevel` int(11) NOT NULL DEFAULT '1',
  `grouplock` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-05-01 17:52:13
