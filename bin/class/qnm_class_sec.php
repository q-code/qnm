<?php

class cSection extends aQTcontainer implements IMultifield,IDatabase
{

public $domname='';     // Domain name
public $idtitle='untitled';// Section idtitle
public $name='untitled';// Section name (translation)
public $descr='';
public $notify=1;       // Notify: 0=disable, 1=enabled
public $modid=0;        // Moderator id
public $modname='Admin';// Moderator name
public $numfield='%03s';// Format of the ref number: 'N' means no ref number
public $titlefield=1;   // Topic title: 0=None, 1=Optional, 2=Mandatory
public $wisheddate=0;   // Topic wisheddate: 0=None, 1=Optional, 2=Mandatory
public $wisheddflt=0;   // Topic wisheddate [2] default: 0=None, 1=Today, 2=Day+1, 3=Day+2
public $notifycc=0;     // Topic alternate notify: 0=None, 1=Optional, 2=Mandatory
public $prefix='a';     // Prefix icon from the serie 'a'
public $options='';     // Several options

// stats
public $types_e=array(); // list of types
public $types_n=array(); // list of types

// options
public $o_order='id';   // Default sort order
public $o_last='notes'; // Last column in the topic list: '0' means no last column
public $o_logo='';      // Ovewrite icon with an image-logo named s_x.gif/.jpeg/.jpg/.png (where x is the section id)

// computed values
public $lastpost=array();

// --------

function __construct($aSection=null,$bLast=false)
{
  if ( isset($aSection) )
  {
    if ( is_int($aSection) )
    {
      if ( $aSection<0 ) die('No section '.$aSection);
      global $oDB;
      $oDB->Query( 'SELECT * FROM '.TABSECTION.' WHERE uid='.$aSection );
      $row = $oDB->Getrow();
      if ( $row===False ) die('No section '.$aSection);
      $this->MakeFromArray($row);
    }
    elseif ( is_array($aSection) )
    {
      $this->MakeFromArray($aSection);
    }
    else
    {
      var_dump($aSection);
      die('Invalid constructor parameter #1 for the class cSection');
    }
  }

  // Read options

  $this->MRead('options',true,'o_');
  $this->MRead('stats',true);

  // Find last post

  if ( $bLast ) $this->GetSectionLastPost($bLast); // $bLast can be an integer

  // Default sort order

  if ( empty($this->o_order) ) $this->o_order='id';
  if ( $this->o_last=='wisheddate' && $this->wisheddate==0 ) $this->o_last='notes';
  if ( $this->o_last=='notifiedname' && $this->notifycc==0 ) $this->o_last='notes';
  if ( $this->o_last=='no' || empty($this->o_last) ) $this->o_last='notes';

}

// --------

private function MakeFromArray($aSection)
{
  foreach($aSection as $strKey=>$oValue) {
  switch ($strKey) {
    case 'uid':          $this->uid       = intval($oValue); break;
    case 'pid':          $this->pid       = intval($oValue); break;
    case 'title':        $this->idtitle   = $oValue; break;
    case 'type':         $this->type      = $oValue; break;
    case 'status':       $this->status    = intval($oValue); break;
    case 'notify':       $this->notify    = intval($oValue); break;
    case 'moderator':    $this->modid     = intval($oValue); break;
    case 'moderatorname':$this->modname   = $oValue; break;
    case 'stats':        $this->stats     = $oValue; break;
    case 'options':      $this->options   = $oValue; break;
    case 'numfield':     $this->numfield  = $oValue; break;
    case 'titlefield':   $this->titlefield= intval($oValue); break;
    case 'wisheddate':
      $this->wisheddate = intval($oValue);
      if ( $this->wisheddate>2 ) { $this->wisheddflt=$this->wisheddate-2; $this->wisheddate=2;  }
    break;
    case 'alternate':    $this->notifycc  = intval($oValue); break;
    case 'prefix':       $this->prefix    = $oValue; break;
  }}
  $this->name = ObjTrans('sec','s'.$this->uid,$this->idtitle);
  $this->descr = ObjTrans('secdesc','s'.$this->uid,false);
  $this->domname= ObjTrans('domain','d'.$this->pid,'(domain '.$this->pid.')');
}

// --------

function GetSectionLastPost($id=false)
{
  // $id can be an interger to return a specific id as last post

  // Initialize & check
  $this->lastpost = array();
  if ( $this->uid<0 ) return null;
  if ( $this->items==0 ) return null;

  // Query
  global $oDB;
  $oDB->Query( 'SELECT id,pclass,pid,issuedate,userid,username,textmsg FROM '.TABPOST.' WHERE id='.(is_integer($id) && $id>=0 ? $id : '(SELECT MAX(id) as maxid FROM '.TABPOST.' WHERE section='.$this->uid.')') );
  if ( $row=$oDB->Getrow() )
  {
  $this->lastpost = array(
    'id'=>(int)$row['id'],
    'nid'=>$row['pclass'].'.'.$row['pid'],
    'issuedate'=>$row['issuedate'],
    'userid'=>(int)$row['userid'],
    'username'=>$row['username'],
    'preview'=>str_replace('"',"'",(strlen($row['textmsg'])<51 ? $row['textmsg'] : substr($row['textmsg'],0,49).'...')));
  }
  return (int)$row['id'];
}

// --------

function GetLogo()
{
  if ( !empty($this->o_logo) )
  {
  if ( file_exists('upload/section/'.$this->o_logo) ) return 'upload/section/'.$this->o_logo;
  }
  return $_SESSION[QT]['skin_dir'].'/ico_section_'.$this->type.'_'.$this->status.'.gif';
}

// --------

public function GetFilter()
{
  $arr = array();
  if ( !empty($this->options) )
  {
    $strFilter = $this->MGet('options','filter');
    if ( strlen($strFilter)>0 )
    {
      if ( $strFilter=='0' ) { return $this->ReadTypes(false,5,'type ASC');  }
      if ( $strFilter=='1' ) { return $this->ReadTypes(false,5,'countid DESC, type ASC'); }
      if ( strlen($strFilter)>2 )
      {
        $arr = explode(',',$strFilter);
        $i = count($arr);
        // check value exists
        if ( empty($this->types_e) ) $this->ReadTypes();
        $arr = array_intersect($arr,$this->types_e);
        if ( count($arr)<5 ) $arr = array_merge( $arr, array_slice($this->types_e,0,10) );
        array_unique($arr);
        $arr = array_slice($arr,0,$i);
        // urlencode value as key
        $arrSrc = $arr;
        $arr = array();
        foreach($arrSrc as $value) $arr[urlencode($value)]=$value;
      }
    }
  }
  return $arr;
}

// --------

public static function GetTagsUsed($intS=-1,$strT='',$intMax=50)
{
  // Check

  if ( !is_int($intS) ) die('cSection->GetTagsUsed: Argument #1 must be integer');
  if ( !is_string($strT) ) die('cSection->GetTagsUsed: Argument #2 must be string');

  // Process

  $arrTags = array();
  global $oDB;
  $oDB->Query( 'SELECT DISTINCT tags FROM '.TABNE.' WHERE uid>0'.($intS>=0 ? ' AND section='.$intS : '').(empty($strT) ? '' : ' AND UPPER(type) LIKE "%'.$strT.'%"') );
  $i=0;
  while($row=$oDB->Getrow()) {
  if ( !empty($row['tags']) ) {

    $arr = explode(';',$row['tags']);
    foreach($arr as $str)
    {
      if ( !empty($str) ) {
      if ( !in_array($str,$arrTags) ) {
        $arrTags[$str] = $str;
        $i++;
        if ( $i>$intMax ) break;
      }}
    }
    if ( $i>$intMax ) break;

  }}
  if ( count($arrTags)>2 ) asort($arrTags);
  return $arrTags;
}

// --------

public function ReadTypes($bAllSections=false,$intMax=50,$strOrder='type ASC')
{
  global $oDB;
  $oDB->Query( 'SELECT DISTINCT type, count(uid) as countid FROM '.TABNE.' WHERE uid>0'.($bAllSections ? '' : ' AND section='.$this->uid).' GROUP BY type ORDER BY '.$strOrder );
  $i=0;
  $this->types_e=array(); // list of types
  $this->types_n=array(); // list of types
  while($row=$oDB->Getrow())
  {
    $this->types_e[urlencode($row['type'])]=$row['type'];
    $this->types_n[urlencode($row['type'])]=$row['countid'];
    $i++;
    if ( $i>=$intMax ) break;
  }
  return $this->types_e;
}

// --------

public function UpdateStats($arrValues=array())
{
  if ( $this->uid<0 ) die('UpdateSectionStats: Wrong id');

  // Process (provided values are not recomputed)

  if ( !isset($arrValues['items']) ) $arrValues['items'] = cSection::CountItems($this->uid,'items');
  if ( !isset($arrValues['notes']) ) $arrValues['notes'] = cSection::CountItems($this->uid,'notes');
  if ( !isset($arrValues['tags']) ) $arrValues['tags'] = cSection::CountItems($this->uid,'tags');
  if ( !isset($arrValues['itemsZ']) ) $arrValues['itemsZ'] = ($arrValues['items']<1 ? 0 : cSection::CountItems($this->uid,'itemsZ'));
  if ( !isset($arrValues['notesA']) ) $arrValues['notesA'] = ($arrValues['notes']<1 ? 0 : cSection::CountItems($this->uid,'notesA'));
  if ( !isset($arrValues['notesZ']) ) $arrValues['notesZ'] = ($arrValues['notes']<1 ? 0 : cSection::CountItems($this->uid,'notesZ'));

  $this->stats = QTimplode($arrValues);
  $this->UpdateField('stats',$this->stats);
}

// --------
// aQTcontainer implementations
// --------

public function Create($title,$parentid)
{
  QTargs( 'cSection->Create',array($title,$parentid),array('str','int') );
  if ( empty($title) ) die('cSection->Create: Argument #1 must be a string');
  global $oDB;
  $uid = $oDB->Nextid(TABSECTION,'uid');
  $oDB->Query( 'INSERT INTO '.TABSECTION.' (pid,uid,type,status,notify,titleorder,moderator,titlefield,wisheddate,alternate,title,stats,options,moderatorname,numfield,prefix) VALUES ('.$parentid.','.$uid.',0,0,1,0,0,1,0,0,"'.addslashes($title).'","","order=0;last=notes;logo=0","Admin","%03s","a")' );
  // Impact on globals
  if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
  return $uid;
}

public static function Drop($id)
{
  if ( $id<1 ) die('cSection->Drop: Cannot delete section 0');
  cSection::DeleteItems($id);
  global $oDB,$oVIP;
  $oDB->Query( 'DELETE FROM '.TABSECTION.' WHERE uid='.$id );
  cLang::Delete(array('sec','secdesc'),'s'.$id);
  // Impact on globals
  if ( isset($_SESSION[QT]['sys_sections']) ) Unset($_SESSION[QT]['sys_sections']);
}

public static function MoveItems($id,$dest,$item='',$status='',$strYear='')
{
  if ( !is_int($id) ) die('cSection->MoveItems: Argument #1 must be integer'); // section
  if ( !is_int($dest) ) die('cSection->MoveItems: Argument #2 must be integer'); // destination section
  if ( $id<0 ) die('cSection->MoveItems: Wrong argument #1 (id<0)');
  if ( $dest<0 ) die('cSection->MoveItems: Wrong argument #2 (d<1)');
  if ( $id==$dest ) die('cSection->MoveItems: Wrong argument, source=destination');
  if ( !is_string($strYear) ) $strYear = intval($strYear); // $strYear can be a integer or "old"
  if ( strlen($strYear)>4 ) die('cSection->MoveItems: Argument #2 must be a string');

  global $oDB;

  // Move only one item (nid)

  if ( $item!=='' )
  {
    $class = GetNclass($item); if  ($class==='c') return; // not applicable for connectors
    $uid = GetUid($item);
    $oDB->Query( 'UPDATE '.TABNE.' SET section='.$dest.'" WHERE section='.$id.' AND class="'.$class.'" AND uid='.$uid );
    $oDB->Query( 'UPDATE '.TABPOST.' SET section='.$dest.' WHERE section='.$id.' AND pclass="'.$class.'" AND pid='.$uid );
    return;
  }

  // Move several items (status and/or year)

  $strWhere = ($status==='' ? '' : ' AND status='.$status).(empty($strYear) ? '' : ' AND '.SqlDateCondition($strYear));
  $oDB->Query( 'UPDATE '.TABPOST.' SET section='.$dest.' WHERE pid IN (SELECT uid FROM '.TABNE.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'UPDATE '.TABNE.' SET section='.$dest.' WHERE section='.$id.$strWhere );

  // Impact on globals

  if ( isset($_SESSION[QT]['sys_stat_items']) ) Unset($_SESSION[QT]['sys_stat_items']);
  if ( isset($_SESSION[QT]['sys_stat_itemsZ']) ) Unset($_SESSION[QT]['sys_stat_itemsZ']);
  if ( isset($_SESSION[QT]['sys_stat_notes']) ) Unset($_SESSION[QT]['sys_stat_notes']);
  if ( isset($_SESSION[QT]['sys_stat_notesA']) ) Unset($_SESSION[QT]['sys_stat_notesA']);
}

public static function DeleteItems($id,$status='',$strYear='',$bPhysical=true)
{
  if ( !is_int($id) ) die('cSection->DeleteItems: Argument #1 must be integer'); // section
  if ( $id<0 ) die('cSection->DeleteItems: Wrong argument #1 (id<0)');
  if ( !is_string($status) ) die('cSection->DeleteItems: Argument #2 must be integer');
  if ( !is_string($strYear) ) $strYear = intval($strYear); // $strYear can be a integer or "old"
  if ( strlen($strYear)>4 ) die('cSection->DeleteItems: Argument #3 must be a string');

  global $oDB;
  $strWhere = ($status==='' ? '' : ' AND status='.$status).(empty($strYear) ? '' : ' AND '.SqlDateCondition($strYear));

  // Delete items

  if ( $bPhysical )
  {
  $oDB->Query( 'DELETE FROM '.TABNL.' WHERE lidclass<>"c" AND lid IN (SELECT uid FROM '.TABNE.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'DELETE FROM '.TABNL.' WHERE lid2class<>"c" AND lid2 IN (SELECT uid FROM '.TABNE.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'DELETE FROM '.TABNL.' WHERE lidclass="c" AND lid IN (SELECT uid FROM '.TABNC.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'DELETE FROM '.TABNL.' WHERE lid2class="c" AND lid2 IN (SELECT uid FROM '.TABNC.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'DELETE FROM '.TABDOC.' WHERE id IN (SELECT uid FROM '.TABNE.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'DELETE FROM '.TABPOST.' WHERE pid IN (SELECT uid FROM '.TABNE.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'DELETE FROM '.TABNE.' WHERE section='.$id.$strWhere );
  $oDB->Query( 'DELETE FROM '.TABNC.' WHERE section='.$id.$strWhere );
  }
  else
  {
  $oDB->Query( 'UPDATE '.TABNL.' SET lstatus=-1 WHERE lidclass<>"c" AND lid IN (SELECT uid FROM '.TABNE.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'UPDATE '.TABNL.' SET lstatus=-1 WHERE lid2class<>"c" AND lid2 IN (SELECT uid FROM '.TABNE.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'UPDATE '.TABNL.' SET lstatus=-1 WHERE lidclass="c" AND lid IN (SELECT uid FROM '.TABNC.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'UPDATE '.TABNL.' SET lstatus=-1 WHERE lid2class="c" AND lid2 IN (SELECT uid FROM '.TABNC.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'UPDATE '.TABDOC.' SET status=-1 WHERE id IN (SELECT uid FROM '.TABNE.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'UPDATE '.TABPOST.' SET status=-1 WHERE pid IN (SELECT uid FROM '.TABNE.' WHERE section='.$id.$strWhere.')' );
  $oDB->Query( 'UPDATE '.TABNE.' SET status=-1 WHERE section='.$id.$strWhere );
  $oDB->Query( 'UPDATE '.TABNC.' SET status=-1 WHERE section='.$id.$strWhere );
  }
  // Impact on globals
  if ( isset($_SESSION[QT]['sys_stat_items']) ) Unset($_SESSION[QT]['sys_stat_items']);
  if ( isset($_SESSION[QT]['sys_stat_itemsZ']) ) Unset($_SESSION[QT]['sys_stat_itemsZ']);
  if ( isset($_SESSION[QT]['sys_stat_notes']) ) Unset($_SESSION[QT]['sys_stat_notes']);
  if ( isset($_SESSION[QT]['sys_stat_notesA']) ) Unset($_SESSION[QT]['sys_stat_notesA']);
}

public static function CountItems($id,$q,$strWhere='')
{
  if ( $id==='*' ) { $id=' AND section>=0'; } else { $id=' AND section='.$id; }
  if ( !is_string($q) ) die('cSection::CountItems: Wrong argument (q)');
  if ( !is_string($strWhere) ) die('cSection::CountItems: Wrong argument (where)');

  global $oDB;

  // Process

  switch($q)
  {
  case 'items': $oDB->Query( 'SELECT count(*) as countid FROM '.TABNE.' WHERE uid>0'.$id.$strWhere ); break;
  case 'itemsZ': $oDB->Query( 'SELECT count(*) as countid FROM '.TABNE.' WHERE uid>0 AND status=0'.$id.$strWhere ); break;
  case 'itemsX': $oDB->Query( 'SELECT count(*) as countid FROM '.TABNE.' WHERE uid>0 AND status=-1'.$id.$strWhere ); break;
  case 'conns': $oDB->Query( 'SELECT count(*) as countid FROM '.TABNC.' WHERE uid>0'.$id.$strWhere ); break;
  case 'connsZ': $oDB->Query( 'SELECT count(*) as countid FROM '.TABNC.' WHERE uid>0 AND status=0'.$id.$strWhere ); break;
  case 'notes': $oDB->Query( 'SELECT count(*) as countid FROM '.TABPOST.' WHERE status>=0'.$id.$strWhere ); break;
  case 'notesA': $oDB->Query( 'SELECT count(*) as countid FROM '.TABPOST.' WHERE status=1'.$id.$strWhere ); break;
  case 'notesZ': $oDB->Query( 'SELECT count(*) as countid FROM '.TABPOST.' WHERE status=0'.$id.$strWhere ); break;
  case 'tags': $oDB->Query( 'SELECT count(*) as countid FROM '.TABNE.' WHERE uid>0 AND tags<>""'.$id.$strWhere ); break;
  case 'docs': $oDB->Query( 'SELECT count(*) as countid FROM '.TABDOC.' d INNER JOIN '.TABNE.' e ON e.uid=d.id WHERE d.id>0'.str_replace('section','e.section',$id).$strWhere ); break;
  default: die('cSection::CountItems: Wrong argument (q) '.$q);
  }
  $row = $oDB->Getrow();
  return intval($row['countid']);
}

public function StatsGet($key,$na='0')
{
  return (int)$this->MGet('stats',$key,$na);
}

// --------
// IMultifield, IDatabase implementations
// --------

public function MRead($prop,$bAssign=true,$prefix='')
{
  // @prop    Reads the property and split it in an array
  // @bAssign If array keys are properties, the values are assigned to these properties
  // @prefix  Destination properties can have a prefix
  if ( !isset($this->$prop) ) die('cSection::MRead: Undefined propertie ['.$prop.']');
  $arr = QTexplode($this->$prop);
  if ( $bAssign )
  {
    foreach($arr as $key=>$value)
    {
    $key = $prefix.$key;
    if ( isset($this->$key) ) $this->$key=$value;
    }
  }
  return $arr;
}

public function MGet($prop,$key,$na='')
{
  // Returns an option value (or $strNA if key not found)
  if ( !isset($this->$prop) ) die('cSection::MGet: Undefined propertie ['.$prop.']');
  QTargs('cSection->MGet',array($key,$na));
  if ( empty($key) ) die('cSection::MGet: Missing key');

  $arr = QTexplode($this->$prop);
  if ( isset($arr[$key]) ) return $arr[$key];
  return $na;
}

public function MChange($prop,$key,$value='')
{
  QTargs('cSection->MChange',array($prop,$key));
  if ( !isset($this->$prop) ) die('cSection::MChange: Undefined propertie ['.$prop.']');
  if ( empty($key) ) die('cSection::MChange: Missing key');

  $arr = QTarradd(QTexplode($this->$prop),$key,$value);
  $this->$prop = QTimplode($arr);
  $this->UpdateField($prop,$this->$prop);
  return $arr;
}

public static function GetTable() { return TABSECTION; }
public static function GetFields($type='')
{
  switch($type)
  {
    case 'int': return array('id','pid','status','titleorder','moderator'); break;
    case 'str': return array('type','notify','title','moderatorname','stats','options','numfield','titlefield','wisheddate','alternate','prefix'); break;
  }
  return array('id','section','pid','userid','status','pclass','textmsg','username','issuedate','attach');
}

public static function GetSqlValue($strField,$strValue)
{
  if ( in_array($strField,cSection::GetFields('int')) ) return ($strValue==='' || is_null($strValue) ? 'NULL' : $strValue);
  if ( empty($strValue) ) if ( in_array($strField,cSection::GetFields('str')) ) return '"0"';
  return '"'.$strValue.'"';
}

public function UpdateField($strField,$strValue)
{
  if ( !is_string($strField) ) die('cSection::UpdateField: Invalid field ['.$strField.']');
  global $oDB;
  return $oDB->Query( 'UPDATE '.cSection::GetTable().' SET '.$strField.'='.$this->GetSqlValue($strField,$strValue).' WHERE uid='.$this->uid );
}

public function Insert() {}

// --------

}
