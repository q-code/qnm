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
if ( !$oVIP->user->CanView('U') ) die($L['E_member']);

// INITIALISE

include 'bin/class/qt_class_smtp.php';
include GetLang().'qnm_reg.php';

$id = -1; QThttpvar('id','int'); if ( $id<0 ) die('Missing parameters');

$oVIP->selfurl = 'qnm_user_pwd.php';
$oVIP->selfname = $L['Change_password'];
$oVIP->exiturl = 'qnm_user.php';
$oVIP->exitname = $L['Profile'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // CHECK VALUE
  $_POST['title'] = trim($_POST['title']); if ( get_magic_quotes_gpc() ) $_POST['title'] = stripslashes($_POST['title']);
  $_POST['title'] = QTconv($_POST['title'],'U');
  $_POST['newpwd'] = trim($_POST['newpwd']); if ( get_magic_quotes_gpc() ) $_POST['newpwd'] = stripslashes($_POST['newpwd']);
  $_POST['newpwd'] = QTconv($_POST['newpwd'],'U');
  $_POST['conpwd'] = trim($_POST['conpwd']); if ( get_magic_quotes_gpc() ) $_POST['conpwd'] = stripslashes($_POST['conpwd']);
  $_POST['conpwd'] = QTconv($_POST['conpwd'],'U');
  if ( !QTispassword($_POST['title']) ) $error=$L['Old_password'].' '.$L['E_invalid'];
  if ( !QTispassword($_POST['newpwd']) ) $error=$L['New_password'].' '.$L['E_invalid'];
  if ( !QTispassword($_POST['conpwd']) ) $error=$L['Confirm_password'].' '.$L['E_invalid'];
  if ( $_POST['title']==$_POST['newpwd'] ) $error=$L['New_password'].' '.$L['E_invalid'];
  if ( $_POST['conpwd']!=$_POST['newpwd'] ) $error=$L['Confirm_password'].' '.$L['E_invalid'];

  // CHECK OLD PWD

  if ( empty($error) )
  {
    $oDB->Query('SELECT count(id) as countid FROM '.TABUSER.' WHERE id='.$id.' AND pwd="'.sha1($_POST['title']).'"');
    $row = $oDB->Getrow();
    if ($row['countid']==0) $error=$L['Old_password'].' '.$L['E_invalid'];
  }

  // EXECUTE

  if ( empty($error) )
  {
    // save new password
    $oDB->Query('UPDATE '.TABUSER.' SET pwd="'.sha1($_POST['newpwd']).'" WHERE id='.$id);

    // exit
    $oVIP->exiturl = 'qnm_user.php?id='.$id;
    $oVIP->exitname = $L['Profile'];
    $oHtml->PageBox(NULL,$L['S_update'],$_SESSION[QT]['skin_dir'],2);
  }
}

// --------
// HTML START
// --------

include 'qnm_inc_hd.php';

// CHECK ACCESS RIGHT

if ( $oVIP->user->role!='A' && $oVIP->user->id!=$id ) die(Error(11));

// QUERY

$oDB->Query('SELECT name,mail,children,photo,stats FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();
$strParentmail = QTexplodevalue($row['stats'],'parentmail');

// DISPLAY

$oHtml->Msgbox($oVIP->selfname,'msgbox profile');

echo '
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(qtHtmldecode("',$L['Missing'],': ',$L['Old_password'],'")); return false; }
  if (theForm.newpwd.value.length==0) { alert(qtHtmldecode("',$L['Missing'],': ',$L['New_password'],'")); return false; }
  if (theForm.conpwd.value.length==0) { alert(qtHtmldecode("',$L['Missing'],': ',$L['Confirm_password'],'")); return false; }
  return null;
}
//-->
</script>
<form method="post" action="',Href(),'" onsubmit="return ValidateForm(this);">
',AsImgBox(AsImg( AsAvatarSrc($row['photo']),'',$row['name'],'member'),'picbox','float:right;margin:0 0 5px 5px',$row['name']),'
<p>',$L['Old_password'],' <input type="password" id="title" name="title" pattern=".{4}.*" size="20" maxlength="24"/></p>
<p>',$L['New_password'],' <input type="password" id="newpwd" name="newpwd" pattern=".{4}.*" size="20" maxlength="24"/></p>
<p>',$L['Confirm_password'],' <input type="password" id="conpwd" name="conpwd" pattern=".{4}.*" size="20" maxlength="24"/></p>
<p><input type="submit" id="ok" name="ok" value="',$L['Save'],'"/>
<input type="hidden" name="id" value="',$id,'"/>
<input type="hidden" name="name" value="',$row['name'],'"/>
<input type="hidden" name="mail" value="',$row['mail'],'"/>
<input type="hidden" name="child" value="',$row['children'],'"/>
<input type="hidden" name="parentmail" value="',$strParentmail,'"/>',( empty($error) ? '' : '<span class="error">'.$error.' </span>' ),'</p>
',($oVIP->user->id==$id ? '' : '<div class="profile warning"><p class="profile warning">'.$L['W_Somebody_else'].'</p></div>'),'
<p class="left"><a href="',Href($oVIP->exiturl),'?id=',$id,'">&laquo; ',$oVIP->exitname,'</a></p>
</form>
<script type="text/javascript">
<!--
document.getElementById("title").focus();
//-->
</script>
';

$oHtml->Msgbox(END);

// HTML END

include 'qnm_inc_ft.php';