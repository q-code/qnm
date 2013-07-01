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
include Translate('qnm_adm.php');
if ( $oVIP->user->role!='A' ) die(Error(13));

// INITIALISE

$oVIP->selfurl = 'qnm_adm_index.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_info'].'</span><br/>'.$L['Adm_status'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // check admin email and website url
  if ( !QTismail($_SESSION[QT]['admin_email'],false) ) $error='Error';
  if ( strlen($_SESSION[QT]['site_url'])<8 ) $error='Error';
  if ( !empty($error) )
  {
    $strFile=Translate('sys_online_error.php');
    if ( file_exists($strFile) ) { $strMsg = include $strFile; } else { $strMsg = '<p>Missing admin e-mail or website url...</p>'; }
    $oVIP->exiturl = 'qnm_adm_site.php';
    $oVIP->exitname = $L['Adm_general'];
    $oHtml->PageBox(NULL,$strMsg,'admin',0);
  }

  if ( isset($_POST['offline']) ) {
  if ( $_POST['offline']=='1' || $_POST['offline']=='0' ) {
    $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$_POST['offline'].'" WHERE param="board_offline"');
    $_SESSION[QT]['board_offline'] = $_POST['offline'];
  }}

  // exit
  $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['S_save'] : 'E|'.$error);
}

// --------
// HTML START
// --------

include 'qnm_adm_inc_hd.php';

// Prepare table template

$table = new cTable('','data_o');
$table->row = new cTableRow('','data_o');
$table->td[0] = new cTableData('','','headfirst'); $table->td[0]->Add('style','width:200px;');
$table->td[1] = new cTableData();
$table->td[2] = new cTableData('','','right');

// BOARD OFFLINE

echo '<h2>',$L['Adm_status'],'</h2>
<form method="post" action="',$oVIP->selfurl,'">
';
echo $table->Start().PHP_EOL;
echo '<tr class="data_o">
<td class="headfirst" style="width:200px;">',$L['Adm_status'],'</td>
<td class="bold center" style="background-color:',($_SESSION[QT]['board_offline']=='0' ? '#AAFFAA' : '#FFAAAA'),'">',($_SESSION[QT]['board_offline']=='0' ? $L['On_line'] : $L['Off_line']),'</td>
<td style="text-align:right">
<label for="offline">',$L['Change'],'</label>&nbsp;<select id="offline" name="offline" onchange="bEdited=true;">
<option value="0"',($_SESSION[QT]['board_offline']=='0' ? QSEL : ''),'>',$L['On_line'],'</option>
<option value="1"',($_SESSION[QT]['board_offline']=='1' ? QSEL : ''),'>',$L['Off_line'],'</option>
</select>&nbsp;<input type="submit" name="ok" value="',$L['Save'],'"/>
</td>
</tr>
</table>
</form>
';

// STATS

echo '<h2>',$L['Information'],'</h2>',PHP_EOL;

$oDB->Query('SELECT count(*) as countid FROM '.TABDOMAIN);
$row = $oDB->Getrow();
$intDomain = $row['countid'];

$oDB->Query('SELECT count(*) as countid FROM '.TABSECTION);
$row = $oDB->Getrow();
$intSection = $row['countid'];

$oDB->Query('SELECT count(*) as countid FROM '.TABSECTION.' WHERE type="1"');
$row = $oDB->Getrow();
$intHidden = $row['countid'];

$oDB->Query('SELECT count(*) as countid FROM '.TABPOST);
$row = $oDB->Getrow();
$intPost = $row['countid'];

echo $table->Start().PHP_EOL;
  $table->SetTDcontent( array(
      $L['Domains'].'/'.$L['Sections'],
      L('Domain',$intDomain).', '.L('Section',$intSection).' <span class="small">('.L('Hidden',$intHidden).')</span> <a href="qnm_stats.php">'.$L['Adm_stats'].'</a>',
      '&nbsp;') );
  echo $table->GetTDrow().PHP_EOL;
  $table->SetTDcontent( array( $L['Board_start_date'], QTdatestr($qnm_install,'$','')) );
  echo $table->GetTDrow().PHP_EOL;

$oDB->Query('SELECT count(id) as countid FROM '.TABUSER);
$row = $oDB->Getrow();
$intUser = $row['countid'];
$oDB->Query('SELECT count(id) as countid FROM '.TABUSER.' WHERE role="A"');
$row = $oDB->Getrow();
$intAdmin = $row['countid'];
$oDB->Query('SELECT count(id) as countid FROM '.TABUSER.' WHERE role="M"');
$row = $oDB->Getrow();
$intMod = $row['countid'];

$table->SetTDcontent( array(
    $L['Users'],
    $intUser.' <span class="small">('.L('Userrole_a',$intAdmin).', '.L('Userrole_m',$intMod).', '.L('User',($intUser-$intMod-$intAdmin)).')</span>',
    ) );
echo $table->GetTDrow().PHP_EOL;

// element

$oDB->Query( 'SELECT count(*) as countid FROM '.TABNE.' WHERE uid>0' );
$row = $oDB->Getrow();
$intElement = (int)$row['countid'];
$intConnector = 0;
if ( $intElement>0 )
{
  $oDB->Query( 'SELECT count(*) as countid FROM '.TABNC );
  $row = $oDB->Getrow();
  $intConnector = (int)$row['countid'];
}

$table->SetTDcontent( array(
    $L['Items'],
    L('Item',$intElement).' <span class="small">('.$intConnector.' connectors)</span>, '.L('Message',$intPost),
    '&nbsp;') );
echo $table->GetTDrow().PHP_EOL;
echo $table->End().PHP_EOL;

// PUBLIC ACCESS LEVEL

echo '
<h2>',$L['Public_access_level'],'</h2>
';
echo $table->Start().PHP_EOL;
$table->SetTDcontent( array( $L['Visitors_can'], $L['Pal'][$_SESSION[QT]['visitor_right']], '<a href="qnm_adm_secu.php">'.$L['Change'].'</a>') );
echo $table->GetTDrow().PHP_EOL;
echo $table->End().PHP_EOL;

// VERSIONS

$str='';
if ( file_exists('bin/phpinfo.php') ) $str .= ' &middot; <a href="bin/phpinfo.php">php info</a>';
if ( file_exists('qnm_adm_const.php') ) $str .= ' &middot; <a href="qnm_adm_const.php">php constants</a>';

echo '
<h2>',$L['Version'],'</h2>
';
echo $table->Start().PHP_EOL;
unset($table->td[2]);
$table->SetTDcontent( array('Q-NetManagement', QNMVERSION.', <span class="small">database '.$_SESSION[QT]['version'].', sid '.QT.'</span>') );
echo $table->GetTDrow().PHP_EOL;
$table->SetTDcontent( array('PHP', PHP_VERSION_ID.$str) );
echo $table->GetTDrow().PHP_EOL;
echo $table->End(true,true,true).PHP_EOL;

// HTML END

include 'qnm_adm_inc_ft.php';