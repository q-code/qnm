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
*
* About text coding in the database
* This script will convert the text before inserting into the dabase as follow:
*
* 1) stripslashes
* 2) htmlspecialchar($text,ENT_QUOTES) <>&"' are converted to html
*/

session_start();
require_once 'bin/qnm_init.php';
if ( $oVIP->user->role=='V' ) HtmlPage(11);
include 'bin/qnm_fn_sql.php';

// --------
// INITIALISE
// --------

$a = '';    // action (mandatory)
$nid = '';  // NE
$s = -1;    // section (used to exit)
$e = 'e';   // exiturl is the section (default), use 'e' to exit to NE page
$nids = ''; // confirmed nids (as a comma-separated string)

QThttpvar('a nid s nids e','str str int str str');

// check arguments
if ( $s<0 ) die('Missing parameters: section');
if ( !in_array($a,array('activate','inactivate','unlink','direction')) ) die('Missing parameters A');
if ( empty($nid) && empty($nids) ) die('Missing parameters: nid');

$oNE = new cNE($nid);

$oVIP->selfurl = 'qnm_f_nl_edits.php';
$oVIP->selfname = L('Edit');
$oVIP->exiturl = 'qnm_items.php?s='.$s; // if $e is 's', exiturl will be to the page of this nid
$oVIP->exitname = $oVIP->sections[$s];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check and format nids to process
  if ( empty($nids) ) die('Missing id to process');
  $arrNids = explode(',',$nids);

  // process
  switch ($a)
  {
    case 'activate':
    case 'inactivate':
      $oNE = new cNE(); // empty object
      foreach($arrNids as $str)
      {
      $oNE->IdDecode($str); // read [class] and [uid] from $str
      $oNE->UpdateField('status',($a=='activate' ? 1 : 0));
      }
      $_SESSION['pagedialog']='O|'.L('S_update');
      $oHtml->Redirect($oVIP->exiturl);
      break;
    case 'unlink':
      $oNE->Unlink($arrNids,'c');
      $_SESSION['pagedialog']='O|'.L('S_update');
      $oHtml->Redirect($oVIP->exiturl);
      break;
    case 'direction':
      if ( !isset($_POST['direction']) )$oHtml->PageBox(NULL,'Invalid direction. Unable to change direction.',$_SESSION[QT]['skin_dir'],2);
      $oNE->RelationDirection($arrNids,'c',intval($_POST['direction'])); // Adding relation will first remove existing
      $_SESSION['pagedialog']='O|'.L('S_update');
      $oHtml->Redirect($oVIP->exiturl);
      break;
  }

  // exit
  $oVIP->exiturl = 'qnm_items.php?s='.$s;
  $_SESSION['pagedialog']='W|Unknown command';
  $oHtml->Redirect($oVIP->exiturl);
}

// --------
// HTML START
// --------

include 'qnm_inc_hd.php';

$arrNids = array(); // read nids to confirm (from POST or from GET)

if ( isset($_POST['t1_cb']) )
{
  foreach($_POST['t1_cb'] as $str ) $arrNids[]=$str;
}
else
{
  $arrNids = explode(',',$nids);
}

if ( $e=='e' )
{
$oVIP->exiturl = "qnm_item.php?nid=".$arrUids[0]; // Exit to element page (if requested)
$oVIP->exitname = 'Network element';
}

// Elements (top 5)

$strElements = '';
for($i=0;$i<5;$i++) {
if ( isset($arrNids[$i]) ) {
  $oLINKED = new cNE($arrNids[$i]);
  $oNL = new cNL($nid,'c',$arrNids[$i]);
  $strElements .= cNL::NLGetIcon($oNL->ldirection,$_SESSION[QT]['skin_dir']).$oLINKED->Dump(false);
}}
if ( count($arrNids)>5 ) $strElements .= '...<br/>';

// Display

echo '<h2>',L('Edit_relation',count($arrNids),false),(count($arrNids)>1 ? ' ('.count($arrNids).')' : ''),'</h2>',PHP_EOL;
if ( !empty($error) ) echo '<p><span class="error">',$error,'</span></p>';

echo '<p>',$oNE->Dump().'</p>';

echo '
<form id="f1" method="post" action="',Href(),'" >
<input type="hidden" name="s" value="',$s,'"/>
<input type="hidden" name="nid" value="',$nid,'"/>
<input type="hidden" name="nids" value="',implode(',',$arrNids),'"/>

<table class="data_o">
<tr>
<td >Relation with</td>
<td>',$strElements,'</td>
</tr>
<tr>
<td >Action</td>
<td><select id="f1_a" name="a" onchange="updateform(this.form.id,this.value);">',QTasTag(array('activate'=>'Activate linked elements','inactivate'=>'Inactivate linked elements','unlink'=>'Remove relation','direction'=>'Change Direction'),$a),'</select></td>
</tr>
<tr id="f1_input_direction">
<td >Direction</td>
<td>
<select name="direction">
<option value="1">Direct &rarr;</option>
<option value="2">Bidirectional &harr;</option>
<option value="-1">Reverse &larr;</option>
<option value="0">Undefined</option>
</select>
</td>
</tr>
<tr id="f1_info_delete">
<td >Info</td>
<td><span class="warning">Warning: You are removing relations</span></td>
</tr>
<tr>
<td >&nbsp;</td>
<td><input type="submit" id="ok" name="ok" value="',$L['Ok'],'"/>',(count($arrNids)>1 ? ' ('.count($arrNids).')' : ''),'</td>
</tr>
</table>
</form>
';

echo '<p><a href="',$oVIP->exiturl,'">&laquo; ',$oVIP->exitname,'</a></p>
';

// --------
// HTML END
// --------

echo '
<script type="text/javascript">
<!--
updateform("f1","'.$a.'");

function updateform(formid,action)
{
  var arr = ["activate","inactivate","direction","delete"];
  for (var i=0; i<arr.length; i++)
  {
  if ( document.getElementById(formid+"_input_"+arr[i]) ) document.getElementById(formid+"_input_"+arr[i]).style.display="none";
  if ( document.getElementById(formid+"_info_"+arr[i]) ) document.getElementById(formid+"_info_"+arr[i]).style.display="none";
  }
  if ( document.getElementById(formid+"_input_"+action) ) document.getElementById(formid+"_input_"+action).style.display="table-row";
  if ( document.getElementById(formid+"_info_"+action) ) document.getElementById(formid+"_info_"+action).style.display="table-row";
  if ( document.getElementById(formid+"_warning") )
  {
  if ( action=="delete" )
  {
  document.getElementById(formid+"_warning").innerHTML="warning, your are deleting network elements";
  }
  else
  {
  document.getElementById(formid+"_warning").innerHTML="";
  }
  }
}
//-->
</script>
';

include 'qnm_inc_ft.php';