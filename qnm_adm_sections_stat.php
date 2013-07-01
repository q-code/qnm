<?php

/**
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license.  If you did not receive a copy of
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
if ( $oVIP->user->role!='A' ) die($L['R_admin']);
include Translate('qnm_adm.php');

// ---------
// INITIALISE
// ---------

$oVIP->selfurl = 'qnm_adm_sections_stat.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br />'.$L['Sections'].'<br />'.$L['Update_stats'];
$oVIP->exiturl = 'qnm_adm_items.php';
$oVIP->exitname = '&laquo; '.$L['Items'];

$arrDomains = GetDomains();
if ( count($arrDomains)>50 ) { $warning='You have too much domains. Try to remove unused domains.'; $_SESSION['pagedialog'] = 'W|'.$warning; }

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

// ---------
// INITIALISE (after update to show new values)
// ---------

$arrSections = GetSections('A',-2); // Optimisation: get all sections at once (grouped by domain)
$intSections=0;
foreach($arrSections as $arr) $intSections += count($arr);
if ( $intSections>100 ) { $warning='You have too much sections. Try to remove unused sections.'; $_SESSION['pagedialog'] = 'W|'.$warning; }

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qnm_table.js"></script>
<script type="text/javascript">
<!--
$(document).ready(function() {
  $(".checkboxdomain").click(function() { qtCheckboxAll(this.id,this.id+"[]",true); }); // false  when no row hightlight
  $(".checkboxsection").click(function() { qtHighlight("tr_"+this.id,this.checked); }); // delete when no row hightlight
  $("#t1 td:not(.tdcheckbox)").click(function() { qtCheckboxToggle(this.parentNode.id.substring(3)); });

});
//-->
</script>
';

include 'qnm_adm_inc_hd.php';

echo '<form method="post" action="qnm_adm_sections_stat.php">
<p style="margin:4px 0"><img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:bottom;margin:2px 10px 0 15px" alt="|" />',$L['Selection'],': <input type="submit" class="small" name="ok" value="',$L['Update_stats'],'" /></p>
<table id="t1" class="data_o">
<tr class="data_o">
<th>&nbsp;</th>
<th colspan="2" style="text-align:left">',$L['Domain'],'/',$L['Section'],'</th>
<th class="center">',$L['Items'],'</th>
<th class="center">',$L['Messages'],'</th>
<th class="center">',$L['Tags'],'</th>
</tr>
';

$i=0;
foreach($arrDomains as $intDomain=>$strDomain)
{
  if ( isset($arrSections[$intDomain]) ) {
  if ( count($arrSections[$intDomain])>0 ) {

    echo '<tr class="data_o" id="tr_domain'.$intDomain.'">',PHP_EOL;
    echo '<td class="colgroup tdcheckbox"><input type="checkbox" id="domain'.$intDomain.'" class="checkboxdomain"',(count($arrSections[$intDomain])<1 ? ' style="display:none"' : ''),' /></td>',PHP_EOL;
    echo '<td class="colgroup" colspan="2">',$strDomain,'</td>',PHP_EOL;
    echo '<td class="colgroup">&nbsp;</td>',PHP_EOL;
    echo '<td class="colgroup">&nbsp;</td>',PHP_EOL;
    echo '<td class="colgroup">&nbsp;</td>',PHP_EOL;
    echo '</tr>',PHP_EOL;
    $i += 1;
    $j = 0;
    foreach($arrSections[$intDomain] as $arrSection)
    {
      $oSEC = new cSection($arrSection);
      echo '<tr class="rowlight" id="tr_section'.$oSEC->uid.'">',PHP_EOL;
      echo '<td class="tdcheckbox"><input type="checkbox" class="checkboxsection" name="domain'.$intDomain.'[]" id="section'.$oSEC->uid.'" value="'.$oSEC->uid.'" onclick="qtCheckboxOne(\'domain'.$intDomain.'[]\',\'domain'.$intDomain.'\');" /></td>',PHP_EOL;
      echo '<td class="center">',AsImg($_SESSION[QT]['skin_dir'].'/ico_section_'.$oSEC->type.'_'.$oSEC->status.'.gif','[+]',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'i_sec20'),'</td>';
      echo '<td><span class="bold">',$oSEC->name,'</span> &middot; <span class="small">',$L['Section_type'][$oSEC->type],($oSEC->status=='1' ? '<span class="small"> ('.$L['Section_status'][1].')</span>' : ''),'</span></td>';
      echo '<td class="center" title="',L('Item',$oSEC->items),($oSEC->items>0 ? ' ('.L('inactive',$oSEC->StatsGet('itemsZ')).')' : ''),'">',$oSEC->items,($oSEC->items>0 ? ' <span class="disabled">('.$oSEC->StatsGet('itemsZ').')' : ''),'</td>',PHP_EOL;
      echo '<td class="center" title="',L('Message',$oSEC->StatsGet('notes')),($oSEC->StatsGet('notes')>0 ? ' ('.L('closed',$oSEC->StatsGet('notesZ')).')' :''),'">',$oSEC->StatsGet('notes'),($oSEC->StatsGet('notes')>0 ? ' <span class="disabled">('.$oSEC->StatsGet('notesZ').')</span>' : ''),'</td>',PHP_EOL;
      echo '<td class="center">',$oSEC->tags,'</td>',PHP_EOL;
      echo '</tr>',PHP_EOL;
    }

  }}
}
echo '</table>
';
if ( $intSections>3 ) echo '<p style="margin:4px 0"><img src="admin/selection_down.gif" style="width:10px;height:10px;vertical-align:top;margin:2px 10px 0 15px" alt="|" />',$L['Selection'],': <input type="submit" class="small" name="ok" value="',$L['Update_stats'],'" /></p>
';
echo '</form>
<p><a href="',$oVIP->exiturl,'">',$oVIP->exitname,'</a></p>
';

// --------
// HTML END
// --------

include 'qnm_adm_inc_ft.php';