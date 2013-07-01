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
* @package    QuickTalk
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
* @version    3.0 build:20130410
*/

session_start();
require_once 'bin/qnm_init.php';
include Translate('qnm_adm.php');
if ( $oVIP->user->role!='A' ) die($L['E_admin']);
if ( empty($_SESSION[QT]['m_map_gkey']) ) die('Missing google map api key. First go to the Map administration page.');

include Translate('qnmm_map.php');
include Translate('qnmm_map_adm.php');
include 'qnmm_map_lib.php';

// INITIALISE

$oVIP->selfurl = 'qnmm_map_adm_options.php';
$oVIP->selfname = $L['map']['Mapping_settings'];
$oVIP->exiturl = 'qnmm_map_adm.php';
$oVIP->exitname = 'Map';
$strPageversion = $L['map']['Version'].' 3.0<br/>';

// Read png in directory

$intHandle = opendir('qnmm_map');
$arrFiles = array();
while ( false!==($strFile = readdir($intHandle)) )
{
  if ( $strFile!='.' && $strFile!='..' ) {
  if ( substr($strFile,-4,4)=='.png' ) {
  if ( !strstr($strFile,'shadow') ) {
    $arrFiles[substr($strFile,0,-4)]=ucfirst(substr(str_replace('_',' ',$strFile),0,-4));
  }}}
}
closedir($intHandle);
asort($arrFiles);

// --------
// SUBMITTED for cancel
// --------

if ( isset($_POST['cancel']) )
{
  $_SESSION[QT]['m_map_symbols'] = '0';
  $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['m_map_symbols'].'" WHERE param="m_map_symbols"');
  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// SUBMITTED for save
// --------

if ( isset($_POST['ok']) )
{
  $arrSymbols = array();
  foreach(array('U','M','A') as $key)
  {
  if ( isset($_POST['symbol_'.$key]) ) $arrSymbols[$key]=$_POST['symbol_'.$key];
  }
  $_SESSION[QT]['m_map_symbols'] = QTimplode($arrSymbols);
  $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['m_map_symbols'].'" WHERE param="m_map_symbols"');
  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

// read symbols values
if ( empty($_SESSION[QT]['m_map_symbols']) ) $_SESSION[QT]['m_map_symbols']='U=0;M=0;A=0'; // empty, not set or false
$arrSymbols = QTexplode($_SESSION[QT]['m_map_symbols']);

// reset if incoherents values
if ( count($arrSymbols)!=3 ) $arrSymbols = array('U'=>'0','M'=>'0','A'=>'0');
if ( !isset($arrSymbols['A']) || !isset($arrSymbols['M']) || !isset($arrSymbols['U']) ) $arrSymbols = array('U'=>'0','M'=>'0','A'=>'0');

$oHtml->links[]='<link rel="stylesheet" type="text/css" href="qnmm_map.css" />';
$oHtml->scripts[] = '<script type="text/javascript">
<!--
function radioHighlight(id)
{
  var doc = document;
  var radios = doc.getElementsByClassName("marker");
  for(var i=radios.length-1;i>=0;i--) radios[i].style.backgroundColor="transparent";
  var imgid = "image_" + id.substr(7);
  var role = id.substr(id.length-1,1);
  if ( doc.getElementById(imgid) )
  {
    doc.getElementById(imgid).style.backgroundColor="#ffffff";
    if ( doc.getElementById("markerpicked_"+role) )
    {
    doc.getElementById("markerpicked_"+role).src = doc.getElementById(imgid).src;
    }
  }
}
//-->
</script>
';
include 'qnm_adm_inc_hd.php';

echo '
<form method="post" action="',Href(),'" onsubmit="return ValidateForm(this,enterkeyPressed);">
<h2 class="subtitle">',$L['map']['Symbol_by_role'],'</h2>
<table class="data_o">
<tr><td class="blanko"></td><td class="blanko"></td><td class="blanko small"><p class="small" style="margin:0">',$L['map']['Click_to_change'],'</p></td></tr>
';

// Read png in directory

$intHandle = opendir('qnmm_map');
$arrFiles = array();
while(false!==($strFile=readdir($intHandle)))
{
  if ( $strFile!='.' && $strFile!='..' ) {
  if ( substr($strFile,-4,4)=='.png' ) {
  if ( !strstr($strFile,'shadow') ) {
    $arrFiles[substr($strFile,0,-4)]=ucfirst(substr(str_replace('_',' ',$strFile),0,-4));
  }}}
}
closedir($intHandle);
asort($arrFiles);

foreach($arrSymbols as $key=>$strSymbol)
{
  echo '<tr>
<td class="headfirst" style="padding-right:10px">',L('Userrole_'.$key.'s'),'</td>
<td id="symbol_cb_'.$key.'" style="width:60px;text-align:center"><img id="markerpicked_'.$key.'" title="default" src="',($strSymbol==='0' ? 'bin/css/gmap_marker.png' : 'qnmm_map/'.$strSymbol.'.png' ),'"/></td>
<td id="picker_cb_'.$key.'">
<div class="markerpicker">
<input type="radio" name="symbol_'.$key.'" value="0" id="symbol_0_'.$key.'"'.(empty($_SESSION[QT]['m_map_gsymbol']) ? QCHE : '').' onchange="radioHighlight(this.id);bEdited=true;"/><label for="symbol_0_'.$key.'"><img id="image_0_'.$key.'" class="marker'.($_SESSION[QT]['m_map_gsymbol']==$strFile ? ' checked' : '').'" title="default" src="bin/css/gmap_marker.png"/></label>
';
  foreach ($arrFiles as $strFile=>$strName)
  {
  echo '<input type="radio" name="symbol_'.$key.'" value="'.$strFile.'" id="symbol_'.$strFile.'_'.$key.'"'.($strSymbol===$strFile ? QCHE : '').' onchange="radioHighlight(this.id);bEdited=true;"/><label for="symbol_'.$strFile.'_'.$key.'"><img id="image_'.$strFile.'_'.$key.'" class="marker'.($strSymbol==$strFile ? ' checked' : '').'" title="'.$strName.'" src="qnmm_map/'.$strFile.'.png"/></label>'.PHP_EOL;
  }
  echo '</div>
</td>
</tr>
';
}
echo '</table>';

echo '
<p style="text-align:center"><input type="submit" name="cancel" value="',$L['Cancel'],'"/> &middot; <input type="submit" name="ok" value="',$L['Save'],'"/></p>
</form>
<p><a href="',$oVIP->exiturl,'" onclick="return qtEdited(bEdited,\'',$L['E_editing'],'\');">&laquo; ',$oVIP->exitname,'</a></p>
';

include 'qnm_adm_inc_ft.php';