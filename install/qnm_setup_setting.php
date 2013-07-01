<?php

// QNM 1.0 build:20130410

switch($oDB->type)
{

case 'mysql4':
case 'mysql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsetting (
  param varchar(24),
  setting varchar(255)
  )';
  break;

case 'sqlsrv':
case 'mssql':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsetting (
  param varchar(24),
  setting varchar(255)
  )';
  break;

case 'pg':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsetting (
  param varchar(24),
  setting varchar(255)
  )';
  break;

case 'sqlite':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsetting (
  param text,
  setting text
  )';
  break;

case 'ibase':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsetting (
  param varchar(24),
  setting varchar(255)
  )';
  break;

case 'db2':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsetting (
  param varchar(24),
  setting varchar(255)
  )';
  break;

case 'oci':
  $strQ='CREATE TABLE '.$qnm_prefix.'qnmsetting (
  param varchar2(24),
  setting varchar2(255)
  )';
  break;

default:
  die("Database type [{$oDB->type}] not supported... Must be mysql, sqlsrv, pg, sqlite, ibase, db2, oci");

}

echo '<span style="color:blue;">';
$b=$oDB->Query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf ($L['E_install'],$qnm_prefix.'qnmsetting',$qnm_database,$qnm_user),'</div>';
  echo '<br/><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qnm_setup_1.php">',$L['Restart'],'</a>&nbsp;</td></tr></table>';
  exit;
}

$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("version", "1.0")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("board_offline", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("site_name", "QNM")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("site_url", "http://")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("home_name", "Home")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("home_url", "http://www.qt-cute.org")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("admin_email", "")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("admin_fax", "")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("admin_name", "")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("admin_addr", "")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("posts_per_item", "100")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("chars_per_post", "4000")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("lines_per_post", "250")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("time_zone", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("show_time_zone", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("home_menu", "0")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("posts_delay", "4")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("posts_per_day", "100")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("site_width", "800")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("register_safe", "text")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("smtp_password", "")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("smtp_username", "")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("smtp_host", "")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("use_smtp", "0")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("sys_welcome", "2")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("items_per_page", "50")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("replies_per_page", "50")');
$str='english';
if ( isset($_SESSION['qnm_setup_lang']) && $_SESSION['qnm_setup_lang']=='fr' ) $str='francais';
if ( isset($_SESSION['qnm_setup_lang']) && $_SESSION['qnm_setup_lang']=='nl' ) $str='nederlands';
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("language", "'.$str.'")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("userlang", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("section_desc", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("show_banner", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("show_legend", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("index_name", "Network index")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("skin_dir", "default")');
/* $result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("bbc", "1")'); */
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("avatar", "gif,jpg,jpeg,png")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("avatar_width", "150")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("avatar_height", "150")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("avatar_size", "30")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("formatdate", "j M Y")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("formattime", "G:i")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("show_id", "T-%03s")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("show_back", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("show_closed", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("login_addon", "0")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("register_mode", "direct")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("daylight", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("visitor_right", "5")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("show_section_tags", "1")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("show_calendar", "U")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("upload", "U")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("upload_size", "500")');
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("show_stats", "U")'); //v1.3
$result=$oDB->Query('INSERT INTO '.$qnm_prefix.'qnmsetting VALUES ("tags", "U")'); //v2.0