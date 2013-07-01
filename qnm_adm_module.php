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
* @package    Q-registerations
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2013 The PHP Group
* @version    1.0 build:20130410
*/

session_start();
require_once 'bin/qnm_init.php';
include Translate('qnm_adm.php');
if ( $oVIP->user->role!='A' ) die(Error(13));

$a = 'add';
$id = '';
$ok = '';
QThttpvar('a id ok','str str str');

$oVIP->selfurl = 'qnm_adm_module.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_modules'].'</span><br/>'.($a=='add' ? $L['Add'] : $L['Remove']);

// --------
// SUBMITTED
// --------

if ( !empty($ok) )
{
  // check form
  if ( get_magic_quotes_gpc() ) $id = stripslashes($id);
  $id = str_replace(' ','_',$id);
  $strFile = 'qnmm_'.$id.'_'.($a=='rem' ? 'un' : '').'install.php';

  if ( file_exists($strFile) )
  {
  // exit
  $oVIP->selfname = $L['Adm_modules'];
  $oVIP->exiturl = 'qnm_adm_module.php?a='.$a;
  $oVIP->exitname = '&laquo; '.$L['Exit'];
  $oHtml->PageBox(NULL,$L['Module_name'].': '.$id.'<br/><br/><a href="'.$strFile.'">'.$L['Module_'.$a].' !</a><br/><br/>','admin',0);
  }
  else
  {
  $error = 'Module not found... ('.$strFile.')<br/><br/>Possible cause: components of this module are not uploaded.';
  }
}

// --------
// HTML START
// --------

include 'qnm_adm_inc_hd.php';

echo '<form method="post" action="',$oVIP->selfurl,'">
<table class="data_o">
';
echo '<tr class="data_o">
<td class="headfirst"><label for="id">',$L['Module_'.$a],'</label></td>
<td><input id="id" name="id" size="12" maxlength="24" value=""/>&nbsp;<span class="help">',$L['Module_name'],'</span></td>
</tr>
';
echo '<tr class="data_o">
<td class="headgroup" style="padding:6px; text-align:center" colspan="2" ><input type="hidden" name="a" value="',$a,'"/><input type="submit" id="ok" name="ok" value="',$L['Search'],'"/></td>
</tr>
';
echo '</table>
</form>
';

// HTML END

echo '
<script type="text/javascript">
<!--
document.getElementById("id").focus();
//-->
</script>
';

include 'qnm_adm_inc_ft.php';