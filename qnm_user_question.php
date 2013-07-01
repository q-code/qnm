<?php

/**
* PHP version 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QNM
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
* @version    1 build:20130410
*/

session_start();
require_once 'bin/qnm_init.php';
if ( !$oVIP->user->CanView('U') ) die($L['E_member']);
$id = -1; QThttpvar('id','int'); if ($id<0) die('Missing parameters');

// INITIALISE

include 'bin/class/qt_class_smtp.php';
include Translate('qnm_reg.php');

$oVIP->selfurl = 'qnm_user_question.php';
$oVIP->selfname = L('Secret_question');
$oVIP->exiturl = Href('qnm_user.php').'?id='.$id;
$oVIP->exitname = '&laquo; '.$L['Profile'];

// QUERY

$oDB->Query('SELECT name,photo,secret_q,secret_a FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // CHECK VALUE and protection against injection

  $strQ = trim($_POST['secret_q']); if ( get_magic_quotes_gpc() ) $strQ = stripslashes($strQ);
  $strA = trim($_POST['secret_a']); if ( get_magic_quotes_gpc() ) $strA = stripslashes($strA);

  if ( empty($error) )
  {
    // save new password
    $oDB->Query('UPDATE '.TABUSER.' SET secret_q="'.QTconv($strQ,'3').'",secret_a="'.QTconv(strtolower($strA),'3').'" WHERE id='.$id);

    // exit
    $oVIP->exitname = $L['Profile'];
    $oHtml->PageBox(NULL,$L['S_update'],$_SESSION[QT]['skin_dir'],2);
  }
}

// --------
// HTML START
// --------

include 'qnm_inc_hd.php';

$oHtml->Msgbox($oVIP->selfname,'msgbox profile');

echo '<form method="post" action="',Href(),'" onsubmit="return ValidateForm(this);">
',AsImgBox(AsImg( AsAvatarSrc($row['photo']),'',$row['name'],'member'),'picbox','float:right;margin:0 0 5px 5px',$row['name']),'
<p>',$L['H_Secret_question'],'</p>
<p><select id="secret_q" name="secret_q">',QTasTag($L['Secret_q'],$row['secret_q']),'</select></p>
<p><input type="text" id="secret_a" name="secret_a" size="32" maxlength="255" value="',$row['secret_a'],'" /></p>
<p>
<input type="submit" id="ok" name="ok" value="',$L['Save'],'" />
<input type="hidden" name="id" value="',$id,'" />
<input type="hidden" name="name" value="',$row['name'],'" />',( empty($error) ? '' : '<span class="error">'.$error.' </span>' ),'
</p>
',($oVIP->user->id==$id ? '' : '<div class="profile warning"><p class="profile warning">'.$L['W_Somebody_else'].'</p></div>'),'
<p class="left"><a href="',Href($oVIP->exiturl),'">',$oVIP->exitname,'</a></p>
</form>
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.secret_a.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Secret_question'].'")); return false; }
  return null;
}
//-->
</script>
';

$oHtml->Msgbox(END);

// HTML END

include 'qnm_inc_ft.php';