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
* @version    1.0 build:20120526
*/

session_start();
require_once 'bin/qnm_init.php';
if ( $oVIP->user->role=='V' ) HtmlPage(11);

include 'bin/qnm_fn_sql.php';

// INITIALISE

$bCmdok = false;
$strMails = '';
$a = ''; // mandatory action
$s = -1; // section section
$t = ''; // element
$p = -1; // post or user-id
$v = ''; // value
$v1 = ''; // value
$v2 = ''; // value
$v3 = ''; // value
$ok = ''; // submitted
QThttpvar('a s t p v v1 v2 v3 ok','str int str int str str str str str');

$oVIP->selfurl = 'qnm_change.php';
$oVIP->selfname = 'QNM command';

// --------
// EXECUTE COMMAND
// --------

switch($a)
{

// --------------
case 'docrename':
// --------------

  if ( $p<0 ) die('Wrong user in '.$oVIP->selfurl); // $p is the document owner
  if ( $p!=$oVIP->user->id && $oVIP->user->role!='A' && $oVIP->user->role!='M' ) die('Wrong user in '.$oVIP->selfurl);

  $oVIP->selfname = $L['Edit'];
  $oVIP->exiturl = 'qnm_form_docs.php?nid='.$t;
  $oVIP->exitname = '&laquo; '.$L['Item'];

  // ask confirmation
  if ( empty($ok) )
  {
    $oDB->Query('SELECT * FROM '.TABDOC.' WHERE docfile="'.$v.'"');
    $row=$oDB->Getrow();
    $str = '';
    if ( isset($row['doctype']) && substr($row['doctype'],0,3)=='url' ) $str = '<p>Url <input type="text" name="url" id="url" pattern="(http://|https://).*" size="50" maxlength="255" value="'.$row['docpath'].$row['docfile'].'"/></p>';

    $bUrlimg = false;
    if (  substr($row['doctype'],0,3)=='url' && QTiswebimageext($row['docfile']) ) $bUrlimg = true;
    if ( $bUrlimg )
    {
      $strType='<p>'.L('Show').' <select name="type" id="type"><option value="url"'.($row['doctype']=='url' ? QSEL : '').'>Url</option><option value="urlimg"'.($row['doctype']=='urlimg' ? QSEL : '').'>'.L('Picture').'</option></select></p>';
    }
    else
    {
      $strType='';
    }

    $oHtml->PageBox
    (
    NULL,
    '<table class="hidden">
    <tr class="hidden">
    <td class="hidden"></td>
    <td class="hidden">
    <form method="post" action="'.Href().'">
    <p><span class="bold">'.$row['docname'].'</span> ('.QTdatestr($row['docdate']).')</p>'.$str.'
    <p>'.$L['Document_name'].' <input type="text" name="name" id="name" size="50" maxlength="255" value="'.$row['docname'].'"/></p>
    '.$strType.'
    <p><input type="hidden" name="a" value="'.$a.'"/>
    <input type="hidden" name="s" value="'.$s.'"/>
    <input type="hidden" name="t" value="'.$t.'"/>
    <input type="hidden" name="p" value="'.$p.'"/>
    <input type="hidden" name="v" value="'.$v.'"/>
    <input type="submit" name="ok" value="'.$L['Ok'].'"/></p>
    </form></td>
    </tr></table>',
    $_SESSION[QT]['skin_dir'],
    0,
    '500px'
    );
    exit;
  }

  // CHANGE

  $strName = QTconv(trim(strip_tags($_POST['name'])),'3'); if ( get_magic_quotes_gpc() ) $strName = stripslashes($strName);
  if ( empty($strName) ) $strName='Untitled';
  if ( strlen($strName)>64 ) $strName = substr($strName,0,63).'...';

  if ( isset($_POST['url']) )
  {
    $strType = 'url';
    $strFile = strtolower($_POST['url']);
    $strPath = '';
    if ( substr($strFile,0,7)=='http://' ) { $strPath='http://'; $strFile=substr($strFile,7); }
    if ( substr($strFile,0,8)=='https://' ) { $strPath='https://'; $strFile=substr($strFile,8); }
    if ( isset($_POST['type']) && $_POST['type']=='urlimg' ) $strType='urlimg';
    $oDB->Query( 'UPDATE '.TABDOC.' SET doctype="'.$strType.'",docpath="'.$strPath.'",docfile="'.$strFile.'",docname="'.$strName.'" WHERE docfile="'.$v.'"');
  }
  else
  {
    $oDB->Query( 'UPDATE '.TABDOC.' SET docname="'.$strName.'" WHERE docfile="'.$v.'"');
  }

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_update'];
  $oHtml->Redirect($oVIP->exiturl);
  break;

// --------------
case 'docdelete':
// --------------

  if ( $p<0 ) die('Wrong user in '.$oVIP->selfurl); // $p is the document owner
  if ( $p!=$oVIP->user->id && $oVIP->user->role!='A' && $oVIP->user->role!='M') die('Wrong user in '.$oVIP->selfurl);

  $oVIP->selfname = $L['Delete'];
  $oVIP->exiturl = 'qnm_form_docs.php?nid='.$t;
  $oVIP->exitname = '&laquo; '.$L['Item'];

  $oDB->Query('SELECT * FROM '.TABDOC.' WHERE docfile="'.$v.'"');
  $row=$oDB->Getrow();

  // ask confirmation
  if ( empty($ok) )
  {
    $oHtml->PageBox
    (
    NULL,
    '<table class="hidden">
    <tr class="hidden">
    <td class="hidden"></td>
    <td class="hidden">
    <form method="get" action="'.Href().'">
    <p><span class="bold">'.$row['docname'].'</span> ('.QTdatestr($row['docdate']).')<p>
    <input type="hidden" name="a" value="'.$a.'"/>
    <input type="hidden" name="s" value="'.$s.'"/>
    <input type="hidden" name="t" value="'.$t.'"/>
    <input type="hidden" name="p" value="'.$p.'"/>
    <input type="hidden" name="v" value="'.$v.'"/>
    <input type="submit" name="ok" value="'.$L['Delete'].'"/>
    </form></td>
    </tr></table>',
    $_SESSION[QT]['skin_dir'],
    0,
    '500px'
    );
    exit;
  }

  // DELETE

  $oDB->Query( 'DELETE FROM '.TABDOC.' WHERE docfile="'.$v.'"');
  $oDB->Query( 'UPDATE '.TABNE.' SET docs=docs-1 WHERE docs>0 AND uid='.GetUid($t));
  if ( file_exists($row['docpath'].$v) )
  {
    unlink($row['docpath'].$v);
    if ( file_exists($row['docpath'].'thumb_'.$v) ) unlink($row['docpath'].'thumb_'.$v);
  }

  // EXIT
  $_SESSION['pagedialog'] = 'O|'.$L['S_delete'];
  $oHtml->Redirect($oVIP->exiturl);
    break;

// --------------
case 'pwdreset':
// --------------

  if ( $oVIP->user->role!='A' ) die('Access is restricted to administrators only');

  if ( $s<0 ) die('Wrong id '.$s);
  if ( $s==1 && $oVIP->user->id!=1 ) die('First Admin password can be changed by himself only...');
  include 'bin/class/qt_class_smtp.php';
  include Translate('qnm_reg.php');

  $oVIP->selfname = $L['Reset_pwd'];
  $oVIP->exiturl = 'qnm_user.php?id='.$s;
  $oVIP->exitname = '&laquo; '.$L['Profile'];

  $oDB->Query('SELECT name,mail,children,parentmail,photo FROM '.TABUSER.' WHERE id='.$s);
  $row = $oDB->Getrow();

  // ask delay
  if ( empty($ok) )
  {
    $oHtml->PageBox
    (
    NULL,
    '<form method="get" action="'.Href().'">
    <table class="hidden">
    <tr class="hidden">
    <td class="hidden">'.AsImgBox(AsImg( AsAvatarSrc($row['photo']),'',$row['name'],'member'),'picbox','',$row['name']).'</td>
    <td class="hidden">
    <p style="text-align:right">'.$L['Reset_pwd_help'].'<br/><br/>'.$oVIP->selfname.'&nbsp;
    <input type="hidden" name="a" value="'.$a.'"/>
    <input type="hidden" name="s" value="'.$s.'"/>
    <input type="submit" name="ok" value="'.$L['Send'].'"/></p>
    </td>
    </tr>
    </table></form>',
    'admin',
    0,
    '500px'
    );
    exit;

  }

  // reset user
  $strNewpwd = 'qt'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
  $oDB->Query('UPDATE '.TABUSER.' SET pwd="'.sha1($strNewpwd).'" WHERE id='.$s);

  // send email
  $strSubject = $_SESSION[QT]['site_name'].' - New password';
  $strMessage = "Here are your login and password\nLogin: %s\nPassword: %s";
  $strFile = GetLang().'mail_pwd.php';
  if ( file_exists($strFile) ) include $strFile;
  $strMessage = sprintf($strMessage,$row['name'],$strNewpwd);
  QTmail($row['mail'],QTconv($strSubject,'-4'),QTconv($strMessage,'-4'),QNM_HTML_CHAR);
  $strEndmessage = str_replace("\n",'<br/>',$strMessage);

  // exit
  $oHtml->PageBox(NULL,$L['S_update'].'<br/><br/>'.$strEndmessage,$_SESSION[QT]['skin_dir'],0);
  exit;
  break;

// --------------
case 'itemstatus':
// --------------

  if ( !$oVIP->user->IsStaff() ) die(Error(12));
  if ( !$oVIP->user->CanView('V6') ) die(Error(11));

  $oVIP->selfname = $L['Change'].' '.$L['Status'];
  $oVIP->exiturl = "qnm_item.php?t=$t";
  $oVIP->exitname = '&laquo; '.$L['Message'];

  // ASK STATUS IF MISSING: When value "*" repost with method GET

  if ( $v=='*' )
  {
    $oVIP->selfname = $L['Change'].' '.$L['Status'];
    $oHtml->PageBox
    (
      NULL,
      '<form method="get" action="'.$oVIP->selfurl.'">
      <input type="hidden" name="a" value="'.$a.'"/>
      <input type="hidden" name="s" value="'.$s.'"/>
      <input type="hidden" name="t" value="'.$t.'"/>
      <select name="v" size="8">'.QTasTag($oVIP->statuses,'',array('format'=>$L['Status'].': %s')).'</select><br/><br/>
      <input type="submit" name="ok" value="'.$L['Ok'].'"/>
      </form>',
      $_SESSION[QT]['skin_dir']
    );
    exit;
  }

  // CHANGE STATUS

  $oNE = new cNE($t);
  $oNE->SetStatus($v,true,$oNE->firstpostid); // this also updates the section stats in case of closed items
  if ( $v=='Z' )
  {
  $oVIP->exitname = '&laquo; '.$L['Section'];
  $oVIP->exiturl = "qnm_items.php?s=$s";
  $voidSEC = new cSection(); $voidSEC->uid=$s; $voidSEC->UpdateStats(); // updates section & system stats
  }

  // EXIT

  $oHtml->PageBox(NULL,$L['S_update'].$strMails,$_SESSION[QT]['skin_dir'],2);
  exit;
  break;

// --------------
case 'itemtype':
// --------------

  if ( !$oVIP->user->IsStaff() ) die(Error(12));

  $oVIP->selfname = $L['Change'].' '.$L['Type'];
  $oVIP->exiturl  = 'qnm_item.php?t='.$t;
  $oVIP->exitname = '&laquo; '.$L['Message'];

  // ASK TYPE IF MISSING: When value "*" repost with method GET
  if ( $v=='*' )
  {
    $oVIP->selfname = $L['Change'].' '.$L['Type'];
    $oHtml->PageBox
    (
      NULL,
      '<form method="get" action="'.$oVIP->selfurl.'">
      <input type="hidden" name="a" value="'.$a.'"/>
      <input type="hidden" name="s" value="'.$s.'"/>
      <input type="hidden" name="t" value="'.$t.'"/>
      <select name="v" size="6">'.
      QTasTag($oVIP->types).'
      </select><br/><br/><input type="submit" name="ok" value="'.$L['Ok'].'"/>
      </form>',
      $_SESSION[QT]['skin_dir']
    );
    exit;
  }

  // CHANGE TYPE

  cElement::SetType($t,$v);
  if ( $v=='I' ) $oHtml->Redirect('qnm_change.php?a=elementparam&amp;s='.$s.'&amp;t='.$t);


  // EXIT

  $oHtml->PageBox(NULL,$L['S_update'],$_SESSION[QT]['skin_dir'],2);
  exit;
  break;

// --------------
case 'userrole':
// --------------

  if ( !$oVIP->user->IsStaff() ) die($L['R_staff']);
  if ( $s<2 ) die('Wrong parameters: user 0 and 1 cannot be changed');
  include Translate('qnm_reg.php');

  $oVIP->selfname = $L['User_upd'];
  $oVIP->exiturl  = 'qnm_user.php?id='.$s;
  $oVIP->exitname = '&laquo; '.$L['Memberlist'];

  // ask confirmation
  if ( empty($ok) )
  {
    $oDB->Query('SELECT name,photo,role FROM '.TABUSER.' WHERE id='.$s);
    $row = $oDB->Getrow();
    $oHtml->PageBox
    (
      NULL,
      '<table class="hidden">
      <tr class="hidden">
      <td class="hidden">'.AsImgBox(AsImg(AsAvatarSrc($row['photo']),'',$row['name'],'member'),'picbox','',$row['name']).'</td>
      <td class="hidden">
      <form method="get" action="'.$oVIP->selfurl.'">
      <h2>'.$row['name'].' ('.$L['Userrole_'.strtolower($row['role'])].')</h2><br/>
      '.$L['Change_role'].' <select name="r" size="1">
      <option value="A"'.($row['role']=='A' ? QSEL : '').($oVIP->user->role!='A' ? ' disabled="disabled"' : '').'>'.$L['Userrole_a'].'</option>
      <option value="M"'.($row['role']=='M' ? QSEL : '').'>'.$L['Userrole_m'].'</option>
      <option value="U"'.($row['role']=='U' ? QSEL : '').'>'.$L['Userrole_u'].'</option>
      </select>&nbsp;<input type="hidden" name="a" value="'.$a.'"/>
      <input type="hidden" name="s" value="'.$s.'"/>
      <input type="submit" name="ok" value="'.$L['Ok'].'"/>
      </form></td>
      </tr>
      </table>',
      'admin',
      0,
      '500px'
    );
    exit;
  }

  //update role
  if ( $oVIP->user->role!='A' && $v=='A' ) die('Access is restricted to administrators only');
  $oDB->Query('UPDATE '.TABUSER.' SET role="'.$_GET['r'].'" WHERE id='.$s);
  if ( $_GET['r']=='U' ) $oDB->Query('UPDATE '.TABSECTION.' SET moderator=1, moderatorname="Admin" WHERE moderator='.$s);

  // exit
  $oHtml->PageBox(NULL,$L['S_update'],'admin',2);
  exit;
  break;

// --------------
case 'user_del':
// --------------

  if ( !$oVIP->user->IsStaff() ) die($L['R_staff']);
  if ( $s<2 ) die("Wrong parameters: user 0 and 1 cannot be deleted");
  include Translate('qnm_reg.php');

  $oVIP->selfname = $L['Delete'].' '.L('user');
  $oVIP->exiturl  = 'qnm_users.php'; if ( $v=='adm' ) $oVIP->exiturl = 'qnm_adm_users.php';
  $oVIP->exitname = '&laquo; '.$L['Memberlist'];

  $oDB->Query('SELECT name,photo FROM '.TABUSER.' WHERE id='.$s);
  $row = $oDB->Getrow();

  // ask confirmation
  if ( empty($ok) )
  {
    $str  = '<table class="hidden">';
    $str .= '<tr>'.PHP_EOL;
    $str .= '<td class="hidden">'.AsImgBox(AsImg(AsAvatarSrc($row['photo']),'',$row['name'],'member'),'picbox','',$row['name']).'</td>';
    $str .= '<td class="hidden">';
    $str .= '<form method="get" action="'.$oVIP->selfurl.'">';
    $str .= '<p style="text-align:right">'.$row['name'].' <input type="hidden" name="a" value="'.$a.'"/><input type="hidden" name="v" value="'.$v.'"/><input type="hidden" name="s" value="'.$s.'"/><input type="submit" name="ok" value="'.$L['Delete'].'"/></p>';
    $str .= '</form></td>'.PHP_EOL;
    $str .= '</tr></table></form>'.PHP_EOL;
    $oHtml->PageBox(NULL,$str,'admin',0,'500px');
    exit;
  }

  // delete avatar first
  if ( file_exists(QNM_DIR_PIC.$row['photo']) ) unlink(QNM_DIR_PIC.$row['photo']);

  // update post.userid, post.username, element.firstpostuser, element.lastpostuser, element.firstpostname, element.lastpostname
  $oDB->Query('UPDATE '.TABPOST.' SET userid=0, username="Visitor" WHERE userid='.$s);
  $oDB->Query('UPDATE '.TABSECTION.' SET moderator=1,moderatorname="Admin" WHERE moderator='.$s);

  // Delete user

  $oDB->Query('DELETE FROM '.TABUSER.' WHERE id='.$s);


  // Unregister global sys (will be recomputed on next page)

  Unset($_SESSION[QT]['sys_members']);
  Unset($_SESSION[QT]['sys_states']);

  // Exit

  $oHtml->PageBox(NULL,$L['S_delete'],'admin',2);
  exit;
  break;

// --------------
case 'user_ban':
// --------------

  if ( !$oVIP->user->IsStaff() ) die($L['R_staff']);
  if ( $s<2 ) die('Wrong parameters: user 0 and 1 cannot be banned');
  include Translate('qnm_reg.php');

  $oVIP->selfname = $L['Ban_user'];
  $oVIP->exiturl  = 'qnm_user.php?id='.$s;
  $oVIP->exitname = '&laquo; '.$L['Profile'];
  if ( $v=='adm' )
  {
    $oVIP->exiturl = 'qnm_adm_users.php';
    $oVIP->exitname = '&laquo; '.$L['Users'];
  }

  // ask delay
  if ( empty($ok) || $t<0 )
  {
    $oDB->Query('SELECT closed,name,photo FROM '.TABUSER.' WHERE id='.$s);
    $row = $oDB->Getrow();
    $oHtml->PageBox
    (
    NULL,
    '<table class="hidden"><tr>
    <td class="hidden">'.AsImgBox(AsImg(AsAvatarSrc($row['photo']),'',$row['name'],'member'),'picbox','',$row['name']).'</td>
    <td class="hidden">
    <form method="get" action="'.$oVIP->selfurl.'">
    <p style="text-align:right">'.$L['H_ban'].' <select name="t" size="1"/>
    <option value="0"'.($row['closed']=='0' ? QSEL : '').'>'.$L['N'].'</option>
    <option value="1"'.($row['closed']=='1' ? QSEL : '').'>1 '.$L['Day'].'</option>
    <option value="2"'.($row['closed']=='2' ? QSEL : '').'>10 '.$L['Days'].'</option>
    <option value="3"'.($row['closed']=='3' ? QSEL : '').'>20 '.$L['Days'].'</option>
    <option value="4"'.($row['closed']=='4' ? QSEL : '').'>30 '.$L['Days'].'</option>
    </select>&nbsp;
    <input type="hidden" name="a" value="'.$a.'"/>
    <input type="hidden" name="s" value="'.$s.'"/>
    <input type="hidden" name="v" value="'.$v.'"/>
    <input type="submit" name="ok" value="'.$L['Ok'].'"/></p>
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

  // ban user
  if ( $t==-1 ) die('Wrong parameters: delay');
  $oDB->Query('UPDATE '.TABUSER.' SET closed="'.$t.'" WHERE id='.$s);

  // exit
  $oHtml->PageBox(NULL,$L['S_update'],'admin',2);
  exit;
  break;

// --------------
case 'unlink':
// --------------

  if ( !$oVIP->user->IsStaff() ) die($L['R_staff']);
  if ( empty($v) ) die('Wrong parameters: missing element id');

  $oVIP->selfname = 'Remove relations';
  $oVIP->exiturl = 'qnm_items.php?s='.$s;
  $oVIP->exitname = '&laquo; '.$L['Section'];

  // delete element
  if ( GetUid($v)<1 ) die('Wrong parameters: missing element id');
  $oDB->Query('DELETE FROM '.TABPOST.' WHERE pid='.GetUid($v));
  $oDB->Query('DELETE FROM '.TABNC.' WHERE pid='.GetUid($v));
  $oDB->Query('UPDATE '.TABNE.' SET pid=0 WHERE pid='.GetUid($v));
  $oDB->Query('DELETE FROM '.TABNL.' WHERE lidclass="'.GetNclass($v).'" AND lid='.GetUid($v));
  $oDB->Query('DELETE FROM '.TABNL.' WHERE lid2class="'.GetNclass($v).'" AND lid2='.GetUid($v));

  // exit
  $oHtml->PageBox(NULL,$L['S_update'],$_SESSION[QT]['skin_dir'],0);
  exit;
  break;

  // --------------
  case 'itemdelete':
  // --------------

    if ( !$oVIP->user->IsStaff() ) die($L['R_staff']);
    if ( $t<0 ) die('Wrong parameters: missing element id');

    $oVIP->selfname = $L['Delete'].' '.$L['Item'];
    $oVIP->exiturl = 'qnm_items.php?s='.$s;
    $oVIP->exitname = '&laquo; '.$L['Section'];

    // ask confirmation
    if ( empty($ok) )
    {
      $oNE = new cNE($t);
      if ( $oNE->notes==0 ) {
        $str=$L['None'];
      } else { $str=$oNE->notes.' <span class="small">('.$L['Last_message'].' '.QTdatestr($oNE->lastpostdate).')</span>';
      }

      $oHtml->PageBox
      (
          NULL,
          '<form method="get" action="'.$oVIP->selfurl.'">
          <table class="data_o">
          <tr>
          <td  style="width:150px;">'.$L['Title'].'</td>
          <td>'.$oNE->GetElementTitle().'</td>
          </tr>
          <tr>
          <td >'.$L['Author'].'</td>
          <td>'.$oNE->firstpostname.' <span class="small">('.QTdatestr($oNE->firstpostdate).')</span></td>
          </tr>
          <tr>
          <td >'.$L['Notes'].'</td>
          <td>'.$str.'</td>
          </tr>
          <tr>
          <td >&nbsp;</td>
          <td><input type="hidden" name="a" value="'.$a.'"/><input type="hidden" name="s" value="'.$s.'"/><input type="hidden" name="t" value="'.$t.'"/><input type="submit" name="ok" value="'.$L['Delete'].'"/></td>
          </tr>
          </table>
          </form>',
          $_SESSION[QT]['skin_dir'],
          0,
          '600px'
      );
      exit;
    }

    // delete element
    if ( $t<0 ) die('Wrong parameters: missing element id');
    $oDB->Query('DELETE FROM '.TABPOST.' WHERE element='.$t);
    $oDB->Query('DELETE FROM '.TABNE.' WHERE id='.$t);

    $voidSEC = new cSection(); $voidSEC->uid=$s; $voidSEC->UpdateStats();  // updates section & system stats

    // exit
    $oHtml->PageBox(NULL,$L['S_delete'],$_SESSION[QT]['skin_dir'],2);
    exit;
    break;

// --------------
case 'itemmove':
// --------------

  if ( !$oVIP->user->IsStaff() ) die($L['R_staff']);
  if ( $t<0 ) die('Wrong parameters: missing element id');

  $oVIP->selfname = $L['Move'].' '.$L['Item'];
  $oVIP->exiturl = 'qnm_items.php?s='.$s;
  $oVIP->exitname = '&laquo; '.$L['Section'];

  // ask confirmation
  if ( empty($ok) || $p<0 )
  {
    $oNE = new cNE($t);
    $arrSections = QTarrget(GetSections($oVIP->user->role,-1,$s));
    if ( $oNE->notes==0 ) { $str=$L['None']; } else { $str=$oNE->notes.' <span class="small">('.$L['Last_message'].' '.QTdatestr($oNE->lastpostdate).')</span>'; }

    $oHtml->PageBox
    (
    NULL,
    '<form method="get" action="'.$oVIP->selfurl.'">
    <table class="data_o">
    <tr>
    <td  style="width:150px;">'.$L['Title'].'</td>
    <td>'.$oNE->GetElementTitle().'</td>
    </tr>
    <tr>
    <td >'.$L['Author'].'</td>
    <td>'.$oNE->firstpostname.' <span class="small">('.QTdatestr($oNE->firstpostdate).')</span></td>
    </tr>
    <tr>
    <td >'.$L['Notes'].'</td>
    <td>'.$str.'</td>
    </tr>
    <tr>
    <td >'.$L['Move_to'].'</td>
    <td><select name="p" size="1">'.QTasTag($arrSections).'</select></td>
    </tr>
    <tr>
    <td >&nbsp;</td>
    <td><input type="hidden" name="a" value="'.$a.'"/>
    <input type="hidden" name="s" value="'.$s.'"/>
    <input type="hidden" name="t" value="'.$t.'"/>
    <input type="submit" name="ok" value="'.$L['Ok'].'"/></td>
    </tr>
    </table>
    </form>',
    $_SESSION[QT]['skin_dir'],
    0,
    '600px'
    );
    exit;
  }

  // move element
  if ( $s<0 ) die('Wrong parameters section id');
  if ( $t<0 ) die('Wrong parameters id');
  if ( $p<0 ) die('Wrong parameters dest');
  cSection::MoveItems($p,$t);

  // exit
  $oHtml->PageBox(NULL,$L['S_update'],$_SESSION[QT]['skin_dir'],2);
  exit;
  break;

// --------------
case 'direction':
// --------------

  if ( !$oVIP->user->IsStaff() ) die($L['R_staff']);
  if ( $t<1 ) die('Wrong parameters: uid');

  $oVIP->selfname = 'Change direction';
  $oVIP->exiturl  = 'qnm_form_link_c.php?uid='.$t;
  $oVIP->exitname = '&laquo; Edit relations';

  // ask direction
  if ( empty($ok) )
  {
    $oHtml->PageBox
    (
      NULL,
      '<form method="get" action="'.$oVIP->selfurl.'">
      '.L('Direction').' <select name="p" size="1">
      <option value="0">&mdash; '.L('Direction0').'</option>
      <option value="1">&rarr; '.L('Direction1').'</option>
      <option value="2">&harr; '.L('Direction2').'</option>
      <option value="-1">&larr; '.L('Direction-1').'</option>
      </select>&nbsp;<input type="hidden" name="t" value="'.$t.'"/>
      <input type="submit" name="ok" value="'.$L['Ok'].'"/>
      </form>',
      'admin',
      0,
      '350px'
    );
    exit;
  }

  // Update direction
  $oDB->Query('UPDATE '.TABUSER.' SET role="'.$_GET['r'].'" WHERE id='.$s);
  if ( $_GET['r']=='U' ) $oDB->Query('UPDATE '.TABSECTION.' SET moderator=1, moderatorname="Admin" WHERE moderator='.$s);

  // exit
  $oHtml->PageBox(NULL,$L['S_update'],'admin',2);
  exit;
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