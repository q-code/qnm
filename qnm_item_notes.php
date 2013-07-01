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
* @copyright  2012 The PHP Group
* @version    1.0 build:20130410
*/

session_start();
require_once 'bin/qnm_init.php';
if ( $oVIP->user->role=='V' ) HtmlPage(11);
if ( !$oVIP->user->CanView('V3') ) HtmlPage(11);

// ---------
// INI
// ---------

$nid = '';
$page = 1;
$intLimit = 0;
$order='issuedate';
$dir='desc';

QThttpvar('nid page order dir','str int str str');

if ( empty($nid) ) die('Missing element id...'); //also item 0 cannot have notes
if ( GetNclass($nid)=='c' ) die('Cannot handled connector notes...');
if ( $page<1 ) $page=1;
if ( $page>1 ) $intLimit = ($page-1)*$_SESSION[QT]['items_per_page'];
$dir = strtolower($dir);

require_once 'bin/qnm_fn_sql.php';

// ---------
// SUBMITTED
// ---------

if ( !empty($nid) && isset($_POST['a']) )
{
  if ( $oVIP->user->role=='V' ) HtmlPage(11);
  // read checkboxes uid
  $arrId=array();
  if ( isset($_POST['cb_n1']) ) {
  foreach($_POST['cb_n1'] as $str ) {
    $arrId[]=intval($str);
  }}

  if ( count($arrId)>0 )
  {
    $oNE = new cNE();
    $oNE->IdDecode($nid);
    // close = inactivate, in process = activate
    if ( $_POST['a']=='activate' || $_POST['a']=='inactivate' )
    {
      $oPOST = new cPOST(); // empty object
      foreach($arrId as $i)
      {
      $oPOST->id = $i;
      $oPOST->UpdateField('status',$_POST['a']=='activate' ? 1 : 0);
      }
    }
    if ( $_POST['a']=='delete' )
    {
      $oNE->DeleteNotes(true,$arrId);
    }
    $oNE->UpdateNotes(); // computes notes in process after 'close','in process' or 'delete'
  }
}

// --------
// INITIALISE
// --------

$oNE = new cNE($nid); if ( $oNE->id=='[unknown]') $oNE->uid=0;
$s = $oNE->section;
$_SESSION[QT]['section'] = $s; // previous section

if (isset($_GET['view'])) $_SESSION[QT]['viewmode'] = $_GET['view'];

$oVIP->selfurl = 'qnm_item_notes.php';
$oVIP->exiturl = 'qnm_item.php?nid='.$nid;
$oVIP->selfname = $L['Item'];

// Parent
if ($oNE->pid>0) $oPARENT = new cNE($oNE->pid); // note: parent can be [e] or [l], the constructor method use the db specified class

// --------
// HTML START
// --------


$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qnm_table.js"></script>
<script type="text/javascript">
<!--
$(document).ready(function() {

  // Prepare preview dialog

  $("#notedialog").dialog( {autoOpen:false,width:400,height:280,minWidth:250,minHeight:150});

  // CHECKBOX checked when clicking some columns

  $("#n1 td:not(.tdcheckbox,.tdaction)").click(function() { qtCheckboxToggle(this.parentNode.id.substring(3)); });

  // CHECKBOX ALL ROWS

  $("input[id=\'n1_cb\']").click(function() { qtCheckboxAll("n1_cb","cb_n1[]",true); });

  // SHIFT-CLICK CHECKBOX

  var lastChecked = null;
  $("input[name=\'cb_n1[]\']").click(function(event) {
    if(!lastChecked)
    {
      lastChecked = this;
      qtHighlight("tr_"+this.id,this.checked);
      return;
    }
    if(event.shiftKey)
    {
      var start = $("input[name=\'cb_n1[]\']").index(this);
      var end = $("input[name=\'cb_n1[]\']").index(lastChecked);
      for(var i=Math.min(start,end);i<=Math.max(start,end);i++)
      {
      $("input[name=\'cb_n1[]\']")[i].checked = lastChecked.checked;
      qtHighlight("tr_"+$("input[name=\'cb_n1[]\']")[i].id,lastChecked.checked);
      }
    }
    lastChecked = this;
    qtHighlight("tr_"+this.id,this.checked);
  });
});

function showpreview(id)
{
  var doc=document;
  var item="'.$oNE->id.'";
  var textmsg = doc.getElementById("textmsg_"+id); if ( !textmsg ) return;
  var dialog = doc.getElementById("notedialog"); if ( !dialog ) return;
  var status = doc.getElementById("status_"+id).outerHTML; if ( !status ) status="";
  dialog.innerHTML = "<p class=\"bold\">" + status + " " + textmsg.title + "</p>" + textmsg.innerHTML;
  $("#notedialog").dialog({title:item});
  $("#notedialog").dialog("open");
}
function datasetcontrol_click(checkboxname,action)
{
  var checkboxes = document.getElementsByName(checkboxname);
  var n = 0;
  for (var i=0; i<checkboxes.length; i++) if ( checkboxes[i].checked ) n++;
  if ( n>0 )
  {
    if (action=="delete") { if (!confirm(qtHtmldecode("'.L('Confirm_delete_notes').'"))) return false; }
    document.getElementById(\'form_n1_a\').value=action;
    document.getElementById(\'form_n1\').submit();
    return true;
  }
  else
  {
    alert(qtHtmldecode("'.L('No_selected_row').'"));
    return false;
  }
}
function highlight(id,color)
{
  var id2;
  var i;
  for (i=0;i<arrC1.length;i++)
  {
    if ( arrC1[i]==id ) { id2=arrC2[i]; break; }
  }

  if (id2==null)
  {
    for (i=0;i<arrC2.length;i++)
    {
      if ( arrC2[i]==id ) { id2=arrC1[i]; break; }
    }
  }

  if ( id && id2 )
  {
  if (document.getElementById(id)) document.getElementById(id).style.backgroundColor=color;
  if (document.getElementById(id2)) document.getElementById(id2).style.backgroundColor=color;
  }
}
function doselect(checkboxname,action)
{
  var checkboxes = document.getElementsByName(checkboxname);
  var i = checkboxes.length - 1;
  do
  {
  switch(action)
  {
  case "none":      checkboxes[i].checked=false; break;
  case "close":     checkboxes[i].checked=(checkboxes[i].id.substring(checkboxes[i].id.length-2)=="_0"); break;
  case "inprocess": checkboxes[i].checked=(checkboxes[i].id.substring(checkboxes[i].id.length-2)=="_1"); break;
  case "delete":    checkboxes[i].checked=(checkboxes[i].id.substring(checkboxes[i].id.length-2)=="-1"); break;
  default:          checkboxes[i].checked=true; break;
  }
  qtHighlight("tr_"+checkboxes[i].id,checkboxes[i].checked);
  }
  while (i--);
  qtCheckboxOne(checkboxname,"n1_cb");
}
//-->
</script>
';

include 'qnm_inc_hd.php';

$strCommand = '<div class="pagecmd">'.PHP_EOL;
$strCommand .= '<ul>'.PHP_EOL;
if  (QNM_BACKBUTTON ) $strCommand .= '<li><a href="'.Href($oVIP->exiturl).'">'.QNM_BACKBUTTON.'</a></li>';
$strCommand .= '<li><a href="'.Href('qnm_f_ne_edits.php').'?s='.$s.'&amp;nids='.$nid.'&amp;a=note&amp;e=e">'.L('Add_note').'</a></li>';
$strCommand .= '</ul>'.PHP_EOL;
$strCommand .= '</div>'.PHP_EOL;

// Moderator action

if ( isset($strStaffMenu) ) echo $strStaffMenu;

// --------
// ELEMENT
// --------

echo '
<div class="frameelement">
<h1 class="elementid">',$oNE->Idstatus(false,'class="elementid"'),'</h1>
<h1 class="elementheader">',cNE::Classname($oNE->class),'</h1>
';
echo '<p>',$oNE->Dump(true,'class="bold"');
if ( $oNE->class!='c' ) echo '<br/>',$oNE->DumpContent(false,'',20);
echo '</p>';

if ( isset($oPARENT) )
{
  echo '<p>',L('in').' ',cNE::GetIcon($oPARENT),' ',$oPARENT->Idstatus(),'</p>';
}
echo '</div>
';

if ( !empty($error) ) echo '
<p id="errormessage" class="error">',$error,'</p>
';

// --------
// NOTES
// --------

$intCountElements = $oNE->CountPosts('status>=0');
// Attention: $oNE->posts are active notes only
// $intVisibleNotes are is active[1] + closed[0] notes
$str = '0';
if ( $intCountElements>0 ) $str = $oNE->posts.'<span class="small"> '.L('in_process_notes').' /'.$intCountElements.'</span>';

echo '
<a name="notes"></a>
<div id="assetnotes" class="noteheader">
<h1>',L('Messages'),' (',$str,')</h1>
</div>
';

// IF NO DATSET

if ( $intCountElements==0 )
{
  $table->th[] = new cTableHead('&nbsp;');
  echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.L('No_item').'...</p>',true,'','r1');
  include 'qnm_inc_ft.php';
  exit;
}

// SHOW PAGE COMMAND (top)

$strPager = MakePager($oVIP->selfurl.'?'.GetURI('order,dir').'&amp;order='.$order.'&amp;dir='.$dir,$intCountElements,$_SESSION[QT]['items_per_page'],$page);
if ($strPager!='') $strPager = $L['Page'].$strPager;

echo '<table class="pagecmd_up"><tr class="pagecmd"><td class="pagecmd_up">',$strCommand,'</td><td id="pager_zt">&nbsp;',$strPager,'</td></tr></table>',PHP_EOL;

// DATASET FORM AND CONTROLS

$strCmd = '';
$strCmd .= '<a class="datasetcontrol" onclick="datasetcontrol_click(\'cb_n1[]\',\'inactivate\'); return false;" href="#">'.L('Close').'</a> &middot; ';
$strCmd .= '<a class="datasetcontrol" onclick="datasetcontrol_click(\'cb_n1[]\',\'activate\'); return false;" href="#">'.L('Set_in_process').'</a> &middot; ';
$strCmd .= '<a class="datasetcontrol" onclick="datasetcontrol_click(\'cb_n1[]\',\'delete\'); return false;" href="#">'.L('Delete').'</a>';
if ( $intCountElements>2 )
{
$strCmd .= '&nbsp; | &nbsp;'.L('Select').' &nbsp;<a class="datasetcontrol" onclick="doselect(\'cb_n1[]\',\'inprocess\'); return false;" href="#">'.L('in_process_notes').'</a>';
$strCmd .= ' &nbsp;<a class="datasetcontrol" onclick="doselect(\'cb_n1[]\',\'close\'); return false;" href="#">'.L('closed_notes').'</a>';
}

$str = Href().'?'.GetURI('page');
echo '<form id="form_n1" method="post" action="',$str,'">
<p class="datasetcontroltop">
<img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:middle;margin:0 5px 0 10px" alt="|" />',$strCmd,'<input type="hidden" name="nid" value="',$nid,'"/><input type="hidden" name="a" value="0" id="form_n1_a"/>
</p>
';

// === TABLE DEFINITION ===
$table = new cTable('n1','data_t');
$table->rowcount = $intCountElements;
$table->activecol = $order;
$table->activelink = '<a href="'.$oVIP->selfurl.'?'.GetURI('page,order,dir').'&amp;order='.$order.'&amp;dir='.($dir=='asc' ? 'desc' : 'asc').'">%s</a>&nbsp;<img class="i_sort" src="'.$_SESSION[QT]['skin_dir'].'/sort_'.$dir.'.gif" alt="+"/>';
// create column headers
$table->th['checkbox'] = new cTableHead(($table->rowcount<2 ? '&nbsp;' : '<input type="checkbox" name="n1_cb_all" id="n1_cb" />'));
$table->th['icon'] = new cTableHead('<span title="'.L('Status').'">['.substr(L('Status'),0,1).']</span>','','','<a href="'.$oVIP->selfurl.'?'.GetURI('page,order,dir').'&amp;order=icon&amp;dir=desc">%s</a>');
$table->th['issuedate'] = new cTableHead(L('Created'),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('page,order,dir').'&amp;order=issuedate&amp;dir=desc">%s</a>');
$table->th['username'] = new cTableHead(L('Author'),'','','<a href="'.$oVIP->selfurl.'?'.GetURI('page,order,dir').'&amp;order=username&amp;dir=asc">%s</a>');
$table->th['textmsg'] = new cTableHead(L('Message'));
$table->th['action'] = new cTableHead('&nbsp;');
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

if ( $order=='icon' ) $order='status';
$strFullOrder = $order.' '.strtoupper($dir); if ( $order!='issuedate' ) $strFullOrder .= ',issuedate DESC';
$oDB->Query( LimitSQL(implode(',',cPost::GetFields()).' FROM '.cPost::GetTable().' WHERE pclass="'.$oNE->class.'" AND pid='.$oNE->uid,$strFullOrder,$intLimit,$_SESSION[QT]['items_per_page'],$intCountElements) );

$intWhile=0;
$strAlt='r1';
while($row=$oDB->Getrow())
{
    $oPost = new cPost($row);

    $table->row = new cTableRow('tr_n_'.$oPost->id.'_'.$oPost->status,'data_t '.$strAlt.' rowlight');
    $table->td['checkbox']->content = '<input type="checkbox" name="cb_n1[]" value="'.$oPost->id.'" id="n_'.$oPost->id.'_'.$oPost->status.'"/>';
    $table->td['icon']->content = $oPost->GetIcon('','','','i_note','status_'.$oPost->id);
    $table->td['username']->content = ( empty($oPost->userid) ? '&nbsp;' : '<a class="small" href="'.Href('qnm_user.php').'?id='.$oPost->userid.'">'.$oPost->username.'</a>' );
    $table->td['issuedate']->content = ( empty($oPost->issuedate) ? '&nbsp;' : QTdatestr($oPost->issuedate,'$','$',true) );
    $table->td['textmsg']->content = ( empty($oPost->textmsg) ? '&nbsp;' : '<div class="scroller" id="textmsg_'.$oPost->id.'" title="'.$table->td['issuedate']->content.' '.L('by').' '.$oPost->username.'">'.$oPost->textmsg.'</div>' );
    if ( $oPost->status==0 )
    {
      $table->td['textmsg']->Add('class','tdtextmsg disabled');
      $table->td['issuedate']->Add('class','tdissuedate disabled');
    }
    else
    {
      $table->td['textmsg']->Add('class','tdtextmsg');
      $table->td['issuedate']->Add('class','tdissuedate');
    }
    $table->td['action']->content = '<img class="scrollerviewer" src="'.$_SESSION[QT]['skin_dir'].'/preview.png" alt="&laquo;" title="'.L('Preview').'" onclick="showpreview('.$oPost->id.');"/>';
    if ( $oVIP->user->IsStaff() || $oVIP->user->id==$oPost->userid )
      $table->td['action']->content .= '<a href="'.Href('qnm_form_note.php').'?id='.$oPost->id.'&amp;pid='.$nid.'"><img class="editviewer" src="'.$_SESSION[QT]['skin_dir'].'/edit.png" alt="'.L('Edit').'" title="'.L('Edit').'"/></a>';
    echo $table->GetTDrow();

    $intWhile++;
    if ( $intWhile>=$_SESSION[QT]['items_per_page'] ) break;
}

echo '
</table>
</form>
';

// SHOW PAGE COMMAND (bottom)

if ( $intCountElements>10 )
{
  echo '<p class="datasetcontrolbot"><img src="admin/selection_down.gif" style="width:10px;height:10px;vertical-align:middle;margin:0 5px 0 10px" alt="|" />',$strCmd,'<input type="hidden" name="nid" value="',$nid,'"/><input type="hidden" name="a" value="0" id="form_n1_a"/></p>',PHP_EOL;
  echo '<table class="pagecmd_down"><tr class="pagecmd"><td class="pagecmd_down">',$strCommand,'</td><td id="pager_zb">&nbsp;',$strPager,'</td></tr></table>',PHP_EOL;
}
else
{
  echo '<p id="pager_zb" class="csv">&nbsp;',$strPager,'</p>',PHP_EOL;
}

// --------
// HTML END
// --------

echo '
<div id="notedialog"></div>
';
include 'qnm_inc_ft.php';