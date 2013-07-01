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
* @version    1.1 build:20130504
*/

session_start();
require_once 'bin/qnm_init.php';
require_once 'bin/class/qnm_class_dom.php';
include Translate('qnm_adm.php');
if ( $oVIP->user->role!='A' ) die(Error(13));

// INITIALISE

$d = -1; QThttpvar('d','int'); if ( $d<0 ) die('Missing argument d');

$oDOM = new cDomain($d);

$oVIP->selfurl = 'qnm_adm_domain.php';
$oVIP->selfname = $L['Domain_upd'];
$oVIP->exiturl = 'qnm_adm_sections.php';
$oVIP->exitname = '&laquo; '.$L['Sections'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  $oGP = new cGetPost($_POST['title']);
  if ( empty($oGP->e) ) $error = $L['Title'].' '.Error(1);

  // save and exit

  if ( empty($error) )
  {
    $oDOM->Rename($oGP->e);

    cLang::Delete('domain','d'.$d);
    foreach($_POST as $strKey=>$strTranslation)
    {
      if ( substr($strKey,0,1)=='T' )
      {
        $strTranslation = trim($strTranslation);
        if ( !empty($strTranslation) )
        {
        if ( get_magic_quotes_gpc() ) $strTranslation = stripslashes($strTranslation);
        cLang::Add('domain', substr($strKey,1), 'd'.$d, addslashes(QTconv($strTranslation,'5')));
        }
      }
    }
    Unset($_SESSION['L']['domain']);
    Unset($_SESSION[QT]['sys_domains']);
    $_SESSION['pagedialog'] = 'O|'.$L['S_save'];
    $oHtml->Redirect($oVIP->exiturl);
  }

  $_SESSION['pagedialog'] = 'E|'.$error;
}

// --------
// HTML START
// --------

include 'qnm_adm_inc_hd.php';

echo '
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length<1) { alert(qtHtmldecode("',$L['Missing'],': ',$L['Title'],'")); return false; }
  return null;
}
//-->
</script>
';

echo '
<form method="post" action="',$oVIP->selfurl,'" onsubmit="return ValidateForm(this);">
<table class="data_o" width="500">
';
$str = str_replace('&','&amp;',$oDOM->title);
echo '<tr class="data_o">
<td class="headfirst"><label for="title">',$L['Title'],'</label></td>
<td><input type="text" id="title" name="title" size="32" maxlength="64" value="',$str,'"/>',(strstr($str,'&amp;') ?  ' <span class="small">'.$oDOM->title.'</span>' : ''),'</td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst">',$L['Translations'],'</td>
<td colspan="2">
<p class="help">',sprintf($L['E_no_translation'],$oDOM->title),'</p>
<table class="hidden">';
$arrTrans = cLang::Get('domain','*','d'.$d);
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
  <td class="hidden"><input class="small" title="',$L['Domain'].' ('.$strIso.')'.'" type="text" id="T',$strIso,'" name="T',$strIso,'" size="32" maxlength="64" value="',$str,'"/>',(strstr($str,'&amp;') ?  ' <span class="small">'.$arrTrans[$strIso].'</span>' : ''),'</td>
  </tr>
  ';
}
echo '</table>
</td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst">&nbsp;</td>
<td><input type="submit" id="ok" name="ok" value="',$L['Save'],'"/><input type="hidden" name="d" value="',$d,'"/></td>
</tr>
';
echo '</table>
</form>
<p><a href="',$oVIP->exiturl,'">',$oVIP->exitname,'</a></p>
';

// HTML END

include 'qnm_adm_inc_ft.php';