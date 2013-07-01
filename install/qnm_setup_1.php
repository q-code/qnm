<?php // v 1.0 build:20130410

session_start();

if ( isset($_GET['language']) ) $_SESSION['qnm_setup_lang']=$_GET['language'];
if ( !isset($_SESSION['qnm_setup_lang']) ) $_SESSION['qnm_setup_lang']='en';
if ( !file_exists('qnm_lang_'.$_SESSION['qnm_setup_lang'].'.php') ) $_SESSION['qnm_setup_lang']='en';

include 'qnm_lang_'.$_SESSION['qnm_setup_lang'].'.php';
include '../bin/config.php';

$strAppl = 'Q-NetManagement 1.0';
$strPrevUrl = 'qnm_setup.php';
$strNextUrl = 'qnm_setup_2.php';
$strPrevLabel= $L['Back'];
$strNextLabel= $L['Next'];
$strError = '';

// --------
// HTML START
// --------

include 'qnm_setup_hd.php';

echo '
<table>
<tr valign="top">
<td width="475" style="padding:0px">';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  include '../bin/class/qt_class_db.php';

  $qnm_dbsystem = strip_tags(trim($_POST['qnm_dbsystem']));
  $qnm_host     = strip_tags(trim($_POST['qnm_host']));
  $qnm_database = strip_tags(trim($_POST['qnm_database']));
  if ( $qnm_dbsystem=='sqlite' && substr($qnm_database,-3,3)!='.db' ) $qnm_database .= '.db';
  $qnm_prefix   = strip_tags(trim($_POST['qnm_prefix']));
  $qnm_user     = strip_tags(trim($_POST['qnm_user']));
  $qnm_pwd      = strip_tags(trim($_POST['qnm_pwd']));
  $qnm_port     = strip_tags(trim($_POST['qnm_port']));
  $qnm_dsn      = strip_tags(trim($_POST['qnm_dsn']));
  $str = strip_tags(trim($_POST['qnm_dbo_login']));
  if ( $str!='') $_SESSION['qnm_dbologin'] = $str;
  $str = strip_tags(trim($_POST['qnm_dbo_pswrd']));
  if ( $str!='') $_SESSION['qnm_dbopwd'] = $str;

  // Test Connection

  if ( isset($_SESSION['qnm_dbologin']) )
  {
    $oDB = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$_SESSION['qnm_dbologin'],$_SESSION['qnm_dbopwd'],$qnm_port,$qnm_dsn);
  }
  else
  {
    $oDB = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
  }

  if ( empty($oDB->error) )
  {
    echo '<div class="setup_ok">',$L['S_connect'],'</div>';
  }
  else
  {
    echo '<div class="setup_err">',sprintf ($L['E_connect'],$qnm_database,$qnm_host),'</div>';
  }

  // Save Connection

  $strFilename = '../bin/config.php';
  $content = '<?php
  $qnm_dbsystem = "'.$qnm_dbsystem.'";
  $qnm_host = "'.$qnm_host.'";
  $qnm_database = "'.$qnm_database.'";
  $qnm_prefix = "'.$qnm_prefix.'";
  $qnm_user = "'.$qnm_user.'";
  $qnm_pwd = "'.$qnm_pwd.'";
  $qnm_port = "'.$qnm_port.'";
  $qnm_dsn = "'.$qnm_dsn.'";
  $qnm_install = "'.date('Y-m-d').'";';

  if (!is_writable($strFilename)) $strError="Impossible to write into the file [$strFilename].";
  if ( empty($strError) )
  {
  if (!$handle = fopen($strFilename, 'w')) $strError="Impossible to open the file [$strFilename].";
  }
  if ( empty($strError) )
  {
  if ( fwrite($handle, $content)===FALSE ) $strError="Impossible to write into the file [$strFilename].";
  fclose($handle);
  }

  // End message
  if ( empty($strError) )
  {
    echo '<div class="setup_ok">',$L['S_save'],'</div>';
  }
  else
  {
    echo '<div class="setup_err">',$strError,$L['E_save'],'</div>';
  }
}

// --------
// FORM
// --------

echo '<form method="post" name="install" action="qnm_setup_1.php">
<table  cellpadding="5">
<tr>
<td colspan="2"><h2>',$L['Connection_db'],'</h2><br/></td>
</tr>
';
echo '<tr>
<td>',$L['Database_type'],'</td>
<td><select name="qnm_dbsystem">
<option value="mysql4"',($qnm_dbsystem=='mysql4' ? ' selected="selected"' : ''),'>MySQL 4</option>
<option value="mysql"',($qnm_dbsystem=='mysql' ? ' selected="selected"' : ''),'>MySQL 5 or next</option>
<option value="sqlsrv"',($qnm_dbsystem=='sqlsrv' ? ' selected="selected"' : ''),'>SQL server (Microsoft driver)</option>
<option value="mssql"',($qnm_dbsystem=='mssql' ? ' selected="selected"' : ''),'>SQL server (old driver)</option>
<option value="pg"'.($qnm_dbsystem=='pg' ? 'selected="selected"' : ''),'>PostgreSQL</option>
<option value="ibase"'.($qnm_dbsystem=='ibase' ? 'selected="selected"' : ''),'>FireBird</option>
<option value="sqlite"'.($qnm_dbsystem=='sqlite' ? 'selected="selected"' : ''),'>SQLite</option>
<option value="db2"',($qnm_dbsystem=='db2' ? ' selected="selected"' : ''),'>IBM DB2</option>
<option value="oci"',($qnm_dbsystem=='oci' ? ' selected="selected"' : ''),'>Oracle</option>
</select></td>
</tr>
';
echo '<tr>
<td>',$L['Database_host'],'</td>
<td>
<input type="text" name="qnm_host" value="',$qnm_host,'" size="15" maxlength="100"/>
<input type="text" name="qnm_port" value="',$qnm_port,'" size="5" maxlength="20"/>
<input type="text" name="qnm_dsn" value="',$qnm_dsn,'" size="8" maxlength="100"/>
</td>
</tr>
<tr>
<td>',$L['Database_name'],'</td>
<td><input type="text" name="qnm_database" value="',$qnm_database,'" size="15" maxlength="100"/></td>
</tr>
<tr>
<td>',$L['Table_prefix'],'</td>
<td><input type="text" name="qnm_prefix" value="',$qnm_prefix,'" size="15" maxlength="100"/></td>
</tr>
<tr>
<td>',$L['Database_user'],'</td>
<td>
<input type="text" name="qnm_user" value="',$qnm_user,'" size="15" maxlength="100"/>
<input type="password" name="qnm_pwd" value="',$qnm_pwd,'" size="15" maxlength="100"/>
</td>
</tr>
<tr>
<td colspan="2" style="background-color:#CCCCCC"><span class="small">',$L['Htablecreator'],'</span></td>
</tr>
<tr>
<td style="background-color:#CCCCCC">Table creator (login/password)</td>
<td style="background-color:#CCCCCC">
<input type="text" name="qnm_dbo_login" value="',(isset($_SESSION['qnm_dbologin']) ? $_SESSION['qnm_dbologin'] : ''),'" size="15" maxlength="100"/>
<input type="password" name="qnm_dbo_pswrd" value="',(isset($_SESSION['qnm_dbopwd']) ? $_SESSION['qnm_dbopwd'] : ''),'" size="15" maxlength="100"/>
</td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
<td colspan="2" style="text-align:center"><input type="submit" name="ok" value="',$L['Save'],'"/></td>
</tr>

</table>
</form>
<span class="small">',$L['Upgrade'],'</a></span>';

echo '
</td>
<td class="hidden"><div class="setup_help">',$L['Help_1'],'</div></td>
</tr>
</table>
';

// --------
// HTML END
// --------

include 'qnm_setup_ft.php';