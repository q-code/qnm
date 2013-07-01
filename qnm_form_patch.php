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
* 3) bbcodes remain UNCHANGED (they are converted while displayed)
*/

/*
Attention: In this page uses uid (rather than nid) as array keys
*/

session_start();
require_once 'bin/qnm_init.php';
if ( $oVIP->user->role=='V' ) HtmlPage(11);
if ( !$oVIP->user->CanView('V6') ) HtmlPage(11);
include Translate('qnm_patch.php');
include 'bin/qnm_fn_sql.php';

// --------
// INITIALISE
// --------

$lid = '';  // source [class].[uid]
$lid2 = '';  // destination [class].[uid]

QThttpvar('lid lid2','str str');

if ( $lid==='' || $lid2==='' ) die('Missing parameters: lid, lid2');
if ( GetUid($lid)==0 || GetUid($lid2)==0  ) HtmlPage(20);

$oVIP->selfurl = "qnm_form_patch.php?lid=$lid&amp;lid2=$lid2";
$oVIP->selfname = L('Edit');
$oVIP->exiturl = 'qnm_item.php?nid='.$lid;
$oVIP->exitname = 'Element';

// --------
// SUBMITTED for unlink. Attention: t1_cb[] containes int
// --------

if ( isset($_POST['a']) ) {
if ( $_POST['a']=='unlink' ) {

  if ( $oVIP->user->role=='V' ) HtmlPage(11);
  // unlink from checkboxes uid
  $arrId=array();
  if ( !empty($_POST['t1_cb']) )
  {
    $oNC = new cNE();
    foreach($_POST['t1_cb'] as $strUid)
    {
      $oNC->class='c';
      $oNC->uid=(int)$strUid;
      $arrLinked = $oNC->GetLinksId('c'); // return a list of [class][uid]
      if ( !empty($arrLinked) )
      {
        $intLinked = GetUid(current($arrLinked));
        if ( !empty($intLinked) ) $oNC->ConnectorUnlink($intLinked);
      }
    }
  }

}}

// --------
// SUBMITTED PATCH
// --------

if ( isset($_POST['ok']) )
{
  if ( $oVIP->user->role=='V' ) HtmlPage(11);
  $arr = array();
  foreach($_POST as $strKey=>$strValue)
  {
    if ( !empty($strValue) ) {
    if ( substr($strKey,0,4)=='NE1C' ) {
    if ( substr($strValue,0,4)=='NE2C' ) {
      $arr[substr($strKey,4)]=substr($strValue,4);
    }}}
  }
  $intDir = 0;
  if ( isset($_POST['dir']) ) $intDir = intval($_POST['dir']);
  // Process each connector
  foreach($arr as $c1=>$c2)
  {
    // Initialise $c1 as a new NE (with class [c]onnector)
    $voidNE = new cNE();
    $voidNE->class='c';
    $voidNE->uid=(int)$c1;
    // Create link to $c2 (previous is deleted)
    $voidNE->ConnectorLink($c2,$intDir); // connector stat is updated
  }
}

// --------
// HTML START
// --------

$oNE = new cNE($lid);
$s=$oNE->section;
$oNE2 = new cNE($lid2);
$oNL = new cNL('c',0,1,$oNE,$oNE2);

$oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qnm_form.css" media="all"/>';
$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qnm_table.js"></script>
<script type="text/javascript" language="javascript">
<!--
function qtConReset(name_src,name_trg)
{
  var arrRB = document.getElementsByName(name_src);
  var i;
  for (i=0;i<arrRB.length;i++)
  {
    arrRB[i].style.display="inline";
    document.getElementById(arrRB[i].value).value="";
    document.getElementById(arrRB[i].value+"_t").innerHTML="'.L('free').'";
  }
  var arrRB = document.getElementsByName(name_trg);
  for (i=0;i<arrRB.length;i++)
  {
    arrRB[i].style.display="inline";
    document.getElementById(arrRB[i].value).value="";
    document.getElementById(arrRB[i].value+"_t").innerHTML="'.L('free').'";
  }
}

function qtConClearRadio(name)
{
  var arrRB = document.getElementsByName(name);
  for (var i=0;i<arrRB.length;i++) { arrRB[i].checked=false; }
}

function qtConLink(name_src,objectRadio)
{
  var name_trg = objectRadio.name;
  var value_trg = objectRadio.value;
  var label_trg = objectRadio.title;

  // find radio (src) checked value
  var dir = "0";
  var value_src = "";
  var label_src = ""
  var arrRB = document.getElementsByName(name_src);
  for (var i=0;i<arrRB.length;i++)
  {
    if (arrRB[i].checked) { value_src = arrRB[i].value; label_src = arrRB[i].title; break; }
  }
  if ( value_src=="" ) return;

  // update connection value
  document.getElementById(value_src).value=value_trg;
  document.getElementById(value_trg).value=value_src;
  document.getElementById(value_src+"_t").innerHTML=label_trg;
  document.getElementById(value_trg+"_t").innerHTML=label_src;

  // remove radio button and uncheck radio buttons
  document.getElementById(value_src+"_r").style.display="none"; qtConClearRadio(name_src);
  document.getElementById(value_trg+"_r").style.display="none"; qtConClearRadio(name_trg);

  // activate first next remaining radio
  for (var ii=i;ii<arrRB.length;ii++)
  {
    if (arrRB[ii].style.display!="none") { arrRB[ii].checked=true; break; }
  }
}

function qtAutoLink(name_src,name_trg)
{
  // find next radio src (not yet linked)
  var arrRB = document.getElementsByName(name_src); //f1_r_src
  var src_id = "";
  for (var i=0;i<arrRB.length;i++)
  {
    src_id = arrRB[i].id.substring(0,arrRB[i].id.length-2);

    if ( document.getElementById(src_id) ) {
    if ( document.getElementById(src_id).value=="" )  {
    if ( arrRB[i].disabled!=true ) {
    if ( arrRB[i].checked ) {

      // find next radio trg (not yet linked)
      var arrRBtrg = document.getElementsByName(name_trg); //f1_r_trg
      var trg_id = "";
      for (var ii=0;ii<arrRBtrg.length;ii++)
      {
        trg_id = arrRBtrg[ii].id.substring(0,arrRBtrg[ii].id.length-2);
        if ( document.getElementById(trg_id) ) {
        if ( document.getElementById(trg_id).value=="" ) {

          arrRBtrg[ii].checked=true; // check radio
          qtConLink(name_src,arrRBtrg[ii]); // patch
          break;
        }}
      }

    }}}}
  }
}

$(document).ready(function() {

  // CHECKBOX checked when clicking some columns

  $("#t1 td:not(.linkcheckbox)").click(function() { qtCheckboxToggle(this.parentNode.id.substring(3)); });

  var lastChecked1 = null;
  var lastChecked2 = null;
  $("input[name=\'t1_cb[]\']").click(function(event) {
    if(!lastChecked1)
    {
      lastChecked1 = this;
      qtHighlight("tr_"+this.id,this.checked);
      return;
    }
    if(event.shiftKey)
    {
      var start = $("input[name=\'t1_cb[]\']").index(this);
      var end = $("input[name=\'t1_cb[]\']").index(lastChecked1);
      for(var i=Math.min(start,end);i<=Math.max(start,end);i++)
      {
      $("input[name=\'t1_cb[]\']")[i].checked = lastChecked1.checked;
      qtHighlight("tr_"+$("input[name=\'t1_cb[]\']")[i].id,lastChecked1.checked);
      }
    }
    lastChecked1 = this;
    qtHighlight("tr_"+this.id,this.checked);
  });

  $("input[name=\'t2_cb[]\']").click(function(event) {
    if(!lastChecked2)
    {
      lastChecked2 = this;
      qtHighlight("tr_"+this.id,this.checked);
      return;
    }
    if(event.shiftKey)
    {
      var start = $("input[name=\'t2_cb[]\']").index(this);
      var end = $("input[name=\'t2_cb[]\']").index(lastChecked2);
      for(var i=Math.min(start,end);i<=Math.max(start,end);i++)
      {
      $("input[name=\'t2_cb[]\']")[i].checked = lastChecked2.checked;
      qtHighlight("tr_"+$("input[name=\'t2_cb[]\']")[i].id,lastChecked2.checked);
      }
    }
    lastChecked2 = this;
    qtHighlight("tr_"+this.id,this.checked);
  });

});

function datasetcontrol_click(checkboxname,action)
{
  var checkboxes = document.getElementsByName(checkboxname);
  var n = 0;
  for (var i=0; i<checkboxes.length; i++) if ( checkboxes[i].checked ) n++;
  if ( n>0 )
  {
  document.getElementById(\'form_t1_a\').value=action;
  document.getElementById(\'form_t1\').submit();
  return true;
  }
  alert(qtHtmldecode("'.L('No_selected_row').'"));
  return false;
}
function doselect(checkboxname,action)
{
  var checkboxes = document.getElementsByName(checkboxname);
  for (i=0; i<checkboxes.length; i++)
  {
    switch(action)
    {
    case "none":  checkboxes[i].checked=false; break;
    case "inner": checkboxes[i].checked=(checkboxes[i].id.substring(checkboxes[i].id.length-2)=="in"); break;
    case "outer": checkboxes[i].checked=(checkboxes[i].id.substring(checkboxes[i].id.length-2)=="ou"); break;
    default:      checkboxes[i].checked=true; break;
    }
    qtHighlight("tr_"+checkboxes[i].id,checkboxes[i].checked);
  }
}
//-->
</script>
';

include 'qnm_inc_hd.php';

echo '<div class="frameelement">
<div id="elementdef" class="elementheader"><h1 class="elementheader">',L('Patching_between'),' ',$oNE->Idstatus(false,'class="elementid" title="element id"'),' ',L('and'),' ',$oNE2->Idstatus(false,'class="elementid" title="element id"'),'</h1></div>
';

// get patching connectors

$arrSub1 = $oNE->GetEmbeded('c'); // embeded connectors (key is nid)
$arrSub2 = $oNE2->GetEmbeded('c'); // embeded connectors (key is nid)
$arrTempParents = array($oNE->uid=>$oNE,$oNE2->uid=>$oNE2); // possible parents
$arrSub1CC = $oNE->GetCC($arrTempParents);  // connectors+parent linked to connectors of $oNE (key is Sub1's uid, cNL::ne1 is the linked connector, cNL::ne2 is the parent of the linked connector)
$arrSub2CC = $oNE2->GetCC($arrTempParents); // connectors+parent linked to connectors of $oNE2 (key is Sub2's uid, cNL::ne1 is the linked connector, cNL::ne2 is the parent of the linked connector)

// check patching faisability
if ( count($arrSub1)==0 || count($arrSub2)==0 )
{
  echo '<p>Patching is not possible between element without connector.</p></div>';
  echo '<p><a href="',$oVIP->exiturl,'">&laquo; ',$oVIP->exitname,'</a></p>';
  include 'qnm_inc_ft.php';
  exit;
}

echo '<form id="form_t1" method="post" action="',Href(),'">
<table class="patch">
<tr>
<td class="patchcheckbox">&nbsp;</td>
<td class="patchitem">',$oNE->Dump(true,'',false),'</td>
<td class="patchlink">',cNL::NLGetIcon($oNL->ldirection),'</td>
<td class="patchitem">',$oNE2->Dump(true,'',false),'<br/>',$oNE2->DumpContent(false,'',10),'</td>
</tr>
</table>
';

// show commands only if links exists
if ( count($arrSub1CC)>0 )
{
echo '
<p class="datasetcontrol">
<img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:middle;margin:0 5px 0 8px" alt="|" />
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'unlink\'); return false;" href="#" title="'.L('H_Unlink').'">'.L('Unlink').'</a>
&nbsp; | &nbsp;'.L('Select').' &nbsp<a class="datasetcontrol" onclick="doselect(\'t1_cb[]\',\'all\'); return false;" href="#">'.L('all').'</a>
&nbsp;<a class="datasetcontrol" onclick="doselect(\'t1_cb[]\',\'none\'); return false;" href="#">'.L('none').'</a>
&nbsp;<a class="datasetcontrol" onclick="doselect(\'t1_cb[]\',\'inner\'); return false;" href="#">'.L('connected_to').' '.$oNE2->id.'</a>
&nbsp;<a class="datasetcontrol" onclick="doselect(\'t1_cb[]\',\'outer\'); return false;" href="#">'.L('connected_to_others').'</a>
<input type="hidden" name="a" value="0" id="form_t1_a"/><input type="hidden" name="lid" value="'.$lid.'"/>
<input type="hidden" name="lid2" value="'.$lid2.'"/>
</p>
';
}

// show connectors
echo '
<div style="max-height:250px;overflow:auto;">
<table class="links" id="t1">
';

foreach($arrSub1 as $key=>$oSubNE)
{
  $key = GetUid($key);
  $strLink='';
  $bThird=false; //in=inner or ou=outer (added to the id to support doselect javascript)
  if ( isset($arrSub1CC[$key]) )
  {
    $oCon = $arrSub1CC[$key]->ne1;
    $oPar = $arrSub1CC[$key]->ne2;
    $strLink = cNE::GetIcon($oCon,true).' '.$oCon->Idstatus().' '.(empty($oCon->type) ? 'unknown' :$oCon->type).sprintf(' <span class="small">[%s]</span>',(empty($oCon->address) ? '-' : $oCon->address)).' '.L('in').' '.cNE::GetIcon($oPar,true).' '.$oPar->Idstatus();
    if ( GetNid($oPar)!=$lid2 ) $bThird=true; // link to a third party connector
  }
  echo '<tr class="links" id="tr_t1_cb'.$oSubNE->uid.($bThird ? 'ou' : 'in').'">',PHP_EOL;
  echo '<td class="linkcheckbox">',(empty($strLink) ? '' : '<input type="checkbox" name="t1_cb[]" id="t1_cb'.$oSubNE->uid.($bThird ? 'ou' : 'in').'"  value="'.$oSubNE->uid.'"/>'),'</td>',PHP_EOL;
  echo '<td class="linkitem">'.$oSubNE->Dump(true,'',false).'</td>',PHP_EOL;
  echo '<td class="linklink',(empty($strLink) ? '' : ($bThird ? ' outercolor' : ' innercolor')),'" style="text-align:center">'.(empty($strLink) ? '' : cNL::NLGetIcon($arrSub1CC[$key]->ldirection)).'</td>',PHP_EOL;
  echo '<td class="linkitem">'.(empty($strLink) ? '<span class="disabled">('.L('free').')</span>' : $strLink).'</td>',PHP_EOL;
  echo '</tr">',PHP_EOL;
}
echo '</table>
</div>
</form>
</div>
';
echo '<p style="margin:0 0 10px 0;text-align:right"><a href="qnm_form_patch.php?lid=',$lid2,'&amp;lid2=',$lid,'">'.L('Edit_reverse').'</a></p>
';

// Patching form

echo '<a name="patching"></a>
<table class="hidden">
<tr>
<td style="width:35%;vertical-align:top">
  <div class="patchinghelp">
  <div id="elementdef" class="elementheader"><h1 class="patchinghelp">'.L('Help').'</h1></div>
  <p class="small">',sprintf(L('Help_patch'),$oNE->id,$oNE2->id,$oNE->id),'.<br/></p>
  <p class="bold" style="margin:0 0 5px 0">'.L('Legend').'</p>
  <p class="small">
  <span class="innercolor">&nbsp;&nbsp;&nbsp;&nbsp;</span> '.L('Help_patch_l1').'<br/><br/>
  <span class="outercolor">&nbsp;&nbsp;&nbsp;&nbsp;</span> '.L('Help_patch_l2').'
  </p>
  </div>
</td>
<td style="vertical-align:top">
';

echo '<div class="patching">
<div id="elementdef" class="elementheader"><h1 class="patching">'.L('Edit_patching').'</h1></div>
<form id="f1" method="post" action="',Href(),'#patching">
<table class="patchitem">
<tr>
<td class="patchitem1">
  <p class="patchitem1">',$oNE->id,'&nbsp;</p>
  <div style="max-height:425px;overflow:auto;">
  <table class="hidden">
';
foreach($arrSub1 as $key=>$oSubNE)
{
  $key = GetUid($key);
  $strLink='';
  $bThird=false;
  if ( isset($arrSub1CC[$key]) )
  {
    $oPar = $arrSub1CC[$key]->ne2;
    $strLink='('.$oSubNE->id.' '.L('in').' '.$oPar->id.')';
    if ( GetNid($oPar)!=$lid2 ) $bThird=true; // link to a third party connector
  }
  echo '<tr class="hidden" style="height:19px">',PHP_EOL;
  echo '<td class="hidden" style="width:120px;vertical-align:middle"><input type="hidden" id="NE1C',$oSubNE->uid,'" name="NE1C',$oSubNE->uid,'" value=""/><span id="NE1C',$oSubNE->uid,'_t" class="small">'.(empty($strLink) ? L('free') : $strLink).'</span></td>';
  echo '<td class="hidden" style="width:50px;text-align:right;vertical-align:middle">',$oSubNE->id,'</td>';
  echo '<td class="hidden',(empty($strLink) ? '' : ($bThird ? ' outercolor' : ' innercolor')),'" style="width:20px">';
  echo '<input',($bThird ? ' disabled="disabled" style="display:none"' : ''),' title="(',$oSubNE->id,' in ',$oNE->id,')" id="NE1C',$oSubNE->uid,'_r" value="NE1C',$oSubNE->uid,'" type="radio" name="f1_r_src" onclick="qtConClearRadio(\'f1_r_trg\');"/>';
  echo '</td>';
  echo '</tr>',PHP_EOL;
}
echo '</table>
  </div>
</td>
<td class="patchitem2">
  <p class="patchitem2">&nbsp;',$oNE2->id,'</p>
  <div style="max-height:425px;overflow:auto;">
  <table class="hidden">
';
foreach($arrSub2 as $key=>$oSubNE)
{
  $key = GetUid($key);
  $strLink='';
  $strColor='#FEFFAF'; // yellow
  $bThird=false;
  if ( isset($arrSub2CC[$key]) )
  {
    $oPar = $arrSub2CC[$key]->ne2;
    $strLink='('.$oSubNE->id.' '.L('in').' '.$oPar->id.')';
    if ( GetNid($oPar)!=$lid ) $bThird=true; // link to a third party connector
  }
  echo '<tr class="hidden" style="height:19px">',PHP_EOL;
  echo '<td class="hidden',(empty($strLink) ? '' : ($bThird ? ' outercolor' : ' innercolor')),'" style="width:20px">';
  if ( !$bThird )
  {
  echo '<input type="hidden" id="NE2C',$oSubNE->uid,'" value=""/>';
  echo '<input title="(',$oSubNE->id,' in ',$oNE2->id,')" id="NE2C',$oSubNE->uid,'_r" value="NE2C',$oSubNE->uid,'" type="radio" name="f1_r_trg" onclick="qtConLink(\'f1_r_src\',this);"/>';
  }
  echo '</td>';
  echo '<td class="hidden" style="width:50px;vertical-align:middle">',$oSubNE->id,'</td>';
  echo '<td class="hidden" style="width:120px;text-align:right;vertical-align:middle"><span id="NE2C',$oSubNE->uid,'_t" class="small">'.(empty($strLink) ? L('free') : $strLink).'</span></td>';
  echo '</tr>',PHP_EOL;
}
echo '
  </table>
  </div>
</td>
</tr>
</table>
<p class="right" style="margin:0">
<input type="button" onclick="this.form.submit();" value="'.L('Reset').'"/>&nbsp;
<input type="button" onclick="qtAutoLink(\'f1_r_src\',\'f1_r_trg\');" value="'.L('Auto_link').'"/>&nbsp;
Direction <select id="dir" name="dir">',QTasTag(cNL::GetDirections()),'</select>
<input type="hidden" name="lid" value="',GetNid($oNE),'"/>
<input type="hidden" name="lid2" value="',GetNid($oNE2),'"/>
<input type="submit" name="ok" value="'.L('Save').'"/></p>
</form>
</div>
</td>
</tr>
</table>
';

// --------
// HTML END
// --------

echo '<p><a href="',$oVIP->exiturl,'">&laquo; ',$oVIP->exitname,'</a></p>
';

include 'qnm_inc_ft.php';