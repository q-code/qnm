<?php

// QNM  1.0 build:20130410

session_start();
require_once 'bin/qnm_init.php';
include Translate('qnm_adm.php');

if ( $oVIP->user->role!='A' ) die(Error(13));

// INITIALISE

$oVIP->selfurl = 'qnm_ext_statusico.php';
$oVIP->exiturl = 'qnm_adm_statuses.php';
$oVIP->selfname = 'Icons';
$oVIP->exitname = $L['Statuses'];

$arrFiles=array();
$arrStatuses=array();

// --------
// HTML START
// --------

include 'qnm_adm_inc_hd.php';

// Browse image file

$intHandle = opendir($_SESSION[QT]['skin_dir']);

$i=0;
while (false !== ($file = readdir($intHandle)))
{
  $file=strtolower($file);
  if ( $file!='.' && $file!='..' ) {
    if ( substr($file,0,6)=='status' )
    {
    $arrStatuses[] = $file;
    }
    else
    {
    if ( substr($file,0,3)!='bg_' && substr($file,0,10)!='background' ) $arrFiles[] = $file;
    }
    $i++;
  }
}
closedir($intHandle);
sort($arrStatuses);
sort($arrFiles);

echo $_SESSION[QT]['skin_dir'],', ',$i,' files<br/><br/>';

echo '
<table class="hidden">
<tr>
<td style="width:250px;vertical-align:top">
';

echo '<table class="hidden" style="background-color:#ffffff">
<groupcol><col></col><col style="width:120px"></col></groupcol>
<tr><td style="padding-left:4px"><b>Icon</b></td><td><b>File</b></td></tr>',PHP_EOL;
foreach($arrStatuses as $key=>$val)
{
  if (strtolower(substr($val,-4,4))=='.gif')
  {
  echo '<tr><td style="padding-left:4px"><img src="',$_SESSION[QT]['skin_dir'],'/',$val,'"/></td><td class="td_icon">',$val,'</td></tr>',PHP_EOL;
  }
}
echo '</table>
';
echo '
</td>
<td style="width:20px;">
<td style="width:250px;vertical-align:top">
';
echo '<table class="hidden" style="background-color:#ffffff">
<groupcol><col></col><col style="width:120px"></col></groupcol>
<tr><td style="padding-left:4px"><b>Icon</b></td><td><b>File</b></td></tr>',PHP_EOL;
foreach($arrFiles as $key=>$val)
{
  if (strtolower(substr($val,-4,4))=='.gif')
  {
  echo '<tr><td style="padding-left:4px"><img src="',$_SESSION[QT]['skin_dir'],'/',$val,'"/></td><td class="td_icon">',$val,'</td></tr>',PHP_EOL;
  }
}
echo '</table>
';
echo '
</td>
<td>&nbsp;</td>
</tr>
</table>
';

// HTML END

include 'qnm_adm_inc_ft.php';