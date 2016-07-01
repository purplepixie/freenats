-- With DROP TABLES - will clean database
-- MySQL dump 10.13  Distrib 5.5.42, for osx10.6 (i386)
--
-- Host: localhost    Database: freenats
-- ------------------------------------------------------
-- Server version	5.5.42

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

DROP TABLE IF EXISTS `fnalert`;
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
) ENGINE=MyISAM AUTO_INCREMENT=2300 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnalertaction`
--

DROP TABLE IF EXISTS `fnalertaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnalertaction` (
  `aaid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `atype` varchar(32) NOT NULL,
  `efrom` varchar(250) NOT NULL,
  `etolist` text NOT NULL,
  `esubject` int(11) NOT NULL DEFAULT '0',
  `etype` int(11) NOT NULL DEFAULT '0',
  `awarnings` tinyint(1) NOT NULL DEFAULT '0',
  `adecrease` tinyint(1) NOT NULL DEFAULT '0',
  `mdata` text NOT NULL,
  `aname` varchar(120) NOT NULL,
  `ctrdate` varchar(8) NOT NULL,
  `ctrlimit` int(10) unsigned NOT NULL DEFAULT '0',
  `ctrtoday` int(10) unsigned NOT NULL DEFAULT '0',
  `scheduleid` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aaid`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnalertlog`
--

DROP TABLE IF EXISTS `fnalertlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnalertlog` (
  `alid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `alertid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `postedx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `logentry` varchar(250) NOT NULL,
  PRIMARY KEY (`alid`),
  KEY `alertid` (`alertid`)
) ENGINE=MyISAM AUTO_INCREMENT=49751 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnconfig`
--

DROP TABLE IF EXISTS `fnconfig`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnconfig` (
  `fnc_var` varchar(64) NOT NULL,
  `fnc_val` varchar(64) NOT NULL,
  PRIMARY KEY (`fnc_var`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fneval`
--

DROP TABLE IF EXISTS `fneval`;
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fngroup`
--

DROP TABLE IF EXISTS `fngroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fngroup` (
  `groupid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `groupname` varchar(128) NOT NULL,
  `groupdesc` varchar(250) NOT NULL,
  `groupicon` varchar(64) NOT NULL,
  `weight` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`groupid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fngrouplink`
--

DROP TABLE IF EXISTS `fngrouplink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fngrouplink` (
  `glid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `groupid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `nodeid` varchar(64) NOT NULL,
  PRIMARY KEY (`glid`),
  KEY `groupid` (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnlocaltest`
--

DROP TABLE IF EXISTS `fnlocaltest`;
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
  `testparam1` varchar(250) NOT NULL,
  `testparam2` varchar(250) NOT NULL,
  `testparam3` varchar(250) NOT NULL,
  `testparam4` varchar(250) NOT NULL,
  `testparam5` varchar(250) NOT NULL,
  `testparam6` varchar(250) NOT NULL,
  `testparam7` varchar(250) NOT NULL,
  `testparam8` varchar(250) NOT NULL,
  `testparam9` varchar(250) NOT NULL,
  `lastvalue` float NOT NULL DEFAULT '0',
  `testinterval` int(10) unsigned NOT NULL DEFAULT '0',
  `nextrunx` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`localtestid`),
  KEY `nodeid` (`nodeid`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnlog`
--

DROP TABLE IF EXISTS `fnlog`;
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
) ENGINE=MyISAM AUTO_INCREMENT=96829630 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnnalink`
--

DROP TABLE IF EXISTS `fnnalink`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnnalink` (
  `nalid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nodeid` varchar(64) NOT NULL,
  `aaid` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`nalid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnnode`
--

DROP TABLE IF EXISTS `fnnode`;
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
  `nskey` varchar(128) NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnnstest`
--

DROP TABLE IF EXISTS `fnnstest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnnstest` (
  `nstestid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nodeid` varchar(64) NOT NULL DEFAULT '',
  `alertlevel` int(11) NOT NULL DEFAULT '-1',
  `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `testtype` varchar(128) NOT NULL DEFAULT '',
  `testdesc` varchar(250) DEFAULT NULL,
  `testrecord` tinyint(1) NOT NULL DEFAULT '0',
  `simpleeval` tinyint(1) NOT NULL DEFAULT '1',
  `testname` varchar(64) NOT NULL DEFAULT '',
  `testenabled` tinyint(1) NOT NULL DEFAULT '0',
  `lastvalue` varchar(128) NOT NULL DEFAULT '',
  `testalerts` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`nstestid`),
  KEY `nodeid` (`nodeid`)
) ENGINE=MyISAM AUTO_INCREMENT=43486 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnrecord`
--

DROP TABLE IF EXISTS `fnrecord`;
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
) ENGINE=MyISAM AUTO_INCREMENT=4768889 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnreport`
--

DROP TABLE IF EXISTS `fnreport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnreport` (
  `reportid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reportname` varchar(128) NOT NULL DEFAULT '',
  `reporttests` text NOT NULL,
  PRIMARY KEY (`reportid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnrssfeed`
--

DROP TABLE IF EXISTS `fnrssfeed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnrssfeed` (
  `feedid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `feedkey` varchar(254) NOT NULL,
  `feedname` varchar(254) NOT NULL,
  `feedtype` varchar(32) NOT NULL,
  `typeopt` varchar(254) NOT NULL,
  `feedrange` varchar(32) NOT NULL,
  `rangeopt` varchar(254) NOT NULL,
  PRIMARY KEY (`feedid`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnscheditem`
--

DROP TABLE IF EXISTS `fnscheditem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnscheditem` (
  `scheditemid` bigint(20) NOT NULL AUTO_INCREMENT,
  `scheduleid` bigint(20) NOT NULL DEFAULT '0',
  `dayofweek` varchar(8) NOT NULL DEFAULT '',
  `dayofmonth` int(11) NOT NULL DEFAULT '0',
  `monthofyear` int(11) NOT NULL DEFAULT '0',
  `year` int(11) NOT NULL DEFAULT '0',
  `starthour` int(11) NOT NULL DEFAULT '0',
  `startmin` int(11) NOT NULL DEFAULT '0',
  `finishhour` int(11) NOT NULL DEFAULT '23',
  `finishmin` int(11) NOT NULL DEFAULT '59',
  PRIMARY KEY (`scheditemid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnschedule`
--

DROP TABLE IF EXISTS `fnschedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnschedule` (
  `scheduleid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `schedulename` varchar(128) NOT NULL DEFAULT '',
  `defaultaction` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`scheduleid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnsession`
--

DROP TABLE IF EXISTS `fnsession`;
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
) ENGINE=MyISAM AUTO_INCREMENT=155 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fntestrun`
--

DROP TABLE IF EXISTS `fntestrun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fntestrun` (
  `trid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `startx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `finishx` bigint(20) unsigned NOT NULL DEFAULT '0',
  `routput` text NOT NULL,
  `fnode` varchar(64) NOT NULL,
  PRIMARY KEY (`trid`),
  KEY `finishx` (`finishx`),
  KEY `fnode` (`fnode`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnuser`
--

DROP TABLE IF EXISTS `fnuser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnuser` (
  `username` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `realname` varchar(128) NOT NULL,
  `userlevel` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnview`
--

DROP TABLE IF EXISTS `fnview`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnview` (
  `viewid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vtitle` varchar(128) NOT NULL DEFAULT '',
  `vstyle` varchar(32) NOT NULL DEFAULT '',
  `vpublic` tinyint(1) NOT NULL DEFAULT '0',
  `vclick` varchar(32) NOT NULL DEFAULT '',
  `vrefresh` int(11) NOT NULL DEFAULT '0',
  `vlinkv` bigint(20) unsigned NOT NULL DEFAULT '0',
  `vcolumns` smallint(6) NOT NULL DEFAULT '0',
  `vcolon` tinyint(1) NOT NULL DEFAULT '1',
  `vdashes` tinyint(1) NOT NULL DEFAULT '1',
  `vtimeago` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`viewid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fnviewitem`
--

DROP TABLE IF EXISTS `fnviewitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fnviewitem` (
  `viewitemid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `viewid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `itype` varchar(128) NOT NULL DEFAULT '',
  `ioption` varchar(250) NOT NULL DEFAULT '',
  `icolour` tinyint(1) NOT NULL DEFAULT '1',
  `itextstatus` tinyint(1) NOT NULL DEFAULT '0',
  `idetail` smallint(5) unsigned NOT NULL DEFAULT '0',
  `iweight` int(10) unsigned NOT NULL DEFAULT '0',
  `isize` smallint(6) NOT NULL DEFAULT '0',
  `igraphic` smallint(6) NOT NULL DEFAULT '0',
  `iname` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`viewitemid`),
  KEY `viewid` (`viewid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-07-01 13:55:17
