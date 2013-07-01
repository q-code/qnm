<?php

// QNM 1 build:20130410

switch($oDB->type)
{

case 'mysql4':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  section int,
  uid int,
  class varchar(2),
  status int,
  type varchar(24),
  pid int,
  items int,
  conns int,
  links int,
  posts int,
  docs int,
  id varchar(24),
  descr varchar(255),
  address varchar(24),
  tags varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  m decimal(13,2),
  insertdate varchar(8),
  PRIMARY KEY (uid)
  )';
  break;

case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  section int,
  uid int,
  class varchar(2),
  status int,
  pid int,
  items int,
  conns int,
  links int,
  posts int,
  docs int,
  id varchar(24),
  type varchar(24),
  descr varchar(4000),
  address varchar(24),
  tags varchar(4000),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  m decimal(13,2),
  insertdate varchar(8),
  PRIMARY KEY (uid)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  section int,
  uid int NOT NULL CONSTRAINT pk_'.$qnm_prefix.'qnmelement PRIMARY KEY,
  class varchar(2) NULL,
  status int NULL,
  pid int NULL,
  items int NULL,
  conns int NULL,
  links int NULL,
  posts int NULL,
  docs int NULL,
  id varchar(24) NULL,
  type varchar(24) NULL,
  descr varchar(4000) NULL,
  address varchar(24) NULL,
  tags varchar(4000) NULL,
  x decimal(13,10) NULL,
  y decimal(13,10) NULL,
  z decimal(13,2) NULL,
  m decimal(13,2) NULL,
  insertdate varchar(8) NULL
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  section integer,
  uid integer,
  class varchar(2) NULL,
  status integer NULL,
  pid integer NULL,
  items integer NULL,
  conns integer NULL,
  links integer NULL,
  posts integer NULL,
  docs integer NULL,
  id varchar(24) NULL,
  type varchar(24) NULL,
  descr varchar(4000) NULL,
  address varchar(24) NULL,
  tags varchar(4000) NULL,
  x decimal(13,10) NULL,
  y decimal(13,10) NULL,
  z decimal(13,2) NULL,
  m decimal(13,2) NULL,
  insertdate varchar(8) NULL,
  PRIMARY KEY (uid)
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  section integer default NULL,
  uid integer,
  class varchar(2) default NULL,
  status integer default NULL,
  pid integer default NULL,
  items integer default NULL,
  conns integer default NULL,
  links integer default NULL,
  posts integer default NULL,
  docs integer default NULL,
  id varchar(24) default NULL,
  type varchar(24) default NULL,
  descr varchar(4000) default NULL,
  address varchar(24) default NULL,
  tags varchar(4000) default NULL,
  x decimal(13,10) default NULL,
  y decimal(13,10) default NULL,
  z decimal(13,2) default NULL,
  m decimal(13,2) default NULL,
  insertdate varchar(8) default NULL,
  PRIMARY KEY (uid)
  )';
  break;

case 'sqlite':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  section integer,
  uid integer,
  class text,
  status integer,
  pid integer,
  items integer,
  conns integer,
  links integer,
  posts integer,
  docs integer,
  id text,
  type text,
  descr text,
  address text,
  tags text,
  x real,
  y real,
  z real,
  m real,
  insertdate text,
  PRIMARY KEY (uid)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  section integer,
  uid integer,
  class varchar(2),
  status integer,
  pid integer,
  items integer,
  conns integer,
  links integer,
  posts integer,
  docs integer,
  id varchar(24),
  type varchar(24),
  descr varchar(255),
  address varchar(24),
  tags varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  m decimal(13,2),
  insertdate varchar(8),
  PRIMARY KEY (uid)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmelement (
  section int,
  uid int,
  class varchar2(2),
  status int,
  pid int,
  items int,
  conns int,
  links int,
  posts int,
  docs int,
  id varchar2(24),
  type varchar2(24),
  descr varchar2(4000),
  address varchar2(24),
  tags varchar2(4000),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  m decimal(13,2),
  insertdate varchar2(8),
  CONSTRAINT pk_'.$qnm_prefix.'qnmelement PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, oci, sqlite, ibase, db2");

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

$strQ='INSERT INTO '.$qnm_prefix.'qnmelement (section,uid,class,status,pid,items,conns,links,posts,docs,id,descr,address) VALUES (0,0,"e",1,0,0,0,0,0,0,"Network","The entire network","")';
$result=$oDB->Query($strQ);