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
include Translate('qnm_adm.php');

if ( $oVIP->user->role!='A' ) die(Error(13));

// DEFINE LANG SET TO EDIT

$tt='en';
if ( isset($_GET['tt']) ) $tt = strip_tags($_GET['tt']);

// INITIALISE

$oVIP->selfurl = 'qnm_adm_tags.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Tags'];

// --------
// SUBMITTED
// --------

$a = '';
$strFile = '';
$strSection = '*';

if ( isset($_GET['s']) ) $strSection = strip_tags($_GET['s']);
if ( isset($_GET['a']) ) $a = strip_tags($_GET['a']);

// --------
// HTML START
// --------

include 'qnm_adm_inc_hd.php';

// CONTENT

$arrDomains = GetDomains();
$arrTabs = array();
if ( file_exists('bin/qnm_lang.php') )
{
  include 'bin/qnm_lang.php';
  foreach($arrLang as $strKey=>$arrDef)
  {
    $arrTabs[$strKey]=$arrDef[1];
  }
}
else
{
  $arrTabs = array('*'=>'No language file');
}

// DISPLAY TABS

echo HtmlTabs($arrTabs, $oVIP->selfurl, $tt, 6, $L['E_editing']);

// DISPLAY TAB PANEL

echo '<div class="pan">
<div class="pan_top">',$L['Edit'],': ',$arrTabs[$tt],'</div>
';

echo '<table class="data_o">
<tr class="data_o">
<th colspan="2">',$L['Domain'],'/',$L['Section'],'</th>
<th class="center">',$L['File'],'</th>
<th class="center">',$L['Action'],'</th>
</tr>
';

  // common tags

  $strSectionTags = '';
  $bFile = false;
  if ( file_exists( 'upload/tags_'.$tt.'.csv' ) )
  {
    $strSectionTags = 'tags_'.$tt.'.csv';
    $bFile = true;
  }

  echo '<tr class="data_o">';
  echo '<td class="center">&nbsp;</td>';
  echo '<td class="bold">Common to all sections</td>';
  echo '<td class="center">',$strSectionTags,'&nbsp;</td>';
  echo '<td class="center">';
  if ( $bFile )
  {
  echo '<a class="small" href="'.$oVIP->selfurl.'?tt=',$tt,'&amp;s=*&amp;a=view">',$L['Preview'],'</a> &middot; <a class="small" href="upload/',$strSectionTags,'">',$L['Download'],'</a> &middot; <a class="small" href="qnm_adm_tags_upload.php?tt=',$tt,'&amp;v=tags_',$tt,'.csv">',$L['Upload'],'</a> &middot; <a class="small" href="qnm_adm_change.php?tt=',$tt,'&amp;a=tags_del&amp;v=',$strSectionTags,'">',$L['Delete'],'</a>';
  }
  else
  {
  echo '<span class="disabled">',$L['Preview'],'</span> &middot; <span class="disabled">',$L['Download'],'</span> &middot; <a class="small" href="qnm_adm_tags_upload.php?tt=',$tt,'&amp;v=tags_',$tt,'.csv">',$L['Upload'],'</a> &middot; <span class="disabled">',$L['Delete'],'</span>';
  }
  echo '</td></tr>',PHP_EOL;

$i=0;
foreach($arrDomains as $intDomid=>$strDomtitle)
{
  // GET SECTIONS (with hidden)

  $arrSections = QTarrget(GetSections('A',$intDomid));

  // DISPLAY

  echo '<tr class="data_o">',PHP_EOL;
  echo '<td class="colgroup" colspan="2">',$strDomtitle,'</td>',PHP_EOL;
  echo '<td class="colgroup">&nbsp;</td>',PHP_EOL;
  echo '<td class="colgroup">&nbsp;</td>',PHP_EOL;
  echo '</tr>';

  // tags per section

  foreach($arrSections as $intSecid=>$strSectitle)
  {
    // GET SECTIONS
    $oSEC = new cSection($intSecid);

    $strSectionTags = '';
    $bFile = false;
    if ( file_exists( 'upload/tags_'.$tt.'_'.$intSecid.'.csv' ) )
    {
      $strSectionTags = 'tags_'.$tt.'_'.$intSecid.'.csv';
      $bFile = true;
    }

    echo '<tr class="data_s data_s2">';
    echo '<td style="text-align:center">',AsImg($oSEC->GetLogo(),'S',$L['Ico_section_'.$oSEC->type.'_'.$oSEC->status],'i_sec','','qnm_adm_section.php?d='.$intDomid.'&amp;s='.$oSEC->uid),'</td>';
    echo '<td><span class="bold">',$oSEC->name,'</span><br/><span class="small">id ',$intSecid,'</span> &middot; ';
    if ( $oSEC->StatsGet('tags')>0 )
    {
    echo '<a class="small" href="qnm_adm_tags.php?tt=',$tt,'&amp;s=',$intSecid,'&amp;a=used">',$L['Find_used_tags'],'</a></span>';
    }
    else
    {
    echo '<span class="disabled">',$L['E_no_tag'],'</span>';
    }
    echo '</td>';
    echo '<td style="text-align:center">',$strSectionTags,'&nbsp;</td>';
    echo '<td style="text-align:center">';
    if ( $bFile )
    {
    echo '<a class="small" href="'.$oVIP->selfurl.'?tt=',$tt,'&amp;s=',$oSEC->uid,'&amp;a=view">',$L['Preview'],'</a> &middot; <a class="small" href="upload/',$strSectionTags,'">',$L['Download'],'</a> &middot; <a class="small" href="qnm_adm_tags_upload.php?tt=',$tt,'&amp;v=tags_',$tt,'_',$intSecid.'.csv">',$L['Upload'],'</a> &middot; <a class="small" href="qnm_adm_change.php?tt=',$tt,'&amp;a=tags_del&amp;v=',$strSectionTags,'">',$L['Delete'],'</a>';
    }
    else
    {
    echo '<span class="disabled">',$L['Preview'],'</span> &middot; <span class="disabled">',$L['Download'],'</span> &middot; <a class="small" href="qnm_adm_tags_upload.php?tt=',$tt,'&amp;v=tags_',$tt,'_',$intSecid.'.csv">',$L['Upload'],'</a> &middot; <span class="disabled">',$L['Delete'],'</span>';
    }
    echo '</td></tr>',PHP_EOL;
  }
}
echo '</table>
';

// END TABS

echo '
</div>
';

// PREVIEW FILE

if ( empty($a) )
{
  echo '<h2>',$L['Preview'],'</h2>';
  echo '<p class="disabled">',$L['E_nothing_selected'],'</p>';
}

if ( $a=='view' )
{
  $strFile = 'tags_'.$tt.($strSection=='*' ? '' : '_'.$strSection).'.csv';

  if ( !empty($strFile) ) { if ( !file_exists('upload/'.$strFile) ) $strFile=''; }

  echo '<h2>',$L['Preview'],(empty($strFile) ? '' : ': '.$L['Proposed_tags'].' ['.$strFile.']'),'</h2>
  ';

  if ( empty($strFile) )
  {
    echo '<p class="disabled">',$L['E_nothing_selected'],'</p>';
  }
  else
  {
    if ( file_exists('upload/'.$strFile) )
    {
     $intSection = -1; if ( $strSection!='*' ) $intSection = intval($strSection);

      // read csv

      $arrTags = TagsRead($tt,$strSection);

      // display
      echo '<div class="scrollmessage">';
      echo '<table class="hidden" style="width:90%;cellspacing:2px">',PHP_EOL;
      foreach($arrTags as $strKey=>$strValue)
      {
      echo '<tr class="hidden rowlight">',PHP_EOL;
      echo '<td class="hidden small" style="width:100px;background-color:#eeeeee;text-align:right;padding:1px 2px 1px 1px">',$strKey,'</td>',PHP_EOL;
      echo '<td class="hidden small" style="padding:1px 1px 1px 2px">',$strValue,'</td>',PHP_EOL;
      echo '<td class="hidden"><a class="small" href="qnm_items.php?q=tag&amp;s=',$intSection,'&amp;v=',urlencode($strKey),'">',L('Search'),'</a></td>',PHP_EOL;
      echo '</tr>',PHP_EOL;
      }
      echo '</table>',PHP_EOL;
      echo '</div>';

    }
    else
    {
      echo '<p class="disabled">File not found...</p>';
    }
  }
}

// PREVIEW FIND

if ( $a=='used' && $strSection!='*' )
{
  $intSection = intval($strSection);

  // search used tags

  $arrUsed = cSection::GetTagsUsed($intSection,'',100);
  if ( count($arrUsed)>=100 ) $arrUsed[]='...';

  // display

  echo '<h2>',$L['Preview'],': ',$L['Used_tags'],' ',L('in_section'),' ',$intSection,'</h2>
  ';

  if ( count($arrUsed)==0 )
  {
    echo '<p class="disabled">',$L['E_no_result'],'</p>';
  }
  else
  {
    // search proposed tags

    $arrTags = TagsRead($tt,'*');
    $arrTags2 = TagsRead($tt,$intSection);
    foreach($arrTags2 as $strKey=>$strValue)
    {
      if ( !isset($arrTags[$strKey]) ) $arrTags[$strKey]=$strValue;
    }

    // display

    echo '<div class="scrollmessage">';
    echo '<table class="hidden" style="width:90%;cellspacing:2px">',PHP_EOL;
    foreach($arrUsed as $strValue)
    {
    echo '<tr class="hidden rowlight">',PHP_EOL;
    echo '<td class="hidden small" style="width:100px;background-color:#eeeeee;text-align:right;padding:1px 2px 1px 1px">',$strValue,'</td>',PHP_EOL;
    echo '<td class="hidden small" style="padding:1px 1px 1px 2px">',(isset($arrTags[$strValue]) ? $arrTags[$strValue] : '&nbsp;'),'</td>',PHP_EOL;
    echo '<td class="hidden"><a class="small" href="qnm_items.php?q=tag&amp;fs=',$strSection,'&amp;v=',urlencode($strValue),'">',$L['Search'],'</a></td>',PHP_EOL;
    echo '</tr>',PHP_EOL;
    }
    echo '</table>',PHP_EOL;
    echo '</div>';
  }
}

// HTML END

include 'qnm_adm_inc_ft.php';
