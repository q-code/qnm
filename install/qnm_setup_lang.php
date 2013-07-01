<?php

// QNM  2 build:20130410

switch($oDB->type)
{

case 'mysql4':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(255),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;
case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  CONSTRAINT pk_'.$qnm_prefix.'qnmlang PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'sqlit':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlang (
  objtype text,
  objlang text,
  objid text,
  objname text,
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmlang (
  objtype varchar2(10),
  objlang varchar2(2),
  objid varchar2(24),
  objname varchar2(4000),
  CONSTRAINT pk_'.$qnm_prefix.'qnmlang PRIMARY KEY (objtype,objlang,objid))';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, mssql, pg, sqlite, firebird, db2, oci");

}

echo '<span style="color:blue;">';
$b=$oDB->Query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qnm_prefix.'qnmlang',$qnm_database,$qnm_user),'</div>';
  echo '<br/><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qnm_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}