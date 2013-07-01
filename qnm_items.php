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
if ( !$oVIP->user->CanView('V2') ) HtmlPage(11);
include 'bin/qnm_fn_sql.php';

// ---------
// INITIALISE
// ---------

// Uri arguments

$q = '';   // in case of search, query type
$s = '*';  // section filter can be '*' or [int]
$fs = '*';  // section filter ($fs will become $s if provided)
$ft = '*';  // type (of filter). Can be urlencoded
$fst = '*';  // status can be '*' or [int]0

// User's preferences (stored as coockies)

$u_fst='*';
$u_dir='asc';
$u_size='50';
$u_col='posts'; // last column
if ( isset($_COOKIE[QT.'_u_fst']) ) $u_fst=$_COOKIE[QT.'_u_fst'];
if ( isset($_COOKIE[QT.'_u_dir']) ) $u_dir=$_COOKIE[QT.'_u_dir'];
if ( isset($_COOKIE[QT.'_u_size']) ) $u_size=$_COOKIE[QT.'_u_size'];
if ( isset($_COOKIE[QT.'_u_col']) && !empty($_COOKIE[QT.'_u_col']) ) $u_col=$_COOKIE[QT.'_u_col'];

$fst=$u_fst;// filter by status
$dir=$u_dir;// id order ('asc'|'desc')
$size=$u_size;// page size (25|50|100)

// Read Uri arguments

QThttpvar('s fs ft fst q','str str str str str');
if ( $fs==='' ) $fs='*';
if ( $s==='' ) $s='*';
if ( $fst==='' ) $fst='*';
if ( $fs!=='*' ) $s=(int)$fs; // $fs becomes $s in this page
if ( $s!=='*' ) $s=(int)$s;
if ( $ft!=='*' ) $ft=urldecode($ft); // $ft must be urldecoded
if ( $fst!=='*' ) $fst=(int)$fst;
if ( !empty($q) ) $fst='*'; // status user preference is not applied in case of search results

// Section (can be an empty section in case of search result)

if ( $s==='*' )
{
  $oSEC = new cSection();
  $oSEC->name=$L['Search_results'];
}
elseif ( $s<0 )
{
  $oHtml->Redirect();
}
else
{
  $oSEC = new cSection($s);
  $oSEC->ReadTypes();
  $_SESSION[QT]['section']= $s; // previous section
}

$_SESSION[QT]['items_per_page'] = (int)$size; // register size as system page size
if ( $q=='tag' ) $u_col='tags';
if ( $q=='date' ) $u_col='insertdate';

// Access right

if ( $oSEC->type==1 && ($oVIP->user->role=='V' || $oVIP->user->role=='U') )
{
  // exit
  $oVIP->selfname = $L['Section'];
  $oVIP->exitname = ObjTrans('index','i',$_SESSION[QT]['index_name']);
  $oHtml->PageBox(NULL,$L['R_staff'],$_SESSION[QT]['skin_dir'],0);
}
if ( $oSEC->type==2 && $oVIP->user->role=='V' )
{
  // exit
  $oVIP->selfname = $L['Section'];
  $oVIP->exitname = ObjTrans('index','i',$_SESSION[QT]['index_name']);
  $oHtml->PageBox(NULL,$L['R_member'],$_SESSION[QT]['skin_dir'],0);
}

$oVIP->selfurl = 'qnm_items.php';
$oVIP->selfname = $L['Section'].': '.$oSEC->name;

if ( $q=='last' || $q=='user' ) { $oSEC->o_order='issuedate'; $dir='desc'; }
$strDirec = strtolower($dir);
$intPage = 1;
$intLimit = 0;
if ( isset($_GET['page']) ) { $intLimit = (intval($_GET['page'])-1)*$_SESSION[QT]['items_per_page']; $intPage = intval($_GET['page']); }
if ( isset($_GET['order']) ) $oSEC->o_order = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = strtolower($_GET['dir']);

// security check 2 (no long argument)

if ( strlen($oSEC->o_order)>12 ) die('Invalid argument #order');
if ( strlen($strDirec)>4 ) die('Invalid argument #dir');

// Criteria sql

$strFields = 'e.*';
$strFrom = ' FROM '.TABNE.' e ';
$strWhere = ' WHERE e.uid>0';
if ( $s!=='*' ) $strWhere .= ' AND e.section='.$s;
$strCount  = 'SELECT count(*) as countid'.$strFrom.$strWhere;
$strFilter = '';
if ( empty($q) )
{
  if ( $ft!=='*' ) $strFilter.=' AND e.type="'.$ft.'"';
  if ( $fst!=='*' ) $strFilter .=' AND e.status='.$fst;
}
else
{
  include 'qnm_items_qry.php';
}

// Count items (or result in case of query)

if ( empty($q) )
{
  // by default the total is the number of element in the section (or query result)
  $intCountItems = $oSEC->items;
  // if a filter is used, re-compute the number of items
  if ( !empty($strFilter) )
  {
    $oDB->Query( $strCount.$strFilter );
    $row = $oDB->Getrow();
    $intCountItems = (int)$row['countid'];
  }
}
else
{
  // in case of query, compute the number of items
  $oDB->Query( $strCount );
  $row = $oDB->Getrow();
  $oSEC->items = (int)$row['countid'];
  $intCountItems = $oSEC->items;
}

// Process User's preferences (from POST) and define User Menu

include 'qnm_inc_menu.php';

// Define Page Commands (and pager)

$oVIP->selfuri = GetURI('order,dir'.($fs==='*' ? ',fs' : '').($ft==='*' ? ',ft' : '').($fst==='*' ? ',fst' : ''));

$strCommand = '';
$strCommand = '<div class="pagecmd">'.PHP_EOL;
$strCommand .= '<ul>'.PHP_EOL;
if ( QNM_BACKBUTTON ) $strCommand .= '<li><a href="'.Href($oVIP->exiturl).'">'.QNM_BACKBUTTON.'</a></li>';
if ( empty($q) )
{
  if ( $oSEC->status==1 && !$oVIP->user->IsStaff() )
  {
    $strCommand .= '<li><span>'.$L['E_section_closed'].'</span></li>';
  }
  else
  {
    $strCommand .= '<li><a href="'.Href('qnm_form_newclass.php').'?s='.$oSEC->uid.'">'.$L['Create_items'].'</a></li>';
  }
  $strCommand .= ( $oSEC->items<3 || $_SESSION[QT]['board_offline']=='1' || ($oVIP->user->role=='V' && $_SESSION[QT]['visitor_right']<5) ? '' : '<li>'.AsImg($_SESSION[QT]['skin_dir'].'/ico_search.gif','Search',$L['Search'],'i_item','','qnm_search.php?fs='.$s).'</li>' );
}
else
{
  $strCommand .= '<li><a href="'.Href('qnm_'.($q=='qs' ? 's_' : '').'search.php').'?'.$oVIP->selfuri.'">'.$L['Search'].'</a></li>';
}
$strCommand .= '</ul>'.PHP_EOL;
$strCommand .= '</div>'.PHP_EOL;

$strPager = MakePager($oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order='.$oSEC->o_order.'&amp;dir='.$strDirec,$intCountItems,$_SESSION[QT]['items_per_page'],$intPage);
if ($strPager!='') $strPager = $L['Page'].$strPager;

// Define Dataset Commands

$strDataCommand = '';
if ( $intCountItems>0 )
{
$strDataCommand .= '
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'activate\'); return false;" href="#" title="'.L('cmd_Activate').'">'.L('Activate').'</a> &middot;
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'inactivate\'); return false;" href="#" title="'.L('cmd_Inactivate').'">'.L('Inactivate').'</a> &middot;
<a class="datasetcontrol secondary" onclick="datasetcontrol_click(\'t1_cb[]\',\'note\'); return false;" href="#" title="'.L('cmd_Add_note').'">'.L('Add_note').'</a><span class="secondary"> &middot;</span>
<a class="datasetcontrol secondary" onclick="datasetcontrol_click(\'t1_cb[]\',\'delete\'); return false;" href="#" title="'.L('cmd_Delete').'">'.L('Delete').'</a><span class="secondary"> &middot;</span>
<a class="datasetcontrol" onclick="datasetcontrol_click(\'t1_cb[]\',\'type\'); return false;" href="#" title="'.L('cmd_More').'">'.L('More').'...</a>
';
}
$strDataFilter = '';
if ( empty($q) ) {
if ( $oSEC->items>2 ) {

  // use favorite if possible
  $arrFilters = array();
  $arrOthers = $oSEC->types_e;
  $strTypes = '';
  if ( $s>= 0 ) {
  if ( $oSEC->items>25 ) {
  if ( count($oSEC->types_e)>10 ) {
    $arrFilters = $oSEC->GetFilter(); // keys are urlencoded
    if ( count($arrFilters)>0 )
    {
    $strTypes .= '<optgroup label="'.L('Favorites').'">'.QTasTag($arrFilters,urlencode($ft)).'</optgroup>'.PHP_EOL;
    }
  }}}
  if ( count($arrFilters)>0 )
  {
    $arrOthers = array_diff($arrOthers,$arrFilters);
    $strTypes .= '<optgroup label="'.L('Others').'">'.QTasTag($arrOthers,urlencode($ft)).'</optgroup>'.PHP_EOL;
  }
  else
  {
    $strTypes .= QTasTag($arrOthers,urlencode($ft)).PHP_EOL;
  }

  $strDataFilter .= '<span class="nowrap">'.(empty($strDataCommand) ? '' : ' &middot; ').L('Show').'&nbsp;';
  if ( count($oSEC->types_e)>0 )
  {
  $strDataFilter .= '<select class="small" id="ft" name="ft" onchange="this.form.submit();">
  <option value="*"'.($ft==='*' ? QSEL : '').'>'.L('all_types').'</option>
  '.$strTypes.'
  </select>'.PHP_EOL;
  }
  $strDataFilter .= '<select class="small" id="fst" name="fst" onchange="this.form.submit();">
  <option value="*"'.($fst==='*' ? QSEL : '').'>'.L('all_statuses').'</option>
  <option value="1"'.($fst===1 ? QSEL : '').'>'.L('Actives').'</option>
  <option value="0"'.($fst===0 ? QSEL : '').'>'.L('Inactives').'</option>
  <option value="-1"'.($fst===-1 ? QSEL : '').'>'.L('Deleted').'</option>
  </select>&nbsp;<span'.($intCountItems<$oSEC->items ? ' class="bold red"' : '').'>'.$intCountItems.'</span>/'.$oSEC->items.'</span>';

}}

// MAP MODULE

$bMap=false;
if ( UseModule('map') )
{
  include 'qnmm_map_lib.php';
  $bMap = QTgcanmap(($s==='*' ? 'S' : $s),true,true); // Read the config file to initialize the $_SESSION[QT]['m_map'][] arguments // Do a main list check
  if ( $bMap )
  {
    include Translate('qnmm_map.php');
    $oHtml->links[]='<link rel="stylesheet" type="text/css" href="qnmm_map.css" />';
    $strMapOptionKey = 's'.($s==='*' ? 'S' : $s); // key of the map settings (search,userlist or section): 'sS', 'sU' or 's'.id
  }
  if ( isset($_GET['hidemap']) ) $_SESSION[QT]['m_map_hidelist']=true;
  if ( isset($_GET['showmap']) ) $_SESSION[QT]['m_map_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_map_hidelist']) ) $_SESSION[QT]['m_map_hidelist']=false;
}

// Table definition

$table = new cTable('t1','data_t');

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qnm_table.js"></script>
<script type="text/javascript">
<!--
$(document).ready(function() {

  // TAG infotip

  $(".tag").hover(function() {
    var oTag = $(this);
    $.post("qnm_j_tagdesc.php",{s:"'.$s.'",val:oTag.html(),lang:"'.GetIso().'",na:""}, function(data) { oTag.attr({title:data}); } );
  });

  // CHECKBOX checked when clicking some columns

  $("#t1 td:not(.tdcheckbox,.tdid,.tdlinks,.tdpopup)").click(function() { qtCheckboxToggle(this.parentNode.id.substring(3)); });

  // CHECKBOX ALL ROWS

  $("input[id=\'t1_cb\']").click(function() { qtCheckboxAll("t1_cb","t1_cb[]",true); });

  // CLICK POPUP/POPOUT

  $(".popup_ctrl").click(function() {

  var oCtrl = $(this);
  var ctrlid = oCtrl.attr("id");
  var aIds = ctrlid.split("_",3); // array of the frist 3 "_" blocks
  var tableid = aIds[1];
  var uid = aIds[2];

  // Even when classname is changed, the jquery selector continues executing this click event!
  // that is why, to popout, we have to check attr("class") to have the ACTUAL class name

  if ( oCtrl.attr("class")=="popout_ctrl" )
  {
    qtPopoutRows(tableid,"tr_"+tableid+"_cb"+uid,ctrlid);
  }
  else
  {
    qtPopupRows(tableid,"tr_"+tableid+"_cb"+uid,[["","","<img src=\"bin/css/qt_wait.gif\" alt=\"...\" title=\"searching\"/>","...","","...","...","..."]],ctrlid);
    var bsection='.(empty($q) ? '0' : '1').'; // 1 when addition section column exist
    $.post(
      "qnm_j_item_links.php",
      {flds:"e.uid,e.class,e.status,e.id,e.type,e.items+e.conns as nec,e.links,e.address,e.descr,e.posts", uid:uid, options:""},
      function(data)
      {
        var arrData = qtSplitDataString(data); // data are 0) P|C|E, 1)ldir, 2) the fields
        var arrView = [];
        for(var i=0;i<arrData.length;i++)
        {
          arrView[i] = [" ","<img src=\""+GetIconSrc("n"+arrData[i][3])+"\" alt=\"+\" class=\"i_item\"/>"," "," "," "," "," "," "];
          if ( arrData[i][0]=="1" ) arrView[i][1] = "<img src=\""+GetIconSrc("le","")+"\" alt=\"-\" class=\"i_sub\"/>" + arrView[i][1];
          if ( arrData[i][0]=="2" ) arrView[i][1] = "<img src=\""+GetIconSrc("lc",arrData[i][1])+"\" alt=\"-\" class=\"i_rel\"/>" + arrView[i][1];
          arrView[i][2] = (arrData[i][0]=="3" ? "'.L('in').' " : "")+"<a class=\"small\" href=\"qnm_item.php?nid="+arrData[i][3]+"."+arrData[i][2]+"\">"+arrData[i][5]+"<\/a>"+(arrData[i][4]=="0" ? "<span style=\"color:#FF0000\">&bull;<\/span>" : "");
          var str = arrData[i][8] + "r, " + arrData[i][7] + "e";
          arrView[i][3] = "(" + str + ")";
            if ( arrData[i][8]+arrData[i][7]==0 && arrData[i][1]=="<null>" ) arrView[i][3] = "('.L('free').')";
            if ( arrData[i][8]+arrData[i][7]==0 && arrData[i][1]!="<null>" ) arrView[i][3] = "&nbsp;";
          arrView[i][4] = "&nbsp;";
          if ( arrData[i][6]!="<null>" && arrData[i][6]!="" ) arrView[i][5] = arrData[i][6]; // type
          if ( arrData[i][9]!="<null>" && arrData[i][9]!="" ) arrView[i][6+bsection] = arrData[i][9]; // address
          if ( arrData[i][10]!="<null>" && arrData[i][10]!="" ) arrView[i][7+bsection] = arrData[i][10]; // descr
          if ( arrData[i][11]!="<null>" && arrData[i][11]!="0" ) arrView[i][8+bsection] = "<img src=\""+GetIconSrc("notes","")+"\" alt=\"N\" title=\""+NoteTitle(arrData[i][11])+"\" class=\"i_note\"/>" + (arrData[i][11]=="1" ? "" : arrData[i][11]); // posts
        }
        qtPopoutRows(tableid,"tr_"+tableid+"_cb"+uid,ctrlid);
        qtPopupRows(tableid,"tr_"+tableid+"_cb"+uid,arrView,ctrlid);
      }
      );
  }

  });

  // SHIFT-CLICK CHECKBOX

  var lastChecked = null;
  $("input[name=\'t1_cb[]\']").click(function(event) {
    if(!lastChecked)
    {
      lastChecked = this;
      qtHighlight("tr_"+this.id,this.checked);
      return;
    }
    if(event.shiftKey)
    {
      var start = $("input[name=\'t1_cb[]\']").index(this);
      var end = $("input[name=\'t1_cb[]\']").index(lastChecked);
      for(var i=Math.min(start,end);i<=Math.max(start,end);i++)
      {
      $("input[name=\'t1_cb[]\']")[i].checked = lastChecked.checked;
      qtHighlight("tr_"+$("input[name=\'t1_cb[]\']")[i].id,lastChecked.checked);
      }
    }
    lastChecked = this;
    qtHighlight("tr_"+this.id,this.checked);
  });

});

function datasetcontrol_click(checkboxname,action)
{
  var checkboxes = document.getElementsByName(checkboxname);
  var n = 0;
  for (var i=0; i<checkboxes.length; i++) if ( checkboxes[i].checked ) n++;
  if ( n>0 )
  {
  document.getElementById(\'form_t1_a\').value=action;
  document.getElementById(\'form_t1\').action=\'qnm_f_ne_edits.php\';
  document.getElementById(\'form_t1\').submit();
  return;
  }
  else
  {
  alert(qtHtmldecode("'.L('No_selected_row').'"));
  return false;
  }
}
function NoteTitle(i)
{
  return i + " " + (i>1 ? "'.L('in_process_notes').'" : "'.L('in_process_note').'");
}
function GetIconSrc(e,ldir)
{
  if (e=="ne") return "'.$_SESSION[QT]['skin_dir'].'/ico_ne_e.gif";
  if (e=="nc") return "'.$_SESSION[QT]['skin_dir'].'/ico_ne_c.gif";
  if (e=="nl") return "'.$_SESSION[QT]['skin_dir'].'/ico_ne_l.gif";
  if (e=="le") return "'.$_SESSION[QT]['skin_dir'].'/ico_link_c.gif";
  if (e=="lc")
  {
  if (ldir=="-1") return "'.$_SESSION[QT]['skin_dir'].'/ico_nc_-1.gif";
  if (ldir=="1") return "'.$_SESSION[QT]['skin_dir'].'/ico_nc_1.gif";
  if (ldir=="2") return "'.$_SESSION[QT]['skin_dir'].'/ico_nc_2.gif";
  return "'.$_SESSION[QT]['skin_dir'].'/ico_nc_0.gif";
  }
  if (e=="notes") return "'.$_SESSION[QT]['skin_dir'].'/ico_notes.gif";
  return "";
}
function CheckboxToggle(id)
{
  qtCheckboxToggle("t1_cb"+id);
}
//-->
</script>
';

include 'qnm_inc_hd.php';

// SHOW SECTION NAME (and usermenu)

if ( !empty($strUsermenu) ) echo $strUsermenu;

echo '<p id="sectiondesc">';

switch($q)
{
  case '': if ($_SESSION[QT]['section_desc']=='1') if (!empty($oSEC->descr)) echo $oSEC->descr; break;
  case 'tag': echo L('Item',$oSEC->items),' ',L('having'),' ',L('tag'),' ',strtolower(implode(' '.L('or').' ',$arrVlbl)); break;
  case 'kw': echo sprintf( L('Search_results_keyword'), $oSEC->items, strtolower(implode(' '.L('or').' ',$arrVlbl)) ); break;
  case 'user': echo sprintf( L('Search_results_user'), $oSEC->items, implode(' '.L('or').' ',$arrVlbl) ); break;
  case 'last': echo sprintf( L('Search_results_last'), $oSEC->items ); break;
  case 'fld': echo sprintf( L('Search_results_field'), $oSEC->items, ($v2=='descr' ? L('description') : L('address')).' '.strtolower(implode(' '.L('or').' ',$arrVlbl)) ); break;
  case 'date': echo sprintf( L('Search_results_date'), $oSEC->items ),' ',L(strtolower('date_'.$v2)),' ',implode(' '.L('or').' ',$arrVlbl); break;
  case 'rel':
    echo L('Item',$oSEC->items),' ',L('having'),' ',L('relation',(int)substr($v,0,1)),(strlen($v)>1 ? ' '.L('or').' '.L('more') : '');
    break;
  case 'qs':
    if ( substr($v,0,4)=='Type' )
    {
     echo L('Item',$oSEC->items),' ',L('having'),' ',strtolower(implode(' '.L('or').' ',$arrVlbl)); break;
    }
    else
    {
    echo sprintf( L('Search_results_id'), $oSEC->items, implode(' '.L('or').' ',$arrVlbl) );
    }
  case 'sub': echo L('Item',$oSEC->items),' ',L('having'),' ',L('sub-item',(int)substr($v,0,1)),(strlen($v)>1 ? ' '.L('or').' '.L('more') : ''); break;
  default:
    if ( empty($arrVlbl) )
      echo L('items',$oSEC->items);
    else
      echo sprintf( L('Search_results_id'), $oSEC->items, implode(' '.L('or').' ',$arrVlbl) );
}

echo '</p>',PHP_EOL;

if (!empty($q) )
{
  echo '<p id="sectiondesc_sub">';
  if ( $q==='rel' && $v!=='0' )
  {
    $arr = array('0'=>L('Undefined'),'1'=>L('Direct'),'2'=>L('Bidirectional'),'-1'=>L('Reveser'),'3'=>L('Not_undefined'),'4'=>L('Not_direct'),'5'=>L('Not_bidirectional'),'6'=>L('Not_reverse'));
    if ( array_key_exists($v2, $arr) ) echo L('direction').' "'.$arr[$v2].'" ';
  }
  if ( $q==='sub' && $v!=='0' )
  {
    $arr = array('e'=>L('Item'),'l'=>L('Line'),'c'=>L('Connector'),'-e'=>L('Not_item'),'-l'=>L('Not_line'),'-c'=>L('Not_connector'));
    if ( array_key_exists($v2, $arr) ) echo L('class').' "'.$arr[$v2].'" ';
  }
  if ( $s!=='*' ) echo L('only_in_section').' "'.$oVIP->sections[$s].'" ';
  if ( $ft!=='*' || $fst!=='*' ) echo ($ft==='*' ? '' : L('having_typename_containing').' "'.$ft.'" '),($fst==='*' ? '' : L('only_status').' "'.$oVIP->statuses[$fst]['name'].'"');
  echo '</p>',PHP_EOL;
}

// SHOW PAGE COMMAND (top)

echo '<table class="pagecmd_up"><tr class="pagecmd"><td>',$strCommand,'</td><td id="pager_zt">&nbsp;',$strPager,'</td></tr></table>',PHP_EOL;

// IF NO DATASET

if ( $intCountItems==0 )
{
  $str=null;
  if ( $fst===1 ) $fst = L('active');
  if ( $fst===0 ) $fst = L('inactive');
  if ( $fst===-1 ) $fst = L('deleted');
  if ( $fst==='*') $fst = L('all_statuses');
  if ( $intCountItems<$oSEC->items )
  $str = '<p style="margin-left:10px;margin-right:10px" class="small">'.L('Result').' <span'.($intCountItems<$oSEC->items ? ' class="bold red"' : '').'>'.$intCountItems.'</span>/'.$oSEC->items.' '.L('type').' "'.($ft==='*' ? L('all_types') : $ft).'" '.L('and').' '.L('status').' "'.$fst.'"</p>';
  $table->th[] = new cTableHead('&nbsp;');
  echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.L('No_item').'... '.$str.'</p>',true,'','r1');
  include 'qnm_inc_ft.php';
  exit;
}

// DATASET FORM AND CONTROLS

$str = Href().'?'.GetURI('page,order,dir'.($fs==='*' ? ',fs' : '').($ft==='*' ? ',ft' : '').($fst==='*' ? ',fst' : ''));
echo '<form id="form_t1" method="post" action="',$str,'">
<p class="datasetcontroltop">
<img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:middle;margin:0 5px 0 10px" alt="|" />',$strDataCommand,$strDataFilter,'
<input type="hidden" name="e" value="'.$str.'"/>
<input type="hidden" name="a" value="0" id="form_t1_a"/>
</p>
';

// === TABLE DEFINITION ===
  $table->rowcount = $oSEC->items;
  $table->activecol = $oSEC->o_order;
  $table->activelink = '<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order='.$oSEC->o_order.'&amp;dir='.($strDirec=='asc' ? 'desc' : 'asc').'">%s</a> <img class="i_sort" src="'.$_SESSION[QT]['skin_dir'].'/sort_'.$strDirec.'.gif" alt="+"/>';
  // create column headers
  $table->th['checkbox']= new cTableHead(($table->rowcount<2 ? '&nbsp;' : '<input type="checkbox" name="t1_cb_all" id="t1_cb" />'));
  $table->th['icon']    = new cTableHead('&nbsp;');
  $table->th['id']      = new cTableHead('Id','','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=id&amp;dir=asc">%s</a>');
  $table->th['links']   = new cTableHead(L('Links'));
  $table->th['popup']   = new cTableHead('&nbsp;');
  $table->th['type']    = new cTableHead(L('Type'),'','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=type&amp;dir=asc">%s</a>');
  $table->th['section'] = new cTableHead(L('Section'),'','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=section&amp;dir=asc">%s</a>'); // only when listting query results
  $table->th['address'] = new cTableHead(L('Address'),'','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=address&amp;dir=asc">%s</a>');
  $table->th['descr']   = new cTableHead(L('Description')); $table->th['descr']->Add('style','max-width:'.(empty($q) ? 300 : 250).'px;');
  $table->th['posts']   = new cTableHead(AsImg($_SESSION[QT]['skin_dir'].'/ico_notes.gif','N',L('In_process_notes'),'i_note'),'','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=posts&amp;dir=desc">%s</a>');
  switch($u_col)
  {
  case 'none':   unset($table->th['posts']); break; // when user request 'none'
  case 'status': unset($table->th['posts']); $table->th['status']= new cTableHead(L('Status'),'','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=status&amp;dir=asc">%s</a>'); break;
  case 'tags':   unset($table->th['posts']); $table->th['tags'] = new cTableHead(L('Tags')); break;
  case 'docs':   unset($table->th['posts']); $table->th['docs'] = new cTableHead(AsImg($_SESSION[QT]['skin_dir'].'/ico_attachment.gif','D',L('Documents'),'','i_doc')); break;
  case 'insertdate': unset($table->th['posts']); $table->th['insertdate'] = new cTableHead(L('Created'),'','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=insertdate&amp;dir=desc">%s</a>'); break;
  }
  // replace descr and posts by issuedate in case of query on notes
  if ( $q=='user' || $q=='last' || $q=='kw' )
  {
    unset($table->th['descr']);
    unset($table->th['posts']);
    $table->th['issuedate'] = new cTableHead(L('Messages'),'issuedate','','<a href="'.$oVIP->selfurl.'?'.$oVIP->selfuri.'&amp;order=issuedate&amp;dir=desc">%s</a>');
  }
  // create column data (from headers identifiers) and add class to all
  foreach($table->th as $key=>$th)
  {
    $table->th[$key]->Add('class','th'.$key);
    $table->td[$key] = new cTableData('','','td'.$key);
  }
  // prepare dynamic attributes for column 'status'
  if ( isset($table->td['status']) )
  {
    foreach($oVIP->statuses as $id=>$arrStatus) if ( !empty($arrStatus['color']) ) $table->td['status']->dynamicValues[$id]='background-color:'.$arrStatus['color'].';';
  }
  // remove the columns 'section' when listing section items
  if ( empty($q) ) { unset($table->th['section']); unset($table->td['section']); }

// === TABLE START DISPLAY ===

echo '
<!-- List of items -->
';
echo $table->Start().PHP_EOL;
echo '<thead>'.PHP_EOL;
echo $table->GetTHrow().PHP_EOL;
echo '</thead>'.PHP_EOL;
echo '<tbody>'.PHP_EOL;

if ( $oSEC->o_order=='issuedate' ) { $strAlias='p.'; } else { $strAlias='e.'; }
$strFullOrder = $strAlias.$oSEC->o_order.' '.strtoupper($strDirec); if ( $oSEC->o_order!='id' ) $strFullOrder .= ',e.id';
$oDB->Query( LimitSQL($strFields.$strFrom.$strWhere.$strFilter,$strFullOrder,$intLimit,$_SESSION[QT]['items_per_page'],$oSEC->items) );

$intWhile=0;
$intNIP=0;
$arrIds=array();
$arrTags=array();
$strAlt='r1';

while($row=$oDB->Getrow())
{
  if ( in_array($row['uid'],$arrIds) ) { $bAllowCheckbox=false; } else { $arrIds[]=$row['uid']; $bAllowCheckbox=true; } // detect double element in the list to remove the checkbox

  // prepare row
  $table->row = new cTableRow( ($bAllowCheckbox ? 'tr_t1_cb'.$row['uid'] : ''), 'data_t '.$strAlt.' rowlight' );

  // prepare values, and insert value into the cells
  $table->SetTDcontent( FormatTableRow('t1',$table->GetTHnames(),$row,true,$bMap,$bAllowCheckbox), false ); // adding extra columns not allowed
  if ( isset($table->td['status']) ) $table->td['status']->AddDynamicAttr('style',$row['status']);

  // display row
  echo $table->GetTDrow().PHP_EOL;
  if ( $strAlt=='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }

  // list tags (up to 50 distinct tags)
  if ( !empty($_SESSION[QT]['show_section_tags']) ) {
  if ( !empty($_SESSION[QT]['tags']) ) {
  if ( count($arrTags)<50) {
  if ( !empty($row['tags']) ) {
    $arr = explode(';',$row['tags']);
    foreach($arr as $str) {
    if ( !empty($str) ) {
      if ( !in_array($str,$arrTags) ) $arrTags[] = $str;
    }}
  }}}}

  // map settings
  if ( $bMap && !QTgempty($row['x']) && !QTgempty($row['y']) )
  {
    $y=(float)$row['y']; $x=(float)$row['x'];
    $oNE = new cNE($row);
    $strPname = QTconv($row['id'],'U');
    $strPinfo = $oNE->Dump(false,'',false,true).'<br/><a class="gmap" href="javascript:void(0)" onclick="CheckboxToggle('.$oNE->uid.');">'.L('Select').'</a> &middot; <a class="gmap" href="'.Href('qnm_item.php').'?nid='.$row['class'].'.'.$row['uid'].'">'.L('Open').'</a>';
    $arrExtData[(int)$row['uid']] = new cMapPoint($y,$x,$strPname,$strPinfo,(isset($_SESSION[QT]['m_map'][$strMapOptionKey]) ? QTexplode($_SESSION[QT]['m_map'][$strMapOptionKey]) : array()));
  }

  $intWhile++;
  if ( $intWhile>=$_SESSION[QT]['items_per_page'] ) break;
}

// === TABLE END DISPLAY ===

echo '</tbody>
</table>
';

// Define bottom page command (add csv to $intCountItems (max 10000))

$strCsv ='';
$oVIP->selfuri = GetURI('page'.($fs==='*' ? ',fs' : '').($ft==='*' ? ',ft' : '').($fst==='*' ? ',fst' : ''));
if ( $oVIP->user->role!='V' )
{
  if ( $intCountItems<=$_SESSION[QT]['items_per_page'] )
  {
  $strCsv = '<a class="csv" href="'.Href('qnm_items_csv.php').'?'.$oVIP->selfuri.'&amp;n='.$intCountItems.'" title="'.$L['H_Csv'].'">'.$L['Csv'].'</a>';
  }
  else
  {
  $strCsv = '<a class="csv" href="'.Href('qnm_items_csv.php').'?'.$oVIP->selfuri.'&amp;size=p'.$intPage.'&amp;n='.$intCountItems.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' ('.L('page').')</a>';
  if ( $intCountItems<=1000 )                           $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_items_csv.php').'?'.$oVIP->selfuri.'&amp;n='.$intCountItems.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' ('.L('all').')</a>';
  if ( $intCountItems>1000 && $intCountItems<=2000 ) $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_items_csv.php').'?'.$oVIP->selfuri.'&amp;size=m1&amp;n='.$intCountItems.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-1000)</a> &middot; <a class="csv" href="'.Href('qnm_items_csv.php').'?'.$oVIP->selfuri.'&amp;size=m2&amp;n='.$intCountItems.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1000-'.$intCountItems.')</a>';
  if ( $intCountItems>2000 && $intCountItems<=5000 ) $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_items_csv.php').'?'.$oVIP->selfuri.'&amp;size=m5&amp;n='.$intCountItems.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-5000)</a>';
  if ( $intCountItems>5000 )                            $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_items_csv.php').'?'.$oVIP->selfuri.'&amp;size=m5&amp;n='.$intCountItems.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-5000)</a> &middot; < class="csv"a href="'.Href('qnm_items_csv.php').'?'.$oVIP->selfuri.'&amp;size=m10&amp;n='.$intCountItems.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (5000-10000)</a>';
  }
}
if ( !empty($strCsv) )
{
  $strPager = $strCsv.' &middot; '.$strPager;
  if ( substr($strPager,-10,10)==' &middot; ' ) $strPager = substr($strPager,0,-10);
}

// Dataset controls (if long page)

if ( $intCountItems>15 )
{
$strDataFilter = str_replace('"ft"','"ft2"',$strDataFilter);
$strDataFilter = str_replace('"fst"','"fst2"',$strDataFilter);
$strDataFilter = str_replace('onchange="','onchange="document.getElementById(this.id.substr(0,2)).value=this.value;',$strDataFilter);
echo '<p class="datasetcontrolbot">
<img src="admin/selection_down.gif" style="width:10px;height:10px;vertical-align:middle;margin:0 5px 0 10px" alt="|" />',$strDataCommand,$strDataFilter,'
</p>
';
}

// End dataset form

echo '
</form>
';

// SHOW PAGE COMMAND (bottom)

if ( $intCountItems>15 )
{
  echo '<table class="pagecmd_down"><tr class="pagecmd"><td class="pagecmd_down">',$strCommand,'</td><td id="pager_zb">&nbsp;',$strPager,'</td></tr></table>',PHP_EOL;
}
else
{
echo '<p id="pager_zb" class="csv">&nbsp;',$strPager,'</p>';
}

// MAP MODULE, Show map

if ( $bMap )
{
  echo '<!-- Map module -->',PHP_EOL;
  if ( count($arrExtData)==0 )
  {
    echo '<div class="gmap_disabled">'.$L['map']['E_noposition'].'</div>';
    $bMap=false;
  }
  else
  {
    //select zoomto (maximum 20 items in the list)
    $str = '';
    if ( count($arrExtData)>1 )
    {
      $str = '<p class="gmap commands" style="margin:0 0 4px 0"><a class="gmap" href="javascript:void(0)" onclick="zoomToFullExtend(); return false;">'.$L['map']['zoomtoall'].'</a> | '.L('Show').' <select class="gmap" id="zoomto" name="zoomto" size="1" onchange="gmapPan(this.value);">';
      $str .= '<option class="small_gmap" value="'.$_SESSION[QT]['m_map_gcenter'].'"> </option>';
      $i=0;
      foreach($arrExtData as $oMapPoint)
      {
      $str .= '<option class="small_gmap" value="'.$oMapPoint->y.','.$oMapPoint->x.'">'.$oMapPoint->title.'</option>';
      $i++; if ( $i>20 ) break;
      }
      $str .= '</select></p>'.PHP_EOL;
    }

    echo '<div class="gmap">',PHP_EOL;
    echo ($_SESSION[QT]['m_map_hidelist'] ? '' : $str.PHP_EOL.'<div id="map_canvas"></div>'.PHP_EOL);
    echo '<p class="gmap" style="margin:4px 0 0 0">',sprintf($L['map']['items'],strtolower( L('Item',count($arrExtData))),strtolower(L('Item',$intCountItems)) ),'</p>',PHP_EOL;
    echo '</div>',PHP_EOL;

    // Show/Hide

    if ( $_SESSION[QT]['m_map_hidelist'] )
    {
    echo '<div class="canvashandler"><a class="canvashandler" href="',Href(),'?',$oVIP->selfuri,'&amp;showmap"><img class="canvashandler" src="qnmm_map_dw.gif" alt="+"/>',$L['map']['Show_map'],'</a></div>',PHP_EOL;
    }
    else
    {
    echo '<div class="canvashandler"><a class="canvashandler" href="',Href(),'?',$oVIP->selfuri,'&amp;hidemap"><img class="canvashandler" src="qnmm_map_up.gif" alt="-"/>',$L['map']['Hide_map'],'</a></div>',PHP_EOL;
    }
  }
  echo '<!-- Map module end -->',PHP_EOL;
}

// TAGS FILTRING

if ( !empty($_SESSION[QT]['show_section_tags']) ) {
if ( !empty($_SESSION[QT]['tags']) ) {
if ( count($arrTags)>0 ) {
  echo '<div class="tagbox">',PHP_EOL;
  echo '<p class="tagbox">',$L['Show_only_tag'],'</p>',PHP_EOL;
  echo '<p class="tagbox">';
  foreach($arrTags as $strTag)
  {
    echo '<a class="tag" href="',Href('qnm_search.php'),'?q=tag&amp;fs=',$s,'&amp;v=',Urlencode($strTag.QNM_QUERY_SEPARATOR),'">',$strTag,'</a> ';
  }
  echo '</p>',PHP_EOL;
  echo '</div>',PHP_EOL;
}}}

// ---------
// HTML END
// ---------


// MAP MODULE

if ( $bMap && !$_SESSION[QT]['m_map_hidelist'] )
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

  // center on the first item
  foreach($arrExtData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    break;
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
      $gmap_markers[] = QTgmapMarker($oMapPoint->y.','.$oMapPoint->x,false,$user_symbol,$oMapPoint->title,$oMapPoint->info,$user_shadow);
    }
  }
  $gmap_functions[] = '
  function zoomToFullExtend()
  {
    if ( markers.length<2 ) return;
    var bounds = new google.maps.LatLngBounds();
    for (var i=markers.length-1; i>=0; i--) bounds.extend(markers[i].getPosition());
    map.fitBounds(bounds);
  }
  function showLocation(address)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( marker )
        {
          marker.setPosition(results[0].geometry.location);
        } else {
          marker = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: "Move to define the default map center"});
        }
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }

  ';
  include 'qnmm_map_load.php';
}

include 'qnm_inc_ft.php';
