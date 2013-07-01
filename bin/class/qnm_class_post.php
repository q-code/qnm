<?php

// QNM 1.0 build:20120322

class cPost implements IDatabase
{

// --------

public $id;
public $pid;
public $section;
public $textmsg = '';
public $status = 1; // 0 closed, 1=Inprocess
public $issuedate = '0';
public $userid;
public $username;
public $userrole;
public $userloca;
public $useravat;
public $usersign;

// --------

function cPost($aPost=null,$intNum=null)
{
  if ( isset($aPost) )
  {
    if ( is_int($aPost) )
    {
      if ( $aPost<0 ) Die('cPost constructor: No post '.$aPost);
      global $oDB;
      $oDB->Query('SELECT p.*,u.role,u.location,u.photo,u.signature FROM '.TABPOST.' p LEFT JOIN '.TABUSER.' u ON p.userid=u.id WHERE p.id='.$aPost);
      $row = $oDB->Getrow();
      $this->MakeFromArray($row);
    }
    elseif ( is_array($aPost) )
    {
      $this->MakeFromArray($aPost);
    }
    else
    {
      Die('cPost constructor: Invalid parameter#1');
    }
  }
  if ( isset($intNum) ) $this->num = intval($intNum);
  if ( $_SESSION[QT]['viewmode']=='C' ) $this->textmsg = QTcompact($this->textmsg,0);
}

// --------

function MakeFromArray($aPost)
{
  if ( !is_array($aPost) ) Die('cPost::MakeFromArray: aPost is not an array');
  foreach($aPost as $strKey=>$oValue) {
  switch ($strKey) {
    case 'id':       $this->id = intval($oValue); break;
    case 'pid':      $this->pid = intval($oValue); break;
    case 'section':  $this->section = intval($oValue); break;
    case 'status':   $this->status = intval($oValue); break;
    case 'textmsg':  $this->textmsg = $oValue; break;
    case 'issuedate':$this->issuedate = $oValue; break;
    case 'userid':   $this->userid   = intval($oValue); break;
    case 'username': $this->username = $oValue; break;
    case 'role':     $this->userrole = $oValue; break;
    case 'location': $this->userloca = $oValue; break;
    case 'photo':    $this->useravat = $oValue; break;
    case 'signature':$this->usersign = $oValue; break;
  }}
}

// --------

function CanEdit()
{
  // By default, Staff can edit post of other staff (even Admin post)
  global $oVIP;
  if ( !$oVIP->user->auth ) return false;
  if ( $this->userid==$oVIP->user->id || $oVIP->user->role=='A' ) return true;
  if ( $oVIP->user->role=='M' )
  {
    if ( $this->user->role=='A' && !QNM_STAFFEDITADMIN ) return false;
    if ( $this->user->role=='M' && !QNM_STAFFEDITSTAFF ) return false;
    return true;
  }
  return false;
}

public static function Format(&$str)
{
  $str = QTconv($str,'3',QNM_CONVERT_AMP,QNM_DROP_TAGS); // format the message (convert quote and doublequote
}

public static function CheckInput(&$str,$bCheckDelay=false)
{
  // check delay
  if ( $bCheckDelay && !empty($_SESSION['qnm_usr_lastpost']) )
  {
    $intMax = ( isset($_SESSION[QT]['posts_delay']) ? (int)$_SESSION[QT]['posts_delay'] : 5 );
    if ( $_SESSION['qnm_usr_lastpost']+$intMax >= time() ) return Error(30).' delay between posts under the limit. Please wait.';
  }
  // check size
  $str = QTconv($str,'3',QNM_CONVERT_AMP,QNM_DROP_TAGS); // format the message (convert quote and doublequote
  if ( $str==='' || $str===' ' ) return Error(22);
  if ( strlen($str)>(int)$_SESSION[QT]['chars_per_post'] )
  {
  $str = substr($str,0,(int)$_SESSION[QT]['chars_per_post']);
  return Error(31).' (max. '.$_SESSION[QT]['chars_per_post'].')';
  }
  if ( substr_count($str,"\n")>(int)$_SESSION[QT]['lines_per_post'] )
  {
  if ( strlen($str)>999 ) $str = substr($str, 0,(int)$_SESSION[QT]['chars_per_post']);
  return Error(31).' (max. '.$_SESSION[QT]['lines_per_post'].' lines)';
  }
  return;
}

// --------
// IDatabase implementations
// --------

public static function GetTable() { return TABPOST; }

public static function GetFields($type='')
{
  switch($type)
  {
  case 'int': return array('id','section','pid','userid','status'); break;
  case 'str': return array('pclass','textmsg','username','issuedate'); break;
  }
  return array('id','section','pid','userid','status','pclass','textmsg','username','issuedate');
}

public function UpdateField($arrFld,$arrVal)
{
  if ( !is_array($arrFld) ) $arrFld = array($arrFld);
  if ( !is_array($arrVal) ) $arrVal = array($arrVal);
  if ( empty($arrFld) || empty($arrVal) ) die('cPost::UpdateField: Invalid field');  
  if ( count($arrFld)!=count($arrVal) ) die('cPost::UpdateField: Invalid number of values');
  global $oDB;
  $arrSet = array();
  foreach($arrFld as $i=>$strFld) { $arrSet[] =$strFld.'='.$this->GetSqlValue($strFld,$arrVal[$i]); }
  return $oDB->Query( 'UPDATE '.cPost::GetTable().' SET '.implode(',',$arrSet).' WHERE id='.$this->id );
}

public static function GetSqlValue($strField,$strValue)
{
  if ( in_array($strField,cPost::GetFields('int')) ) return ($strValue==='' || is_null($strValue) ? 'NULL' : $strValue);
  if ( empty($strValue) ) if ( in_array($strField,cPost::GetFields('str')) ) return '"0"';
  return '"'.$strValue.'"';
}

public function Insert($bElementStat=false,$bUserStat=false,$bCheckUserPostsToday=true)
{
  global $oDB,$oVIP;

  // Insert

  $oDB->Query( 'INSERT INTO '.TABPOST.' (id,section,pid,status,userid,username,issuedate,textmsg) VALUES ( '.$this->id.','.$this->section.','.$this->pid.','.$this->status.','.$this->userid.',"'.addslashes($this->username).'","'.$this->issuedate.'","'.addslashes(QTconv($this->text,'3',QNM_CONVERT_AMP,false)).'")' );

  // Update Element's replies and lastpost (inserting a post does NOT change the modifdate of the element!)

  if ( $bElementStat )
  {
  $oDB->Query('UPDATE '.TABNE.' SET posts=posts+1 WHERE id='.$this->pid);
  }

  // Update system stats
  
  $oVIP->stats->notes++;

  // Lastpost delay control

  $_SESSION['qnm_usr_lastpost'] = time();

  // Number of posts today control

  if ( isset($_SESSION['qnm_usr_posts_today']) && $bCheckUserPostsToday )
  {
    if ( $this->type=='P' || $this->type=='R' ) $_SESSION['qnm_usr_posts_today']++;
  }

  // User stat

  if ( $bUserStat )
  {
  $oDB->Query('UPDATE '.TABUSER.' SET lastdate="'.Date('Ymd His').'", numpost=numpost+1, ip="'.$oDB->ip.'" WHERE id='.$this->userid);
  $_SESSION[QT.'_usr_posts']++;
  }
}

// --------

public function Dump($bHref=true,$classIssue='',$classText='')
{
  $str = ( empty($this->issuedate) ? '' : QTdatestr($this->issuedate,'$','$',true) );
  if ( $bHref )
  {
  $str .= ' by <a class="small" href="'.Href('qnm_user.php').'?id='.$this->userid.'">'.$this->username.'</a>';
  }
  else
  {
  $str .= ' by '.$this->username;
  } 
  return $this->GetIcon().' '.(empty($classIssue) ? $str : '<span class="'.$classIssue.'">'.$str.'</span>').' &middot; '.(empty($classText) ? $this->textmsg : '<span class="'.$classText.'">'.$this->textmsg.'</span>').'<br/>';
}

// --------

public function GetIcon($strHref='',$strAlt='',$strTitle='',$strClass='i_note',$strId='')
{
  return cPost::IconMaker($this->status,$strAlt,$strTitle,$strClass,'',$strHref,$strId);
}

static function IconMaker($intStatus=1,$strAlt='',$strTitle='',$strClass='i_note',$strStyle='',$strHref='',$strId='')
{
  return AsImg((empty($_SESSION[QT]['skin_dir']) ? 'skin/default': $_SESSION[QT]['skin_dir']).'/ico_note_'.$intStatus.'.gif',(string)$intStatus,(empty($strTitle) ? '' : $strTitle),$strClass,$strStyle,$strHref,$strId);
}

// --------

function SetFromPost($bNew=true)
{
  $error='';
  global $oVIP,$strBehalf;

  // Identify the user (can be onbehalf)
    
  $this->modifuser = $oVIP->user->id;
  $this->modifname = $oVIP->user->name;
  if ( isset($_POST['behalf']) )
  {
    $strBehalf = trim($_POST['behalf']); if ( get_magic_quotes_gpc() ) $strBehalf = stripslashes($strBehalf);
    if ( !is_null($strBehalf) && $strBehalf!=='' )
    {
      // Find behalf id
      $strBehalf = htmlspecialchars($strBehalf,ENT_QUOTES);
      $intBehalf = current(array_keys(GetUsers('name',$strBehalf) )); // can be FALSE when not found
      if ( is_int($intBehalf) ) { $this->modifuser = $intBehalf; $this->modifname = $strBehalf; } else { $error=L('Send_on_behalf').' '.Error(1); }
    }
  }

  // Identify creator as being the user. When editing existing message ($bNew=false) then autor remains unchanged

  if ( $bNew )
  {
  $this->userid = $this->modifuser;
  $this->username = $this->modifname;
  }

  // Read message values
  
  if ( isset($_POST['icon']) ) $this->icon = $_POST['icon'];
  if ( isset($_POST['title']) ) $this->title = QTunbbc(trim((get_magic_quotes_gpc() ? stripslashes($_POST['title']) : $_POST['title'])));
  if ( isset($_POST['text']) ) $this->text = trim((get_magic_quotes_gpc() ? stripslashes($_POST['text']) : $_POST['text']));
  if ( isset($_POST['wisheddate']) )
  {
    if ( !empty($_POST['wisheddate']) )
    {
    $str = QTdatestr(trim($_POST['wisheddate']),'Ymd','');
    if ( !is_string($str) ) $error = L('Wisheddate').' '.Error(1);
    if ( substr($str,0,6)=='Cannot' ) $error = L('Wisheddate').' '.Error(1);
    if ( substr($str,0,4)=='1970' ) $error = L('Wisheddate').' '.Error(1);
    if ( empty($error) ) $this->wisheddate = $str;
    }
  }
  if ( isset($_POST['oldattach']) ) { $this->attach = $_POST['oldattach']; }
  
  return $error;
}

// --------

}