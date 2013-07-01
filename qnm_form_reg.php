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
if ( !$oVIP->user->CanView('V0') ) HtmlPage(10);

// INITIALISE

include 'bin/class/qt_class_smtp.php';
include GetLang().'qnm_reg.php';

$oVIP->selfurl = 'qnm_form_reg.php';
$oVIP->selfname = $L['Register'];
if ( $_SESSION[QT]['register_mode']=='backoffice' ) $oVIP->selfname .= ' ('.L('request').')';

$strChild = '0';
if ( isset($_GET['c']) ) $strChild = $_GET['c'];
if ( isset($_POST['child']) ) $strChild = $_POST['child'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // pre-checks
  if ( !isset($_POST['parentmail']) ) $_POST['parentmail']='';
  if ( empty($_POST['mail']) ) $error=$L['Email'].' '.$L['E_invalid'];
  if ( empty($_POST['title']) ) $error=$L['Username'].' '.$L['E_invalid'];
  if ( $_SESSION[QT]['register_safe']!='none' )
  {
  if ( trim($_POST['code'])=='' ) $error = $L['Type_code'];
  if ( strlen($_POST['code'])!=6 ) $error = $L['Type_code'];
  }

  // check name
  if ( empty($error) )
  {
    if ( get_magic_quotes_gpc() ) $_POST['title'] = stripslashes($_POST['title']);
    $_POST['title'] = QTconv($_POST['title'],'U');
    if ( !QTislogin($_POST['title']) ) $error=$L['Username'].' '.$L['E_invalid'];
  }

  // check mail
  if ( empty($error) )
  {
    $_POST['mail'] = trim($_POST['mail']);
    if ( !QTismail($_POST['mail']) ) $error=$L['Email'].' '.$L['E_invalid'];
    $_POST['parentmail'] = trim($_POST['parentmail']);
    if ( !empty($_POST['parentmail']) ) { if ( !QTismail($_POST['parentmail']) ) $error=L('Parent_email').' '.$L['E_invalid']; }
  }

  // check password
  if ( empty($error) && $_SESSION[QT]['register_mode']=='direct' )
  {
    if ( get_magic_quotes_gpc() ) $_POST['pwd'] = stripslashes($_POST['pwd']);
    $_POST['pwd'] = QTconv($_POST['pwd'],'U');
    if ( !QTispassword($_POST['pwd']) ) $error = $L['Password'].' '.$L['E_invalid'];

    if ( get_magic_quotes_gpc() ) $_POST['conpwd'] = stripslashes($_POST['conpwd']);
    $_POST['conpwd'] = QTconv($_POST['conpwd'],'U');
    if ( !QTispassword($_POST['conpwd']) ) $error = $L['Password'].' '.$L['E_invalid'];
  }
  if ( empty($error) && $_SESSION[QT]['register_mode']=='direct' )
  {
    if ( $_POST['conpwd']!=$_POST['pwd'] ) $error = $L['Password'].' '.$L['E_invalid'];
  }

  // check code
  if ( empty($error) )
  {
    if ( $_SESSION[QT]['register_safe']!='none' )
    {
    $strCode = strtoupper(strip_tags(trim($_POST['code'])));
    if ($strCode=='') $error = $L['Type_code'];
    if ( $_SESSION['textcolor']!=sha1($strCode) ) $error = $L['Type_code'];
    }
  }

  // --------
  // register user
  // --------

  if ( empty($error) )
  {
    if ( $_SESSION[QT]['register_mode']=='backoffice' )
    {
      // Send email
      $strSubject = $_SESSION[QT]['site_name'].' - Registration request';
      $strMessage = "This user request access to the board {$_SESSION[QT]['site_name']}.\nUsername: %s\nEmail: %s";
      $strFile = GetLang().'mail_request.php';
      if ( file_exists($strFile) ) include $strFile;
      $strMessage = sprintf($strMessage,$_POST['title'],$_POST['mail']);
      QTmail($_SESSION[QT]['admin_email'],QTconv($strSubject,'-4'),QTconv($strMessage,'-4'),QNM_HTML_CHAR);
      $oHtml->PageBox(NULL,'<h2>'.L('Request_completed').'</h2><p>'.L('Reg_mail').'</p>',$_SESSION[QT]['skin_dir'],0,'350px','login_header','login');
    }
    else
    {
      // email code
      if ( $_SESSION[QT]['register_mode']=='email' ) $_POST['pwd'] = 'QT'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);

      // Add user
      cVIP::AddUser($_POST['title'],$_POST['pwd'],$_POST['mail'],'U',$strChild,$_POST['parentmail']);

      // Unregister global sys (will be recomputed on next page)
      Unset($_SESSION[QT]['sys_members']);
      Unset($_SESSION[QT]['sys_states']);

      // Send email
      $strSubject = $_SESSION[QT]['site_name'].' - Welcome';
      $strMessage = "Please find here after your login and password to access the board {$_SESSION[QT]['site_name']}.\nLogin: %s\nPassword: %s";
      $strFile = GetLang().'mail_registred.php';
      if ( file_exists($strFile) ) include $strFile;
      $strMessage = sprintf($strMessage,$_POST['title'],$_POST['pwd']);
      QTmail($_POST['mail'],QTconv($strSubject,'-4'),QTconv($strMessage,'-4'),QNM_HTML_CHAR);

      // END MESSAGE
      if ( $_SESSION[QT]['register_mode']=='email' )
      {
      $oVIP->exiturl = 'qnm_index.php';
      $oVIP->exitname = ObjTrans('index','i',$_SESSION[QT]['index_name']);
      }
      else
      {
      $L['Reg_mail'] = S;
      $oVIP->exiturl = 'qnm_login.php?dfltname='.urlencode($_POST['title']);
      $oVIP->exitname = $L['Login'];
      }
      $oHtml->PageBox(NULL,'<h2>'.L('Register_completed').'</h2><p>'.L('Reg_mail').'</p>',$_SESSION[QT]['skin_dir'],0,'350px','login_header','login');
    }
  }
}

// --------
// HTML START
// --------

$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qnm_main2.css" title="cssmain" />';
$oHtml->scripts[] = '<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Choose_name'].'")); return false; }
  if (theForm.mail.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Your_mail'].'")); return false; }
  if (theForm.code.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Security'].'")); return false; }
  if (theForm.code.value=="QT") { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Security'].'")); return false; }
  return null;
}
function MinChar(strField,strValue)
{
  if ( strValue.length>0 && strValue.length<4 )
  {
  document.getElementById(strField+"_err").innerHTML="<br/>'.$L['E_min_4_char'].'";
  return null;
  }
  else
  {
  document.getElementById(strField+"_err").innerHTML="";
  return null;
  }
}
$(function() {
  var doc = document;
  $("#title").focus(function() { doc.getElementById("title").pattern="^.{4}.*"; doc.getElementById("title_err").innerHTML=""; });
  $("#title").blur(function() {
  		$.post("qnm_j_exists.php",
      {f:"name",v:$("#title").val(),e1:"'.$L['E_min_4_char'].'",e2:"'.$L['E_already_used'].'"},
      function(data) {
        if ( data.length>0 )
      	{
      		doc.getElementById("title").pattern="X";
      	  doc.getElementById("title_err").innerHTML=data;
      	}
        else
      	{
      	  doc.getElementById("title").pattern="^.{4,}$";
      	  doc.getElementById("title_err").innerHTML="";
        }
      });
  });
});
//-->
</script>
';

include 'qnm_inc_hd.php';

// DEFAULT VALUE RECOVERY (na)

if ( !isset($_POST['title']) ) $_POST['title']='';
if ( !isset($_POST['pwd']) ) $_POST['pwd']='';
if ( !isset($_POST['conpwd']) ) $_POST['conpwd']='';
if ( !isset($_POST['mail']) ) $_POST['mail']='';
if ( !isset($_POST['parentmail']) ) $_POST['parentmail']='';

if ( $_SESSION[QT]['register_safe']=='text' )
{
  $keycode = 'QT'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
  $_SESSION['textcolor'] = sha1($keycode);
}

$oHtml->Msgbox($oVIP->selfname,'msgbox about');

echo '<table class="hidden register">
<tr class="hidden">
<td class="hidden">
<div class="register">
<form method="post" action="',Href(),'" onsubmit="return ValidateForm(this);">
<fieldset class="fs_register">
<legend>',$L['Username'],'</legend>
<span class="small">',$L['Choose_name'],'</span>&nbsp;
<input type="text" id="title" name="title" size="20" maxlength="24" value="',$_POST['title'],'" pattern="^.{4,}$" /><br/><span id="title_err" class="error"></span><br/>
';
if ( $_SESSION[QT]['register_mode']=='direct' )
{
echo '<span class="small">',$L['Choose_password'],'</span>&nbsp;<input type="password" id="pwd" name="pwd" pattern=".{4}.*" size="20" maxlength="24" value="',$_POST['pwd'],'" /><span id="pwd_err" class="error"></span><br/>';
echo '<span class="small">',$L['Confirm_password'],'</span>&nbsp;<input type="password" id="conpwd" name="conpwd" pattern=".{4}.*" size="20" maxlength="24" value="',$_POST['conpwd'],'" /><span id="conpwd_err" class="error"></span><br/>';
}
else
{
echo '<span class="small">',$L['Password_by_mail'],'</span><br/>';
}
echo '
</fieldset>
<fieldset class="fs_register">
<legend>',$L['Email'],'</legend>
<span class="small">',$L['Your_mail'],'</span>&nbsp;<input type="text" id="mail" name="mail" size="32" maxlength="64" value="',$_POST['mail'],'"/><span id="mail_err" class="error"></span><br/>
';
echo '
</fieldset>
<fieldset class="fs_register">
<legend>',$L['Security'],'</legend>
';
if ( $_SESSION[QT]['register_safe']=='image' ) echo '<img width="100" height="35" src="admin/qnm_icode.php" alt="security" style="text-align:right"/> <input type="text" name="code" pattern=".{8}.*" size="8" maxlength="8" value="QT"/><br/><span class="small">',$L['Type_code'],'</span>';
if ( $_SESSION[QT]['register_safe']=='text' ) echo $keycode,'&nbsp;<input type="text" id="code" name="code" pattern=".{6}.*" size="8" maxlength="8" value="QT"/><br/><span class="small">',$L['Type_code'],'</span>';
echo '
</fieldset>
<input type="hidden" name="child" value="',$strChild,'"/>
',(!empty($error) ? '<p class="error">'.$error.'</p>' : ''),'<input type="submit" name="ok" value="',($_SESSION[QT]['register_mode']=='backoffice' ? $L['Send'] : $L['Register']),'"/>
</form>
</div>
</td>
<td class="hidden">
<div class="registerhelp">
',L('Reg_help'),'
</div>
</td>
</tr>
</table>
';

$oHtml->Msgbox(END);

// HTML END

include 'qnm_inc_ft.php';