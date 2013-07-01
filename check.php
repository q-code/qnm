<?php

// QNM 1.0 build:20130410

// --------
// HTML start
// --------
echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">
<head>
<title>QNM installation checker</title>
<meta charset="UTF-8" />
<meta name="description" content="QT QNM network element management" />
<meta name="keywords" content="Element,network,element management,management,qt-cute,OpenSource" />
<meta name="author" content="qt-cute.org" />
<link rel="stylesheet" href="admin/qnm_main.css" />
<style type="text/css">
p.check {margin:5px 0 0 0; padding:0}
p.endcheck {margin:5px 0 0 0; padding:5px; border:solid 1px #aaaaaa}
span.ok {color:#00aa00; background-color:inherit}
span.nok {color:#ff0000; background-color:inherit}
div.footer_copy {width:100%; text-align:right}
a.footer_copy {color:blue; background-color:inherit; font-size:8pt}
</style>
</head>';

echo '<body>

<!-- PAGE CONTROL -->
<div class="page" style="width:700px">
<!-- PAGE CONTROL -->

<!-- HEADER BANNER -->
<div class="banner">
<div class="banner_in">
<img src="admin/qnm_logo.gif" width="150" height="50" style="border-width:0" alt="QNM " title="QNM "/>
</div>
</div>
<!-- END HEADER BANNER -->

<!-- BODY MAIN -->
<table width="100%" style="border:1px solid #AAAAAA;">
<tr>
<td style="padding:5px 10px 5px 10px;">
<!-- BODY MAIN -->
';

// --------
// 1 CONFIG
// --------

echo '<p style="margin:0;text-align:right">QNM 1.0 build:20130410</p>';

echo '<h1>Checking your configuration</h1>';

$error = '';

// 1 file exist

  echo '<p class="check">Checking installed files... ';

  if ( !file_exists('bin/config.php') ) $error .= 'File <b>config.php</b> is not in the <b>bin</b> directory. Communication with database is impossible.<br/>';
  if ( !file_exists('bin/qnm_init.php') ) $error .= 'File <b>qnm_init.php</b> is not in the <b>bin</b> directory. Application cannot start.<br/>';
  if ( !file_exists('bin/qnm_fn_base.php') ) $error .= 'File <b>qnm_fn_base.php</b> is not in the <b>bin</b> directory. Application cannot start.<br/>';
  if ( !file_exists('bin/qnm_fn_html.php') ) $error .= 'File <b>qnm_fn_html.php</b> is not in the <b>bin</b> directory. Application cannot start.<br/>';
  if ( !file_exists('bin/class/qt_class_db.php') ) $error .= 'File <b>qt_class_db.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br/>';
  if ( !file_exists('bin/qt_lib_txt.php') ) $error .= 'File <b>qt_lib_txt.php</b> is not in the <b>bin</b> directory. Application cannot start.<br/>';
  if ( !file_exists('bin/class/qnm_class_sec.php') ) $error .= 'File <b>qnm_class_sec.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br/>';
  if ( !file_exists('bin/class/qnm_class_ne.php') ) $error .= 'File <b>qnm_class_ne.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br/>';
  if ( !file_exists('bin/class/qnm_class_post.php') ) $error .= 'File <b>qnm_class_post.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br/>';
  if ( !file_exists('bin/class/qnm_class_vip.php') ) $error .= 'File <b>qnm_class_vip.php</b> is not in the <b>bin/class</b> directory. Application cannot start.<br/>';

  if ( empty($error) )
  {
  echo '<span class="ok">Main files found.</span></p>';
  }
  else
  {
  die('<span class="nok">'.$error.'</span></p>');
  }

// 2 config is correct

  echo '<p class="check">Checking config.php... ';

  include 'bin/config.php';

  if ( !isset($qnm_dbsystem) ) $error .= 'Variable <b>$qnm_dbsystem</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br/>';
  if ( !isset($qnm_host) ) $error .= 'Variable <b>$qnm_host</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br/>';
  if ( !isset($qnm_database) ) $error .= 'Variable <b>$qnm_database</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br/>';
  if ( !isset($qnm_prefix) ) $error .= 'Variable <b>$qnm_prefix</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br/>';
  if ( !isset($qnm_user) ) $error .= 'Variable <b>$qnm_user</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br/>';
  if ( !isset($qnm_pwd) ) $error .= 'Variable <b>$qnm_pwd</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br/>';
  if ( !isset($qnm_port) ) $error .= 'Variable <b>$qnm_port</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br/>';
  if ( !isset($qnm_dsn) ) $error .= 'Variable <b>$qnm_dsn</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br/>';

  if ( !empty($error) )  die('<span class="nok">'.$error.'</span>');

  // check db type
  if ( !in_array($qnm_dbsystem,array('mysql4','mysql','sqlsrv','mssql','pg','ibase','sqlite','db2','oci')) ) die('Unknown db type '.$qnm_dbsystem);
  // check other values
  if ( empty($qnm_host) ) $error .= 'Variable <b>$qnm_host</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br/>';
  if ( empty($qnm_database) ) $error .= 'Variable <b>$qnm_database</b> is not defined in the file <b>bin/config.php</b>. Communication with database is impossible.<br/>';
  if ( !empty($error) ) die($error);

  if ( empty($error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  die('<span class="nok">'.$error.'</span></p>');
  }

// 3 test db connection

  echo '<p class="check">Connecting to database... ';

  include 'bin/class/qt_class_db.php';

  $oDB = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);

  if ( empty($oDB->error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  die('<span class="nok">Connection with database failed.<br/>Check that server is up and running.<br/>Check that the settings in the file <b>bin/config.php</b> are correct for your database.</span></p>');
  }

// end CONFIG tests

  echo '<p class="endcheck">Configuration tests completed successfully.</p>';

// --------
// 2 DATABASE
// --------

$error = '';

echo '
<h1>Checking your database design</h1>
';

// 1 setting table

  echo '<p class="check">Checking setting table... ';

  $oDB->Query('SELECT setting FROM '.$qnm_prefix.'qnmsetting WHERE param="version"');
  if ( !empty($oDB->error) ) die("<br/><font color=red>Problem with table ".$qnm_prefix."qnmsetting</font>");
  $row = $oDB->Getrow();
  $strVersion = $row['setting'];

  echo '<span class="ok">Table [',$qnm_prefix,'qnmsetting] exists. Version is ',$strVersion,'.</span>';
  if ( !in_array(substr($strVersion,0,3),array('1.0')) ) die('<span class="nok">But data in this table refers to an incompatible version (must be version 1.0).</span></p>');
  echo '</p>';

// 2 domain table

  echo '<p class="check">Checking domain table... ';

  $oDB->Query('SELECT count(*) as countid FROM '.$qnm_prefix.'qnmdomain');
  if ( !empty($oDB->error) ) die("<br/><font color=red>Problem with table ".$qnm_prefix."qnmdomain</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qnm_prefix,'qnmdomain] exists. ',$intCount,' domain(s) found.</span></p>';

// 3 section table

  echo '<p class="check">Checking section table...';

  $oDB->Query('SELECT count(*) as countid FROM '.$qnm_prefix.'qnmsection');
  if ( !empty($oDB->error) ) die("<br/><font color=red>Problem with table ".$qnm_prefix."qnmsection</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qnm_prefix,'qnmsection] exists. ',$intCount,' section(s) found.</span></p>';

// 4 element table

  echo '<p class="check">Checking element table...';

  $oDB->Query('SELECT count(uid) as countid FROM '.$qnm_prefix.'qnmelement');
  if ( !empty($oDB->error) ) die("<br/><font color=red>Problem with table ".$qnm_prefix."qnmelement</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qnm_prefix,'qnmelement] exists. ',$intCount,' element(s) found.</span></p>';

// 4 element table

  echo '<p class="check">Checking link table...';

  $oDB->Query('SELECT count(lid) as countid FROM '.$qnm_prefix.'qnmlink');
  if ( !empty($oDB->error) ) die("<br/><font color=red>Problem with table ".$qnm_prefix."qnmlink</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qnm_prefix,'qnmlink] exists. ',$intCount,' element(s) found.</span></p>';

// 5 post table

  echo '<p class="check">Checking post table...';

  $oDB->Query('SELECT count(*) as countid FROM '.$qnm_prefix.'qnmpost');
  if ( !empty($oDB->error) ) die("<br/><font color=red>Problem with table ".$qnm_prefix."qnmpost</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qnm_prefix,'qnmpost] exists. ',$intCount,' post(s) found.</span></p>';

// 6 user table

  echo '<p class="check">Checking user table... ';

  $oDB->Query('SELECT count(*) as countid FROM '.$qnm_prefix.'qnmuser');
  if ( !empty($oDB->error) ) die("<br/><font color=red>Problem with table ".$qnm_prefix."qnmuser</font>");
  $row = $oDB->Getrow();
  $intCount = $row['countid'];
  echo '<span class="ok">Table [',$qnm_prefix,'qnmuser] exists. ',$intCount,' user(s) found.</span></p>';

// end DATABASE tests

  echo '<p class="endcheck">Database tests completed successfully.</p>';

// --------
// 3 LANGUAGE AND SKIN
// --------

$error = '';

echo '
<h1>Checking language and skin options</h1>
';

  echo '<p class="check">Files... ';

  $oDB->Query('SELECT setting FROM '.$qnm_prefix.'qnmsetting WHERE param="language"');
  $row = $oDB->Getrow();
  $str = $row['setting'];
  if ( empty($str) ) $error .= 'Setting <b>language</b> is not defined in the setting table. Application can only work with english.<br/>';
  if ( !file_exists("language/$str/qnm_main.php") ) $error .= "File <b>qnm_main.php</b> is not in the <b>language/xxxx</b> directory.<br/>";
  if ( !file_exists("language/$str/qnm_adm.php") ) $error .= "File <b>qnm_adm.php</b> is not in the <b>language/xxxx</b> directory.<br/>";
  if ( !file_exists("language/$str/qnm_icon.php") ) $error .= "File <b>qnm_icon.php</b> is not in the <b>language/xxxx</b> directory.<br/>";
  if ( !file_exists("language/$str/qnm_reg.php") ) $error .= "File <b>qnm_reg.php</b> is not in the <b>language/xxxx</b> directory.<br/>";
  if ( !file_exists("language/$str/qnm_zone.php") ) $error .= "File <b>qnm_zone.php</b> is not in the <b>language/xxxx</b> directory.<br/>";
  if ( $str!='english' )
  {
  if ( !file_exists("language/english/qnm_main.php") ) $error .= "File <b>qnm_main.php</b> is not in the <b>language/english</b> directory. English language is mandatory.<br/>";
  if ( !file_exists("language/english/qnm_adm.php") )  $error .= "File <b>qnm_adm.php</b> is not in the <b>language/english</b> directory. English language is mandatory.<br/>";
  if ( !file_exists("language/english/qnm_icon.php") ) $error .= "File <b>qnm_icon.php</b> is not in the <b>language/english</b> directory. English language is mandatory.<br/>";
  if ( !file_exists("language/english/qnm_reg.php") )  $error .= "File <b>qnm_reg.php</b> is not in the <b>language/english</b> directory. English language is mandatory.<br/>";
  if ( !file_exists("language/english/qnm_zone.php") ) $error .= "File <b>qnm_zone.php</b> is not in the <b>language/english</b> directory. English language is mandatory.<br/>";
  }

  $oDB->Query('SELECT setting FROM '.$qnm_prefix.'qnmsetting WHERE param="skin_dir"');
  $row = $oDB->Getrow();
  $str = $row['setting']; if ( substr($str,0,5)!='skin/' ) $str = 'skin/'.$str;

  if ( empty($str) ) $error .= 'Setting <b>skin</b> is not defined in the setting table. Application will not display correctly.<br/>';
  if ( !file_exists("$str/qnm_main.css") ) $error .= "File <b>qnm_main.css</b> is not in the <b>skin/xxxx</b> directory.<br/>";
  if ( !file_exists("skin/default/qnm_main.css") ) $error .= "File <b>qnm_main.css</b> is not in the <b>skin/default</b> directory. Default skin is mandatory.<br/>";

  if ( empty($error) )
  {
  echo '<span class="ok">Ok.</span>';
  }
  else
  {
  echo '<span class="nok">',$error,'</span>';
  }

  echo '</p>';

// end LANGUAGE AND SKIN tests

  echo '<p class="endcheck">Language and skin files tested.</p>';

// --------
// 4 ADMINISTRATION TIPS
// --------

$error = '';

echo '
<h1>Administration tips</h1>
';

// 1 admin email

  echo '<p class="check">Email setting... ';

  $oDB->Query('SELECT setting FROM '.$qnm_prefix.'qnmsetting WHERE param="admin_email"');
  $row = $oDB->Getrow();
  $strMail = $row['setting'];
  if ( empty($strMail) )
  {
  $error .= 'Administrator e-mail is not yet defined. It\'s mandatory to define it!';
  }
  else
  {
  if ( !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i",$strMail) ) $error .= 'Administrator e-mail format seams incorrect. Please check it';
  }

  if ( !empty($error) ) echo '<span class="nok">'.$error.'</span></p>';
  echo '<span class="ok">Done.</span></p>';
  $error = '';

// 2 admin password

  echo '<p class="check">Security check... ';

  $oDB->Query('SELECT pwd FROM '.$qnm_prefix.'qnmuser WHERE id=1');
  $row = $oDB->Getrow();
  $strPwd = $row['pwd'];
  If ( $strPwd==sha1('Admin') ) $error .= 'Administrator password is still the initial password. It\'s recommended to change it !<br/>';

  if ( empty($error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  echo '<span class="nok">',$error,'</span></p>';
  }
  $error = '';

// 3 site url

  echo '<p class="check">Site url... ';

  $oDB->Query('SELECT setting FROM '.$qnm_prefix.'qnmsetting WHERE param="site_url"');
  $row = $oDB->Getrow();
  $strText = trim($row['setting']);
  if ( substr($strText,0,7)!="http://" && substr($strText,0,8)!="https://" )
  {
    $error .= 'Site url is not yet defined (or not starting by http://). It\'s mandatory to define it !<br/>';
  }
  else
  {
    $strURL = ( empty($_SERVER['SERVER_HTTPS']) ? 'http://' : 'https://' ).$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $strURL = substr($strURL,0,-10);
    if ( $strURL!=$strText ) $error .= 'Site url seams to be different that the current url. Please check it<br/>';
  }

  if ( empty($error) )
  {
  echo '<span class="ok">Done.</span></p>';
  }
  else
  {
  echo '<span class="nok">',$error,'</span></p>';
  }
  $error = '';

// 4 avatar/upload folder permission

  echo '<p class="check">Folder permissions... ';

  if ( !is_dir('avatar') )
  {
    $error .= 'Directory <b>avatar</b> not found.<br/>Please create this directory and make it writeable (chmod 777) if you want to allow avatars.<br/>';
  }
  else
  {
    if ( !is_readable('avatar') ) $error .= 'Directory <b>avatar</b> is not readable. Change permissions (chmod 777) if you want to allow avatars.<br/>';
    if ( !is_writable('avatar') ) $error .= 'Directory <b>avatar</b> is not writable. Change permissions (chmod 777) if you want to allow avatars.<br/>';
  }

  if ( !is_dir('upload') )
  {
    $error .= '>Directory <b>upload</b> not found.<br/>Please create this directory and make it writeable (chmod 777) if you want to allow uploads<br/>';
  }
  else
  {
    if ( !is_readable('upload') ) $error .= 'Directory <b>upload</b> is not readable. Change permissions (chmod 777) if you want to allow uploads<br/>';
    if ( !is_writable('upload') ) $error .= 'Directory <b>upload</b> is not writable. Change permissions (chmod 777) if you want to allow uploads<br/>';
  }

  if ( !empty($error) ) echo '<span class="nok">',$error,'</span></p>';
  echo '<span class="ok">Done.</span></p>';
  $error = '';

echo '<p class="endcheck">Administration tips completed.</p>';

// --------
// 5 END
// --------

echo '
<h1>Result</h1>
';
echo 'The checker did not found blocking issues in your configuration.<br/>';

  $oDB->Query('SELECT setting FROM '.$qnm_prefix.'qnmsetting WHERE param="board_offline"');
  $row = $oDB->Getrow();
  $strOff = $row['setting'];
  if ( $strOff=='1' ) echo 'Your board seams well installed, but is currently off-line.<br/>Log as Administrator and go to the Administration panel to turn your board on-line.<br/>';

echo '<br/><br/><a href="qnm_index.php">Go to QNM </a>';

// --------
// HTML END
// --------

echo '<!-- END BODY MAIN -->
</td>
</tr>
</table>
<!-- END BODY MAIN -->

<div class="footer_copy">
<span class="footer_copy">powered by <a href="http://www.qt-cute.org" class="footer_copy">QT-cute</a></span>
</div>

<!-- END PAGE CONTROL -->
</div>
<!-- END PAGE CONTROL -->

</body>
</html>';