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

// ---------
// INITIALISE
// ---------

$strTitle = '';
$strDelimit = ';';
$strEnclose = '"';
$strSkip = 'N';

$oVIP->selfurl = 'qnm_adm_users_imp.php';
$oVIP->selfname = '<span class="upper">'.$L['Adm_content'].'</span><br/>'.$L['Users'].'<br/>'.$L['Users_import_csv'];
$oVIP->exiturl = 'qnm_adm_users.php';
$oVIP->exitname = '&laquo;&nbsp;'.$L['Users'];

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // Check uploaded document

  $error = InvalidUpload($_FILES['title'],'csv,txt,text','',500);

  // check form value

  if ( empty($error) )
  {
    $strDelimit = trim($_POST['delimit']);
    if ( isset($_POST['skip']) ) $strSkip='Y';
    if ( empty($strDelimit) ) $error=$L['Separator'].' '.Error(1);
    if ( strlen($strDelimit)!=1 ) $error=$L['Separator'].' '.Error(1);
    if ( preg_match('/[0-9A-Za-z]/',$strDelimit) ) $error = $L['Separator'].' '.Error(1);
  }

  // read file

  if ( empty($error) )
  {
    if ( $handle = fopen($_FILES['title']['tmp_name'],'r') )
    {
      $i = 0;
      $intCountUser = 0;
      $intNextUser = $oDB->Nextid(TABUSER);
      while( ($row=fgetcsv($handle,500,$strDelimit))!==FALSE )
      {
        $i++;
        if ( $strSkip=='Y' && $i==1 ) continue;
        if ( count($row)==1 ) continue;
        if ( count($row)==4 )
        {
          $strRole = 'U'; if ( $row[0]=='A' || $row[0]=='M' || $row[0]=='a' || $row[0]=='m') $strRole=strtoupper($row[0]);
          $strLog = trim($row[1]); if ( !empty($strLog) ) $strLog=utf8_decode($strLog);
          $strPwd = trim($row[2]);
          if ( substr($strPwd,0,3)=='SHA' || substr($strPwd,0,3)=='sha' ) $strPwd = sha1($strPwd);
          if ( empty($strPwd) ) $strPwd=sha1($strLog);
          $strMail = $row[3];
          // insert
          if ( !empty($strLog) )
          {
            if ( $oDB->Query(
             'INSERT INTO '.TABUSER.' (id,name,pwd,closed,role,mail,privacy,numpost,children,photo,stats) VALUES ('.$intNextUser.',"'.$strLog.'","'.$strPwd.'","0","'.$strRole.'","'.$strMail.'","1",0,"0","0","firstdate='.Date('Ymd His').'")' ) )
            {
              $intNextUser++;
              $intCountUser++;
            }
            else
            {
              echo ' - Cannot insert a new user with username ',$strLog,'<br/>';
            }
          }
        }
        else
        {
          $error='Number of parameters ('.count($row).') not matching in line '.$i;
        }
      }
    }
    fclose($handle);
    // Unregister global sys (will be recomputed on next page)
    Unset($_SESSION[QT]['sys_stat_members']);
    Unset($_SESSION[QT]['sys_stat_states']);
  }

  // End message

  if ( empty($error) )
  {
    unlink($_FILES['title']['tmp_name']);
   $oVIP->selfname = $L['Users_import_csv'];
    if ( $intCountUser==0 )
    {
    $oHtml->PageBox(NULL, 'No user inserted... Check the file and check that you don\'t have duplicate usernames.', 'admin',0);
    }
    else
    {
    $oHtml->PageBox(NULL, $intCountUser.' '.$L['Users'].'<br/>'.$L['S_update'], 'admin',0);
    }
  }
}

// --------
// HTML START
// --------

include 'qnm_adm_inc_hd.php';

echo '
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert("',$L['Missing'],': File"); return false; }
  if (theForm.delimit.value.length==0) { alert("',$L['Missing'],': ',$L['Separator'],'"); return false; }
  return null;
}
//-->
</script>
';

echo '<form method="post" action="',$oVIP->selfurl,'" enctype="multipart/form-data" onsubmit="return ValidateForm(this);">
<input type="hidden" name="maxsize" value="5242880"/>
<table class="data_o">
';
echo '<tr class="data_o"><td class="headgroup" colspan="2">File</td></tr>
<tr class="data_o">
<td class="headfirst" style="width:200px"><label for="title">CSV file</label></td>
<td><input type="file" id="title" name="title" size="32" value="',$strTitle,'"/></td>
</tr>
';
echo '<tr class="data_o"><td class="headgroup" colspan="2">',$L['Adm_settings'],'</td></tr>
<tr class="data_o">
<td class="headfirst"><label for="delimit">',$L['Separator'],'</label></td>
<td><input type="text" id="delimit" name="delimit" size="1" maxlength="5" value="',$strDelimit,'"/></td>
</tr>
';
echo '<tr class="data_o">
<td class="headfirst">',$L['First_line'],'</td>
<td><input type="checkbox" id="skip" name="skip"',($strSkip=='Y' ? QCHE : ''),'/> <label for="skip">',$L['Skip_first_line'],'</label></td>
</tr>
';
echo '<tr class="data_o"><td class="headgroup" colspan="2" style="padding:6px; text-align:center"><input type="submit" name="ok" value="',$L['Ok'],'"/></td></tr>
</table>
</form>
';

// HTML END

include 'qnm_adm_inc_ft.php';