<?php

// QNM 1.0 build:20130410

// BODY END

echo '
</div>
';

// LINE END

$bSectionlist = false;
if ( $oVIP->selfurl!='qnm_index.php' ) {
if ( QNM_SHOW_GOTOLIST ) {
if ( count($_SESSION[QT]['sys_sections'])>1 ) {
  $bSectionlist = true;
}}}

echo '
<!-- bottom bar -->
';
if ( $bSectionlist ) echo '<form method="get" id="goto" action="',($oVIP->selfurl=='qnm_calendar.php' || $oVIP->selfurl=='qnm_email.php' ? Href() : Href('qnm_items.php')),'">
';
echo '<div class="bodyft">
<div class="bodyftleft"><img class="bodyftleft" src="',$_SESSION[QT]['skin_dir'],'/ico_ft_left.gif" alt="" />';
if ( QNM_SHOW_TIME )
{
  echo gmdate($_SESSION[QT]['formattime'], time() + 3600*($_SESSION[QT]['time_zone']));
  if ( $_SESSION[QT]['show_time_zone']=='1' )
  {
    echo ' (gmt';
    if ( $_SESSION[QT]['time_zone']>0 ) echo '+',$_SESSION[QT]['time_zone'];
    if ( $_SESSION[QT]['time_zone']<0 ) echo $_SESSION[QT]['time_zone'];
    echo ')';
  }
}
if ( isset($oSEC) )
{
  if ( QNM_SHOW_MODERATOR ) echo ' &middot; ',L('Userrole_c'),': <a href="',Href('qnm_user.php?id='.$oSEC->modid),'">',$oSEC->modname,'</a>';
}

echo '</div>
<div class="bodyftright">';
if ( $bSectionlist ) echo '<label for="Sectionlist">',$L['Goto'],S,'</label><select id="Sectionlist" name="s" size="1" class="small" onchange="document.getElementById(\'goto\').submit();"><option value="-1">&nbsp;</option>',Sectionlist(),'</select>&nbsp;<input type="submit" value="',$L['Ok'],'" class="small" id="goto_ok" />';
echo '<img class="bodyftright" src="',$_SESSION[QT]['skin_dir'],'/ico_ft_right.gif" alt="" /></div>
</div>
';

if ( $bSectionlist ) echo '<script type="text/javascript"><!-- document.getElementById("goto_ok").style.display="none"; //--></script>
</form>
';

echo '
<!-- END MAIN CONTROL -->
</div></div>
';

// --------
// INFO & LEGEND
// --------

if ( $_SESSION[QT]['board_offline']!='1' ) {
if ( $_SESSION[QT]['show_legend']=='1' ) {
if ( in_array($oVIP->selfurl,array('index.php','qnm_index.php','qnm_items.php','qnm_item.php','qnm_find.php','qnm_calendar.php')) ) {

echo '
<!-- Legend -->
<table class="legend">
<tr class="legend">
<td class="legend">
<div class="legendbox">
<p class="legendtitle">',L('Information'),'</p>
<span class="small">';

// section info

if ( empty($q) )
{
  if ( isset($oSEC) )
  {
    $n = $oSEC->StatsGet('notes'); $na = $oSEC->StatsGet('notesA');
    echo ObjTrans('sec',"s$s",(isset($oSEC) ? $oSEC->name : $oVIP->sections[$s])),':<br/>';
    echo '&bull; ',L('Item',$oSEC->items),($oSEC->items>0 ? ' ('.L('inactive',$oSEC->StatsGet('itemsZ')).')' : ''),'<br/>';
    echo '&bull; ',L('In_process_note',$na),($n>0 ? ' ('.L('closed',$n-$na).')' : '');
    echo '<br/><br/>';
  }
}
else
{
  echo L('Search_results'),':<br/>';
  echo '&bull; ',L('Item',$oSEC->items),($oSEC->items>0 ? ' ('.L('inactive',$oSEC->StatsGet('itemsZ')).')' : '');
  echo '<br/><br/>';
}

// application info

echo ObjTrans('index','i',$_SESSION[QT]['index_name']),':<br/>';
echo '&bull; ',L('Item',$oVIP->stats->items),($oVIP->stats->items>0 ? ' ('.L('inactive',$oVIP->stats->itemsZ).')' : ''),'<br/>';
echo '&bull; ',L('In_process_note',$oVIP->stats->notesA),($oVIP->stats->notes>0 ? ' ('.L('closed',$oVIP->stats->notes - $oVIP->stats->notesA).')' : '');

// new user info

if ( isset($oVIP->stats->states['newuserid']) ) {
if ( !empty($oVIP->stats->states['newuserdate']) ) {
if ( DateAdd($oVIP->stats->states['newuserdate'],30,'day')>Date('Ymd') ) {
echo '<br/><br/>',L('Welcome_to'),'<a class="small" href="',Href('qnm_user.php'),'?id=',$oVIP->stats->states['newuserid'],'">',$oVIP->stats->states['newusername'],'</a>';
}}}

echo '</span>',PHP_EOL;
echo '</div>',PHP_EOL;
echo '</td>',PHP_EOL;
echo '<td class="legend">',PHP_EOL;
if ( isset($strDetailLegend) )
{
echo '<div class="legendbox"><p class="legendtitle">',L('Details'),'</p>',PHP_EOL;
echo $strDetailLegend;
echo '</div>',PHP_EOL;
}
echo '</td>',PHP_EOL;
echo '<td class="legend">',PHP_EOL;
echo '<div class="legendbox"><p class="legendtitle">',L('Legend'),'</p>',PHP_EOL;
echo '<span class="small">',PHP_EOL;
if ( $oVIP->selfurl=='qnm_index.php' )
{
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_section_0_0.gif','F',L('Ico_section_0_0'),'i_sec'),' ',L('Ico_section_0_0'),'<br/>',PHP_EOL;
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_section_0_1.gif','F',L('Ico_section_0_1'),'i_sec'),' ',L('Ico_section_0_1'),'<br/>',PHP_EOL;
  if ( $oVIP->user->IsStaff() ) echo AsImg($_SESSION[QT]['skin_dir'].'/ico_section_1_0.gif','F',L('Ico_section_1_0'),'i_sec'),' ',L('Ico_section_1_0'),'<br/>',PHP_EOL;
}
else
{
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_ne_e.gif','E',L('Item'),'i_item'),' ',L('Item'),PHP_EOL;
  echo ' &nbsp;',AsImg($_SESSION[QT]['skin_dir'].'/ico_ne_l.gif','L',L('Line'),'i_item'),' ',L('Line'),PHP_EOL;
  echo '<br/>';
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_link_c.gif','L',L('Sub-item'),'i_post'),' ',L('Sub-item'),PHP_EOL;
  echo ' &nbsp;',AsImg($_SESSION[QT]['skin_dir'].'/ico_ne_c.gif','E',L('Connector'),'i_item'),' ',L('Connector'),'<br/>',PHP_EOL;
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_nc_0.gif','r',L('Relation'),'i_post'),' ',L('Relation'),' (',L('direction0'),')<br/>',PHP_EOL;
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_nc_1.gif','r',L('Relation').' '.L('direction1'),'i_post'),' ',L('Relation'),' ',L('direction1'),'<br/>',PHP_EOL;
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_nc_2.gif','r',L('Relation').' '.L('direction2'),'i_post'),' ',L('Relation'),' ',L('direction2'),'<br/>',PHP_EOL;
  echo AsImg($_SESSION[QT]['skin_dir'].'/ico_nc_-1.gif','r',L('Relation').' '.L('direction-1'),'i_post'),' ',L('Relation'),' ',L('direction-1'),'<br/>',PHP_EOL;
  echo '<span style="color:red">&bull;</span> ',L('inactive'),'<br/>',PHP_EOL;
}
echo '</span></div>',PHP_EOL;
echo '</td>',PHP_EOL;
echo '</tr>',PHP_EOL;
echo '</table>',PHP_EOL;

}}}

// --------
// COPYRIGHT
// --------

// MODULE RSS
if ( $_SESSION[QT]['board_offline']!='1' ) {
if ( UseModule('rss') ) {
if ( $_SESSION[QT]['m_rss']=='1' ) {
if ( $oVIP->user->role!='V' || $oVIP->user->role.substr($_SESSION[QT]['m_rss_conf'],0,1)=='VV' ) {
if ( $oVIP->selfurl!='qnmm_rss.php' ) {
  $arrMenus[]=array('h'=>false,'f'=>true, 'n'=>'<img src="admin/rss.gif" width="34" height="14" style="vertical-align:bottom;border-width:0" alt="rss" title="RSS"/>', 'u'=>'qnmm_rss.php');
}}}}}

echo '
<!-- footer -->
<div class="footer">
<div class="footerleft">';
$i=0;
foreach($arrMenus as $arrMenu) {
if ( $arrMenu['f'] ) {
  if ( !isset($arrMenu['s']) ) $arrMenu['s']=$arrMenu['u'];
  if ( $i!=0 ) echo ' &middot; ';
  $i++;
  if ( empty($arrMenu['u']) )
  {
  echo $arrMenu['n'];
  }
  else
  {
  echo '<a href="',Href($arrMenu['u']),'"',(strstr($arrMenu['s'],$oVIP->selfurl) ? ' onclick="return false;"' : ''),'>',$arrMenu['n'],'</a>';
  }
}}
if ( $oVIP->user->role=='A' ) echo ' &middot; <a href="',Href('qnm_adm_index.php'),'">['.L('Administration').']</a>';

echo '</div>
<div class="footerright">powered by <a href="http://www.qt-cute.org">QT-cute</a> <span title="',QNMVERSION,'">v',substr(QNMVERSION,0,3),'</span></div>
</div>
';

// END PAGE CONTROL

HtmlPageCtrl(END);

// HTML END

if ( isset($oDB->stats) )
{
  $oDB->stats['end'] = (float)(vsprintf('%d.%06d', gettimeofday()));
  echo '<br/>&nbsp;',$oDB->stats['num'],' queries in ',round($oDB->stats['end']-$oDB->stats['start'],4),' sec';
}

// CDN fallback

if ( isset($oHtml->scripts['jquery']) )
{
echo '
<!-- Jquery CDN fallback -->
<script type="text/javascript">
<!--
window.jQuery || document.write(\'<link rel="stylesheet" type="text/css" href="'.JQUERYUI_CSS_OFF.'" /><script type="text/javascript" src="'.JQUERY_OFF.'"></script><script type="text/javascript" src="'.JQUERYUI_OFF.'"></script>\');
//-->
</script>
';
}

echo $oHtml->End();

ob_end_flush();