<?php // v 1.0 build:20130410

session_start();

if ( !isset($_SESSION['qnm_setup_lang']) ) $_SESSION['qnm_setup_lang']='en';

include 'qnm_lang_'.$_SESSION['qnm_setup_lang'].'.php';
include '../bin/config.php'; if ( $qnm_dbsystem=='sqlite' ) $qnm_database = '../'.$qnm_database;
if ( isset($qnm_install) ) { define('QT','qnm'.substr($qnm_install,-1)); } else { define('QT','qnm'); }
include '../bin/class/qt_class_db.php';
include '../bin/qnm_fn_base.php';

function QTismail($str)
{
  if ( !is_string($str) ) die('QTismail: arg #1 must be a string');

  if ( $str!=trim($str) ) return false;
  if ( $str!=strip_tags($str) ) return false;
  if ( !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i",$str) ) return false;
  return true;
}

$strAppl     = 'QNM 1.0';
$strPrevUrl  = 'qnm_setup_2.php';
$strNextUrl  = 'qnm_setup_4.php';
$strPrevLabel= $L['Back'];
$strNextLabel= $L['Next'];

// Read admin_email setting

$oDB = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
define('TABSETTING', $qnm_prefix.'qnmsetting');
GetParam(true,'param="admin_email"');
if ( !isset($_SESSION[QT]['admin_email']) ) $_SESSION[QT][admin_email]='';

// --------
// HTML START
// --------

include 'qnm_setup_hd.php';

// Submitted

if ( !empty($_POST['admin_email']) )
{
  if ( QTismail($_POST['admin_email']) )
  {
    $_SESSION[QT]['admin_email'] = $_POST['admin_email'];
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['admin_email'].'" WHERE param="admin_email"');
    if ( empty($oDB->error) )
    {
    echo '<div class="setup_ok">',$L['S_save'],'</div>';
    }
    else
    {
    echo '<div class="setup_err">',sprintf ($L['E_connect'],$qnm_database,$qnm_host),'</div>';
    }
  }
  else
  {
  echo '<div class="setup_err">Invalid e-mail</div>';
  }
}

// Form

echo '<h2>',$L['Board_email'],'</h2>
<form method="post" name="install" action="qnm_setup_3.php">
<table class="hidden">
<tr valign="top">
<td class="hidden">',$L['Board_email'],' <input type="email" name="admin_email" value="',$_SESSION[QT]['admin_email'],'" size="34" maxlength="100"/>&nbsp;<input type="submit" name="ok" value="',$L['Ok'],'"/></td>
<td class="hidden" style="width:40%"><div class="setup_help">',$L['Help_3'],'</div></td>
</tr>
</table>
</form>
';

include 'qnm_setup_ft.php';