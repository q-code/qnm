<?php

/**
* PHP versions 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QNM
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2013 The PHP Group
* @version    1 build:20130410
*/

session_start();
require_once 'bin/qnm_init.php';
if ( !$oVIP->user->CanView('U') ) die('Access denied');

// --------
// INITIALISE
// --------

include 'bin/class/qt_class_smtp.php';
include Translate('qnm_reg.php');

$id = -1; QThttpvar('id','int'); if ( $id<0 ) die('Missing user id...');

$oVIP->selfurl = 'qnm_user_rename.php';
$oVIP->selfname = $L['Rename'];
$oVIP->exiturl = 'qnm_user.php?id='.$id;
$oVIP->exitname = '&laquo; '.$L['Profile'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check name
  if ( empty($error) )
  {
    $strName = trim(strip_tags($_POST['title'])); if ( get_magic_quotes_gpc() ) $strName = stripslashes($strName);
    if ( !QTislogin($strName) ) $error = $L['Username'].' '.Error(1);
    if ( empty($error) )
    {
    $oDB->Query('SELECT count(*) as countid FROM '.TABUSER.' WHERE name="'.$strName.'"');
    $row = $oDB->Getrow();
    if ( $row['countid']!=0 ) $error = '['.$strName.'] '.$L['E_already_used'];
    }
  }

  // execute and exit
  if ( empty($error) )
  {
    $oDB->Query('UPDATE '.TABUSER.' SET name="'.$strName.'" WHERE id='.$id);
    $oDB->Query('UPDATE '.TABPOST.' SET username="'.$strName.'" WHERE userid='.$id);
    $oDB->Query('UPDATE '.TABPOST.' SET modifname="'.$strName.'" WHERE modifuser='.$id);
    $oDB->Query('UPDATE '.TABTOPIC.' SET firstpostname="'.$strName.'" WHERE firstpostuser='.$id);
    $oDB->Query('UPDATE '.TABTOPIC.' SET lastpostname="'.$strName.'" WHERE lastpostuser='.$id);
    $oDB->Query('UPDATE '.TABDATA.' SET fieldusername="'.$strName.'" WHERE fielduserid='.$id);
    $oHtml->PageBox(NULL,$L['S_update'],$_SESSION[QT]['skin_dir'],2);
  }
}

$oDB->Query('SELECT name,photo FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();
$row['name'] = QTconv($row['name'],'5');

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Username'].'")); return false; }
  return null;
}
$(function() {
  $("#title").keyup(function() {
    if ($("#title").val().length>1)
    {
      $.post("qnm_j_exists.php",
      {f:"name",v:$("#title").val(),e1:"'.$L['E_min_4_char'].'",e2:"'.$L['E_already_used'].'"},
      function(data) { if ( data.length>0 ) document.getElementById("title_err").innerHTML=data; });
    }
    else
    {
      document.getElementById("title_err").innerHTML="";
    }
  });
});
//-->
</script>
';

include 'qnm_inc_hd.php';

$oHtml->Msgbox($oVIP->selfname,'msgbox profile');

echo '<form method="post" action="',Href(),'" onsubmit="return ValidateForm(this);">
',AsImgBox(AsImg( AsAvatarSrc($row['photo']),'',$row['name'],'member'),'picbox','float:right;margin:0 0 5px 5px',$row['name']),'
<input type="hidden" name="id" value="',$id,'"/>
<h2>',$row['name'],'</h2>
<p>',$L['Choose_name'],'</p>
<p><input type="text" id="title" name="title" size="20" maxlength="32" onfocus="document.getElementById(\'title_err\').innerHTML=\'\';"/> <input type="submit" name="ok" value="',$L['Save'],'"/><br/><span id="title_err" class="small error"></span></p>
</form>
',( empty($error) ? '' : '<span class="error">'.$error.' </span>' ),($oVIP->user->id==$id ? '' : '<div class="profile warning"><p class="profile warning">'.$L['W_Somebody_else'].'</p></div>'),'
<a href="',Href($oVIP->exiturl),'">',$oVIP->exitname,'</a>
';

$oHtml->Msgbox(END);

// --------
// HTML END
// --------

include 'qnm_inc_ft.php';