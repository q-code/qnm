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
include 'bin/qnm_fn_sql.php';

if ( $oVIP->user->role!='A' ) die(Error(13));

// INITIALISE

$oVIP->selfurl = 'qnm_adm_items.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Items'];

$arrDomains = GetDomains();
if ( count($arrDomains)>50 ) $_SESSION['pagedialog']='W|You have too much domains. Try to remove unused domains.';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  $arr = array();
  foreach(array_keys($arrDomains) as $id)
  {
  if ( isset($_POST['domain'.$id]) ) $arr = array_merge($arr,$_POST['domain'.$id]);
  }
  if ( count($arr)>0 )
  {
    foreach($arr as $id) { $voidSEC=new cSection(); $voidSEC->uid=$id; $voidSEC->UpdateStats(); }
    $_SESSION['pagedialog'] = 'O|'.L('Section',count($arr)).'. '.$L['S_update'];
  }
  else
  {
    $_SESSION['pagedialog'] = 'W|'.L('E_nothing_selected');
  }
}

// INITIALISE

$arrSections = GetSections('A',-2); // Optimisation: get all sections at once (grouped by domain)
$intSections=0;
foreach($arrSections as $arr) $intSections += count($arr);
if ( $intSections>100 ) { $warning='You have too much sections. Try to remove unused sections.'; $_SESSION['pagedialog']='W|'.$warning; }

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qnm_table.js"></script>
<script type="text/javascript">
<!--
$(document).ready(function() {
  $(".checkboxdomain").click(function() { qtCheckboxAll(this.id,this.id+"[]",true); }); // false  when no row hightlight
  $(".checkboxsection").click(function() { qtHighlight("tr_"+this.id,this.checked); }); // delete when no row hightlight
  $("#t1 td:not(.tdcheckbox,.tdaction)").click(function() { qtCheckboxToggle(this.parentNode.id.substring(3)); });
});
//-->
</script>
';

include 'qnm_adm_inc_hd.php';

echo '<form method="post" action="'.$oVIP->selfurl.'">
<p style="margin:4px 0"><img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:bottom;margin:2px 10px 0 15px" alt="|" />',$L['Selection'],': <input type="submit" class="small" name="ok" value="',$L['Update_stats'],'" /></p>
<table id="t1" class="data_o">
<tr class="data_o">
<th>&nbsp;</th>
<th style="text-align:left" colspan="2">',$L['Domain'],'/',$L['Section'],'</th>
<th style="text-align:center">',L('Items'),'</th>
<th style="text-align:center">',L('Messages'),'</th>
<th style="text-align:center">',L('Documents'),'</th>
<th style="text-align:center">',L('Deleted'),'</th>
<th style="text-align:center">',L('Action'),'</td>
</tr>
';

foreach($arrDomains as $intDomain=>$strDomain)
{
  if ( isset($arrSections[$intDomain]) ) {
  if ( count($arrSections[$intDomain])>0 ) {

    echo '<tr class="data_o" id="tr_domain'.$intDomain.'">',PHP_EOL;
    echo '<td class="colgroup tdcheckbox"><input type="checkbox" id="domain'.$intDomain.'" class="checkboxdomain"',(count($arrSections[$intDomain])<1 ? ' style="display:none"' : ''),' /></td>',PHP_EOL;
    echo '<td class="colgroup" colspan="8">',$strDomain,'</td>',PHP_EOL;
    echo '</tr>',PHP_EOL;

    foreach($arrSections[$intDomain] as $arrSection)
    {
      $oSEC = new cSection($arrSection);
      $iDocs = cSection::CountItems($oSEC->uid,'docs');
      $iItemsX = cSection::CountItems($oSEC->uid,'itemsX');
      echo '<tr class="rowlight" id="tr_section'.$oSEC->uid.'">',PHP_EOL;
      echo '<td class="tdcheckbox"><input type="checkbox" class="checkboxsection" name="domain'.$intDomain.'[]" id="section'.$oSEC->uid.'" value="'.$oSEC->uid.'" onclick="qtCheckboxOne(\'domain'.$intDomain.'[]\',\'domain'.$intDomain.'\');" /></td>',PHP_EOL;
      echo '<td style="width:40px;text-align:center">',AsImg($oSEC->GetLogo(),'S',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'i_sec'),'</td>',PHP_EOL;
      echo '<td><span class="bold">',$oSEC->name,'</span><br/><span class="small">',$L['Section_type'][$oSEC->type],($oSEC->status==1 ? '('.$L['Section_status'][1].')' : ''),'</span></td>',PHP_EOL;
      $i=$oSEC->StatsGet('itemsZ');
      echo '<td class="center" title="',L('Item',$oSEC->items),($oSEC->items>0 ? ' ('.L('active',$oSEC->items-$i).', '.L('inactive',$i).')' : ''),'">',$oSEC->items,($oSEC->items>0 ? ' <span class="disabled">('.$oSEC->StatsGet('itemsZ').')</span>' : ''),'</td>',PHP_EOL;
      echo '<td class="center" title="',L('Message',$oSEC->StatsGet('notes')),($oSEC->StatsGet('notes')>0 ? ' ('.L('in_process',$oSEC->StatsGet('notesA')).', '.L('closed',$oSEC->StatsGet('notesZ')).')' :''),'">',$oSEC->StatsGet('notes'),($oSEC->StatsGet('notes')>0 ? ' <span class="disabled">('.$oSEC->StatsGet('notesZ').')</span>' : ''),'</td>',PHP_EOL;
      echo '<td class="center">',$iDocs,'</td>',PHP_EOL;
      echo '<td class="center">',$iItemsX,'</td>',PHP_EOL;
      echo '<td class="tdaction center">';
      if ( $oSEC->items>0 ) { echo '<a class="small" href="qnm_adm_change.php?a=itemmoveall&amp;s=',$oSEC->uid,'">',L('Move'),'</a> &middot; '; } else { echo '<span class="disabled">',L('Move'),'</span> &middot; '; }
      if ( $oSEC->items>0 ) { echo '<a class="small" href="qnm_adm_change.php?a=itemdeleteall&amp;s=',$oSEC->uid,'">',L('Delete'),'</a><br />'; } else { echo '<span class="disabled">',L('Delete'),'</span><br />'; }
      if ( $iItemsX>0 ) { echo '<a class="small" href="qnm_adm_change.php?a=itemprugeall&amp;s=',$oSEC->uid,'">',L('Purge'),'</a> &middot; '; } else { echo '<span class="disabled">',L('Purge'),'</span> &middot; '; }
      if ( $iItemsX>0 ) { echo '<a class="small" href="qnm_adm_change.php?a=itemundeleteall&amp;s=',$oSEC->uid,'">',L('Restore'),'</a>'; } else { echo '<span class="disabled">',L('Restore'),'</span>'; }
      echo '</td>',PHP_EOL;
      echo '</tr>',PHP_EOL;
    }
  }}
}

echo '</table>
';
if ( $intSections>3 ) echo '<p style="margin:4px 0"><img src="admin/selection_down.gif" style="width:10px;height:10px;vertical-align:top;margin:2px 10px 0 15px" alt="|" />',$L['Selection'],': <input type="submit" class="small" name="ok" value="',$L['Update_stats'],'" /></p>
';
echo '</form>
';

// HTML END

include 'qnm_adm_inc_ft.php';