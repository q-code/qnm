<?php

// QNM 1 build:20130410

switch($oDB->type)
{

case 'mysql4':
case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsection (
  uid int,
  pid int NOT NULL default 0,
  status int NOT NULL default 0,
  type char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 255,
  moderator int NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),
  options varchar(255),
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1),
  PRIMARY KEY (uid)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsection (
  uid int NOT NULL CONSTRAINT pk_'.$qnm_prefix.'qnmsection PRIMARY KEY,
  pid int NOT NULL default 0,
  status int NOT NULL default 0,
  type char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0,
  moderator int NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),
  options varchar(255),
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1) NULL,
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsection (
  uid integer,
  pid integer NOT NULL default 0,
  status integer NOT NULL default 0,
  type char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 255,
  moderator integer NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),
  options varchar(255),
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1) NULL,
  PRIMARY KEY (uid)
  )';
  break;

case 'sqlite':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsection (
  uid integer,
  pid integer NOT NULL default 0,
  status integer NOT NULL default 0,
  type text NOT NULL default "0",
  notify text NOT NULL default "1",
  title text NOT NULL default "untitled",
  titleorder integer NOT NULL default 255,
  moderator integer NOT NULL default 0,
  moderatorname text NOT NULL default "Administrator",
  stats text,
  options text,
  numfield text NOT NULL default " ",
  titlefield text NOT NULL default "0",
  wisheddate text NOT NULL default "0",
  alternate text NOT NULL default "0",
  prefix text,
  PRIMARY KEY (uid)
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsection (
  uid integer,
  pid integer default 0,
  status integer default 0,
  type char(1) default "0",
  notify char(1) default "1",
  title varchar(64) default "untitled",
  titleorder integer default 255,
  moderator integer default 0,
  moderatorname varchar(24) default "Administrator",
  stats varchar(255),
  options varchar(255),
  numfield varchar(24) default " ",
  titlefield char(1) default "0",
  wisheddate char(1) default "0",
  alternate char(1) default "0",
  prefix char(1),
  PRIMARY KEY (uid)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsection (
  uid integer NOT NULL,
  pid integer NOT NULL default 0,
  status integer NOT NULL default 0,
  type char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 255,
  moderator integer NOT NULL default 0,
  moderatorname varchar(24) NOT NULL default "Administrator",
  stats varchar(255),
  options varchar(255),
  numfield varchar(24) NOT NULL default " ",
  titlefield char(1) NOT NULL default "0",
  wisheddate char(1) NOT NULL default "0",
  alternate char(1) NOT NULL default "0",
  prefix char(1),
  PRIMARY KEY (uid)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsection (
  uid number(32),
  pid number(32) default 0 NOT NULL,
  status number(32) default 0 NOT NULL,
  type char(1) default "0" NOT NULL,
  notify char(1) default "1" NOT NULL,
  title varchar2(64) default "untitled" NOT NULL,
  titleorder number(32) default 255 NOT NULL,
  moderator number(32) default 0 NOT NULL,
  moderatorname varchar2(24) default "Administrator" NOT NULL,
  stats varchar2(255),
  options varchar(255),
  numfield varchar2(24) default " " NOT NULL,
  titlefield char(1) default "0" NOT NULL,
  wisheddate char(1) default "0" NOT NULL,
  alternate char(1) default "0" NOT NULL,
  prefix char(1),
  CONSTRAINT pk_'.$qnm_prefix.'qnmsection PRIMARY KEY (uid))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, ibase, db2, oci");

}

echo '<span style="color:blue;">';
$b=$oDB->Query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qnm_prefix.'qnmsection',$qnm_database,$qnm_user),'</div>';
  echo '<br/><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qnm_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}

$strQ='INSERT INTO '.$qnm_prefix.'qnmsection (
uid,pid,status,type,notify,title,titleorder,moderator,moderatorname,stats,options,numfield,titlefield,wisheddate,alternate,prefix)
VALUES (0,0,0,"1","0","Admin section",0,0,"Admin","","logo=0","T-%03s","0","0","0","a")';

$oDB->Query($strQ);