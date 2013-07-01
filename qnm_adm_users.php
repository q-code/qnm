<?php

/**
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license.  If you did not receive a copy of
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
if ( $oVIP->user->role!='A' ) die(Error(13));

include Translate('qnm_adm.php');
include Translate('qnm_reg.php');
include 'bin/qnm_fn_sql.php';

// ---------
// INITIALISE
// ---------

$strGroups='';

$oVIP->selfurl = 'qnm_adm_users.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Users'];
$oVIP->exiturl = 'qnm_adm_users.php';
$oVIP->exitname = '&laquo; '.$L['Users'];

// --------
// SUBMITTED for delete
// --------

if ( isset($_POST['action']) && $_POST['action']=='delete' )
{
  $arr = array();
  if ( !isset($_POST['confirm']) )
  {
    // ask confirmation
    if ( isset($_POST['t1_cb']) ) $arr = (is_array($_POST['t1_cb']) ? $_POST['t1_cb'] : explode(',',$_POST['t1_cb']));
    if ( count($arr)>0 )
    {
    $oVIP->selfname = $L['User_del'];
    $oVIP->exitname = '&laquo; '.$L['Users'];
    $oHtml->PageBox
    (
    NULL,
    '<table class="hidden">
    <tr class="hidden">
    <td class="hidden">
    <form method="post" action="'.$oVIP->selfurl.'">
    <p style="text-align:right">'.$L['Delete'].' ('.L('User',count($arr)).')</p>
    <p style="text-align:right"><input type="hidden" name="action" value="delete" /><input type="hidden" name="confirm" value="1" /><input type="hidden" name="user" value="'.implode(',',$arr).'" /><input type="submit" name="ok" value="'.$L['Delete'].' !" /></p>
    </form>
    </td>
    </tr>
    </table>',
    'admin',
    0,
    '500px'
    );
    exit;
    }
    else
    {
    $warning = $L['E_nothing_selected'];
    }
  }
  else
  {
    $arr = explode(',',$_POST['user']);
    // delete (except admin and visitor)
    foreach($arr as $id) { if ($id>1) cVIP::Unregister(array('id'=>$id)); }
    // End message
    $_SESSION['pagedialog'] = 'O|'.$L['S_delete'].'|'.count($arr);
  }
}

// --------
// SUBMITTED for change role
// --------

if ( isset($_POST['action']) && $_POST['action']=='role' )
{
  $arr = array();
  if ( !isset($_POST['confirm']) )
  {
    // ask confirmation
    if ( isset($_POST['t1_cb']) ) $arr = (is_array($_POST['t1_cb']) ? $_POST['t1_cb'] : explode(',',$_POST['t1_cb']));
    if ( count($arr)>0 )
    {
      // ask status and confirmation
      $oVIP->selfname = $L['Change_role'];
      $oVIP->exitname = '&laquo; '.$L['Users'];
      $oHtml->PageBox
      (
        NULL,
        '<table class="hidden">
        <tr class="hidden">
        <td class="hidden">
        <form method="post" action="'.$oVIP->selfurl.'">
        <p style="text-align:right">'.$L['Role'].' <select name="role" size="1"><option value="A">'.$L['Userrole_a'].'</option><option value="M">'.$L['Userrole_m'].'</option><option value="U"'.QSEL.'>'.$L['Userrole_u'].'</option></select>&nbsp;</p>
        <p style="text-align:right">('.L('User',count($arr)).') <input type="hidden" name="confirm" value="1"/><input type="hidden" name="action" value="role"/><input type="hidden" name="user" value="'.implode(',',$arr).'"/><input type="submit" name="ok" value="'.$L['Ok'].'"/></p>
        </form>
        </td>
        </tr>
        </table>',
        'admin',
        0,
        '500px'
      );
      exit;
    }
    else
    {
    $warning = $L['E_nothing_selected'];
    }
  }
  else
  {
    $arr = explode(',',$_POST['user']);
    // status (except admin and visitor)
    $str = '';
    foreach($arr as $id) if ($id>1) $str.=','.$id;
    $str = substr($str,1);
    if ( !empty($str) )
    {
      $oDB->Query('UPDATE '.TABUSER.' SET role="'.strtoupper(substr($_POST['role'],0,1)).'" WHERE id IN ('.$str.')');
      // change section coordinator if required
      if ( $_POST['role']=='U' ) $oDB->Query('UPDATE '.TABSECTION.' SET moderator=1,moderatorname="Admin" WHERE moderator IN ('.$str.')');
    }
    // End message
    $_SESSION['pagedialog'] = 'O|'.$L['S_update'].'|'.count($arr);
  }
}

// INITIALISE

$oDB->Query('SELECT count(*) as countid FROM '.TABUSER.' WHERE id>0');
$row = $oDB->Getrow();
$intUsers = intval($row['countid']);

$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br />'.$L['Users'].' ('.$intUsers.')';

$strGroup = 'all';
$intLimit = 0;
$intPage  = 1;
$strOrder = 'name';
$strDirec = 'asc';
$strCateg = 'all';

// security check 1
if ( isset($_GET['group']) ) $strGroup = strip_tags($_GET['group']);
if ( isset($_GET['page']) ) $intPage = intval(strip_tags($_GET['page']));
if ( isset($_GET['order']) ) $strOrder = strip_tags($_GET['order']);
if ( isset($_GET['dir']) ) $strDirec = strtolower(strip_tags($_GET['dir']));
if ( isset($_GET['cat']) ) $strCateg = strip_tags($_GET['cat']);

// security check 2 (no long argument)
if ( strlen($strGroup)>4 ) die('Invalid argument #group');
if ( strlen($strOrder)>12 ) die('Invalid argument #order');
if ( strlen($strDirec)>4 ) die('Invalid argument #dir');

$intLimit = ($intPage-1)*25;

$strDataCommand = L('selection').': <a class="datasetcontrol" onclick="datasetcontrol_click(\'role\'); return false;" href="#">'.L('change_role').'</a> &middot; <a class="datasetcontrol" onclick="datasetcontrol_click(\'delete\'); return false;" href="#">'.L('delete').'</a>
';

// User menu (and jquery)

include 'qnm_inc_menu.php';

// --------
// HTML START
// --------

$oHtml->scripts[] = '<script type="text/javascript" src="bin/js/qnm_table.js"></script>
<script type="text/javascript">
<!--
function datasetcontrol_click(action)
{
  var doc = document.getElementById("form_users");
  doc.form_users_action.value=action;
  doc.submit();
  return;
}
//-->
</script>';
$oHtml->scripts_end[] = '<script type="text/javascript">
<!--
$(document).ready(function() {

  $("#t1 td:not(.tdcheckbox,.tdname)").click(function() { qtCheckboxToggle(this.parentNode.id.substring(3)); });

  // CHECKBOX ALL ROWS

  $("input[id=\'t1_cb\']").click(function() { qtCheckboxAll("t1_cb","t1_cb[]",true); });

  // SHIFT-CLICK CHECKBOX

  var lastChecked1 = null;
  var lastChecked2 = null;
  $("input[name=\'t1_cb[]\']").click(function(event) {
    if(!lastChecked1)
    {
      lastChecked1 = this;
      qtHighlight("tr_"+this.id,this.checked);
      return;
    }
    if(event.shiftKey)
    {
      var start = $("input[name=\'t1_cb[]\']").index(this);
      var end = $("input[name=\'t1_cb[]\']").index(lastChecked1);
      for(var i=Math.min(start,end);i<=Math.max(start,end);i++)
      {
      $("input[name=\'t1_cb[]\']")[i].checked = lastChecked1.checked;
      qtHighlight("tr_"+$("input[name=\'t1_cb[]\']")[i].id,lastChecked1.checked);
      }
    }
    lastChecked1 = this;
    qtHighlight("tr_"+this.id,this.checked);
  });
});
//-->
</script>';

include 'qnm_adm_inc_hd.php';

// Add user(s) form

echo '<p>',(empty($strUsermenu) ? '' : $strUsermenu),'</p>';
if ( !empty($strUserform) ) echo $strUserform;
echo '<p><a href="qnm_adm_users_imp.php">',$L['Users_import_csv'],'...</a></p>';

// --------
// Category subform
// --------

if ( $strCateg!='all' ) echo '<h1>',$L['Members_'.$strCateg],' (',$L['H_Members_'.$strCateg],')</h1>',PHP_EOL;

// --------
// Button line and pager
// --------

switch($strGroup)
{
  case 'all': $strWhere = ' WHERE id>0'; Break;
  case '0': $strWhere = ' WHERE '.FirstCharCase('name','a-z'); Break;
  default: $strWhere = ' WHERE '.FirstCharCase('name','u').'="'.$strGroup.'"'; Break;
}

if ( $strCateg=='CH' ) $strWhere .= ' AND id>1 AND children<>"0"'; //children
if ( $strCateg=='SC' ) $strWhere .= ' AND id>1 AND children="2"';  //sleeping children

$oDB->Query('SELECT count(id) as countid FROM '.TABUSER.$strWhere);
$row = $oDB->Getrow();
$intCount = $row['countid'];

// -- build pager --
$strPager = MakePager("qnm_adm_users.php?cat=$strCateg&group=$strGroup&order=$strOrder&dir=$strDirec",$intCount,25,$intPage);
if ( !empty($strPager) ) { $strPager = $L['Page'].$strPager; } else { $strPager=S; }
if ( $intCount<$intUsers ) $strPager = '<span class="small">'.$intCount.' '.$L['Selected_from'].' '.$intUsers.' '.L('users').'</span>'.($strPager==S ? '' : ' | '.$strPager);

// -- Display button line and pager --

if ( $intCount>25 || $strGroup!='all' ) echo '<table class="lettres"><tr class="lettres">',PHP_EOL,( $strCateg=='all' ? HtmlLettres($strGroup,$L['All']) : '' ),PHP_EOL,'</tr></table>',PHP_EOL;

// --------
// Memberlist
// --------

$table = new cTable('t1','data_u',$intCount);

if ( $intCount!=0 )
{
  echo PHP_EOL,'<form id="form_users" method="post" action="',$oVIP->selfurl,'"><input type="hidden" id="form_users_action" name="action" value=""/>',PHP_EOL;
  echo '<table class="hidden"><tr>',($intCount<3 ? '' : '<td class="pager_zt"><img src="admin/selection_up.gif" style="width:10px;height:10px;vertical-align:bottom;margin:0 10px 0 13px" alt="|" />'.$strDataCommand.'</td>'),'<td class="pager_zt right">',$strPager,'</td></tr></table>',PHP_EOL;

  // === TABLE DEFINITION ===
  $table->activecol = $strOrder;
  $table->activelink = '<a  href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order='.$strOrder.'&amp;dir='.($strDirec=='asc' ? 'desc' : 'asc').'">%s</a> <img class="i_sort" src="admin/sort_'.$strDirec.'.gif" alt="+"/>';
  $table->th['checkbox'] = new cTableHead(($table->rowcount<2 ? '&nbsp;' : '<input type="checkbox" name="t1_cb_all" id="t1_cb" />'));
  $table->th['name']     = new cTableHead($L['Username'],'','thname','<a  href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=name&amp;dir=asc">%s</a>');
  $table->th['role']     = new cTableHead($L['Role'],'','','<a  href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=role&amp;dir=asc">%s</a>');
  $table->th['firstdate']= new cTableHead($L['Registration'],'','','<a  href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=firstdate&amp;dir=asc">%s</a>');
  $table->th['notes']    = new cTableHead($L['Messages'],'','','<a  href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=notes&amp;dir=asc">%s</a>');
  $table->th['lastvisit'] = new cTableHead($L['Last_visit'].' (ip)','','','<a  href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=lastdate&amp;dir=asc">%s</a>');
  $table->th['id']       = new cTableHead('Id','','','<a  href="'.$oVIP->selfurl.'?cat='.$strCateg.'&amp;group='.$strGroup.'&amp;page=1&amp;order=id&amp;dir=asc">%s</a>');
  // create column data (from headers identifiers) and add class to all
  foreach($table->th as $key=>$th)
  {
    $table->td[$key] = new cTableData();
  }
  $table->th['id']->Add('style','width:50px');
  $table->td['checkbox']->Add('class','tdcheckbox');
  $table->td['name']->Add('class','tdname');

  // === TABLE START DISPLAY ===

  echo PHP_EOL;
  echo $table->Start().PHP_EOL;
  echo '<thead>'.PHP_EOL;
  echo $table->GetTHrow(2).PHP_EOL;
  echo '</thead>'.PHP_EOL;
  echo '<tbody>'.PHP_EOL;

  //-- LIMIT QUERY --
  $oDB->Query( LimitSQL('id,name,role,stats,(SELECT COUNT('.TABPOST.'.id) FROM '.TABPOST.' WHERE '.TABPOST.'.userid='.TABUSER.'.id AND '.TABPOST.'.status>=0) as notes FROM '.TABUSER.$strWhere, $strOrder.' '.strtoupper($strDirec), $intLimit, 25) );
  // --------

  $bEndCommands=false;
  $strAlt='r1';
  for($i=0;$i<25;$i++)
  {
    $row = $oDB->Getrow();
    if ( !$row ) break;
    if ( !$bEndCommands ) if ( $row['id']>1 ) $bEndCommands=true;

    $strFirstdate = QTexplodevalue($row['stats'],'firstdate');
    $strLastVisit = QTexplodevalue($row['stats'],'lastvisit');
    $strLastIp = QTexplodevalue($row['stats'],'lastip');

    // prepare row
    $table->row = new cTableRow( 'tr_t1_cb'.$row['id'], 'data_t '.$strAlt.' rowlight' );
    $table->td['checkbox']->content = ($row['id']>1 ? '<input type="checkbox" name="t1_cb[]" id="t1_cb'.$row['id'].'" value="'.$row['id'].'" />' : '&nbsp;');
    $table->td['name']->content     = '<a href="qnm_user.php?id='.$row['id'].'">'.$row['name'].'</a>';
    $table->td['role']->content     = $L['Userrole_'.strtolower($row['role'])];
    $table->td['firstdate']->content= (empty($strFirstdate) ? '&nbsp;' : QTdatestr($strFirstdate,'Y-m-d',''));
    $table->td['notes']->content    = ($row['notes']>0 ? '<a class="small" href="qnm_items.php?q=user&amp;v='.$row['id'].'&amp;v2='.urlencode($row['name']).'">'.$row['notes'].'</a>' : $row['notes']);
    $table->td['lastvisit']->content = (empty($strLastVisit) ? '&nbsp;' : QTdatestr($strLastVisit,'Y-m-d','')).(empty($strLastIp) ?  '&nbsp;' : ' ('.$strLastIp.')' );
    $table->td['id']->content       = $row['id'];

    echo $table->GetTDrow().PHP_EOL;
    if ( $strAlt=='r1' ) { $strAlt='r2'; } else { $strAlt='r1'; }
  }

  // === TABLE END DISPLAY ===

  echo '</tbody>',PHP_EOL;
  echo '</table>',PHP_EOL;

  echo '<table class="hidden"><tr class="hidden">',($bEndCommands ? '<td class="pager_zb"><img src="admin/selection_down.gif" style="width:10px;height:10px;vertical-align:top;margin:0 10px 0 13px" alt="|" />'.$strDataCommand.'</td>' : ''),'<td class="pager_zb right">',$strPager,'</td></tr></table>',PHP_EOL;
  echo '</form>',PHP_EOL;

}
else
{
  if ( !empty($strPager) ) echo '<table class="hidden"><tr><td class="pager_zt right">',$strPager,'</td></tr></table>',PHP_EOL;
  $table->th[] = new cTableHead('&nbsp;');
  echo $table->GetEmptyTable('<p style="margin-left:10px;margin-right:10px">'.L('None').'...</p>',true,'','r1');
}

// Define bottom page command (add csv to $intCount (max 10000))

$strCsv ='';
$oVIP->selfuri = GetURI('page');
if ( $oVIP->user->role!='V' )
{
  if ( $intCount<=$_SESSION[QT]['items_per_page'] )
  {
    $strCsv = '<a class="csv" href="'.Href('qnm_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].'</a>';
  }
  else
  {
    $strCsv = '<a class="csv" href="'.Href('qnm_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=p'.$intPage.'&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' ('.L('page').')</a>';
    if ( $intCount<=1000 )                   $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' ('.L('all').')</a>';
    if ( $intCount>1000 && $intCount<=2000 ) $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m1&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-1000)</a> &middot; <a class="csv" href="'.Href('qnm_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m2&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1000-'.$intCount.')</a>';
    if ( $intCount>2000 && $intCount<=5000 ) $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m5&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-5000)</a>';
    if ( $intCount>5000 )                    $strCsv .= ' &middot; <a class="csv" href="'.Href('qnm_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;sier=m5&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (1-5000)</a> &middot; < class="csv"a href="'.Href('qnm_adm_users_csv.php').'?'.$oVIP->selfuri.'&amp;size=m10&amp;n='.$intCount.'" title="'.$L['H_Csv'].'">'.$L['Csv'].' (5000-10000)</a>';
  }
}
if ( !empty($strCsv) )
{
  echo '<p class="right">',$strCsv,'</p>',PHP_EOL;
}

// --------
// HTML END
// --------

include 'qnm_adm_inc_ft.php';