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
include Translate('qnm_zone.php');

if ( $oVIP->user->role!='A' ) die(Error(13));

// INITIALISE

$oVIP->selfurl = 'qnm_adm_time.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_settings'].'</span><br/>'.$L['Application_time'];
$oVIP->exiturl = 'qnm_adm_region.php';
$oVIP->exitname = $L['Adm_settings'].' '.$L['Adm_region'];

if ( PHP_VERSION_ID<50200 )
{
  $oHtml->PageBox(PHP_VERSION_ID,'Sorry...<br/>Your webhost must support PHP 5.2 or next to allow application time change.',$_SESSION[QT]['skin_dir'],0);
  exit;
}

// Default time zone setting

if ( !isset($_SESSION[QT]['defaulttimezone']) ) $_SESSION[QT]['defaulttimezone']=date_default_timezone_get();

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  $strTZI = strip_tags(trim($_POST['tzi']));
  if ( !in_array($strTZI,DateTimeZone::listIdentifiers()) ) $error='Unknown time zone identifier ['.$strTZI.']';

  // Save change. Attention, it can be a empty string (i.e. No change in the timezone)

  if ( empty($error) )
  {
  $_SESSION[QT]['defaulttimezone']=$strTZI;
  $oDB->Query('DELETE FROM '.TABSETTING.' WHERE param="defaulttimezone"');
  $oDB->Query('INSERT INTO '.TABSETTING.' VALUES ("defaulttimezone", "'.$_SESSION[QT]['defaulttimezone'].'")');
  }

  // Exit
  if ( empty($error) ) $strInfo = $L['S_save'];
}

// --------
// HTML START
// --------

include 'qnm_adm_inc_hd.php';

echo '<form method="post" action="',$oVIP->selfurl,'">
<table class="data_o">
';
if ( $_SESSION[QT]['defaulttimezone']!='' )
{
date_default_timezone_set($_SESSION[QT]['defaulttimezone']); // restore application timezone
}
$oDT = new DateTime();

echo '<tr class="data_o"><td class="headgroup" colspan="3">Application time zone</td></tr>
';
echo '<tr class="data_o">
<td class="headfirst" style="width:150px;">Identifier</td>
<td style="width:225px;">',$oDT->getTimezone()->getName(),'</td>
<td><span class="help">&nbsp;</span></td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst" style="width:150px;">Time</td>
<td style="width:225px;">',$oDT->format('H:i:s'),'</td>
<td><span class="help">',$oDT->format(DATE_ATOM),'</span></td>
</tr>
';
echo '<tr class="data_o"><td class="headgroup" colspan="3">Change time zone</td></tr>
';
echo '<tr class="data_o">
<td class="headfirst" style="width:150px;">Identifier</td>
<td style="width:225px;"><input type="text" id="tzi" name="tzi" size="32" value="',$oDT->getTimezone()->getName(),'"/></td>
<td><span class="help">Time zone identifier</span></td>
</tr>
';
echo '<tr class="data_o"><td class="headgroup" colspan="3" style="padding:6px; text-align:center"><input type="submit" name="ok" value="',$L['Save'],'"/></td></tr>
';
echo '</table>
</form>
';

$arrGroup = array('AFRICA'=>'Africa','ANTARCTICA'=>'Antarctica','ARCTIC'=>'Arctic','AMERICA'=>'America','ASIA'=>'Asia','ATLANTIC'=>'Atlantic','AUSTRALIA'=>'Australia','EUROPE'=>'Europe','INDIAN'=>'Indian','PACIFIC'=>'Pacific','OTHERS'=>'Universal &amp; others');
$strGroup='EUROPE';
$arrTZI = array();
if ( isset($_GET['group']) )
{
  $strGroup = strtoupper(strip_tags(trim($_GET['group'])));
  if ( !array_key_exists($strGroup,$arrGroup) ) $strGroup='ALL';
}
switch($strGroup)
{
case 'ALL':
  $arrTZI = DateTimeZone::listIdentifiers();
  break;
case 'OTHERS':
  $arrTZI = DateTimeZone::listIdentifiers();
  foreach($arrTZI as $i=>$str) {
  foreach($arrGroup as $s=>$strName) {
  if ( $s==strtoupper(substr($str,0,strlen($s))) ) unset($arrTZI[$i]);
  }}
  break;
default:
  foreach(DateTimeZone::listIdentifiers() as $str)
  {
  if ( $strGroup==strtoupper(substr($str,0,strlen($strGroup))) ) $arrTZI[]=$str;
  }
  break;
}

echo '<table class="hidden">
<tr>
<td class="hidden">&nbsp;</td>
<td class="hidden" style="padding:10px 4px 4px 4px"><b>Search by zone</b></td>
<td class="hidden" style="padding:10px 4px 4px 4px"><b>Time zone identifiers</b></td>
</tr>
<tr>
<td class="hidden">&nbsp;</td>
<td class="hidden" style="padding:4px">
';
foreach($arrGroup as $strKey=>$strValue) echo '<a href="qnm_adm_time.php?group=',$strKey,'">',$strValue,'</a><br/>';
echo '<br/><a href="qnm_adm_time.php?group=ALL">Show all</a>';
echo '</td>
<td class="hidden" style="padding:4px"><div class="scrollmessage small">',implode('<br/>',$arrTZI),'</div></td>
</tr>
</table>
';

echo '<p>&laquo; <a href="',$oVIP->exiturl,'">',$oVIP->exitname,'</a></p>';

// HTML END

include 'qnm_adm_inc_ft.php';