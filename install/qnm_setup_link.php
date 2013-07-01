<?php

// QNM 1 build:20130410

switch($oDB->type)
{

case 'mysql4':
case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlink (
  lid int,
  lclass varchar(2),
  lid2 int,
  ldirection int,
  lidclass varchar(2),
  lid2class varchar(2),
  lstatus int,
  PRIMARY KEY (lid,lidclass,lclass,lid2,lid2class)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlink (
  lid int,
  lclass varchar(2),
  lid2 int,
  ldirection int,
  lidclass varchar(2),
  lid2class varchar(2),
  lstatus int NULL,
  CONSTRAINT pk_'.$qnm_prefix.'qnmlink PRIMARY KEY (lid,lidclass,lclass,lid2,lid2class)
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlink (
  lid integer,
  lclass varchar(2),
  lid2 integer,
  ldirection integer,
  lidclass varchar(2),
  lid2class varchar(2),
  lstatus integer,
  PRIMARY KEY (lid,lidclass,lclass,lid2,lid2class)
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlink (
  lid integer,
  lclass varchar(2),
  lid2 integer,
  ldirection integer,
  lidclass varchar(2),
  lid2class varchar(2),
  lstatus integer,
  PRIMARY KEY (lid,lidclass,lclass,lid2,lid2class)
  )';
  break;

case 'sqlite':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlink (
  lid integer,
  lclass text,
  lid2 integer,
  ldirection integer,
  lidclass text,
  lid2class text,
  lstatus integer,
  PRIMARY KEY (lid,lidclass,lclass,lid2,lid2class)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlink (
  lid integer,
  lclass varchar(2),
  lid2 integer,
  ldirection integer,
  lidclass varchar(2),
  lid2class varchar(2),
  lstatus integer,
  PRIMARY KEY (lid,lidclass,lclass,lid2,lid2class)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlink (
  lid int,
  lclass varchar2(2),
  lid2 int,
  ldirection int,
  lidclass varchar2(2),
  lid2class varchar2(2),
  lstatus int,
  CONSTRAINT pk_'.$qnm_prefix.'qnmlink PRIMARY KEY (lidclass,lid,lclass,lid2class,lid2))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, mssql, pg, oracle, sqlite, firebird, db2");

}

echo '<span style="color:blue;">';
$b=$oDB->Query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qnm_prefix.'qnmlink',$qnm_database,$qnm_user),'</div>';
  echo '<br/><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qnm_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}