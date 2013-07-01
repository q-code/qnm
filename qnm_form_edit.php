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
if ( $oVIP->user->role=='V' ) HtmlPage(11);
if ( !$oVIP->user->CanView('V6') ) HtmlPage(11);
include 'bin/qnm_fn_sql.php';
include 'bin/qnm_fn_tags.php';

// --------
// INITIALISE
// --------

$nid = '';
QThttpvar('nid','str');
if ( empty($nid) ) die('Missing parameters: nid');
if ( GetUid($nid)==0 ) HtmlPage(20);

$oNE = new cNE($nid);

$oVIP->selfurl = 'qnm_form_edit.php';
$oVIP->selfname = L('Edit');
$oVIP->exiturl = 'qnm_item.php?nid='.GetNid($oNE);
$oVIP->exitname = 'Element';

// MAP MODULE

$bMap=false;
if ( UseModule('map') ) {
if ( $oNE->section>=0 ) {
  // Map configuration
  include 'qnmm_map_lib.php';
  $bMap = QTgcanmap($oNE->section,true,false); // Read the config file to initialize the $_SESSION[QT]['m_map'][] arguments // No main list check
  if ( $bMap )
  {
    include Translate('qnmm_map.php');
    $oHtml->links[]='<link rel="stylesheet" type="text/css" href="qnmm_map.css" />';
  }
  // Symbol
  if ( isset($_SESSION[QT]['m_map']['s'.$oNE->section]) ) { $arrMapSymbol=QTexplode($_SESSION[QT]['m_map']['s'.$oNE->section]); } else { $arrMapSymbol=array(); }
}}

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  $error = $oNE->SetFromPost();
  $str='';
  if ( QNM_EDIT_INSERTDATE && !empty($_POST['insertdate']) && $oNE->insertdate!=$_POST['insertdate'] )
  {
    $str = str_replace('-','',$_POST['insertdate']);
    if ( strlen($str)!=8 || !QTisvaliddate($str,true,true) ) $error = 'Invalid date ['.$_POST['insertdate'].']';
    $str = ',insertdate="'.$str.'"';
  }
  if ( empty($error) )
  {
    $oDB->Query( 'UPDATE '.TABNE.' SET id="'.$oNE->id.'",type="'.$oNE->type.'",address="'.$oNE->address.'",descr="'.$oNE->descr.'",m='.$oNE->m.',tags="'.$oNE->tags.'"'.$str.' WHERE uid='.$oNE->uid );
    if ( isset($_POST['coord']) )
    {
      if ( empty($_POST['coord']) )
      {
        QTgpointdelete(TABNE,$oNE->uid,'uid');
      }
      else
      {
        QTgpoint(TABNE,$oNE->uid,QTgety($_POST['coord']),QTgetx($_POST['coord']),'uid');
      }
    }
    $_SESSION['pagedialog']='O|'.L('S_update');
    $oHtml->Redirect($oVIP->exiturl);
  }
  else
  {
    $_SESSION['pagedialog']='E|'.$error;
  }
}

// --------
// HTML START
// --------

// tags preprocessing

$arr1 = TagsRead(GetIso(),$oNE->section);
$arr2 = TagsRead(GetIso(),'*');
$arrTags = array_merge($arr1,$arr2);
if ( count($arrTags)<100 )
{
  $arr1 = cSection::GetTagsUsed($oNE->section);
  foreach($arr1 as $strKey=>$strDesc) {
    if ( !isset($arrTags[$strKey]) ) $arrTags[$strKey]=$strDesc;
  }
}
$str = '';
foreach($arrTags as $strKey=>$strDesc) {
  $str .= '{n:"'.$strKey.'",d:"'.($strKey==$strDesc ? ' ' : substr($strDesc,0,64)).'"},';
}
$strTags = substr($str,0,-1);

// header

$oHtml->scripts[] = '<script type="text/javascript">
<!--
var e0 = "'.L('No_result').'";
var e1 = "'.L('try_other_lettres').'";

function split( val ) { return val.split( "'.QNM_QUERY_SEPARATOR.'" ); }
function extractLast( term ) { return split( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }

$(function() {
  $( "#type" ).autocomplete({
    minLength: 1,
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_type.php",
        dataType: "json",
        data: { term: request.term, s:'.$oNE->section.', e0: e0, e1: e1 },
        success: function(data) { response(data); }
      });
    },
    focus: function( event, ui ) {
      $( "#type" ).val( ui.item.rItem );
      return false;
    },
    select: function( event, ui ) {
      $( "#type" ).val( ui.item.rItem );
      return false;
    }
  })
  .data( "autocomplete" )._renderItem = function( ul, item ) {
    return $( "<li></li>" )
      .data( "item.autocomplete", item )
      .append( "<a class=\"jvalue\">" + item.rItem + (item.rInfo=="" ? "" : " &nbsp;<span class=\"jinfo\">(" + item.rInfo + ")</span>") + "</a>" )
      .appendTo( ul );
  };

  // TAG autocomplete

  $( "#tags" ).autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "qnm_j_tag.php",
        dataType: "json",
        data: { term: extractLast( request.term ), o_se:'.$oNE->section.', lang:"'.GetIso().'", e0:e0, e1:e1,e2:e1 },
        success: function(data) { response(data); }
      });
    },
    search: function() {
      // custom minLength
      var term = extractLast( this.value );
      if ( term.length < 1 ) { return false; }
  },
    focus: function( event, ui ) { return false; },
    select: function( event, ui ) {
      var terms = split( this.value );
      terms.pop(); // remove current input
      terms.push( ui.item.rItem ); // add the selected item
      terms.push( "" ); // add placeholder to get the comma-and-space at the end
      this.value = terms.join( "'.QNM_QUERY_SEPARATOR.'" );
      return false;
    }
  })
  .data( "autocomplete" )._renderItem = function( ul, item ) {
    return $( "<li></li>" )
      .data( "item.autocomplete", item )
      .append( "<a class=\"jvalue\">" + item.rItem + (item.rInfo=="" ? "" : " &nbsp;<span class=\"jinfo\">(" + item.rInfo + ")</span>") + "</a>" )
      .appendTo( ul );
  };

});
//-->
</script>
';

include 'qnm_inc_hd.php';

echo '<div id="elementdef" class="elementheader">
<h1>Network ',($oNE->class=='c' ? 'connector' : 'element'),'</h1>
</div>
';

echo '<p>',$oNE->Dump(true,'class="bold"');
if ( $oNE->class!='c' ) echo '<br/>',$oNE->DumpContent(false,'',20);
echo '</p>';

echo '<div class="frameelement">
';
if ( !empty($error) ) echo '<p style="margin:0"><span class="error">',$error,'</span></p>';
echo '<form method="post" action="',$oVIP->selfurl,'">
<p class="right small" style="margin:0">',L('Created'),' <input type="date" class="small" id="insertdate" size="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" name="insertdate" title="',L('Change_insertdate'),' (yyyy-mm-dd)" value="'.QTdatestr($oNE->insertdate,'Y-m-d','').'"/></p>
<table class="nefields">
';

foreach(cNE::GetFields($oNE->class,'useredit') as $strField)
{
  if ( $strField=='descr' || $strField=='tags' ) continue;
  echo '<tr class="nefields"><td class="nefields bold">',L(ucfirst($strField)),'</td><td class="nefields"><input class="nefields" type="text" id="'.$strField.'" name="'.$strField.'" value="',$oNE->$strField,'" size="50"/></td></tr>';

}

echo '<tr class="nefields"><td class="nefields bold">',$L['Tags'],'</td><td class="nefields"><input class="nefields" type="text" id="tags" name="tags" value="',str_replace(';',QNM_QUERY_SEPARATOR,$oNE->tags),'"/></td></tr>',PHP_EOL;
echo '<tr class="nefields"><td class="nefields bold">Description</td><td class="nefields"><textarea class="nefields" id="descr" name="descr" rows="5" cols="78">'.$oNE->descr.'</textarea></td></tr>',PHP_EOL;

if ( $bMap )
{
echo '<tr class="nefields"><td class="nefields bold">',$L['Coord'],'</td><td class="nefields"><input type="text" id="yx" name="coord" size="32" value="'.(!empty($oNE->y) ? $oNE->y.','.$oNE->x : '').'" /> <span class="small">',$L['Coord_latlon'],'</span></td></tr>',PHP_EOL;
}

echo '<tr class="nefields"><td class="nefields">&nbsp;</td><td class="nefields"><input type="hidden" name="nid" value="',$nid,'"/><input type="submit" name="ok" value="',$L['Save'],'"/></td></tr>',PHP_EOL;

if ( $bMap )
{
  $strPosition  = '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">'.$L['map']['cancreate'];
  if ( !empty($oNE->y) && !empty($oNE->x) )
  {
    $y = (float)$oNE->y; $x = (float)$oNE->x;
    $strPname = QTconv($oNE->id,'U');
    $oMapPoint = new cMapPoint($y,$x,$strPname,'',$arrMapSymbol);
    $arrExtData[$id] = $oMapPoint;
    $_SESSION[QT]['m_map_gcenter'] =$oNE->y.','.$oNE->x;
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

  echo '<tr class="nefields"><td colspan="2">',$strPosition,'</td></tr>',PHP_EOL;
}

echo '</table>
</form>
</div>
';

echo '<p><a href="',$oVIP->exiturl,'">&laquo; ',$oVIP->exitname,'</a></p>
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
  $y = (float)QTgety($_SESSION[QT]['m_map_gcenter']);
  $x = (float)QTgetx($_SESSION[QT]['m_map_gcenter']);

  // First item is the item's location and symbol
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
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
      $user_symbol = $gmap_symbol; // required to reset symbol on each user
      $user_shadow = $gmap_shadow;
      if ( !empty($oMapPoint->icon) ) $user_symbol = $oMapPoint->icon;
      if ( !empty($oMapPoint->shadow) ) $user_shadow = $oMapPoint->shadow;
      $gmap_markers[] = QTgmapMarker($oMapPoint->y.','.$oMapPoint->x,true,$user_symbol,$oMapPoint->title,$oMapPoint->info,$user_shadow);
    }
  }
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