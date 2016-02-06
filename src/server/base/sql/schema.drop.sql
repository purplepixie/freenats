-- With DROP TABLES - will clean database
-- MySQL dump 10.9
--
-- Host: localhost    Database: freenats
-- ------------------------------------------------------
-- Server version	4.1.14

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `fnalert`
--

DROP TABLE IF EXISTS `fnalert`;
CREATE TABLE `fnalert` (
  `alertid` bigint(20) unsigned NOT NULL auto_increment,
  `nodeid` varchar(64) NOT NULL default '',
  `alertlevel` int(11) NOT NULL default '0',
  `openedx` bigint(20) unsigned NOT NULL default '0',
  `closedx` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`alertid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnalertaction`
--

DROP TABLE IF EXISTS `fnalertaction`;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnalertlog`
--

DROP TABLE IF EXISTS `fnalertlog`;
CREATE TABLE `fnalertlog` (
  `alid` bigint(20) unsigned NOT NULL auto_increment,
  `alertid` bigint(20) unsigned NOT NULL default '0',
  `postedx` bigint(20) unsigned NOT NULL default '0',
  `logentry` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`alid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnconfig`
--

DROP TABLE IF EXISTS `fnconfig`;
CREATE TABLE `fnconfig` (
  `fnc_var` varchar(64) NOT NULL default '',
  `fnc_val` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`fnc_var`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fneval`
--

DROP TABLE IF EXISTS `fneval`;
CREATE TABLE `fneval` (
  `evalid` bigint(20) unsigned NOT NULL auto_increment,
  `testid` varchar(128) NOT NULL default '',
  `weight` int(11) NOT NULL default '0',
  `eoperator` varchar(32) NOT NULL default '',
  `evalue` varchar(128) NOT NULL default '',
  `eoutcome` int(11) NOT NULL default '0',
  PRIMARY KEY  (`evalid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fngroup`
--

DROP TABLE IF EXISTS `fngroup`;
CREATE TABLE `fngroup` (
  `groupid` bigint(20) unsigned NOT NULL auto_increment,
  `groupname` varchar(128) NOT NULL default '',
  `groupdesc` varchar(250) NOT NULL default '',
  `groupicon` varchar(64) NOT NULL default '',
  `weight` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fngrouplink`
--

DROP TABLE IF EXISTS `fngrouplink`;
CREATE TABLE `fngrouplink` (
  `glid` bigint(20) unsigned NOT NULL auto_increment,
  `groupid` bigint(20) unsigned NOT NULL default '0',
  `nodeid` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`glid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnlocaltest`
--

DROP TABLE IF EXISTS `fnlocaltest`;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnlog`
--

DROP TABLE IF EXISTS `fnlog`;
CREATE TABLE `fnlog` (
  `logid` bigint(20) unsigned NOT NULL auto_increment,
  `postedx` bigint(20) unsigned NOT NULL default '0',
  `modid` varchar(32) NOT NULL default '',
  `catid` varchar(32) NOT NULL default '',
  `username` varchar(64) NOT NULL default '',
  `loglevel` int(11) NOT NULL default '1',
  `logevent` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`logid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnnalink`
--

DROP TABLE IF EXISTS `fnnalink`;
CREATE TABLE `fnnalink` (
  `nalid` bigint(20) unsigned NOT NULL auto_increment,
  `nodeid` varchar(64) NOT NULL default '',
  `aaid` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`nalid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnnode`
--

DROP TABLE IF EXISTS `fnnode`;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnrecord`
--

DROP TABLE IF EXISTS `fnrecord`;
CREATE TABLE `fnrecord` (
  `recordid` bigint(20) unsigned NOT NULL auto_increment,
  `testid` varchar(128) NOT NULL default '',
  `alertlevel` int(11) NOT NULL default '0',
  `testvalue` float NOT NULL default '0',
  `recordx` bigint(20) unsigned NOT NULL default '0',
  `nodeid` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`recordid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnsession`
--

DROP TABLE IF EXISTS `fnsession`;
CREATE TABLE `fnsession` (
  `sessionid` bigint(20) unsigned NOT NULL auto_increment,
  `sessionkey` varchar(128) NOT NULL default '',
  `ipaddress` varchar(128) NOT NULL default '',
  `username` varchar(64) NOT NULL default '',
  `startx` bigint(20) unsigned NOT NULL default '0',
  `updatex` bigint(20) unsigned NOT NULL default '0',
  `userlevel` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sessionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fntestrun`
--

DROP TABLE IF EXISTS `fntestrun`;
CREATE TABLE `fntestrun` (
  `trid` bigint(20) unsigned NOT NULL auto_increment,
  `startx` bigint(20) unsigned NOT NULL default '0',
  `finishx` bigint(20) unsigned NOT NULL default '0',
  `routput` text NOT NULL,
  `fnode` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`trid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnuser`
--

DROP TABLE IF EXISTS `fnuser`;
CREATE TABLE `fnuser` (
  `username` varchar(64) NOT NULL default '',
  `password` varchar(64) NOT NULL default '',
  `realname` varchar(128) NOT NULL default '',
  `userlevel` int(11) NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

