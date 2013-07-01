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
if ( $oVIP->user->role!='A' ) die(Error(13));

include Translate('qnm_adm.php');

// --------
// INITIALISE
// --------

$oVIP->selfurl = 'qnm_adm_site.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_info'].'</span><br/>'.$L['Adm_general'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check sitename
  $oGP = new cGetPost($_POST['sitename'],64);
  if ( empty($oGP->e) ) $error = $L['Site_name'].' ['.$oGP->e.'] '.Error(1);
  $_SESSION[QT]['site_name'] = $oGP->e;
  $oHtml->title = $_SESSION[QT]['site_name'];

  // check siteurl
  if ( empty($error) )
  {
    $oGP = new cGetPost($_POST['siteurl'],255);
    if ( substr($oGP->e,-1,1)=='/' ) $oGP->e = substr($oGP->e,0,-1);
    if ( empty($oGP->e) ) $error = $L['Site_url'].' ['.$oGP->e.'] '.Error(1);
    $_SESSION[QT]['site_url'] = $oGP->e;
    if ( !preg_match('/^(http:\/\/|https:\/\/)/',$oGP->e) ) $warning = $L['Site_url'].': '.L('E_missing_http');
  }

  // check indexname
  if ( empty($error) )
  {
    $oGP = new cGetPost($_POST['title'],64);
    if ( empty($oGP->e) ) $error = $L['Name_of_index'].' ['.$oGP->e.'] '.Error(1);
    $_SESSION[QT]['index_name'] = $oGP->e;
  }

  // check adminemail
  if ( empty($error) )
  {
    $oGP = new cGetPost($_POST['adminmail'],64);
    if ( QTismail($oGP->e) ) { $_SESSION[QT]['admin_email'] = $oGP->e; } else { $error = $L['Adm_e_mail'].' ['.$oGP->e.'] '.Error(1); }
  }

  // check others
  if ( empty($error) )
  {
    $_SESSION[QT]['use_smtp'] = $_POST['smtp'];
    if ( $_SESSION[QT]['use_smtp']=='1' )
    {
    $_SESSION[QT]['smtp_host'] = $_POST['smtphost'];
    $_SESSION[QT]['smtp_port'] = $_POST['smtpport'];
    $_SESSION[QT]['smtp_username'] = $_POST['smtpusr'];
    $_SESSION[QT]['smtp_password'] = $_POST['smtppwd'];
    if ( empty($_SESSION[QT]['smtp_host']) ) $error = 'Smtp host '.Error(1);
    }
  }

  // save value
  if ( empty($error) )
  {
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.addslashes($_SESSION[QT]['site_name']).'" WHERE param="site_name"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['site_url'].'"WHERE param="site_url"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.addslashes($_SESSION[QT]['index_name']).'" WHERE param="index_name"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['admin_email'].'" WHERE param="admin_email"');
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_SESSION[QT]['use_smtp'].'" WHERE param="use_smtp"');
    if ( $_SESSION[QT]['smtp_host']=='1' )
    {
    $oDB->Query('DELETE FROM '.TABSETTING.' WHERE param="smtp_host" OR param="smtp_port" OR param="smtp_username" OR param="smtp_password"');
    $oDB->Query('INSERT INTO '.TABSETTING.' VALUES ("smtp_host","'.$_SESSION[QT]['smtp_host'].'")');
    $oDB->Query('INSERT INTO '.TABSETTING.' VALUES ("smtp_port","'.$_SESSION[QT]['smtp_port'].'")');
    $oDB->Query('INSERT INTO '.TABSETTING.' VALUES ("smtp_username","'.$_SESSION[QT]['smtp_username'].'")');
    $oDB->Query('INSERT INTO '.TABSETTING.' VALUES ("smtp_password","'.$_SESSION[QT]['smtp_password'].'")');
    }
    $oGP = new cGetPost($_POST['adminfax'],255);
      $_SESSION[QT]['admin_fax'] = $oGP->e;
      $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.addslashes($oGP->e).'" WHERE param="admin_fax"');
    $oGP = new cGetPost($_POST['adminname'],255);
      $_SESSION[QT]['admin_name'] = $oGP->e;
      $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.addslashes($oGP->e).'" WHERE param="admin_name"');
    $oGP = new cGetPost($_POST['adminaddr'],255);
      $_SESSION[QT]['admin_addr'] = $oGP->e;
      $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.addslashes($oGP->e).'" WHERE param="admin_addr"');

    // save translations

    cLang::Delete('index','i');
    foreach($_POST as $strKey=>$str) {
    if ( substr($strKey,0,1)=='T' ) {
    if ( !empty($str) ) {
      $oGP = new cGetPost($str);
      cLang::Add('index',substr($strKey,1),'i',$oGP->e);
    }}}

    // register lang

    $_SESSION['L']['index'] = cLang::Get('index',GetIso());

    // exit
    $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
  }
}

if ( !preg_match('/^(http:\/\/|https:\/\/)/',$_SESSION[QT]['site_url']) ) $warning = $L['Site_url'].': '.$L['E_missing_http'];

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript">
<!--
function smtpdisabled(str)
{
  if (str=="0")
  {
  document.getElementById("smtphost").disabled=true;
  document.getElementById("smtpport").disabled=true;
  document.getElementById("smtpusr").disabled=true;
  document.getElementById("smtppwd").disabled=true;
  }
  else
  {
  document.getElementById("smtphost").disabled=false;
  document.getElementById("smtpport").disabled=false;
  document.getElementById("smtpusr").disabled=false;
  document.getElementById("smtppwd").disabled=false;
  }
  return null;
}
function PassInLink()
{
  strHost = document.getElementById("smtphost").value;
  strPort = document.getElementById("smtpport").value;
  strUser = document.getElementById("smtpusr").value;
  strPass = document.getElementById("smtppwd").value;
  document.getElementById("smtplink").href="qnm_ext_smtp.php?h=" + strHost + "&amp;p=" + strPort + "&amp;u=" + strUser + "&amp;w=" + strPass;
  document.getElementById("smtplink").target="_blank";
  return null;
}
function ValidateForm(theForm)
{
  if (theForm.sitename.value.length<1) { alert(qtHtmldecode("'.L('Missing').': '.L('Site_name').'")); return false; }
  if (theForm.siteurl.value.length<1) { alert(qtHtmldecode("'.L('Missing').': '.L('Site_url').'")); return false; }
  if (theForm.siteurl.value.substr(0,4)!="http") { alert(qtHtmldecode("http:// or https:// required in '.L('Site_url').'")); return false; }
  if (theForm.title.value.length<1) { alert(qtHtmldecode("'.L('Missing').': '.L('Name_of_index').'")); return false; }
  if (theForm.adminmail.value.length<1) { alert(qtHtmldecode("'.L('Missing').': '.L('Adm_e_mail').'")); return false; }
  return null;
}
//-->
</script>
';

include 'qnm_adm_inc_hd.php';

// Prepare table template

$table = new cTable('','data_o');
$table->row = new cTableRow('','data_o');
$table->td[0] = new cTableData('','','headfirst'); $table->td[0]->Add('style','width:200px;');
$table->td[1] = new cTableData();

// Show table

echo '<form method="post" action="',$oVIP->selfurl,'" onsubmit="return ValidateForm(this);">
';
echo $table->Start().PHP_EOL;
echo '<tr class="data_o"><td class="headgroup" colspan="2">'.L('General_site'),'</td></tr>
';

if ( empty($_SESSION[QT]['site_name']) ) $_SESSION[QT]['site_name']='';
$str = QTconv($_SESSION[QT]['site_name'],'I');
$table->row->Add('title',L('H_Site_name'));
$table->SetTDcontent( array(
    '<label for="sitename">'.L('Site_name').'</label>',
    '<input type="text" id="sitename" name="sitename" size="50" maxlength="64" value="'.$str.'" onchange="bEdited=true;"/>'.(strstr($str,'&amp;') ?  ' <span class="disabled">'.$_SESSION[QT]['site_name'].'</span>' : ''))
    );
echo $table->GetTDrow().PHP_EOL;

$table->row->Add('title',L('H_Site_url'));
$table->SetTDcontent( array(
    '<label for="siteurl">'.L('Site_url').'</label>',
    '<input type="url" id="siteurl" name="siteurl" pattern="(http://|https://).*" size="50" maxlength="255" value="'.$_SESSION[QT]['site_url'].'" onchange="bEdited=true;"/>'
    ));
echo $table->GetTDrow().PHP_EOL;

if ( empty($_SESSION[QT]['index_name']) ) $_SESSION[QT]['index_name']='';
$str = QTconv($_SESSION[QT]['index_name'],'I');
$table->row->Add('title',L('H_Name_of_index'));
$table->SetTDcontent( array(
    '<label for="title">'.L('Name_of_index').'</label>',
    '<input type="text" id="title" name="title" size="50" maxlength="64" value="'.$str.'" style="background-color:#FFFF99" onchange="bEdited=true;"/>'.(strstr($str,'&amp;') ?  ' <span class="disabled">'.$_SESSION[QT]['index_name'].'</span>' : '')
    ));
echo $table->GetTDrow().PHP_EOL;

echo '<tr class="data_o"><td class="headgroup" colspan="2">'.L('Contact'),'</td></tr>
';
$table->row->Add('title',L('H_Admin_e_mail'));
$table->SetTDcontent( array(
    '<label for="adminmail">'.L('Adm_e_mail').'</label>',
    '<input type="email" id="adminmail" name="adminmail" size="50" maxlength="255" value="'.$_SESSION[QT]['admin_email'].'" onchange="bEdited=true;"/>'
    ));
echo $table->GetTDrow().PHP_EOL;

if ( empty($_SESSION[QT]['admin_fax']) ) $_SESSION[QT]['admin_fax']='';
$str = QTconv($_SESSION[QT]['admin_fax'],'I');
$table->row->Add('title',L('H_Admin_fax'));
$table->SetTDcontent( array(
    '<label for="adminfax">'.L('Adm_fax').'</label>',
    '<input type="text" id="adminfax" name="adminfax" size="50" maxlength="255" value="'.$str.'" onchange="bEdited=true;"/>'.(strstr($str,'&amp;') ?  ' <span class="disabled">'.$_SESSION[QT]['admin_fax'].'</span>' : '')
    ));
echo $table->GetTDrow().PHP_EOL;

if ( empty($_SESSION[QT]['admin_name']) ) $_SESSION[QT]['admin_name']='';
$str = QTconv($_SESSION[QT]['admin_name'],'I');
$table->row->Add('title',L('Adm_name'));
$table->SetTDcontent( array(
    '<label for="adminname">'.L('Adm_name').'</label>',
    '<input type="text" id="adminname" name="adminname" size="50" maxlength="255" value="'.$str.'" onchange="bEdited=true;"/>'.(strstr($str,'&amp;') ?  ' <span class="disabled">'.$_SESSION[QT]['admin_name'].'</span>' : '')
    ));
echo $table->GetTDrow().PHP_EOL;

if ( empty($_SESSION[QT]['admin_addr']) ) $_SESSION[QT]['admin_addr']='';
$str = QTconv($_SESSION[QT]['admin_addr'],'I');
$table->row->Add('title',L('Adm_addr'));
$table->SetTDcontent( array(
    '<label for="adminaddr">'.L('Adm_addr').'</label>',
    '<input type="text" id="adminaddr" name="adminaddr" size="50" maxlength="255" value="'.$str.'" onchange="bEdited=true;"/>'.(strstr($str,'&amp;') ?  ' <span class="disabled">'.$_SESSION[QT]['admin_addr'].'</span>' : '')
));
echo $table->GetTDrow().PHP_EOL;

echo '<tr class="data_o"><td class="headgroup" colspan="2">'.L('Email_settings'),'</td></tr>
';
$table->row->Add('title',L('H_Use_smtp'));
$table->SetTDcontent( array(
    '<label for="smtp">'.L('Use_smtp').'</label>',
    '<select id="smtp" name="smtp" onchange="smtpdisabled(this.value); bEdited=true;">'.QTasTag(array($L['N'],$L['Y']),(int)$_SESSION[QT]['use_smtp']).'</select>'
    ));
echo $table->GetTDrow().PHP_EOL;

$table->SetTDcontent( array(
    '<label for="smtphost">Smtp host</label>',
    '<input type="text" id="smtphost" name="smtphost" size="28" maxlength="64" value="'.$_SESSION[QT]['smtp_host'].'"'.($_SESSION[QT]['use_smtp']=='0' ? QDIS : '').' onchange="bEdited=true;"/> port <input type="text" id="smtpport" name="smtpport" size="4" maxlength="6" value="'.(isset($_SESSION[QT]['smtp_port']) ? $_SESSION[QT]['smtp_port'] : '25').'"'.($_SESSION[QT]['use_smtp']=='0' ? QDIS : '').' onchange="bEdited=true;"/>'
    ));
echo $table->GetTDrow().PHP_EOL;

$table->SetTDcontent( array(
    '<label for="smtpusr">Smtp username</label>',
    '<input type="text" id="smtpusr" name="smtpusr" size="28" maxlength="64" value="'.$_SESSION[QT]['smtp_username'].'"'.($_SESSION[QT]['use_smtp']=='0' ? QDIS : '').' onchange="bEdited=true;"/>'
));
echo $table->GetTDrow().PHP_EOL;

$table->SetTDcontent( array(
    '<label for="smtppwd">Smtp password</label>',
    '<input type="text" id="smtppwd" name="smtppwd" size="28" maxlength="64" value="'.$_SESSION[QT]['smtp_password'].'"'.($_SESSION[QT]['use_smtp']=='0' ? QDIS : '').' onchange="bEdited=true;"/> <a id="smtplink" href="qnm_ext_smtp.php" onclick="PassInLink()">test smtp</a>'
));
echo $table->GetTDrow().PHP_EOL;

echo '<tr class="data_o"><td class="headgroup" colspan="2">'.L('Translations'),'</td></tr>
';
$table->row=null;
$strTD = '<p class="help">'.sprintf($L['E_no_translation'],$_SESSION[QT]['index_name']).'</p><table class="hidden">';
$arrTrans = cLang::Get('index','*','i');
include 'bin/qnm_lang.php'; // this creates $arrLang
foreach($arrLang as $strIso=>$arr)
{
  $str = '';
  if ( isset($arrTrans[$strIso]) ) {
  if ( !empty($arrTrans[$strIso]) ) {
  $str = QTconv($arrTrans[$strIso],'I');
  }}
  $strTD .= '<tr class="hidden"><td class="hidden" style="width:30px"><span title="'.$arr[1].'">'.$arr[0].'</span></td><td class="hidden"><input class="small" title="'.$L['Name_of_index'].' ('.$strIso.')'.'" type="text" id="T'.$strIso.'" name="T'.$strIso.'" size="45" maxlength="200" value="'.$str.'" onchange="bEdited=true;"/>'.(strstr($str,'&amp;') ?  ' <span class="disabled">'.$arrTrans[$strIso].'</span>' : '').'</td></tr>';
}
$strTD .= '</table>';
$table->SetTDcontent( array(L('Name_of_index'), $strTD) );
echo $table->GetTDrow().PHP_EOL;

echo '<tr class="data_o"><td class="headgroup" colspan="2" style="padding:6px; text-align:center"><input type="submit" name="ok" value="',$L['Save'],'"/></td></tr>
</table>
</form>
';

// HTML END

include 'qnm_adm_inc_ft.php';