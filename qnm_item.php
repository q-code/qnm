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
if ( !$oVIP->user->CanView('V3') ) HtmlPage(11);

// ---------
// Function
// ---------

function VisualSubNE($arrE,$arrHighlight=array(),$iSize=1)
{
  // $iSize [1] for small box 10 element per line (max 5 lines) [2] for large box 20 items per line (maximum 6 lines)
  $strSub = '';
  $iCol=0;
  $iRow=0;
  $iCon=0;
  $iEqu=0;
  $bMirrorBreak=false;
  foreach($arrE as $key=>$oSubNE)
  {
    if ( $iRow>3+$iSize ) { $strSub .='...<br/>'; break; }
    switch($oSubNE->class)
    {
    case 'e':
    case 'l':
      $iEqu++;
      if ( $iEqu<=10*$iSize ) $strSub .= cNE::GetIcon($oSubNE,true).' '.$oSubNE->Idstatus(true,'class="small"').$oSubNE->DumpLinks('class="small"').$oSubNE->DumpNotes().'<br/>';
      if ( $iEqu==10*$iSize+1 ) $strSub .='...';
      break;
    case 'c':
      $iCon++;
      $iCol++;
      if ( in_array(GetUid($key),$arrHighlight) ) $oSubNE->links=1;
      if ( !$bMirrorBreak && substr($oSubNE->id,0,1)=='~' ) { $iRow++; $iCol=1; $strSub .='<br/>'; $bMirrorBreak=true; }
      $strSub .=  cNE::GetIcon(
        $oSubNE->class,
        $oSubNE->id.' : '.$oSubNE->type.($oSubNE->status==0 ? ' ('.L('inactive').')' : '').($oSubNE->links==0 ? ' ('.L('free').')' : ''),
        'c.'.$oSubNE->uid,
        'visualconn'.($oSubNE->links==0 ? ' free' : '').($oSubNE->status==0 ? ' disabled' : ''),
        'onmouseover="highlight(this.id,\'#66ff66\');" onmouseout="highlight(this.id,\'#eeeeee\');"'
        );
      if ( $iCol>=10*$iSize ) { $iRow++; $iCol=0; $strSub .='<br/>'; }
      break;
    }
  }
  if ( empty($strSub) ) return '<span class="disabled">'.L('no_sub-item').'</span><br/>';
  return $strSub.(substr($strSub,-5,5)=='<br/>' ? '' : '<br/>');
}

// ---------
// INI
// ---------

$nid = '';
$page = 1;
$nip = 1; // in process note on top, default=1 (true)
$note = 0; // note to highlight
QThttpvar('nid page note nip','str int int int');
if ( $nid==='' ) die('Missing element id...');
if ( $note<0 ) $note=0;
if ( $page<1 ) $page=1;
$intLimit = 0;
if ( $page>1 ) $intLimit = ($page-1)*$_SESSION[QT]['replies_per_page'];

require_once 'bin/qnm_fn_tags.php';
require_once 'bin/qnm_fn_sql.php';

$oNE = new cNE($nid); if ( $oNE->id=='[unknown]') $oNE->uid=0;
$s = $oNE->section;
$oSEC = new cSection($s);

// MAP MODULE

$bMap=false;
if ( UseModule('map') )
{
  include 'qnmm_map_lib.php';
  $bMap = QTgcanmap($s,true,false); // Read the config file to initialize the $_SESSION[QT]['m_map'][] arguments // No main list check
  if ( $bMap )
  {
    include Translate('qnmm_map.php');
    $oHtml->links[]='<link rel="stylesheet" type="text/css" href="qnmm_map.css" />';
  }
}

// ---------
// SUBMITTED (action on selected NOTES)
// ---------

if ( !empty($nid) && isset($_POST['a']) )
{
  if ( $oVIP->user->role=='V' ) HtmlPage(11);
  // read checkboxes uid
  $arrId=array();
  if ( isset($_POST['cb_n1']) ) foreach($_POST['cb_n1'] as $str ) $arrId[]=(int)$str;

  if ( count($arrId)>0 )
  {
    switch($_POST['a'])
    {
      case 'inactivate':
      case 'activate':
        $oPOST = new cPOST(); // empty object
        foreach($arrId as $i)
        {
          $oPOST->id = $i;
          $oPOST->UpdateField('status',$_POST['a']=='activate' ? 1 : 0);
          if ( $_POST['a']=='activate' ) { $oNE->posts++; } else { $oNE->posts--; }
        }
        $oSEC->MChange('stats','notesA',cSection::CountItems($s,'notesA')); // update section stats
        $_SESSION[QT]['sys_stat_notesA']=cSection::CountItems('*','notesA'); // update system stats
        $_SESSION['pagedialog']='O|'.L('S_update');
        break;
      case 'delete':
        $oNE->DeleteNotes(true,$arrId);
        $arr = $oSEC->MRead('stats'); unset($arr['notes']); unset($arr['notesA']);
        $oSEC->UpdateStats($arr);
        $_SESSION[QT]['sys_stat_notes']=cSection::CountItems('*','notes'); // update system stats
        $_SESSION[QT]['sys_stat_notesA']=cSection::CountItems('*','notesA'); // update system stats
        break;
    }
    $oNE->UpdateNotes(); // computes notes in process after 'close','in process' or 'delete'
    if ( $oNE->posts<0 ) $oNE->posts=0;
  }
}

// --------
// INITIALISE
// --------

$_SESSION[QT]['section'] = $s; // previous section

// exit according to section settings
if ( $oSEC->type!=0 && !$oVIP->user->IsStaff() )
{
$oVIP->selfname = $L['Section'];
$oVIP->exitname = ObjTrans('index','i',$_SESSION[QT]['index_name']);
if ( $oSEC->type==1 ) $oHtml->PageBox(NULL,$L['R_staff'],$_SESSION[QT]['skin_dir'],0);
if ( $oSEC->type==2 && $oVIP->user->role=='V' ) $oHtml->PageBox(NULL,$L['R_member'].'<br/><br/><a href="'.Href('qnm_login.php').'?s='.$s.'&amp;nid='.$nid.'">'.$L['Login'].'</a>',$_SESSION[QT]['skin_dir'],0);
}

if (isset($_GET['view'])) $_SESSION[QT]['viewmode'] = $_GET['view'];

$oVIP->selfurl = 'qnm_item.php';
$oVIP->exiturl = 'qnm_items.php?s='.$s;
$oVIP->selfname = $L['Item'];

$strOrder = 'uid';
$strDirec = 'ASC';
if ( isset($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = $_GET['dir'];

// Parent
if ($oNE->pid>0) $oPARENT = new cNE($oNE->pid); // note: parent can be [e] or [l], the constructor method use the db specified class

// Linked
$arrR = array();  // the relations
$arrE = array();  // the embeded items
$arrCC = array(); // If can exists, register the connector to connectors links (used by java)
if ( $oNE->uid>0 )
{
  if ( $oNE->class=='c' )
  {
    $arrR = $oNE->GetConnected(); // List of cNL relations type [c]connections, the keys are the [class].[uid]
  }
  else
  {
    if ( $oNE->links>0 ) $arrR = $oNE->GetConnected(); // List of cNL relations type [c]connected, the keys are the [nid]
    if ( $oNE->items>0 || $oNE->conns>0 ) $arrE = $oNE->GetEmbeded('all'); // List of sub cNE (keys are the [nid])
    if ( $oNE->conns>0 && count($arrR)>0 ) $arrCC = $oNE->GetCC(cNL::GetNEs($arrR,2,false)); // List of cNL, the keys are the [nid]. GetCC use $arrR to speed up parent search
  }
}

// tags

if ( isset($_POST['addtag']) )
{
  $str = strip_tags(trim($_POST['tag']));
  if ( !empty($str) && $str!='*' ) { $oNE->TagsAdd($str,$oSEC); $_SESSION['pagedialog']='O|'.L('S_update');}
}
if ( isset($_POST['deltag']) )
{
  $str = strip_tags($_POST['tag']);
  if ( !empty($str) ) { $oNE->TagsDel($str,$oSEC); $_SESSION['pagedialog']='O|'.L('S_update'); }
}

// --------
// HTML START
// --------

// tags preprocessing
$arr1 = TagsRead(GetIso(),$s);
$arr2 = TagsRead(GetIso(),'*');
$arrTags = array_merge($arr1,$arr2);
if ( count($arrTags)<100 )
{
  $arr1 = cSection::GetTagsUsed($s);
  foreach($arr1 as $strKey=>$strDesc) {
    if ( !isset($arrTags[$strKey]) ) $arrTags[$strKey]=$strDesc;
  }
}
$str = '';
foreach($arrTags as $strKey=>$strDesc) {
  $str .= '{n:"'.$strKey.'",d:"'.($strKey==$strDesc ? ' ' : substr($strDesc,0,64)).'"},';
}
$strTags = substr($str,0,-1);

// scripts
$oHtml->links[] = '<link rel="stylesheet" href="bin/js/prettyPhoto/css/prettyPhoto.css" type="text/css" media="screen" />';
$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qnm_table.js"></script>
<script type="text/javascript" src="bin/js/prettyPhoto/js/jquery.prettyPhoto.js"></script>
<script type="text/javascript">
<!--
function showEdittags()
{
  var doc = document;
  if (doc.getElementById("edittags") && doc.getElementById("tag") && doc.getElementById("addtag") && doc.getElementById("deltag"))
  {
    var s = (doc.getElementById("tag").style.display=="none" || doc.getElementById("tag").style.display=="" ? "inline" : "none");
    doc.getElementById("tag").style.display=s;
    doc.getElementById("addtag").style.display=s;
    doc.getElementById("deltag").style.display=s;
    if ( s=="none" )
    {
    doc.getElementById("edittags").value=String.fromCharCode(187);
    }
    else
    {
    doc.getElementById("edittags").value=String.fromCharCode(171);
    doc.getElementById("tag").focus();
    }
  }
}
function split( val ) { return val.split( "'.QNM_QUERY_SEPARATOR.'" ); }
function extractLast( term ) { return split( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }
//-->
</script>
';

$oHtml->scripts_end[] = '<script type="text/javascript">
<!--
$(document).ready(function() {

  // Prepare preview dialog

  $("#notedialog").dialog( {autoOpen:false,width:400,height:280,minWidth:250,minHeight:150});

  // CHECKBOX checked when clicking some columns

  $("#notes td:not(.tdcheckbox,.tdaction)").click(function() { qtCheckboxToggle(this.parentNode.id.substring(3)); });

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

  // TAG infotip

  $(".tag").hover(function() {
    var oTag = $(this);
    $.post("qnm_j_tagdesc.php",{s:"'.$s.'",val:oTag.html(),lang:"'.GetIso().'",na:"..."}, function(data) { oTag.attr({title:data}); } );
  });

  // TAG autocomplete

  $("#tag").autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_tag.php",
        dataType: "json",
        data: { term: extractLast( request.term ), o_se:'.$s.', lang:"'.GetIso().'" },
        success: function(data) { response(data); }
      });
    },
    search: function() {
      // custom minLength
      var term = extractLast( this.value );
      if ( term.length < 1 ) { return false; }
  },
    focus: function( event, ui ) { return false; },
    select: function( event, ui ) {
      var terms = split( this.value );
      terms.pop(); // remove current input
      terms.push( ui.item.rItem ); // add the selected item
      terms.push( "" ); // add placeholder to get the comma-and-space at the end
      this.value = terms.join( "'.QNM_QUERY_SEPARATOR.'" );
      return false;
    }
  })
  .data( "autocomplete" )._renderItem = function( ul, item ) {
    return $( "<li></li>" )
      .data( "item.autocomplete", item )
      .append( "<a class=\"jvalue\">" + item.rItem + (item.rInfo=="" ? "" : " &nbsp;<span class=\"jinfo\">(" + item.rInfo + ")</span>") + "</a>" )
      .appendTo( ul );
  };

  // PrettyPhoto
  $("a[rel=\'prettyPhoto[]\']").prettyPhoto({ social_tools:false, slideshow:false, overlay_gallery:false });

});

function preview(thumb,title,onclick)
{
  doc = document.getElementById(\'imgpreview\');
  doc2 = document.getElementById(\'imgpreviewclick\');
  if ( doc )
  {
    doc.src = thumb;
    doc.title = title;
    var label = (onclick ? title : "'.L('Open').'...");
    if ( doc2 )
    {
    if ( label.length>30 )
    {
      if (label.substr(-1)=="/" ) label = label.substr(0,label.length-1);
      label = label.replace("http://","").replace("https://","");
    }
    if ( label.length>30 )
    {
      var arr = label.split("/");
      var str1 = arr[0];
      var str2 = (arr.length>1 ? arr[arr.length-1] : "");
      if ( str1.length>30 ) str1 = str1.substr(0,29) + "...";
      if ( str2.length>30 ) str2 = str2.substr(0,29) + "...";
      if ( str1.length + str2.length<25 ) { label = str1  + "/.../" + str2; } else { label = str1  + "<br/>.../" + str2;}
    }
    doc2.innerHTML = label;
    doc2.style.display=(onclick ? "block" : "none");
    doc2.href=onclick;
    }
  }
}

function datasetcontrol_click(checkboxname,action)
{
  var checkboxes = document.getElementsByName(checkboxname);
  var n = 0;
  var i = checkboxes.length-1;
  do { if ( checkboxes[i].checked ) n++; } while (i--);
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
  if ( arrC1.length==0 || arrC2.length==0 ) return;
  var id2;
  var i = arrC1.length-1;
  do
  {
    if ( id == "c."+arrC1[i] ) { id2 = "c."+arrC2[i]; break; }
  } while (i--);
  if (id2==null)
  {
    i = arrC2.length-1;
    do
    {
      if ( id == "c."+arrC2[i] ) { id2 = "c."+arrC1[i]; break; }
    } while (i--);
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
  var i = checkboxes.length-1;
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
  } while (i--);
}
function showpreview(id)
{
  var doc=document;
  var item="'.addslashes(cNE::GetIcon($oNE,true).'&nbsp; '.$oNE->id).'";
  var textmsg = doc.getElementById("textmsg_"+id); if ( !textmsg ) return;
  var dialog = doc.getElementById("notedialog"); if ( !dialog ) return;
  var status = doc.getElementById("status_"+id).outerHTML; if ( !status ) status="";
  dialog.innerHTML = "<p class=\"bold\">" + status + " " + textmsg.title + "</p>" + textmsg.innerHTML;
  $("#notedialog").dialog({title:item});
  $("#notedialog").dialog("open");
}
//-->
</script>
';

include 'qnm_inc_hd.php';

$strCommand = '<div class="pagecmd">'.PHP_EOL;
$strCommand .= '<ul>'.PHP_EOL;
if  (QNM_BACKBUTTON ) $strCommand .= '<li><a href="'.Href($oVIP->exiturl).'">'.QNM_BACKBUTTON.'</a></li>';
if ( $oNE->class=='c' || ($oNE->items+$oNE->conns)>0 || $oNE->posts>0 )
{
  $strCommand .= '<li><a href="'.Href('qnm_f_ne_edits.php').'?s='.$s.'&amp;nids='.$nid.'&amp;a=note&amp;e=e">'.L('Add_note').'</a></li>';
}
else
{
  $strCommand .= '<li><a href="qnm_form_newclass.php?s='.$s.'&amp;nid='.GetNid($oNE).'">'.L('Create_sub-items').'</a></li>';
}
$strCommand .= '</ul>'.PHP_EOL;
$strCommand .= '</div>'.PHP_EOL;

// Moderator action

if ( isset($strStaffMenu) ) echo $strStaffMenu;

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

// DISPLAY PAGER

echo '<table class="pagecmd_up"><tr class="pagecmd"><td class="pagecmd_up">',$strCommand,'</td><td id="pager_zt">&nbsp;</td></tr></table>',PHP_EOL;

// --------
// Parent
// --------

if ( isset($oPARENT) )
{
echo '<p>',$oNE->id,' '.L('in').' ',$oPARENT->Dump(true,''),'</p>
';
}

// --------
// FIELDS
// --------

echo '
<div class="frameelement">
<h1 class="elementid">',$oNE->Idstatus(false,'',false),'</h1>
<h1 class="elementheader">',cNE::Classname($oNE),'</h1>
';

// field and docs

echo '<table class="hidden">
<tr class="hidden">
<td class="hidden">
';
if ( $oNE->uid>0 )
{
echo '<p class="datasetcontrol">
<a class="datasetcontrol" href="'.Href('qnm_f_ne_edits.php').'?s='.$s.'&amp;nids='.$nid.'&amp;a=',($oNE->status==0 ? 'activate' : 'inactivate'),'&amp;e=e">',($oNE->status==0 ? $L['Activate'] : $L['Inactivate']),'</a> &middot;
<a class="datasetcontrol" href="'.Href('qnm_form_edit.php').'?nid='.$nid.'">',$L['Edit'],'</a> &middot;
<a class="datasetcontrol" href="'.Href('qnm_f_ne_edits.php').'?s='.$s.'&amp;nids='.$nid.'&amp;a=move&amp;e=e">',$L['Move'],'</a> &middot;
<a class="datasetcontrol" href="'.Href('qnm_f_ne_edits.php').'?s='.$s.'&amp;nids='.$nid.'&amp;a=delete&amp;e=e">',$L['Delete'],'</a> &middot;
<a class="datasetcontrol" href="'.Href('qnm_f_ne_edits.php').'?s='.$s.'&amp;nids='.$nid.'&amp;a=type&amp;e=e" title="',$L['Change_type'].', ...">',$L['More'],'...</a>
</p>
';
}
else
{
echo '&nbsp;';
}
echo '</td>
<td class="docviewer doctypeselector">
';
if ( $bMap ) echo '<p class="datasetcontrol"><a id="cmdDocviewer" class="datasetcontrol" href="javascript:void(0)" onclick="showViewer(\'docviewer\'); return false;">',L('Documents'),'</a><a id="cmdMapviewer" class="datasetcontrol" href="javascript:void(0)" onclick="showViewer(\'mapviewer\'); return false;">',L('Map'),'</a></p>';
echo '
</td>
</tr>
<tr class="hidden">
<td class="hidden">
';

// fields panel

if ( $oNE->uid>0 ) echo '<form method="post" action="',Href(),'?nid=',GetNid($oNE),'">',PHP_EOL;

echo '<table class="nefields">
';

echo '<tr class="nefields">
<td class="nefields">Id</td>
<td class="nefields">',cNE::GetIcon($oNE,true),' ',$oNE->id,($oNE->status==0 ? ' <span style="color:#ff0000">'.L('inactive').'</span>' : ''),($oNE->status<0 ? '<span style="color:#ff0000">'.L('deleted').'</span>' : ''),(empty($oNE->insertdate) ? '' : '<br/><span class="disabled">('.L('created').': '.qtDatestr($oNE->insertdate,'$','').')</span>'),'</td>
</tr>';

foreach(cNE::GetFields($oNE->class,'useredit') as $strField)
{
if ( $strField=='id' || $strField=='tags') continue;
echo '<tr class="nefields"><td class="nefields">',L(ucfirst($strField)),'</td><td class="nefields">',$oNE->$strField,'</td></tr>';
}

// map

$strCoord = '';
if ( $bMap )
{
  if ( !QTgempty($oNE->x) && !QTgempty($oNE->y) )
  {
  $y=(float)$oNE->y; $x=(float)$oNE->x;
  $strPname = QTconv($oNE->id,'U');
  $strPlink = '<a class="small" href="http://maps.google.com?q='.$y.','.$x.'+('.urlencode($oNE->id).')&amp;z='.$_SESSION[QT]['m_map_gzoom'].'" title="'.$L['map']['In_google'].'" target="_blank">[G]</a>';
  $strCoord = QTdd2dms($y).', '.QTdd2dms($x).' '.$L['Coord_latlon'].'<br/><span class="small disabled">DD '.round($y,8).','.round($x,8).'</span>';
  $strPinfo = '<span class="bold">Lat: '.QTdd2dms($y).' <br />Lon: '.QTdd2dms($x).'</span><br /><span class="small">DD: '.round($y,8).', '.round($x,8).'</span> '.$strPlink;
  $arrExtData[$oNE->uid] = new cMapPoint($y,$x,$strPname,$strPinfo,(isset($_SESSION[QT]['m_map']['s'.$oNE->section]) ? QTexplode(($_SESSION[QT]['m_map']['s'.$oNE->section])) : array()));
  }
  echo '<tr class="nefields"><td class="nefields">',$L['Coord'],'</td><td class="nefields">'.$strCoord.(isset($strPlink) ? '&nbsp;'.$strPlink : '&nbsp;').'</td></tr>',PHP_EOL;
}

// tags

if ( empty($oNE->tags) ) { $arrTags=array(); } else { $arrTags=explode(';',$oNE->tags); }
echo '<tr class="nefields">
<td class="nefields">',$L['Tags'],'</td>
<td class="nefields">
<p style="margin:0">';
foreach($arrTags as $strTag)
{
  if ( !empty($strTag) ) echo '<span class="tag" title="" onclick="document.getElementById(\'tag\').value=this.innerHTML;">',$strTag,'</span> ';
}
if ( count($arrTags)>1 ) echo '</p><p style="margin:5px 0 0 0">';

if ( $oNE->uid>0 )
{
  echo '<input type="hidden" name="s" value="',$s,'"/>';
  echo '<input type="text" class="small" size="20" id="tag" name="tag" maxlength="24" value=""/>';
  echo '<input type="submit" class="small" name="addtag" id="addtag" value="" title="',$L['Add'],'" onclick="if (document.getElementById(\'tag\').value==\'\') {return false;} else { return null;}"/>';
  echo '<input type="submit" class="small" name="deltag" id="deltag" value="" title="',$L['Delete_tags'],'" onclick="if (document.getElementById(\'tag\').value==\'\') {return false;} else { return null;}"/>';
  echo '<input type="button" id="edittags" title="'.L('Edit').'" onclick="showEdittags(); return false;" value="&raquo;" />';
}
echo '</p>
</td>
</tr>
</table>
';
if ( $oNE->uid>0 ) echo '</form>',PHP_EOL;

echo '</td>
<td class="docviewer">
';

// documents panel

if ( $oNE->class!='c' && $oNE->uid>0 )
{
  echo '<div id="docviewer" class="docviewer">',PHP_EOL;
  echo '<p style="margin:0;text-align:right">',PHP_EOL;
  $iPreview = 0;
  if ( $oNE->docs>0 )
  {
    // Get documents in this topic.
    // Attention, the document-id is docfile

    $arrDocs = array();
      $oDB->Query('SELECT doctype,docname,docfile,docpath,docdate FROM '.TABDOC.' WHERE id='.GetUid($nid).' ORDER by docname ASC');
      while($row=$oDB->Getrow()) { $arrDocs[]=$row; if ( count($arrDocs)>20 ) break; } // keys 0..9

    foreach($arrDocs as $i=>$arrDoc)
    {
      $arrDocs[$i]['doctype'] = $arrDoc['doctype'];
      $arrDocs[$i]['docpath'] = $arrDoc['docpath'];
      $arrDocs[$i]['docfile'] = $arrDoc['docfile'];
      $arrDocs[$i]['docname'] = (empty($arrDoc['docname']) ? $arrDoc['docfile'] : $arrDoc['docname']);
      $arrDocs[$i]['image'] = $_SESSION[QT]['skin_dir'].'/qnm_doc_dlc.png';
      $arrDocs[$i]['thumb'] = $_SESSION[QT]['skin_dir'].'/qnm_doc_dlc.png';
      switch($arrDoc['doctype'])
      {
        case 'txt':
        case 'pdf':
        case 'url':
          $arrDocs[$i]['image'] = $_SESSION[QT]['skin_dir'].'/qnm_doc_'.$arrDoc['doctype'].'.png';
          $arrDocs[$i]['thumb'] = $_SESSION[QT]['skin_dir'].'/qnm_doc_'.$arrDoc['doctype'].'.png';
          break;
          $arrDocs[$i]['image'] = $_SESSION[QT]['skin_dir'].'/qnm_doc_pdf.png';
          $arrDocs[$i]['thumb'] = $_SESSION[QT]['skin_dir'].'/qnm_doc_pdf.png';
          break;
        case 'urlimg':
          $arrDocs[$i]['image'] = $arrDocs[$i]['docpath'].$arrDocs[$i]['docfile'];
          $arrDocs[$i]['thumb'] = $arrDocs[$i]['image'];
          break;
        case 'img':
          $arrDocs[$i]['image'] = $arrDocs[$i]['docpath'].$arrDocs[$i]['docfile'];
          if ( file_exists($arrDocs[$i]['image']) )
          {
            if ( $i<5 ) $iPreview=$i;
            $arrDocs[$i]['thumb'] = $arrDoc['docpath'].(file_exists($arrDoc['docpath'].'thumb_'.$arrDoc['docfile']) ? 'thumb_' : '').$arrDocs[$i]['docfile'];
          }
          else
          {
            $arrDocs[$i]['image'] = $_SESSION[QT]['skin_dir'].'/qnm_doc_err.png';
            $arrDocs[$i]['thumb'] = $_SESSION[QT]['skin_dir'].'/qnm_doc_err.png';
          }
          break;
      }

      // show top 5 images button (other are hidden but images are accessible by the viewer)
      if ( $i<5 )
      {
      echo '<a href="'.$arrDocs[$i]['image'].'" rel="prettyPhoto[]" title="'.(isset($arrDocs[$i]['docname']) ? $arrDocs[$i]['docname'] : '').'"><img class="button" src="'.$_SESSION[QT]['skin_dir'].'/ico_attachment.gif" title="'.(isset($arrDocs[$i]['docname']) ? $arrDocs[$i]['docname'] : '').' ('.($i+1).'/'.count($arrDocs).')" alt="'.$oNE->id.' '.$L['Documents'].'" onmouseover="preview(\''.$arrDocs[$i]['thumb'].'\',\''.$arrDocs[$i]['docname'].'\',\''.($arrDocs[$i]['doctype']=='url' || $arrDocs[$i]['doctype']=='pdf' || $arrDocs[$i]['doctype']=='txt' ? $arrDocs[$i]['docpath'].$arrDocs[$i]['docfile'] : '').'\');"/></a>';
      }
      else
      {
      echo '<a style="display:none" href="'.$arrDocs[$i]['image'].'" rel="prettyPhoto[]" title="'.(isset($arrDocs[$i]['docname']) ? $arrDocs[$i]['docname'] : '').' ('.($i+1).'/'.count($arrDocs).'"></a>';
      }
    }

  }
  echo '<a href="',Href('qnm_form_docs.php'),'?nid=',$nid,'"><img class="button" src="'.$_SESSION[QT]['skin_dir'].'/ico_doc_add.png" alt="D" title="'.L('Documents').'..."/></a>';
  echo '</p>',PHP_EOL;
  if ( isset($arrDocs[$iPreview]) )
  {
  echo '<img id="imgpreview" class="docviewer" src="'.$arrDocs[$iPreview]['thumb'].'" alt="doc" style="vertical-align:middle" onerror="this.src=\''.$_SESSION[QT]['skin_dir'].'/qnm_doc_err.png\';"/>';
  echo '<p style="margin:0"><a id="imgpreviewclick" class="small" title="'.L('Open').'..." style="display:'.($arrDocs[$iPreview]['doctype']==='url' || $arrDocs[$iPreview]['doctype']==='pdf' || $arrDocs[$iPreview]['doctype']==='txt' ? 'block' : 'none').'" href="'.$arrDocs[$iPreview]['docfile'].'">'.(empty($arrDocs[$iPreview]['docname']) ? L('Open').'...' : $arrDocs[$iPreview]['docname']).'</a></p>';
  }
  echo '</div>',PHP_EOL;

  // MAP MODULE, Show map

  if ( $bMap )
  {
    if ( count($arrExtData)>0 )
    {
    echo '<div id="mapviewer" class="mapviewer" style="display:none">',PHP_EOL;
    echo '<div id="map_canvas"></div>',PHP_EOL;
    echo '</div>';
    }
    else
    {
    echo '<div id="mapviewer" class="docviewer" style="display:none">',PHP_EOL;
    echo '<p class="gmap">'.$L['map']['E_noposition'].'</p>',PHP_EOL;
    echo '</div>';
    }
  }

}
else
{
  echo '&nbsp;';
}
echo '
</td>
</tr>
</table>
';

echo '</div>
';

echo '<p class="small" style="margin:0 5px 7px 0;text-align:right">';
if ( $_SESSION[QT]['viewmode']=='C' )
{
  echo '<span class="disabled">',L('Schematic_view'),'</span> | <a class="small" href="'.Href().'?nid='.$nid.'&amp;view=N">',L('Detailed_lists'),'</a>';
}
else
{
  echo '<a class="small" href="'.Href().'?nid='.$nid.'&amp;view=C">',L('Schematic_view'),'</a> | <span class="disabled">',L('Detailed_lists'),'</span>';
}
echo '</p>',PHP_EOL;

// --------
// Relations (can exist for element or for connector)
// --------
if ( $_SESSION[QT]['viewmode']=='N' ) {
// --------

echo '
<div class="framerelations">
<div id="assetrelations" class="childheader">
<h1>',$L['Relations'],' (',count($arrR),'r)</h1>
</div>
';

echo '
<p class="datasetcontrol">
<a class="datasetcontrol" href="',Href('qnm_form_link_c.php'),'?s=',$s,'&amp;nid=',GetNid($oNE),'" title="'.L('cmd_Edit_links').'">',L('Edit'),'</a>
</p>
';

if ( count($arrR)>0 )
{
  echo '<table class="hidden">';
  foreach($arrR as $key=>$oSubNL)
  {
    echo '<tr class="hidden rowlight">';
    echo '<td class="hiddenlist">',$oNE->id,' ',$oSubNL->NLDump(true,''),'</td><td class="hiddenlist" style="width:120px;text-align:right">';

    // Patching edit is enabled when both (element and linked element) have connectors.
    if ( $oNE->conns>0 && $oSubNL->ne2->conns>0 )
    {
      echo ' <a class="small" href="qnm_form_patch.php?lid='.GetNid($oNE).'&amp;lid2='.GetNid($oSubNL->ne2).'">',L('Edit_patching'),'</a>';
    }
    echo '&nbsp;</td></tr>';
  }
  echo '</table>';
}

echo '
</div>
';

// --------
// Embeded items or connectors (exist only for element)
// --------
if ( $oNE->class!='c' ) {
// --------

echo '
<div class="framechilds">
<div id="assetchilds" class="childheader">
<h1>',$L['Contained_items'],' (',count($arrE),'e)</h1>
</div>
';

echo '
<p class="datasetcontrol">
<a class="datasetcontrol" href="',Href('qnm_form_link_e.php'),'?fs=',$s,'&amp;nid=',GetNid($oNE),'" title="'.L('cmd_Edit_content').'">',L('Edit'),'</a> &middot;
<a class="datasetcontrol" href="',Href('qnm_form_newclass.php'),'?s=',$s,'&amp;nid=',GetNid($oNE),'">',L('Create_sub-items'),'</a> &middot;
<a class="datasetcontrol" href="',Href('qnm_form_new.php'),'?s=',$s,'&amp;nid=',GetNid($oNE),'&amp;a=c">',L('Create_connectors'),'</a>
</p>
';

if ( count($arrE)>0 )
{
  echo '  <div style="max-height:400px;overflow:auto;">',PHP_EOL;
  echo '  <table class="hidden">',PHP_EOL;
  foreach($arrE as $key=>$oSubNE)
  {
    echo '<tr class="hidden rowlight">';
    echo '<td class="hiddenlist">',AsImg($_SESSION[QT]['skin_dir'].'/ico_link_c.gif','-','','i_sub'),$oSubNE->Dump(true,''),'</td>';

    $strLINKED='';
    if ( $oSubNE->class=='c' )
    {
      // linked connector
      if ( isset($arrCC[$oSubNE->uid]) )
      {
        $ccNL = $arrCC[$oSubNE->uid];
        $oLINKED = $ccNL->ne1;
        $oLINKEDPARENT = $ccNL->ne2;

        // parent of the linked connector
        $strLINKED = cNE::GetIcon($oLINKED,true).' '.$oLINKED->Idstatus().' '.$oLINKED->type.sprintf(' <span class="small">[%s]</span>',(empty($oLINKED->address) ? '-' : $oLINKED->address)).' '.L('in').' '.cNE::GetIcon($oLINKEDPARENT,true).' '.$oLINKEDPARENT->Idstatus();
      }
    }
    echo '<td class="hiddenlist" style="width:30px; text-align:center">'.(empty($strLINKED) ? '&nbsp;' : cNL::NLGetIcon($ccNL->ldirection)).'</td>';
    echo '<td class="hiddenlist">'.($oSubNE->class=='c' && empty($strLINKED) ? '<span class="disabled">('.L('free').')</span>' : $strLINKED).'</td></tr>';
  }
  echo '</table></div>';
}

echo '
</div>
';

// --------
}
}
// --------

// --------
// Graphic view
if ( $_SESSION[QT]['viewmode']=='C' ) {
// --------

if (isset($_GET['boxsize'])) $_SESSION[QT]['boxsize'] = (int)$_GET['boxsize'];
if ( !isset($_SESSION[QT]['boxsize']) ) $_SESSION[QT]['boxsize']=1;
if ( $_SESSION[QT]['boxsize']!==2 ) $_SESSION[QT]['boxsize']=1;

// Connectors that can be highlighted

$arrJava1 = array_keys($arrCC);
$arrJava2 = array_keys(cNL::GetNEs($arrCC,1,false));

// Display

echo '<div class="framechilds">
<div id="assetchilds" class="childheader"><h1>',L('Contained_items'),' ',L('and'),' ',L('Relations'),'</h1></div>
';

if ( $oNE->uid>0 )
{
echo '
<p class="datasetcontrol">
<a class="datasetcontrol" href="',Href('qnm_form_link_c.php'),'?s=',$s,'&amp;nid=',$nid,'" title="'.L('cmd_Edit_links').'">',L('Edit_relations'),'</a> &middot; ';
if ( $_SESSION[QT]['boxsize']==2 )
{
echo '<a class="datasetcontrol" href="'.Href().'?nid='.$nid.'&amp;view=C&amp;boxsize=1">',L('View_compact'),'</a> &middot; <span class="disabled">'.L('View_large').'</span>';
}
else
{
echo '<span class="disabled">'.L('View_compact').'</span> &middot; <a class="datasetcontrol" href="'.Href().'?nid='.$nid.'&amp;view=C&amp;boxsize=2">',L('View_large'),'</a>';
}
echo '</p>
';
}

echo '<table>
<tr>
<td style="vertical-align:top">
<div class="ne_box_',$_SESSION[QT]['boxsize'],'">
<p style="margin:0 0 5px 0;padding:0 0 5px 0;border-bottom:1px solid #aaaaaa">',cNE::GetIcon($oNE,true),' ',$oNE->Idstatus(false),$oNE->DumpLinks(),$oNE->DumpNotes(),'</p>',VisualSubNE($arrE,array_keys($arrCC),$_SESSION[QT]['boxsize']),'
<p style="margin:0;text-align:right"><a class="datasetcontrol" href="',Href('qnm_form_link_e.php'),'?fs=',$s,'&amp;nid=',$nid,'" title="'.L('cmd_Edit_links').'">',L('edit'),'</a></p>';
echo '
</div>
',( isset($oPARENT) ? '<p style="margin:0">'.L('in').' '.cNE::GetIcon($oPARENT,true).' '.$oPARENT->Idstatus().'</p>' : ''),'
</td>
<td style="vertical-align:top">
';

$i=0;
foreach($arrR as $key=>$oNL)
{
  $oNE2 = $oNL->ne2;
  $arrE = array();
  $arrCC = array();
  if ( $oNE2->items + $oNE2->conns >0 ) $arrE = $oNE2->GetEmbeded();
  if ( $oNE2->conns>0 && $oNE2->links>0 )
  {
    $arrCC = $oNE2->GetCC(false); // Search connected connectors (don't search parents of the connected connectors)
    // additional connections highlight (if not het set)
    if ( count($arrCC)>0 ) {
    foreach($arrCC as $key=>$o) {
      if ( in_array($key,$arrJava1) ) continue;
      if ( in_array($key,$arrJava2) ) continue;
      $arrJava1[]=$key;
      $arrJava2[]=$o->ne1->uid;
    }}
  }
  echo '<table><tr>';
  echo '<td style="vertical-align:top;width:30px;text-align:center">',cNL::NLGetIcon($oNL->ldirection),'</td>';
  if ( $i<10 )
  {
    echo '<td style="vertical-align:top;width:200px">';
    echo '<div class="ne_box_',$_SESSION[QT]['boxsize'],'"><p style="margin:0 0 5px 0;padding:0 0 5px 0;border-bottom:1px solid #aaaaaa">',cNE::GetIcon($oNE2,true),' ',$oNE2->Idstatus(),$oNE2->DumpLinks(),$oNE2->DumpNotes(),'</p>',VisualSubNE($arrE,array_keys($arrCC),$_SESSION[QT]['boxsize']);
    echo '<p style="margin:0;text-align:right"><a class="datasetcontrol" href="',Href('qnm_form_link_e.php'),'?fs=',$s,'&amp;nid=',GetNid($oNE2),'">',L('edit'),'</a></p>';
    echo '</div></td>';
    echo '<td style="vertical-align:top">&nbsp;';
    // Patching edit is enabled when both (element and linked element) have connectors.
    if ( $oNE->conns>0 && $oNE2->conns>0 )
    {
      echo '<a class="datasetcontrol" href="',Href('qnm_form_patch.php'),'?lid='.$nid.'&amp;lid2='.GetNid($oNE2).'">',L('Edit_patching'),'</a>';
    }
    echo '</td></tr></table>';
  }
  else
  {
    echo '<td style="vertical-align:top;width:200px"><div class="ne_box_',$_SESSION[QT]['boxsize'],'"><p style="margin:0 0 5px 0;padding:0;">'.L('More').'... <span class="disabled">('.L('relation',count($arrR)-10).')</span></p></div></td><td>&nbsp;</td></tr></table>';
    break;
  }
  $i++;
}
echo '</td>
<td style="vertical-align:top">&nbsp;</td>
</tr>
</table>
</div>
<script type="text/javascript">
<!--
var arrC1 = ['.implode(',', array_unique($arrJava1) ).'];
var arrC2 = ['.implode(',', array_unique($arrJava2) ).'];
//-->
</script>
';

// --------
} // end graphic view
// --------

// --------
if ( $oNE->class!='c' && $oNE->uid>0 ) { // NOTES
// --------

$intVisibleNotes = $oNE->CountPosts('status>=0');
// Attention: $oNE->posts are active notes only
// $intVisibleNotes are active[1] + closed[0] notes
$str = '0';
$arrNotes = array();
if ( $intVisibleNotes>0 )
{
  $str = $oNE->posts.'<span class="small"> '.L('in_process_notes').' /'.$intVisibleNotes.'</span>';
  $arrNotes = $oNE->GetPosts('',QNM_SHOW_ITEM_NOTES+1,($nip==0 ? 'issuedate DESC' : 'status DESC, issuedate DESC'));
}
echo '
<div class="framenotes">
<div id="assetnotes" class="noteheader"><h1>',L('Messages'),' (',$str,')</h1></div>
';

echo '<table class="hidden">',PHP_EOL;
echo '<tr class="hidden">',PHP_EOL;
echo '<td class="hidden small" style="padding:8px 5px">',PHP_EOL;
$strCmd = '';
if ( $intVisibleNotes>0 )
{
$strCmd .= '<img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:middle;margin:0 5px 0 8px" alt="|" />';
$strCmd .= '<a class="datasetcontrol" onclick="datasetcontrol_click(\'cb_n1[]\',\'inactivate\'); return false;" href="#">'.L('Close').'</a> &middot; ';
$strCmd .= '<a class="datasetcontrol" onclick="datasetcontrol_click(\'cb_n1[]\',\'activate\'); return false;" href="#">'.L('Set_in_process').'</a> &middot; ';
$strCmd .= '<a class="datasetcontrol" onclick="datasetcontrol_click(\'cb_n1[]\',\'delete\'); return false;" href="#">'.L('Delete').'</a>';
}
if ( $intVisibleNotes>2 )
{
$strCmd .= '&nbsp; | &nbsp;'.L('Select').' &nbsp;<a class="datasetcontrol" onclick="doselect(\'cb_n1[]\',\'all\'); return false;" href="#">'.L('all').'</a>';
$strCmd .= ' &nbsp;<a class="datasetcontrol" onclick="doselect(\'cb_n1[]\',\'none\'); return false;" href="#">'.L('none').'</a>';
$strCmd .= ' &nbsp;<a class="datasetcontrol" onclick="doselect(\'cb_n1[]\',\'inprocess\'); return false;" href="#">'.L('in_process_notes').'</a>';
$strCmd .= ' &nbsp;<a class="datasetcontrol" onclick="doselect(\'cb_n1[]\',\'close\'); return false;" href="#">'.L('closed_notes').'</a>';
}

echo $strCmd.'</td>
<td class="hidden" style="padding:8px 2px;width:100px"><a href="',Href('qnm_f_ne_edits.php'),'?s='.$s.'&amp;nids='.$nid.'&amp;a=note&amp;e=e">'.L('Add_note').'</a></td>
</tr>
</table>
';

if ( $intVisibleNotes>0 )
{
  $pager = '';
  if ( count($arrNotes)>QNM_SHOW_ITEM_NOTES ) { $pager='<a href="qnm_item_notes.php?nid='.$nid.'">'.L('Show_more_notes').'...</a>'; array_pop($arrNotes); }

  echo '<form id="form_n1" method="post" action="',Href(),'?nid='.$nid.'#notes"><input id="form_n1_a" type="hidden" name="a" value=""/><input type="hidden" name="nid" value="',$nid,'"/>',PHP_EOL;
  echo '<table id="notes" class="notes">',PHP_EOL;
  foreach($arrNotes as $linkid=>$arrLink)
  {
    $oPost = new cPost($arrLink);
    $strIssuedate = ( empty($oPost->issuedate) ? '&nbsp;' : QTdatestr($oPost->issuedate,'$','$',true) );
    echo '<tr class="notes" id="tr_n_',$oPost->id,'_',$oPost->status,'">',PHP_EOL;
    echo '<td class="notes tdcheckbox"><input type="checkbox" name="cb_n1[]" value="'.$oPost->id.'" id="n_'.$oPost->id.'_'.$oPost->status.'"/></td>',PHP_EOL;
    echo '<td class="notes tdicon">',$oPost->GetIcon('','','','i_note','status_'.$oPost->id),'</td>',PHP_EOL;
    echo '<td class="notes tdissuedate',($oPost->status==0 ? ' disabled' : ''),'">',$strIssuedate,( empty($arrLink['userid']) ? '&nbsp;' : '<br/>by <a href="'.Href('qnm_user.php').'?id='.$arrLink['userid'].'">'.$arrLink['username'].'</a>' ),'</td>',PHP_EOL;
    echo '<td class="notes tdtextmsg',($oPost->status==0 ? ' disabled' : ''),'">',( $oPost->textmsg==='' ? '&nbsp;' : '<div class="scroller" id="textmsg_'.$oPost->id.'" title="'.$strIssuedate.' '.L('by').' '.$oPost->username.'">'.$oPost->textmsg.'<div>' ),'</td>',PHP_EOL;
    echo '<td class="notes tdaction">';
    echo '<img class="scrollerviewer" src="'.$_SESSION[QT]['skin_dir'].'/preview.png" alt="&laquo;" title="'.L('Preview').'" onclick="showpreview('.$oPost->id.');"/>';
    if ( $oVIP->user->IsStaff() || $oVIP->user->id==$oPost->userid ) echo '<a href="'.Href('qnm_form_note.php').'?id='.$oPost->id.'&amp;pid='.$nid.'"><img class="editviewer" src="'.$_SESSION[QT]['skin_dir'].'/edit.png" alt="'.L('Edit').'" title="'.L('Edit').'"/></a>';
    echo '</td>',PHP_EOL;
    echo '</tr>',PHP_EOL;
  }
  echo '</table>',PHP_EOL;
  echo '</form>',PHP_EOL;
  if ( count($arrNotes)>5 )
  {
  echo '<table class="hidden">',PHP_EOL;
  echo '<tr class="hidden">',PHP_EOL;
  echo '<td class="hidden small" style="padding:8px">',PHP_EOL;
  echo str_replace('selection_up.gif','selection_down.gif',$strCmd).'</td>',PHP_EOL;
  echo '<td class="hidden" style="padding:8px 2px;width:100px"><a href="',Href('qnm_f_ne_edits.php'),'?s=',$s,'&amp;nids=',$nid,'&amp;a=note&amp;e=e">'.L('Add_note').'</a></td>'.PHP_EOL;
  echo '</tr>',PHP_EOL;
  echo '</table>',PHP_EOL;
  if ( !empty($pager) ) echo '<p>'.$pager.'</p>';
  }

}

echo '
</div>
';

// --------
} // end note
// --------

if ( $oNE->uid<1 && GetUid($nid)!=0)
{
  echo '<p>Unknown element ['.$nid.'].<br/>It\'s possible that this element is still used as parent or child.<br/><a href="qnm_change.php?a=unlink&amp;v='.$nid.'&amp;s='.$s.'">Remove this element from relations</a></p>';
}

// --------
// HTML END
// --------

echo '
<div id="notedialog"></div>
';

// toggle a note if requested
if ( !empty($note) )
{
echo '
<script type="text/javascript">
<!--
var cb = document.getElementById("n_'.$note.'_1");
if ( !cb ) cb = document.getElementById("n_'.$note.'_0");
if ( cb )
{
qtCheckboxToggle(cb.id);
qtHighlight("tr_"+cb.id,cb.checked);
}
//-->
</script>
';
}

// MAP MODULE

if ( $bMap )
{
  $gmap_shadow = false;
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_map_gsymbol']) )
  {
    $arr = explode(' ',$_SESSION[QT]['m_map_gsymbol']);
    $gmap_symbol=$arr[0];
    if ( isset($arr[1]) ) $gmap_shadow=$arr[1];
  }

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_map_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_map_gcenter']));

  // First item is the user's location and symbol
  if ( isset($arrExtData[$oNE->uid]) )
  {
    // symbol by role
    $oMapPoint = $arrExtData[$oNE->uid];
    if ( !empty($oMapPoint->icon) ) $gmap_symbol = $oMapPoint->icon;
    if ( !empty($oMapPoint->shadow) ) $gmap_shadow = $oMapPoint->shadow;

    // center on user
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    }
  }

  // update center
  $_SESSION[QT]['m_map_gcenter'] = $y.','.$x;

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();
  $gmap_markers[] = QTgmapMarker($_SESSION[QT]['m_map_gcenter'],false,$gmap_symbol,$row['name'],'',$gmap_shadow);
  include 'qnmm_map_load.php';
}

if ( $bMap )
{
$oHtml->scripts_end[] = '<script type="text/javascript">
<!--
function showViewer(id)
{
  var doc = document;
  if ( doc.getElementById("mapviewer") ) doc.getElementById("mapviewer").style.display="none";
  if ( doc.getElementById("docviewer") ) doc.getElementById("docviewer").style.display="none";
  if ( id=="mapviewer" )
  {
    doc.getElementById("cmdMapviewer").style.backgroundColor="#ffffff";
    doc.getElementById("cmdDocviewer").style.backgroundColor="#eeeeee";
    doc.getElementById(id).style.display="block";
    google.maps.event.trigger(map,"resize");
    gmapPan("'.$_SESSION[QT]['m_map_gcenter'].'");
  }
  if ( id=="docviewer" )
  {
    doc.getElementById("cmdMapviewer").style.backgroundColor="#eeeeee";
    doc.getElementById("cmdDocviewer").style.backgroundColor="#ffffff";
    doc.getElementById(id).style.display="inline-block";
  }
}
//-->
</script>
';
}

include 'qnm_inc_ft.php';