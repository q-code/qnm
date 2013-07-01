<?php

// QNM 1.0 build:20130410

class cUser
{
private $prefix;
public $auth = false;
public $id = 0;
public $coockieconfirm = false; // Will be set to TRUE when login is performed via coockie.
public $name = 'Guest';
public $role = 'V'; //A=Administator,M=Moderator,U=User,V=Visitor
public $error = ''; // Provide info on Login problem or User access limitations
public $stats = '';

// --------

function __construct($prefix='')
{
  $this->prefix = strtolower($prefix); if ( empty($this->prefix) ) $this->prefix = strtolower(substr(constant('QT'),0,3));
  // User's properties as in CURRENT SESSION
  if ( isset($_SESSION[QT.'_usr_auth']) )  $this->auth = $_SESSION[QT.'_usr_auth'];
  if ( isset($_SESSION[QT.'_usr_id']) )    $this->id = $_SESSION[QT.'_usr_id'];
  if ( isset($_SESSION[QT.'_usr_name']) )  $this->name = $_SESSION[QT.'_usr_name'];
  if ( isset($_SESSION[QT.'_usr_role']) )  $this->role = $_SESSION[QT.'_usr_role'];
  // all other user attributes can be accessed via the array $_SESSION[QT.'_usr_info'] after using RegisterUser()

  // User's properties if coockies valid
  if ( !$this->auth ) {
  if ( isset($_COOKIE[QT.'_cookname']) && isset($_COOKIE[QT.'_cookpass']) ) {
    $this->RegisterUser($_COOKIE[QT.'_cookname'],$_COOKIE[QT.'_cookpass'],false); // false because password is already hashed in coockies
    $this->coockieconfirm=true; // A welcome is displayed (at the end of the init file). This welcome is displayed only once as coockeconfirm is reset to FALSE on next page
  }}
}

// --------

function CanView($strCanView='V5',$bStopOff=true)
{
  // $strCanView user role (V[i], U, M or A) that can access the page (i=public access level)
  // $bStopOff stop when application off-line
  if ( $this->role=='A' ) { if ( $_SESSION[QT]['board_offline']=='1' ) echo '<p style="padding:4px;background-color:#ff0000;color:#ffffff">Board is offline but Administrators can make some actions.</p>'; return true; }
  if ( $strCanView=='U' && $this->role=='V') return false;
  if ( $strCanView=='M' && !$this->IsStaff() ) return false;
  if ( $strCanView=='A' && $this->role!='A' ) return false;
  if ( strlen($strCanView)==2 ) { $strPAL=substr($strCanView,-1,1); } else { $strPAL='5'; }
  if ( $this->role=='V' && $_SESSION[QT]['visitor_right']<$strPAL ) return false;
  if ( $_SESSION[QT]['board_offline']=='1' && $bStopOff ) return false;
  return true;
}

// --------

function IsStaff() { return ($this->role=='M' || $this->role=='A'); }

// --------

function IsPrivate($str,$id)
{
  // Check the privacy setting. $str is the user's privacy level (can be integer !)
  // Returns true/false if current user can see the private info
  if ( $str=='2' || $this->id==$id || $this->IsStaff() ) return false;
  if ( $str=='1' && $this->role!='V') return false;
  return true;
}

// --------

public function RegisterUser($name='',$password='',$bSha1=true)
{
  // Read and Set user properties. Also register this user in session variable $_SESSION[QT.'_usr']
  global $oDB;
  $oDB->Query('SELECT * FROM '.TABUSER.' WHERE name="'.$name.'" AND pwd="'.($bSha1===false ? $password : sha1($password)).'"'); // when checking from coockies, password is already hashed
  if ( $row=$oDB->Getrow() )
  {
    $this->auth = true;
    $this->id = (int)$row['id']; unset($row['id']); // unset in orther to not include this in the $_SESSION[QT.'_usr_info']
    $this->name = $name;         unset($row['name']);
    $this->role = $row['role'];  unset($row['role']);
  }
  // Register User in session
  $_SESSION[QT.'_usr_auth'] = $this->auth;
  $_SESSION[QT.'_usr_id'] = $this->id;
  $_SESSION[QT.'_usr_name'] = $this->name;
  $_SESSION[QT.'_usr_role'] = $this->role;
  $_SESSION[QT.'_usr_info'] = $row;
}

// --------

public function Login($username='',$password='',$bRemember=FALSE)
{
  // Check profile exists for this username/password (auth is true if only one user exists)

  $this->auth = ( 1===cVIP::SysCount('members',' AND name="'.$username.'" AND pwd="'.sha1($password).'"') ? true : false );

  // External login: even if profile is not found ($this->auth is false) external login may be able to create a new profile
  // Note 'Admin' MUST ALWAYS BYPASS external login:
  // When ldap config/server changes, Admin (at least) MUST be able to login to change the settings!

  if ( isset($_SESSION[QT]['login_addon']) && $_SESSION[QT]['login_addon']!=='0' && $username!=='Admin' )
  {
    $sModuleKey = $_SESSION[QT]['login_addon'];
    if ( isset($_SESSION[QT][$sModuleKey]) && $_SESSION[QT][$sModuleKey]!=='0' )
    {
      if ( file_exists($this->prefix.$sModuleKey.'_login.php') )
      {
        include $this->prefix.$sModuleKey.'_login.php';
      } else {
        $this->auth = false;
        $this->error = 'Access denied (missing addon controler)';
      }
    }
  }

  // Register and get extra user info, if authentication is successfull

  if ( $this->auth )
  {
    $this->RegisterUser($username,$password,true); // get extra user info and register user's info
    $this->ChangeStats('lastvisit;lastip',date('Ymd His').';'.$_SERVER['REMOTE_ADDR']); // add last visit info to the user stats

    if ( $bRemember )
    {
    setcookie(QT.'_cookname', htmlspecialchars($this->name,ENT_QUOTES), time()+60*60*24*100, '/');
    setcookie(QT.'_cookpass', sha1($password), time()+60*60*24*100, '/');
    setcookie(QT.'_cooklang', $_SESSION[QT]['language'], time()+60*60*24*100, '/');
    }

    // Reset parameters (because the Role can impact the lists)
    unset($_SESSION[QT]['sys_domains']);
    unset($_SESSION[QT]['sys_sections']);
  }

  return $this->auth;
}

// --------

public function ChangeStats($f='',$v='',$bSave=true)
{
  if ( !is_string($f) || !is_string($v) ) die('cUser::ChangeStats Invalid arguments');
  if ( trim($f)==='' || trim($v)==='' ) die('cUser::ChangeStats Invalid arguments');
  if ( substr($f,-1,1)===';' ) $f = substr($f,0,-1);
  if ( substr($v,-1,1)===';' ) $v = substr($v,0,-1);
  $arrF = explode(';',$f);
  $arrV = explode(';',$v);
  $i = count($arrF);
  if ( $i!=count($arrV) ) die('cUser::ChangeStats Invalid arguments');

  $arr = QTexplode($this->stats);
  do { $i--; $arr = QTarradd($arr,$arrF[$i],$arrV[$i]); } while($i);

  $this->stats = QTimplode($arr);
  $_SESSION[QT.'_usr_stats']= $this->stats;
  if ( $bSave)
  {
    global $oDB;
    $oDB->Query('UPDATE '.TABUSER.' SET stats="'.$this->stats.'" WHERE id='.$this->id);
  }
  return $this->stats;
}

// --------

public static function GetUserInfo($key,$not=null,$bInt=false)
{
  // User's property as in CURRENT SESSION or $not if property not fount. Existing value can be converted to int ($bInt=true)
  if ( empty($key) ) die('cUser::GetUserInfo: invalid argument #1');
  if ( !isset($_SESSION[QT.'_usr_info'][$key]) ) return $not;
  return ($bInt ? (int)$_SESSION[QT.'_usr_info'][$key] : $_SESSION[QT.'_usr_info'][$key]);
}
public static function SetUserInfo($key,$value)
{
  if ( cUser::GetUserInfo($key,null)===null ) return;
  $_SESSION[QT.'_usr_info'][$key] = $value;
}

// --------

public static function SessionUnset()
{
  // User's properties as in CURRENT SESSION
  $_SESSION[QT.'_usr_auth']=false;
  $_SESSION[QT.'_usr_id']=0;
  $_SESSION[QT.'_usr_name']='Guest';
  $_SESSION[QT.'_usr_role']='V';
  $_SESSION[QT.'_usr_info']=array();
}

// --------

}