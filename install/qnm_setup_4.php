<?php // v 1.0 build:20130410

session_start();

if ( !isset($_SESSION['qnm_setup_lang']) ) $_SESSION['qnm_setup_lang']='en';
$strLang=$_SESSION['qnm_setup_lang']; // remember in ordrer to restore after reset (end of page)

include 'qnm_lang_'.$strLang.'.php';
include '../bin/config.php'; if ( $qnm_dbsystem=='sqlite' ) $qnm_database = '../'.$qnm_database;
include '../bin/class/qt_class_db.php';

$strAppl     = 'QNM 1.0';
$strPrevUrl  = 'qnm_setup_2.php';
$strNextUrl  = '../qnm_login.php?dfltname=Admin';
$strPrevLabel= $L['Back'];
$strNextLabel= $L['Finish'];
$strMessage = '';

// CHECK DB VERSION (in case of update)

$oDB = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);
if ( !empty($oDB->error) ) die ('<p><font color="red">Connection with database failed.<br/>Please contact the webmaster for further information.</font></p><p>The webmaster must check that server is up and running, and that the settings in the config file are correct for the database.</p>');

$oDB->Query('SELECT setting FROM '.$qnm_prefix.'qnmsetting WHERE param="version"');
$row=$oDB->Getrow();

// UPDAGRADE x.xx

if ( $row['setting']=='1.0' )
{
}

// --------
// HTML START
// --------

include 'qnm_setup_hd.php';

if (!empty($strMessage) ) echo $strMessage;

if ( isset($_SESSION['qnmInstalled']) )
{
echo '<p>Database 1.0 in place.</p>';
echo '<p>',$L['S_install_exit'],'</p>';
echo '<div style="width:350px; padding:10px; border-style:solid; border-color:#FF0000; border-width:1px; background-color:#EEEEEE">',$L['End_message'],'<br/>',$L['User'],': <b>Admin</b><br/>',$L['Password'],': <b>Admin</b><br/></div><br/>';
}
else
{
echo $L['N_install'];
}

// document folders

$error='';
if ( !is_dir('upload') )
{
  $error .= '<font color=red>Directory <b>upload</b> not found.</font><br/>Please create this directory and make it writeable (chmod 777) if you want to allow uploads<br/>';
}
else
{
  if ( !is_readable('upload') ) $error .= '<font color=red>Directory <b>upload</b> is not readable.</font><br/>Change permissions (chmod 777) if you want to allow uploads<br/>';
  if ( !is_writable('upload') ) $error .= '<font color=red>Directory <b>upload</b> is not writable.</font><br/>Change permissions (chmod 777) if you want to allow uploads<br/>';
}

if ( empty($error) )
{
  $iY = intval(date('Y'));
  for ($i=$iY;$i<=$iY+5;$i++)
  {
    if ( !is_dir('upload/'.$i) )
    {
      if ( mkdir('upload/'.$i) )
      {
        for ($j=1;$j<=12;$j++)
        {
        mkdir('upload/'.$i.'/'.($i*100+$j));
        }
      }
    }
  }
}

echo '<p><a href="../check.php">',$L['Check_install'],'</a></p>';

// DISCONNECT to reload new variables

$_SESSION = array();
$_SESSION['qnm_setup_lang']=$strLang; // restore language after reset

// --------
// HTML END
// --------

include 'qnm_setup_ft.php';