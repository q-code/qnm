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
if ( $oVIP->user->role=='V' ) HtmlPage(11);
if ( !$oVIP->user->CanView('V6') ) die(Error(11));

// INITIALISE

$id = -1; // post
$pid = ''; // parent element
$ok = '';// submitted
QThttpvar('id pid ok', 'int str str');
if ( $id<0 ) die('Missing parameters id');
if ( empty($pid) ) die('Missing parameters parent-id');

$oVIP->selfurl = 'qnm_form_note.php';
$oVIP->selfname = 'Edit note for';
$oVIP->exiturl = 'qnm_item.php?nid='.$pid;

$oPost = new cPost($id);
if ( !$oVIP->user->IsStaff() && $oVIP->user->id!=$oPost->userid ) die(Error(11));

$oNE = new cNE($pid);
$s = $oNE->section;

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  if ( !isset($_POST['note']) ) $error='Message error';
  if ( empty($error) ) $error = cPost::CheckInput($_POST['note'],true);

  // Update post
  if ( empty($error) )
  {
    if ( isset($_POST['update']) )
      $oPost->UpdateField( array('textmsg','issuedate'), array($_POST['note'],date('Ymd his')) );
    else
      $oPost->UpdateField( array('textmsg'), array($_POST['note']) );
    $_SESSION['qnm_usr_lastpost']=time();
    $_SESSION['pagedialog']='O|'.L('S_update');
    $oHtml->Redirect($oVIP->exiturl);
  }
}

// --------
// HTML START
// --------

include 'qnm_inc_hd.php';

echo '<h2>'.$oVIP->selfname.'</h2>',PHP_EOL;

echo '<p>',$oNE->Dump(),'<br/>',$oNE->DumpContent(false),'</p>';

echo '
<form method="post" action="',Href(),'">
<table class="data_o">
<tr class="data_o"><td class="headfirst" style="width:100px">',L('Author'),'</td><td>',$oPost->username,'</td></tr>
<tr class="data_o"><td class="headfirst" style="width:100px">',L('Creation_date'),'</td><td>',QTdatestr($oPost->issuedate,'$','$',true),' <input type="checkbox" name="insertdate" id="insertdate"/><label for="insertdate">',L('Change'),'</label></td></tr>
<tr class="data_o"><td class="headfirst" style="width:100px"><label for="note">',$L['Message'],'</label></td><td><textarea name="note" rows="6" wrap="virtual" cols="80">',$oPost->textmsg,'</textarea></td></tr>
<tr class="data_o"><td class="headfirst">&nbsp;</td><td>
<input type="hidden" name="id" value="',$id,'"/>
<input type="hidden" name="pid" value="',$pid,'"/>
<input type="submit" name="ok" value="',$L['Save'],'"/>&nbsp;';
if ( !empty($error) ) echo '<span class="error">',$error,'</span>';
echo '</td></tr>
</table>
</form>
';

echo '<p><a href="',$oVIP->exiturl,'">&laquo; ',$oVIP->exitname,'</a></p>
';

// HTML END

include 'qnm_inc_ft.php';