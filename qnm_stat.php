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
if ( !isset($_GET['y']) ) die('Missing parameter y');
include Translate('qnm_stat.php');

include 'bin/qnm_fn_sql.php';

// INITIALISE

$y = -1;
$m = -1;
$s = -1;
QThttpvar('y m s','int int int',true,true,false); // reject POST method

if ( $s>=0 )
{
$strSection = 'section='.$s.' AND ';
$arrSectionTitle = QTarrget(GetSections('A'));
$strSectionTitle = '<br/>'.$L['Section'].' '.$arrSectionTitle[$s];
}
else
{
$strSection='';
$strSectionTitle = '';
}

$oVIP->selfurl = 'qnm_stat.php';
$oVIP->selfname = $L['Statistics'];
$oVIP->exiturl = 'qnm_stats.php';
$oVIP->exitname = '&laquo; '.$L['Statistics'];

// --------
// HTML START
// --------

$oHtml->scripts = array();
$oHtml->links[] = '<script type="text/javascript">
<!--
$(function() {
  $(".ajaxmouseover").mouseover(function() {
    $.post("qnm_j_user.php",
      {id:this.id,lang:"'.GetLang().'",dir:"'.QNM_DIR_PIC.'"},
      function(data) { if ( data.length>0 ) document.getElementById("title_err").innerHTML=data; });
  });
});
//-->
</script>
';

include 'qnm_inc_hd.php';

// USERS

if ( $m==0 )
{
  if ( substr($oDB->type,0,5)=='mysql' )
  {
  $oDB->Query( 'SELECT DISTINCT userid, username, count(id) as countid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition($y,'issuedate').' GROUP BY userid,username' );
  }
  else
  {
  $oDB->Query( 'SELECT DISTINCT userid, username FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition($y,'issuedate') );
  }
}
else
{
  if ( substr($oDB->type,0,5)=='mysql' )
  {
  $oDB->Query( 'SELECT DISTINCT userid, username, count(id) as countid FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition(($y*100+$m),'issuedate',6).' GROUP BY userid,username' );
  }
  else
  {
  $oDB->Query( 'SELECT DISTINCT userid, username FROM '.TABPOST.' WHERE '.$strSection.SqlDateCondition(($y*100+$m),'issuedate',6) );
  }
}
$arrUsers = array();
while($row=$oDB->Getrow())
{
  $arrUsers[$row['userid']]=$row['username'].(isset($row['countid']) ? ' ('.$row['countid'].')' : '');
}
$intUsers = count($arrUsers);
asort($arrUsers);

echo '<h1>',$L['Statistics'],'</h1>',PHP_EOL;

echo '<h2>',$L['Users'],'*  ',( $m!=0 ? ' '.$L['dateMM'][$m] : ''),' ',$y,$strSectionTitle,'</h2>',PHP_EOL;
echo L('User',$intUsers).'<br/><br/>';

echo '<table class="data_t">',PHP_EOL;
echo '<tr class="data_t">',PHP_EOL;
echo '<td  style="width:10px;">&nbsp;</td>';
echo '<td  style="width:300px">',$L['Username'],' (',strtolower($L['Messages']),')</td>';
echo '<td  style="width:250px">',$L['Information'],'</td>';
echo '<td  style="width:10px;">&nbsp;</td>';
echo '</tr>',PHP_EOL;
echo '<tr class="data_t">',PHP_EOL;
echo '<td >&nbsp;</td>';
echo '<td  style="vertical-align:top">';

if ( $intUsers>0 )
{
  $str = '<br/>'; if ($intUsers>50) $str = ', ';
  foreach($arrUsers as $intId=>$strName)
  {
  echo '<a class="ajaxmouseover" id="u',$intId,'" href="qnm_user.php?id=',$intId,'">',$strName,'</a>',$str;
  }
}
else
{
  echo $L['None'].'<br/>';
}

echo '</td>',PHP_EOL;
echo '<td style="vertical-align:top">',PHP_EOL;

  // DISPLAY Preview
  echo '<script type="text/javascript"></script><noscript>Your browser does not support JavaScript</noscript>';
  echo '<div id="title_err"></div>',PHP_EOL;

// preview
echo '</td>
<td>&nbsp;</td>
</tr>
</table>
';

echo '<p>*  <span class="small">',$L['Distinct_users'],'</span></p>
';

// HTML END

echo '<p><a href="',$oVIP->exiturl,'">',$oVIP->exitname,'</a></p>',PHP_EOL;

include 'qnm_inc_ft.php';