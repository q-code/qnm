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
include Translate('qnmm_export.php');
if ( $oVIP->user->role!='A' ) die(Error(13));
require_once 'bin/qnm_fn_sql.php';
if ( !defined('QNM_XML_CHAR') ) define('QNM_XML_CHAR','UTF-8');

function ToXml($str)
{
  $str = html_entity_decode($str,ENT_QUOTES);
  if ( strstr($str,'&') ) $str = QTencode($str,'-A -Q -L -R -&');
  $str = str_replace(chr(160),' ',$str); // required for xml
  $str = QTencode($str,'& L R'); // required for xml
  return $str;
}

// INITIALISE

$intElements = 0;
$arrYears = array(
  strval(date('Y'))=>strval(date('Y')),
  strval(date('Y')-1)=>strval(date('Y')-1),
  'old'=>'&lt; '.strval(date('Y')-1)
  );

if ( !isset($_SESSION['m_export_xml']) )
{
  $_SESSION['m_export_xml'] = array(
  'title'   => 'export_'.date('Ymd').'.xml',
  'dropbbc' => 'Y');
}

$oVIP->selfurl = 'qnmm_export_adm.php';
$oVIP->selfname = $L['export']['Admin'];
$oVIP->exiturl = $oVIP->selfurl;
$oVIP->exitname = $oVIP->selfname;
$strPageversion = $L['export']['Version'].' 1.0';

// --------
// SUBMITTED
// --------

if ( isset($_POST['submit']) )
{
  // read and check mandatory
  if ( isset($_POST['dropbbc']) ) { $_SESSION['m_export_xml']['dropbbc']='Y'; } else { $_SESSION['m_export_xml']['dropbbc']='N'; }
  if ( empty($_POST['title']) ) $error='Filename '.Error(1);
  if ( substr($_POST['title'],-4,4)!='.xml' ) $_POST['title'] .= '.xml';
  if ( $_POST['section']=='-' ) $error='No data found';
  if ( $_POST['year']=='-' ) $error='No data found';

  // EXPORT COUNT
  if ( empty($error) )
  {
    $strWhere = '';
    if ( $_POST['section']!='*' ) { $strWhere .= 'section='.$_POST['section']; } else { $strWhere .= 'section>=0'; }
    if ( $_POST['year']!='*' ) $strWhere .= ' AND '.SqlDateCondition($_POST['year'],'firstpostdate');
    $oDB->Query( 'SELECT count(*) as countid FROM '.TABNE.' WHERE '.$strWhere );
    $row=$oDB->Getrow();
    if ( $row['countid']==0 ) $error='No data found';
  }

  // ------
  // EXPORT XML
  // ------

  if ( empty($error) )
  {
    $oDB2 = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);

    // start export

    if (!headers_sent())
    {
      header('Content-Type: text/xml; charset='.QNM_XML_CHAR);
      header('Content-Disposition: attachment; filename="'.$_POST['title'].'"');
    }

    echo '<?xml version="1.0" encoding="'.QNM_XML_CHAR.'"?>',PHP_EOL;
    echo '<qnm version="1.0">',PHP_EOL;

    // export element
    $oDB->Query( 'SELECT * FROM '.TABNE.' WHERE '.$strWhere );
    while($row=$oDB->Getrow())
    {
      $oNE = new cNE($row);

      echo '<element id="',$oNE->id,'" type="',$oNE->type,'" section="',$oNE->section,'">',PHP_EOL;
      echo '<numid>',$oNE->numid,'</numid>',PHP_EOL;
      echo '<status>',$oNE->status,'</status>',PHP_EOL;
      if ( !empty($oNE->statusdate) )    echo '<statusdate>',$oNE->statusdate,'</statusdate>',PHP_EOL;
      if ( !empty($oNE->wisheddate) )    echo '<wisheddate>',$oNE->wisheddate,'</wisheddate>',PHP_EOL;
      if ( !empty($oNE->firstpostid) )   echo '<firstpostid>',$oNE->firstpostid,'</firstpostid>',PHP_EOL;
      if ( !empty($oNE->lastpostid) )    echo '<lastpostid>',$oNE->lastpostid,'</lastpostid>',PHP_EOL;
      if ( !empty($oNE->firstpostuser) ) echo '<firstpostuser>',$oNE->firstpostuser,'</firstpostuser>',PHP_EOL;
      if ( !empty($oNE->lastpostuser) )  echo '<lastpostuser>',$oNE->lastpostuser,'</lastpostuser>',PHP_EOL;
      if ( !empty($oNE->firstpostname) ) echo '<firstpostname>',$oNE->firstpostname,'</firstpostname>',PHP_EOL;
      if ( !empty($oNE->lastpostname) )  echo '<lastpostname>',$oNE->lastpostname,'</lastpostname>',PHP_EOL;
      if ( !empty($oNE->firstpostdate) ) echo '<firstpostdate>',$oNE->firstpostdate,'</firstpostdate>',PHP_EOL;
      if ( !empty($oNE->lastpostdate) )  echo '<lastpostdate>',$oNE->lastpostdate,'</lastpostdate>',PHP_EOL;
      if ( !empty($oNE->x) )             echo '<x>',$oNE->x,'</x>',PHP_EOL;
      if ( !empty($oNE->y) )             echo '<y>',$oNE->y,'</y>',PHP_EOL;
      if ( !empty($oNE->z) )             echo '<z>',$oNE->z,'</z>',PHP_EOL;
      if ( !empty($oNE->tags) )          echo '<tags>',$oNE->tags,'</tags>',PHP_EOL;
      if ( !empty($oNE->param) )         echo '<param>',$oNE->param,'</param>',PHP_EOL;

      echo '<posts>',PHP_EOL;

        $oDB2->Query( 'SELECT * FROM '.TABPOST.' WHERE element='.$oNE->id );
        while($row2=$oDB2->Getrow())
        {
          $oPost = new cPost($row2);
          echo '<post id="',$oPost->id,'" type="',$oPost->type,'">',PHP_EOL;
          echo '<icon>',$oPost->icon,'</icon>',PHP_EOL;
          echo '<title>',ToXml($oPost->title),'</title>',PHP_EOL;
          echo '<userid>',$oPost->userid,'</userid>',PHP_EOL;
          echo '<username>',$oPost->username,'</username>',PHP_EOL;
          echo '<issuedate>',$oPost->issuedate,'</issuedate>',PHP_EOL;
          if ( !empty($oPost->modifdate) ) echo '<modifdate>',$oPost->modifdate,'</modifdate>',PHP_EOL;
          if ( !empty($oPost->modifuser) ) echo '<modifuser>',$oPost->modifuser,'</modifuser>',PHP_EOL;
          if ( !empty($oPost->modifname) ) echo '<modifname>',$oPost->modifname,'</modifname>',PHP_EOL;
          echo '<textmsg>',ToXml($oPost->text),'</textmsg>',PHP_EOL;
          echo '</post>',PHP_EOL;  // doc is not exported
        }

      echo '</posts>',PHP_EOL;
      echo '</element>',PHP_EOL;
    }

    // end export

    echo '</qnm>';
    exit;
  }

}

// --------
// HTML START
// --------

$oHtml->links[] = '
<script type="text/javascript">
<!--
function ValidateForm(theForm)
{
  if (theForm.title.value.length==0) { alert("'.$L['Missing'].': File"); return false; }
  return null;
}
//-->
</script>
';

include 'qnm_adm_inc_hd.php';

echo '<form method="post" action="',$oVIP->selfurl,'" onsubmit="return ValidateForm(this);">
<table class="data_o">
<tr class="data_o">
<td class="colgroup" colspan="3">',$L['export']['Content'],'</td>
</tr>
';
echo '<tr class="data_o">
<td ><label for="section">',$L['Section'],'</label></td>
<td colspan="2">
<select id="section" name="section" size="1">
<option value="*">[ ',$L['All'],' ]</option>
<option value="-" disabled="disabled">----------</option>
',QTasTag(QTarrget(GetSections('A'))),'</select>
</td>
</tr>
';
echo '<tr class="data_o">';
echo '<td ><label for="year">',$L['Items'],'</label></td>';
echo '<td><select id="year" name="year" size="1">
<option value="*">[ ',$L['All'],' ]</option>
<option value="-" disabled="disabled">----------</option>
';
foreach($arrYears as $intKey=>$strValue)
{
echo '<option value="',$intKey,'">',$strValue,'</option>';
}
echo '</select></td>
<td><span class="help">&nbsp;</span></td>
</tr>
';
echo '<tr class="data_o">
<td ><label for="dropbbc">',$L['export']['Drop_bbc'],'</label></td>
<td colspan="2"><input type="checkbox" id="dropbbc" name="dropbbc"',($_SESSION['m_export_xml']['dropbbc']=='Y' ? QCHE : ''),'/> <label for="dropbbc">',$L['export']['H_Drop_bbc'],'</label></td>
</tr>
';
echo '<tr class="data_o">
<td class="colgroup" colspan="3">',$L['Destination'],'</td>
</tr>
';
echo '<tr class="data_o">
<td ><label for="title">',$L['export']['Filename'],'</label></td>
<td colspan="2"><input type="text" id="title" name="title" size="32" maxlength="32" value="',$_SESSION['m_export_xml']['title'],'"/></td>
</tr>';
echo '<tr class="data_o">
<td class="colgroup" colspan="3" style="padding:6px; text-align:center"><input type="submit" name="submit" value="',$L['Ok'],'"/></td>
</tr>
';
echo '</table>
</form>
';

// HTML END

include 'qnm_adm_inc_ft.php';