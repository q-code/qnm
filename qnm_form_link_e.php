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
* Different link-types can exist beween 2 network elements.
* If a link-type must be changed, user have to delete/re-create the link
*/

session_start();
require_once 'bin/qnm_init.php';
if ( !$oVIP->user->CanView('V3') ) HtmlPage(11);

$c = 'e';  // network element (mandatory)
$nid = ''; // network element (mandatory)
$fs = ''; // section filter can be '*' or [int]. The default '' will become the current section [int].
$fu = '*';  // status filter
$ft = '*'; // type filter
$fi = '';  // key filter
$a = 'e';  // e=embeded item, c=connection (default is e in this page)
$page = 1;

QThttpvar('c nid fs fu ft fi page','str str str str str str int');

if ( empty($c) || $nid==='' ) die('Missing element nid...');
if ( GetUid($nid)==0 ) HtmlPage(20);
$ft = urldecode($ft);

$oNE = new cNE($nid);

// ---------
// SUBMITTED add items
// ---------

$strFEcla = 'e'; // link type embed
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

  $_SESSION['pagedialog'] = array('o', L('Item_added'), count($arrNids));

}

// ---------
// SUBMITTED edit items
// ---------

if ( isset($_POST['nid']) && isset($_POST['a']) )
{
  if ( $oVIP->user->role=='V' ) HtmlPage(11);
  // read checkboxes uid
  $arrNids=array();
  if ( isset($_POST['t1_cb']) ) { foreach($_POST['t1_cb'] as $str ) $arrNids[]=$str; }

  if ( count($arrNids)>0 )
  {
    if ( $_POST['a']=='activate' || $_POST['a']=='inactivate' )
    {
      $oNE->ChangeStatusSubElements(($_POST['a']=='activate' ? 1 : 0),$arrNids);
      $_SESSION['pagedialog'] = array('o', L('S_update'), count($arrNids));
    }
    if ( $_POST['a']=='unlink' )
    {
      cNL::DeleteRelationsE($nid,$arrNids); // Unlink [e]mbeded selected items (the pid of these NE is reset to 0). Connectors are also deleted
      $oNE->UpdateItems();
      $_SESSION['pagedialog'] = array('o', L('Item_removed'), count($arrNids));
    }
  }
}

// --------
// INITIALISE
// --------

$s = $oNE->section;
if ( $fs==='' ) $fs = $s;

if ( $fs==='*' )
{
  $oVOIDSEC = new cSection(); // void section in case of "all sections"
  $oVOIDSEC->ReadTypes(true); // return types through all sections
}
else
{
  $fs = (int)$fs;
  $oVOIDSEC = new cSection($fs); // Attention: This is not the section of the item, but the section used in the Search form!
  $oVOIDSEC->ReadTypes();
}

$strCommand = '';
if (isset($_GET['view'])) { $_SESSION[QT]['viewmode'] = $_GET['view']; }

$oVIP->selfurl = 'qnm_form_link_e.php';
$oVIP->exiturl = 'qnm_item.php';
$oVIP->selfname = L('Content').' '.L('of').' '.$oNE->id;
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

// --------
// HTML START
// --------

$oHtml->links[] = '<script type="text/javascript" src="bin/js/qnm_table.js"></script>';
$oHtml->scripts[] = '<script type="text/javascript">
<!--
$(document).ready(function() {

  // TAG HOVER

  $(".tag").hover(function() {
    var oTag = $(this);
    $.post("qnm_j_tag.php",{s:"'.$oNE->section.'",val:oTag.html(),lang:"'.GetIso().'",na:"..."}, function(data) { oTag.attr({title:data}); } );
    });

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
  for (i=0; i<checkboxes.length; i++) if ( checkboxes[i].checked ) n++;
  if ( n>0 )
  {
  document.getElementById(\'f1_a\').value=action;
  if ( action==\'type\' ) document.getElementById(\'f1\').action=\'qnm_f_ne_edits.php\';
  document.getElementById(\'f1\').submit();
  return true;
  }
  alert(qtHtmldecode("'.L('No_selected_row').'"));
  return false;
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
// ELEMENT FIELDS
// --------

if ( !empty($nid) )
{
echo '<p>',$oNE->Dump(true,'class="bold"'),'</p>';
}

// --------
// EXISTING LINKS (fl)
// --------

echo '
<h1>',$oVIP->selfname,'</h1>
';

$arrLinked = $oNE->GetNL('e');  // list not sortable

// ::::::::
if ( count($arrLinked)==0 )
{
  $table = new cTable('t1','data_t subitems',count($arrLinked));
  $table->th[] = new cTableHead('&nbsp;');
  echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.L('No_sub-item').'...</p>',true,'','r1');
}
else
{
// ::::::::

  if ( $oNE->conns>0 ) $arrCC = $oNE->GetCC(); // in case of connectors detect if connection exist

  // pager

  $strPager = MakePager($oVIP->selfurl.'?'.GetURI('page'),count($arrLinked),$_SESSION[QT]['items_per_page'],$page);
  if ( !empty($strPager) ) $strPager = $L['Page'].$strPager;
  if ( !empty($strPager) ) echo '<table class="hidden"><tr class="hidden"><td class="hidden" id="pager_zt">',$strPager,'</td></tr></table>',PHP_EOL;

echo '
<form id="f1" method="post" action="',Href(),'?',GetURI('page'),'">
<p class="datasetcontrol">
<img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:middle;margin:0 10px 0 12px" alt="|" />
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'activate\'); return false;" href="#" title="'.L('cmd_Activate').'">'.L('Activate').'</a> &middot;
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'inactivate\'); return false;" href="#" title="'.L('cmd_Inactivate').'">'.L('Inactivate').'</a> &middot;
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'type\'); return false;" href="#" title="'.L('cmd_Change_type').'">'.L('Change_type').'</a> &middot;
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'unlink\'); return false;" href="#" title="'.L('cmd_Delete').'">'.L('Remove').'</a>
<span class="small">',L('cmd_Delete_help'),'</span>
<input type="hidden" name="s" value="',$s,'"/>
<input type="hidden" id="f1_a" name="a" value=""/>
<input type="hidden" id="f1_nid" name="nid" value="',GetNid($oNE),'"/>
</p>
';

// === TABLE DEFINITION ===

$table = new cTable('t1','data_t subitems',count($arrLinked));
$table->activecol = $strOrder;
$table->activelink = '<a  href="'.Href().'?'.GetURI('order,dir,page').'&amp;order='.$strOrder.'&amp;dir='.($strDirec=='asc' ? 'desc' : 'asc').'&amp;page=1">%s</a> <img class="i_sort" src="'.$_SESSION[QT]['skin_dir'].'/sort_'.$strDirec.'.gif" alt="+"/>';
// column headers
$table->th['checkbox'] = new cTableHead(($table->rowcount<2 ? '&nbsp;' : '<input type="checkbox" name="t1_cb_all" id="t1_cb" />'));
$table->th['icon']     = new cTableHead('&nbsp;');
$table->th['id']       = new cTableHead('ID','','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=id&amp;dir=asc">%s</a>');
$table->th['(links)']  = new cTableHead(L('Links'));
$table->th['type']     = new cTableHead(L('Type'),'','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=type&amp;dir=asc">%s</a>');
$table->th['address']  = new cTableHead(L('Address'),'','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=address&amp;dir=asc">%s</a>');
$table->th['descr']    = new cTableHead(L('Description'));
$table->th['posts']    = new cTableHead(AsImg($_SESSION[QT]['skin_dir'].'/ico_notes.gif','N',L('In_process_notes'),'i_note'),'','','<a href="'.Href().'?'.GetURI('order,dir').'&amp;order=note&amp;dir=desc">%s</a>');
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
if ( $page>1 ) $arrLinked=array_slice($arrLinked,($page-1)*$_SESSION[QT]['items_per_page'],$_SESSION[QT]['items_per_page'],true); // slice item in case of several pages
foreach($arrLinked as $key=>$oSubNL)
{
  $oSubNE = $oSubNL->ne2;
  if ( isset($arrCC['c.'.$oSubNE->uid]) ) $oSubNE->links=1;
  if ( !empty($oSubNE->descr) ) $oSubNE->descr = QTcompact($oSubNE->descr,50);
  // prepare row
  $table->row = new cTableRow( 'tr_t1_cb'.GetUid($key), 'data_t '.$strAlt.' rowlight' );

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
}
// ::::::::

// extra menu: create element

if ( $oNE->class!='c' )
{
  echo '<table class="pagecmd_up"><tr class="pagecmd">
  <td class="pagecmd_up right"><div class="pagecmd">
  <ul><li><a href="',Href('qnm_form_newclass.php'),'?s=',$s,'&amp;nid=',GetNid($oNE),'">',L('Create_sub-items').'</a></li><li><a href="',Href('qnm_form_new.php'),'?s=',$s,'&amp;nid=',GetNid($oNE),'&amp;a=c">',L('Create_connectors').'</a></li></ul>
  </div></td></tr></table>
  ';
}
// --------
// ADD LINK form filter (ff)
// --------

// element class 'e' not included int the filter liste (0,nid,existing link)
$arrNot = array($oNE->uid);
foreach($arrLinked as $key=>$oSubNL) { if ( $oSubNL->ne2->class!='c' ) $arrNot[]=$oSubNL->ne2->uid; }

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
<h1>',L('Add_inside'),' ',$oNE->id,'</h1>
';

echo '<div class="itemselect">
<div class="itemfilter">
<form id="ff" method="post" action="',Href(),'?nid=',GetNid($oNE),'#ff">
<p class="itemfilter">',L('f_Search_other_section'),' <select name="fs" onchange="this.form.submit();">',Sectionlist($fs,array(),array(),L('All_sections')),'</select></p>
<p class="itemfilter">
',L('f_Show_only_type'),' <select name="ft"><option value="*">',L('all_types'),'</option>',$strTypes,'</select>
',L('status'),' <select name="fu"><option value="*">',L('all_statuses'),'</option>',QTasTag(array(L('Inactive'),L('Active'),'-1'=>L('Deleted')),($fu!=='*' ? $fu : null)),'</select>
Id <input type="text" name="fi" value="',$fi,'" size="8" title="',L('f_Enter_id'),'"/>
<input type="submit" id="ff_ok" name="ff_ok" value="',L('Ok'),'"/>
<input type="hidden" name="a" value="',$a,'"/>
<input type="hidden" name="c" value="',$c,'"/>
<input type="hidden" name="nid" value="',$nid,'"/>
</p>
</form>
</div>
';

// ::::::::
if ( count($arrFilter)==0 ) { $table = new cTable('t2','data_t'); $table->th[] = new cTableHead('&nbsp;'); echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.L('No_item').'...</p>',true,'','r1'); } else {
// ::::::::

echo '
<form id="fe" method="post" action="',Href(),'?nid=',GetNid($oNE),'">
<p class="datasetcontrol"><img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:middle;margin:0 10px 0 12px" alt="|" />
<input type="hidden" id="fe_a" name="a" value="',$a,'"/>
<input type="hidden" id="fe_c" name="c" value="',$c,'"/>
<input type="hidden" id="fe_uid" name="uid" value="',$oNE->uid,'"/>
<input class="small" type="submit" id="fe_ok" name="fe_ok" value="',L('Add_selected'),'" onclick="return datasetcontrol_click(\'t2_cb[]\');"/>
 <span class="small">',L('f_Add_parent'),'</span>
 </p>
';

// === TABLE DEFINITION ===

$table = new cTable('t2','data_t subfilter',count($arrLinked));
  $table->activecol = $strOrderFilter;
  $table->activelink = '<a  href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf='.$strOrderFilter.'&amp;dirf='.($strDirecFilter=='asc' ? 'desc' : 'asc').'#ff">%s</a> <img class="i_sort" src="'.$_SESSION[QT]['skin_dir'].'/sort_'.$strDirecFilter.'.gif" alt="+"/>';
  // column headers
  $table->th['checkbox']    = new cTableHead(($table->rowcount<2 ? '&nbsp;' : '<input type="checkbox" name="t2_cb_all" id="t2_cb" />'));
  $table->th['icon']        = new cTableHead('&nbsp;');
  $table->th['id']          = new cTableHead('ID','','','<a href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf=id&amp;dirf=asc#ff">%s</a>');
  $table->th['(parent_red)']= new cTableHead('P');
  $table->th['(links)']     = new cTableHead(L('Links'));
  $table->th['type']        = new cTableHead(L('Type'),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf=type&amp;dirf=asc#ff">%s</a>');
  $table->th['address']     = new cTableHead(L('Address'),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf=address&amp;dirf=asc#ff">%s</a>');
  if ( $fs==='*' ) {
    $table->th['section']   = new cTableHead(L('Section'),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf=section&amp;dirf=asc#ff">%s</a>');
  } else {
    $table->th['descr']     = new cTableHead(L('Description'));
  }
  $table->th['posts']       = new cTableHead(AsImg($_SESSION[QT]['skin_dir'].'/ico_notes.gif','N',L('In_process_notes'),'i_note'),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('orderf,dirf').'&amp;orderf=posts&amp;dirf=desc#ff">%s</a>');
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
  if ( $strAlt=='r1' ) {
    $strAlt='r2';
  } else { $strAlt='r1';
  }
}

// === TABLE END DISPLAY ===

echo '</tbody>
</table>
</form>
';

// ::::::::
}
// ::::::::

echo '
</div>
<p><a href="',$oVIP->exiturl,'?nid=',GetNid($oNE),'">&laquo; ',$oVIP->exitname,'</a></p>
';

// --------
// HTML END
// --------

include 'qnm_inc_ft.php';