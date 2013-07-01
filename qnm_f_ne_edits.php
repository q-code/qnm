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
include 'bin/qnm_fn_sql.php';

// --------
// INITIALISE
// --------

$a = 'more'; // action (mandatory); more means user select later
$e = '';     // exit url (can be 'e' to return to the element page)
$s = -1;
$nids = '';  // confirmed nids (as a comma-separated string)
$sids = '';  // [optional] list of impacted section ids
$strTxt = ''; // textmessage (used when security rules refuse the $_POST['note']

QThttpvar('a nids e s sids','str str str int str');
if ( strlen($e)>1 ) { $e = str_replace('&','&amp;',$e); $e = str_replace('&amp;amp;','&amp;',$e); } // security for web serveur posting &amp; as &

// check arguments
if ( !in_array($a,array('activate','inactivate','note','delete','type','address','descr','move','close','more','deleteclosed','addtags','deltags')) ) die('Missing parameters A');

$oVIP->selfurl = 'qnm_f_ne_edits.php';
$oVIP->selfname = L('Edit');
$oVIP->exiturl = ($e=='e' ? 'qnm_item.php?nid='.$nids : $e);

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check and format nids/sids to process

  if ( empty($nids) ) die('Missing id to process');
  $arrNids = explode(',',$nids); // list of [class][uid]
  $arrSids = array($s);
  if ( $sids!=='' ) $arrSids = explode(',',$sids);
  $arrSids = array_unique($arrSids);
  $arrSids = array_map('intval',$arrSids); // list of [int] section id
  $arrUids = ExtractELuids($arrNids);

  // initialise object

  $oNE = new cNE(); // empty object

  // process action

  switch($a)
  {
  case 'activate':
  case 'inactivate':
    global $oDB;
    $arrClass = array();
    $arrClass['e'] = ExtractUids($arrNids,'e');
    $arrClass['l'] = ExtractUids($arrNids,'l');
    $arrClass['c'] = ExtractUids($arrNids,'c');

    foreach ($arrClass as $key=>$arr) {
    if ( count($arr)>0 ) {

      $oDB->Query( 'UPDATE '.($key=='c' ? TABNC : TABNE).' SET status='.($a=='activate' ? 1 : 0).' WHERE class="'.$key.'" AND uid IN ('.implode(',',$arr).')' );
      if ( isset($_POST['all']) && $key!='c' )
      {
        $oDB->Query( 'UPDATE '.TABNE.' SET status='.($a=='activate' ? 1 : 0).' WHERE pid IN ('.implode(',',$arr).')' );
        $oDB->Query( 'UPDATE '.TABNC.' SET status='.($a=='activate' ? 1 : 0).' WHERE pid IN ('.implode(',',$arr).')' );
      }

    }}
    // Updates sections stats (no system stats)
    foreach ($arrSids as $i) {
    if ( $i>=0 ) {
      $oSEC = new cSection($i);
      $oSEC->MChange('stats','itemsZ',cSection::CountItems($i,'itemsZ')); // notes and notesZ are stored in the stat (not the notesA)
    }}
    // Update system stats
    unset($_SESSION[QT]['sys_stat_itemsZ']);
    // Exit
    $_SESSION['pagedialog']='O|'.L('S_update');
    $oHtml->Redirect($oVIP->exiturl);
    break;
  case 'note':
    if ( !isset($_POST['note']) ) $error='Message error';
    if ( empty($error) ) $error = cPost::CheckInput($_POST['note'],true);
    // add notes
    if ( empty($error) )
    {
      $i=0;
      foreach($arrNids as $str)
      {
	  if ( $s<0 && isset($arrSids[$i]) )  { $oNE->section = (int)$arrSids[$i]; } else { $oNE->section = $s; }
	  $oNE->IdDecode($str); // read [class] and [uid] from $str
	  $oNE->AddNote($_POST['note'],true,false); // add note without users's stats change (stats are updated after)
	  if ( !empty($oNE->error) ) { $error .= 'Problem while adding note in ['.$str.']: '.$oNE->error.'</br></br>'; $oNE->error=''; }
	  $i++;
	  $_SESSION[QT]['sys_stat_notes']++; // direct system stats update
	  $_SESSION[QT]['sys_stat_notesA']++;
      }
      // udpate user's stats
      $oDB->Query( 'UPDATE '.TABUSER.' SET numpost=numpost+'.$i.' WHERE id='.$oVIP->user->id );

      // update user's last post
      $_SESSION['qnm_usr_lastpost']=time();
      // update sections stats
      foreach ($arrSids as $i)
      {
        if ( $i>=0 )
        {
        $oSEC = new cSection($i);
        $arr = $oSEC->MRead('stats'); unset($arr['notes']); unset($arr['notesA']);
        $oSEC->UpdateStats($arr);
        }
      }
      // Exit
      if ( !empty($error) ) $oHtml->PageBox(L('Add_note'),$error,null,0,'90%');
      $_SESSION['pagedialog']='O|'.L('S_insert');
      $oHtml->Redirect($oVIP->exiturl.'#notes');
    }
    break;
  case 'delete':
    foreach($arrNids as $str)
    {
    $oNE->IdDecode($str); // read [class] and [uid] from $str
    $oNE->Delete(true,false); // update section stats after
    }
    // update sections stats
    foreach ($arrSids as $i)
    {
      if ( $i>=0 )
      {
      $oSEC = new cSection(); $oSEC->uid=(int)$i; $oSEC->UpdateStats(); // updates section
      }
    }
    // Update system stats
    unset($_SESSION[QT]['sys_stat_itemsZ'],$_SESSION[QT]['sys_stat_items'],$_SESSION[QT]['sys_stat_notes'],$_SESSION[QT]['sys_stat_notesA']);
    // Exit
    $_SESSION['pagedialog']='O|'.L('S_delete');
    if ( count($arrSids)==1 ) $s=(int)$arrSids[0];
    $oHtml->Redirect(Href('qnm_items.php').'?s='.$s);
    break;
  case 'type':
  case 'address':
  case 'descr':
    $_POST['value_'.$a] = trim($_POST['value_'.$a]);
    if ( empty($_POST['value_'.$a]) ) $oHtml->PageBox(NULL,'Invalid value. Unable to change '.$a.'.',$_SESSION[QT]['skin_dir'],2);
    foreach($arrNids as $str)
    {
    $oNE->IdDecode($str); // read [class] and [uid] from $str
    $oNE->UpdateField($a,$_POST['value_'.$a]);
    }
    $_SESSION['pagedialog']='O|'.L('S_update');
    $oHtml->Redirect($oVIP->exiturl);
    break;
  case 'move':
    if ( $_POST['move']=='-' ) $oHtml->PageBox(NULL,'No section selected. Unable to change section.',$_SESSION[QT]['skin_dir'],2);
    $oNE = new cNE();
    foreach($arrNids as $str)
    {
    $oNE->IdDecode($str); // read [class] and [uid] from $str
    $oNE->Move(intval($_POST['move']));
    }
    // Update sections stats: source and destination (no need to update system stats)
    foreach ($arrSids as $i)
    {
    $oSEC = new cSection(); $oSEC->uid=(int)$i; $oSEC->UpdateStats(); // updates section & system stats
    }
    $voidSEC = new cSection(); $voidSEC->uid=$_POST['move']; $voidSEC->UpdateStats();
    $_SESSION['pagedialog']='O|'.L('S_update');
    $oHtml->Redirect($oVIP->exiturl);
    break;
  case 'close':
    global $oDB;
    $arrClass = array();
    $arrClass['e'] = ExtractUids($arrNids,'e');
    $arrClass['l'] = ExtractUids($arrNids,'l');
    if ( !empty($arrClass['e']) )
    {
    $oDB->Query( 'UPDATE '.TABPOST.' SET status=0 WHERE pclass="e" AND pid IN ('.implode(',',$arrClass['e']).')' );
    $oDB->Query( 'UPDATE '.TABNE.' SET posts=0 WHERE class="e" AND uid IN ('.implode(',',$arrClass['e']).')' );
    }
    if ( !empty($arrClass['l']) )
    {
    $oDB->Query( 'UPDATE '.TABPOST.' SET status=0 WHERE pclass="l" AND pid IN ('.implode(',',$arrClass['l']).')' );
    $oDB->Query( 'UPDATE '.TABNE.' SET posts=0 WHERE class="l" AND uid IN ('.implode(',',$arrClass['l']).')' );
    }
    // update sections stats
    foreach ($arrSids as $i)
    {
    $oSEC = new cSection((int)$i);
    $arr = $oSEC->MRead('stats'); unset($arr['notes']); unset($arr['notesZ']);
    $oSEC->UpdateStats($arr);
    }
    // Update system stats
    unset($_SESSION[QT]['sys_stat_notesA']);
    // Exit
    $_SESSION['pagedialog']='O|'.L('S_update');
    $oHtml->Redirect($oVIP->exiturl);
    break;
  case 'deleteclosed':
    global $oDB;
    $arrClass = array();
    $arrClass['e'] = ExtractUids($arrNids,'e');
    $arrClass['l'] = ExtractUids($arrNids,'l');
    if ( !empty($arrClass['e']) ) $oDB->Query( 'DELETE FROM '.TABPOST.' WHERE status=0 AND pclass="e" AND pid IN ('.implode(',',$arrClass['e']).')' );
    if ( !empty($arrClass['l']) ) $oDB->Query( 'DELETE FROM '.TABPOST.' WHERE status=0 AND pclass="l" AND pid IN ('.implode(',',$arrClass['l']).')' );
    // update sections stats
    foreach ($arrSids as $i)
    {
    $oSEC = new cSection((int)$i);
    $arr = $oSEC->MRead('stats'); unset($arr['notesZ']);
    $oSEC->UpdateStats($arr);
    }
    // Update system stats
    unset($_SESSION[QT]['sys_stat_itemsZ'],$_SESSION[QT]['sys_stat_items'],$_SESSION[QT]['sys_stat_notes'],$_SESSION[QT]['sys_stat_notesA']);
    // Exit
    $_SESSION['pagedialog']='O|'.L('S_delete');
    $oHtml->Redirect($oVIP->exiturl);
    break;
  case 'addtags':
    if ( empty($_POST['addtags']) ) $oHtml->PageBox(NULL,'No tags. Unable to change tags.',$_SESSION[QT]['skin_dir'],2);
    $strTags = QTconv($_POST['addtags']);
    foreach($arrNids as $str)
    {
    $oNE = new cNE(GetUid($str));
    //$oNE->IdDecode($str); // read [class] and [uid] from $str
    $oNE->TagsAdd($strTags,null); // add tags without section stat update
    }
    foreach ($arrSids as $i)
    {
    $oSEC = new cSection((int)$i);
    $oSEC->MChange('stats','tags',cSection::CountItems($i,'tags'));
    }
    $_SESSION['pagedialog']='O|'.L('S_insert');
    $oHtml->Redirect($oVIP->exiturl);
    break;
  case 'deltags':
    if ( empty($_POST['deltags']) ) $oHtml->PageBox(NULL,'No tags. Unable to change tags.',$_SESSION[QT]['skin_dir'],2);
    $strTags = QTconv($_POST['deltags']);
    foreach($arrNids as $str)
    {
    $oNE = new cNE(GetUid($str)); //$oNE->IdDecode($str); // read [class] and [uid] from $str
    $oNE->TagsDel($strTags,null); // delete tags without section stat update
    }
    foreach ($arrSids as $i)
    {
    $oSEC = new cSection((int)$i);
    $oSEC->MChange('stats','tags',cSection::CountItems($i,'tags'));
    }
    $_SESSION['pagedialog']='O|'.L('S_delete');
    $oHtml->Redirect($oVIP->exiturl);
    break;
  default:
    $_SESSION['pagedialog']='W|Unknown command';
    $oHtml->Redirect($oVIP->exiturl);
  }
}

// --------
// HTML START
// --------

// read uids to confirm, may also include corresponding section id (format e.123.s.5)

if ( isset($_POST['t1_cb']) )
{
  foreach($_POST['t1_cb'] as $str ) $arrCB[]=$str;
}
else
{
  $arrCB = explode(',',$nids);
}

$arrNids = array(); // list of element ids (format e.123)
$arrSids = array(); // list of section id
foreach($arrCB as $str)
{
  $arr = explode('.',$str);
  if ( count($arr)>=2 ) $arrNids[]=$arr[0].'.'.$arr[1];
  if ( count($arr)>=4 ) $arrSids[]=$arr[3];
}

if ( $e=='e' )
{
$oVIP->exiturl = "qnm_item.php?nid=".$arrNids[0]; // Exit to element page (if requested)
$oVIP->exitname = 'Network element';
}

// header

$oHtml->scripts[] = '<script type="text/javascript">
<!--
function updateform(formid,action)
{
  var arr = ["note","type","address","descr","move","delete","addtags","deltags"];
  for (var i=0; i<arr.length; i++)
  {
  if ( document.getElementById(formid+"_input_"+arr[i]) ) document.getElementById(formid+"_input_"+arr[i]).style.display="none";
  if ( document.getElementById(formid+"_info_"+arr[i]) ) document.getElementById(formid+"_info_"+arr[i]).style.display="none";
  }
  if ( document.getElementById(formid+"_input_"+action) ) document.getElementById(formid+"_input_"+action).style.display="table-row";
  if ( document.getElementById(formid+"_info_"+action) ) document.getElementById(formid+"_info_"+action).style.display="table-row";

  if ( action=="activate" || action=="inactivate" )
  {
  document.getElementById(formid+"_all").style.display="inline";
  document.getElementById(formid+"_all_label").style.display="inline";
  }
  else
  {
  document.getElementById(formid+"_all").style.display="none";
  document.getElementById(formid+"_all_label").style.display="none";
  }

  if ( document.getElementById(formid+"_warning") )
  {
    if ( action=="delete" )
    {
    document.getElementById(formid+"_warning").innerHTML="'.$L['f_warning_delete'].'";
    }
    else
    {
    document.getElementById(formid+"_warning").innerHTML="";
    }
  }
  if ( action=="type" ) qtFocusEnd("type");
  if ( action=="note" ) qtFocusEnd("note");
  if ( action=="addtags" ) qtFocusEnd("addtags");
  if ( action=="deltags" ) qtFocusEnd("deltags");
}

var e0 = "'.L('No_result').'";
var e1 = "'.L('try_other_lettres').'";
var e2 = "'.L('try_without_options').'";
var e3 = "'.L('All_categories').'";
var e4 = "'.L('Impossible').'";
var e5 = "'.L('Category_not_yet_used').'";

function split( val ) { return val.split( "," ); }
function extractLast( term ) { return split( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }

$(function() {

  updateform("f1","'.$a.'");

  $( "#value_type" ).autocomplete({
    minLength: 1,
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_type.php",
        dataType: "json",
        data: { term: request.term, s:'.$s.', e0: e0, e1: e1, e2:e2 },
        success: function(data) { response(data); }
      });
    },
    focus: function( event, ui ) {
      $( "#value_type" ).val( ui.item.rItem );
      return false;
    },
    select: function( event, ui ) {
      $( "#value_type" ).val( ui.item.rItem );
      return false;
    }
  })
  .data( "autocomplete" )._renderItem = function( ul, item ) {
    return $( "<li></li>" )
      .data( "item.autocomplete", item )
      .append( "<a class=\"jvalue\">" + item.rItem + (item.rInfo=="" ? "" : " &nbsp;<span class=\"jinfo\">(" + item.rInfo + ")</span>") + "</a>" )
      .appendTo( ul );
  };

  $( "#addtags" ).autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_tag.php",
        dataType: "json",
        data: { term: extractLast( request.term ), sids:"'.implode(',',$arrSids).'", e0:e0, e1:e5, e2:e5, e4:e4 },
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
  $( "#deltags" ).autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_tag.php",
        dataType: "json",
        data: { term: extractLast( request.term ), uids:"'.implode(',',ExtractELuids($arrNids)).'", e0:e0, e1:e1, e2:e1, e4:e4 },
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

});
//-->
</script>
';

include 'qnm_inc_hd.php';

// Elements (top 5)

$strElements = '';
for($i=0;$i<5;$i++) {
if ( isset($arrNids[$i]) ) {
  $oNE = new cNE($arrNids[$i]);
  $strElements .= $oNE->Dump(false).'<br/>';
  if ( count($arrNids)==1 && $oNE->class!='c' ) $strElements .= $oNE->DumpContent(false,'',20);
}}
if ( count($arrNids)>5 ) $strElements .= '...<br/>';

// Info Note

$strInfoName = L('Information');
$strInfoValue = '';
if ( count($arrNids)==1 )
{
  $strInfoName = str_replace(' ','<br/>',L('Previous_notes'));
  if ( isset($arrNids[0]) ) {
  if ( $oNE->posts>0 ) {
    $arr = $oNE->GetPosts('',10);
    foreach($arr as $id=>$arrPost)
    {
    $oPost = new cPost($arrPost);
    $oPost->textmsg = (strlen($oPost->textmsg)>200 ? substr($oPost->textmsg,0,199).'...' : $oPost->textmsg);
    $strInfoValue .= $oPost->Dump(true,'small','small');
    }
  }}
}
else
{
  $strInfoValue = L('f_info_add_note');
}

// Display

echo '<h2>',L('Edit'),'</h2>',PHP_EOL;
if ( !empty($error) ) echo '<p><span class="error">',$error,'</span></p>';

echo '
<form id="f1" method="post" action="',Href(),'" >
<table class="data_o">
<tr>
<td class="headfirst">',L('Item',count($arrNids),false),'</td>
<td>',$strElements,'</td>
</tr>
<tr>
<td class="headfirst">',L('Action'),'</td>
<td>
<select id="f1_a" name="a" onchange="updateform(this.form.id,this.value);">
<optgroup label="',L('State'),'">
<option value="activate"',($a=='activate' ? QSEL : ''),'>',L('Activate'),'</option>
<option value="inactivate"',($a=='inactivate' ? QSEL : ''),'>',L('Inactivate'),'</option>
<option value="delete"',($a=='delete' ? QSEL : ''),'>',L('Delete'),'</option>
</optgroup>
<optgroup label="',L('Properties'),'">
<option value="type"',($a=='type' ? QSEL : ''),'>',L('Change_type'),'</option>
<option value="address"',($a=='address' ? QSEL : ''),'>',L('Change_address'),'</option>
<option value="descr"',($a=='descr' ? QSEL : ''),'>',L('Change_descr'),'</option>
<option value="move"',($a=='move' ? QSEL : ''),'>',L('Move_to_section'),'</option>
</optgroup>
<optgroup label="',L('Messages'),'">
<option value="note"',($a=='note' ? QSEL : ''),'>',L('Add_note'),'</option>
<option value="close"',($a=='close' ? QSEL : ''),'>',L('f_Close_all_notes'),'</option>
<option value="deleteclosed"',($a=='deleteclosed' ? QSEL : ''),'>',L('f_Delete_all_closed_notes'),'</option>
</optgroup>
<optgroup label="',L('Tags'),'">
<option value="addtags"',($a=='addtags' ? QSEL : ''),'>',L('Tags_add'),'</option>
<option value="deltags"',($a=='deltags' ? QSEL : ''),'>',L('Tags_remove'),'</option>
</optgroup>
</select>
 <input type="checkbox" name="all" id="f1_all"/><label for="f1_all" id="f1_all_label">',L('f_Also_sub_items'),'</label>
</td>
</tr>
<tr id="f1_input_note"><td class="headfirst">',L('Message'),'</td><td><textarea id="note" name="note" cols="80" rows="3" maxlength="'.(empty($_SESSION[QT]['chars_per_post']) ? '1000' : $_SESSION[QT]['chars_per_post']).'">'.$strTxt.'</textarea></td></tr>
<tr id="f1_info_note"><td class="headfirst">',$strInfoName,'</td><td>',(empty($strInfoValue) ? L('None') : $strInfoValue),'</td></tr>
<tr id="f1_input_type"><td class="headfirst">',L('Type'),'</td><td><input id="value_type" name="value_type" value="" size="32" /></td></tr>
<tr id="f1_info_type"><td class="headfirst">',L('Information'),'</td><td>',L('f_info_replace'),'</td></tr>
<tr id="f1_input_address"><td class="headfirst">',L('Address'),'</td><td><input id="value_address" name="value_address" value="" size="32" /></td></tr>
<tr id="f1_info_address"><td class="headfirst">',L('Information'),'</td><td>',L('f_info_replace'),'</td></tr>
<tr id="f1_input_descr"><td class="headfirst">',L('Description'),'</td><td><textarea id="value_descr" name="value_descr" cols="60" rows="5"></textarea></td></tr>
<tr id="f1_info_descr"><td class="headfirst">',L('Information'),'</td><td>',L('f_info_replace'),'</td></tr>
<tr id="f1_input_move"><td class="headfirst">',L('Move_to_section'),'</td><td><select id="move" name="move"><option value="-1" selected="selected" style="font-style:italic">',L('Select...'),'</option>',Sectionlist(null,$s),'</select></td></tr>
<tr id="f1_info_delete"><td class="headfirst">',L('Information'),'</td><td>',L('f_info_delete'),'</td></tr>
<tr id="f1_input_addtags"><td class="headfirst">',L('Tags'),'</td><td><input id="addtags" name="addtags" value="" size="32"/></td></tr>
<tr id="f1_info_addtags"><td class="headfirst" >',L('Information'),'</td><td>',L('f_info_add_tags'),'</td></tr>
<tr id="f1_input_deltags"><td class="headfirst">',L('Tags'),'</td><td><input id="deltags" name="deltags" value="" size="32"/></td></tr>
<tr id="f1_info_deltags"><td class="headfirst">',L('Information'),'</td><td>',L('f_info_remove_tags'),'</td></tr>
<tr><td class="headfirst">&nbsp;</td><td><input type="hidden" id="f1_s" name="s" value="',$s,'"/><input type="hidden" name="e" value="',$e,'"/>
<input type="hidden" id="f1_nids" name="nids" value="',implode(',',$arrNids),'"/>
<input type="hidden" id="f1_sids" name="sids" value="',implode(',',$arrSids),'"/>
<input type="submit" id="ok" name="ok" value="',$L['Ok'],(count($arrNids)>1 ? ' ('.count($arrNids).')' : ''),'"/> <span id="f1_warning" class="warning"></span></td>
</tr>
</table>
</form>
';

echo '<p><a href="',$oVIP->exiturl,'">&laquo; ',$oVIP->exitname,'</a></p>
';

// --------
// HTML END
// --------

include 'qnm_inc_ft.php';