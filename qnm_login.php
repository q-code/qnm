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

// INITIALISE

include GetLang().'qnm_reg.php';

$oVIP->selfurl = 'qnm_login.php';
$oVIP->selfname = $L['Login'];
$oVIP->exitname = ObjTrans('index','i',$_SESSION[QT]['index_name']);

$strName = '';
if ( isset($_GET['dfltname']) )
{
  $strName=$_GET['dfltname']; if ( get_magic_quotes_gpc() ) $strName = stripslashes($strName);
  $strName=QTconv($strName,'U',false,false);
}

// --------
// SUBMITTED for login
// --------

if ( isset($_POST['ok']) )
{
  // CHECK FORM VALUE

  $strName = $_POST['title']; if ( get_magic_quotes_gpc() ) $strName = stripslashes($strName);
  $strName = QTconv($strName,'U',false,false);
  if ( !QTislogin($strName) ) $error = $L['Username'].' '.$L['E_invalid'];

  $strPwd = $_POST['pwd']; if ( get_magic_quotes_gpc() ) $strPwd = stripslashes($strPwd);
  $strPwd = QTconv($strPwd,'U',false,false);
  if ( !QTispassword($strPwd) ) $error = $L['Password'].' '.$L['E_invalid'];

  if ( isset($_POST['u']) ) $oVIP->exiturl=strip_tags($_POST['u']);

  // EXECUTE

  if ( empty($error) )
  {
    $oVIP->user->Login($strName,$strPwd,isset($_POST['remember']));

    if ( $oVIP->user->auth )
    {
      // check banned
      $arr= QTexplode($oVIP->user->stats);
      $intClosed = ( isset($arr['closed']) ? (int)$arr['closed'] : 0 );

      if ( $intClosed>0 )
      {
        // protection against hacking of admin/moderator
        if ( $oVIP->user->id<2 || $oVIP->user->IsStaff() )
        {
        $oDB->Query('UPDATE '.TABUSER.' SET closed="0" WHERE id='.$oVIP->user->id);
        $oVIP->exiturl = 'qnm_login.php?dfltname='.$strName;
        $oVIP->exitname = $L['Login'];
        $oHtml->PageBox(NULL,'<p>You were banned...<br/>As you are admin/moderator, the protection system has re-opened your account.<br/>Re-try login now...</p>',$_SESSION[QT]['skin_dir'],0);
        }
        // normal process
        $intDays = 1;
        if ( $intClosed==2 ) $intDays = 10;
        if ( $intClosed==3 ) $intDays = 20;
        if ( $intClosed==4 ) $intDays = 30;
        $oDB->Query( 'SELECT lastdate FROM '.TABUSER.' WHERE id='.$oVIP->user->id);
        $row = $oDB->Getrow();
        if ( $row['lastdate']=='0' ) $row['lastdate']='20000101';
        $endban = DateAdd($row['lastdate'],$intDays,'day');
        if ( date('Ymd')>$endban )
        {
          $oDB->Query('UPDATE '.TABUSER.' SET closed="0" WHERE id='.$oVIP->user->id);
          $oVIP->exiturl = 'qnm_login.php?dfltname='.$strName;
          $oVIP->exitname = $L['Login'];
          $oHtml->PageBox(NULL,'<p>'.$L['Is_banned_nomore'].'</p>',$_SESSION[QT]['skin_dir'],0,'350px','login_header','login');
        }
        else
        {
          $oVIP->user->auth=false;
          $_SESSION[QT.'_usr_auth']='no';
          $oHtml->PageBox(NULL,"<h2>$strName ".strtolower($L['Is_banned'])."</h2><p>{$L['E_access']}</p><p>{$L['Retry_tomorrow']}</p>",$_SESSION[QT]['skin_dir'],0,'350px','login_header','login');
        }
      }

      // upgrade profile

      $oDB->Query('SELECT secret_a FROM '.TABUSER.' WHERE id='.$oVIP->user->id);
      $row = $oDB->Getrow();
      if ( empty($row['secret_a']) )
      {
        $oVIP->exiturl = 'qnm_user_question.php?id='.$oVIP->user->id;
        $oVIP->exitname = $L['Secret_question'];
        $oHtml->PageBox(NULL,'<h2>'.$L['Welcome'].'<br />'.$strName.'</h2><p>'.$L['Update_secret_question'].'</p>',$_SESSION[QT]['skin_dir'],0,'400px','login_header','login');
      }

      // end message
      $_SESSION['pagedialog']='L|'.$L['Welcome'].' '.$strName;
      $oHtml->Redirect('qnm_index.php');
    }
    else
    {
      $error = L('E_access');
    }
  }
}

// --------
// SUBMITTED for loggout
// --------

if ( isset($_GET['a']) ) {
if ( $_GET['a']=='out' ) {

  // LOGGING OUT

  $oVIP->Logout();

  // REBOOT

  GetParam(true);

  // check major parameters
  if ( !isset($_SESSION[QT]['skin_dir']) ) $_SESSION[QT]['skin_dir']='skin/default';
  if ( !isset($_SESSION[QT]['language']) ) $_SESSION[QT]['language']='english';
  if ( empty($_SESSION[QT]['skin_dir']) ) $_SESSION[QT]['skin_dir']='skin/default';
  if ( empty($_SESSION[QT]['language']) ) $_SESSION[QT]['language']='english';
  if ( substr($_SESSION[QT]['skin_dir'],0,5)!='skin/' ) $_SESSION[QT]['skin_dir'] = 'skin/'.$_SESSION[QT]['skin_dir'];

  session_start();
  $_SESSION['pagedialog']='W|'.L('Goodbye');
  $oHtml->Redirect('qnm_index.php');

}}

if ( !empty($error) ) $_SESSION['pagedialog']='E|'.$error;

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Username'].'")); return false; }
  if (theForm.pwd.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Password'].'")); return false; }
  return null;
}
//-->
</script>
';

include 'qnm_inc_hd.php';

$oHtml->Msgbox($oVIP->selfname,'msgbox login');

$str='';
if ( isset($_GET['s']) ) $str = '<input type="hidden" id="u" name="u" value="qnm_items.php?s='.intval($_GET['s']).'">';
if ( isset($_GET['t']) ) $str = '<input type="hidden" id="u" name="u" value="qnm_item.php?t='.intval($_GET['t']).'">';

if ( !empty($error) ) echo '<p style="text-align:left;margin:0" class="error">',$error,'</p>';
echo '<form method="post" action="',Href(),'" onsubmit="return ValidateForm(this);">
',AsImg('bin/css/pagedialog_l.png','','','','float:left;margin:10px'),'
<p style="text-align:right"><label for="title">',$L['Username'],'</label>&nbsp;<input type="text" id="title" name="title" pattern=".{2}.*" size="20" maxlength="24" value="',$strName,'"/>&nbsp;</p>
<p style="text-align:right"><label for="pwd">',$L['Password'],'</label>&nbsp;<input type="password" id="pwd" name="pwd" pattern=".{4}.*" size="20" maxlength="24" onkeyup="qtKeypress(event,\'ok\')"/>&nbsp;</p>
<p style="text-align:right"><input type="checkbox" id="remember" name="remember"/>&nbsp;<label for="remember">',$L['Remember'],'</label>&nbsp;&nbsp;
',$str,'<input type="submit" id="ok" name="ok" value="',$L['Ok'],'"/>&nbsp;</p>
<p style="text-align:right"><a class="small" href="',Href('qnm_user_new.php'),'">',$L['Register'],'</a> &middot; <a class="small" href="',Href('qnm_user_pwd_for.php'),'">',$L['Forgotten_pwd'],'</a>&nbsp;</p>
</form>
<script type="text/javascript">
<!--
document.getElementById("title").focus();
if ( document.getElementById("title").value.length>1 ) { document.getElementById("pwd").focus(); }
//-->
</script>
';

$oHtml->Msgbox(END);

// HTML END

include 'qnm_inc_ft.php';