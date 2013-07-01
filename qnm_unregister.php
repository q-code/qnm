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
 * @copyright  2013 The PHP Group
 * @version    1.0 build:20130410
 */

session_start();
require_once 'bin/qnm_init.php';
if ( !$oVIP->user->CanView('U') ) die(Error(11));
$id = -1; QThttpvar('id','int'); if ( $id<0 ) die('Missing id...');

// --------
// INITIALISE
// --------

include 'bin/class/qt_class_smtp.php';
include Translate('qnm_reg.php');

$oVIP->selfurl = 'qnm_unregister.php?id='.$id;
$oVIP->selfname = $L['Unregister'];
$oVIP->exitname = $L['Exit'];

if ( $id<2 ) $oHtml->PageBox(NULL,$L['E_access'].'<br />Visitor and System administrator cannot be deleted.',$_SESSION[QT]['skin_dir'],0);;
if ( $oVIP->user->id!=$id ) $oHtml->PageBox(NULL,$L['E_access'].'<br />Only user himself can unregister. Administrators can delete users.',$_SESSION[QT]['skin_dir'],0);

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check password
  $oDB->Query( 'SELECT count(id) as countid FROM '.TABUSER.' WHERE id='.$id.' AND pwd="'.sha1($_POST['title']).'"' );
  $row = $oDB->Getrow();
  if ($row['countid']==0) $error=$L['Password'].' '.$L['E_invalid'];

  // execute and exit
  if ( empty($error) )
  {
    $oDB->Query( 'SELECT * FROM '.TABUSER.' WHERE id='.$id );
    $row = $oDB->Getrow();
    $oVIP->Unregister($row);
    $oVIP->exiturl='qnm_login.php?a=out';
    $oHtml->PageBox(NULL,$L['S_delete'],$_SESSION[QT]['skin_dir'],2);
  }
}

// --------
// HTML START
// --------

$oDB->Query( 'SELECT * FROM '.TABUSER.' WHERE id='.$id );
$row = $oDB->Getrow();

$oHtml->links[] = '
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(html_entity_decode("'.$L['Missing'].': '.$L['Password'].'")); return false; }
  return null;
}
//-->
</script>
';

include 'qnm_inc_hd.php';

$oHtml->Msgbox($oVIP->selfname,'msgbox login');

$str = $L['H_Unregister'].'
<form method="post" action="'.$oVIP->selfurl.'" onsubmit="return ValidateForm(this);">
<input type="hidden" name="id" value="'.$id.'" />
<p>'.$L['Password'].' <input type="password" id="title" name="title" pattern=".{4}.*" size="20" maxlength="32" /> <input type="submit" name="ok" value="'.$L['Ok'].'" /> <span id="title_err" class="error"></span></p>
</form>
<script type="text/javascript">
<!--
document.getElementById("title").focus();
//-->
</script>
';
if ( $row['role']!='U' ) $str = '<p>'.$row['name'].' is a Staff member.<br />To unregister a staff member, an administrator must first change role to User, or use the delete function.</p>';
if ( $id<2 ) $str = '<p>Admin and Visitor cannot be removed...</p>';

if ( !empty($error) ) echo '<p id="infomessage" class="error">',$error,'</p>';
echo AsImgBox(AsImg(AsAvatarSrc($row['photo']),'',$row['name']),'','float:right;margin:0 0 5px 5px;padding:5px;border:1px solid #dddddd'),'
<h2>',$row['name'],'</h2>
',$str,'
<p><a href="',$oVIP->exiturl,'">',$oVIP->exitname,'</a></p>
';

$oHtml->Msgbox(END);


include 'qnm_inc_ft.php';