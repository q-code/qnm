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

$oVIP->selfurl = 'qnm_index.php';
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qnm_index.css" /><link rel="stylesheet" type="text/css" href="bin/css/qnm_index_prt.css" media="print" />';

// --------
// SECURITY
// --------

if ( $_SESSION[QT]['board_offline']=='1' ) HtmlPage(99);
if ( $_SESSION[QT]['visitor_right']<1 && $oVIP->user->role=='V' ) HtmlPage(11);

// --------
// INITIALIZE
// --------

if ( isset($_SESSION[QT]['section']) ) unset($_SESSION[QT]['section']); // previous section

// --------
// HTML START
// --------

include 'qnm_inc_hd.php';

// Optimising queries: Search all lastpost and Sections attributes

$arrLastPostId = array();
$oDB->Query( 'SELECT section,MAX(id) as maxid FROM '.TABPOST.' WHERE status>=0 GROUP BY section' );
while($row = $oDB->Getrow()) $arrLastPostId[(int)$row['section']] = (int)$row['maxid'];
$arrSections = GetSections($oVIP->user->role,-2); // Get all sections at once (grouped by domain)

// --------
// DOMAIN / SECTIONS
// --------

$table = new cTable('','data_s');
$table->th[0] = new cTableHead('&nbsp;','','thicon');
$table->th[1] = new cTableHead('&nbsp;','','thsection');
$table->th[2] = new cTableHead($L['Items'],'','thitems');
$table->th[3] = new cTableHead($L['Messages'],'','thnotes');
$table->th[4] = new cTableHead($L['Last_message'],'','thissue');
$table->td[0] = new cTableData('','','tdicon');
$table->td[1] = new cTableData('','','tdsection');
$table->td[2] = new cTableData('','','tditems');
$table->td[3] = new cTableData('','','tdnotes');
$table->td[4] = new cTableData('','','tdissue');

$intDom = 0;
$intSec = 0;
$intSumItems = 0; // sum of items (for visible section)
$intSumNotes = 0; // sum of notes in process (for visible sections)
foreach($oVIP->domains as $intDomid=>$strDomtitle)
{
  if ( isset($arrSections[$intDomid]) ) {
  if ( count($arrSections[$intDomid])>0 ) {

    $intDom++;
    if ( $intDom>1 ) echo '<div class="dom_separator"></div>',PHP_EOL;
    echo '<!-- domain ',$intDomid,': ',$strDomtitle,' -->',PHP_EOL;
    $table->row = new cTableRow('', 'data_s');
    echo $table->Start().PHP_EOL;
    echo '<thead>',PHP_EOL;
    $table->th[1]->content = $strDomtitle;
    echo $table->GetTHrow().PHP_EOL;
    echo '</thead>',PHP_EOL;
    echo '<tbody>',PHP_EOL;

    // SHOW SECTIONS

    $strAlt='r1';

    foreach($arrSections[$intDomid] as $intSection=>$arrSection)
    {
      $intSec++;
      $oSEC = new cSection($arrSection,(isset($arrLastPostId[$intSection]) ? $arrLastPostId[$intSection] : false)); //use query optimisation
      $strFilters = '';
      if ( $oSEC->items>25 )
      {
      $arrFilters = $oSEC->GetFilter();
      foreach($arrFilters as $key=>$str) $arrFilters[$key] = '<a class="sectionfilter" href="'.Href('qnm_items.php?s='.$oSEC->uid).'&amp;ft='.urlencode($str).'" title="'.L('Show').'">'.$str.'</a>';
      $strFilters = implode('&nbsp; ',$arrFilters);
      }
      $intSumItems += $oSEC->items;
      $intSumNotes += $oSEC->StatsGet('notes');
      $strLastpost = '&nbsp;';
      if ( !empty($oSEC->lastpost) ) $strLastpost = QTdatestr($oSEC->lastpost['issuedate'],'$','$',true,true,true).' <a href="'.Href('qnm_item.php').'?nid='.$oSEC->lastpost['nid'].(isset($arrLastPostId[$intSection]) ? '&amp;note='.$arrLastPostId[$intSection] : '').'#notes" title="'.$oSEC->lastpost['preview'].'">'.$L['Goto_message'].'</a><br/>'.L('by').' <a href="'.Href('qnm_user.php').'?id='.$oSEC->lastpost['userid'].'" title="'.$L['Ico_user_p'].'" class="small">'.$oSEC->lastpost['username'].'</a>';

      $table->row = new cTableRow('', 'data_s '.$strAlt);
      $table->td[0]->content = AsImg($oSEC->GetLogo(),'F',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'i_sec','',Href('qnm_items.php?s='.$oSEC->uid));
      $table->td[1]->content = '<a class="section" href="'.Href('qnm_items.php?s='.$oSEC->uid).'">'.$oSEC->name.'</a> &nbsp; '.$strFilters.(empty($oSEC->descr) ? '' : '<br/><span class="sectiondesc">'.$oSEC->descr.'</span>');
      $table->td[2]->content = $oSEC->items.'&nbsp;'.($oSEC->StatsGet('itemsZ')==0 ?  '<span class="activebull" title="'.L('inactive',0).'">&nbsp;&bull;&nbsp;</span>' : '<span class="inactivebull" title="'.L('inactive',$oSEC->StatsGet('itemsZ')).'">&nbsp;&bull;&nbsp;</span>');
      $nA = $oSEC->StatsGet('notesA');
      $nZ = $oSEC->StatsGet('notesZ');
      $table->td[3]->content = ($nA>0 ? cPost::IconMaker(1,'note','','i_note').($nA>=QNM_SEVERAL_NOTES ? '!' : '') : '&nbsp;');
      $table->td[3]->Add('title', L('in_process',$nA).', '.L('closed',$nZ));

      $table->td[4]->content = ($oSEC->items<1 ? '&nbsp;' : $strLastpost);
      echo $table->GetTDrow().PHP_EOL;
      if ( $strAlt=='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
    }
    echo '</tbody>',PHP_EOL;
    echo '</table>',PHP_EOL;

  }}
}

// No public section

if ( $intSec==0 ) echo '<p>',($oVIP->user->role=='V' ? $L['E_no_public_section'] : $L['E_no_visible_section']),'</p>';

// --------
// HTML END
// --------

if ( isset($oSEC) ) unset($oSEC);
include 'qnm_inc_ft.php';
