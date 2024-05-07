-- FreeNATS freenats-1.30.5a Schema
-- Experimental Upgrade SQL - run after schema.sql (not drop!)
-- Both will generate many many errors - run with --force, ignore errors
-- myrug -- PurplePixie Systems
-- http://www.purplepixie.org/myrug
-- 
-- SHOW TABLES
-- Table: fnalert
-- DESCRIBE fnalert
ALTER TABLE `fnalert` CHANGE `alertid` `alertid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnalert` ADD `alertid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnalert` ADD PRIMARY KEY( `alertid` );
ALTER TABLE `fnalert` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnalert` ADD `nodeid` varchar(64) NOT NULL DEFAULT '';
CREATE INDEX `nodeid` ON `fnalert` ( `nodeid` );
ALTER TABLE `fnalert` CHANGE `alertlevel` `alertlevel` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalert` ADD `alertlevel` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalert` CHANGE `openedx` `openedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalert` ADD `openedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalert` CHANGE `closedx` `closedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalert` ADD `closedx` bigint(20) unsigned NOT NULL DEFAULT '0';
CREATE INDEX `closedx` ON `fnalert` ( `closedx` );
-- 
-- Table: fnalertaction
-- DESCRIBE fnalertaction
ALTER TABLE `fnalertaction` CHANGE `aaid` `aaid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnalertaction` ADD `aaid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnalertaction` ADD PRIMARY KEY( `aaid` );
ALTER TABLE `fnalertaction` CHANGE `atype` `atype` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnalertaction` ADD `atype` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnalertaction` CHANGE `efrom` `efrom` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `fnalertaction` ADD `efrom` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `fnalertaction` CHANGE `etolist` `etolist` text DEFAULT '';
ALTER TABLE `fnalertaction` ADD `etolist` text DEFAULT '';
ALTER TABLE `fnalertaction` CHANGE `esubject` `esubject` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `esubject` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `etype` `etype` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `etype` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `awarnings` `awarnings` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `awarnings` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `adecrease` `adecrease` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `adecrease` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `mdata` `mdata` text DEFAULT '';
ALTER TABLE `fnalertaction` ADD `mdata` text DEFAULT '';
ALTER TABLE `fnalertaction` CHANGE `aname` `aname` varchar(120) NOT NULL DEFAULT '';
ALTER TABLE `fnalertaction` ADD `aname` varchar(120) NOT NULL DEFAULT '';
ALTER TABLE `fnalertaction` CHANGE `ctrdate` `ctrdate` varchar(8) DEFAULT '';
ALTER TABLE `fnalertaction` ADD `ctrdate` varchar(8) DEFAULT '';
ALTER TABLE `fnalertaction` CHANGE `ctrlimit` `ctrlimit` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `ctrlimit` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `ctrtoday` `ctrtoday` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `ctrtoday` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `scheduleid` `scheduleid` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `scheduleid` bigint(20) unsigned NOT NULL DEFAULT '0';
-- 
-- Table: fnalertlog
-- DESCRIBE fnalertlog
ALTER TABLE `fnalertlog` CHANGE `alid` `alid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnalertlog` ADD `alid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnalertlog` ADD PRIMARY KEY( `alid` );
ALTER TABLE `fnalertlog` CHANGE `alertid` `alertid` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertlog` ADD `alertid` bigint(20) unsigned NOT NULL DEFAULT '0';
CREATE INDEX `alertid` ON `fnalertlog` ( `alertid` );
ALTER TABLE `fnalertlog` CHANGE `postedx` `postedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertlog` ADD `postedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertlog` CHANGE `logentry` `logentry` varchar(250) DEFAULT '';
ALTER TABLE `fnalertlog` ADD `logentry` varchar(250) DEFAULT '';
-- 
-- Table: fnconfig
-- DESCRIBE fnconfig
ALTER TABLE `fnconfig` CHANGE `fnc_var` `fnc_var` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnconfig` ADD `fnc_var` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnconfig` ADD PRIMARY KEY( `fnc_var` );
ALTER TABLE `fnconfig` CHANGE `fnc_val` `fnc_val` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnconfig` ADD `fnc_val` varchar(64) NOT NULL DEFAULT '';
-- 
-- Table: fneval
-- DESCRIBE fneval
ALTER TABLE `fneval` CHANGE `evalid` `evalid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fneval` ADD `evalid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fneval` ADD PRIMARY KEY( `evalid` );
ALTER TABLE `fneval` CHANGE `testid` `testid` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fneval` ADD `testid` varchar(128) NOT NULL DEFAULT '';
CREATE INDEX `testid` ON `fneval` ( `testid` );
ALTER TABLE `fneval` CHANGE `weight` `weight` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fneval` ADD `weight` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fneval` CHANGE `eoperator` `eoperator` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fneval` ADD `eoperator` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fneval` CHANGE `evalue` `evalue` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fneval` ADD `evalue` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fneval` CHANGE `eoutcome` `eoutcome` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fneval` ADD `eoutcome` int(11) NOT NULL DEFAULT '0';
-- 
-- Table: fngroup
-- DESCRIBE fngroup
ALTER TABLE `fngroup` CHANGE `groupid` `groupid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fngroup` ADD `groupid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fngroup` ADD PRIMARY KEY( `groupid` );
ALTER TABLE `fngroup` CHANGE `groupname` `groupname` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fngroup` ADD `groupname` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fngroup` CHANGE `groupdesc` `groupdesc` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `fngroup` ADD `groupdesc` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `fngroup` CHANGE `groupicon` `groupicon` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fngroup` ADD `groupicon` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fngroup` CHANGE `weight` `weight` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fngroup` ADD `weight` int(10) unsigned NOT NULL DEFAULT '0';
-- 
-- Table: fngrouplink
-- DESCRIBE fngrouplink
ALTER TABLE `fngrouplink` CHANGE `glid` `glid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fngrouplink` ADD `glid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fngrouplink` ADD PRIMARY KEY( `glid` );
ALTER TABLE `fngrouplink` CHANGE `groupid` `groupid` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fngrouplink` ADD `groupid` bigint(20) unsigned NOT NULL DEFAULT '0';
CREATE INDEX `groupid` ON `fngrouplink` ( `groupid` );
ALTER TABLE `fngrouplink` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fngrouplink` ADD `nodeid` varchar(64) NOT NULL DEFAULT '';
-- 
-- Table: fngrouplock
-- DESCRIBE fngrouplock
ALTER TABLE `fngrouplock` CHANGE `glid` `glid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fngrouplock` ADD `glid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fngrouplock` ADD PRIMARY KEY( `glid` );
ALTER TABLE `fngrouplock` CHANGE `username` `username` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fngrouplock` ADD `username` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fngrouplock` CHANGE `groupid` `groupid` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `fngrouplock` ADD `groupid` bigint(20) NOT NULL DEFAULT '0';
-- 
-- Table: fnlocaltest
-- DESCRIBE fnlocaltest
ALTER TABLE `fnlocaltest` CHANGE `localtestid` `localtestid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `localtestid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnlocaltest` ADD PRIMARY KEY( `localtestid` );
ALTER TABLE `fnlocaltest` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `nodeid` varchar(64) NOT NULL DEFAULT '';
CREATE INDEX `nodeid` ON `fnlocaltest` ( `nodeid` );
ALTER TABLE `fnlocaltest` CHANGE `alertlevel` `alertlevel` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `fnlocaltest` ADD `alertlevel` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `fnlocaltest` CHANGE `lastrunx` `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` ADD `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` CHANGE `testtype` `testtype` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testtype` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `testparam` `testparam` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testparam` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `testrecord` `testrecord` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` ADD `testrecord` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` CHANGE `simpleeval` `simpleeval` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnlocaltest` ADD `simpleeval` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnlocaltest` CHANGE `testname` `testname` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testname` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `attempts` `attempts` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` ADD `attempts` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` CHANGE `timeout` `timeout` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` ADD `timeout` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` CHANGE `testenabled` `testenabled` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnlocaltest` ADD `testenabled` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnlocaltest` CHANGE `testparam1` `testparam1` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testparam1` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `testparam2` `testparam2` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testparam2` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `testparam3` `testparam3` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testparam3` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `testparam4` `testparam4` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testparam4` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `testparam5` `testparam5` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testparam5` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `testparam6` `testparam6` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testparam6` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `testparam7` `testparam7` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testparam7` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `testparam8` `testparam8` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testparam8` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `testparam9` `testparam9` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` ADD `testparam9` varchar(250) DEFAULT '';
ALTER TABLE `fnlocaltest` CHANGE `lastvalue` `lastvalue` float NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` ADD `lastvalue` float NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` CHANGE `testinterval` `testinterval` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` ADD `testinterval` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` CHANGE `nextrunx` `nextrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` ADD `nextrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
-- 
-- Table: fnlog
-- DESCRIBE fnlog
ALTER TABLE `fnlog` CHANGE `logid` `logid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnlog` ADD `logid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnlog` ADD PRIMARY KEY( `logid` );
ALTER TABLE `fnlog` CHANGE `postedx` `postedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlog` ADD `postedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlog` CHANGE `modid` `modid` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnlog` ADD `modid` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnlog` CHANGE `catid` `catid` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnlog` ADD `catid` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnlog` CHANGE `username` `username` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnlog` ADD `username` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnlog` CHANGE `loglevel` `loglevel` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `fnlog` ADD `loglevel` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `fnlog` CHANGE `logevent` `logevent` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `fnlog` ADD `logevent` varchar(250) NOT NULL DEFAULT '';
-- 
-- Table: fnnalink
-- DESCRIBE fnnalink
ALTER TABLE `fnnalink` CHANGE `nalid` `nalid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnnalink` ADD `nalid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnnalink` ADD PRIMARY KEY( `nalid` );
ALTER TABLE `fnnalink` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnnalink` ADD `nodeid` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnnalink` CHANGE `aaid` `aaid` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnalink` ADD `aaid` bigint(20) unsigned NOT NULL DEFAULT '0';
-- 
-- Table: fnnode
-- DESCRIBE fnnode
ALTER TABLE `fnnode` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` ADD `nodeid` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` ADD PRIMARY KEY( `nodeid` );
ALTER TABLE `fnnode` CHANGE `nodename` `nodename` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` ADD `nodename` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` CHANGE `nodedesc` `nodedesc` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` ADD `nodedesc` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` CHANGE `hostname` `hostname` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` ADD `hostname` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` CHANGE `nodeenabled` `nodeenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `nodeenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `pingtest` `pingtest` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `pingtest` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `pingfatal` `pingfatal` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `pingfatal` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `alertlevel` `alertlevel` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `fnnode` ADD `alertlevel` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `fnnode` CHANGE `nodeicon` `nodeicon` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` ADD `nodeicon` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` CHANGE `weight` `weight` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `weight` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `nodealert` `nodealert` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnnode` ADD `nodealert` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnnode` CHANGE `scheduleid` `scheduleid` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `scheduleid` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `lastrunx` `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `testinterval` `testinterval` int(10) unsigned NOT NULL DEFAULT '5';
ALTER TABLE `fnnode` ADD `testinterval` int(10) unsigned NOT NULL DEFAULT '5';
ALTER TABLE `fnnode` CHANGE `nextrunx` `nextrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `nextrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `nsenabled` `nsenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `nsenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `nsurl` `nsurl` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` ADD `nsurl` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` CHANGE `nskey` `nskey` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` ADD `nskey` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` CHANGE `nspullenabled` `nspullenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `nspullenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `nspushenabled` `nspushenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `nspushenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `nspuship` `nspuship` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` ADD `nspuship` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` CHANGE `nsinterval` `nsinterval` int(10) unsigned NOT NULL DEFAULT '15';
ALTER TABLE `fnnode` ADD `nsinterval` int(10) unsigned NOT NULL DEFAULT '15';
ALTER TABLE `fnnode` CHANGE `nslastx` `nslastx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `nslastx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `nsnextx` `nsnextx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `nsnextx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `nspullalert` `nspullalert` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `nspullalert` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `nsfreshpush` `nsfreshpush` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `nsfreshpush` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `masterid` `masterid` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnnode` ADD `masterid` varchar(64) NOT NULL DEFAULT '';
CREATE INDEX `masterid` ON `fnnode` ( `masterid` );
ALTER TABLE `fnnode` CHANGE `masterjustping` `masterjustping` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnnode` ADD `masterjustping` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnnode` CHANGE `ulink0` `ulink0` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `ulink0` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `ulink0_title` `ulink0_title` varchar(254) NOT NULL DEFAULT 'VNC';
ALTER TABLE `fnnode` ADD `ulink0_title` varchar(254) NOT NULL DEFAULT 'VNC';
ALTER TABLE `fnnode` CHANGE `ulink0_url` `ulink0_url` varchar(254) NOT NULL DEFAULT 'http://{HOSTNAME}:5800/';
ALTER TABLE `fnnode` ADD `ulink0_url` varchar(254) NOT NULL DEFAULT 'http://{HOSTNAME}:5800/';
ALTER TABLE `fnnode` CHANGE `ulink1` `ulink1` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `ulink1` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `ulink1_title` `ulink1_title` varchar(254) NOT NULL DEFAULT 'SSH';
ALTER TABLE `fnnode` ADD `ulink1_title` varchar(254) NOT NULL DEFAULT 'SSH';
ALTER TABLE `fnnode` CHANGE `ulink1_url` `ulink1_url` varchar(254) NOT NULL DEFAULT 'ssh://{HOSTNAME}';
ALTER TABLE `fnnode` ADD `ulink1_url` varchar(254) NOT NULL DEFAULT 'ssh://{HOSTNAME}';
ALTER TABLE `fnnode` CHANGE `ulink2` `ulink2` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `ulink2` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `ulink2_title` `ulink2_title` varchar(254) NOT NULL DEFAULT 'Web';
ALTER TABLE `fnnode` ADD `ulink2_title` varchar(254) NOT NULL DEFAULT 'Web';
ALTER TABLE `fnnode` CHANGE `ulink2_url` `ulink2_url` varchar(254) NOT NULL DEFAULT 'http://{HOSTNAME}';
ALTER TABLE `fnnode` ADD `ulink2_url` varchar(254) NOT NULL DEFAULT 'http://{HOSTNAME}';
-- 
-- Table: fnnstest
-- DESCRIBE fnnstest
ALTER TABLE `fnnstest` CHANGE `nstestid` `nstestid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnnstest` ADD `nstestid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnnstest` ADD PRIMARY KEY( `nstestid` );
ALTER TABLE `fnnstest` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnnstest` ADD `nodeid` varchar(64) NOT NULL DEFAULT '';
CREATE INDEX `nodeid` ON `fnnstest` ( `nodeid` );
ALTER TABLE `fnnstest` CHANGE `alertlevel` `alertlevel` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `fnnstest` ADD `alertlevel` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `fnnstest` CHANGE `lastrunx` `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnstest` ADD `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnstest` CHANGE `testtype` `testtype` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnnstest` ADD `testtype` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnnstest` CHANGE `testdesc` `testdesc` varchar(250) DEFAULT '';
ALTER TABLE `fnnstest` ADD `testdesc` varchar(250) DEFAULT '';
ALTER TABLE `fnnstest` CHANGE `testrecord` `testrecord` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnstest` ADD `testrecord` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnstest` CHANGE `simpleeval` `simpleeval` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnnstest` ADD `simpleeval` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnnstest` CHANGE `testname` `testname` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnnstest` ADD `testname` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnnstest` CHANGE `testenabled` `testenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnstest` ADD `testenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnstest` CHANGE `lastvalue` `lastvalue` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnnstest` ADD `lastvalue` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnnstest` CHANGE `testalerts` `testalerts` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnnstest` ADD `testalerts` tinyint(1) NOT NULL DEFAULT '1';
-- 
-- Table: fnrecord
-- DESCRIBE fnrecord
ALTER TABLE `fnrecord` CHANGE `recordid` `recordid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnrecord` ADD `recordid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnrecord` ADD PRIMARY KEY( `recordid` );
ALTER TABLE `fnrecord` CHANGE `testid` `testid` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnrecord` ADD `testid` varchar(128) NOT NULL DEFAULT '';
CREATE INDEX `testid` ON `fnrecord` ( `testid` );
ALTER TABLE `fnrecord` CHANGE `alertlevel` `alertlevel` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` ADD `alertlevel` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` CHANGE `testvalue` `testvalue` float NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` ADD `testvalue` float NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` CHANGE `recordx` `recordx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` ADD `recordx` bigint(20) unsigned NOT NULL DEFAULT '0';
CREATE INDEX `recordx` ON `fnrecord` ( `recordx` );
ALTER TABLE `fnrecord` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnrecord` ADD `nodeid` varchar(64) NOT NULL DEFAULT '';
-- 
-- Table: fnreport
-- DESCRIBE fnreport
ALTER TABLE `fnreport` CHANGE `reportid` `reportid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnreport` ADD `reportid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnreport` ADD PRIMARY KEY( `reportid` );
ALTER TABLE `fnreport` CHANGE `reportname` `reportname` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnreport` ADD `reportname` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnreport` CHANGE `reporttests` `reporttests` text DEFAULT '';
ALTER TABLE `fnreport` ADD `reporttests` text DEFAULT '';
-- 
-- Table: fnrssfeed
-- DESCRIBE fnrssfeed
ALTER TABLE `fnrssfeed` CHANGE `feedid` `feedid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnrssfeed` ADD `feedid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnrssfeed` ADD PRIMARY KEY( `feedid` );
ALTER TABLE `fnrssfeed` CHANGE `feedkey` `feedkey` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` ADD `feedkey` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` CHANGE `feedname` `feedname` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` ADD `feedname` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` CHANGE `feedtype` `feedtype` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` ADD `feedtype` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` CHANGE `typeopt` `typeopt` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` ADD `typeopt` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` CHANGE `feedrange` `feedrange` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` ADD `feedrange` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` CHANGE `rangeopt` `rangeopt` varchar(254) NOT NULL DEFAULT '';
ALTER TABLE `fnrssfeed` ADD `rangeopt` varchar(254) NOT NULL DEFAULT '';
-- 
-- Table: fnscheditem
-- DESCRIBE fnscheditem
ALTER TABLE `fnscheditem` CHANGE `scheditemid` `scheditemid` bigint(20) NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnscheditem` ADD `scheditemid` bigint(20) NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnscheditem` ADD PRIMARY KEY( `scheditemid` );
ALTER TABLE `fnscheditem` CHANGE `scheduleid` `scheduleid` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` ADD `scheduleid` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` CHANGE `dayofweek` `dayofweek` varchar(8) NOT NULL DEFAULT '';
ALTER TABLE `fnscheditem` ADD `dayofweek` varchar(8) NOT NULL DEFAULT '';
ALTER TABLE `fnscheditem` CHANGE `dayofmonth` `dayofmonth` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` ADD `dayofmonth` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` CHANGE `monthofyear` `monthofyear` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` ADD `monthofyear` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` CHANGE `year` `year` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` ADD `year` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` CHANGE `starthour` `starthour` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` ADD `starthour` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` CHANGE `startmin` `startmin` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` ADD `startmin` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnscheditem` CHANGE `finishhour` `finishhour` int(11) NOT NULL DEFAULT '23';
ALTER TABLE `fnscheditem` ADD `finishhour` int(11) NOT NULL DEFAULT '23';
ALTER TABLE `fnscheditem` CHANGE `finishmin` `finishmin` int(11) NOT NULL DEFAULT '59';
ALTER TABLE `fnscheditem` ADD `finishmin` int(11) NOT NULL DEFAULT '59';
-- 
-- Table: fnschedule
-- DESCRIBE fnschedule
ALTER TABLE `fnschedule` CHANGE `scheduleid` `scheduleid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnschedule` ADD `scheduleid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnschedule` ADD PRIMARY KEY( `scheduleid` );
ALTER TABLE `fnschedule` CHANGE `schedulename` `schedulename` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnschedule` ADD `schedulename` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnschedule` CHANGE `defaultaction` `defaultaction` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnschedule` ADD `defaultaction` tinyint(1) NOT NULL DEFAULT '1';
-- 
-- Table: fnsession
-- DESCRIBE fnsession
ALTER TABLE `fnsession` CHANGE `sessionid` `sessionid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnsession` ADD `sessionid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnsession` ADD PRIMARY KEY( `sessionid` );
ALTER TABLE `fnsession` CHANGE `sessionkey` `sessionkey` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnsession` ADD `sessionkey` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnsession` CHANGE `ipaddress` `ipaddress` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnsession` ADD `ipaddress` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnsession` CHANGE `username` `username` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnsession` ADD `username` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnsession` CHANGE `startx` `startx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnsession` ADD `startx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnsession` CHANGE `updatex` `updatex` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnsession` ADD `updatex` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnsession` CHANGE `userlevel` `userlevel` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnsession` ADD `userlevel` int(11) NOT NULL DEFAULT '0';
-- 
-- Table: fntestrun
-- DESCRIBE fntestrun
ALTER TABLE `fntestrun` CHANGE `trid` `trid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fntestrun` ADD `trid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fntestrun` ADD PRIMARY KEY( `trid` );
ALTER TABLE `fntestrun` CHANGE `startx` `startx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fntestrun` ADD `startx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fntestrun` CHANGE `finishx` `finishx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fntestrun` ADD `finishx` bigint(20) unsigned NOT NULL DEFAULT '0';
CREATE INDEX `finishx` ON `fntestrun` ( `finishx` );
ALTER TABLE `fntestrun` CHANGE `routput` `routput` text DEFAULT '';
ALTER TABLE `fntestrun` ADD `routput` text DEFAULT '';
ALTER TABLE `fntestrun` CHANGE `fnode` `fnode` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fntestrun` ADD `fnode` varchar(64) NOT NULL DEFAULT '';
CREATE INDEX `fnode` ON `fntestrun` ( `fnode` );
-- 
-- Table: fnuser
-- DESCRIBE fnuser
ALTER TABLE `fnuser` CHANGE `username` `username` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnuser` ADD `username` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnuser` ADD PRIMARY KEY( `username` );
ALTER TABLE `fnuser` CHANGE `password` `password` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnuser` ADD `password` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnuser` CHANGE `realname` `realname` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnuser` ADD `realname` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnuser` CHANGE `userlevel` `userlevel` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `fnuser` ADD `userlevel` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `fnuser` CHANGE `grouplock` `grouplock` tinyint(4) NOT NULL DEFAULT '0';
ALTER TABLE `fnuser` ADD `grouplock` tinyint(4) NOT NULL DEFAULT '0';
-- 
-- Table: fnview
-- DESCRIBE fnview
ALTER TABLE `fnview` CHANGE `viewid` `viewid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnview` ADD `viewid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnview` ADD PRIMARY KEY( `viewid` );
ALTER TABLE `fnview` CHANGE `vtitle` `vtitle` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnview` ADD `vtitle` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnview` CHANGE `vstyle` `vstyle` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnview` ADD `vstyle` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnview` CHANGE `vpublic` `vpublic` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnview` ADD `vpublic` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnview` CHANGE `vclick` `vclick` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnview` ADD `vclick` varchar(32) NOT NULL DEFAULT '';
ALTER TABLE `fnview` CHANGE `vrefresh` `vrefresh` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnview` ADD `vrefresh` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnview` CHANGE `vlinkv` `vlinkv` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnview` ADD `vlinkv` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnview` CHANGE `vcolumns` `vcolumns` smallint(6) NOT NULL DEFAULT '0';
ALTER TABLE `fnview` ADD `vcolumns` smallint(6) NOT NULL DEFAULT '0';
ALTER TABLE `fnview` CHANGE `vcolon` `vcolon` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnview` ADD `vcolon` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnview` CHANGE `vdashes` `vdashes` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnview` ADD `vdashes` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnview` CHANGE `vtimeago` `vtimeago` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnview` ADD `vtimeago` tinyint(1) NOT NULL DEFAULT '1';
-- 
-- Table: fnviewitem
-- DESCRIBE fnviewitem
ALTER TABLE `fnviewitem` CHANGE `viewitemid` `viewitemid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnviewitem` ADD `viewitemid` bigint(20) unsigned NOT NULL auto_increment DEFAULT '';
ALTER TABLE `fnviewitem` ADD PRIMARY KEY( `viewitemid` );
ALTER TABLE `fnviewitem` CHANGE `viewid` `viewid` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` ADD `viewid` bigint(20) unsigned NOT NULL DEFAULT '0';
CREATE INDEX `viewid` ON `fnviewitem` ( `viewid` );
ALTER TABLE `fnviewitem` CHANGE `itype` `itype` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnviewitem` ADD `itype` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `fnviewitem` CHANGE `ioption` `ioption` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `fnviewitem` ADD `ioption` varchar(250) NOT NULL DEFAULT '';
ALTER TABLE `fnviewitem` CHANGE `icolour` `icolour` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnviewitem` ADD `icolour` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnviewitem` CHANGE `itextstatus` `itextstatus` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` ADD `itextstatus` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` CHANGE `idetail` `idetail` smallint(5) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` ADD `idetail` smallint(5) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` CHANGE `iweight` `iweight` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` ADD `iweight` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` CHANGE `isize` `isize` smallint(6) NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` ADD `isize` smallint(6) NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` CHANGE `igraphic` `igraphic` smallint(6) NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` ADD `igraphic` smallint(6) NOT NULL DEFAULT '0';
ALTER TABLE `fnviewitem` CHANGE `iname` `iname` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `fnviewitem` ADD `iname` varchar(64) NOT NULL DEFAULT '';
-- 
