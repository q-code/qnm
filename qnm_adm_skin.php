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

$oVIP->selfurl = 'qnm_adm_skin.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_settings'].'</span><br/>'.$L['Adm_layout'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check skin
  if ( empty($error) )
  {
    if ( !file_exists('skin/'.$_POST['skin'].'/qnm_main.css') )
    {
    $error = $L['Board_skin'].' '.Error(1).' (qnm_main.css not found)';
    }
  }

  // check banner/welcome/legend/home
  if ( empty($error) )
  {
    $_SESSION[QT]['skin_dir'] = 'skin/'.$_POST['skin'];
    $_SESSION[QT]['sys_welcome'] = $_POST['welcome'];
    $_SESSION[QT]['show_legend'] = $_POST['legend'];
    $_SESSION[QT]['show_banner'] = $_POST['banner'];
    $_SESSION[QT]['home_menu'] = $_POST['home'];
    $_SESSION[QT]['section_desc'] = $_POST['section_desc'];
    $_SESSION[QT]['items_per_page'] = $_POST['items_per_page'];
    $_SESSION[QT]['show_section_tags'] = $_POST['show_section_tags'];
  }

  // check homename
  if ( $_SESSION[QT]['home_menu']=='1' ) {
  if ( isset($_POST['homename']) && isset($_POST['homeurl']) ) {

    if ( empty($error) )
    {
      $oGP = new cGetPost($_POST['homename'],64);
      if ( !empty($oGP->e) ) { $_SESSION[QT]['home_name'] = $oGP->e; } else { $error = $L['Home_website_name'].' '.Error(1); }
    }
    if ( empty($error) )
    {
      $oGP = new cGetPost($_POST['homeurl'],255);
      if ( !empty($oGP->e) ) { $_SESSION[QT]['home_url'] = $oGP->e; } else { $error = $L['Site_url'].': '.Error(1); }
      if ( !preg_match('/^(http:\/\/|https:\/\/)/',$oGP->e) ) $warning = $L['Home_website_url'].': '.$L['E_missing_http'];
      $_SESSION[QT]['home_url'] = $oGP->e;
    }

  }}

  // save value
  if ( empty($error) )
  {
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_POST['skin'].'" WHERE param="skin_dir"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['sys_welcome'].'" WHERE param="sys_welcome"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_banner'].'" WHERE param="show_banner"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_legend'].'" WHERE param="show_legend"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['home_menu'].'" WHERE param="home_menu"');
    if ( $_SESSION[QT]['home_menu']=='1' )
    {
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.addslashes($_SESSION[QT]['home_name']).'" WHERE param="home_name"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['home_url'].'" WHERE param="home_url"');
    }
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['items_per_page'].'" WHERE param="items_per_page"');
    //$oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['replies_per_page'].'" WHERE param="replies_per_page"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['section_desc'].'" WHERE param="section_desc"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['show_section_tags'].'" WHERE param="show_section_tags"');
  }

  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

// warning
if ( !preg_match('/^(http:\/\/|https:\/\/)/',$_SESSION[QT]['home_url']) ) $warning = $L['Home_website_url'].': '.$L['E_missing_http'];

$oHtml->scripts[] = '<script type="text/javascript">
<!--
function homedisabled(str)
{
  if (str=="0")
  {
  document.getElementById("homename").disabled=true;
  document.getElementById("homeurl").disabled=true;
  }
  else
  {
  document.getElementById("homename").disabled=false;
  document.getElementById("homeurl").disabled=false;
  }
  return;
}
function ValidateForm(theForm)
{
  if (theForm.items_per_page.value.length < 1) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Items_per_section_page'].'")); return false; }
  //if (theForm.replies_per_page.value.length < 1) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Replies_per_item_page'].'")); return false; }
  return null;
}
//-->
</script>';

include 'qnm_adm_inc_hd.php';

// Read directory in language

$intHandle = opendir('skin');
$arrFiles = array();
while( false!==($strFile=readdir($intHandle)) )
{
if ( $strFile!='.' && $strFile!='..' ) $arrFiles[$strFile]=ucfirst($strFile);
}
closedir($intHandle);
asort($arrFiles);

// Current skin
$strDfltskin = substr($_SESSION[QT]['skin_dir'],5);

// Prepare table template

$table = new cTable('','data_o');
$table->row = new cTableRow('','data_o');
$table->td[0] = new cTableData('','','headfirst'); $table->td[0]->Add('style','width:250px;');
$table->td[1] = new cTableData();

// FORM

echo '<form method="post" action="',$oVIP->selfurl,'" onsubmit="return ValidateForm(this);">
';
echo $table->Start().PHP_EOL;
echo '<tr class="data_o"><td class="headgroup" colspan="2">',L('Skin'),'</td></tr>',PHP_EOL;
$table->row->Add('title',L('H_Board_skin'));
$table->SetTDcontent( array(
    '<label for="skin">'.L('Board_skin').'</label>',
    '<select id="skin" name="skin" onchange="bEdited=true;">'.QTasTag($arrFiles,$strDfltskin).'</select>'
    ));
    echo $table->GetTDrow().PHP_EOL;
$table->row->Add('title',L('H_Show_banner'));
$table->SetTDcontent( array(
    '<label for="banner">'.L('Show_banner').'</label>',
    '<select id="banner" name="banner" onchange="bEdited=true;">'.QTasTag(array(L('Show_banner0'),L('Show_banner1'),L('Show_banner2')),(int)$_SESSION[QT]['show_banner']).'</select>'
    ));
    echo $table->GetTDrow().PHP_EOL;
$table->row->Add('title',L('H_Show_legend'));
$table->SetTDcontent( array(
    '<label for="legend">'.L('Show_legend').'</label>',
    '<select id="legend" name="legend" onchange="bEdited=true;">'.QTasTag(array($L['N'],$L['Y']),(int)$_SESSION[QT]['show_legend']).'</select>'
    ));
    echo $table->GetTDrow().PHP_EOL;

echo '<tr class="data_o"><td class="headgroup" colspan="2">',L('Layout'),'</td></tr>',PHP_EOL;
$table->row->Add('title',L('H_Items_per_section_page'));
$arr = array('25'=>'25','50'=>'50','100'=>'100');
$table->SetTDcontent( array(
    '<label for="items_per_page">'.L('Items_per_section_page').'</label>',
    '<select id="items_per_page" name="items_per_page" onchange="bEdited=true;">'.QTasTag($arr,$_SESSION[QT]['items_per_page'],array('format'=>'%s / '.L('page'))).'</select>'
    ));
    echo $table->GetTDrow().PHP_EOL;
$table->row->Add('title',L('H_Show_welcome'));
$table->SetTDcontent( array(
    '<label for="welcome">'.L('Show_welcome').'</label>',
    '<select id="welcome" name="welcome" onchange="bEdited=true;">'.QTasTag(array($L['N'],$L['Y'],L('While_unlogged')),(int)$_SESSION[QT]['sys_welcome']).'</select>'
    ));
    echo $table->GetTDrow().PHP_EOL;

echo '<tr class="data_o"><td class="headgroup" colspan="2">',L('Your_website'),'</td></tr>',PHP_EOL;
$table->row->Add('title',L('H_Home_website_name'));
$str = QTconv($_SESSION[QT]['home_name'],'I');
$table->SetTDcontent( array(
    '<label for="home">'.L('Add_home').'</label>',
    '<select id="home" name="home" onchange="homedisabled(this.value); bEdited=true;">'.QTasTag(array($L['N'],$L['Y']),(int)$_SESSION[QT]['home_menu']).'</select>&nbsp;<input type="text" id="homename" name="homename" size="15" maxlength="24" value="'.$str.'"'.($_SESSION[QT]['home_menu']=='0' ? QDIS : '').' onchange="bEdited=true;"/>'.(strstr($str,'&amp;') ?  ' <span class="disabled">'.$_SESSION[QT]['home_name'].'</span>' : '')
    ));
    echo $table->GetTDrow().PHP_EOL;
$table->row->Add('title',L('H_Website'));
$table->SetTDcontent( array(
    '<label for="homeurl">'.L('Home_website_url').'</label>',
    '<input type="text" id="homeurl" name="homeurl" pattern="(http://|https://).*" size="30" maxlength="255" value="'.$_SESSION[QT]['home_url'].'"'.($_SESSION[QT]['home_menu']=='0' ? QDIS : '').' onchange="bEdited=true;"/>'
    ));
    echo $table->GetTDrow().PHP_EOL;
echo '<tr class="data_o"><td class="headgroup" colspan="2">',L('Display_options'),'</td></tr>',PHP_EOL;
$table->row->Add('title',L('H_Repeat_section_description'));
$table->SetTDcontent( array(
    '<label for="section_desc">'.L('Repeat_section_description').'</label>',
    '<select id="section_desc" name="section_desc" onchange="bEdited=true;">'.QTasTag(array($L['N'],$L['Y']),(int)$_SESSION[QT]['section_desc']).'</select>'
    ));
    echo $table->GetTDrow().PHP_EOL;
$table->row->Add('title',L('H_Show_section_tags'));
$table->SetTDcontent( array(
    '<label for="show_section_tags">'.L('Show_section_tags').'</label>',
    '<select id="show_section_tags" name="show_section_tags" onchange="bEdited=true;">'.QTasTag(array($L['N'],$L['Y']),(int)$_SESSION[QT]['show_section_tags']).'</select>'
    ));
    echo $table->GetTDrow().PHP_EOL;

echo '<tr class="data_o"><td class="headgroup" colspan="2" style="padding:6px; text-align:center"><input type="submit" name="ok" value="',$L['Save'],'"/></td></tr>
</table>
</form>
';

// HTML END

include 'qnm_adm_inc_ft.php';