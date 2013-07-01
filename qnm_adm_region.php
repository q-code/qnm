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
include 'bin/qnm_lang.php'; $arrLangDir = QTarrget($arrLang,2); // this creates an array with only the [iso]directories
include Translate('qnm_adm.php');
include Translate('qnm_zone.php');

if ( $oVIP->user->role!='A' ) die(Error(13));

// INITIALISE

$oVIP->selfurl = 'qnm_adm_region.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_settings'].'</span><br/>'.$L['Adm_region'];
$oVIP->exiturl = $oVIP->selfurl;
$oVIP->exitname = $oVIP->selfname;

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  $_SESSION[QT]['time_zone'] = substr($_POST['timezone'],3);
  $_SESSION[QT]['show_time_zone'] = $_POST['showtimezone'];
  $_SESSION[QT]['userlang'] = $_POST['userlang'];
  $_SESSION[QT]['language'] = (isset($arrLangDir[$_POST['dfltlang']]) ? $arrLangDir[$_POST['dfltlang']] : 'english');

  // change language
  include Translate('qnm_main.php');
  include Translate('qnm_adm.php');
  include Translate('qnm_zone.php');
  $oVIP->selfname = $L['Adm_region'];
  $oVIP->selfname = '<span class="upper">'.$L['Adm_settings'].'</span><br/>'.$L['Adm_region'];

  // save
  $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['time_zone'].'" WHERE param="time_zone"');
  $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_time_zone'].'" WHERE param="show_time_zone"');
  $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['userlang'].'" WHERE param="userlang"');
  $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.(isset($arrLangDir[$_POST['dfltlang']]) ? $arrLangDir[$_POST['dfltlang']] : 'english').'" WHERE param="language"');

  // formatdate
  $oGP = new cGetPost($_POST['formatdate'],64,''); // this is a formula, do not convert quotes (just add slashes)
  if ( empty($oGP->e) ) $error = Error(1).' '.$L['Date_format'];
  if ( empty($error) )
  {
  $_SESSION[QT]['formatdate'] = $oGP->e;
  $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.addslashes($_SESSION[QT]['formatdate']).'" WHERE param="formatdate"');
  }

  // formattime
  $oGP = new cGetPost($_POST['formattime'],64,''); // this is a formula, do not convert quotes (just add slashes)
  if ( empty($oGP->e) ) $error = Error(1).' '.$L['Time_format'];
  if ( empty($error) )
  {
  $_SESSION[QT]['formattime'] = $oGP->e;
  $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.addslashes($_SESSION[QT]['formattime']).'" WHERE param="formattime"');
  }

  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

include 'qnm_adm_inc_hd.php';

// Current language

$strCurrent = 'en';
  $arr = GetParam(false,'param="language"');
  $str = $arr['language'];
  $arr = array_flip($arrLangDir);
  if ( isset($arr[$str]) ) $strCurrent = $arr[$str];

// Check language subdirectories

$arrFiles = array();
foreach($arrLang as $strIso=>$arr)
{
  if ( is_dir('language/'.$arr[2]) ) $arrFiles[$strIso] = ucfirst($arr[1]);
}
asort($arrFiles);

// Prepare table template

$table = new cTable('','data_o');
$table->row = new cTableRow('','data_o');
$table->td[0] = new cTableData('','','headfirst'); $table->td[0]->Add('style','width:150px;');
$table->td[1] = new cTableData(); $table->td[1]->Add('style','width:225px;');
$table->td[2] = new cTableData('','','help');

// FORM

if ( PHP_VERSION_ID>=50200 ) echo '<p style="text-align:right">',L('Time'),' ',date('H:i'), ' &middot; <a href="qnm_adm_time.php">',L('Change_time'),'...</a></p>',PHP_EOL;

echo '<form method="post" action="',$oVIP->selfurl,'">
';
echo $table->Start().PHP_EOL;
echo '<tr class="data_o"><td class="headgroup" colspan="3">',L('Language'),'</td></tr>'.PHP_EOL;
$table->SetTDcontent( array(
    '<label for="dfltlang">'.L('Dflt_language').'</label>',
    '<select id="dfltlang" name="dfltlang" onchange="bEdited=true;">'.QTasTag($arrFiles,$strCurrent).'</select>',
    '&nbsp;'
    ));
    echo $table->GetTDrow().PHP_EOL;
$table->SetTDcontent( array(
    '<label for="userlang">'.L('User_language').'</label>',
    '<select id="userlang" name="userlang" onchange="bEdited=true;">'.QTasTag(array($L['N'],$L['Y']),(int)$_SESSION[QT]['userlang']).'</select>',
    L('H_User_language')
    ));
    echo $table->GetTDrow().PHP_EOL;

echo '<tr class="data_o"><td class="headgroup" colspan="3">',L('Date_time'),'</td></tr>'.PHP_EOL;
$table->SetTDcontent( array(
    '<label for="formatdate">'.L('Date_format').'</label>',
    '<input id="formatdate" name="formatdate" size="10" maxlength="24" value="'.QTencode($_SESSION[QT]['formatdate']).'" onchange="bEdited=true;"/>',
    L('H_Date_format')
    ));
    echo $table->GetTDrow().PHP_EOL;
$table->SetTDcontent( array(
    '<label for="formattime">'.L('Time_format').'</label>',
    '<input id="formattime" name="formattime" size="10" maxlength="24" value="'.QTencode($_SESSION[QT]['formattime']).'" onchange="bEdited=true;"/>',
    L('H_Time_format')
    ));
  echo $table->GetTDrow().PHP_EOL;

echo '<tr class="data_o"><td class="headgroup" colspan="3">',L('Clock'),'</td></tr>'.PHP_EOL;
$table->SetTDcontent( array(
    L('Application_time'),
    date('H:i'),
    '(gmt '.gmdate('H:i').')'
    ));
    echo $table->GetTDrow().PHP_EOL;
$table->SetTDcontent( array(
    '<label for="timezone">'.L('Clock_setting').'</label>',
    '<select id="timezone" name="timezone" onchange="bEdited=true;">'.QTasTag($L['tz'],'gmt'.$_SESSION[QT]['time_zone']).'</select>',
    L('&nbsp;')
    ));
    echo $table->GetTDrow().PHP_EOL;
$table->SetTDcontent( array(
    '<label for="showtimezone">'.L('Show_time_zone').'</label>',
    '<select id="showtimezone" name="showtimezone" onchange="bEdited=true;">'.QTasTag(array($L['N'],$L['Y']),(int)$_SESSION[QT]['show_time_zone']).'</select>',
    L('H_Show_time_zone')
    ));
    echo $table->GetTDrow().PHP_EOL;

echo '<tr class="data_o"><td class="headgroup" colspan="3" style="padding:6px; text-align:center"><input type="submit" name="ok" value="',$L['Save'],'"/></td></tr>
</table>
</form>
';

echo '<h2>',$L['Format_preview'],'</h2>
';
$table->Add('style','width:350px;');
unset($table->td[2]);
echo $table->Start().PHP_EOL;
$table->SetTDcontent( array(L('Date'), QTdatestr('now','$','')) );
echo $table->GetTDrow().PHP_EOL;
$table->SetTDcontent( array(L('Clock'), gmdate($_SESSION[QT]['formattime'],time()+(3600*$_SESSION[QT]['time_zone'])).($_SESSION[QT]['show_time_zone']!='1' ? '' : ' (gmt'.($_SESSION[QT]['time_zone']>0 ? '+' : '').($_SESSION[QT]['time_zone']==0 ? '' : $_SESSION[QT]['time_zone']).')')) );
echo $table->GetTDrow().PHP_EOL;
echo '</table>
';

// HTML END

include 'qnm_adm_inc_ft.php';