<?php

// QNM 1.0 build:20130410

// -----------------
// Connection config
// -----------------
require_once 'bin/config.php';
if ( isset($qnm_install) ) { define('QT','qnm'.substr($qnm_install,-1)); } else { define('QT','qnm'); }

// -----------------
// System constants (this CANNOT be changed by webmasters)
// -----------------
if ( !defined('PHP_VERSION_ID') ) { $version=explode('.',PHP_VERSION); define('PHP_VERSION_ID',($version[0]*10000+$version[1]*100+$version[2])); }
define('TABDOMAIN', $qnm_prefix.'qnmdomain');
define('TABSECTION', $qnm_prefix.'qnmsection');
define('TABUSER', $qnm_prefix.'qnmuser');
define('TABNE', $qnm_prefix.'qnmelement');
define('TABNL', $qnm_prefix.'qnmlink');
define('TABNC', $qnm_prefix.'qnmconn');
define('TABPOST', $qnm_prefix.'qnmpost');
define('TABSETTING', $qnm_prefix.'qnmsetting');
define('TABLANG', $qnm_prefix.'qnmlang');
define('TABDOC', $qnm_prefix.'qnmdoc');
define('QNMCLASSES', 'e,l,c'); // Supported Element [class code]
define('QNMVERSION', '1.0 build:20130410');
define('QSEL', ' selected="selected"');
define('QCHE', ' checked="checked"');
define('QDIS', ' disabled="disabled"');
define('N', "\n");
define('S', '&nbsp;');
define('START', 1);
define('END', -1);
define('JQUERY_OFF', 'bin/js/jquery.min.js'); // jQuery resource when offline. This will be used if CDN (defined here after) is not possible.
define('JQUERYUI_OFF', 'bin/js/jquery-ui.min.js');
define('JQUERYUI_CSS_OFF', 'bin/css/jquery-ui/themes/base/jquery-ui.css');

// -----------------
// Interface constants (this can be changed by webmasters)
// -----------------
define('QNM_OB_GZ', null); //'ob_gzhandler'); // Allow serving compressed pages. Use NULL if zlib extension is not available on your server (or if you want to disable compression)
define('QNM_BACKBUTTON', '&nbsp;&laquo;&nbsp;'); // use FALSE to hide backbutton
define('QNM_DFLT_VIEWMODE',   'N');   // default view mode: N=normal view, C=compact view
define('QNM_SHOW_VIEWMODE',   true);  // allow user changing view mode
define('QNM_SHOW_TIME',       true);  // show time in the bottom bar
define('QNM_SHOW_MEMBERLIST', true);  // show memberlist in the menu
define('QNM_SHOW_MODERATOR',  true);  // show moderator in the bottom bar
define('QNM_SHOW_GOTOLIST',   true);  // show gotolist in the bottom bar
define('QNM_SHOW_DOMAIN',     false); // show domain + section name in the crumb trail bar
define('QNM_CRUMBTRAIL',' &middot; ');// crumbtrail separator (dont forget spaces)
define('QNM_MENUSEPARATOR', ' &middot; '); // bottom menu separator (dont forget spaces)
define('QNM_CONVERT_AMP',     false); // save &amp; instead of &. Use TRUE to make &#0000; symbols NOT working.
define('QNM_SEVERAL_NOTES',   10); // Show ! in the index page when notes in process >= this value.
define('QNM_DROP_TAGS',       false); // Remove html tags in notes.
define('QNM_SIMPLESEARCH',    true);  // simple search by default (use false to directly search as advanced)
define('QNM_DIR_PIC', 'avatar/'); // where to store uploaded userphoto, if allowed, (with final '/')
define('QNM_DIR_DOC', 'upload/'); // where to store uploaded files, if allowed, (with final '/')
define('QNM_JAVA_MAIL', false);   // Protect e-mail by a javascript
define('QNM_QUERY_SEPARATOR', ','); // Values separator in search queries (when mutliple criterias are allowed). Also used as jQuery autocomplete-ajax separator. CANNOT BE EMPTY!
define('QNM_WEEKSTART', 1);       // Start of the week (use code 1=monday,...,7=sunday)
define('QNM_STAFFEDITSTAFF',true); // Staff member can edit posts issued by an other staff member
define('QNM_STAFFEDITADMIN',true); // Staff member can edit posts issued by an administrator
define('QNM_CHANGE_USERNAME',true);  // User can change his username
define('QNM_SECTIONLOGO_WIDTH', 75); // Maximum size of section logo (pixels)
define('QNM_SECTIONLOGO_HEIGHT',75); // Maximum size of section logo (pixels)
define('QNM_TAGS_SORT',true); // Sort the tags in alphabetic order when editing (or adding tags to) element
define('QNM_TAGS_CASE',false); // Save tags in 0=lowercase, 1=uppercase, false=no change
define('QNM_SHOW_ITEM_NOTES', 10); // Maximum number of notes displayed in a element page. A more link is added if required
define('QNM_EDIT_INSERTDATE', true); // Allow changing creation date

define('QNM_URLREWRITE',false);
// URL rewriting (for expert only):
// Rewriting url requires that your server is configured with following rule for the application folder: RewriteRule ^(.+)\.html(.*) qnm_$1.php$2 [L]
// This can NOT be activated if you application folder contains html pages (they will not be accessible anymore when urlrewriting is acticated)

// -----------------
// JQUERY (this can be changed by webmaster)
// -----------------
// Content Delivery Network for jQuery and jQuery-UI. Using a CDN will increase performances.
// Possible CDN are: Google, Microsoft, jQuery-Media-Temple.
// You can also decide to use your local copy (in the bin/ directory) to avoid using a CDN.

define('JQUERY_CDN', 'https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js');
  // define('JQUERY_CDN', 'http://ajax.aspnetcdn.com/ajax/jquery/jquery-1.8.3.min.js');
  // define('JQUERY_CDN', 'http://code.jquery.com/jquery-1.8.3.js');
  // define('JQUERY_CDN', 'bin/js/jquery.min.js');

define('JQUERYUI_CDN', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');
  // define('JQUERYUI_CDN', 'http://ajax.aspnetcdn.com/ajax/jquery.ui/1.9.2/jquery-ui.min.js');
  // define('JQUERYUI_CDN', 'http://code.jquery.com/ui/1.9.2/jquery-ui.js');
  // define('JQUERYUI_CDN', 'bin/js/jquery-ui.min.js');

define('JQUERYUI_CSS_CDN', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css');
  // define('JQUERYUI_CSS_CDN', 'http://ajax.aspnetcdn.com/ajax/jquery.ui/1.9.2/themes/base/jquery-ui.css');
  // define('JQUERYUI_CSS_CDN', 'http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css');
  // define('JQUERYUI_CSS_CDN', 'bin/css/jquery-ui/themes/base/jquery-ui.css');

// -----------------
// Class and functions
// -----------------
if ( !ob_start(QNM_OB_GZ) ) ob_start();
require_once 'bin/class/qt_class_db.php';
require_once 'bin/qt_lib_txt.php';
require_once 'bin/class/qt_class_html.php';
require_once 'bin/class/qt_class_table.php';
require_once 'bin/class/qt_class_sys.php';
require_once 'bin/class/qnm_class_user.php';
require_once 'bin/class/qnm_class_vip.php';
require_once 'bin/class/qt_abstracts.php';
require_once 'bin/class/qnm_class_sec.php';
require_once 'bin/class/qnm_class_ne.php'; // cNE (and cNL extend) class definition
require_once 'bin/class/qnm_class_nl.php'; // cNE (and cNL extend) class definition
require_once 'bin/class/qnm_class_post.php';
require_once 'bin/qnm_fn_base.php';
require_once 'bin/qnm_fn_html.php';

// -----------------
//  Installation wizard (if file exists)
// -----------------
if ( !isset($qnm_install) ) $qnm_install='';
if ( empty($qnm_install) )
{
  if ( file_exists('install/index.php') )
  {
  echo 'QNM  ',QNMVERSION,' <a href="install/index.php">starting installation</a>...';
  echo '<meta http-equiv="REFRESH" content="1;url=install/index.php">';
  exit;
  }
}

// ----------------
// Initialise Classes
// ----------------
$oDB = new cDB($qnm_dbsystem,$qnm_host,$qnm_database,$qnm_user,$qnm_pwd,$qnm_port,$qnm_dsn); if ($oDB===FALSE) Exit;
if ( !empty($oDB->error) ) die ('<p><font color="red">Connection with database failed.<br/>Please contact the webmaster for further information.</font></p><p>The webmaster must check that server is up and running, and that the settings in the config file are correct for the database.</p>');

// Load system parameters

if ( !isset($_SESSION[QT]) ) GetParam(true);

// check major parameters

if ( !isset($_SESSION[QT]['skin_dir']) ) $_SESSION[QT]['skin_dir']='skin/default';
if ( !isset($_SESSION[QT]['language']) ) $_SESSION[QT]['language']='english';
if ( empty($_SESSION[QT]['skin_dir']) ) $_SESSION[QT]['skin_dir']='skin/default';
if ( empty($_SESSION[QT]['language']) ) $_SESSION[QT]['language']='english';
if ( substr($_SESSION[QT]['skin_dir'],0,5)!='skin/' ) $_SESSION[QT]['skin_dir'] = 'skin/'.$_SESSION[QT]['skin_dir'];

// change language if required (by coockies or by the menu)

$str=GetIso();
if ( isset($_COOKIE[QT.'_cooklang']) ) $str=substr($_COOKIE[QT.'_cooklang'],0,2);
if ( isset($_GET['lx']) ) $str=substr($_GET['lx'],0,2);
if ( $str!=GetIso() && !empty($str) )
{
  include 'bin/qnm_lang.php';
  if ( array_key_exists($str,$arrLang) )
  {
    $_SESSION[QT]['language'] = $arrLang[$str][2];
    if ( isset($_COOKIE[QT.'_cooklang']) ) setcookie(QT.'_cooklang', $str, time()+60*60*24*100, '/');
    // unset dictionnaries
    $_SESSION['L'] = array();
  }
  else
  {
    die('Wrong iso code language');
  }
}

if ( !isset($_SESSION['L']) ) $_SESSION['L'] = array();

CheckDico('index domain sec secdesc');

$oVIP = new cVIP();

  if ( !isset($_SESSION[QT.'_usr_id']) )    $_SESSION[QT.'_usr_id']=-1;
  if ( !isset($_SESSION[QT.'_usr_name']) )  $_SESSION[QT.'_usr_name']='Guest';
  if ( !isset($_SESSION[QT.'_usr_role']) )  $_SESSION[QT.'_usr_role']='V';
  if ( !isset($_SESSION[QT.'_usr_items']) ) $_SESSION[QT.'_usr_items']=0;
  if ( !isset($_SESSION[QT.'_usr_stats']) ) $_SESSION[QT.'_usr_stats']='';

// ----------------
// Initialise variable
// ----------------

$error = ''; // Required when server uses register_global_on
$warning = '';
$arrExtData = array(); // Can be used by extensions

if ( !isset($_SESSION[QT]['viewmode']) ) $_SESSION[QT]['viewmode']=QNM_DFLT_VIEWMODE;
if ( !isset($_SESSION[QT]['userlang']) ) $_SESSION[QT]['userlang']='1';
if ( !isset($_SESSION[QT]['cal_shownews']) ) $_SESSION[QT]['cal_shownews']=FALSE;
if ( !isset($_SESSION[QT]['cal_showall']) ) $_SESSION[QT]['cal_showall']=FALSE;

// ----------------
// Load dictionary
// ----------------

include_once GetLang().'qnm_main.php';
include_once GetLang().'qnm_icon.php';

// ----------------
// Default HTML settings
// ----------------
$oHtml = new cHtml();
$oHtml->dtd = QNM_HTML_DTD;
$oHtml->file = 'qnm';
$oHtml->html = '<html xmlns="http://www.w3.org/1999/xhtml" dir="'.QNM_HTML_DIR.'" xml:lang="'.QNM_HTML_LANG.'" lang="'.QNM_HTML_LANG.'">';
$oHtml->title = $_SESSION[QT]['site_name'];
$oHtml->metas['charset'] = '<meta charset="'.QNM_HTML_CHAR.'" />';
$oHtml->metas['description'] = '<meta name="description" content="QNM net management" />';
$oHtml->metas['keywords'] = '<meta name="keywords" content="QNM,Network,Management,qt-cute,OpenSource" />';
$oHtml->metas['author'] = '<meta name="author" content="qt-cute.org" />';
$oHtml->metas['viewport'] = '<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5" />';
$oHtml->links['icon'] = '<link rel="shortcut icon" href="'.$_SESSION[QT]['skin_dir'].'/qnm_icon.ico" />';
$oHtml->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$_SESSION[QT]['skin_dir'].'/qnm_main.css" /><link rel="stylesheet" type="text/css" href="bin/css/qnm_print.css" media="print" />';
$oHtml->links['jqueryui'] = '<link rel="stylesheet" type="text/css" href="'.JQUERYUI_CSS_CDN.'" />';
$oHtml->scripts['base'] = '<script type="text/javascript" src="bin/js/qnm_base.js"></script>';
$oHtml->scripts['jquery'] = '<script type="text/javascript" src="'.JQUERY_CDN.'"></script>';
$oHtml->scripts['jqueryui'] = '<script type="text/javascript" src="'.JQUERYUI_CDN.'"></script>';

// ----------------
// Check user in case of coockie login
// ----------------

if ( $oVIP->user->coockieconfirm )
{
  $oVIP->exitname = $L['Continue'];
  include 'qnm_inc_hd.php';
  $oHtml->Msgbox($L['Login'],'msgbox login');
  echo '<h2>'.L('Welcome').' '.$oVIP->user->name.'</h2><p><a href="'.Href($oVIP->exiturl).'">'.$oVIP->exitname.'</a>&nbsp; &middot; &nbsp;<a href="'.Href('qnm_login.php?a=out').'">'.sprintf(L('Welcome_not'),$oVIP->user->name).'</a></p>';
  $oHtml->Msgbox(END);
  include 'qnm_inc_ft.php';
  exit;
}

// -----------------
//  Time setting (for PHP >=5.2)
// -----------------
if ( PHP_VERSION_ID>=50200 ) {
if ( isset($_SESSION[QT]['defaulttimezone']) ) {
if ( $_SESSION[QT]['defaulttimezone']!=='' ) {

date_default_timezone_set($_SESSION[QT]['defaulttimezone']);

}}}