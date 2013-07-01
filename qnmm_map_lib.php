<?php

/* ============
 * map_lib.php mpodule
 * ------------
 * version: 3.0 build:20130410
 * This is a module library
 * ------------
 * QTgempty()
 * QTgpoint()
 * QTgpointdelete()
 * QTgmapscript()
 * QTgmappoints()
 * ============ */

class cMapPoint
{
  // Properties

  public $y = 4.352;
  public $x = 50.847;
  public $title = ''; // marker tips
  public $info = '';  // html to display on click
  public $icon = false;
  public $shadow = false;
  public $printicon = false;
  public $printshadow = false;

  // Class constructor

  function __construct($y,$x,$title='',$info='',$arrStyle=array())
  {
    if ( isset($y) && isset($x) )
    {
      $this->y = $y;
      $this->x = $x;
    }
    else
    {
      global $qnm_gcenter;
      if ( isset($qnm_gcenter) )
      {
      $this->y = floatval(QTgety($qnm_gcenter));
      $this->x = floatval(QTgetx($qnm_gcenter));
      }
    }
    if ( !empty($title) ) $this->title = $title;
    if ( !empty($info) ) $this->info = $info;
    if ( !empty($arrStyle) ) $this->StyleFromArray($arrStyle);
  }

  // Methods

  function StyleFromArray($arr)
  {
    if ( !is_array($arr)) die('cMapPoint::MakeFromArray: Invalid arg, requires an array.');
    if ( isset($arr['icon']) && $arr['icon']!='0' ) $this->icon = $arr['icon'];
    if ( isset($arr['shadow']) && $arr['shadow']=='1' ) $this->shadow = true;
    if ( isset($arr['printicon']) && $arr['printicon']=='1' ) $this->printicon = true;
    if ( isset($arr['printshadow']) && $arr['printshadow']=='1' ) $this->printshadow = true;
  }

  function MarkerWith($str='shadow')
  {
    if ( $str=='shadow' )     { if ( $this->icon && $this->shadow ) return 'true'; }
    if ( $str=='printicon' )  { if ( $this->icon && $this->printicon ) return 'true'; }
    if ( $str=='printshadow' ){ if ( $this->icon && $this->printshadow ) return 'true'; }
    return 'false';
  }
}

// Attention x,y,z MUST be FLOAT (or null) !
// If x,y,z are NULL or not float, these functions will returns FALSE.
// When entity (topic) is created, the x,y,z are null (i.e. no point, no display)

// ---------

function QTgcanmap($strSection=null,$bConfigFile=false,$bCheckMainList=true)
{

  // Check system settings and module API key

  if ( !isset($strSection) ) die('QTgcanmap: arg #1 must be a section ref');
  if ( !isset($_SESSION[QT.'_usr_role']) ) $_SESSION[QT.'_usr_role']='V';
  if ( !isset($_SESSION[QT]['m_map_gkey']) ) return FALSE;
  if ( empty($_SESSION[QT]['m_map_gkey']) ) return FALSE;

  // Initialize settings (if not yet done)

  if ( !isset($_SESSION[QT]['m_map']) && $bConfigFile )
  {
  $_SESSION[QT]['m_map'] = array();
  global $oVIP;
  if ( file_exists($oVIP->prefix.'m_map/config.php') ) include $oVIP->prefix.'m_map/config.php';
  }

  // Check settings

  if ( !isset($_SESSION[QT]['m_map']['s'.$strSection]) ) return FALSE;

  $arr = QTexplode($_SESSION[QT]['m_map']['s'.$strSection]); // return at least the 'set'
  if ( !isset($arr['set']) || $arr['set']!=='1' ) return FALSE; // must be '1'

  // Check main list (in some pages, the gcanmap return false (list is N or user is not staff)

  if ( $bCheckMainList )
  {
    if ( !isset($arr['list']) ) return FALSE; // must be Y, N or M (staff only)
    if ( $arr['list']==='N' ) return FALSE;
    if ( $arr['list']==='M' && ($_SESSION[QT.'_usr_role']=='V' || $_SESSION[QT.'_usr_role']=='U') ) return FALSE;
  }

  // Otherwise is valid

  return TRUE;
}

// ---------

function QTgmapApi($strKey='',$strAddLibrary='')
{
  if ( empty($strKey) ) return '';
  return '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key='.$strKey.'&amp;sensor=false"></script>'.PHP_EOL.(empty($strAddLibrary) ? '' : $strAddLibrary);
}

// ---------

// QTgempty
// Return true when $i is empty or a value starting with '0.000000'

function QTgempty($i)
{
  if ( empty($i) ) return true;
  if ( !is_string($i) && !is_float($i) && !is_int($i) ) die('QTgempty: Invalid argument #1');
  if ( substr((string)$i,0,8)=='0.000000' ) return true;
  return false;
}

// ---------

function QTgmapMarker($centerLatLng='',$draggable=false,$gsymbol=false,$title='',$info='',$gshadow=false)
{
  if ( $centerLatLng==='' || $centerLatLng==='0,0' ) return 'marker = null;';
  if ( $centerLatLng=='map' )
  {
    $centerLatLng = 'map.getCenter()';
  }
  else
  {
    $centerLatLng = 'new google.maps.LatLng('.$centerLatLng.')';
  }
  if ( $draggable=='1' || $draggable==='true' || $draggable===true )
  {
  	$draggable='draggable:true, animation:google.maps.Animation.DROP,';
  }
  else
  {
  	$draggable='draggable:false,';
  }
  return '	marker = new google.maps.Marker({
		position: '.$centerLatLng.',
		map: map,
		' . $draggable . QTgmapMarkerIcon($gsymbol,$gshadow) . '
		title: qtHtmldecode("'.$title.'")
		});
		markers.push(marker); '.PHP_EOL.(empty($info) ? '' : '	gmapInfo(marker,\''.$info.'\');');
}

// ---------

function QTgmapMarkerIcon($gsymbol=false,$gshadow=false,$qnm_gprinticon=true,$qnm_gprintshadow=false)
{
  // returns the google.maps.Marker.icon argument (or nothing in case of default symbol)
  if ( empty($gsymbol) ) return '';
  $str = '';
  // icons are 32x32 pixels and the anchor depends on the name: (10,32) for puhspin, (16,32) for point, center form others
  $arr = explode('_',$gsymbol);
  switch($arr[0])
  {
    case 'pushpin':
      $str = 'icon: new google.maps.MarkerImage("qnmm_map/'.$gsymbol.'.png",new google.maps.Size(32,32),new google.maps.Point(0,0),new google.maps.Point(10,32)),';
      if ( $gshadow ) $str .= 'shadow: new google.maps.MarkerImage("qnmm_map/'.$gshadow .'.png",new google.maps.Size(59,32),new google.maps.Point(0,0),new google.maps.Point(10,32)),';
      break;
    case 'point':
     $str = 'icon: new google.maps.MarkerImage("qnmm_map/'.$gsymbol.'.png",new google.maps.Size(32,32),new google.maps.Point(0,0),new google.maps.Point(16,32)),';
      if ( $gshadow ) $str .= 'shadow: new google.maps.MarkerImage("qnmm_map/'.$gshadow .'.png",new google.maps.Size(59,32),new google.maps.Point(0,0),new google.maps.Point(16,32)),';
     break;
    default:
     $str = 'icon: new google.maps.MarkerImage("qnmm_map/'.$gsymbol.'.png",new google.maps.Size(32,32),new google.maps.Point(0,0),new google.maps.Point(16,16)),';
      if ( $gshadow ) $str .= 'shadow: new google.maps.MarkerImage("qnmm_map/'.$gshadow .'.png",new google.maps.Size(59,32),new google.maps.Point(0,0),new google.maps.Point(16,16)),';
     break;
  }
  return $str;
}

// ---------

function QTgmapMarkerMapTypeId($qnm_gbuttons)
{
  switch($qnm_gbuttons)
  {
	case 'S':
	case 'SATELLITE': return 'google.maps.MapTypeId.SATELLITE'; break;
	case 'H':
	case 'HYBRID': return 'google.maps.MapTypeId.HYBRID'; break;
	case 'P':
	case 'T':
	case 'TERRAIN': return 'google.maps.MapTypeId.TERRAIN'; break;
	default: return 'google.maps.MapTypeId.ROADMAP';
  }
}

// ---------

function QTgetx($str=null,$onerror=0.0)
{
  // checks
  if ( !is_string($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetx: arg #1 must be a string'); }
  if ( !strstr($str,',') ) { { if ( isset($onerror) ) return $onerror; die('QTgetx: arg #1 must be a string with 2 values'); }}
  $arr = explode(',',$str);
  $str = trim($arr[1]);
  if ( !is_numeric($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetx: x-coordinate is not a float'); }
  Return floatval($str);
}
function QTgety($str=null,$onerror=0.0)
{
  // checks
  if ( !is_string($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetx: arg #1 must be a string'); }
  if ( !strstr($str,',') ) { { if ( isset($onerror) ) return $onerror; die('QTgetx: arg #1 must be a string with 2 values'); }}
  $arr = explode(',',$str);
  $str = trim($arr[0]);
  if ( !is_numeric($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetx: y-coordinate is not a float'); }
  Return floatval($str);
}

// ---------

function QTdd2dms($dd,$intDec=0)
{
  $dms_d = intval($dd);
  $dd_m = abs($dd - $dms_d);
  $dms_m_float = 60 * $dd_m;
  $dms_m = intval($dms_m_float);
  $dd_s = abs($dms_m_float - $dms_m);
  $dms_s = 60 * $dd_s;
  return $dms_d.'&#176;'.$dms_m.'&#039;'.round($dms_s,$intDec).'&quot;';
}