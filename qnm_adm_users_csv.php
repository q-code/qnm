<?php

/**
* PHP versions 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QNM
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2013 The PHP Group
* @version    1.0 build:20130410
*/

session_start();
require_once 'bin/qnm_init.php';
if ( !$oVIP->user->CanView('V2') ) HtmlPage(11);
include Translate('qnm_adm.php');

// ---------
// INITIALISE
// ---------

$strCSV = '';
$strGroup = 'all';
$strWhere = '';
$strOrder = 'name';
$strDirec = 'asc';
$intLimit = 0;
$intLen = (int)$_SESSION[QT]['items_per_page'];
$size = ( isset($_GET['size']) ? strip_tags($_GET['size']) : 'all');
$intCount = (int)$_GET['n'];

// Check arguments

if ( empty($size) || $intCount<=$intLen ) $size='all';
if ( strlen($size)>6 ) die('Invalid argument');
if ( substr($size,0,1)!='p' && substr($size,0,1)!='m' && $size!=='all') die('Invalid argument');
if ( substr($size,0,1)=='p' )
{
  $i = (int)substr($size,1);
  if ( empty($i) || $i<0 ) die('Invalid argument');
  if ( ($i-1) > $intCount/$intLen ) die('Invalid argument');
}
if ( substr($size,0,1)=='m' )
{
  if ( $size!='m1' && $size!='m2' && $size!='m5' && $size!='m10' ) die('Invalid argument');
}
if ( $intCount>1000 && $size=='all' ) die('Invalid argument');
if ( $intCount<=1000 && substr($size,0,1)=='m' ) die('Invalid argument');
if ( $intCount>1000 && substr($size,0,1)=='p' ) die('Invalid argument');

// Read Uri arguments

if ( isset($_GET['group']) ) $strGroup = strip_tags($_GET['group']);
if ( isset($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = strtolower($_GET['dir']);

// Security check 2 (no long argument)

if ( strlen($strGroup)>4 ) die('Invalid argument #group');
if ( strlen($strOrder)>20 ) die('Invalid argument #order');
if ( strlen($strDirec)>4 ) die('Invalid argument #dir');

include 'bin/qnm_fn_sql.php';

$oVIP->selfurl = 'qnm_items.php';
$oVIP->selfname = $L['Memberlist'];

// apply argument

if ( $size=='all') { $intLimit=0; $intLen=$intCount; }
if ( $size=='m1' ) { $intLimit=0; $intLen=999; }
if ( $size=='m2' ) { $intLimit=1000; $intLen=1000; }
if ( $size=='m5' ) { $intLimit=0; $intLen=4999; }
if ( $size=='m10') { $intLimit=5000; $intLen=5000; }
if ( substr($size,0,1)=='p' ) { $i = (int)substr($size,1); $intLimit = ($i-1)*$intLen; }

// --------
// HTML START
// --------

// ========
$table = new cTable('t1','data_u');
$table->th['user.name'] = new cTableHead($L['Username']);
$table->th['user.role'] = new cTableHead($L['Role']);
$table->th['user.firstdate'] = new cTableHead($L['Registration']);
$table->th['user.notes'] = new cTableHead($L['Messages']);
$table->th['user.lastdate'] = new cTableHead($L['Last_message']);
$table->th['user.id'] = new cTableHead('id');
// ========
foreach(array_keys($table->th) as $key) $strCSV .= ToCsv($table->th[$key]->content);
$strCSV = substr($strCSV,0,-1)."\r\n";
// ========
if ($strGroup!=='all') $strWhere = ($strGroup=='0' ? ' AND '.FirstCharCase('name','a-z') : ' AND '.FirstCharCase('name','u').'="'.$strGroup.'"' );
$oDB->Query( LimitSQL('*,(SELECT COUNT('.TABPOST.'.id) FROM '.TABPOST.' WHERE '.TABPOST.'.userid='.TABUSER.'.id AND '.TABPOST.'.status>=0) as notes FROM '.TABUSER.' WHERE id>0'.$strWhere ,$strOrder.' '.strtoupper($strDirec), $intLimit,$intLen,$intCount) );
// ========
$intWhile=0;
while($row=$oDB->Getrow())
{
  $str = implode('',FormatCsvRow($table->GetTHnames(),$row));
  if ( substr($str,-1,1)==';' ) $str = substr($str,0,-1);
  $strCSV .= $str.PHP_EOL;
  //odbcbreak
  $intWhile++; if ( $intWhile>=$intCount ) break;
}
// ========

// OUPUT

if ( isset($_GET['debug']) )
{
  echo $strCSV;
  exit;
}

if ( !headers_sent() )
{
  header('Content-Type: text/csv; charset='.QNM_HTML_CHAR);
  header('Content-Disposition: attachment; filename="qnm_'.date('YmdHi').'.csv"');
}

echo $strCSV;