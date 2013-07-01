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
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2013 The PHP Group
* @version    1.0 build:20130410
*/

session_start();
require_once 'bin/qnm_init.php';
if ( $oVIP->user->role!='A' ) die(Error(13));

include Translate('qnm_adm.php');
if ( !isset($_GET['a'])) die('Wrong action');

include 'bin/qnm_fn_sql.php';

// --------
// INITIALISE
// --------

$a = ''; // mandatory action
$d = -1; // domain (or days)
$s = -1; // section
$t = -1; // element (or move target)
$p = -1; // post
$v = ''; // value
$ok = ''; // submitted
QThttpvar('a d s t p v ok','str int int int int str str');

$oVIP->selfurl = 'qnm_adm_change.php';
$oVIP->selfname = 'QNM  command';

// --------
// EXECUTE COMMAND
// --------

switch($a)
{

// --------------
case 'deletedomain':
// --------------

  if ( $d<1 ) die('Wrong id '.$d);

  $oVIP->selfname = $L['Domain_del'];
  $oVIP->exiturl = 'qnm_adm_sections.php';
  $oVIP->exitname = '&laquo; '.$L['Sections'];

  // Ask destination

  if ( empty($ok) )
  {
    $arrDomains = GetDomains();
    $strTitle = $arrDomains[$d];
    $arrSections = QTarrget(GetSections($oVIP->user->role,$d));
    $strCont='';
    $strDest='';

    // list the domain content
    if ( count($arrSections)==0 )
    {
      $strCont = '<span class="small">0 '.L('Section').'</span>';
    }
    else
    {
      $strCont = QTasSpan($arrSections,'',array('format'=>L('Section').': %s','class'=>'small','endline'=>'<br/>'));
    }

    // list of domain destination
    if ( count($arrSections)>0 )
    {
      if ( isset($arrDomains[$d]) ) unset($arrDomains[$d]);
      $strDest = '<tr class="data_o">
      <td class="headfirst"><label for="t">'.L('Sections').'</label></td>
      <td><select id="t" name="t" size="1" class="small">'.QTasTag($arrDomains,'',array('format'=>L('Move_to').': %s')).'</select></td>
      </tr>';
    }

    // form
    $oHtml->PageBox
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="data_o">
    <tr class="data_o">
    <td class="headfirst" style="width:150px">'.$L['Title'].'</td>
    <td><b>'.$strTitle.'</b></td>
    </tr>
    <tr class="data_o">
    <td class="headfirst">'.$L['Containing'].'</td>
    <td>'.$strCont.'</td>
    </tr>
    '.$strDest.'
    <tr class="data_a">
    <td class="headfirst">&nbsp;</td>
    <td><input type="hidden" name="a" value="'.$a.'"/><input type="hidden" name="d" value="'.$d.'"/><input type="submit" name="ok" value="'.$L['Delete'].'"/></td>
    </tr>
    </table>
    </form>',
    'admin',
    0,
    '600px'
    );
    exit;
  }

  // Delete domain

  require_once 'bin/class/qnm_class_dom.php';
  if ( $t>=0 ) cDomain::MoveItems($d,$t);
  cDomain::Drop($d);

  // Exit
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'deletesection':
// --------------

  if ( $s<1 ) die('Wrong id '.$s);

  $oVIP->selfname = $L['Section_del'];
  $oVIP->exiturl = 'qnm_adm_sections.php';
  $oVIP->exitname = '&laquo; '.$L['Sections'];


  // Ask confirmation

  if ( empty($ok) )
  {
    $oSEC = new cSection($s);

    // list items
    if ( $oSEC->items>0 )
    {
      $strList = '<tr class="data_o"><td class="headfirst">&nbsp;</td><td><span class="italic bold">'.$L['H_Items_delete'].'</span><br/><a href="qnm_adm_change.php?a=itemmoveall&amp;s='.$s.'&amp;d=10">'.$L['Adm_items_move'].' &raquo;</a></td></tr>';
    }
    else
    {
      $strList = '';
    }

    $oHtml->PageBox
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="data_o">
    <tr class="data_o">
    <td class="headfirst" style="width:150px">'.$L['Section'].'</td>
    <td>'.$oSEC->name.'</td>
    </tr>
    <tr class="data_o">
    <td class="headfirst">'.$L['Containing'].'</td>
    <td>'.L('Item',$oSEC->items).', '.L('Message',$oSEC->StatsGet('notes')).'</td>
    </tr>
    '.$strList.'
    <tr class="data_o">
    <td class="headfirst">&nbsp;</td>
    <td><input type="hidden" name="a" value="'.$a.'"/><input type="hidden" name="s" value="'.$s.'"/><input type="submit" name="ok" value="'.$L['Delete'].'"/>
    </td>
    </tr>
    </table>
    </form>',
    'admin',
    0,
    '600px'
    );
    exit;
  }

  // Delete section

  cSection::Drop($s);

  // Exit
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'status_del':
// --------------

  if ( $v=='A' || $v=='Z' ) die('Wrong id '.$v);

  $oVIP->selfname = $L['Delete'].' '.L('status');
  $oVIP->exiturl = 'qnm_adm_statuses.php';
  $oVIP->exitname = '&laquo; '.$L['Statuses'];

  // ask confirmation
  if ( empty($ok) || !isset($_GET['to']) )
  {
    // list of status destination
    $strSdest = '';
    foreach($oVIP->statuses as $strKey=>$arrStatus)
    {
      if ( $strKey!=$v ) $strSdest .= '<option value="'.$strKey.'"/>'.$strKey.' - '.$arrStatus['statusname'].'</option>';
    }

    $oHtml->PageBox
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="data_o">
    <tr>
    <td class="headfirst" style="width:150px;">'.$L['Status'].'</td>
    <td><b>'.$v.'&nbsp;&nbsp;'.AsImg($_SESSION[QT]['skin_dir'].'/'.$oVIP->statuses[$v]['icon'],'-',$oVIP->statuses[$v]['statusname'],'i_status').'&nbsp;&nbsp;'.$oVIP->statuses[$v]['name'].'</b></td>
    </tr>
    <tr>
    <td class="headfirst">'.$L['Description'].'</td>
    <td>'.$oVIP->statuses[$v]['statusdesc'].'</td>
    </tr>
    <tr>
    <td class="headfirst">'.$L['Move'].'</td>
    <td>'.$L['H_Status_move'].' <select name="to" size="1" class="small">'.$strSdest.'</select></td>
    </tr>
    <tr>
    <td class="headfirst">&nbsp;</td>
    <td>
    <input type="hidden" name="a" value="'.$a.'"/>
    <input type="hidden" name="v" value="'.$v.'"/>
    <input type="submit" name="ok" value="'.$L['Delete'].'"/></td>
    </tr>
    </table>
    </form><br/>',
    'admin',
    0,
    '600px'
    );
    exit;
  }

  // Delete status

  $oVIP->StatusDelete($v,$_GET['to']);

  // Exit
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'itemdeleteall':
// --------------

  if ( $s<0 ) die('Wrong id '.$s);

  $oVIP->selfname = $L['Adm_items_delete'];
  $oVIP->exiturl  = 'qnm_adm_items.php?d='.$d;
  $oVIP->exitname = '&laquo; '.$L['Items'];

  $oSEC = new cSection($s);
  $intCount = cSection::CountItems($s,'items'); // Recompute stats, number of items before the action
  $intCountZ = cSection::CountItems($s,'itemsZ');
  $intConns = cSection::CountItems($s,'conns');
  $intNotes = cSection::CountItems($s,'notes');

  // ask confirmation
  if ( empty($ok) )
  {
    $oHtml->PageBox
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="data_o">
    <tr>
    <td class="headfirst" style="width:150px;">'.$L['Section'].'</td>
    <td>'.$oSEC->name.'</td>
    </tr>
    <tr>
    <td class="headfirst">'.$L['Containing'].'</td>
    <td>'.L('Item',$intCount).' ('.L('Connector',$intConns).', '.L('Message',$intNotes).')</td>
    </tr>
    <tr>
    <td class="headfirst">&nbsp;</td>
    <td class="bold italic">'.$L['H_Items_delete'].'</td>
    </tr>
    <tr>
    <td class="headfirst">&nbsp;</td>
    <td><input type="hidden" name="a" value="'.$a.'"/>
    <input type="hidden" name="s" value="'.$s.'"/>
    <input type="hidden" name="d" value="'.$d.'"/>
    <input type="submit" name="ok" value="'.$L['Delete'].'"/> <span class="small">('.$intCount.')</span>&nbsp;&nbsp;'.( $intCountZ>0 ? ' <input type="submit" name="ok" value="'.$L['Delete_inactive'].'"/> <span class="small">('.$intCountZ.')</span>' : '').'</td>
    </tr>
    </table>
    </form><br/>',
    'admin',
    0,
    '600px'
    );
    exit;
  }

  // delete items (or closed only)
  cSection::DeleteItems($s,($ok!=$L['Delete'] ? '0' : ''));
  $oSEC->UpdateStats();

  // Exit
  $_SESSION['pagedialog'] = 'O|'.$L['Items_deleted'].'|'.$intCount;
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'itemmoveall':
// --------------

  if ( $s<0 ) die('Wrong id '.$s);

  $oVIP->selfname = $L['Adm_items_move'];
  $oVIP->exiturl  = 'qnm_adm_items.php?d='.$d;
  $oVIP->exitname = '&laquo; '.$L['Items'];

  $oSEC = new cSection($s);
  $intCount = cSection::CountItems($s,'items'); // Recompute stats, number of items before the action
  $intCountZ = cSection::CountItems($s,'itemsZ');
  $intConns = cSection::CountItems($s,'conns');
  $intNotes = cSection::CountItems($s,'notes');

  // Ask confirmation
  if ( empty($ok) || $t<0 )
  {
    $oHtml->PageBox
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="data_o">
    <tr>
    <td class="headfirst" style="width:150px;">'.$L['Section'].'</td>
    <td>'.$oSEC->name.'</td>
    </tr>
    <tr>
    <td class="headfirst">'.$L['Containing'].'</td>
    <td>'.L('Item',$intCount).' ('.L('Connector',$intConns).', '.L('Message',$intNotes).')</td>
    </tr>
    <tr>
    <td class="headfirst"><label for="t">'.$L['Move_to'].'</label></td>
    <td><select id="t" name="t" size="1">'.Sectionlist(null,$s).'</select></td>
    </tr>
    <tr>
    <td class="headfirst">&nbsp;</td>
    <td><input type="hidden" name="a" value="'.$a.'"/>
    <input type="hidden" name="s" value="'.$s.'"/>
    <input type="hidden" name="d" value="'.$d.'"/>
    <input type="submit" name="ok" value="'.$L['Move'].'"/> <span class="small">('.$intCount.')</span>&nbsp;&nbsp;'.( $intCountZ>0 ? ' <input type="submit" name="ok" value="'.$L['Move_inactive'].'"/> <span class="small">('.$intCountZ.')</span>' : '').'</td>
    </tr>
    </table>
    </form><br/>',
    'admin',
    0,
    '600px'
    );
    exit;
  }

  // move items
  cSection::MoveItems($s,$t,'',($ok==$L['Move'] ? '' : '0'));
  $oSEC->UpdateStats();
  $oSEC = new cSection($t); $oSEC->UpdateStats();


  // Exit
  $_SESSION['pagedialog'] = 'O|'.$L['Items_deleted'].'|'.$intCount;
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'tags_del':
// --------------

  if ( isset($_GET['tt']) ) { $tt=strip_tags($_GET['tt']); } else { $tt='en'; }

  $oVIP->selfname = $L['Delete'].' CSV';
  $oVIP->exiturl = 'qnm_adm_tags.php?tt='.$tt;
  $oVIP->exitname = '&laquo; '.$L['Tags'];


  // Ask confirmation

  if ( empty($ok) )
  {
    $oHtml->PageBox
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="data_o">
    <tr class="data_o">
    <td class="headfirst" style="width:150px">File</td>
    <td>'.$v.'</td>
    </tr>
    <tr class="data_o">
    <td class="headfirst">&nbsp;</td>
    <td><input type="hidden" name="a" value="'.$a.'"/><input type="hidden" name="tt" value="'.$tt.'"/><input type="hidden" name="v" value="'.$v.'"/><input type="submit" name="ok" value="'.$L['Delete'].'"/>
    </td>
    </tr>
    </table>
    </form>',
    'admin',
    0,
    '600px'
    );
    exit;
  }

  // Delete

  if ( file_exists('upload/'.$v) ) unlink('upload/'.$v);

  // Exit
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
default:
// --------------

  echo 'Unknown action';
  break;

// --------------
}

// Exit
$_SESSION['pagedialog'] = 'E|'.'Command ['.$a.'] failled...';
$oHtml->Redirect($oVIP->exiturl);