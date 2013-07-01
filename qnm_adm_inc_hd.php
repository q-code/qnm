<?php

// QNM 1.0 build:20130410

$bShowtoc = false;
if ( substr($oVIP->selfurl,0,7)=='qnm_adm' || substr($oVIP->selfurl,0,5)=='qnmm_' ) $bShowtoc=true;

$oHtml->links['icon'] = '<link rel="shortcut icon" href="admin/qnm_icon.ico" />';
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="admin/qnm_main.css" />';
$oHtml->scripts[] = '<script type="text/javascript">
<!--
var e0 = '.(isset($L['E_editing']) ? '"'.$L['E_editing'].'"' : '0').';
//-->
</script>
';

if ( !empty($_SESSION['pagedialog']) )
{
if ( empty($oVIP->msg->text) ) $oVIP->msg->FromString($_SESSION['pagedialog']);
$oHtml->scripts_end[] = '<script type="text/javascript">
<!--
$(document).ready(function() {
var doc = document.getElementById("pagedialog");
if ( doc )
{
doc.innerHTML = "<img src=\"bin/css/pagedialog_'.$oVIP->msg->type.'.png\" alt=\"+\" class=\"pagedialog\"/>'.$oVIP->msg->text.'";
doc.className = "absolute_'.$oVIP->msg->type.'";
$("#pagedialog").fadeIn(500).delay(2000).fadeOut(800);
}
});
//-->
</script>
';
$oVIP->msg->Clear();
}

echo $oHtml->Head();
echo $oHtml->Body(array('onload'=>(isset($strBodyAddOnload) ? $strBodyAddOnload : null),'onunload'=>(isset($strBodyAddOnunload) ? $strBodyAddOnunload : null)));

echo '
<!-- MENU/PAGE -->
<table style="width:900px">
<tr>
<td style="',($bShowtoc ? 'width:170px;' : 'width:1px;'),' vertical-align:top;">
';

if ( $bShowtoc )
{

echo '
<!-- TOC -->
<div class="menu">
<div class="banner"><img class="logo" id="toc_logo" src="admin/qnm_logo.gif" style="border-width:0" alt="QNM" title="Q-Net Management" /></div>
<div class="header">',strtoupper(L('Administration')),'</div>
';

if ( file_exists('bin/qnm_lang.php') )
{
  include 'bin/qnm_lang.php';
  $oVIP->selfuri = QTimplodeUri(QTarradd(QTexplodeUri(),'lx'));
  $strLangMenu = '';
  foreach($arrLang as $strKey=>$arrDef)
  {
  $strLangMenu .= '<a class="small" href="'.Href().'?'.$oVIP->selfuri.'&amp;lx='.$strKey.'"'.(isset($arrDef[1]) ? ' title="'.$arrDef[1].'"' : '').' onclick="return qtEdited(bEdited,e0);">'.$arrDef[0].'</a>&nbsp;';
  }
}
else
{
  $strLangMenu .= '<span class="small">missing file:bin/qnm_lang.php</span>';
}

echo '<p class="language">',$strLangMenu,'</p>
';

$str='qtEdited(bEdited,e0);';
echo '<div class="group">
<p class="group">',L('Adm_info'),'</p>
<p class="item"><a href="qnm_adm_index.php" onclick="return '.($oVIP->selfurl=='qnm_adm_index.php' ? 'false;' : $str).'">',L('Adm_status'),'</a></p>
<p class="item"><a href="qnm_adm_site.php" onclick="return '.($oVIP->selfurl=='qnm_adm_site.php' ? 'false;' : $str).'">',L('Adm_general'),'</a></p>
</div>
<div class="group">
<p class="group">',L('Adm_settings'),'</p>
<p class="item"><a href="qnm_adm_region.php" onclick="return '.($oVIP->selfurl=='qnm_adm_region.php' ? 'false;' : $str).'">',L('Adm_region'),'</a></p>
<p class="item"><a href="qnm_adm_skin.php" onclick="return '.($oVIP->selfurl=='qnm_adm_skin.php' ? 'false;' : $str).'">',L('Adm_layout'),'</a></p>
<p class="item"><a href="qnm_adm_secu.php" onclick="return '.($oVIP->selfurl=='qnm_adm_secu.php' ? 'false;' : $str).'">',L('Adm_security'),'</a></p>
</div>
<div class="group">
<p class="group">',L('Adm_content'),'</p>
<p class="item"><a href="qnm_adm_sections.php" onclick="return '.($oVIP->selfurl=='qnm_adm_sections.php' ? 'false;' : $str).'">',L('Sections'),'</a></p>
<p class="item"><a href="qnm_adm_items.php" onclick="return '.($oVIP->selfurl=='qnm_adm_items.php' ? 'false;' : $str).'">',L('Items'),'</a></p>
<p class="item"><a href="qnm_adm_users.php" onclick="return '.($oVIP->selfurl=='qnm_adm_users.php' ? 'false;' : $str).'">',L('Users'),'</a></p>
<p class="item"><a href="qnm_adm_tags.php" onclick="return '.($oVIP->selfurl=='qnm_adm_tags.php' ? 'false;' : $str).'">',L('Tags'),'</a></p>
</div>
<div class="group">
<p class="group">',L('Adm_modules'),'</p>
';

// search modules
$arrModules = GetParam(false,'param LIKE "module%"');
if ( count($arrModules)>0 )
{
  foreach($arrModules as $strKey=>$strValue)
  {
  $strKey = str_replace('module_','',$strKey);
  echo '<p class="item"><a href="qnmm_',$strKey,'_adm.php" onclick="return qtEdited(bEdited,e0);">',$strValue,'</a></p>',PHP_EOL;
  }
}
echo '<p class="item"><a href="qnm_adm_module.php?a=add" onclick="return qtEdited(bEdited,e0);">[',L('Add'),']</a>&nbsp;&middot;&nbsp;<a class="menu" href="qnm_adm_module.php?a=rem" onclick="return warningedited(bEdited,e0);">[',L('Remove'),']</a></p>
</div>
<div class="footer"><a href="qnm_index.php" onclick="return qtEdited(bEdited,e0);">',L('Exit'),'</a></div>
</div>
';
}

// --------------
// END TABLE OF CONTENT
// --------------

echo '
</td>
<td style="padding-left:10px; vertical-align:top">
<!-- END MENU/PAGE -->
';

HtmlPageCtrl(START);

HtmlBanner('admin/qnm_logo.gif');

// Title (and help frame)

echo '<div style="width:300px; margin-bottom:20px"><h1>',$oVIP->selfname,'</h1>';
if ( isset($strPageversion) ) echo '<p class="small">',$strPageversion,'</p>';
if ( !empty($error) ) echo '<p class="error">',$error,'</p>';
if ( empty($error) && !empty($warning) ) echo '<p id="warningmessage" class="warning">',$warning,'</p>';

echo '</div>
';

if ( file_exists(Translate($oVIP->selfurl.'.txt')) )
{
  echo '<div style="width:400px; position:absolute; top:15px; left:495px; border:solid 1px #eeeeee;">';
  echo '<div class="hlp_head">',$L['Help'],'</div>';
  echo '<div class="hlp_body"><span id="helparea">';
  include Translate($oVIP->selfurl.'.txt');
  echo '</span></div></div>',PHP_EOL;
}

echo '
<!-- CONTENT -->
';