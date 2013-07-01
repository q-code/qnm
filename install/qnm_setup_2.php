<?php // v 1.0 build:20130410

session_start();

if ( !isset($_SESSION['qnm_setup_lang']) ) $_SESSION['qnm_setup_lang']='en';

include 'qnm_lang_'.$_SESSION['qnm_setup_lang'].'.php';
include '../bin/config.php'; if ( $qnm_dbsystem=='sqlite' ) $qnm_database = '../'.$qnm_database;

$strAppl     = 'QNM 1.0';
$strPrevUrl  = 'qnm_setup_1.php';
$strNextUrl  = 'qnm_setup_3.php';
$strPrevLabel= $L['Back'];
$strNextLabel= $L['Next'];

// --------
// HTML START
// --------

include 'qnm_setup_hd.php';

if ( isset($_POST['ok']) )
{
  include '../bin/class/qt_class_db.php';
  include '../bin/qnm_fn_base.php';

  if ( isset($_SESSION['qnm_dbopwd']) )
  {
  $qnm_user = $_SESSION['qnm_dbologin'];
  $qnm_pwd = $_SESSION['qnm_dbopwd'];
  }

  define('TABDOMAIN', $qnm_prefix.'qnmdomain');
  define('TABSECTION', $qnm_prefix.'qnmsection');
  define('TABUSER', $qnm_prefix.'qnmuser');
  define('TABNE', $qnm_prefix.'qnmelement');
  define('TABNC', $qnm_prefix.'qnmconn');
  define('TABNL', $qnm_prefix.'qnmlink');
  define('TABPOST', $qnm_prefix.'qnmpost');
  define('TABSETTING', $qnm_prefix.'qnmsetting');
  define('TABLANG', $qnm_prefix.'qnmlang');
  define('TABDOC', $qnm_prefix.'qnmdoc');

  $oDB = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn);

  if ( empty($oDB->error) )
  {
    // Install the tables
    $strTable = TABSETTING;
    echo "<p>A) {$L['Installation']} SETTING... ";
    include 'qnm_setup_setting.php';
    echo "{$L['Done']}, {$L['Default_setting']}<br/>";
    $strTable = TABDOMAIN;
    echo "B) {$L['Installation']} DOMAIN... ";
    include 'qnm_setup_domain.php';
    echo "{$L['Done']}, {$L['Default_domain']}<br/>";
    $strTable = TABSECTION;
    echo "C) {$L['Installation']} SECTION... ";
    include 'qnm_setup_section.php';
    echo "{$L['Done']}, {$L['Default_section']}<br/>";
    $strTable = TABNE;
    echo "D) {$L['Installation']} ELEMENT... ";
    include 'qnm_setup_element.php';
    echo "{$L['Done']}<br/>";
    $strTable = TABNC;
    echo "E) {$L['Installation']} CONN... ";
    include 'qnm_setup_conn.php';
    echo "{$L['Done']}<br/>";
    $strTable = TABNL;
    echo "F) {$L['Installation']} LINK... ";
    include 'qnm_setup_link.php';
    echo "{$L['Done']}<br/>";
    $strTable = TABPOST;
    echo "G) {$L['Installation']} POST... ";
    include 'qnm_setup_post.php';
    echo "{$L['Done']}<br/>";
    $strTable = TABUSER;
    echo "H) {$L['Installation']} USER... ";
    include 'qnm_setup_user.php';
    echo "{$L['Done']}, {$L['Default_user']}<br/>";
    $strTable = TABLANG;
    echo "I) {$L['Installation']} LANG... ";
    include 'qnm_setup_lang.php';
    echo "{$L['Done']}<br/>";
    $strTable = TABDOC;
    echo "J) {$L['Installation']} DOC... ";
    include 'qnm_setup_doc.php';
    echo "{$L['Done']}</p>";
    if ($result==FALSE)
    {
      echo '<div class="setup_err">',sprintf ($L['E_install'],$strTable,$qnm_database,$qnm_user),'</div>';
    }
    else
    {
      echo '<div class="setup_ok">',$L['S_install'],'</div>';
      $_SESSION['qnmInstalled'] = true;
      // save the url
      $strURL = ( empty($_SERVER['SERVER_HTTPS']) ? "http://" : "https://" ).$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
      $strURL = substr($strURL,0,-24);
      $oDB->Query('UPDATE '.TABSETTING.' SET setting="'.$strURL.'" WHERE param="site_url"');
    }
  }
  else
  {
    echo '<div class="setup_err">',sprintf ($L['E_connect'],$qnm_database,$qnm_host),'</div>';
  }

}
else
{
  echo '
  <h2>',$L['Install_db'],'</h2>
  <table>
  <tr valign="top">
  <td width="475" style="padding:5px">
  <form method="post" name="install" action="qnm_setup_2.php">
  <p class="small">',$L['Upgrade2'],'</p>
  <p>',sprintf($L['Create_tables'],$qnm_database),'&nbsp;<input type="submit" name="ok" value="',$L['Ok'],'"/></p>
  </form>
  </td>
  <td class="hidden"><div class="setup_help">',$L['Help_2'],'</div></td>
  </tr>
  </table>
  ';
}

// --------
// HTML END
// --------

include 'qnm_setup_ft.php';