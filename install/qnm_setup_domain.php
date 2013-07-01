<?php

// QNM  2 build:20130410

switch($oDB->type)
{

case 'mysql4':
case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdomain (
  uid int,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0,
  PRIMARY KEY (uid)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdomain (
  uid int NOT NULL CONSTRAINT pk_'.$qnm_prefix.'qnmdomain PRIMARY KEY,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdomain (
  uid integer,
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 0,
  PRIMARY KEY (uid)
  )';
  break;

case 'sqlit':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdomain (
  uid integer,
  title text NOT NULL default "untitled",
  titleorder integer NOT NULL default 0,
  PRIMARY KEY (uid)
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdomain (
  uid integer,
  title varchar(64) default "untitled",
  titleorder integer default 0,
  PRIMARY KEY (uid)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdomain (
  uid integer NOT NULL,
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 0,
  PRIMARY KEY (uid)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdomain (
  uid number(32),
  title varchar2(64) default "untitled" NOT NULL,
  titleorder number(32) default 0 NOT NULL,
  CONSTRAINT pk_'.$qnm_prefix.'qnmdomain PRIMARY KEY (uid))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, firebird, db2, oci");

}

echo '<span style="color:blue;">';
$b=$oDB->Query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qnm_prefix.'qnmdomain',$qnm_database,$qnm_user),'</div>';
  echo '<br/><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qnm_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}

$strQ='INSERT INTO '.$qnm_prefix.'qnmdomain (uid,title,titleorder) VALUES (0,"Admin hidden domain",0)';
$result=$oDB->Query($strQ);