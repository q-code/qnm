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
if ( !$oVIP->user->CanView('V4') ) HtmlPage(11);

$id = -1; QThttpvar('id','int'); if ( $id<0 ) die('Wrong id');

if ( isset($_GET['edit']) ) $_SESSION[QT]['editing']=($_GET['edit']=='1' ? true : false);
if ( isset($_POST['edit']) ) $_SESSION[QT]['editing']=($_POST['edit']=='1' ? true : false);

// --------
// FUNCTION
// --------

function show_ban($strRole='V',$intBan=0)
{
  if ( $intBan<1 ) return '';
  if ( $strRole=='A' || $strRole=='M' )
  {
    global $L;
    if ( $intBan>1 ) $intBan=($intBan-1)*10;
    Return '<p class="small error">'.$L['Is_banned'].' '.L('Day',$intBan).' '.$L['Since'].' '.L('last_message').'</p>';
  }
}

// --------
// INITIALISE
// --------

include 'bin/class/qt_class_smtp.php';
include GetLang().'qnm_reg.php';

$bCanEdit = false;
if ( $oVIP->user->id==$id ) $bCanEdit=true;
if ( $oVIP->user->IsStaff() ) $bCanEdit=true;
if ( $id==0 ) $bCanEdit=false;
if ( !isset($_SESSION[QT]['editing']) || !$bCanEdit) $_SESSION[QT]['editing']=false;

$oVIP->selfurl = 'qnm_user.php';
$oVIP->selfname = $L['Profile'];

// Map module

$bMap=false;
if ( UseModule('map') )
{
  include 'qnmm_map_lib.php';
  $bMap = QTgcanmap('U',true,false); // Use config file // No main list check
  if ( $bMap )
  {
    include Translate('qnmm_map.php');
    $oHtml->links[]='<link rel="stylesheet" type="text/css" href="qnmm_map.css" />';
  }
}

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check form
  $strLoca = trim($_POST['location']); if ( get_magic_quotes_gpc() ) $strLoca = stripslashes($strLoca);
  $strLoca = QTconv($strLoca,'3',QNM_CONVERT_AMP);

  if ( empty($error) )
  {
    $strMail = trim($_POST['mail']);
    $strMail = str_replace(';',' ; ',$strMail);
    $strMail = str_replace('  ',' ',$strMail);
    if ( !empty($strMail) && !QTismail($strMail) ) $error=$L['Email'].' '.$strMail.' '.$L['E_invalid'];
  }

  if ( empty($error) )
  {
    $strPhone = QTconv($_POST['phone'],'2');
  }

  if ( empty($error) )
  {
    $strChild='0';
  }

  if ( empty($error) )
  {
    $strWww = QTconv($_POST['www'],'2');
    if ( !empty($strWww) && substr($strWww,0,4)!='http' ) $error=$L['Website'].' '.$L['E_invalid'];
    if ( $strWww=='http://' || $strWww=='https://' ) $strWww='';
  }

  // save

  if ( empty($error) )
  {
    $oDB->Query('UPDATE '.TABUSER.' SET location="'.addslashes($strLoca).'", mail="'.$strMail.'", phone="'.addslashes($strPhone).'", www="'.addslashes($strWww).'", privacy="'.$_POST['privacy'].'", children="'.$strChild.'" WHERE id='.$id);
    if ( isset($_POST['m_map_gcenter']) )
    {
      if ( empty($_POST['m_map_gcenter']) )
      {
      QTgpointdelete(TABUSER,$id);
      }
      else
      {
      QTgpoint(TABUSER,$id,QTgety($_POST['m_map_gcenter']),QTgetx($_POST['m_map_gcenter']));
      }
    }

    // exit

    unset($_SESSION[QT]['sys_domains']);
    unset($_SESSION[QT]['sys_sections']);
    $oVIP->exiturl = "qnm_user.php?id=$id";
    $oVIP->exitname = $L['Profile'];
    $oHtml->PageBox(NULL,$L['S_save'],$_SESSION[QT]['skin_dir'],2);
  }
}

// --------
// STATS AND USER
// --------

// -- COUNT MESSAGES (not deleted) AND Find LAST MESSAGE element (parent id) --

$strParticip = '';
$oDB->Query('SELECT count(id) as countid FROM '.TABPOST.' WHERE status>=0 AND userid='.$id);
$row = $oDB->Getrow();
$intNotes = (int)$row['countid'];
if ( $intNotes>0 )
{
  $oDB->Query( 'SELECT p.id,p.pclass,p.pid,p.issuedate,p.username FROM '.TABPOST.' p WHERE p.status>=0 AND p.userid='.$id.' ORDER BY p.issuedate DESC' );
  $row = $oDB->Getrow();
  $strParticip .= ' <a class="small" href="'.Href('qnm_items.php').'?q=user&amp;v='.$id.'&amp;v2='.urlencode($row['username']).'" title="'.$L['Search'].'">'.L('Message',$intNotes).'</a>, <span class="small">'.L('last_message').' '.QTdatestr($row['issuedate'],'$','$',true,true).'</span> <a class="small" href="'.Href('qnm_item.php').'?nid='.$row['pclass'].'.'.$row['pid'].'&amp;note='.$row['id'].'" title="'.$L['H_Goto_message'].'">'.$L['Goto_message'].'</a>';
}

// -- QUERY USER --

$oDB->Query('SELECT * FROM '.TABUSER.' WHERE id='.$id);
$row = $oDB->Getrow();

  // check id
  if ( !isset($row['id']) ) $oHtml->PageBox(NULL,'Unknown user (id '.$id.')',$_SESSION[QT]['skin_dir'],2);

  // check privacy
  if ( $oVIP->user->IsPrivate($row['privacy'],$id) ) { $row['y']=null; $row['x']=null; }

  // staff cannot edit other staff nor admin
  if ( $row['role']=='M' && $oVIP->user->role=='M' && $oVIP->user->id!=$id ) { $bCanEdit=false; $_SESSION[QT]['editing']=false; }
  if ( $row['role']=='A' && $oVIP->user->role=='M' ) { $bCanEdit=false; $_SESSION[QT]['editing']=false; }

  // map settings
  if ( $bMap && !QTgempty($row['x']) && !QTgempty($row['y']) )
  {
    $y = (float)$row['y']; $x = (float)$row['x'];
    $strPname = QTconv($row['name'],'U');
    $oMapPoint = new cMapPoint($y,$x,$strPname,'',(isset($_SESSION[QT]['m_map']['sU']) ? QTexplode(($_SESSION[QT]['m_map']['sU'])) : array()));
    $arrExtData[$id] = $oMapPoint;
  }

// -- sitework limitation --
if ( $_SESSION[QT]['editing'] && !empty($_SESSION[QT]['m_sitework']) )
{
  echo '<p class="small">SiteWork module:<br />Profile cannot be changed from a remote computer.</p>';
  $_SESSION[QT]['editing'] = false;
}
// -- sitework limitation --

// --------
// HTML START
// --------

include 'qnm_inc_hd.php';

include 'qnm_inc_menu.php';
if ( !empty($strUsermenu) ) echo $strUsermenu;

// -- DISPLAY PROFILE --

$strMail = '';  if ( !empty($row['mail']) && !$oVIP->user->IsPrivate($row['privacy'],$id) ) $strMail = AsEmails($row['mail'],$id,0,'txt'.(QNM_JAVA_MAIL ? 'java' : ''),false,$_SESSION[QT]['skin_dir'],$L['E_javamail']);
$strPhone = ''; if ( !empty($row['phone']) && !$oVIP->user->IsPrivate($row['privacy'],$id) ) $strPhone = $row['phone'];
$strCoord = ''; if ( $bMap && !QTgempty($row['x']) && !QTgempty($row['y']) ) { $y = (float)$row['y']; $x = (float)$row['x']; if ( !$oVIP->user->IsPrivate($row['privacy'],$id) ) $strCoord = QTdd2dms($y).', '.QTdd2dms($x).' '.$L['Coord_latlon'].' <span class="small disabled">DD '.round($y,8).','.round($x,8).'</span>'; }
$strPriv = '';  if ( $row['privacy']!=2 && ($oVIP->user->IsStaff() || $oVIP->user->id==$id) ) $strPriv=' <img class="ico" src="admin/private'.$row['privacy'].'.gif" alt="'.$row['privacy'].'" title="'.L('Privacy_visible_'.$row['privacy']).'" />';
$strFirstdate =  QTexplodevalue($row['stats'],'firstdate');

if ( $bCanEdit )
{
echo '<p style="float:right;margin:5px">',( $_SESSION[QT]['editing'] ? '<a href="'.Href().'?id='.$id.'&amp;edit=0">'.$L['Edit_stop'].'</a>' : '<a href="'.Href().'?id='.$id.'&amp;edit=1">'.$L['Edit_start'].'</a>'),'</p>';
}
echo '<h2>',$oVIP->selfname,'</h2>
<table class="hidden">
<tr class="hidden">
<td class="hidden leftcolumn">',AsImgBox(AsImg( AsAvatarSrc($row['photo']),'',$row['name'],'member'),'picbox','',$row['name']),show_ban($oVIP->user->role,$row['closed']);

if ( $_SESSION[QT]['editing'] && $oVIP->user->id!=$id ) echo '<div class="profile warning"><p class="profile warning">',L('W_Somebody_else'),'</p></div>';

if ( $bCanEdit )
{
  if ( $_SESSION[QT]['avatar']!='0' )
  {
  echo '<p class="profile menu"><a href="',Href('qnm_user_img.php'),'?id=',$id,'">',$L['Change_picture'],'</a></p>';
  }
  echo '<p class="profile menu"><a href="',Href('qnm_user_pwd.php'),'?id=',$id,'">',$L['Change_password'],'</a></p>';
  echo '<p class="profile menu"><a href="',Href('qnm_user_question.php'),'?id=',$id,'">',$L['Secret_question'],'</a></p>';
  if ( $id>1 )
  {
  if ( $oVIP->user->role=='A' || ($oVIP->user->id==$id && QNM_CHANGE_USERNAME) ) echo '<p class="profile menu"><a href="',Href('qnm_user_rename.php'),'?id=',$id,'">',$L['Change_name'],'</a></p>';
  if ( $oVIP->user->id==$id ) echo '<p class="profile menu"><a href="',Href('qnm_unregister.php'),'?id=',$id,'">',$L['Unregister'],'</a></p>';
  }
}

echo '
</td>
<td class="hidden">
';

// --------
if ( !$_SESSION[QT]['editing'] ) {
// --------

echo '
<table class="data_o">
<tr class="data_o"><td class="headfirst">',$L['Username'],'</td><td><b>',$row['name'],'</b></td></tr>
<tr class="data_o"><td class="headfirst">',$L['Role'],'</td><td>',$L['Userrole_'.strtolower($row['role'])],'</td></tr>
';
if ( $oVIP->user->id==$id || $oVIP->user->IsStaff() ) echo '<tr class="data_o"><td class="headfirst">',$L['Privacy'],'</td><td>',$L['Email'],'/',$L['Phone'],($bMap ? '/'.$L['map']['position'] : ''),$strPriv,' ',L('Privacy_visible_'.$row['privacy']),'</td></tr>';
echo '
<tr class="data_o"><td class="headfirst">',$L['Location'],'</td><td>',$row['location'],'&nbsp;</td></tr>
<tr class="data_o"><td class="headfirst">',$L['Email'],$strPriv,'</td><td>',$strMail,'&nbsp;</td></tr>
<tr class="data_o"><td class="headfirst">',$L['Phone'],$strPriv,'</td><td>',$strPhone,'&nbsp;</td></tr>
<tr class="data_o"><td class="headfirst">',$L['Website'],'</td><td>',(empty($row['www']) ? '' : '<a class="small" href="'.$row['www'].'" target="_blank">'.$row['www'].'</a>'),'&nbsp;</td></tr>
<tr class="data_o"><td class="headfirst">',$L['Joined'],'</td><td>',(empty($strFirstdate) ? '&nbsp;' : QTdatestr($strFirstdate,'$','')),'</td></tr>
<tr class="data_o"><td class="headfirst">',$L['Messages'],'</td><td>',(empty($strParticip) ? '' : $strParticip),'&nbsp;</td></tr>
';
if ( $bMap ) {
if ( !empty($row['x']) && !empty($row['y']) ) {

  $strPlink = '<a href="http://maps.google.com?q='.$row['y'].','.$row['x'].'+('.urlencode($row['name']).')" class="small" title="'.$L['map']['In_google'].'" target="_blank">[G]</a>';
  $strPosition = '<div id="map_canvas" style="width:100%; height:350px;"></div>';
  echo '<tr class="data_o"><td class="headfirst">',$L['Coord'],'</td><td>',$strCoord,' ',$strPlink,'</td></tr>',PHP_EOL;
  echo '<tr class="data_o"><td colspan="2">',$strPosition,'</td></tr>',PHP_EOL;

}}

echo '</table>';

// --------
}
else
{
// --------

echo '
<form method="post" action="',Href('qnm_user.php'),'?id=',$id,'">
<table class="data_o">
<tr class="data_o"><td class="headfirst">',$L['Username'],'</td><td><b>',$row['name'],'</b></td></tr>
<tr class="data_o"><td class="headfirst">',$L['Role'],'</td><td>',$L['Userrole_'.strtolower($row['role'])],'</td></tr>
<tr class="data_o"><td class="headfirst">',$L['Privacy'],'</td><td>',$L['Email'],'/',$L['Phone'],($bMap ? '/'.$L['map']['position'] : ''),' <select size="1" name="privacy" class="small"><option value="0"',($row['privacy']=='0' ? QSEL : ''),'>',L('Privacy_visible_0'),'</option><option value="1"',($row['privacy']=='1' ? QSEL : ''),'>',L('Privacy_visible_1'),'</option><option value="2"',($row['privacy']=='2' ? QSEL : ''),'>',L('Privacy_visible_2'),'</option></select></td></tr>
<tr class="data_o"><td class="headfirst">',$L['Location'],'</td><td><input type="text" name="location" size="35" maxlength="24" value="',(empty($row['location']) ? '' : QTconv($row['location'],'I')),'" /></td></tr>
<tr class="data_o"><td class="headfirst">',$L['Email'],'</td><td><input type="text" name="mail" size="35" maxlength="64" value="',$row['mail'],'" /></td></tr>
<tr class="data_o"><td class="headfirst">',$L['Phone'],'</td><td><input type="text" name="phone" size="35" maxlength="64" value="',(empty($row['phone']) ? '' : QTconv($row['phone'],'I')),'" /></td></tr>
<tr class="data_o"><td class="headfirst">',$L['Website'],'</td><td><input type="text" name="www" size="35" maxlength="64" value="',(!empty($row['www']) ? $row['www'] : 'http://'),'" title="',$L['H_Website'],'" /></td>
</tr>
';

if ( $bMap )
{
echo '<tr class="data_o">
<td class="headfirst">',$L['Coord'],'</td>
<td><input type="text" id="yx" name="coord" size="32" value="'.(!empty($row['y']) ? $row['y'].','.$row['x'] : '').'"/> <span class="small">',$L['Coord_latlon'],'</span></td>
</tr>
';
}

echo '<tr class="data_o">
<td class="headfirst">&nbsp;</td>
<td><input type="hidden" name="id" value="',$id,'" /><input type="hidden" name="name" value="',$row['name'],'" /><input type="submit" name="ok" value="',$L['Save'],'" />',( !empty($error) ? ' <span class="error">'.$error.'</span>' : '' ),'</td>
</tr>
';

if ( $bMap )
{
  $strPosition  = '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">'.$L['map']['cancreate'];
  if ( !empty($row['x']) && !empty($row['y']) )
  {
    $_SESSION[QT]['m_map_gcenter'] = $row['y'].','.$row['x'];
    $strPosition  = '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">'.$L['map']['canmove'];
  }
  $strPosition .= ' | <a class="small" href="javascript:void(0)" onclick="createMarker(); return false;" title="'.$L['map']['H_pntadd'].'">'.$L['map']['pntadd'].'</a>';
  $strPosition .= ' | <a class="small" href="javascript:void(0)" onclick="deleteMarker(); return false;">'.$L['map']['pntdelete'].'</a>';
  $strPosition .= '</p>'.PHP_EOL;
  $strPosition .= '<div id="map_canvas"></div>'.PHP_EOL;
  //$strPosition .= '<input type="hidden" id="yx" name="yx" value="'.$_SESSION[QT]['m_map_gcenter'].'"/>'.PHP_EOL;
  $strPosition .= '<p class="small commands" style="margin:4px 0 2px 2px;text-align:right">'.$L['map']['addrlatlng'].' ';
  $strPosition .= '<input type="text" size="24" id="find" name="find" class="small" value="'.$_SESSION[QT]['m_map_gfind'].'" title="'.$L['map']['H_addrlatlng'].'" onkeypress="enterkeyPressed=qtKeyEnter(event); if (enterkeyPressed) showLocation(this.value,null);"/>';
  $strPosition .= '<img id="findit" src="qnmm_map_find.png" onclick="showLocation(document.getElementById(\'find\').value,null);" style="margin:0 0 0 2px;padding:2px;vertical-align:middle;width:16px;height:16px;border:solid 1px #cccccc;border-radius:3px;cursor:pointer" title="'.L('Search').'"/>';

  echo '<tr class="data_o">
  <td colspan="2">',$strPosition,'</td>
  </tr>
  ';
}

echo '</table>
</form>
';

// --------
}
// --------

echo '
</td>
</tr>
</table>
';

// --------
// HTML END
// --------

// MAP MODULE

if ( $bMap )
{
  $gmap_shadow = false;
  $gmap_symbol = false;
  if ( !empty($_SESSION[QT]['m_map_gsymbol']) )
  {
    $arr = explode(' ',$_SESSION[QT]['m_map_gsymbol']);
    $gmap_symbol=$arr[0];
    if ( isset($arr[1]) ) $gmap_shadow=$arr[1];
  }

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_map_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_map_gcenter']));

  // First item is the user's location and symbol
  if ( isset($arrExtData[$id]) )
  {
    // symbol by role
    $oMapPoint = $arrExtData[$id];
    if ( !empty($oMapPoint->icon) ) $gmap_symbol = $oMapPoint->icon;
    if ( !empty($oMapPoint->shadow) ) $gmap_shadow = $oMapPoint->shadow;

    // center on user
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    }
  }

  // update center
  $_SESSION[QT]['m_map_gcenter'] = $y.','.$x;

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();
  $gmap_markers[] = QTgmapMarker($_SESSION[QT]['m_map_gcenter'],true,$gmap_symbol,$row['name'],'',$gmap_shadow);
  $gmap_events[] = '
	google.maps.event.addListener(markers[0], "position_changed", function() {
		if (document.getElementById("yx")) {document.getElementById("yx").value = gmapRound(marker.getPosition().lat(),10) + "," + gmapRound(marker.getPosition().lng(),10);}
	});
	google.maps.event.addListener(marker[0], "dragend", function() {
		map.panTo(marker.getPosition());
	});';
  $gmap_functions[] = '
  function showLocation(address,title)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( markers[0] )
        {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: title});
        }
        gmapYXfield("yx",markers[0]);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
  function createMarker()
  {
    if ( !map ) return;
    if (infowindow) infowindow.close();
    deleteMarker();
    '.QTgmapMarker('map',true,$gmap_symbol).'
    gmapYXfield("yx",markers[0]);
    google.maps.event.addListener(markers[0], "position_changed", function() { gmapYXfield("yx",markers[0]); });
    google.maps.event.addListener(markers[0], "dragend", function() { map.panTo(markers[0].getPosition()); });
  }
  function deleteMarker()
  {
    if (infowindow) infowindow.close();
    for(var i=markers.length-1;i>=0;i--)
    {
      markers[i].setMap(null);
    }
    gmapYXfield("yx",null);
    markers=[];
  }
  ';
  include 'qnmm_map_load.php';
}

include 'qnm_inc_ft.php';
