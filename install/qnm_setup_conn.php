<?php

// QNM 1 build:20130410

switch($oDB->type)
{

case 'mysql4':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmconn (
  section int,
  uid int,
  pid int,
  class varchar(2),
  status int,
  id varchar(24),
  type varchar(24),
  descr varchar(255),
  address varchar(24),
  tags varchar(255),
  insertdate varchar(8),
  PRIMARY KEY (uid)
  )';
  break;

case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmconn (
  section int,
  uid int,
  pid int,
  class varchar(2),
  status int,
  id varchar(24),
  type varchar(24),
  descr varchar(4000),
  address varchar(24),
  tags varchar(4000),
  insertdate varchar(8),
  PRIMARY KEY (uid)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmconn (
  section int,
  uid int NOT NULL CONSTRAINT pk_'.$qnm_prefix.'qnmconn PRIMARY KEY,
  pid int NULL,
  class varchar(2) NULL,
  status int NULL,
  id varchar(24) NULL,
  type varchar(24) NULL,
  descr varchar(4000) NULL,
  address varchar(24) NULL,
  tags varchar(4000) NULL,
  insertdate varchar(8) NULL
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmconn (
  section integer,
  uid integer,
  pid integer NULL,
  class varchar(2) NULL,
  status integer NULL,
  id varchar(24) NULL,
  type varchar(24) NULL,
  descr varchar(4000) NULL,
  address varchar(24) NULL,
  tags varchar(4000) NULL,
  insertdate varchar(8) NULL,
  PRIMARY KEY (uid)
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmconn (
  section integer default NULL,
  uid integer,
  pid integer default NULL,
  class varchar(2) default NULL,
  status integer default NULL,
  id varchar(24) default NULL,
  type varchar(24) default NULL,
  descr varchar(4000) default NULL,
  address varchar(24) default NULL,
  tags varchar(4000) default NULL,
  insertdate varchar(8) default NULL,
  PRIMARY KEY (uid)
  )';
  break;

case 'sqlite':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmconn (
  section integer,
  uid integer,
  pid integer,
  class text,
  status integer,
  id text,
  type text,
  descr text,
  address text,
  tags text,
  insertdate text,
  PRIMARY KEY (uid)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmconn (
  section integer,
  uid integer,
  pid integer,
  class varchar(2),
  status integer,
  id varchar(24),
  type varchar(24),
  descr varchar(255),
  address varchar(24),
  tags varchar(255),
  insertdate varchar(8),
  PRIMARY KEY (uid)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmconn (
  section int,
  uid int,
  pid int,
  class varchar2(2),
  status int,
  id varchar2(24),
  type varchar2(24),
  descr varchar2(4000),
  address varchar2(24),
  tags varchar2(4000),
  insertdate varchar2(8),
  CONSTRAINT pk_'.$qnm_prefix.'qnmconn PRIMARY KEY (uid))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite  oci, ibase, db2");

}

echo '<span style="color:blue;">';
$b=$oDB->Query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qnm_prefix.'qnmconn',$qnm_database,$qnm_user),'</div>';
  echo '<br/><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qnm_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}

$strQ='INSERT INTO '.$qnm_prefix.'qnmconn (section,uid,pid,class,status,id,descr,address,tags) VALUES (0,0,0,"c",1,"None","None","","")';
$result=$oDB->Query($strQ);