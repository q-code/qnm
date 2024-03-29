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
include Translate('qnm_adm.php');

if ( $oVIP->user->role!='A' ) die(Error(13));

// INITIALISE

$tt='en';
$v = '';
if ( isset($_GET['v']) ) $v = strip_tags($_GET['v']);
if ( isset($_POST['v']) ) $v = strip_tags($_POST['v']);
if ( empty($v) ) $error = 'Missing file name';
if ( isset($_GET['tt']) ) $tt = strip_tags($_GET['tt']);
if ( isset($_POST['tt']) ) $tt = strip_tags($_POST['tt']);

$intSize = 100;

$oVIP->selfurl = 'qnm_adm_tags_upload.php';
$oVIP->selfname = $L['Add'].' CSV '.L('file');
$oVIP->exiturl = 'qnm_adm_tags.php';
$oVIP->exitname = $L['Tags'];

// --------
// SUBMITTED FOR UPLOAD
// --------

if ( isset($_POST['ok']) )
{
  // Check uploaded document

  $error = InvalidUpload($_FILES['title'],'csv,txt,text','',500);

  // Save

  if ( empty($error) )
  {
    copy($_FILES['title']['tmp_name'],'upload/'.$v);
    unlink($_FILES['title']['tmp_name']);
    $oHtml->PageBox(NULL,$L['S_update'],$_SESSION[QT]['skin_dir'],2);
  }
}

// --------
// HTML START
// --------

$oHtml->scripts[] = '
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert("'.$L['Missing'].': File"); return false; }
  return null;
}
function ValidateWarning(str)
{
  if (str.value=="'.$v.'")
  {
  document.getElementById("warning").style.visibility="visible";
  }
  else
  {
  document.getElementById("warning").style.visibility="hidden";
  }
  return null;
}
//-->
</script>
';
include 'qnm_adm_inc_hd.php';

$str='';
if ( file_exists('upload/'.$v) ) $str = 'upload/'.$v;

echo '<br/>',PHP_EOL;

$oHtml->Msgbox($oVIP->selfname,'msgbox upload');

echo '<form method="post" action="',$oVIP->selfurl,'" enctype="multipart/form-data" onsubmit="return ValidateForm(this);">',PHP_EOL;

if ( !empty($error) ) echo '<span class="error">',$error,'</span>',PHP_EOL;
echo '<p style="text-align:right">',PHP_EOL;
echo $L['File'],': <input type="hidden" name="max_file_size" value="',($intSize*1024),'"/>',PHP_EOL;
echo '<input type="file" id="title" name="title" size="32"/><br/><br/><br/><br/>',PHP_EOL;
echo $L['Destination'],':  upload/<input type="text" id="v" name="v" size="25" maxlength="25" value="',$v,'" onkeyup="ValidateWarning(this);"/><br/><br/>',PHP_EOL;
echo '<span id="warning" class="warning">',(file_exists('upload/'.$v) ? $L['E_overwrite_file'].' ['.$v.']' : ''),'</span> ';
echo '<input type="hidden" name="tt" value="',$tt,'"/>',PHP_EOL;
echo '<input type="submit" name="ok" value="',$L['Ok'],'"/></p>',PHP_EOL;
echo '</form>',PHP_EOL;
echo '<p><a href="',$oVIP->exiturl,'?tt=',$tt,'">&laquo; ',$oVIP->exitname,'</a></p>';

$oHtml->Msgbox(END);

// HTML END

include 'qnm_adm_inc_ft.php';
