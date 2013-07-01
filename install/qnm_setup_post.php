<?php

// QNM  2 build:20130410

switch($oDB->type)
{

case 'mysql4':
case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmpost (
  section int NOT NULL default 0,
  id int,
  pclass varchar(2) NOT NULL default "e",
  pid int NOT NULL default 0,
  status int NOT NULL default 0,
  userid int NOT NULL default 0,
  username varchar(24),
  issuedate varchar(20) NOT NULL default "0",
  attach varchar(255),
  textmsg text,
  PRIMARY KEY (id)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmpost (
  section int NOT NULL default 0,
  id int NOT NULL CONSTRAINT pk_'.$qnm_prefix.'qnmpost PRIMARY KEY,
  pclass varchar(2) NOT NULL default "e",
  pid int NOT NULL default 0,
  status int NOT NULL default 0,
  userid int NOT NULL default 0,
  username varchar(24) NULL,
  issuedate varchar(20) NOT NULL default "0",
  attach varchar(255) NULL,
  textmsg text
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmpost (
  section integer NOT NULL default 0,
  id integer,
  pclass varchar(2) NOT NULL default "e",
  pid integer NOT NULL default 0,
  status integer NOT NULL default 0,
  userid integer NOT NULL default 0,
  username varchar(24) NULL,
  issuedate varchar(20) NOT NULL default "0",
  attach varchar(255) NULL,
  textmsg text,
  PRIMARY KEY (id)
  )';
  break;

case 'sqlite':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmpost (
  section integer NOT NULL default 0,
  id integer,
  pclass varchar(2) NOT NULL default "e",
  pid integer NOT NULL default 0,
  status integer NOT NULL default 0,
  userid integer NOT NULL default 0,
  username text,
  issuedate text NOT NULL default "0",
  attach text,
  textmsg text,
  PRIMARY KEY (id)
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmpost (
  section integer default 0,
  id integer,
  pclass varchar(2) default "e",
  pid integer default 0,
  status integer default 0,
  userid integer default 0,
  username varchar(24),
  issuedate varchar(20) default "0",
  attach varchar(255),
  textmsg varchar(32700),
  PRIMARY KEY (id)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmpost (
  section integer NOT NULL default 0,
  id integer NOT NULL,
  pclass varchar(2) NOT NULL default "e",
  pid integer NOT NULL default 0,
  status integer NOT NULL default 0,
  userid integer NOT NULL default 0,
  username varchar(24),
  issuedate varchar(20) NOT NULL default "0",
  attach varchar(255),
  textmsg long varchar,
  textmsg2 varchar(255),
  PRIMARY KEY (id)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmpost (
  section number(32) default 0 NOT NULL,
  id number(32),
  pclass varchar2(2) default "e" NOT NULL,
  pid number(32) default 0 NOT NULL,
  status number(32) default 0 NOT NULL,
  userid number(32) default 0 NOT NULL,
  username varchar2(24),
  issuedate varchar2(15) default "0" NOT NULL,
  attach varchar2(255),
  textmsg varchar2(4000),
  CONSTRAINT pk_'.$qnm_prefix.'qnmpost PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, oci, sqlite, ibase, db2");

}

echo '<span style="color:blue;">';
$b=$oDB->Query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qnm_prefix.'qnmpost',$qnm_database,$qnm_user),'</div>';
  echo '<br/><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qnm_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}