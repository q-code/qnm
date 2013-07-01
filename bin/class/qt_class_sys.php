<?php

// QT re-usable component 1.0 build:20121117

// ==========
// This class includes info on the current user and the current page
// The class also provides major lists or global stats used in most of the pages
// ==========

class cSYS
{
  public $prefix; // TLA used for the files prefix
  public $selfurl;
  public $selfname;
  public $selfuri;
  public $exiturl;
  public $exitname;
  public $exituri;

  public $user; // sub class cUser
  public $msg; // sub class cMsg
  public $stats; // sub class cStats

  function __construct($prefix='')
  {
    $this->prefix = strtolower($prefix); if ( empty($this->prefix) ) $this->prefix = strtolower(substr(constant('QT'),0,3));
    $this->selfurl = $this->prefix.'_index.php';
    $this->selfname = '';
    $this->selfuri = '';  // URL parameters
    $this->exiturl = $this->prefix.'_index.php';
    $this->exitname = 'Back';
    $this->exituri = '';
    $this->user = new cUser($this->prefix); // performs session-login/cockie check and register
    $this->msg = new cMsg(); // subclass
    $this->stats = new cStats();
  }
}

// ========

class cLang
{
  public static function Add($strType='',$strLang='en',$strId='',$strName='',$bCheck=false)
  {
    QTargs( 'cLang::Add',array($strType,$strLang,$strId,$strName,$bCheck),array('str','str','str','str','boo') );
    QTargs( 'cLang::Add',array($strType,$strLang,$strId,$strName),'empty' );

    // Process
    $prefix = strtoupper(substr(constant('QT'),0,3));
    if ( !defined($prefix.'_CONVERT_AMP') ) define($prefix.'_CONVERT_AMP',false);

    global $oDB;
    if ( $bCheck )
    {
    $oDB->Query('SELECT count(objid) AS countid FROM '.TABLANG.' WHERE objtype="'.$strType.'" AND objlang="'.strtolower($strLang).'" AND objid="'.$strId.'"');
    $row=$oDB->Getrow();
    if ( $row['countid']!=0 ) return False;
    }
    $oDB->Query('INSERT INTO '.TABLANG.' (objtype,objlang,objid,objname) VALUES ("'.$strType.'","'.strtolower($strLang).'","'.$strId.'","'.addslashes(QTconv($strName,'3',constant($prefix.'_CONVERT_AMP'),false)).'")');
    return true;
  }
  public static function Delete($strType='',$strId='')
  {
    if ( is_array($strType) ) $strType = implode('" OR objtype="',$strType);
    QTargs( 'cLang::Delete',array($strType,$strId) );
    QTargs( 'cLang::Delete',array($strType,$strId),'empty' );

    // Process

    global $oDB;
    $oDB->Query( 'DELETE FROM '.TABLANG.' WHERE (objtype="'.$strType.'") AND objid="'.$strId.'"' );
  }
  public static function Get($strType='',$strLang='en',$strId='*')
  {
    // Return the object name (translated)
    // Can return an array of object names (in this language) when $strId is '*'
    // Can return an array of object translation when $strLang is '*'

    QTargs('cLang::Get',array($strType,$strLang,$strId));
    QTargs('cLang::Get',array($strType,$strLang,$strId),'empty');
    if ( $strId==='*' && $strLang==='*' ) Die('cLang::Get: Arg 2 and 3 cannot be *');

    // Process

    global $oDB;
    if ( $strId==='*' )
    {
      $arr = array();
      $oDB->Query('SELECT objid,objname FROM '.TABLANG.' WHERE objtype="'.$strType.'" AND objlang="'.strtolower($strLang).'"');
      while($row=$oDB->Getrow())
      {
        if ( !empty($row['objname']) ) $arr[$row['objid']]=$row['objname'];
      }
      return $arr;
    }
    elseif ( $strLang==='*' )
    {
      $arr = array();
      $oDB->Query('SELECT objlang,objname FROM '.TABLANG.' WHERE objtype="'.$strType.'" AND objid="'.$strId.'"');
      while($row=$oDB->Getrow())
      {
        $arr[$row['objlang']]=$row['objname'];
      }
      return $arr;
    }
    else
    {
      $oDB->Query('SELECT objname FROM '.TABLANG.' WHERE objtype="'.$strType.'" AND objlang="'.strtolower($strLang).'" AND objid="'.$strId.'"');
      $row=$oDB->Getrow();
      return (empty($row['objname']) ? '' : $row['objname']);
    }
  }
}

// ========

class cStats
{
  // This uses dynamic properties
  // Any properties can be created (i.e. to create a property 'items' just set $oStats->items=0)
  // The properties are stored in a session variable (i.e. $_SESSION[QT]['sys_stat_items'])
  // It's USELESS to create several object cStats (there is only one storage per session)
  // NOTE: when properties is not defined, __get generate an error
  function __get($prop)
  {
    if ( !isset($this->$prop) && isset($_SESSION[QT]['sys_stat_'.$prop]) ) $this->$prop = $_SESSION[QT]['sys_stat_'.$prop];
    if ( isset($this->$prop) ) return $this->$prop;
    echo 'cStats: undefined properties '.$prop;
  }
  function __set($prop,$value)
  {
    $this->$prop = $value;
    $_SESSION[QT]['sys_stat_'.$prop] = $this->$prop;
  }
  public function RemoveProperty($prop)
  {
    if ( isset($_SESSION[QT]['sys_stat_'.$prop]) ) unset($_SESSION[QT]['sys_stat_'.$prop]);
    if ( isset($this->$prop) ) unset($this->$prop);
  }
}

// ========

class cMsg
{
  // This class handles a message comming from a previous page (session variable 'pagedialog')
  // The message is displayed thanks to a jquery function from *_page_header.
  // While the session variable is destroyed after display, the properties of this class remains accessible
  public $text; // '' means nothing (no display)
  public $fulltext;
  public $type; // i=info, e=error, w=warning, o=ok (default)
  public $items; // affected items
  function __construct()
  {
    if ( !empty($_SESSION['pagedialog']) ) $this->FromString($_SESSION['pagedialog']);
  }
  public function Clear()
  {
    $_SESSION['pagedialog']=null;
  }
  public function FromString($arr)
  {
    if ( is_string($arr) ) $arr=explode('|',$arr);
    if ( is_array($arr) )
    {
      if ( isset($arr[0]) ) $this->type = strtolower(substr($arr[0],0,1));
      if ( isset($arr[1]) ) $this->fulltext = str_replace('"','',$arr[1]);
      if ( isset($arr[2]) ) $this->items = (int)$arr[2];
      if ( $this->items>1 ) $this->fulltext .= ' ('.$this->items.')';
      $this->text = $this->fulltext;
      if ( strlen($this->text)>64 ) $this->text = substr($this->text,0,60).'...';
    }
    return $this->text;
  }
}