<?php

// QNM  2 build:20130410

switch($oDB->type)
{

case 'mysql4':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  id int,
  numid int NOT NULL default 0,
  section int NOT NULL default 0,
  type char(1) NOT NULL default "T",
  status char(1) NOT NULL default "0",
  statusdate varchar(20) NOT NULL default "0",
  wisheddate varchar(20) NOT NULL default "0",
  tags varchar(255),
  firstpostid int NOT NULL default 0,
  lastpostid int NOT NULL default 0,
  firstpostuser int NOT NULL default 0,
  lastpostuser int NOT NULL default 0,
  firstpostname varchar(24),
  lastpostname varchar(24),
  firstpostdate varchar(20) NOT NULL default "0",
  lastpostdate varchar(20) NOT NULL default "0",
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  actorid int,
  actorname varchar(24),
  notifiedid int,
  notifiedname varchar(24),
  replies int NOT NULL default 0,
  views int NOT NULL default 0,
  modifdate varchar(20) NOT NULL default "0",
  param varchar(255),
  PRIMARY KEY (id)
  )';
  break;

case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  id int,
  numid int NOT NULL default 0,
  section int NOT NULL default 0,
  type char(1) NOT NULL default "T",
  status char(1) NOT NULL default "0",
  statusdate varchar(20) NOT NULL default "0",
  wisheddate varchar(20) NOT NULL default "0",
  tags varchar(4000),
  firstpostid int NOT NULL default 0,
  lastpostid int NOT NULL default 0,
  firstpostuser int NOT NULL default 0,
  lastpostuser int NOT NULL default 0,
  firstpostname varchar(24),
  lastpostname varchar(24),
  firstpostdate varchar(20) NOT NULL default "0",
  lastpostdate varchar(20) NOT NULL default "0",
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  actorid int,
  actorname varchar(24),
  notifiedid int,
  notifiedname varchar(24),
  replies int NOT NULL default 0,
  views int NOT NULL default 0,
  modifdate varchar(20) NOT NULL default "0",
  param varchar(255),
  PRIMARY KEY (id)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  id int NOT NULL CONSTRAINT pk_'.$qnm_prefix.'qnmelement PRIMARY KEY,
  numid int NOT NULL default 0,
  section int NOT NULL default 0,
  type char(1) NOT NULL default "T",
  status char(1) NOT NULL default "0",
  statusdate varchar(20) NOT NULL default "0",
  wisheddate varchar(20) NOT NULL default "0",
  tags varchar(4000),
  firstpostid int NOT NULL default 0,
  lastpostid int NOT NULL default 0,
  firstpostuser int NOT NULL default 0,
  lastpostuser int NOT NULL default 0,
  firstpostname varchar(24) NULL,
  lastpostname varchar(24) NULL,
  firstpostdate varchar(20) NOT NULL default "0",
  lastpostdate varchar(20) NOT NULL default "0",
  actorid int NULL,
  actorname varchar(24) NULL,
  notifiedid int NULL,
  notifiedname varchar(24) NULL,
  x decimal(13,10) NULL,
  y decimal(13,10) NULL,
  z decimal(13,2) NULL,
  replies int NOT NULL default 0,
  views int NOT NULL default 0,
  modifdate varchar(20) NOT NULL default "0",
  param varchar(255) NULL
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  id integer,
  numid integer NOT NULL default 0,
  section integer NOT NULL default 0,
  type char(1) NOT NULL default "T",
  status char(1) NOT NULL default "0",
  statusdate varchar(20) NOT NULL default "0",
  wisheddate varchar(20) NOT NULL default "0",
  tags varchar(4000) NULL,
  firstpostid integer NOT NULL default 0,
  lastpostid integer NOT NULL default 0,
  firstpostuser integer NOT NULL default 0,
  lastpostuser integer NOT NULL default 0,
  firstpostname varchar(24) NULL,
  lastpostname varchar(24) NULL,
  firstpostdate varchar(20) NOT NULL default "0",
  lastpostdate varchar(20) NOT NULL default "0",
  actorid integer NULL,
  actorname varchar(24) NULL,
  notifiedid integer NULL,
  notifiedname varchar(24) NULL,
  x decimal(13,10) NULL,
  y decimal(13,10) NULL,
  z decimal(13,2) NULL,
  replies integer NOT NULL default 0,
  views integer NOT NULL default 0,
  modifdate varchar(20) NOT NULL default "0",
  param varchar(255) NULL,
  PRIMARY KEY (id)
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  id integer,
  numid integer default 0,
  section integer default 0,
  type char(1) default "T",
  status char(1) default "0",
  statusdate varchar(20) default "0",
  wisheddate varchar(20) default "0",
  tags varchar(4000),
  firstpostid integer default 0,
  lastpostid integer default 0,
  firstpostuser integer default 0,
  lastpostuser integer default 0,
  firstpostname varchar(24) default NULL,
  lastpostname varchar(24) default NULL,
  firstpostdate varchar(20) default "0",
  lastpostdate varchar(20) default "0",
  actorid integer default NULL,
  actorname varchar(24) default NULL,
  notifiedid integer default NULL,
  notifiedname varchar(24) default NULL,
  x decimal(13,10) default NULL,
  y decimal(13,10) default NULL,
  z decimal(13,2) default NULL,
  replies integer default 0,
  views integer default 0,
  modifdate varchar(20) default "0",
  param varchar(255) default NULL,
  PRIMARY KEY (id)
  )';
  break;

case 'sqlite':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  id integer,
  numid integer NOT NULL default 0,
  section integer NOT NULL default 0,
  type text NOT NULL default "T",
  status text NOT NULL default "0",
  statusdate text default "0",
  wisheddate text default "0",
  tags text,
  firstpostid integer NOT NULL default 0,
  lastpostid integer NOT NULL default 0,
  firstpostuser integer NOT NULL default 0,
  lastpostuser integer NOT NULL default 0,
  firstpostname text,
  lastpostname text,
  firstpostdate text default "0",
  lastpostdate text default "0",
  actorid integer,
  actorname text,
  notifiedid integer,
  notifiedname text,
  x real,
  y real,
  z real,
  replies integer NOT NULL default 0,
  views integer NOT NULL default 0,
  modifdate text default "0",
  param text,
  PRIMARY KEY (id)
  )';
  break;

case 'access':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  id int CONSTRAINT pk_'.$qnm_prefix.'qnmelement PRIMARY KEY,
  numid int,
  section int,
  type char(1),
  status char(1),
  statusdate varchar(20),
  wisheddate varchar(20),
  tags varchar(255),
  firstpostid int,
  lastpostid int,
  firstpostuser int,
  lastpostuser int,
  firstpostname varchar(24),
  lastpostname varchar(24),
  firstpostdate varchar(20),
  lastpostdate varchar(20),
  actorid int,
  actorname varchar(24),
  notifiedid int,
  notifiedname varchar(24),
  x float,
  y float,
  z float,
  replies int,
  views int,
  modifdate varchar(20),
  param varchar(255)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  id integer NOT NULL,
  numid integer NOT NULL default 0,
  section integer NOT NULL default 0,
  type char(1) NOT NULL default "T",
  status char(1) NOT NULL default "0",
  statusdate varchar(20) NOT NULL default "0",
  wisheddate varchar(20) NOT NULL default "0",
  tags varchar(4000),
  firstpostid integer NOT NULL default 0,
  lastpostid integer NOT NULL default 0,
  firstpostuser integer NOT NULL default 0,
  lastpostuser integer NOT NULL default 0,
  firstpostname varchar(24),
  lastpostname varchar(24),
  firstpostdate varchar(20) NOT NULL default "0",
  lastpostdate varchar(20) NOT NULL default "0",
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  actorid int,
  actorname varchar(24),
  notifiedid int,
  notifiedname varchar(24),
  replies integer NOT NULL default 0,
  views integer NOT NULL default 0,
  modifdate varchar(20) NOT NULL default "0",
  param varchar(255),
  PRIMARY KEY (id)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  id number(32),
  numid number(32) default 0 NOT NULL,
  section number(32) default 0 NOT NULL,
  type char(1) default "T" NOT NULL,
  status char(1) default "0" NOT NULL,
  statusdate varchar2(20) default "0" NOT NULL,
  wisheddate varchar2(20) default "0" NOT NULL,
  tags varchar2(4000),
  firstpostid number(32) default 0 NOT NULL,
  lastpostid number(32) default 0 NOT NULL,
  firstpostuser number(32) default 0 NOT NULL,
  lastpostuser number(32) default 0 NOT NULL,
  firstpostname varchar2(24),
  lastpostname varchar2(24),
  firstpostdate varchar2(20) default "0" NOT NULL,
  lastpostdate varchar2(20) default "0" NOT NULL,
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  actorid int,
  actorname varchar2(24),
  notifiedid int,
  notifiedname varchar2(24),
  replies number(32) default 0 NOT NULL,
  views number(32) default 0 NOT NULL,
  modifdate varchar2(20) default "0" NOT NULL,
  param varchar2(255),
  CONSTRAINT pk_'.$qnm_prefix.'qnmelement PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, mssql, pg, oracle, sqlite, firebird, db2 or access");

}

echo '<span style="color:blue;">';
$b=$oDB->Query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qnm_prefix.'qnmelement',$qnm_database,$qnm_user),'</div>';
  echo '<br/><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qnm_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}