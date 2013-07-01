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

/*
* Note #1:
* To change the link-class or the link-status of an existing link,
* you can re-create the link (when inserting a link, existing same link is first deleted)
* Note #2:
* Different link-types can exist beween 2 elements.
* If a link-type must be changed, user have to delete/re-create the link
*/

session_start();
require_once 'bin/qnm_init.php';
if ( !$oVIP->user->CanView('V3') ) HtmlPage(11);

$nid = ''; // network element (mandatory)
$fs = ''; // section filter can be '*' or [int]. The default '' will become the current section [int].
$fu = '*'; // status filter
$ft = '*'; // type filter
$fi = ''; // key filter
$page = 1;

QThttpvar('nid fs fu ft fi page','str str str str str int');

if ( empty($nid) ) die('Missing element nid...');
$ft = urldecode($ft);

// --------
// INITIALISE
// --------

$oNE = new cNE($nid);
$s = $oNE->section;
$_SESSION[QT]['section'] = $s;

if ( $fs==='' ) $fs = $s;

if ( $fs==='*' )
{
  $oVOIDSEC = new cSection(); // void section in case of "all sections"
  $oVOIDSEC->ReadTypes(true); // return types through all sections
}
else
{
  $fs = (int)$fs;
  $oVOIDSEC = new cSection($fs); // Attention: This is not the section of the element, but the section used in the Search form!
  $oVOIDSEC->ReadTypes();
}

$strCommand = '';
if (isset($_GET['view'])) { $_SESSION[QT]['viewmode'] = $_GET['view']; }

$oVIP->selfurl = 'qnm_form_link_c.php';
$oVIP->exiturl = 'qnm_item.php';
$oVIP->selfname = L('Relations').' '.L('of').' '.$oNE->id;
$oVIP->exitname = L('Back');

$strOrder = 'id';
$strDirec = 'asc';
if ( isset($_GET['order']) ) if ( !empty($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) if ( !empty($_GET['dir']) ) $strDirec = strtolower($_GET['dir']);
$strOrderFilter = 'id'; // for the "add items" filtering table
$strDirecFilter = 'asc';
if ( isset($_GET['orderf']) ) if ( !empty($_GET['orderf']) ) $strOrderFilter = $_GET['orderf'];
if ( isset($_GET['dirf']) ) if ( !empty($_GET['dirf']) ) $strDirecFilter = strtolower($_GET['dirf']);

$u_col='posts'; // last column
if ( isset($_COOKIE[QT.'_u_col']) && !empty($_COOKIE[QT.'_u_col']) ) $u_col=$_COOKIE[QT.'_u_col'];

include 'qnm_inc_menu.php';

// ---------
// SUBMITTED add elements
// ---------

$strFEcla = 'c'; // link type connect
$intFEdir = 0; // link direction
$intFEsta = 0; // link status
if ( isset($_POST['fe_ok']) )
{
  if ( $oVIP->user->role=='V' ) HtmlPage(11);
  if ( isset($_POST['fe_cla']) ) $strFEcla = strip_tags($_POST['fe_cla']);
  if ( isset($_POST['fe_dir']) ) $intFEdir = intval($_POST['fe_dir']);
  if ( isset($_POST['fe_sta']) ) $intFEsta = intval($_POST['fe_sta']);

  // read nid(s)
  $arrNids=array();
  if ( isset($_POST['t2_cb']) ) { foreach($_POST['t2_cb'] as $str ) $arrNids[]=$str; }

  // add links as parent/child
  if ( count($arrNids)>0 ) $oNE->AddRelations($arrNids,$strFEcla,$intFEdir,$intFEsta); // add several links at once

  // message
  $_SESSION['pagedialog'] = array('o', L('Relation_added'), count($arrNids));
}

// ---------
// SUBMITTED edit elements
// ---------

if ( isset($_POST['nid']) && isset($_POST['a']) )
{
  if ( $oVIP->user->role=='V' ) HtmlPage(11);
  // read checkboxes nid
  $arrNids=array();
  if ( isset($_POST['t1_cb']) ) { foreach($_POST['t1_cb'] as $str ) $arrNids[]=$str; }

  if ( count($arrNids)>0 )
  {
    if ( $_POST['a']=='activate' || $_POST['a']=='inactivate' )
    {
      $oLINKED = new cNE(); // empty object
      foreach($arrNids as $str)
      {
      $oLINKED->IdDecode($str);
      $oLINKED->UpdateField('status',$_POST['a']=='activate' ? 1 : 0);
      }
      $_SESSION['pagedialog'] = array('o', L('S_update'), count($arrNids));
    }
    if ( $_POST['a']=='unlink' )
    {
      $oNE->Unlink($arrNids,'c');
      $oNE->links -= count($arrNids);
      $_SESSION['pagedialog'] = array('o', L('Relation_removed'), count($arrNids));
    }
    if ( substr($_POST['a'],0,9)=='direction' ) {
    if ( strlen($_POST['a'])>9 ) {
      $d = substr($_POST['a'],9);
      $oNE->ChangeDirection($arrNids,intval($d));
      $_SESSION['pagedialog'] = array('o', L('S_update'), count($arrNids));
    }}
  }
}

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qnm_table.js"></script>
<script type="text/javascript">
<!--
$(document).ready(function() {

  // CHECKBOX checked when clicking some columns

  $("#t1 td:not(.tdcheckbox,.tdid)").click(function() { qtCheckboxToggle(this.parentNode.id.substring(3)); });
  $("#t2 td:not(.tdcheckbox,.tdid)").click(function() { qtCheckboxToggle(this.parentNode.id.substring(3)); });

  // CHECKBOX ALL ROWS

  $("input[id=\'t1_cb\']").click(function() { qtCheckboxAll("t1_cb","t1_cb[]",true); });
  $("input[id=\'t2_cb\']").click(function() { qtCheckboxAll("t2_cb","t2_cb[]",true); });

  // SHIFT-CLICK CHECKBOX

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
  document.getElementById(\'f1_a\').value=action;
  document.getElementById(\'f1\').submit();
  return;
  }
  else
  {
  alert(qtHtmldecode("'.L('No_selected_row').'"));
  return false;
  }
}
//-->
</script>
';

include 'qnm_inc_hd.php';

// User preferences

if ( !empty($strUsermenu) ) echo $strUsermenu;

// Element DESCRIPTION AND MAP

$strDescr = '';
$strLocation = '';

// display element descripition and map
if ( !empty($strDescr) || !empty($strLocation) )
{
echo '
<table class="hidden">
<tr class="hidden">
<td class="hidden">',$strDescr,'</td>
<td class="hidden">',$strLocation,'</td>
</tr>
</table>
';
}

// --------
// ELEMENT
// --------

echo '<p>',$oNE->Dump(true,'class="bold"'),'<br/>',$oNE->DumpContent(false,'',20);'</p>';

// --------
// EXISTING LINKS (fl)
// --------

echo '
<h1>',$oVIP->selfname,'</h1>
';

$arrLinked = $oNE->GetNL('c','ORDER BY e.'.$strOrder.' '.$strDirec);  // list sortable;

// ::::::::
if ( count($arrLinked)>0 ) {
// ::::::::

echo '<p class="datasetcontrol"><img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:bottom;margin:0 10px 0 13px" alt="|" />
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'activate\'); return false;" href="#" title="'.L('cmd_Activate').'">'.L('Activate').'</a> &middot;
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'inactivate\'); return false;" href="#" title="'.L('cmd_Inactivate').'">'.L('Inactivate').'</a> &middot;
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'unlink\'); return false;" href="#" title="'.L('cmd_Remove_relations').'">'.L('Remove_relations').'</a> &middot;
<span class="datasetcontrol">'.L('Direction').'
<select class="small" name="direction" onchange="datasetcontrol_click(\'t1_cb[]\',\'direction\'+this.value); return false;">
<option value="" style="font-style:italic" selected="selected">'.L('Select...').'</option>
',QTasTag(cNL::GetDirections()),'
</select>
</span>
</p>';

echo '
<form id="f1" method="post" action="',Href(),'">
<input type="hidden" name="s" value="',$s,'"/>
<input type="hidden" id="f1_a" name="a" value=""/>
<input type="hidden" id="f1_nid" name="nid" value="',GetNid($oNE),'"/>
';

// === TABLE DEFINITION ===

$table = new cTable('t1','data_t',count($arrLinked));
$table->activecol = $strOrder;
$table->activelink = '<a  href="'.Href().'?'.GetURI('order,dir,page').'&amp;order='.$strOrder.'&amp;dir='.($strDirec=='asc' ? 'desc' : 'asc').'&amp;page=1">%s</a> <img class="i_sort" src="'.$_SESSION[QT]['skin_dir'].'/sort_'.$strDirec.'.gif" alt="+"/>';
// column headers
$table->th['checkbox']  = new cTableHead(($table->rowcount<2 ? '&nbsp;' : '<input type="checkbox" name="t1_cb_all" id="t1_cb" />'));
$table->th['ldirection']= new cTableHead(L('Direction'));
$table->th['id']        = new cTableHead('ID','','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=id&amp;dir=asc">%s</a>');
$table->th['(links)']   = new cTableHead(L('Links'));
$table->th['type']      = new cTableHead(L('Type'),'','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=type&amp;dir=asc">%s</a>');
$table->th['address']   = new cTableHead(L('Address'),'','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=address&amp;dir=asc">%s</a>');
$table->th['descr']     = new cTableHead(L('Description'));
$table->th['posts']     = new cTableHead(AsImg($_SESSION[QT]['skin_dir'].'/ico_notes.gif','N',L('In_process_notes'),'i_note'),'','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=note&amp;dir=desc">%s</a>');
switch($u_col)
{
  case 'none':   unset($table->th['posts']); break; // when user request 'none'
  case 'status': unset($table->th['posts']); $table->th['status']= new cTableHead(L('Status'),'','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=status&amp;dir=asc">%s</a>'); break;
  case 'tags':   unset($table->th['posts']); $table->th['tags'] = new cTableHead(L('Tags')); break;
  case 'docs':   unset($table->th['posts']); $table->th['docs'] = new cTableHead(AsImg($_SESSION[QT]['skin_dir'].'/ico_attachment.gif','D',L('Documents'),'','i_doc')); break;
  case 'insertdate': unset($table->th['posts']); $table->th['insertdate'] = new cTableHead(L('Created'),'','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=insertdate&amp;dir=desc">%s</a>'); break;
}
// create column data (from headers identifiers) and add class to all
foreach($table->th as $key=>$th)
{
  $table->th[$key]->Add('class','th'.$key);
  $table->td[$key] = new cTableData('','','td'.$key);
}

// === TABLE START DISPLAY ===

echo '
<!-- List of relations -->
';
echo $table->Start().PHP_EOL;
echo '<thead>'.PHP_EOL;
echo $table->GetTHrow().PHP_EOL;
echo '</thead>'.PHP_EOL;
echo '<tbody>'.PHP_EOL;

$intWhile=0;
$strAlt='r1';
foreach($arrLinked as $oSubNL)
{
  $oSubNE = $oSubNL->ne2;
  if ( !empty($oSubNE->descr) ) $oSubNE->descr = QTcompact($oSubNE->descr,50);

  // prepare row
  $table->row = new cTableRow( 'tr_t1_cb'.$oSubNE->uid, 'data_t '.$strAlt.' rowlight' );

  // prepare values, and insert value into the cells
  $table->SetTDcontent( FormatTableRow('t1',$table->GetTHnames(),array_merge(array('lclass'=>$oSubNL->lclass,'ldirection'=>$oSubNL->ldirection,'lstatus'=>$oSubNL->lstatus),get_object_vars($oSubNE))), false ); // adding extra columns not allowed

  // display row
  echo $table->GetTDrow().PHP_EOL;
  if ( $strAlt=='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }

  $intWhile++;
  //odbcbreak
  if ( $intWhile>=$_SESSION[QT]['items_per_page'] ) break;
}

// === TABLE END DISPLAY ===

echo '
</tbody>
</table>
</form>
';

// ::::::::
} else {
// ::::::::

  $table = new cTable('t2','data_t subitems');
  $table->th[] = new cTableHead('&nbsp;');
  echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.L('No_relation').'...</p>',true,'','r1');

// ::::::::
}
// ::::::::

// --------
// form filter (ff) not for connectors
// --------

if ( $oNE->class!='c' )
{

// element class 'e' not included int the filter liste (0,nid,existing link)
$arrNot = array($oNE->uid);
foreach($arrLinked as $oSubNL) $arrNot[]=$oSubNL->ne2->uid;

$strWhere = 'uid>0 AND uid NOT IN ('.implode(',',$arrNot).')';
if ( $fs!=='*' ) $strWhere .= ' AND section='.$fs;
if ( $fu!=='*' ) $strWhere .= ' AND status='.$fu;
if ( $ft!=='*' ) $strWhere .= ' AND type="'.$ft.'"';
if ( $fi!=='' ) $strWhere .= ' AND id LIKE "%'.$fi.'%"';
$strFullOrder = $strOrderFilter.' '.$strDirecFilter;
if ( $strOrderFilter!='id' ) $strFullOrder = $strOrderFilter.' '.$strDirecFilter.',id ASC';
$oDB->Query( 'SELECT * FROM '.TABNE.' WHERE '.$strWhere.' ORDER BY '.$strFullOrder );
$intWhile=0;
$arrFilter = array();
while($row=$oDB->GetRow())
{
  $arrFilter[$row['uid']] = $row;
  $intWhile++;
  if ( $intWhile>100 ) break;
}

// filter (use favorite if possible)
$arrFilters = array();
$arrOthers = $oVOIDSEC->types_e; // keys are urlencoded
$strTypes = '';
if ( $fs!=='*' && $fs>=0 ) {
if ( $oVOIDSEC->items>25 ) {
if ( count($oVOIDSEC->types_e)>10 ) {
  $arrFilters = $oVOIDSEC->GetFilter(); // keys are urlencoded
  if ( count($arrFilters)>0 ) $strTypes .= '<optgroup label="'.L('Favorites').'">'.QTasTag($arrFilters,urlencode($ft)).'</optgroup>'.PHP_EOL;
}}}
if ( count($arrFilters)>0 )
{
  $arrOthers = array_diff($arrOthers,$arrFilters);
  $strTypes .= '<optgroup label="'.L('Others').'">'.QTasTag($arrOthers,urlencode($ft)).'</optgroup>'.PHP_EOL;
}
else
{
  $strTypes .= QTasTag($arrOthers,urlencode($ft)).PHP_EOL;
}

echo '
<br/>
<h1>'.L('Create_relations').'</h1>
<div class="itemselect">
<div class="itemfilter">
<form id="ff" method="post" action="',Href(),'?nid=',$nid,'#ff">
<input type="hidden" name="nid" value="',$nid,'"/>
<p class="itemfilter">',L('f_Search_other_section'),' <select name="fs" onchange="this.form.submit();">',Sectionlist($fs,array(),array(),L('All_sections')),'</select></p>
<p class="itemfilter">
',L('f_Show_only_type'),' <select name="ft"><option value="*">',L('all_types'),'</option>',$strTypes,'</select>
',L('status'),' <select name="fu"><option value="*">',L('all_statuses'),'</option>',QTasTag(array(L('Inactive'),L('Active')),($fu!=='*' ? $fu : null)),'</select>
Id <input type="text" name="fi" value="',$fi,'" size="8" title="',L('f_Enter_id'),'"/>
<input type="submit" id="ff_ok" name="ff_ok" value="',L('Ok'),'"/></p>
</form>
</div>
';

// ::::::::
if ( count($arrFilter)>0 ) {
// ::::::::

echo '
<form id="fe" method="post" action="',Href(),'?nid=',GetNid($oNE),'">
<p class="datasetcontrol"><img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:middle;margin:0 10px" alt="|" />',L('f_Add_direction'),' <select name="fe_dir" class="small">
',QTasTag(cNL::GetDirections()),'
</select> <input type="hidden" id="fe_nid" name="nid" value="',GetNid($oNE),'"/>
<input class="small" type="submit" id="fe_ok" name="fe_ok" value="',L('Ok'),'" onclick="return datasetcontrol_click(\'t2_cb[]\');"/>
</p>
';

// === TABLE DEFINITION ===

$table = new cTable('t2','data_t subfilter',count($arrFilter));
$table->activecol = $strOrderFilter;
$table->activelink = '<a  href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf='.$strOrderFilter.'&amp;dirf='.($strDirecFilter=='asc' ? 'desc' : 'asc').'#ff">%s</a> <img class="i_sort" src="'.$_SESSION[QT]['skin_dir'].'/sort_'.$strDirecFilter.'.gif" alt="+"/>';
// column headers
$table->th['checkbox'] = new cTableHead(($table->rowcount<2 ? '&nbsp;' : '<input type="checkbox" name="t2_cb_all" id="t2_cb" />'));
$table->th['icon']     = new cTableHead('&nbsp;');
$table->th['id']       = new cTableHead('ID','','','<a href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf=id&amp;dirf=asc#ff">%s</a>');
$table->th['(links)']  = new cTableHead(L('Links'));
$table->th['type']     = new cTableHead(L('Type'),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf=type&amp;dirf=asc#ff">%s</a>');
$table->th['address']  = new cTableHead(L('Address'),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf=address&amp;dirf=asc#ff">%s</a>');
if ( $fs==='*' ) {
  $table->th['section']= new cTableHead(L('Section'),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf=section&amp;dirf=asc#ff">%s</a>');
} else {
  $table->th['descr']  = new cTableHead(L('Description'));
}
$table->th['posts']    = new cTableHead(AsImg($_SESSION[QT]['skin_dir'].'/ico_notes.gif','N',L('In_process_notes'),'i_note'),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf=posts&amp;dirf=desc#ff">%s</a>');
switch($u_col)
{
  case 'none':   unset($table->th['posts']); break; // when user request 'none'
  case 'status': unset($table->th['posts']); $table->th['status']= new cTableHead(L('Status'),'','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=status&amp;dir=asc">%s</a>'); break;
  case 'tags':   unset($table->th['posts']); $table->th['tags'] = new cTableHead(L('Tags')); break;
  case 'docs':   unset($table->th['posts']); $table->th['docs'] = new cTableHead(AsImg($_SESSION[QT]['skin_dir'].'/ico_attachment.gif','D',L('Documents'),'','i_doc')); break;
  case 'insertdate': unset($table->th['posts']); $table->th['insertdate'] = new cTableHead(L('Created'),'','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=insertdate&amp;dir=desc">%s</a>'); break;
}
// create column data (from headers identifiers) and add class to all
foreach($table->th as $key=>$th)
{
  $table->th[$key]->Add('class','th'.$key);
  $table->td[$key] = new cTableData('','','td'.$key);
}
// === TABLE START DISPLAY ===

echo '
<!-- List of items -->
';
echo $table->Start().PHP_EOL;
echo '<thead>'.PHP_EOL;
echo $table->GetTHrow().PHP_EOL;
echo '</thead>'.PHP_EOL;
echo '<tbody>'.PHP_EOL;

$strAlt='r1';
foreach($arrFilter as $key=>$arr)
{
  if ( !empty($arr['descr']) ) $arr['descr'] = QTcompact($arr['descr'],50);

  // prepare row
  $table->row = new cTableRow( 'tr_t2_cb'.$arr['uid'], 'data_t '.$strAlt.' rowlight' );

  // prepare values, and insert value into the cells
  $table->SetTDcontent( FormatTableRow('t2',$table->GetTHnames(),$arr), false ); // adding extra columns not allowed

  // display row
  echo $table->GetTDrow('','',true).PHP_EOL;
  if ( $strAlt=='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
}

// === TABLE END DISPLAY ===

echo '</tbody>
</table>
</form>
';

// ::::::::
} else {
// ::::::::

  $table = new cTable('t2','data_t',count($arrFilter));
  $table->th[] = new cTableHead('&nbsp;');
  echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.L('No_item').'...</p>',true,'','r1');

// ::::::::
}
// ::::::::

}

echo '</div>
<p><a href="',$oVIP->exiturl,'?nid=',$nid,'">&laquo; ',$oVIP->exitname,'</a></p>
';

// --------
// HTML END
// --------

include 'qnm_inc_ft.php';