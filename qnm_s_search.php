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
if ( !$oVIP->user->CanView('V5') ) HtmlPage(11);

// INITIALISE

$v = ''; QThttpvar('v','str');
$oVIP->selfurl = 'qnm_s_search.php';
$oVIP->selfname = $L['Search'];

// ---------
// SUBMITTED
// ---------

if ( isset($_POST['ok']) )
{
  // security check

  $v = $_POST['idtype']; if ( get_magic_quotes_gpc() ) $v = stripslashes($v);
  $v = strip_tags($v);
  if ( $v=='' ) $error = 'Id '.$L['E_invalid'];
  $v = str_replace(array(';','+'),',',trim($v));

  // read keys

  if ( empty($error) ) $oHtml->Redirect('qnm_items.php?q=qs&amp;v='.urlencode($v),$L['Search']);
}

// --------
// HTML START
// --------

if ( !empty($error) ) $_SESSION['pagedialog']='E|'.$error;

$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qnm_search.css" />';
$oHtml->scripts[] = '<script type="text/javascript">
<!--
var e0 = "'.L('No_result').'";
var e1 = "'.L('try_other_lettres').'";

$(function() {
  $( "#idtype" ).autocomplete({
    minLength: 1,
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_idtype.php",
        dataType: "json",
        data: { term: request.term, e0: e0, e1: e1 },
        success: function(data) { response(data); }
      });
    },
    focus: function( event, ui ) {
      $( "#idtype" ).val( ui.item.rItem );
      return false;
    },
    select: function( event, ui ) {
      $( "#idtype" ).val( (ui.item.rInfo=="*" ? "Type:"+ui.item.rItem : ui.item.rInfo) );
      return false;
    }
  })
  .data( "autocomplete" )._renderItem = function( ul, item ) {
    return $( "<li></li>" )
      .data( "item.autocomplete", item )
      .append( "<a class=\"jvalue\">" + (item.rInfo=="*" ? "Type: "+item.rItem : item.rItem ) + (item.rInfo=="" || item.rInfo=="*"  ? "" : " &nbsp;<span class=\"jinfo\">(" + item.rInfo + ")</span>") + "</a>" )
      .appendTo( ul );
  };
});

function ValidateForm(theForm)
{
  if (theForm.idtype.value.length==0) { alert("'.$L['Missing'].'"); return false; }
  return true;
}
//-->
</script>
';

include 'qnm_inc_hd.php';
include 'qnm_inc_menu.php';

// SIMPLE SEARCH

echo '<h2>',$L['Search'],'</h2>
<form method="post" id="s_search" action="',Href(),'" onsubmit="return ValidateForm(this);">
<table class="data_s">
<tr class="data_s">
<td class="tdicon">',AsImg($_SESSION[QT]['skin_dir'].'/ico_section_search.gif','search',$L['Search'],'i_sec'),'</td>
<td><input type="text" id="idtype" name="idtype" size="30" maxlength="64" value="',$v,'"/>&nbsp;<input type="submit" id="ok" name="ok" value="',$L['Ok'],'"/><span class="small"> '.$L['H_Search'].'</span></td>
<td style="padding:7px; text-align:right"><a href="qnm_search.php">',$L['Advanced_search'],'...</a></td>
</tr>
</table>
</form>
';

if ( !empty($error) ) echo '<p class="error">',$error,'</p>';

// HTML END

echo '<script type="text/javascript">
<!--
qtFocusEnd("idtype");
//-->
</script>';

include 'qnm_inc_ft.php';