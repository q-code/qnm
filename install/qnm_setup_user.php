<?php

// QNM 1 build:20130410

switch($oDB->type)
{

case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmuser (
  id int,
  name varchar(24) NOT NULL UNIQUE,
  closed char(1) NOT NULL default "0",
  role char(1) NOT NULL default "V",
  pwd varchar(40),
  www varchar(64),
  mail varchar(64),
  phone varchar(64),
  privacy char(1) NOT NULL default "1",
  location varchar(24),
  numpost int,
  signature varchar(255),
  photo varchar(24),
  children char(1) NOT NULL default "0",
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  stats varchar(255),
  PRIMARY KEY (id)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmuser (
  id int NOT NULL CONSTRAINT pk_'.$qnm_prefix.'qnmuser PRIMARY KEY,
  name varchar(24) NOT NULL CONSTRAINT uk_'.$qnm_prefix.'qnmuser UNIQUE,
  closed char(1) NOT NULL default "0",
  role char(1) NOT NULL default "V",
  pwd varchar(40) NULL,
  www varchar(64) NULL,
  mail varchar(64) NULL,
  phone varchar(64) NULL,
  privacy char(1) NOT NULL default "1",
  location varchar(24) NULL,
  numpost int NULL,
  signature varchar(255) NULL,
  photo varchar(24) NULL,
  children char(1) NOT NULL default "0",
  secret_q varchar(255) NULL,
  secret_a varchar(255) NULL,
  x decimal(13,10) NULL,
  y decimal(13,10) NULL,
  z decimal(13,2) NULL,
  stats varchar(255) NULL
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmuser (
  id integer,
  name varchar(24) UNIQUE,
  closed char(1) NOT NULL default "0",
  role char(1) NOT NULL default "V",
  pwd varchar(40),
  www varchar(64),
  mail varchar(64),
  phone varchar(64),
  privacy char(1) NOT NULL default "1",
  location varchar(24),
  numpost integer,
  signature varchar(255),
  photo varchar(24),
  children char(1) NOT NULL default "0",
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  stats varchar(255),
  PRIMARY KEY (id)
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmuser (
  id integer,
  name varchar(24) UNIQUE,
  closed char(1) default "0",
  role char(1) default "V",
  pwd varchar(40),
  www varchar(64),
  mail varchar(64),
  phone varchar(64),
  privacy char(1) default "1",
  location varchar(24),
  numpost integer,
  signature varchar(255),
  photo varchar(24),
  children char(1) default "0",
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  stats varchar(255),
  PRIMARY KEY (id)
  )';
  break;

case 'sqlit':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmuser (
  id integer,
  name text UNIQUE,
  closed text NOT NULL default "0",
  role text NOT NULL default "V",
  pwd text,
  www text,
  mail text,
  phone text,
  privacy text NOT NULL default "1",
  location text,
  numpost integer,
  signature text,
  photo text,
  children text NOT NULL default "0",
  secret_q text,
  secret_a text,
  x real,
  y real,
  z real,
  stats text,
  PRIMARY KEY (id)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmuser (
  id integer NOT NULL,
  name varchar(24) NOT NULL UNIQUE,
  closed char(1) NOT NULL default "0",
  role char(1) NOT NULL default "V",
  pwd varchar(40),
  www varchar(64),
  mail varchar(64),
  phone varchar(64),
  privacy char(1) NOT NULL default "1",
  location varchar(24),
  numpost integer,
  signature varchar(255),
  photo varchar(24),
  children char(1) NOT NULL default "0",
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  stats varchar(255),
  PRIMARY KEY (id)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmuser (
  id number(32),
  name varchar2(24),
  closed char(1) default "0" NOT NULL,
  role char(1) default "V" NOT NULL,
  pwd varchar2(40),
  www varchar2(64),
  mail varchar2(64),
  phone varchar2(64),
  privacy char(1) default "1" NOT NULL,
  location varchar2(24),
  numpost number(32),
  signature varchar2(255),
  photo varchar2(24),
  children char(1) default "0" NOT NULL,
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  stats varchar2(255),
  CONSTRAINT pk_'.$qnm_prefix.'qnmuser PRIMARY KEY (id))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, oci, sqlite, ibase, db2");

}

echo '<span style="color:blue;">';
$b=$oDB->Query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qnm_prefix.'qnmuser',$qnm_database,$qnm_user),'</div>';
  echo '<br/><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qnm_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}

$oDB->Query( 'INSERT INTO '.$qnm_prefix.'qnmuser (id,name,photo,closed,role,numpost,privacy,children) VALUES (0,"Visitor","0","0","V",0,"0","0")' );
$oDB->Query( 'INSERT INTO '.$qnm_prefix.'qnmuser (id,name,photo,closed,role,pwd,numpost,privacy,signature,children) VALUES (1,"Admin","0","0","A","'.sha1('Admin').'",0,"0","[i][b]The board Administrator[/b][/i]","0")' );