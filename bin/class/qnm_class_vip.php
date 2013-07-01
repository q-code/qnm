<?php

// QNM 1.0 build:20130410

/***
 * VIP means Visitor In Page: This class includes info on the current user and the current page
 * The class also provides major lists or global stats used in most of the pages
 * This class is using a basic cSYS class that provides
 * public $selfurl;
 * public $selfname;
 * public $selfuri;
 * public $exiturl;
 * public $exitname;
 * public $exituri;
 * public $user; // sub class cUser
 * public $msg; // sub class cMsg
 * public $stats; // sub class cStats
 ***/

class cVIP extends cSYS
{

  public $domains = array(); // list of domains (translated) visible for the current user ($oVIP->user->role)
  public $sections = array();// list of sectionstitles (translated) visible for the current user ($oVIP->user->role)
  public $statuses = array();// list of statuses

  function __construct()
  {
    parent::__construct('qnm'); // call cSYS constructor

    // initialise list of domains, sections, status (depend on user role)
    if ( !isset($_SESSION[QT]['sys_domains']) ) $_SESSION[QT]['sys_domains'] = GetDomains($this->user->role);
    if ( !isset($_SESSION[QT]['sys_sections']) ) { $_SESSION[QT]['sys_sections'] = QTarrget(GetSections($this->user->role)); }
    $this->domains = $_SESSION[QT]['sys_domains'];
    $this->sections = $_SESSION[QT]['sys_sections'];
    $this->statuses = $this->GetStatuses();

    // stats initialisation (if not yet done)
    if ( !isset($_SESSION[QT]['sys_stat_items']) ) $this->stats->items = cSection::CountItems('*','items');
    if ( !isset($_SESSION[QT]['sys_stat_itemsZ']) ) $this->stats->itemsZ = cSection::CountItems('*','itemsZ');
    if ( !isset($_SESSION[QT]['sys_stat_notes']) ) $this->stats->notes = cSection::CountItems('*','notes');
    if ( !isset($_SESSION[QT]['sys_stat_notesA']) ) $this->stats->notesA = cSection::CountItems('*','notesA');
    if ( !isset($_SESSION[QT]['sys_stat_members']) ) $this->stats->members = cVIP::SysCount('members');
    if ( !isset($_SESSION[QT]['sys_stat_states']) ) $this->stats->states = cVIP::SysCount('states');
  }

  // -------- old methods, now handled by subclasses in cSYS

  function CanView($strCanView='V5',$bStopOff=true) { return $this->user->CanView($strCanView,$bStopOff); }
  function IsPrivate($str,$id) { return $this->user->IsPrivate($str,$id); }
  function IsStaff() { return $this->user->IsStaff(); }
  function Login($username='',$password='',$bRemember=FALSE) { return $this->user->Login($username,$password,$bRemember); }

  // --------

  public function GetStatuses()
  {
    return array(
    0=>array('name'=>L('Inactive'),'color'=>'#FF8181'),
    1=>array('name'=>L('Active'),'color'=>'inherit'),
    -1=>array('name'=>L('Deleted'),'color'=>'#eeeeee')
    );
  }

  // --------

  public static function AddUser($username='',$password='',$mail='',$role='U',$child='0',$parentmail='')
  {
    if ( empty($role) || empty($username) || empty($password) ) return false;
    global $oDB;
    $id = $oDB->Nextid(TABUSER);
    return $oDB->Query( 'INSERT INTO '.TABUSER.' (id,name,pwd,closed,role,mail,privacy,numpost,children,photo,stats) VALUES ('.$id.',"'.$username.'","'.sha1($password).'","0","'.strtoupper($role).'","'.$mail.'","1",0,"0","'.$child.'","firstdate='.Date('Ymd His').';parentmail='.$parentmail.'")' );
  }

  // --------

  public static function Unregister($row)
  {
    // delete avatar first
    if ( isset($row['photo']) )
    {
    if ( file_exists(QNM_DIR_PIC.$row['photo']) ) unlink(QNM_DIR_PIC.$row['photo']);
    }

    // update post.userid, post.username, topic.firstpostuser, topic.lastpostuser, topic.firstpostname, topic.lastpostname
    global $oDB;
    $oDB->Query('UPDATE '.TABPOST.' SET userid=0, username="Visitor" WHERE userid='.$row['id']);
    $oDB->Query('UPDATE '.TABSECTION.' SET moderator=1,moderatorname="Admin" WHERE moderator='.$row['id']);

    // Delete user
    $oDB->Query('DELETE FROM '.TABUSER.' WHERE id='.$row['id']);

    // Unregister global sys (will be recomputed on next page)
    Unset($_SESSION[QT]['sys_stat_members']);
    Unset($_SESSION[QT]['sys_stat_states']);
  }

  // --------

  function Logout()
  {
    // Remove session info (and cookie)
    $_SESSION=array();
    session_destroy();
    if ( isset($_COOKIE[QT.'_cookname']) ) setcookie(QT.'_cookname', '', time()+60*60*24*100, '/');
    if ( isset($_COOKIE[QT.'_cookpass']) ) setcookie(QT.'_cookpass', '', time()+60*60*24*100, '/');
    if ( isset($_COOKIE[QT.'_cooklang']) ) setcookie(QT.'_cooklang', '', time()+60*60*24*100, '/');
  }

  // --------
  // @$strTitle: title of the message box. When null or empty string, uses the page name ($this->selfname)
  //  when $strTitle=="0", it makes a direct exit
  // @$strMessage: message body.
  // @$strSkin: the skin folder
  // @$intTime: the pause (in second) before redirecting to the exit page. Use 0 to NOT redirect.
  // @$strWidth: css width parameter ("300px" or "90%")
  // @$strIdHead: css id of the header
  // @$strIdMain: css id of the body

  public function EndMessage($strTitle,$strMessage='Access denied',$strSkin='admin',$intTime=0,$strWidth='300px',$strTitleId='msgboxtitle',$strBodyId='msgbox')
  {
    global $oHtml;
    if ( $strTitle=='0' ) $oHtml->Redirect($this->exiturl,$this->exitname);
    if ( empty($strTitle) ) $strTitle = $this->selfname;
    if ( empty($strSkin) ) $strSkin='admin';

    $oHtml->links   = array();
    $oHtml->links[] = '<link rel="shortcut icon" href="'.$strSkin.'/qnm_icon.ico" />';
    $oHtml->links[] = '<link rel="stylesheet" type="text/css" href="'.$strSkin.'/qnm_main.css" />';
    $oHtml->links[] = '<link rel="stylesheet" type="text/css" href="bin/css/qnm_print.css" media="print" />';
    echo $oHtml->Head();
    echo $oHtml->Body();
    HtmlPageCtrl(START);
    $oHtml->Msgbox($strTitle,array('style'=>'width:'.$strWidth),array('id'=>$strTitleId),array('id'=>$strBodyId));
    echo $strMessage,'
    <p><a id="exiturl" href="',Href($this->exiturl),'">',$this->exitname,'</a></p>';
    $oHtml->Msgbox(END);
    HtmlPageCtrl(END);

    if ( $intTime>0 )
    {
    echo '
    <script type="text/javascript">
    <!--
    setTimeout(\'window.location=document.getElementById("exiturl").href\',',($intTime*1000),');
    //-->
    </script>
    ';
    }
    echo $oHtml->End();

    exit;
  }

  // --------

  static function SysCount($strObject='items',$strWhere='')
  {
    global $oDB;
    switch($strObject)
    {
    case 'members': $oDB->Query('SELECT count(*) as countid FROM '.TABUSER.' WHERE id>0'.$strWhere); $row = $oDB->Getrow(); break;
    case 'states':
        $oDB->Query('SELECT max(id) as countid FROM '.TABUSER.' WHERE id>=0'.$strWhere);
        $row = $oDB->Getrow();
        $id = (int)$row['countid'];
        $oDB->Query('SELECT name,stats FROM '.TABUSER.' WHERE id='.$row['countid'] );
        $row = $oDB->Getrow();
      $arr = QTexplode($row['stats']);
      $arr['newuserid'] = $id;
      $arr['newusername'] = $row['name'];
      return $arr;
      break;
    default: return 0;
    }
    return (int)$row['countid'];
  }

// --------

}