<?php

// QNM 1.0 build:20130410

switch($oDB->type)
{

case 'mysql4':
case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdoc (
  id int NOT NULL default 0,
  doctype varchar(64),
  docdate varchar(20),
  docname varchar(255),
  docfile varchar(255),
  docpath varchar(255)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdoc (
  id int NOT NULL default 0,
  doctype varchar(64),
  docdate varchar(20),
  docname varchar(255),
  docfile varchar(255),
  docpath varchar(255)
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdoc (
  id integer NOT NULL default 0,
  doctype varchar(64),
  docdate varchar(20),
  docname varchar(255),
  docfile varchar(255),
  docpath varchar(255)
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdoc (
  id integer default 0,
  doctype varchar(64),
  docdate varchar(20),
  docname varchar(255),
  docfile varchar(255),
  docpath varchar(255)
  )';
  break;

case 'sqlite':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdoc (
  id integer,
  doctype text,
  docdate text,
  docname text,
  docfile text,
  docpath text
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmchild (
  id integer NOT NULL default 0,
  doctype varchar(64),
  docdate varchar(20),
  docname varchar(255),
  docfile varchar(255),
  docpath varchar(255)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmdoc (
  id number(32),
  doctype varchar2(64),
  docdate varchar2(20),
  docname varchar2(255),
  docfile varchar2(255),
  docpath varchar2(255)
  )';
  break;

default:
  die('Database type ['.$this->type.'] not supported... Must be mysql, mssql, pg, firebird, db2, oracle or access');
}

echo '<span style="color:blue;">';
$b=$oDB->Query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qnm_prefix.'qnmdoc',$qnm_database,$qnm_user),'</div>';
  echo '<br/><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qnm_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}