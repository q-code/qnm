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
require_once 'bin/qnm_fn_tags.php';
if ( !$oVIP->user->CanView('V5') ) HtmlPage(11);
if ( !defined('QNM_QUERY_SEPARATOR') ) define('QNM_QUERY_SEPARATOR', ',');

// INITIALISE

$oVIP->selfurl = 'qnm_search.php';
$oVIP->selfname = $L['Search'];
$oVIP->exitname = $L['Search'];

$tt = 0;  // tab index
$q = '';
$v = '';
$v2 = '';
$fs = '*';  // section filter can be '*' or [int]
$ft = '*';
$fst = '*';

QThttpvar('q v v2 fs ft fst tt','str str str str str str int');

if ( isset($_POST['s_id']) ) $fs = strip_tags($_POST['s_id']);
if ( isset($_POST['s_kw']) ) $fs = strip_tags($_POST['s_kw']);
if ( isset($_POST['s_tag']) ) $fs = strip_tags($_POST['s_tag']);
if ( isset($_POST['s_fld']) ) $fs = strip_tags($_POST['s_fld']);
if ( isset($_POST['s_date']) ) $fs = strip_tags($_POST['s_date']);
if ( isset($_POST['s_rel']) ) $fs = strip_tags($_POST['s_rel']);
if ( isset($_POST['s_sub']) ) $fs = strip_tags($_POST['s_sub']);
if ( isset($_POST['t_id']) ) $ft = strip_tags($_POST['t_id']);
if ( isset($_POST['t_kw']) ) $ft = strip_tags($_POST['t_kw']);
if ( isset($_POST['t_tag']) ) $ft = strip_tags($_POST['t_tag']);
if ( isset($_POST['t_fld']) ) $ft = strip_tags($_POST['t_fld']);
if ( isset($_POST['t_date']) ) $ft = strip_tags($_POST['t_date']);
if ( isset($_POST['t_rel']) ) $ft = strip_tags($_POST['t_rel']);
if ( isset($_POST['t_sub']) ) $ft = strip_tags($_POST['t_sub']);
if ( isset($_POST['st_id']) ) $fst = strip_tags($_POST['st_id']);
if ( isset($_POST['st_kw']) ) $fst = strip_tags($_POST['st_kw']);
if ( isset($_POST['st_tag']) ) $fst = strip_tags($_POST['st_tag']);
if ( isset($_POST['st_fld']) ) $fst = strip_tags($_POST['st_fld']);
if ( isset($_POST['st_date']) ) $fst = strip_tags($_POST['st_date']);
if ( isset($_POST['st_rel']) ) $fst = strip_tags($_POST['st_rel']);
if ( isset($_POST['st_sub']) ) $fst = strip_tags($_POST['st_sub']);
if ( $fs==='' ) $fs='*';
if ( $ft==='' ) $ft='*';
if ( $fst==='' ) $fst='*';
if ( $tt<0 || $tt>2 ) $tt=0;
if ( $fs!=='*' ) $fs=(int)$fs;
if ( $fst!=='*' ) $fst=(int)$fst;
if ( $q=='rel' || $q=='sub' ) if ( empty($v) ) { $v='0'; $v2='*'; }

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) ) {
if ( !empty($_POST['q']) ) {

  $str = '';
  foreach(array('tt','fs','ft','fst','v','v2') as $key) if ( $$key!=='*' ) $str .= '&amp;'.$key.'='.urlencode($$key); // concat uri arguments (and drop '*')

  switch($_POST['q'])
  {
  case 'id':
    if ( $v==='' && $ft==='*' && $fst==='*') $error='Missing argument';
    if ( empty($error) ) { $oVIP->exiturl = 'qnm_items.php?q=id'.$str; $oHtml->PageBox('0'); exit; }
    break;
  case 'kw':
    if ( empty($v) ) $error = $L['Keywords'].' '.$L['E_invalid'];
    if ( empty($error) ) { $oVIP->exiturl = 'qnm_items.php?q=kw'.$str; $oHtml->PageBox('0'); exit; }
    break;
  case 'tag':
    if ( empty($v) ) $error = $L['Tags'].' '.$L['E_invalid'];
    if ( empty($error) ) { $oVIP->exiturl = 'qnm_items.php?q=tag'.$str; $oHtml->PageBox('0'); exit; }
    break;
  case 'fld':
    if ( empty($v) || empty($v2) ) $error = $L['Field'].' '.$L['E_invalid'];
    if ( empty($error) ) { $oVIP->exiturl = 'qnm_items.php?q=fld'.$str; $oHtml->PageBox('0'); exit; }
    break;
  case 'date':
    if ( empty($v) ) $error = $L['Date'].' '.$L['E_invalid'];
    if ( empty($error) ) { $oVIP->exiturl = 'qnm_items.php?q=date'.$str; $oHtml->PageBox('0'); exit; }
    break;
  case 'rel':
    if ( $v==='' ) $error = $L['Relation'].' '.$L['E_invalid'];
    if ( empty($error) ) { $oVIP->exiturl = 'qnm_items.php?q=rel'.$str; $oHtml->PageBox('0'); exit; }
    break;
  case 'sub':
    if ( $v==='' ) $error = $L['Relation'].' '.$L['E_invalid'];
    if ( empty($error) ) { $oVIP->exiturl = 'qnm_items.php?q=sub'.$str; $oHtml->PageBox('0'); exit; }
    break;
  default:
    $error = 'Invalid argument';
  }

}}

if ( !empty($error) ) $_SESSION['pagedialog']='E|'.$error;

// --------
// HTML START
// --------

$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qnm_search.css" />';
$oHtml->scripts[] = '<script type="text/javascript">
<!--
var e0 = "'.L('No_result').'";
var e1 = "'.L('try_other_lettres').'";
var e2 = "'.L('try_without_options').'";
var e3 = "'.L('All_categories').'";
var e4 = "'.L('Impossible').'";

function split( val ) { return val.split( "'.QNM_QUERY_SEPARATOR.'" ); }
function extractLast( term ) { return split( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }

function EnablePopup(id,pid)
{
  var pop = document.getElementById(id);
  var ppop = document.getElementById(pid);
  if ( pop && ppop ) pop.disabled=(ppop.value=="0");
}

function Running()
{
  var doc = document;
  if ( doc.getElementById("ico_option") )
  {
  var running="option";
  if (doc.getElementById("fs").value!="*") running="run";
  if (doc.getElementById("ft").value!="") running="run";
  if (doc.getElementById("fst").value!="*") running="run";
  doc.getElementById("ico_option").src="'.$_SESSION[QT]['skin_dir'].'/ico_section_" + running + ".gif"
  }
}
function SearchOptionS(strValue)
{
  var doc = document;
  if (doc.getElementById("s_id")) doc.getElementById("s_id").value=strValue;
  if (doc.getElementById("s_kw")) doc.getElementById("s_kw").value=strValue;
  if (doc.getElementById("s_tag")) doc.getElementById("s_tag").value=strValue;
  if (doc.getElementById("s_fld")) doc.getElementById("s_fld").value=strValue;
  if (doc.getElementById("s_date")) doc.getElementById("s_date").value=strValue;
  if (doc.getElementById("s_rel")) doc.getElementById("s_rel").value=strValue;
  if (doc.getElementById("s_sub")) doc.getElementById("s_sub").value=strValue;
  Running();
}
function SearchOptionT(strValue)
{
  var doc = document;
  if (doc.getElementById("t_id")) doc.getElementById("t_id").value=strValue;
  if (doc.getElementById("t_kw")) doc.getElementById("t_kw").value=strValue;
  if (doc.getElementById("t_tag")) doc.getElementById("t_tag").value=strValue;
  if (doc.getElementById("t_fld")) doc.getElementById("t_fld").value=strValue;
  if (doc.getElementById("t_date")) doc.getElementById("t_date").value=strValue;
  if (doc.getElementById("t_rel")) doc.getElementById("t_rel").value=strValue;
  if (doc.getElementById("t_sub")) doc.getElementById("t_sub").value=strValue;
  Running();
}
function SearchOptionSt(strValue)
{
  var doc = document;
  if (doc.getElementById("st_id")) doc.getElementById("st_id").value=strValue;
  if (doc.getElementById("st_kw")) doc.getElementById("st_kw").value=strValue;
  if (doc.getElementById("st_tag")) doc.getElementById("st_tag").value=strValue;
  if (doc.getElementById("st_fld")) doc.getElementById("st_fld").value=strValue;
  if (doc.getElementById("st_date")) doc.getElementById("st_date").value=strValue;
  if (doc.getElementById("st_rel")) doc.getElementById("st_rel").value=strValue;
  if (doc.getElementById("st_sub")) doc.getElementById("st_sub").value=strValue;
  Running();
}
function ValidateForm(theForm)
{
  if ( theForm.id=="form_kw" ) if (document.getElementById("kw").value.length==0) { alert("Text - "+qtHtmldecode("'.$L['Missing'].'")); return false; }
  if ( theForm.id=="form_tag" ) if (document.getElementById("searchtag").value.length==0) { alert("Text - "+qtHtmldecode("'.$L['Missing'].'")); return false; }
  if ( theForm.id=="form_fld" ) if (document.getElementById("fld").value.length==0) { alert("Field - "+qtHtmldecode("'.$L['Missing'].'")); return false; }
  if ( theForm.id=="form_date" ) if (document.getElementById("date").value.length==0) { alert("Date - "+qtHtmldecode("'.$L['Missing'].'")); return false; }
  return null;
}

if ( document.getElementById("id") ) {
if ( document.getElementById("id").value.length==0 ) {
  if ( document.getElementById("kw").value.length!=0 ) qtFocusEnd("kw");
  if ( document.getElementById("searchtag").value.length!=0 ) qtFocusEnd("searchtag");
}}

$(function() {

  $( "#ft" ).autocomplete({
    minLength: 0,
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_type.php",
        dataType: "json",
        data: { term: request.term, fs:function() {return $("#fs").val();}, fst:function() {return $("#fst").val();}, e0:e0, e1:e1, e2:e2 },
        success: function(data) { response(data); }
      });
    },
    focus: function( event, ui ) {
      $( "#ft" ).val( ui.item.rItem );
      return false;
    },
    select: function( event, ui ) {
      $( "#ft" ).val( (ui.item.rInfo=="*" ? "Type:"+ui.item.rItem : ui.item.rItem) );
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
include 'qnm_inc_menu.php';

// SEARCH OPTION

echo '
<h2>',$L['Search_options'],'</h2>
<div class="searchoptions">
<form method="post" action="',Href(),'">
<p>
',AsImg($_SESSION[QT]['skin_dir'].'/ico_section_option.gif','search',$L['Search'],'i_sec','','','ico_option'),'
',$L['Section'],'&nbsp;<select id="fs" name="fs" size="1" class="small" onchange="SearchOptionS(this.value);" style="width:150px">',Sectionlist($fs,array(),array(),$L['All_sections']),'</select>
',$L['Status'],'&nbsp;<select id="fst" name="fst" size="1" class="small" onchange="SearchOptionSt(this.value);" style="width:75px"><option value="*">&nbsp;</option>',QTasTag($oVIP->GetStatuses(),$fst),'</select>
',$L['Type'],'&nbsp;<input id="ft" name="ft" size="13" class="small" onblur="SearchOptionT(this.value);" value="'.($ft==='*' ? '' : $ft).'"/> <input type="submit" id="fok" name="fok" value="',$L['Ok'],'"/>
</p>
</form>
</div>
';

// TAB PANEL

$arrTabs = array(L('Field'),L('Creation_date'),L('Links'));
echo HtmlTabs($arrTabs, $oVIP->selfurl.'?'.GetURI('tt'), $tt, 6);

// DISPLAY TAB PANEL

echo '<div class="pan">
';

// -------
switch($tt) {
// -------

case 0:

  echo '<div class="searchcriteria">
  <h2>',$L['Search_criteria'],'<sup>*</sup></h2>
  ';

  // ERROR MESSAGE

  if ( !empty($error) ) echo '<p class="error">',$error,'</p>';

  // SEARCH BY ID

  echo '<script type="text/javascript">
  <!--
  $(function() {

    $("#id").autocomplete({
      source: function(request, response) {
        $.ajax({
          url: "qnm_j_id.php",
          dataType: "json",
          data: { term: extractLast( request.term ), fs:function() { return $("#fs").val(); }, ft:function() { return $("#ft").val(); }, fst:function() { return $("#fst").val(); }, e0:e0, e1:e1, e2:e2 },
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

    $( "#kw" ).autocomplete({
      source: function(request, response) {
        $.ajax({
          url: "qnm_j_kw.php",
          dataType: "json",
          data: { term: extractLast( request.term ), fs:function() { return $("#fs").val(); }, ft:function() { return $("#ft").val(); }, fst:function() { return $("#fst").val(); }, v2:function() { return $("#v_inp").is(":checked"); }, e0:e0, e1:e1, e2:e2 },
          success: function(data) { response(data); }
        });
      },
      search: function() {
        // custom minLength
        var term = extractLast( this.value );
        if ( term.length < 2 ) { return false; }
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

    $( "#searchtag" ).autocomplete({
      source: function(request, response) {
        $.ajax({
          url: "qnm_j_tag.php",
          dataType: "json",
          data: { term: extractLast( request.term ), fs:function() { return $("#fs").val(); }, ft:function() { return $("#ft").val(); }, fst:function() { return $("#fst").val(); }, e0:e0, e1:e1, e2:e2, e3:e3, e4:e4 },
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

    $( "#fld" ).autocomplete({
      source: function(request, response) {
        $.ajax({
          url: "qnm_j_fld.php",
          dataType: "json",
          data: { term: extractLast( request.term ), v2:function() { return $("#v_fld").val(); }, fs:function() { return $("#fs").val(); }, ft:function() { return $("#ft").val(); }, o_st:function() { return $("#o_st").val(); }, e0:e0, e1:e1, e2:e2 },
          success: function(data) { response(data); }
        });
      },
      search: function() {
        // custom minLength
        var term = extractLast( this.value );
        if ( term.length < 2 ) { return false; }
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
  <form method="post" id="form_id" action="',Href(),'" onsubmit="return ValidateForm(this);">
  <table class="data_s">
  <tr class="data_s">
  <td class="tdicon">',AsImg($_SESSION[QT]['skin_dir'].'/ico_section_search.gif','search',$L['Search'],'i_sec'),'</td>
  <td>Id <input type="text" id="id" name="v" size="32" maxlength="50" value="'.($q=='id' || $q=='' ? $v : '').'"/></td>
  <td style="padding:7px; text-align:right">
  <input type="hidden" name="q" value="id"/>
  <input type="hidden" id="s_id" name="s_id" value="',$fs,'"/>
  <input type="hidden" id="t_id" name="t_id" value="',$ft,'"/>
  <input type="hidden" id="st_id" name="st_id" value="',$fst,'"/>
  <input type="submit" id="okid" name="ok" value="',$L['Search'],'"/>
  </td>
  </tr>
  </table>
  <br/>
  </form>
  ';

  // SEARCH BY KEY

  echo '<form method="post" id="form_kw" action="',Href(),'" onsubmit="return ValidateForm(this);">
  <table class="data_s">
  <tr class="data_s">
  <td class="tdicon">',AsImg($_SESSION[QT]['skin_dir'].'/ico_section_search.gif','search',$L['Search'],'i_sec'),'</td>
  <td>',$L['Search_by_key'],' <input type="text" id="kw" name="v" size="32" maxlength="64" value="'.($q=='kw' ? $v : '').'" onkeyup="qtKeypress(event,\'okkw\')"/> <span style="white-space:nowrap;"><input type="checkbox" name="v2" id="v_inp" ',(empty($v2) ? '' : QCHE),'><label for="v_inp">',L('Only_notes_in_process'),'</label></span></td>
  <td style="padding:7px; text-align:right">
  <input type="hidden" name="q" value="kw"/>
  <input type="hidden" id="s_kw" name="s_kw" value="',$fs,'"/>
  <input type="hidden" id="t_kw" name="t_kw" value="',$ft,'"/>
  <input type="hidden" id="st_kw" name="st_kw" value="',$fst,'"/>
  <input type="submit" id="okkw" name="ok" value="',$L['Search'],'"/>
  </td>
  </tr>
  </table>
  <br/>
  </form>
  ';

  // SEARCH BY TAGS

  echo '<form method="post" id="form_tag" action="',Href(),'" onsubmit="return ValidateForm(this);">
  <table class="data_s">
  <tr class="data_s">
  <td class="tdicon">',AsImg($_SESSION[QT]['skin_dir'].'/ico_section_search.gif','search',$L['Search'],'i_sec'),'</td>
  <td>',$L['With_tag'],' <input type="text" name="v" id="searchtag" size="32" value="'.($q=='tag' ? $v : '').'" onkeyup="qtKeypress(event,\'oktag\')" class="small"/></td>
  <td style="padding:7px; text-align:right">
  <input type="hidden" name="q" value="tag"/>
  <input type="hidden" id="s_tag" name="s_tag" value="',$fs,'"/>
  <input type="hidden" id="t_tag" name="t_tag" value="',$ft,'"/>
  <input type="hidden" id="st_tag" name="st_tag" value="',$fst,'"/>
  <input type="submit" id="oktag" name="ok" value="',$L['Search'],'"/>
  </td>
  </tr>
  </table>
  <br/>
  </form>
  ';

  // SEARCH BY ADRESS/DESCRIPTION

  echo '<form method="post" id="form_fld" action="',Href(),'" onsubmit="return ValidateForm(this);">
  <table class="data_s">
  <tr class="data_s">
  <td class="tdicon">',AsImg($_SESSION[QT]['skin_dir'].'/ico_section_search.gif','search',$L['Search'],'i_sec'),'</td>
  <td>',$L['Search_by_field'],' <select name="v2" id="v_fld">
  <option value="address"',($v2=='address' ? QSEL : ''),'>',$L['Address'],'</option>
  <option value="descr"',($v2=='descr' ? QSEL : ''),'>',$L['Description'],'</option>
  </select>&nbsp;<input type="text" name="v" id="fld" size="30" value="'.($q=='fld' ? $v : '').'" onkeyup="qtKeypress(event,\'okfld\')" class="small"/></td>
  <td style="padding:7px; text-align:right">
  <input type="hidden" name="q" value="fld"/>
  <input type="hidden" id="s_fld" name="s_fld" value="',$fs,'"/>
  <input type="hidden" id="t_fld" name="t_fld" value="',$ft,'"/>
  <input type="hidden" id="st_fld" name="st_fld" value="',$fst,'"/>
  <input type="submit" id="okfld" name="ok" value="',$L['Search'],'"/>
  </td>
  </tr>
  </table>
  </form>
  ';
  if ( $_SESSION[QT]['tags']!='0' ) echo '<p class="small">* ',$L['H_Search_criteria'],'</p>';

  echo '</div>
  ';
  break;

case 1:

  echo '<div class="searchcriteria">
  <h2>',$L['Search_criteria'],'</h2>
  ';

  // ERROR MESSAGE

  if ( !empty($error) ) echo '<p class="error">',$error,'</p>';

  // SEARCH BY DATE

  echo '<form method="post" id="form_date" action="',Href(),'" onsubmit="return ValidateForm(this);">
  <table class="data_s">
  <tr class="data_s">
  <td class="tdicon">',AsImg($_SESSION[QT]['skin_dir'].'/ico_section_search.gif','search',$L['Search'],'i_sec'),'</td>
  <td>',L('Creation_date'),' <select name="v2" id="v_date" style="min-width:80px">
  <option value="before"',($v2=='before' ? QSEL : ''),'>',L('date_before'),'</option>
  <option value="after"',($v2=='after' ? QSEL : ''),'>',L('date_after'),'</option>
  <option value="on"',($v2=='on' ? QSEL : ''),'>',L('date_on'),'</option>
  <option value="near"',($v2=='near' ? QSEL : ''),'>',L('date_near'),'</option>
  </select>&nbsp;<input type="text" style="min-width:80px" id="date" pattern="(([0-9]{4})|([0-9]{4}-(0[1-9]|1[012]))|([0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])))" name="v" size="10" value="'.($q=='date' ? $v : '').'" onkeyup="qtKeypress(event,\'okdate\')" />*</td>
  <td style="padding:7px; text-align:right">
  <input type="hidden" name="q" value="date"/>
  <input type="hidden" id="s_date" name="s_date" value="',$fs,'"/>
  <input type="hidden" name="tt" value="',$tt,'"/>
  <input type="hidden" id="t_date" name="t_date" value="',$ft,'"/>
  <input type="hidden" id="st_date" name="st_date" value="',$fst,'"/>
  <input type="submit" id="okdate" name="ok" value="',$L['Search'],'"/>
  </td>
  </tr>
  </table>
  <p class="small">* ',$L['H_datesearch'],'</p>
  <br/>
  </form>
  ';

  echo '</div>
  ';
  break;

case 2:

  echo '<div class="searchcriteria">
  <h2>',$L['Search_criteria'],'</h2>
  ';

  // ERROR MESSAGE

  if ( !empty($error) ) echo '<p class="error">',$error,'</p>';

  // SEARCH BY RELATION

  $str = L('or').' '.L('more');
  echo '<form method="post" id="form_rel" action="',Href(),'">
  <table class="data_s">
  <tr class="data_s">
  <td class="tdicon">',AsImg($_SESSION[QT]['skin_dir'].'/ico_section_search.gif','search',$L['Search'],'i_sec'),'</td>
  <td>',L('Having'),'&nbsp;<select name="v" id="v_rel" onchange="EnablePopup(\'v_dir\',\'v_rel\');" style="width:120px">
  <option value="0"',($q=='rel' && $v==='0' ? QSEL : ''),'>0</option>
  <option value="1"',($q=='rel' && $v==='1' ? QSEL : ''),'>1 (',L('exactly'),')</option>
  <option value="1*"',($q=='rel' && $v==='1*' ? QSEL : ''),'>1 ',$str,'</option>
  <option value="2*"',($q=='rel' && $v==='2*' ? QSEL : ''),'>2 ',$str,'</option>
  <option value="3*"',($q=='rel' && $v==='3*' ? QSEL : ''),'>3 ',$str,'</option>
  <option value="4*"',($q=='rel' && $v==='4*' ? QSEL : ''),'>4 ',$str,'</option>
  </select>&nbsp;',L('relation'),', ',L('direction'),'&nbsp;<select name="v2" id="v_dir"',($q=='rel' && $v2==='0' ? QDIS : ''),'>
  <option value="*"',($q=='rel' && $v2==='*' ? QSEL : ''),'></option>
  <optgroup label="',$L['Direction_specific'],'">
  <option value="0"',($q=='rel' && $v2==='0' ? QSEL : ''),'>&mdash; ',L('Direction0'),'</option>
  <option value="1"',($q=='rel' && $v2==='1' ? QSEL : ''),'>&rarr; ',L('Direction1'),'</option>
  <option value="2"',($q=='rel' && $v2==='2' ? QSEL : ''),'>&harr; ',L('Direction2'),'</option>
  <option value="-1"',($q=='rel' && $v2==='-1' ? QSEL : ''),'>&larr; ',L('Direction-1'),'</option>
  </optgroup>
  <optgroup label="',$L['Direction_multiple'],'">
  <option value="3"',($q=='rel' && $v2==='3' ? QSEL : ''),'>',L('Direction3'),'</option>
  <option value="4"',($q=='rel' && $v2==='4' ? QSEL : ''),'>',L('Direction4'),'</option>
  <option value="5"',($q=='rel' && $v2==='5' ? QSEL : ''),'>',L('Direction5'),'</option>
  <option value="6"',($q=='rel' && $v2==='6' ? QSEL : ''),'>',L('Direction6'),'</option>
  </optgroup>
  </select>
  </td>
  <td style="padding:7px; text-align:right">
  <input type="hidden" name="q" value="rel"/>
  <input type="hidden" name="tt" value="',$tt,'"/>
  <input type="hidden" id="s_rel" name="s_rel" value="',$fs,'"/>
  <input type="hidden" id="t_rel" name="t_rel" value="',$ft,'"/>
  <input type="hidden" id="st_rel" name="st_rel" value="',$fst,'"/>
  <input type="submit" id="okrel" name="ok" value="',$L['Search'],'"/>
  </td>
  </tr>
  </table>
  <br/>
  </form>
  ';

  // SEARCH BY SUBITEM

  $str = L('or').' '.L('more');
  $arr = explode(',',QNMCLASSES);
  $arrClasses = array();
  $arrClassesN = array();
  foreach($arr as $key) $arrClasses[$key]=cNE::Classname($key);
  foreach($arr as $key) $arrClassesN['-'.$key]=L('Not_class_'.$key);

  echo '<form method="post" id="form_sub" action="',Href(),'" onsubmit="return ValidateForm(this);">
  <table class="data_s">
  <tr class="data_s">
  <td class="tdicon">',AsImg($_SESSION[QT]['skin_dir'].'/ico_section_search.gif','search',$L['Search'],'i_sec'),'</td>
  <td>',L('Having'),'&nbsp;<select name="v" id="v_sub" onchange="EnablePopup(\'v_class\',\'v_sub\');" style="width:120px">
  <option value="0"',($q=='sub' && $v==='0' ? QSEL : ''),'>0</option>
  <option value="1"',($q=='sub' && $v==='1' ? QSEL : ''),'>1 (',L('exactly'),')</option>
  <option value="1*"',($q=='sub' && $v==='1*' ? QSEL : ''),'>1 ',$str,'</option>
  <option value="2*"',($q=='sub' && $v==='2*' ? QSEL : ''),'>2 ',$str,'</option>
  <option value="3*"',($q=='sub' && $v==='3*' ? QSEL : ''),'>3 ',$str,'</option>
  <option value="4*"',($q=='sub' && $v==='4*' ? QSEL : ''),'>4 ',$str,'</option>
  </select>&nbsp;',L('sub-item'),', ',L('class'),' <select name="v2" id="v_class"',($q=='sub' && $v2==='0' ? QDIS : ''),'>
  <option value="*"',($v2==='*' ? QSEL : ''),'></option>
  <optgroup label="',$L['Class_specific'],'">
  ';
  foreach ($arrClasses as $key=>$name) echo '<option value="',$key,'"',($q=='sub' && $v2===$key ? QSEL : ''),'>',$name,'</option>',PHP_EOL;
  echo '</optgroup>
  <optgroup label="',$L['Class_multiple'],'">
  ';
  foreach ($arrClassesN as $key=>$name) echo '<option value="',$key,'"',($q=='sub' && $v2===$key ? QSEL : ''),'>',$name,'</option>',PHP_EOL;
  echo '</optgroup>
  </select>
  </td>
  <td style="padding:7px; text-align:right">
  <input type="hidden" name="q" value="sub"/>
  <input type="hidden" name="tt" value="',$tt,'"/>
  <input type="hidden" id="s_sub" name="s_sub" value="',$fs,'"/>
  <input type="hidden" id="t_sub" name="t_sub" value="',$ft,'"/>
  <input type="hidden" id="st_sub" name="st_sub" value="',$fst,'"/>
  <input type="submit" id="oksub" name="ok" value="',$L['Search'],'"/>
  </td>
  </tr>
  </table>
  <br/>
  </form>
  ';

  echo '</div>
  ';
  break;

// -------
}
// -------

// END TAB PANEL

echo'</div>
';

// HTML END

if ( $q=='last' || $q=='user' ) $q='kw'; // in case of 'recent' or 'my notes', show at least kw criteria

echo '<script type="text/javascript">
<!--
document.getElementById("fok").style.display="none";
qtFocusEnd("id");
Running();
'.($tt=2 ? 'EnablePopup("v_dir","v_rel"); EnablePopup("v_class","v_sub");' : '').'
//-->
</script>
';

include 'qnm_inc_ft.php';
