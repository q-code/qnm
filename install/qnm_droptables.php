<?php

// QNM  1.0 build:20130410

session_start();

function SqlDrop($strTable,$strConstrain=null)
{
  global $oDB;
  if ( isset($strConstrain) && $oDB->type=='oci' ) $oDB->Query('ALTER TABLE '.$strTable.' DROP CONSTRAINT '.$strConstrain);
  $oDB->Query('DROP TABLE '.$strTable);
}

// INITIALISATION

include '../bin/class/qt_class_db.php';
include '../bin/config.php';
define('TABDOMAIN', $qnm_prefix.'qnmdomain');
define('TABSECTION', $qnm_prefix.'qnmsection');
define('TABUSER', $qnm_prefix.'qnmuser');
define('TABNE', $qnm_prefix.'qnmelement');
define('TABNL', $qnm_prefix.'qnmlink');
define('TABNC', $qnm_prefix.'qnmconn');
define('TABPOST', $qnm_prefix.'qnmpost');
define('TABSETTING', $qnm_prefix.'qnmsetting');
define('TABLANG', $qnm_prefix.'qnmlang');
define('TABDOC', $qnm_prefix.'qnmdoc');

$strAppl = 'QNM 1.0';
include 'qnm_lang_en.php';

// --------
// HTML START
// --------

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">
<head>
<title>Uninstalling ',$strAppl,'</title>
<meta charset="UTF-8" />
<meta name="description" content="QT QNM network element management" />
<meta name="keywords" content="Element,network,element management,management,qt-cute,OpenSource" />
<meta name="author" content="qt-cute.org" />
<link rel="stylesheet" href="qnm_setup.css" />
</head>

<body>

<!-- PAGE CONTROL -->
<div class="page">
<!-- PAGE CONTROL -->

<!-- HEADER BANNER -->
<div class="banner">
<img src="i_logo.gif" width="150" height="50" style="border-width:0" alt="QNM" title="QNM"/>
</div>
<p style="margin:2px 5px 0 0; font-size:8pt; text-align:right">powered by <a href="http://www.qt-cute.org" class="small">QT-cute</a></p>
<!-- END HEADER BANNER -->

<!-- BODY MAIN -->
<div style="padding:5px 10px">
<!-- BODY MAIN -->
';

echo '<p>1. Opening database connection... ';

$oDB = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDB->error) ) die ('<p style="color:red">Connection with database failed.<br/>Check that server is up and running.<br/>Check that the settings in the file <b>bin/config.php</b> are correct for your database.</p>');

echo '<span class="success">done</span></p>
<p>';

// SUBMITTED

if ( isset($_GET['a']) )
{
  switch ($_GET['a'])
  {
  case 'DropALL':
    echo ' Dropping Post...'; SqlDrop(TABPOST,'pk_'.$qnm_prefix.'qnmpost'); echo '<span class="success">done</span>.<br/>';
    echo ' Dropping Element...'; SqlDrop(TABNE,'pk_'.$qnm_prefix.'qnmelement'); echo '<span class="success">done</span>.<br/>';
    echo ' Dropping Link...'; SqlDrop(TABNL,'pk_'.$qnm_prefix.'qnmlink'); echo '<span class="success">done</span>.<br/>';
    echo ' Dropping Connection...'; SqlDrop(TABNC,'pk_'.$qnm_prefix.'qnmconn'); echo '<span class="success">done</span>.<br/>';
    echo ' Dropping Section...'; SqlDrop(TABSECTION,'pk_'.$qnm_prefix.'qnmsection'); echo '<span class="success">done</span>.<br/>';
    echo ' Dropping Domain...'; SqlDrop(TABDOMAIN,'pk_'.$qnm_prefix.'qnmdomain'); echo '<span class="success">done</span>.<br/>';
    echo ' Dropping User...'; SqlDrop(TABUSER,'pk_'.$qnm_prefix.'qnmuser'); echo '<span class="success">done</span>.<br/>';
    echo ' Dropping Setting...'; SqlDrop(TABSETTING); echo '<span class="success">done</span>.<br/>';
    echo ' Dropping Lang...'; SqlDrop(TABLANG); echo '<span class="success">done</span>.<br/>';
    echo ' Dropping Doc...'; SqlDrop(TABDOC); echo '<span class="success">done</span>.<br/>';
    break;
  case 'DropPost':
    echo ' Dropping Post...'; SqlDrop(TABPOST,'pk_'.$qnm_prefix.'qnmpost'); echo '<span class="success">done</span>.<br/>'; break;
  case 'DropElement':
    echo ' Dropping Element...'; SqlDrop(TABNE,'pk_'.$qnm_prefix.'qnmelement'); echo '<span class="success">done</span>.<br/>'; break;
  case 'DropLink':
    echo ' Dropping Link...'; SqlDrop(TABNL,'pk_'.$qnm_prefix.'qnmlink'); echo '<span class="success">done</span>.<br/>'; break;
  case 'DropConn':
    echo ' Dropping Connection...'; SqlDrop(TABNC,'pk_'.$qnm_prefix.'qnmconn'); echo '<span class="success">done</span>.<br/>'; break;
  case 'DropSection':
    echo ' Dropping Section...'; SqlDrop(TABSECTION,'pk_'.$qnm_prefix.'qnmsection'); echo '<span class="success">done</span>.<br/>'; break;
  case 'DropDomain':
    echo ' Dropping Domain...'; SqlDrop(TABDOMAIN,'pk_'.$qnm_prefix.'qnmdomain'); echo '<span class="success">done</span>.<br/>'; break;
  case 'DropUser':
    echo ' Dropping User...'; SqlDrop(TABUSER,'pk_'.$qnm_prefix.'qnmuser'); echo '<span class="success">done</span>.<br/>'; break;
  case 'DropSetting':
    echo ' Dropping Setting...'; SqlDrop(TABSETTING); echo '<span class="success">done</span>.<br/>'; break;
  case 'DropLang':
    echo ' Dropping Lang...'; SqlDrop(TABLANG); echo '<span class="success">done</span>.<br/>'; break;
  case 'DropDoc':
    echo ' Dropping Doc...'; SqlDrop(TABDOC); echo '<span class="success">done</span>.<br/>'; break;
  case 'AddPost':
    include 'qnm_setup_post.php'; echo $_GET['a'],' <span class="success">done</span>'; break;
  case 'AddElement':
    include 'qnm_setup_element.php'; echo $_GET['a'],' <span class="success">done</span>'; break;
  case 'AddLink':
    include 'qnm_setup_link.php'; echo $_GET['a'],' <span class="success">done</span>'; break;
  case 'AddConn':
    include 'qnm_setup_conn.php'; echo $_GET['a'],' <span class="success">done</span>'; break;
  case 'AddSection':
    include 'qnm_setup_section.php'; echo $_GET['a'],' <span class="success">done</span>'; break;
  case 'AddDomain':
    include 'qnm_setup_domain.php'; echo $_GET['a'],' <span class="success">done</span>'; break;
  case 'AddUser':
    include 'qnm_setup_user.php'; echo $_GET['a'],' <span class="success">done</span>'; break;
  case 'AddSetting':
    include 'qnm_setup_setting.php'; echo $_GET['a'],' <span class="success">done</span>'; break;
  case 'AddLang':
    include 'qnm_setup_lang.php'; echo $_GET['a'],' <span class="success">done</span>'; break;
  case 'AddDoc':
    include 'qnm_setup_doc.php'; echo $_GET['a'],' <span class="success">done</span>'; break;
  }
}

// Tables do drop

echo '</p><p>2. Drop the tables</p>';

echo '<form action="qnm_droptables.php" method="get"><p>';
echo '<input type="submit" name="a" value="DropALL"/> from the database ',$qnm_database,'<br/><br/>';
echo '<input type="submit" name="a" value="DropPost"/> ',TABPOST,'<br/>';
echo '<input type="submit" name="a" value="DropElement"/> ',TABNE,'<br/>';
echo '<input type="submit" name="a" value="DropLink"/> ',TABNL,'<br/>';
echo '<input type="submit" name="a" value="DropConn"/> ',TABNC,'<br/>';
echo '<input type="submit" name="a" value="DropUser"/> ',TABUSER,'<br/>';
echo '<input type="submit" name="a" value="DropSection"/> ',TABSECTION,'<br/>';
echo '<input type="submit" name="a" value="DropDomain"/> ',TABDOMAIN,'<br/>';
echo '<input type="submit" name="a" value="DropSetting"/> ',TABSETTING,'<br/>';
echo '<input type="submit" name="a" value="DropLang"/> ',TABLANG,'<br/>';
echo '<input type="submit" name="a" value="DropDoc"/> ',TABDOC,'<br/><br/>';
echo '<input type="submit" name="a" value="AddPost"/> ',TABPOST,'<br/>';
echo '<input type="submit" name="a" value="AddElement"/> ',TABNE,'<br/>';
echo '<input type="submit" name="a" value="AddLink"/> ',TABNL,'<br/>';
echo '<input type="submit" name="a" value="AddConn"/> ',TABNC,'<br/>';
echo '<input type="submit" name="a" value="AddUser"/> ',TABUSER,'<br/>';
echo '<input type="submit" name="a" value="AddSection"/> ',TABSECTION,'<br/>';
echo '<input type="submit" name="a" value="AddDomain"/> ',TABDOMAIN,'<br/>';
echo '<input type="submit" name="a" value="AddSetting"/> ',TABSETTING,'<br/>';
echo '<input type="submit" name="a" value="AddLang"/> ',TABLANG,'<br/>';
echo '<input type="submit" name="a" value="AddDoc"/> ',TABDOC,'<br/>';
echo '</p></form>
<p><a href="qnm_setup.php">install &raquo;</a></p>';

// --------
// HTML END
// --------

echo '
<!-- END BODY MAIN -->
</div>
<!-- END BODY MAIN -->

<!-- END PAGE CONTROL -->
</div>
<!-- END PAGE CONTROL -->

</div>
</body>
</html>';