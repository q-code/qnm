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
* @version    1.0 build:20120518
*/

session_start();
require_once 'bin/qnm_init.php';
require_once 'bin/qnm_fn_admin.php';
include Translate('qnm_adm.php');

if ( $oVIP->user->role!='A' ) die(Error(13));

// INITIALISE

$a='';
$d=-1;
$s=-1;
QThttpvar('a d s','str int int');

$oVIP->selfurl = 'qnm_adm_sections.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Sections'];

// --------
// SUBMITTED
// --------

// REODER DOMAINS/SECTION (enabled by java drag and drop)

if ( isset($_POST['neworder']) )
{
  $arrO = explode(';',$_POST['neworder']); // format of the domain id is "dom_{i}"
  if ( count($arrO)>1 )
  {
    switch(substr($arrO[0],0,3))
    {
      case 'dom': foreach($arrO as $intKey=>$strId) $oDB->Query( 'UPDATE '.TABDOMAIN.' SET titleorder='.$intKey.' WHERE uid='.substr($strId,4) ); break;
      case 'sec': foreach($arrO as $intKey=>$strId) $oDB->Query( 'UPDATE '.TABSECTION.' SET titleorder='.$intKey.' WHERE uid='.substr($strId,4) ); break;
      default: die("invalid command");
    }
    if ( isset($_SESSION[QT]['sys_domains']) ) Unset($_SESSION[QT]['sys_domains']);
    if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
    $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
  }
}

// ADD DOMAIN

if ( isset($_POST['add_dom']) )
{

  $oGP = new cGetPost($_POST['title'],64);
  if ( empty($oGP->e) ) $error = $L['Domain'].'/'.$L['Section'].' '.Error(1);

  if ( empty($error) )
  {
    require_once 'bin/class/qnm_class_dom.php';
    cDomain::Create($oGP->e,-1);
    if ( isset($_SESSION[QT]['sys_domains']) ) Unset($_SESSION[QT]['sys_domains']);
    if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
    $_SESSION['pagedialog'] = 'O|'.$L['S_insert'];
  }
  else
  {
    $_SESSION['pagedialog'] = 'E|'.$error;
  }
}

// ADD SECTION

if ( isset($_POST['add_sec']) )
{

  $oGP = new cGetPost($_POST['title'],64);
  if ( empty($oGP->e) ) $error = $L['Domain'].'/'.$L['Section'].' '.Error(1);

  // Add section
  if ( empty($error) )
  {
    cSection::Create($oGP->e,intval($_POST['indomain']));
    if ( isset($_SESSION[QT]['sys_domains']) ) Unset($_SESSION[QT]['sys_domains']);
    if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
    $_SESSION['pagedialog'] = 'O|'.$L['S_insert'];
  }
  else
  {
    $_SESSION['pagedialog'] = 'E|'.$error;
  }
}

// Move domain/section

if ( !empty($a) )
{
  if ( $a=='d_up' || $a=='d_down' )
  {
    $oDB->Query('SELECT uid FROM '.TABDOMAIN.' ORDER BY titleorder');
    $arrList = array();
    while($row=$oDB->Getrow()) $arrList[]=intval($row['uid']);
    $arrO = array_values(arrShift($arrList,$d,substr($a,2)));
    foreach($arrO as $intKey=>$intId) $oDB->Query('UPDATE '.TABDOMAIN.' SET titleorder='.$intKey.' WHERE uid='.$intId);
    if ( isset($_SESSION[QT]['sys_domains']) ) Unset($_SESSION[QT]['sys_domains']);
    if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
  }
  if ( $a=='f_up' || $a=='f_down' )
  {
    $oDB->Query('SELECT uid FROM '.TABSECTION.' WHERE pid='.$d.' ORDER BY titleorder');
    $arrList = array();
    while($row=$oDB->Getrow()) $arrList[]=intval($row['uid']);
    $arrO = array_values(arrShift($arrList,$s,substr($a,2)));
    foreach($arrO as $intKey=>$intId) $oDB->Query('UPDATE '.TABSECTION.' SET titleorder='.$intKey.' WHERE uid='.$intId);
    if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
  }
}

// --------
// HTML START
// --------

$arrDomains = GetDomains();
if ( count($arrDomains)>50 ) { $warning='You have too much domains. Try to remove unused domains.'; $_SESSION['pagedialog'] = 'W|'.$warning; }
$arrSections = GetSections('A',-2); // Optimisation: get all sections at once (grouped by domain)
if ( count($arrSections)>100 ) { $warning='You have too much sections. Try to remove unused sections.'; $_SESSION['pagedialog'] = 'W|'.$warning; }

$oHtml->scripts[] = '<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Domain'].'/'.$L['Section'].'")); return false; }
  return null;
}
function ToggleForms()
{
  if ( document.getElementById("adddomain").style.display=="none" )
  {
  document.getElementById("adddomain").style.display="block";
  document.getElementById("addsection").style.display="block";
  }
  else
  {
  document.getElementById("adddomain").style.display="none";
  document.getElementById("addsection").style.display="none";
  }
}

function orderbox(b)
{
  var doc = document;
  doc.getElementById("domorderbox").style.display=(b ? "block" : "none");
}

$(function() {

  // Return a helper with preserved width of cells
  var fixHelper = function(e, ui) {
    ui.children().each(function() {
      $(this).width($(this).width());
    });
    return ui;
  };

  $("tbody.sortable").sortable({
    items:"tr",
    handle:"td:first",
    helper: fixHelper,
    axis: "y",
    containment:"parent",
    cursor: "n-resize",
    tolerance:"pointer",
    update: function(e,ui) {
      var arrOrder = ui.item.parent().sortable("toArray");
      document.getElementById("neworder").value=arrOrder.join(";");
      document.getElementById("neworder_save").click();
    }
  }).disableSelection();

});
//-->
</script>
';

include 'qnm_adm_inc_hd.php';

echo '
<p style="text-align:right"><a id="toggleforms" href="qnm_adm_sections.php" onclick="ToggleForms(); return false;">',$L['Add'],' ',$L['Domain'],'/',$L['Section'],'...</a></p>
<form id="adddomain" method="post" action="qnm_adm_sections.php" onsubmit="return ValidateForm(this);">
<table class="data_o">
<tr class="data_o">
<td class="colgroup" style="width:120px;"><label for="domain">',$L['Domain_add'],'</label></td>
<td class="colgroup"><input id="domain" name="title" type="text" size="30" maxlength="64"/></td>
<td class="colgroup" style="width:50px;"><input id="add_dom" name="add_dom" type="submit" value="',$L['Add'],'"/></td>
</tr>
</table>
</form>
<form id="addsection" method="post" action="qnm_adm_sections.php" onsubmit="return ValidateForm(this);">
<table class="data_o" style="margin-bottom:10px">
<tr class="data_s">
<td style="width:120px;"><label for="section">',$L['Section_add'],'</label></td>
<td><input id="section" name="title" type="text" size="30" maxlength="64" class="small"/> <span class="small">',L('in_domain'),'</span> <select name="indomain" size="1" class="small">',QTasTag($arrDomains),'</select></td>
<td style="width:50px;"><input name="add_sec" type="submit" value="',$L['Add'],'"/></td>
</tr>
</table>
</form>
';
if ( !isset($_POST['title']) ) echo '<script type="text/javascript">ToggleForms();</script>';

echo '
<table class="data_o">
<tr class="data_s">
<th>&nbsp;</th>
<th style="text-align:left" colspan="2">',$L['Domain'],'/',$L['Section'],'</th>
<th>',$L['Type'],'</th>
<th>',$L['Userrole_c'],'</th>
<th class="center">',$L['Action'],'</th>
<th class="center">',$L['Move'],'</th>
</tr>
';

$i=0;
$bSortableDomains = count($arrDomains)>1;
foreach($arrDomains as $intDomain=>$strDomain)
{
  echo '<tr class="data_o">',PHP_EOL;
  echo '<td class="colgroup">',($bSortableDomains ? '<span class="draghandler" title="'.L('Move').'" onmousedown="orderbox(true); return false;">&nbsp;</span>' : '&nbsp;'),'</td>',PHP_EOL;
  echo '<td class="colgroup" colspan="2">',$strDomain,'</td>',PHP_EOL;
  echo '<td class="colgroup">&nbsp;</td>',PHP_EOL;
  echo '<td class="colgroup">&nbsp;</td>',PHP_EOL;
  echo '<td class="colgroup" style="text-align:center"><a class="small" href="qnm_adm_domain.php?d=',$intDomain,'">',$L['Edit'],'</a>';
  echo ' &middot; ',($intDomain==0 ? '<span class="disabled">'.$L['Delete'].'</span>' : '<a class="small" href="qnm_adm_change.php?a=deletedomain&amp;d='.$intDomain.'">'.$L['Delete'].'</a>'),'</td>';
  echo '<td class="colgroup" style="text-align:center;">';
  $strUp = '<img class="ctrl disabled" src="admin/ico_up.gif" alt="up"/>';
  $strDw = '<img class="ctrl disabled" src="admin/ico_dw.gif" alt="down"/>';
  if ( count($arrDomains)>1 )
  {
    if ( $i>0 ) $strUp = '<a class="popup_ctrl" href="qnm_adm_sections.php?d='.$intDomain.'&amp;a=d_up"><img class="ctrl" src="admin/ico_up.gif" alt="up" title="'.L('Up').'"/></a>';
    if ( $i<count($arrDomains)-1 ) $strDw = '<a class="popup_ctrl" href="qnm_adm_sections.php?d='.$intDomain.'&amp;a=d_down"><img class="ctrl" src="admin/ico_dw.gif" alt="dw" title="'.L('Down').'"/></a>';
  }
  echo $strUp.'&nbsp;'.$strDw;
  echo '</td>',PHP_EOL;
  echo '</tr>',PHP_EOL;

  $i++;
  $j = 0;

  if ( isset($arrSections[$intDomain]) ) {
  if ( count($arrSections[$intDomain])>0 ) {

    $bSortable = count($arrSections[$intDomain])>1;

    echo '<tbody ',($bSortable ? ' class="sortable"' : ''),'>',PHP_EOL;
    foreach($arrSections[$intDomain] as $intSecid=>$arrSection)
    {
      $oSEC = new cSection($arrSection);
      $strUp = '<img class="ctrl disabled" src="admin/ico_up.gif" alt="up"/>';
      $strDw = '<img class="ctrl disabled" src="admin/ico_dw.gif" alt="down"/>';
      echo '<tr class="data_o rowlight" id="sec_'.$oSEC->uid.'">';
      echo '<td>',($bSortable ? '<span class="draghandler" title="'.L('Move').'">&nbsp;</span>' : '&nbsp;'),'</td>',PHP_EOL;
      echo '<td class="center">',AsImg($oSEC->GetLogo(),'S',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'i_sec'),'</td>';
      echo '<td><a class="bold" href="qnm_adm_section.php?s=',$oSEC->uid,'">',$oSEC->name,'</a></td>';
      echo '<td><span class="small">',$L['Section_type'][$oSEC->type],($oSEC->status==1 ? ', '.strtolower($L['Section_status'][1]) : ''),'</span></td>';
      echo '<td>',$oSEC->modname,'</td>';
      echo '<td class="center"><a class="small" href="qnm_adm_section.php?s=',$oSEC->uid,'">',$L['Edit'],'</a>';
      echo ' &middot; ',($intSecid==0 ? '<span class="disabled">'.$L['Delete'].'</span>' : '<a class="small" href="qnm_adm_change.php?a=deletesection&amp;s='.$intSecid.'">'.$L['Delete'].'</a>'),'</td>';
      echo '<td class="center">';
      if ( count($arrSections[$intDomain])>1 )
      {
        if ( $j>0 ) $strUp = '<a href="qnm_adm_sections.php?d='.$intDomain.'&amp;s='.$intSecid.'&amp;a=f_up"><img class="ctrl" src="admin/ico_up.gif" alt="up" title="'.L('Up').'"/></a>';
        if ( $j<count($arrSections[$intDomain])-1 ) $strDw = '<a href="qnm_adm_sections.php?d='.$intDomain.'&amp;s='.$intSecid.'&amp;a=f_down"><img class="ctrl" src="admin/ico_dw.gif" alt="dw" title="'.L('Down').'"/></a>';
      }
      echo $strUp.'&nbsp;'.$strDw;
      $j++;
      echo '</td></tr>',PHP_EOL;
    }

  }}
  echo '</tbody>',PHP_EOL;
}

echo '</table>
';

// DOMAIN ORDER TOOL

if ( count($arrDomains)>1 )
{
echo '
<div id="domorderbox">
<p class="top">Reorder domains<br/>(drag and drop to reorder)</p>
<ul id="domorder">
';
foreach($arrDomains as $intDomain=>$strDomain) echo '<li id="dom_'.$intDomain.'" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>',(strlen($strDomain)>20 ? substr($strDomain,0,19).'...' : $strDomain),'</li>',PHP_EOL;
echo '</ul>

<form id="form_order" method="post" action="qnm_adm_sections.php">
<p class="bottom"><input type="hidden" name="neworder" id="neworder" value="" /><input type="submit" id="neworder_save" name="neworder_save" value="Save" /><input type="button" name="neworder_cancel" value="Cancel" onclick="orderbox(false);"/></p>
</form>
</div>
<script type="text/javascript">
$(document).ready(function(){

  $("#domorder").sortable({
    axis: "y",
    cursor: "n-resize",
    containment: "parent",
    tolerance:"pointer",
    update: function() {
      var arrOrder = $("#domorder").sortable("toArray");
      document.getElementById("neworder").value=arrOrder.join(";");
    }
  }).disableSelection();
});
</script>
';
}

include 'qnm_adm_inc_ft.php';