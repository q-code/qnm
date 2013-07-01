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
require_once 'bin/qnm_fn_sql.php';
include Translate('qnm_adm.php');

if ( $oVIP->user->role!='A' ) die(Error(13));

// INITIALISE

$oVIP->selfurl = 'qnm_adm_secu.php';
$oVIP->selfname = '<span class="upper">'.L('Adm_settings').'</span><br/>'.L('Adm_security');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  SqlSetting('visitor_right', $_POST['pal']); // this save the setting and register value in $_SESSION[QT][]

  if ( !isset($_POST['login_addon']) ) $_POST['login_addon']='0';
  if ( $_POST['login_addon']!=='0' )
  {
    $sPrefix = $_POST['login_addon'];
    if ( !isset($_SESSION[QT][$sPrefix]) || $_SESSION[QT][$sPrefix]==='0' ) $error = 'Use the module administration page to configure your settings';
    if ( !empty($error) ) $_POST['login_addon']=$sPrefix;
  }
  SqlSetting('login_addon', $_POST['login_addon']);
  SqlSetting('register_mode', $_POST['regmode']);
  SqlSetting('register_safe', $_POST['regsafe']);
  SqlSetting('avatar', $_POST['avatar']);
  SqlSetting('upload', $_POST['upload']);
  SqlSetting('show_stats', $_POST['show_stats']);
  SqlSetting('tags', $_POST['tags']);
  if ( $_SESSION[QT]['avatar']!='0' )
  {
    if ( isset($_POST['avatarwidth']) )
    {
      $str = strip_tags(trim($_POST['avatarwidth']));
      if ( !QTisbetween($str,20,200) ) $error = $L['Avatar'].' '.L('Maximum').' '.Error(1).' (20-200 pixels)';
      if ( empty($error) ) SqlSetting('avatar_width', $str);
    }
    if ( isset($_POST['avatarheight']) )
    {
      $str = strip_tags(trim($_POST['avatarheight']));
      if ( !QTisbetween($str,20,200) ) $error = $L['Avatar'].' '.L('Maximum').' '.Error(1).' (20-200 pixels)';
      if ( empty($error) ) SqlSetting('avatar_height', $str);
    }
  }
  if ( $_SESSION[QT]['upload']!='0' )
  {
    if ( isset($_POST['uploadsize']) )
    {
      $str = strip_tags(trim($_POST['uploadsize']));
      if ( !QTisbetween($str,1,10000) ) { $error = $L['Allow_upload'].' '.Error(1).' (1-10000 Kb)'; }
      if ( empty($error) ) SqlSetting('upload_size', $str);
    }
  }
  /* PPT is obsolete in this application: close item does not extists and unable to check ppt while making bulk AddNote
  $str = strip_tags(trim($_POST['ppt']));
  if ( !QTisbetween($str,10,999) ) $error = L('Max_post_per_item').' '.Error(1).' (10-999)';
  if ( empty($error) ) SqlSetting('posts_per_item', $str);
  */
  $str = strip_tags(trim($_POST['cpp']));
  if ( !QTisbetween($str,1,32) ) $error = $L['Max_char_per_post'].' '.Error(1).' (1-32)';
  if ( $oDB->type=='oci' && !QTisbetween($str,1,4) ) $error = $L['Max_char_per_post'].' '.Error(1).' (1-4)';
  $str = (int)$str*1000;
  if ( empty($error) ) SqlSetting('chars_per_post', $str);

  $str = strip_tags(trim($_POST['lpp']));
  if ( !QTisbetween($str,10,999) ) $error = $L['Max_line_per_post'].' '.Error(1).' (10-999)';
  if ( empty($error) ) SqlSetting('lines_per_post', $str);

  $str = strip_tags(trim($_POST['delay']));
  if ( !QTisbetween($str,1,99) ) $error = $L['Posts_delay'].' '.Error(1).' (1-99)';
  if ( empty($error) ) SqlSetting('posts_delay', $str);
  /*
  $str = strip_tags(trim($_POST['ppd']));
  if ( !QTisbetween($str,1,9999) ) $error = $L['Max_post_per_user'].' '.Error(1).' (1-9999)';
  if ( empty($error) ) SqlSetting('posts_per_day', $str);
  */
  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

if ( !isset($_SESSION[QT]['m_ldap']) ) $_SESSION[QT]['m_ldap']='0';

$oHtml->scripts[] = '
<script type="text/javascript">
<!--
function avatardisabled(str)
{
  if (str=="0")
  {
  document.getElementById("avatarwidth").disabled=true;
  document.getElementById("avatarheight").disabled=true;
  }
  else
  {
  document.getElementById("avatarwidth").disabled=false;
  document.getElementById("avatarheight").disabled=false;
  }
  return null;
}
function uploaddisabled(str)
{
  if (str=="0")
  {
  document.getElementById("uploadsize").disabled=true;
  }
  else
  {
  document.getElementById("uploadsize").disabled=false;
  }
  return null;
}
function ValidateForm(theForm)
{
  if (theForm.delay.value.length < 1) { alert(qtHtmldecode("'.L('Missing').': '.L('Posts_delay').'")); return false; }
  if (theForm.cpp.value.length < 1) { alert(qtHtmldecode("'.L('Missing').': '.L('Max_char_per_post').'")); return false; }
  if (theForm.lpp.value.length < 1) { alert(qtHtmldecode("'.L('Missing').': '.L('Max_line_per_post').'")); return false; }
  return null;
}
//-->
</script>
';

include 'qnm_adm_inc_hd.php';

echo '
<form method="post" action="',$oVIP->selfurl.'"  onsubmit="return ValidateForm(this);">
<table class="data_o">
';
echo '<tr class="data_o"><td class="headgroup" colspan="2">',$L['Public_access_level'].'</td></tr>
<tr class="data_o" title="',$L['H_Visitors_can'].'">
<td class="headfirst" style="width:250px;"><label for="pal">',$L['Visitors_can'].'</label></td>
<td class="colct"><select id="pal" name="pal" onchange="bEdited=true;">',QTasTag($L['Pal'],(int)$_SESSION[QT]['visitor_right']).'</select></td>
</tr>
<tr class="data_o"><td class="headgroup" colspan="2">',$L['Registration'].'</td></tr>
';
if ( !isset($_SESSION[QT]['login_addon']) ) $_SESSION[QT]['login_addon']='0';
$str = 'Internal authority (default)';
$arrLoginAddOn=array('0'=>$str);
$arr = GetParam(false,'param LIKE "m_%:login"');
foreach($arr as $param=>$name)
{
  $sPrefix = str_replace(':login','',$param);
  if ( isset($_SESSION[QT][$sPrefix]) && $_SESSION[QT][$sPrefix]!=='0' ) $arrLoginAddOn[$sPrefix] = 'Module '.$name;
}
if ( count($arrLoginAddOn)>1 ) $str = '<select id="login_addon" name="login_addon" onchange="bEdited=true;">'.QTasTag($arrLoginAddOn,$_SESSION[QT]['login_addon']).'</select>';
echo '<tr class="data_o">
<td class="headfirst"><label for="login_addon">',L('Authority'),'</label></td>
<td class="colct">',$str,'</td>
</tr>
';
echo '<tr class="data_o" title="',$L['Reg_mode'],'">
<td class="headfirst"><label for="regmode">',$L['Reg_mode'],'</label></td>
<td class="colct"><select id="regmode" name="regmode" onchange="bEdited=true;">',QTasTag(array('direct'=>'Online (direct)','email'=>'Online (with e-mail checking)','backoffice'=>'Back-office request'),$_SESSION[QT]['register_mode']),'</select></td>
</tr>
<tr class="data_o" title="',$L['H_Reg_security'],'">
<td class="headfirst"><label for="regsafe">',$L['Reg_security'],'</label></td>
<td class="colct"><select id="regsafe" name="regsafe" onchange="bEdited=true;">',QTasTag(array('none'=>L('None'),'text'=>L('Text_code'),'image'=>L('Image_code')),$_SESSION[QT]['register_safe']),'</select></td>
</tr>
';
echo '<tr class="data_o"><td class="headgroup" colspan="2">',$L['Security_rules'],'</td></tr>
<tr title="',$L['H_Posts_delay'],'">
<td class="headfirst"><label for="delay">',$L['Posts_delay'],'</label></td>
<td class="colct"><input type="text" id="delay" name="delay" size="2" maxlength="2" pattern="[1-9][0-9]{0,1}" value="',$_SESSION[QT]['posts_delay'],'" onchange="bEdited=true;" /> ',L('seconds').'</td>
</tr>
<tr class="data_o" title="',$L['H_Max_char_per_post'],'">
<td class="headfirst"><label for="cpp">',$L['Max_char_per_post'],'</label></td>
<td class="colct"><input type="text" id="cpp" name="cpp" size="2" maxlength="2" pattern="[1-9][0-9]{0,1}" value="',($_SESSION[QT]['chars_per_post']/1000),'" onchange="bEdited=true;" /> x 1000</td>
</tr>
<tr class="data_o" title="',$L['H_Max_line_per_post'],'">
<td class="headfirst"><label for="lpp">',$L['Max_line_per_post'],'</label></td>
<td class="colct"><input type="text" id="lpp" name="lpp" size="3" maxlength="3" pattern="[1-9][0-9]{1,2}" value="',$_SESSION[QT]['lines_per_post'],'" onchange="bEdited=true;" /></td>
</tr>
';
echo '<tr class="data_o"><td class="headgroup" colspan="2">',$L['User_interface'],'</td></tr>
<tr class="data_o" title="',$L['H_Allow_picture'],'">
<td class="headfirst"><label for="avatar">',$L['Allow_picture'],'</label></td>
<td class="colct"><select id="avatar" name="avatar" onchange="avatardisabled(this.value);bEdited=true;">'.QTasTag(array('0'=>L('N'),'jpg,jpeg'=>L('Y').' ('.L('Jpg_only').')','gif,jpg,jpeg,png'=>L('Y').' ('.L('Gif_jpg_png').')'),$_SESSION[QT]['avatar']).'</select> '.L('Maximum').' <input type="text" id="avatarwidth" name="avatarwidth" pattern="[1-9][0-9]{1,2}" size="3" maxlength="3" pattern="[0-9]{2,3}" value="'.$_SESSION[QT]['avatar_width'].'"'.($_SESSION[QT]['avatar']=='0' ? QDIS : '').'/> x <input type="text" id="avatarheight" name="avatarheight" pattern="[1-9][0-9]{1,2}" size="3" maxlength="3" pattern="[0-9]{2,3}" value="'.$_SESSION[QT]['avatar_height'].'"'.($_SESSION[QT]['avatar']=='0' ? QDIS : '').'/> pixels</td>
</tr>
<tr class="data_o" title="',$L['H_Allow_upload'],'">
<td class="headfirst"><label for="upload">',$L['Allow_upload'],'</label></td>
<td class="colct"><select id="upload" name="upload" onchange="uploaddisabled(this.value);bEdited=true;">'.QTasTag(array('0'=>L('N'),'M'=>L('Y').' ('.$L['Userrole_m'].')','U'=>L('Y').' ('.$L['Userrole_u'].')'),$_SESSION[QT]['upload']).'</select> '.L('Maximum').' <input type="text" id="uploadsize" name="uploadsize" size="4" maxlength="5" pattern="[1-9][0-9]{2,4}" value="'.$_SESSION[QT]['upload_size'].'"'.($_SESSION[QT]['upload']=='0' ? QDIS : '').'/>Kb</td>
</tr>
<tr class="data_o" title="',$L['H_Show_statistics'],'">
<td class="headfirst"><label for="show_stats">',$L['Show_statistics'],'</label></td>
<td class="colct"><select id="show_stats" name="show_stats" onchange="bEdited=true;">'.QTasTag(array('M'=>$L['Userrole_m'],'U'=>$L['Userrole_u']),$_SESSION[QT]['show_stats']).'</select></td>
</tr>
<tr class="data_o" title="',$L['H_Allow_tags'],'">
<td class="headfirst"><label for="tags">',$L['Allow_tags'],'</label></td>
<td class="colct"><select id="tags" name="tags">'.QTasTag(array('M'=>$L['Userrole_m'],'U'=>$L['Userrole_u']),$_SESSION[QT]['tags']).'</select></td>
</tr>
';
echo '<tr class="data_o"><td class="headgroup" colspan="2" style="padding:6px; text-align:center"><input type="submit" name="ok" value="',$L['Save'],'" /></td></tr>
</table>
</form>
';

// HTML END

include 'qnm_adm_inc_ft.php';