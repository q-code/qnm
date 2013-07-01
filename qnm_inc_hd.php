<?php

// QNM 1.0 build:20130410

// Page message

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

// check LangMenu condition

$arrLangMenu = array(); //$strLangMenu = '';
if ( $_SESSION[QT]['userlang']=='1' )
{
  if ( file_exists('bin/qnm_lang.php') )
  {
    include 'bin/qnm_lang.php';
    foreach ($arrLang as $strKey => $arrDef)
    {
    $arrLangMenu[] = '<a href="'.Href().'?'.GetURI('lx').'&amp;lx='.$strKey.'"'.(isset($arrDef[1]) ? ' title="'.$arrDef[1].'"' : '').' class="'.($_SESSION[QT]['show_banner']=='1' ? 'banner' : 'nobanner').'">'.$arrDef[0].'</a>';
    }
  }
  else
  {
    $arrLangMenu[] = '<span class="small">missing file:bin/qnm_lang.php</span>';
  }
}

// check Welcome

$bWelcome = $_SESSION[QT]['board_offline']!='1'; // true except when offline;
if ( $bWelcome && $_SESSION[QT]['sys_welcome']=='0' ) $bWelcome = false;
if ( $bWelcome && $_SESSION[QT]['sys_welcome']=='2' && $oVIP->user->auth) $bWelcome = false;
if ( $bWelcome && in_array($oVIP->selfurl,array('qnm_register.php','qnm_form_reg.php','qnm_change.php')) ) $bWelcome = false;
if ( $bWelcome && !file_exists(GetLang().'sys_welcome.txt') ) $bWelcome = false;

// --------
// HTML START
// --------

$oHtml->title = (empty($oVIP->selfname) ? '' : $oVIP->selfname.' - ').$oHtml->title;
echo $oHtml->Head();
echo $oHtml->Body(array('onload'=>(isset($strBodyAddOnload) ? $strBodyAddOnload : null),'onunload'=>(isset($strBodyAddOnunload) ? $strBodyAddOnunload : null)));

// PAGE CONTROL

HtmlPageCtrl(START);

// BANNER and MAIN MENU

$strLangMenu = '<div class="langmenu">'.PHP_EOL;
if ( $oVIP->user->id>0 )
{
  $strLangMenu .= AsImg($_SESSION[QT]['skin_dir'].'/ico_user_p_1.gif','-',$L['User'],'i_user').'&nbsp;<a href="'.Href('qnm_user.php').'?id='.$oVIP->user->id.'" class="banner">'.$oVIP->user->name.'</a>';
}
else
{
  $strLangMenu .= AsImg($_SESSION[QT]['skin_dir'].'/ico_user_p_0.gif','-',$L['User'],'i_user').'&nbsp;'.L('Userrole_v');
}
if ( count($arrLangMenu)>1 ) $strLangMenu .= ' | '.implode(' ',$arrLangMenu);
$strLangMenu .= '</div>'.PHP_EOL;

$arrMenus = array(); // keys are: 'h' in header, 'f' in footer, 'n' name, 'u' url, 's' selected with url's, 'i' inactive with url's
if ( $_SESSION[QT]['home_menu']=='1' && !empty($_SESSION[QT]['home_url']) )
{
  $arrMenus[]=array('h'=>true, 'f'=>true, 'n'=>$_SESSION[QT]['home_name'], 'u'=>$_SESSION[QT]['home_url']);
}
$arrMenus[]=array('h'=>false,'f'=>true, 'n'=>L('Legal'), 'u'=>'qnm_privacy.php');
$arrMenus[]=array('h'=>false,'f'=>true, 'n'=>L('FAQ'), 'u'=>'qnm_faq.php');
$arrMenus[]=array('h'=>true, 'f'=>false,'n'=>ObjTrans('index','i',$_SESSION[QT]['index_name']), 'u'=>'qnm_index.php', 's'=>'qnm_index.php qnm_calendar.php qnm_items.php qnm_item.php', 'secondary'=>true);
if ( QNM_SIMPLESEARCH )
{
  $arrMenus[]=array('h'=>true, 'f'=>true, 'n'=>L('Search'), 'u'=>( $_SESSION[QT]['board_offline']=='1' || ($oVIP->user->role=='V' && $_SESSION[QT]['visitor_right']<5) ? '' : 'qnm_s_search.php'),'s'=>'qnm_s_search.php qnm_search.php qnm_find.php');
}
else
{
  $arrMenus[]=array('h'=>true, 'f'=>true, 'n'=>L('Search'), 'u'=>( $_SESSION[QT]['board_offline']=='1' || ($oVIP->user->role=='V' && $_SESSION[QT]['visitor_right']<5) ? '' : 'qnm_search.php'),'s'=>'qnm_s_search.php qnm_search.php qnm_find.php');
}
$arrMenus[]=array('h'=>true, 'f'=>false,'n'=>L('Memberlist'), 'u'=>( $_SESSION[QT]['board_offline']=='1' || ($oVIP->user->role=='V' && $_SESSION[QT]['visitor_right']<4) ? '' : 'qnm_users.php'));
if ( CanPerform('show_stats',$oVIP->user->role) )
{
  $arrMenus[]=array('h'=>false,'f'=>true, 'n'=>L('Statistics'), 'u'=>($_SESSION[QT]['board_offline']=='1' ? '' : 'qnm_stats.php'));
}
if ( $oVIP->user->auth )
{
  $arrMenus[]=array('h'=>true, 'f'=>true, 'n'=>L('Profile'), 'u'=>($_SESSION[QT]['board_offline']=='1' ? '' : 'qnm_user.php?id='.$oVIP->user->id), 's'=>'qnm_user.php qnm_user_img.php.php qnm_user_pwd.php', 'i'=>'', 'secondary'=>true);
  $arrMenus[]=array('h'=>true, 'f'=>true, 'n'=>L('Logout'), 'u'=>'qnm_login.php?a=out');
}
else
{
  $arrMenus[]=array('h'=>true, 'f'=>true, 'n'=>L('Register'), 'u'=>($_SESSION[QT]['board_offline']=='1' ? '' : 'qnm_user_new.php'), 's'=>'qnm_user_new.php qnm_form_reg.php', 'secondary'=>true);
  $arrMenus[]=array('h'=>true, 'f'=>true, 'n'=>L('Login'), 'u'=>'qnm_login.php');
}

$strMenus = '
<!-- menu -->
<div class="menu'.($bWelcome ? ' withwelcome' : '').'">
<ul>
';
foreach($arrMenus as $arrMenu) {
  if ( $arrMenu['h'] ) {
    if ( !isset($arrMenu['s']) ) $arrMenu['s']=' '.$arrMenu['u'];
    if ( !isset($arrMenu['i']) ) $arrMenu['i']=' '.$arrMenu['u'];
    if ( empty($arrMenu['u']) )
    {
      $strMenus .= '<li'.(isset($arrMenu['secondary']) ? ' class="secondary"' : '').'>'.$arrMenu['n'].'</li>'.PHP_EOL;
    }
    else
    {
      $strMenus .= '<li'.(isset($arrMenu['secondary']) ? ' class="secondary"' : '').(strstr($arrMenu['s'],$oVIP->selfurl) ? ' id="menuactif"' : '').'><a href="'.Href($arrMenu['u']).'"'.(strstr($arrMenu['i'],$oVIP->selfurl) ? ' onclick="return false;"' : '').'>'.$arrMenu['n'].'</a></li>'.PHP_EOL;
    }
  }
}
$strMenus .='</ul>
</div>
';

// show banner, menu and welcome

switch($_SESSION[QT]['show_banner'])
{
  case '0': HtmlBanner('',$strLangMenu,'','nobanner'); echo $strMenus; break;
  case '1': HtmlBanner($_SESSION[QT]['skin_dir'].'/qnm_logo.gif',$strLangMenu); echo $strMenus; break;
  case '2': HtmlBanner($_SESSION[QT]['skin_dir'].'/qnm_logo.gif',$strLangMenu,$strMenus); break;
}

if ( $bWelcome )
{
echo '
<!-- welcome -->
<div class="welcome">';
include Translate('sys_welcome.txt');
echo '</div>
';
}

// MAIN

$ps = -1; // previous section
if ( isset($_SESSION[QT]['section']) ) {
if ( $_SESSION[QT]['section']>=0 ) {
  $ps = (int)$_SESSION[QT]['section'];
}}

echo '
<!-- MAIN CONTROL -->
<div class="body">
<div class="body_in">
';

echo '
<!-- top bar -->
<div class="bodyhd">
<div class="bodyhdleft"><img class="bodyhdleft" src="',$_SESSION[QT]['skin_dir'],'/ico_hd_left.gif" alt="" /><a class="body_hd" href="',Href('qnm_index.php'),'"',($oVIP->selfurl=='qnm_index.php' ? ' onclick="return false;"' : ''),'>',ObjTrans('index','i',$_SESSION[QT]['index_name']),'</a>';
if ( $ps>=0 )
{
  if ( isset($oVIP->sections[$ps]) )
  {
    echo QNM_CRUMBTRAIL,'<a class="body_hd" href="',Href('qnm_items.php'),'?s=',$ps,'"',($oVIP->selfurl=='qnm_items.php' ? ' onclick="return false;"' : ''),'>',ObjTrans('sec',"s$ps",(isset($oSEC) ? $oSEC->name : $oVIP->sections[$ps])),'</a>';
  }
  else
  {
    $ps=-1; unset($_SESSION[QT]['section']);
  }
  if ( isset($oNE) )
  {
    if ( isset($oPARENT) ) echo QNM_CRUMBTRAIL,'<a href="',Href('qnm_item.php'),'?nid=',GetNid($oPARENT),'">',$oPARENT->id,'</a>';
    if ( $oVIP->selfurl=='qnm_form_docs.php' )
    {
      echo QNM_CRUMBTRAIL,'<a href="',Href('qnm_item.php'),'?nid=',GetNid($oNE),'">',$oNE->id,'</a>',QNM_CRUMBTRAIL,L('Documents');
    }
    else
    {
      echo QNM_CRUMBTRAIL,$oNE->id;
    }
  }
  if ( $oVIP->selfurl=='qnm_calendar.php' && isset($intYear) )
  {
  if ( !empty($intYear) ) echo QNM_CRUMBTRAIL,$intYear;
  }
}
echo '</div>
<div class="bodyhdright">';

switch($oVIP->selfurl)
{
case 'qnm_users.php':
  if ( $_SESSION[QT]['viewmode']=='C' )
  {
  echo '<a href="',Href(),'?',GetURI('view'),'&amp;view=N"><img class="ico i_modes" src="',$_SESSION[QT]['skin_dir'],'/ico_view_n.gif" title="',L('Ico_view_n'),'" alt="N" /></a>';
  }
  else
  {
  echo '<a href="',Href(),'?',GetURI('view'),'&amp;view=C"><img class="ico i_modes" src="',$_SESSION[QT]['skin_dir'],'/ico_view_c.gif" title="',L('Ico_view_c'),'" alt="C" /></a>';
  }
  break;
case 'qnm_calendar.php':
  if ( $ps>=0 ) echo '<a href="',Href('qnm_items.php'),'?s=',$ps,'"><img class="ico i_modes" src="',$_SESSION[QT]['skin_dir'],'/ico_view_f_n.gif" title="',L('Ico_view_f_n'),'" alt="N" /></a>';
  break;
}

echo '&nbsp;<img class="bodyhdright" src="',$_SESSION[QT]['skin_dir'],'/ico_hd_right.gif" alt="" /></div>
</div>
';

// MAIN CONTENT

echo '
<!-- main content -->
<div class="bodyct">
';