<?php

// QNM 1.0 build:20130410

session_start();
require_once 'bin/qnm_init.php';
if ( $oVIP->user->role!='A' ) die('Access denied');
$oVIP->selfurl='qnm_adm_const.php';
$oVIP->selfname='PHP constants';

function ConstantToString($str)
{
  if ( is_string($str) ) return '"'.htmlentities($str).'"';
  if ( is_bool($str) ) return ($str ? 'TRUE' : 'FALSE');
  if ( is_array($str) ) return 'array of '.count($str).' values';
  if ( is_null($str) ) return '(null)';
  return $str;
}

// HTML start

include Translate('qnm_adm.php');
include 'qnm_adm_inc_hd.php';

// CONSTANT

$arr = get_defined_constants(true); if ( isset($arr['user']) ) $arr = $arr['user']; // userdefined constants

// Prepare table template

$table = new cTable('','data_o');
$table->row = new cTableRow('','data_o');
$table->td[0] = new cTableData('','','headfirst'); $table->td[0]->Add('style','width:200px;');
$table->td[1] = new cTableData();

// Show constants

echo '<p>Here are the major constants. To have a full list of constants see the file /bin/qnm_init.php.</p>';

echo $table->Start().PHP_EOL;
$table->SetTDcontent( array('QT', ConstantToString(constant('QT'))) );
echo $table->GetTDrow().PHP_EOL;

foreach($arr as $key=>$str)
{
  if ( substr($key,0,4)=='QNM_' )
  {
    $table->SetTDcontent( array($key, ConstantToString($str)) );
    echo $table->GetTDrow().PHP_EOL;
  }
}
echo $table->End(true).PHP_EOL;

// Show DB parameters

echo '<p>Here are the database connection parameters (except passwords)</p>';

echo $table->Start().PHP_EOL;
$table->td[0] = new cTableData('','','headfirst'); $table->td[0]->Add('style','width:200px;');
$table->td[1] = new cTableData();
foreach(array('qnm_dbsystem','qnm_host','qnm_database','qnm_prefix','qnm_user','qnm_port','qnm_dsn','qnm_install') as $str)
{
  $table->SetTDcontent( array('$'.$str, (isset($$str) ? ConstantToString($$str) : '&nbsp;')) );
  echo $table->GetTDrow().PHP_EOL;
}
echo $table->End(true,true,true).PHP_EOL;

include 'qnm_adm_inc_ft.php';