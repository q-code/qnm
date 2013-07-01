<?php

// QNM  1.0 build:20130410

include 'bin/class/qt_class_db.php';
include 'bin/config.php';

if ( !isset($_POST['v']) ) { echo ' '; exit; }
if ( !isset($_POST['f']) ) $_POST['f']='name';
if ( get_magic_quotes_gpc() ) $_POST['v'] = stripslashes($_POST['v']);
$e0 = 'Already used'; if ( isset($_GET['e0']) ) $e0 = $_GET['e0'];

if ( strlen($_POST['v'])==0 ) { echo ' '; exit; }

if ( strlen($_POST['v'])<4 )
{
  if ( isset($_POST['e1']) ) { echo $_POST['e1']; } else { echo 'Minium 4 characters'; }
}
else
{
  $oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
  if ( !empty($oDBAJAX->error) ) return;
  $oDBAJAX->Query( 'SELECT count(*) as countid FROM '.$qnm_prefix.'qnmuser WHERE '.$_POST['f'].'="'.htmlspecialchars(addslashes($_POST['v']),ENT_QUOTES).'"' );
  $row = $oDBAJAX->GetRow();
  if ( $row['countid']>0 )
  {
  if ( isset($_POST['e2']) ) { echo $_POST['e2']; } else { echo $e0; }
  }
  else
  {
  echo ' ';
  }
}