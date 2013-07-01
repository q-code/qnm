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

if ( $oVIP->user->role!='A' ) die(Error(13));

// INITIALISE

$s = -1;
$tt = 0; // 0:definition, 1:display or 9:translation
QThttpvar('s tt','int int');
if ( $s<0 ) die('Missing parameters');
if ( $tt!=0 && $tt!=1 && $tt!=9 ) $tt=0;

$oVIP->selfurl = 'qnm_adm_section.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Section_upd'];
$oVIP->exiturl = 'qnm_adm_sections.php';
$oVIP->exitname = '&laquo; '.$L['Sections'];

$arrDomains = GetDomains();
$arrStaff = GetUsers('M');
$oSEC = new cSection($s);

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) && $tt==0 )
{
  // CHECK MANDATORY VALUE

  $str = trim($_POST['title']); if ( get_magic_quotes_gpc() ) $str = stripslashes($str);
  $str = QTconv($str,'3',QNM_CONVERT_AMP,false);
  if ( empty($str) ) $error = $L['Title'].' '.$L['E_invalid'];

  if ( empty($error) )
  {
    $oSEC->pid = intval($_POST['domain']);
    $oSEC->idtitle = $str;
    $oSEC->name = $str;
    $oSEC->type = intval($_POST['type']);
    $oSEC->status = intval($_POST['status']);
    if ( isset($_POST['modname']) )
    {
      if ( $_POST['modname']!=$_POST['modnameold'] )
      {
      $oSEC->modname = $_POST['modname'];
      $oSEC->modid = array_search($_POST['modname'],$arrStaff);
      if ( $oSEC->modid==FALSE || empty($oSEC->modid) ) { $oSEC->modid=1; $oSEC->modname=$arrStaff[1]; $warning=$L['Userrole_c'].' '.$L['E_invalid']; $_SESSION['pagedialog'] = 'W|'.$warning; }
      }
    }
    if ( isset($_POST['modid']) )
    {
      if ( $_POST['modid']!=$_POST['modidold'] )
      {
        $oSEC->modname = $arrStaff[$_POST['modid']];
        $oSEC->modid = $_POST['modid'];
      }
    }
    $oSEC->o_order = $_POST['o_order'];
    $oSEC->o_last = $_POST['o_last'];
    $oSEC->o_logo = $_POST['o_logo'];
    $oSEC->options = 'order='.$oSEC->o_order.';last='.$oSEC->o_last.';logo='.$oSEC->o_logo;

  }

  // SAVE

  if ( empty($error) )
  {
    $strQ = 'UPDATE '.TABSECTION.' SET';
    $strQ .= ' pid='.$oSEC->pid;
    $strQ .= ',title="'.addslashes($oSEC->idtitle).'"';
    $strQ .= ',type="'.$oSEC->type.'"';
    $strQ .= ',status='.$oSEC->status;
    $strQ .= ',notify="'.$oSEC->notify.'"';
    $strQ .= ',moderator='.$oSEC->modid;
    $strQ .= ',moderatorname="'.$oSEC->modname.'"';
    $strQ .= ',titlefield="'.$oSEC->titlefield.'"';
    $strQ .= ',numfield="'.$oSEC->numfield.'"';
    $strQ .= ',alternate="'.$oSEC->notifycc.'"';
    $strQ .= ',wisheddate="'.($oSEC->wisheddate+$oSEC->wisheddflt).'"';
    $strQ .= ',prefix="'.$oSEC->prefix.'"';
    $strQ .= ',options="'.$oSEC->options.'"';
    $strQ .= ' WHERE uid='.$oSEC->uid;
    $oDB->Query($strQ);
    if ( isset($_SESSION['L']) ) $_SESSION['L'] = array();
    if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
    $_SESSION['pagedialog'] = 'O|'.$L['S_save'];
  }
  else
  {
    $_SESSION['pagedialog'] = 'E|'.$error;
  }
}

if ( isset($_POST['ok']) && $tt==1 )
{
  $str = null;
  if ( isset($_POST['usefavorite']) )
  {
    if ( isset($_POST['favoritefilter']) )
    {
      switch($_POST['favoritefilter'])
      {
        case '0':
        case '1': $str = $_POST['favoritefilter']; break;
        case '2':
          $str = $_POST['typelist'];
          if ( empty($str) ) $error = L('Custom_list').' '.L('E_invalid');
          break;
      }
    }
  }
  if ( empty($error) )
  {
    $oSEC->MChange('options','filter',$str); // when null, key is removed for the options
    $_SESSION['pagedialog'] = 'O|'.$L['S_save'];
  }
  else
  {
    $_SESSION['pagedialog'] = 'E|'.$error;
  }
}

if ( isset($_POST['ok']) && $tt==9 )
{
  // translations

  cLang::Delete(array('sec','secdesc'),'s'.$oSEC->uid);
  foreach($_POST as $strKey=>$strTranslation)
  {
    if ( substr($strKey,0,1)=='T' )
    {
      if ( !empty($strTranslation) )
      {
      if ( get_magic_quotes_gpc() ) $strTranslation = stripslashes($strTranslation);
      cLang::Add('sec',substr($strKey,1),'s'.$oSEC->uid,$strTranslation);
      }
    }
    if ( substr($strKey,0,1)=='D' )
    {
      if ( !empty($strTranslation) )
      {
      if ( get_magic_quotes_gpc() ) $strTranslation = stripslashes($strTranslation);
      cLang::Add('secdesc',substr($strKey,1),'s'.$oSEC->uid,$strTranslation);
      }
    }
  }

  // unregister and exit

  if ( isset($_SESSION['L']) ) $_SESSION['L'] = array();
  if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
  $_SESSION['pagedialog'] = 'O|'.$L['S_save'];
}

// --------
// HTML START
// --------

include 'qnm_adm_inc_hd.php';

$arrDest = $arrDomains;
Unset($arrDest[$oSEC->pid]);

// DISPLAY TABS

$arrTabs = array(0=>L('Adm_settings'),1=>L('Favorite_filters'),9=>L('Translations'));
echo HtmlTabs($arrTabs, $oVIP->selfurl.'?s='.$s, $tt, 6, $L['E_editing']);

// DISPLAY TAB PANEL

echo '<div class="pan">
<div class="pan_top">',$oSEC->idtitle,' &middot; ',$arrTabs[$tt],'</div>
';

// FORM 0

if ( $tt==0 )
{

$strFile='';
if ( file_exists('upload/section/'.$s.'.gif') ) $strFile = $s.'.gif';
if ( empty($strfile) && file_exists('upload/section/'.$s.'.jpg') ) $strFile = $s.'.jpg';
if ( empty($strfile) && file_exists('upload/section/'.$s.'.png') ) $strFile = $s.'.png';
if ( empty($strfile) && file_exists('upload/section/'.$s.'.jpeg') ) $strFile = $s.'.jpeg';

echo '
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(qtHtmldecode("',$L['Missing'],': ',$L['Title'],'")); return false; }
  return null;
}
function switchimage(strId)
{
  var strDefault="'.$_SESSION[QT]['skin_dir'].'/ico_section_'.$oSEC->type.'_'.$oSEC->status.'.gif";
  var strSpecific="upload/section/',$strFile,'";
  document.getElementById(strId).src=(document.getElementById(strId).src.search(strDefault)==-1 ? strDefault : strSpecific);
  return null;
}
var e0 = "'.L('No_result').'";
var e1 = "'.L('try_other_lettres').'";
var e2 = "'.L('try_without_options').'";
$(function() {
  $( "#modname" ).autocomplete({
    minLength: 1,
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_name.php",
        dataType: "json",
        data: { term: request.term, r:"M", e0: e0, e1: e1 },
        success: function(data) { response(data); }
      });
    },
    focus: function( event, ui ) {
      $( "#modname" ).val( ui.item.rItem );
      return false;
    },
    select: function( event, ui ) {
      $( "#modname" ).val( ui.item.rItem );
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

echo '<form method="post" action="',$oVIP->selfurl,'" onsubmit="return ValidateForm(this);">
<table class="data_o">
<tr class="data_o">
<td class="headgroup" colspan="2">',$L['Definition'],'</td>
</tr>
';
$str = QTconv($oSEC->idtitle,'I');
echo '<tr class="data_o">
<td class="headfirst" style="width:150px; text-align:right"><label for="title">',$L['Title'],'</label></td>
<td><input type="text" id="title" name="title" size="55" maxlength="64" value="',$str,'" style="background-color:#FFFF99;" onchange="bEdited=true;"/>',(strstr($str,'&amp;') ?  ' <span class="small">'.$oSEC->idtitle.'</span>' : ''),'</td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst" style="width:150px; text-align:right"><label for="domain">',$L['Domain'],'</label></td>
<td><select id="domain" name="domain" onchange="bEdited=true;">
<option value="',$oSEC->pid,'"',QSEL,'>',$arrDomains[$oSEC->pid],'</option>',QTasTag($arrDest,'',array('format'=>$L['Move_to'].': %s')),'</select></td>
</tr>
';
echo '<tr class="data_o">
<td class="headgroup" colspan="2">',$L['Properties'],'</td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst" style="text-align: right; width:150px"><label for="type">',$L['Type'],'</label></td>
<td>
<select id="type" name="type" onchange="bEdited=true;">
<option value="1"',($oSEC->type==1 ? QSEL : ''),'>',$L['Section_type'][1],'</option>
<option value="0"',($oSEC->type==0 ? QSEL : ''),'>',$L['Section_type'][0],'</option>
</select>
 ',$L['Status'],' <select id="status" name="status" onchange="bEdited=true;">
<option value="0"',($oSEC->status==0 ? QSEL : ''),'>',$L['Section_status'][0],'</option>
<option value="1"',($oSEC->status==1 ? QSEL : ''),'>',$L['Section_status'][1],'</option>
</select>
</tr>
';
if ( count($arrStaff)>20 )
{
echo '<tr class="data_o">
<td class="headfirst" style="width:150px; text-align:right"><label for="modname">',$L['Userrole_c'],'</label></td>
<td>
<input type="hidden" name="modnameold" value="',$oSEC->modname,'" onchange="bEdited=true;"/>
<input name="modname" id="modname" size="20" maxlength="24" value="',$oSEC->modname,'" onchange="bEdited=true;"/>
</td>
</tr>
';
}
else
{
echo '<tr class="data_o">
<td class="headfirst" style="width:150px; text-align:right"><label for="modid">',$L['Userrole_c'],'</label</td>
<td>
<input type="hidden" id="modname"/><input type="hidden" name="modidold" value="',$oSEC->modid,'" onchange="bEdited=true;"/>
<select name="modid" id="modid" onchange="bEdited=true;">',QTasTag($arrStaff,$oSEC->modid,array('current'=>$oSEC->modid,'classC'=>'bold')),'</select>
</td>
</tr>
';
}
echo '<tr class="data_o">
<td class="headgroup" colspan="2">',$L['Display_options'],'</td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst" style="width:150px; text-align:right"><label for="o_logo">Logo</label></td>
<td><select id="o_logo" name="o_logo" onchange="bEdited=true; switchimage(\'idlogo\');">
<option value=""',(empty($oSEC->o_logo) ? QSEL : ''),'>',$L['Default'],'</option>
',(empty($strFile) ? '' : '<option value="'.$strFile.'"'.(!empty($oSEC->o_logo) ? QSEL : '').'>'.L('Specific_image').'</option>').'
</select> ',AsImg($oSEC->GetLogo(),'S',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'i_sec','vertical-align:middle','','idlogo'),' <a class="small" href="qnm_adm_section_img.php?id=',$s,'">',$L['Add'],'/',$L['Remove'],'</a>
</td>
</tr>
';
$arr = array('id'=>'Id (recommended)','type'=>'Type, then Id','status'=>'Status, then Id');
echo '<tr class="data_o">
<td class="headfirst" style="width:150px; text-align:right"><label for="o_order">',$L['Item_order'],'</label></td>
<td>
<select name="o_order" id="o_order" onchange="bEdited=true;">',QTasTag($arr,$oSEC->o_order),'</select>
</td>
</tr>
';
$arr = array('posts'=>$L['Messages'],'status'=>$L['Status'],'tags'=>$L['Tags'],'docs'=>$L['Documents'],'insertdate'=>$L['Creation_date']);
echo '<tr class="data_o">
<td class="headfirst" style="text-align: right; width:150px"><label for="o_last">',$L['Infofield'],'</label></td>
<td><select id="o_last" name="o_last" onchange="bEdited=true;">',QTasTag($arr,$oSEC->o_last),'</select></td>
</tr>
';

echo '<tr class="data_o">
<td class="headgroup" colspan="2" style="padding:6px; text-align:center"><input type="hidden" name="s" value="',$oSEC->uid,'"/><input type="submit" name="ok" value="',$L['Save'],'"/></td>
</tr>
';

echo '</table>
</form>
';

}

// FORM Favorite filters

if ( $tt==1 )
{

$strFilter = $oSEC->MGet('options','filter'); // can be null if not yet set
$bUse = strlen($strFilter)>0;

echo '<form method="post" action="',$oVIP->selfurl,'">
<script type="text/javascript">
<!--
function enableoption(b)
{
  doc = document;
  if ( doc.getElementById("sort_a") ) doc.getElementById("sort_a").disabled=!b;
  if ( doc.getElementById("sort_o") ) doc.getElementById("sort_o").disabled=!b;
  if ( doc.getElementById("sort_c") ) doc.getElementById("sort_c").disabled=!b;
  if ( doc.getElementById("typelist") )
  {
  doc.getElementById("typelist").disabled=!b;
  doc.getElementById("typelist").style.visibility="hidden";
  if ( b && doc.getElementById("sort_c").checked ) visibleoption("sort_c");
  }
  if ( b )
  {
    doc.getElementById("sort_a").parentNode.style.color="#000000";
    doc.getElementById("sort_c_help").style.color="#000000";
  }
  else
  {
    doc.getElementById("sort_a").parentNode.style.color="#999999";
    doc.getElementById("sort_c_help").style.color="#999999";
  }
}
function visibleoption(id)
{
  doc = document;
  if ( doc.getElementById("typelist") ) doc.getElementById("typelist").style.visibility=(id=="sort_c" ? "visible" : "hidden");
}
var e0 = "'.L('No_result').'";
var e1 = "'.L('try_other_lettres').'";
var e2 = "'.L('try_without_options').'";
function split( val ) { return val.split( "'.QNM_QUERY_SEPARATOR.'" ); }
function extractLast( term ) { return split( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }
$(function() {
  $("#typelist").autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_type.php",
        dataType: "json",
        data: { term: extractLast( request.term ), s:'.$s.', e0:e0, e1:e1, e2:e2 },
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
<p>',L('Favorite_filters_help'),'</p>
';
echo '<table class="data_o">
<tr class="data_o">
<td class="headgroup" colspan="3">',L('Favorite_filters'),'</td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst">',L('Status'),'</td>
<td class="tdcheckbox"><input type="checkbox" id="usefavorite" name="usefavorite"',($bUse ? QCHE : ''),' value="1" onclick="enableoption(this.checked);"/></td>
<td><label for="usefavorite">',L('Use_favorites'),'</label></td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst">',L('Options'),'</td>
<td class="tdcheckbox">&nbsp;</td>
<td>
<input type="radio" id="sort_a" name="favoritefilter" value="0"',($strFilter=="0" ? QCHE : ''),($bUse ? '' : ' disabled="disabled"'),' onclick="visibleoption(this.id);"/><label for="sort_a">',L('Sort_alphabetic'),'</label><br/>
<input type="radio" id="sort_o" name="favoritefilter" value="1"',($strFilter=="1" ? QCHE : ''),($bUse ? '' : ' disabled="disabled"'),' onclick="visibleoption(this.id);"/><label for="sort_o">',L('Sort_occurance'),'</label><br/>
<input type="radio" id="sort_c" name="favoritefilter" value="2"',(strlen($strFilter)>1 ? QCHE : ''),($bUse ? '' : ' disabled="disabled"'),' onclick="visibleoption(this.id);"/><label for="sort_c">',L('Custom_list'),'</label> <input type="text" id="typelist" name="typelist"',($bUse ? '' : ' disabled="disabled" style="visibility:hidden"'),' value="',(strlen($strFilter)>1 ? $strFilter : ''),'" size="40"/><br/>
</td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst">',L('Information'),'</td>
<td class="tdcheckbox">&nbsp;</td>
<td><p class="help" id="sort_c_help">',L('Sort_custom_help'),'</p></td>
</tr>
';
echo '<tr class="data_o">
<td class="headgroup" colspan="3" style="padding:6px; text-align:center">
<input type="hidden" name="s" value="',$oSEC->uid,'"/>
<input type="hidden" name="tt" value="',$tt,'"/>
<input type="submit" name="ok" value="',$L['Save'],'"/></td>
</tr>
';
echo '</table>
</form>
';

}

// FORM 9 (translation)

if ( $tt==9 )
{

echo '<form method="post" action="',$oVIP->selfurl,'">
<table class="data_o">
';
echo '<tr class="data_o">
<td class="headgroup" colspan="3">',$L['Translations'],'</td>
</tr>
<tr class="data_o">
<td class="headfirst">',$L['Section_name_and_desc'],'</td>
<td colspan="2">
  <p class="help">',sprintf($L['E_no_translation'],$oSEC->idtitle),'</p>
  <table class="hidden">';
$arrTrans = cLang::Get('sec','*','s'.$oSEC->uid);
$arrDescTrans = cLang::Get('secdesc','*','s'.$oSEC->uid);
include 'bin/qnm_lang.php'; // this creates $arrLang
foreach($arrLang as $strIso=>$arr)
{
  $str = '';
  if ( isset($arrTrans[$strIso]) ) {
  if ( !empty($arrTrans[$strIso]) ) {
    $str = QTconv($arrTrans[$strIso],'I');
  }}
  echo '
  <tr class="hidden">
  <td class="hidden" style="width:30px"><span title="',$arr[1],'">',$arr[0],'</span></td>
  <td class="hidden"><input class="small" title="',$L['Section'].' ('.$strIso.')','" type="text" id="T',$strIso,'" name="T',$strIso,'" size="30" maxlength="64" value="',$str,'" onchange="bEdited=true;"/>&nbsp;</td>';
  $str = '';
  if ( isset($arrDescTrans[$strIso]) ) {
  if ( !empty($arrDescTrans[$strIso]) ) {
    $str = QTconv($arrDescTrans[$strIso],'I');
  }}
  echo '  <td class="hidden"><textarea class="small" title="',$L['Description'].' ('.$strIso.')','" id="D',$strIso,'" name="D',$strIso,'" cols="45" rows="2" onchange="bEdited=true;">',$str,'</textarea></td>
  </tr>
  ';
}
echo '  </table>
</td>
</tr>
';
echo '<tr class="data_o">
<td class="headgroup" colspan="3" style="padding:6px; text-align:center">
<input type="hidden" name="s" value="',$oSEC->uid,'"/>
<input type="hidden" name="tt" value="',$tt,'"/>
<input type="submit" name="ok" value="',$L['Save'],'"/></td>
</tr>
';
echo '</table>
</form>
';

}

// END TABS

echo '
</div>
';

echo '<p><a href="',$oVIP->exiturl,'" onclick="return qtEdited(bEdited,\''.$L['E_editing'].'\');">',$oVIP->exitname,'</a></p>';

// HTML END

include 'qnm_adm_inc_ft.php';