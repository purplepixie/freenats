-- No DROP TABLES - suitable for upgrade
-- MySQL dump 10.9
--
-- Host: localhost    Database: freenats
-- ------------------------------------------------------
-- Server version	4.1.14
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `fnalert`
--

CREATE TABLE `fnalert` (
  `alertid` bigint(20) unsigned NOT NULL auto_increment,
  `nodeid` varchar(64) NOT NULL default '',
  `alertlevel` int(11) NOT NULL default '0',
  `openedx` bigint(20) unsigned NOT NULL default '0',
  `closedx` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`alertid`)
);

--
-- Table structure for table `fnalertaction`
--

CREATE TABLE `fnalertaction` (
  `aaid` bigint(20) unsigned NOT NULL auto_increment,
  `atype` varchar(32) NOT NULL default '',
  `efrom` varchar(250) NOT NULL default '',
  `etolist` text NOT NULL,
  `esubject` int(11) NOT NULL default '0',
  `etype` int(11) NOT NULL default '0',
  `awarnings` tinyint(1) NOT NULL default '0',
  `adecrease` tinyint(1) NOT NULL default '0',
  `mdata` text NOT NULL,
  `aname` varchar(120) NOT NULL default '',
  `ctrdate` varchar(8) NOT NULL default '',
  `ctrlimit` int(10) unsigned NOT NULL default '0',
  `ctrtoday` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`aaid`)
);

--
-- Table structure for table `fnalertlog`
--

CREATE TABLE `fnalertlog` (
  `alid` bigint(20) unsigned NOT NULL auto_increment,
  `alertid` bigint(20) unsigned NOT NULL default '0',
  `postedx` bigint(20) unsigned NOT NULL default '0',
  `logentry` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`alid`)
);

--
-- Table structure for table `fnconfig`
--

CREATE TABLE `fnconfig` (
  `fnc_var` varchar(64) NOT NULL default '',
  `fnc_val` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`fnc_var`)
);

--
-- Table structure for table `fneval`
--

CREATE TABLE `fneval` (
  `evalid` bigint(20) unsigned NOT NULL auto_increment,
  `testid` varchar(128) NOT NULL default '',
  `weight` int(11) NOT NULL default '0',
  `eoperator` varchar(32) NOT NULL default '',
  `evalue` varchar(128) NOT NULL default '',
  `eoutcome` int(11) NOT NULL default '0',
  PRIMARY KEY  (`evalid`)
);

--
-- Table structure for table `fngroup`
--

CREATE TABLE `fngroup` (
  `groupid` bigint(20) unsigned NOT NULL auto_increment,
  `groupname` varchar(128) NOT NULL default '',
  `groupdesc` varchar(250) NOT NULL default '',
  `groupicon` varchar(64) NOT NULL default '',
  `weight` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`groupid`)
);

--
-- Table structure for table `fngrouplink`
--

CREATE TABLE `fngrouplink` (
  `glid` bigint(20) unsigned NOT NULL auto_increment,
  `groupid` bigint(20) unsigned NOT NULL default '0',
  `nodeid` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`glid`)
);

--
-- Table structure for table `fnlocaltest`
--

CREATE TABLE `fnlocaltest` (
  `localtestid` bigint(20) unsigned NOT NULL auto_increment,
  `nodeid` varchar(64) NOT NULL default '',
  `alertlevel` int(11) NOT NULL default '-1',
  `lastrunx` bigint(20) unsigned NOT NULL default '0',
  `testtype` varchar(128) NOT NULL default '',
  `testparam` varchar(250) default NULL,
  `testrecord` tinyint(1) NOT NULL default '0',
  `simpleeval` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`localtestid`)
);

--
-- Table structure for table `fnlog`
--

CREATE TABLE `fnlog` (
  `logid` bigint(20) unsigned NOT NULL auto_increment,
  `postedx` bigint(20) unsigned NOT NULL default '0',
  `modid` varchar(32) NOT NULL default '',
  `catid` varchar(32) NOT NULL default '',
  `username` varchar(64) NOT NULL default '',
  `loglevel` int(11) NOT NULL default '1',
  `logevent` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`logid`)
);

--
-- Table structure for table `fnnalink`
--

CREATE TABLE `fnnalink` (
  `nalid` bigint(20) unsigned NOT NULL auto_increment,
  `nodeid` varchar(64) NOT NULL default '',
  `aaid` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`nalid`)
);

--
-- Table structure for table `fnnode`
--

CREATE TABLE `fnnode` (
  `nodeid` varchar(64) NOT NULL default '',
  `nodename` varchar(128) NOT NULL default '',
  `nodedesc` varchar(254) NOT NULL default '',
  `hostname` varchar(254) NOT NULL default '',
  `nodeenabled` tinyint(1) NOT NULL default '0',
  `pingtest` tinyint(1) NOT NULL default '0',
  `pingfatal` tinyint(1) NOT NULL default '0',
  `alertlevel` int(11) NOT NULL default '-1',
  `nodeicon` varchar(64) NOT NULL default '',
  `weight` int(10) unsigned NOT NULL default '0',
  `nodealert` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`nodeid`)
);

--
-- Table structure for table `fnrecord`
--

CREATE TABLE `fnrecord` (
  `recordid` bigint(20) unsigned NOT NULL auto_increment,
  `testid` varchar(128) NOT NULL default '',
  `alertlevel` int(11) NOT NULL default '0',
  `testvalue` float NOT NULL default '0',
  `recordx` bigint(20) unsigned NOT NULL default '0',
  `nodeid` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`recordid`)
);

--
-- Table structure for table `fnsession`
--

CREATE TABLE `fnsession` (
  `sessionid` bigint(20) unsigned NOT NULL auto_increment,
  `sessionkey` varchar(128) NOT NULL default '',
  `ipaddress` varchar(128) NOT NULL default '',
  `username` varchar(64) NOT NULL default '',
  `startx` bigint(20) unsigned NOT NULL default '0',
  `updatex` bigint(20) unsigned NOT NULL default '0',
  `userlevel` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sessionid`)
);

--
-- Table structure for table `fntestrun`
--

CREATE TABLE `fntestrun` (
  `trid` bigint(20) unsigned NOT NULL auto_increment,
  `startx` bigint(20) unsigned NOT NULL default '0',
  `finishx` bigint(20) unsigned NOT NULL default '0',
  `routput` text NOT NULL,
  `fnode` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`trid`)
);

--
-- Table structure for table `fnuser`
--

CREATE TABLE `fnuser` (
  `username` varchar(64) NOT NULL default '',
  `password` varchar(64) NOT NULL default '',
  `realname` varchar(128) NOT NULL default '',
  `userlevel` int(11) NOT NULL default '1'
);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

