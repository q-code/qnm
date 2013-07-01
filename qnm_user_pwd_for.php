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
if ( !$oVIP->user->CanView('V0') ) die('Access denied');

// INITIALISE

include 'bin/class/qt_class_smtp.php';
include GetLang().'qnm_reg.php';

$oVIP->selfurl = 'qnm_user_pwd_for.php';
$oVIP->selfname = $L['Forgotten_pwd'];
$oVIP->exiturl = 'qnm_index.php';
$oVIP->exitname = $L['Section'];

$strTitle = '';
$strMail = '';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check value

  $strTitle = trim(strip_tags($_POST['title'])); if ( get_magic_quotes_gpc() ) $strTitle = stripslashes($strTitle);
  $strTitle = QTconv($strTitle,'U');
  if (!QTislogin($strTitle,2)) $error=$L['Username'].' '.$L['E_invalid'];

  $strMail = trim(strip_tags($_POST['mail']));
  if (!QTismail($strMail)) $error=$L['Email'].' '.$L['E_invalid'];

  if ( empty($error) )
  {
    // check login exists
    $oDB->Query('SELECT count(id) as countid FROM '.TABUSER.' WHERE name="'.$strTitle.'" and mail="'.$strMail.'"');
    $row = $oDB->Getrow();
    if ($row['countid']!=1) $error=$L['Username'].'/'.$L['Email'].' '.$L['E_invalid'];

    // read user info
    $oDB->Query('SELECT children FROM '.TABUSER.' WHERE name="'.$strTitle.'" AND mail="'.$strMail.'"');
    $row = $oDB->Getrow();
    $strChildren = $row['children'];

    // execute
    if ( empty($error) )
    {
      $newpwd = 'qt'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
      $oDB->Query('UPDATE '.TABUSER.' SET pwd="'.sha1($newpwd).'" WHERE name="'.$strTitle.'" AND mail="'.$strMail.'"');

      // send email
      $strSubject = $_SESSION[QT]['site_name'].' - New password';
      $strMessage="Please find here after a new password to access the board {$_SESSION[QT]['site_name']}.\nLogin: %s\nPassword: %s";
      $strFile = GetLang().'mail_pwd.php';
      if ( file_exists($strFile) ) include $strFile;
      $strMessage = sprintf($strMessage,$strTitle,$newpwd);
      QTmail($strMail,QTconv($strSubject,'-4'),QTconv($strMessage,'-4'),QNM_HTML_CHAR);
      $strEndmessage = str_replace("\n",'<br/>',$strMessage);

      // exit
      if ( $_SESSION[QT]['register_mode']!='direct' ) $strEndmessage='';
      $oHtml->PageBox(NULL,$L['S_update'].'<br/><br/>'.$strEndmessage,$_SESSION[QT]['skin_dir'],0);
    }
  }
}

// --------
// HTML START
// --------

$oHtml->links[] = '
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Username'].'")); return false; }
  if (theForm.mail.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Email'].'")); return false; }
  return null;
}
//-->
</script>
';

include 'qnm_inc_hd.php';

$oHtml->Msgbox($oVIP->selfname,'msgbox login');
echo '
<form method="post" action="',Href(),'" onsubmit="return ValidateForm(this);">
<p>',$L['Reg_pass'],'</p>
<p style="text-align:right">',$L['Username'],'&nbsp;<input type="text" id="title" name="title" size="24" maxlength="24" value="',$strTitle,'"/></p>
<p style="text-align:right">',$L['Email'],'&nbsp;<input type="text" id="mail" name="mail" size="24" maxlength="64" value="',$strMail,'" onkeyup="qtKeypress(event,\'ok\')"/></p>
<p style="text-align:right">',(!empty($error) ? '<span class="error">'.$error.'</span> ' : ''),'
<input type="submit" id="ok" name="ok" value="',$L['Ok'],'"/></p>
</form>
';
$oHtml->Msgbox(END);

// HTML END

include 'qnm_inc_ft.php';