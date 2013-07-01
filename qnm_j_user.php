<?php

// QNM  1.0 build:20130410

include 'bin/qt_lib_txt.php';
include 'bin/class/qt_class_db.php';
include 'bin/qnm_fn_base.php';
include 'bin/qnm_fn_html.php';

// Protection against injection (accept only 4 'lang')
$id = strip_tags($_POST['id']);
$lang = strip_tags($_POST['lang']);
if ( !in_array($lang,array('language/english/','language/francais/','language/nederlands/','language/espanol/')) ) $lang='language/english/';
$dir = strip_tags($_POST['dir']);

$id = intval(substr($id,1));

include $lang.'qnm_main.php';
include 'bin/config.php';

$oDBAJAX = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDBAJAX->error) ) exit;

// query

$oDBAJAX->Query('SELECT * FROM '.$qnm_prefix.'qnmuser WHERE id='.$id);
$row = $oDBAJAX->GetRow();

//output the response

echo AsImgBox(
  (empty($row['photo']) ? '' : AsImg($dir.$row['photo'],'',$row['name'],'member')),
  'picbox',
  '',
  $row['name'].'<br/>('.QTconv($L['Userrole_'.strtolower($row['role'])],'5').')'.(empty($row['location']) ? '' : '<br/>'.QTconv($row['location'],'5'))
  );