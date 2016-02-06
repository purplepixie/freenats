-- Both will generate many many errors - run with --force, ignore errors
-- dbupg.sh -- PurplePixie Systems
-- 
-- SHOW TABLES LIKE "fn%"
-- Table: fnalert
-- DESCRIBE fnalert
ALTER TABLE `fnalert` CHANGE `alertid` `alertid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnalert` ADD `alertid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnalert` ADD PRIMARY KEY( `alertid` );
ALTER TABLE `fnalert` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL;
ALTER TABLE `fnalert` ADD `nodeid` varchar(64) NOT NULL;
ALTER TABLE `fnalert` CHANGE `alertlevel` `alertlevel` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalert` ADD `alertlevel` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalert` CHANGE `openedx` `openedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalert` ADD `openedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalert` CHANGE `closedx` `closedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalert` ADD `closedx` bigint(20) unsigned NOT NULL DEFAULT '0';
-- 
-- Table: fnalertaction
-- DESCRIBE fnalertaction
ALTER TABLE `fnalertaction` CHANGE `aaid` `aaid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnalertaction` ADD `aaid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnalertaction` ADD PRIMARY KEY( `aaid` );
ALTER TABLE `fnalertaction` CHANGE `atype` `atype` varchar(32) NOT NULL;
ALTER TABLE `fnalertaction` ADD `atype` varchar(32) NOT NULL;
ALTER TABLE `fnalertaction` CHANGE `efrom` `efrom` varchar(250) NOT NULL;
ALTER TABLE `fnalertaction` ADD `efrom` varchar(250) NOT NULL;
ALTER TABLE `fnalertaction` CHANGE `etolist` `etolist` text NOT NULL;
ALTER TABLE `fnalertaction` ADD `etolist` text NOT NULL;
ALTER TABLE `fnalertaction` CHANGE `esubject` `esubject` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `esubject` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `etype` `etype` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `etype` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `awarnings` `awarnings` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `awarnings` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `adecrease` `adecrease` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `adecrease` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `mdata` `mdata` text NOT NULL;
ALTER TABLE `fnalertaction` ADD `mdata` text NOT NULL;
ALTER TABLE `fnalertaction` CHANGE `aname` `aname` varchar(120) NOT NULL;
ALTER TABLE `fnalertaction` ADD `aname` varchar(120) NOT NULL;
ALTER TABLE `fnalertaction` CHANGE `ctrdate` `ctrdate` varchar(8) NOT NULL;
ALTER TABLE `fnalertaction` ADD `ctrdate` varchar(8) NOT NULL;
ALTER TABLE `fnalertaction` CHANGE `ctrlimit` `ctrlimit` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `ctrlimit` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` CHANGE `ctrtoday` `ctrtoday` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertaction` ADD `ctrtoday` int(10) unsigned NOT NULL DEFAULT '0';
-- 
-- Table: fnalertlog
-- DESCRIBE fnalertlog
ALTER TABLE `fnalertlog` CHANGE `alid` `alid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnalertlog` ADD `alid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnalertlog` ADD PRIMARY KEY( `alid` );
ALTER TABLE `fnalertlog` CHANGE `alertid` `alertid` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertlog` ADD `alertid` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertlog` CHANGE `postedx` `postedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertlog` ADD `postedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnalertlog` CHANGE `logentry` `logentry` varchar(250) NOT NULL;
ALTER TABLE `fnalertlog` ADD `logentry` varchar(250) NOT NULL;
-- 
-- Table: fnconfig
-- DESCRIBE fnconfig
ALTER TABLE `fnconfig` CHANGE `fnc_var` `fnc_var` varchar(64) NOT NULL;
ALTER TABLE `fnconfig` ADD `fnc_var` varchar(64) NOT NULL;
ALTER TABLE `fnconfig` ADD PRIMARY KEY( `fnc_var` );
ALTER TABLE `fnconfig` CHANGE `fnc_val` `fnc_val` varchar(64) NOT NULL;
ALTER TABLE `fnconfig` ADD `fnc_val` varchar(64) NOT NULL;
-- 
-- Table: fneval
-- DESCRIBE fneval
ALTER TABLE `fneval` CHANGE `evalid` `evalid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fneval` ADD `evalid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fneval` ADD PRIMARY KEY( `evalid` );
ALTER TABLE `fneval` CHANGE `testid` `testid` varchar(128) NOT NULL;
ALTER TABLE `fneval` ADD `testid` varchar(128) NOT NULL;
ALTER TABLE `fneval` CHANGE `weight` `weight` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fneval` ADD `weight` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fneval` CHANGE `eoperator` `eoperator` varchar(32) NOT NULL;
ALTER TABLE `fneval` ADD `eoperator` varchar(32) NOT NULL;
ALTER TABLE `fneval` CHANGE `evalue` `evalue` varchar(128) NOT NULL;
ALTER TABLE `fneval` ADD `evalue` varchar(128) NOT NULL;
ALTER TABLE `fneval` CHANGE `eoutcome` `eoutcome` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fneval` ADD `eoutcome` int(11) NOT NULL DEFAULT '0';
-- 
-- Table: fngroup
-- DESCRIBE fngroup
ALTER TABLE `fngroup` CHANGE `groupid` `groupid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fngroup` ADD `groupid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fngroup` ADD PRIMARY KEY( `groupid` );
ALTER TABLE `fngroup` CHANGE `groupname` `groupname` varchar(128) NOT NULL;
ALTER TABLE `fngroup` ADD `groupname` varchar(128) NOT NULL;
ALTER TABLE `fngroup` CHANGE `groupdesc` `groupdesc` varchar(250) NOT NULL;
ALTER TABLE `fngroup` ADD `groupdesc` varchar(250) NOT NULL;
ALTER TABLE `fngroup` CHANGE `groupicon` `groupicon` varchar(64) NOT NULL;
ALTER TABLE `fngroup` ADD `groupicon` varchar(64) NOT NULL;
ALTER TABLE `fngroup` CHANGE `weight` `weight` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fngroup` ADD `weight` int(10) unsigned NOT NULL DEFAULT '0';
-- 
-- Table: fngrouplink
-- DESCRIBE fngrouplink
ALTER TABLE `fngrouplink` CHANGE `glid` `glid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fngrouplink` ADD `glid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fngrouplink` ADD PRIMARY KEY( `glid` );
ALTER TABLE `fngrouplink` CHANGE `groupid` `groupid` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fngrouplink` ADD `groupid` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fngrouplink` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL;
ALTER TABLE `fngrouplink` ADD `nodeid` varchar(64) NOT NULL;
-- 
-- Table: fnlocaltest
-- DESCRIBE fnlocaltest
ALTER TABLE `fnlocaltest` CHANGE `localtestid` `localtestid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnlocaltest` ADD `localtestid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnlocaltest` ADD PRIMARY KEY( `localtestid` );
ALTER TABLE `fnlocaltest` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL;
ALTER TABLE `fnlocaltest` ADD `nodeid` varchar(64) NOT NULL;
ALTER TABLE `fnlocaltest` CHANGE `alertlevel` `alertlevel` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `fnlocaltest` ADD `alertlevel` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `fnlocaltest` CHANGE `lastrunx` `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` ADD `lastrunx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` CHANGE `testtype` `testtype` varchar(128) NOT NULL;
ALTER TABLE `fnlocaltest` ADD `testtype` varchar(128) NOT NULL;
ALTER TABLE `fnlocaltest` CHANGE `testparam` `testparam` varchar(250);
ALTER TABLE `fnlocaltest` ADD `testparam` varchar(250);
ALTER TABLE `fnlocaltest` CHANGE `testrecord` `testrecord` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` ADD `testrecord` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnlocaltest` CHANGE `simpleeval` `simpleeval` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnlocaltest` ADD `simpleeval` tinyint(1) NOT NULL DEFAULT '1';
-- 
-- Table: fnlog
-- DESCRIBE fnlog
ALTER TABLE `fnlog` CHANGE `logid` `logid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnlog` ADD `logid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnlog` ADD PRIMARY KEY( `logid` );
ALTER TABLE `fnlog` CHANGE `postedx` `postedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlog` ADD `postedx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnlog` CHANGE `modid` `modid` varchar(32) NOT NULL;
ALTER TABLE `fnlog` ADD `modid` varchar(32) NOT NULL;
ALTER TABLE `fnlog` CHANGE `catid` `catid` varchar(32) NOT NULL;
ALTER TABLE `fnlog` ADD `catid` varchar(32) NOT NULL;
ALTER TABLE `fnlog` CHANGE `username` `username` varchar(64) NOT NULL;
ALTER TABLE `fnlog` ADD `username` varchar(64) NOT NULL;
ALTER TABLE `fnlog` CHANGE `loglevel` `loglevel` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `fnlog` ADD `loglevel` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `fnlog` CHANGE `logevent` `logevent` varchar(250) NOT NULL;
ALTER TABLE `fnlog` ADD `logevent` varchar(250) NOT NULL;
-- 
-- Table: fnnalink
-- DESCRIBE fnnalink
ALTER TABLE `fnnalink` CHANGE `nalid` `nalid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnnalink` ADD `nalid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnnalink` ADD PRIMARY KEY( `nalid` );
ALTER TABLE `fnnalink` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL;
ALTER TABLE `fnnalink` ADD `nodeid` varchar(64) NOT NULL;
ALTER TABLE `fnnalink` CHANGE `aaid` `aaid` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnalink` ADD `aaid` bigint(20) unsigned NOT NULL DEFAULT '0';
-- 
-- Table: fnnode
-- DESCRIBE fnnode
ALTER TABLE `fnnode` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL;
ALTER TABLE `fnnode` ADD `nodeid` varchar(64) NOT NULL;
ALTER TABLE `fnnode` ADD PRIMARY KEY( `nodeid` );
ALTER TABLE `fnnode` CHANGE `nodename` `nodename` varchar(128) NOT NULL;
ALTER TABLE `fnnode` ADD `nodename` varchar(128) NOT NULL;
ALTER TABLE `fnnode` CHANGE `nodedesc` `nodedesc` varchar(254) NOT NULL;
ALTER TABLE `fnnode` ADD `nodedesc` varchar(254) NOT NULL;
ALTER TABLE `fnnode` CHANGE `hostname` `hostname` varchar(254) NOT NULL;
ALTER TABLE `fnnode` ADD `hostname` varchar(254) NOT NULL;
ALTER TABLE `fnnode` CHANGE `nodeenabled` `nodeenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `nodeenabled` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `pingtest` `pingtest` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `pingtest` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `pingfatal` `pingfatal` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `pingfatal` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `alertlevel` `alertlevel` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `fnnode` ADD `alertlevel` int(11) NOT NULL DEFAULT '-1';
ALTER TABLE `fnnode` CHANGE `nodeicon` `nodeicon` varchar(64) NOT NULL;
ALTER TABLE `fnnode` ADD `nodeicon` varchar(64) NOT NULL;
ALTER TABLE `fnnode` CHANGE `weight` `weight` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` ADD `weight` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnnode` CHANGE `nodealert` `nodealert` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `fnnode` ADD `nodealert` tinyint(1) NOT NULL DEFAULT '1';
-- 
-- Table: fnrecord
-- DESCRIBE fnrecord
ALTER TABLE `fnrecord` CHANGE `recordid` `recordid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnrecord` ADD `recordid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnrecord` ADD PRIMARY KEY( `recordid` );
ALTER TABLE `fnrecord` CHANGE `testid` `testid` varchar(128) NOT NULL;
ALTER TABLE `fnrecord` ADD `testid` varchar(128) NOT NULL;
ALTER TABLE `fnrecord` CHANGE `alertlevel` `alertlevel` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` ADD `alertlevel` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` CHANGE `testvalue` `testvalue` float NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` ADD `testvalue` float NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` CHANGE `recordx` `recordx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` ADD `recordx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnrecord` CHANGE `nodeid` `nodeid` varchar(64) NOT NULL;
ALTER TABLE `fnrecord` ADD `nodeid` varchar(64) NOT NULL;
-- 
-- Table: fnsession
-- DESCRIBE fnsession
ALTER TABLE `fnsession` CHANGE `sessionid` `sessionid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnsession` ADD `sessionid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fnsession` ADD PRIMARY KEY( `sessionid` );
ALTER TABLE `fnsession` CHANGE `sessionkey` `sessionkey` varchar(128) NOT NULL;
ALTER TABLE `fnsession` ADD `sessionkey` varchar(128) NOT NULL;
ALTER TABLE `fnsession` CHANGE `ipaddress` `ipaddress` varchar(128) NOT NULL;
ALTER TABLE `fnsession` ADD `ipaddress` varchar(128) NOT NULL;
ALTER TABLE `fnsession` CHANGE `username` `username` varchar(64) NOT NULL;
ALTER TABLE `fnsession` ADD `username` varchar(64) NOT NULL;
ALTER TABLE `fnsession` CHANGE `startx` `startx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnsession` ADD `startx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnsession` CHANGE `updatex` `updatex` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnsession` ADD `updatex` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fnsession` CHANGE `userlevel` `userlevel` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `fnsession` ADD `userlevel` int(11) NOT NULL DEFAULT '0';
-- 
-- Table: fntestrun
-- DESCRIBE fntestrun
ALTER TABLE `fntestrun` CHANGE `trid` `trid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fntestrun` ADD `trid` bigint(20) unsigned NOT NULL auto_increment;
ALTER TABLE `fntestrun` ADD PRIMARY KEY( `trid` );
ALTER TABLE `fntestrun` CHANGE `startx` `startx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fntestrun` ADD `startx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fntestrun` CHANGE `finishx` `finishx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fntestrun` ADD `finishx` bigint(20) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `fntestrun` CHANGE `routput` `routput` text NOT NULL;
ALTER TABLE `fntestrun` ADD `routput` text NOT NULL;
ALTER TABLE `fntestrun` CHANGE `fnode` `fnode` varchar(64) NOT NULL;
ALTER TABLE `fntestrun` ADD `fnode` varchar(64) NOT NULL;
-- 
-- Table: fnuser
-- DESCRIBE fnuser
ALTER TABLE `fnuser` CHANGE `username` `username` varchar(64) NOT NULL;
ALTER TABLE `fnuser` ADD `username` varchar(64) NOT NULL;
ALTER TABLE `fnuser` CHANGE `password` `password` varchar(64) NOT NULL;
ALTER TABLE `fnuser` ADD `password` varchar(64) NOT NULL;
ALTER TABLE `fnuser` CHANGE `realname` `realname` varchar(128) NOT NULL;
ALTER TABLE `fnuser` ADD `realname` varchar(128) NOT NULL;
ALTER TABLE `fnuser` CHANGE `userlevel` `userlevel` int(11) NOT NULL DEFAULT '1';
ALTER TABLE `fnuser` ADD `userlevel` int(11) NOT NULL DEFAULT '1';
-- 
