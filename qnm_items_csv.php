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

// ---------
// INITIALISE
// ---------

$size = ( isset($_GET['size']) ? strip_tags($_GET['size']) : 'all');
$intCount = (int)$_GET['n'];
$intLimit = 0;
$intLen = (int)$_SESSION[QT]['items_per_page'];

// Check arguments

if ( empty($size) || $intCount <= $intLen ) $size='all';
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

// Uri arguments

$q = '';   // in case of search, query type
$s = '*';  // section filter can be '*' or [int]
$fs = '*';  // section filter ($fs will become $s if provided)
$ft = '*';  // type (of filter). Can be urlencoded
$fst = '*';  // status can be '*' or [int]

// User's preferences (stored as coockies)

$u_fst='*';
$u_dir='asc';
if ( isset($_COOKIE[QT.'_u_fst']) ) $u_fst=$_COOKIE[QT.'_u_fst'];
if ( isset($_COOKIE[QT.'_u_dir']) ) $u_dir=$_COOKIE[QT.'_u_dir'];

$fst=$u_fst;// filter by status
$dir=$u_dir;// id order ('asc'|'desc')

// Read Uri arguments

QThttpvar('s fs ft fst q','str str str str str');
if ( $fs==='' ) $fs='*';
if ( $s==='' ) $s='*';
if ( $fst==='' ) $fst='*';
if ( $fs!=='*' ) $s=(int)$fs; // $fs becomes $s in this page
if ( $s!=='*' ) $s=(int)$s;
if ( $fst!=='*' ) $fst=(int)$fst;
if ( !empty($q) ) $fst='*'; // status user preference is not applied in case of search results

// Section (can be an empty section in case of search result)

if ( $s==='*' )
{
  $oSEC = new cSection();
}
elseif ( $s<0 )
{
  $oHtml->Redirect();
}
else
{
  $oSEC = new cSection($s);
}

if ( $q=='last' || $q=='user' ) { $oSEC->o_order='issuedate'; $dir='desc'; }
if ( isset($_GET['order']) ) $oSEC->o_order = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = $_GET['dir'];
$strDirec = strtolower($dir);

$strCSV = '';
$arrMe[] = array();

include 'bin/qnm_fn_sql.php';

// apply argument

if ( $size=='all') { $intLimit=0; $intLen=$intCount; }
if ( $size=='m1' ) { $intLimit=0; $intLen=999; }
if ( $size=='m2' ) { $intLimit=1000; $intLen=1000; }
if ( $size=='m5' ) { $intLimit=0; $intLen=4999; }
if ( $size=='m10') { $intLimit=5000; $intLen=5000; }
if ( substr($size,0,1)=='p' ) { $i = (int)substr($size,1); $intLimit = ($i-1)*$intLen; }

// Criteria sql

$strFields = 'e.*';
$strFrom = ' FROM '.TABNE.' e ';
$strWhere = ' WHERE e.uid>0';
if ( $s!=='*' ) $strWhere .= ' AND e.section='.$s;
$strCount  = 'SELECT count(*) as countid'.$strFrom.$strWhere;
$strFilter = '';
if ( empty($q) )
{
  if ( $ft!=='*' ) $strFilter.=' AND e.type="'.$ft.'"';
  if ( $fst!=='*' ) $strFilter .=' AND e.status='.$fst;
}
else
{
  include 'qnm_items_qry.php';
}

// --------
// HTML START
// --------

// Last column
switch($oSEC->o_last)
{
case 'messages': $str=$L['Notes']; break;
case 'status': $str=$L['Status']; break;
case 'wisheddate': $str=$L['Wisheddate']; break;
case 'id': $str='Id'; break;
case 'tags': $str=$L['Tags']; break;
case 'coord': $str=$L['Coord']; break;
default: $str=$oSEC->o_last;
}

// ========

$table = new cTable();
$table->th['uid'] = new cTableHead('uid');
$table->th['id'] = new cTableHead('Id');
$table->th['links'] = new cTableHead('Links');
$table->th['status'] = new cTableHead(L('Status'));
$table->th['type'] = new cTableHead(L('Type'));
$table->th['address'] = new cTableHead(L('Address'));
$table->th['descr'] = new cTableHead(L('Description'));
$table->th['posts'] = new cTableHead(L('Messages'));

// ========
foreach(array_keys($table->th) as $key) $strCSV .= ToCsv($table->th[$key]->content);
$strCSV = substr($strCSV,0,-1).PHP_EOL;
// ========
if ( $oSEC->o_order=='issuedate' ) { $strAlias='p.'; } else { $strAlias='e.'; }
$strFullOrder = $strAlias.$oSEC->o_order.' '.strtoupper($strDirec); if ( $oSEC->o_order!='id' ) $strFullOrder .= ',e.id';
$oDB->Query( LimitSQL($strFields.$strFrom.$strWhere.$strFilter,$strFullOrder,$intLimit,$intLen,$intCount) );
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