<?php

/**
* qt_lib_txt.php
* ------------
* version: 5.0 build:20130410
* This is a library of public functions
* ------------
* QTargs
* QTasTag
* QTdatestr
* QTbbc
* QTconv
* QTdatestr
* QTislogin
* QTispassword
* QTismail
* QTisbetween
* QTisvaliddate
* QTarradd
* QTexplode($str,$sep=';',$function='')
*   QTexplodeUri($str=null,$urldecode=false)
*   QTexplodeValue($str,$key='',$sep=';',$function='')
*   QTexplodeUriValue($str=null,$key='',$urldecode=false,$function='')
* QTimplode($arr,$sep=';',$function='')
*   QTimplodeUri($arr,$sep='&amp;',$urlencode=false)
* QTunbbc
* QTcompact
* QThttpvar
*/

// This function allow cheching argument types: The value in $arrArgs must be of type specified in $arrTypes
// Application stops when the value is not of the specified type.
// Note 1: The type 'empty' means that the application stops if the value IS empty.
// Note 2: When $arrTypes is one type, this type is converted to a list of types

function QTargs($str='Error',$arrArgs,$arrTypes='str')
{
  if ( !is_string($str) ) die('QTargs: Argument #1 must be a string');
  if ( !is_array($arrArgs) ) die('QTargs: Argument #2 must be an array');
  // last argument can be one string meaning: an array of n time this string is created
  if ( is_string($arrTypes) ) { $s=$arrTypes; $arrTypes=array(); foreach($arrArgs as $a) $arrTypes[]=$s; }
  if ( !is_array($arrTypes) ) die('QTargs: Argument #3 must be an array');
  if ( count($arrTypes)!=count($arrArgs) ) die('QTargs: Argument #2 and #3 are not the same size');

  // Process

  for($i=0;$i<count($arrArgs);$i++) {
  switch($arrTypes[$i]) {
  case 'str': if ( !is_string($arrArgs[$i]) ) die($str.': Argument #'.$i.' must be a string'); break;
  case 'int': if ( !is_int($arrArgs[$i]) ) die($str.': Argument #'.$i.' must be an int'); break;
  case 'arr': if ( !is_array($arrArgs[$i]) ) die($str.': Argument #'.$i.' must be an array'); break;
  case 'flo': if ( !is_float($arrArgs[$i]) ) die($str.': Argument #'.$i.' must be a float'); break;
  case 'boo': if ( !is_bool($arrArgs[$i]) ) die($str.': Argument #'.$i.' must be a boolean'); break;
  case 'empty': if ( empty($arrArgs[$i]) ) die($str.': Argument #'.$i.' is empty'); break;
  }}
}

// arrAttr can includes (S means selected & C current):
// format,name,endline,current,class,classS,classC,style,styleS,styleC

function QTasHidden($arr,$valSelected='',$arrAttr=array()) { return QTasTag($arr,$valSelected,$arrAttr,'hidden'); }
function QTasCheckbox($arr,$valSelected='',$arrAttr=array(),$arrDisabled=array()) { return QTasTag($arr,$valSelected,$arrAttr,'checkbox',$arrDisabled); }
function QTasSpan($arr,$valSelected='',$arrAttr=array()) { return QTasTag($arr,$valSelected,$arrAttr,'span'); }
function QTasTag($arr,$valSelected='',$arrAttr=array(),$strTag='option',$arrDisabled=array())
{
  QTargs( 'QTasTag',array($arr,$arrAttr,$strTag),array('arr','arr','str') ); // valSelected can be str or int

  $strReturn = '';
  foreach($arr as $strKey=>$strValue)
  {
    // format the value
    if ( is_array($strValue) ) $strValue = reset($strValue);
    if ( isset($arrAttr['format']) ) $strValue = sprintf($arrAttr['format'],$strValue);

    $strName='';
      if ( isset($arrAttr['name']) ) $strName=$arrAttr['name'];
    $strClass='';
      if ( isset($arrAttr['class']) ) $strClass=$arrAttr['class'];
      if ( isset($arrAttr['classS']) ) { if ( strlen($valSelected)>0 && $valSelected==$strKey ) $strClass=$arrAttr['classS']; }
      if ( isset($arrAttr['current']) && isset($arrAttr['classC']) ) { if ( $arrAttr['current']==$strKey ) $strClass=$arrAttr['classC']; }
    $strStyle='';
      if ( isset($arrAttr['style']) ) $strStyle=$arrAttr['style'];
      if ( isset($arrAttr['styleS']) ) { if ( strlen($valSelected)>0 && $valSelected==$strKey ) $strStyle=$arrAttr['styleS']; }
      if ( isset($arrAttr['current']) && isset($arrAttr['styleC']) ) { if ( $arrAttr['current']==$strKey ) $strStyle=$arrAttr['styleC']; }
    switch($strTag)
    {
    case 'option':
      $strReturn .= '<option value="'.$strKey.'"'.(empty($strClass) ? '' : ' class="'.$strClass.'"').(empty($strStyle) ? '' : ' style="'.$strStyle.'"').($valSelected===$strKey ? ' selected="selected"' : '').(in_array($strKey,$arrDisabled,true) ? ' disabled="disabled" ': '').'>'.$strValue.'</option>';
      break;
    case 'checkbox':
      $strReturn .= '<input type="checkbox" value="'.$strKey.'"'.(empty($strClass) ? '' : ' class="'.$strClass.'"').(empty($strStyle) ? '' : ' style="'.$strStyle.'"').(empty($strName) ? '' : ' name="'.$strName.'"').(in_array($strKey,$arrDisabled,true) ? ' disabled="disabled" ': '').'/>'.$strValue;
      break;
    case 'hidden':
      $strReturn .= '<input type="hidden" name="'.$strKey.'" value="'.$strValue.'"/>';
      break;
    case 'span':
      $strReturn .= '<span'.(empty($strClass) ? '' : ' class="'.$strClass.'"').'>'.$strValue.'</span>'.(isset($arrAttr['endline']) ? $arrAttr['endline'] : '' );
      break;
    default:
      die('HtmlTags: Invalid argument #2');
    }
  }
  return $strReturn;
}

// Format datetime to a [string] YYYYMMDD{HHMMSS} (maximum 14 char)
// $s can be: QTdatabase format, 'now', integer or a string like 'YYYY-MM-DD HH:MM:SS' with any king of separator BUT with trailing 0!
// Returns '' when format is not supported

function QTdateclean($s='now')
{
  if ( is_int($s) ) $s = (string)$s;
  if ( !is_string($s) || empty($s) ) return '';
  if ( $s==='now' ) return date('YmdHi');
  if ( is_numeric($s) ) return $s;
  $s = substr(str_replace(array(' ','-','.','/',':'),'',$s),0,14);
  if ( is_numeric($s) ) return $s;
  return '';
}

/**
 * QTdatestr
 *
 * Convert a date [string] to a formatted date [string] and translate it.
 *
 * @$sDate    The date string, can be 'YYYYMMDD[HH][MM][SS]' or 'now'. It can include [.][/][-][ ]
 * @$sOutDate The format for the date (or '$' to use the system format)
 * @$sOutTime The format for the time (or '$' to use the system format). If not empty, it is added to the date format (or to the short date)
 * @$bFriendlyDate Replace $OutDate by 'Today','Yesterday' when possible.
 * @$bDropOldTime  Don't show time for date > 2 days.
 *
 * When $sDate is '0' or empty, or when the input date format is unsupported returns $e '?'
 * The translation uses $L['dateSQL'], if existing, and the default php words if not.
 * Also accept $sOutDate='RFC-3339' (this will ignore other parametres)
 */

function QTdatestr($sDate='now',$sOutDate='$',$sOutTime='$',$bFriendlyDate=false,$bDropOldTime=false,$bTitle=false,$e='?')
{
  $sDate = QTdateclean($sDate); if ( empty($sDate) ) return $e; // Clean $sDate (max 14 char)

  // Stop if input is a year only

  if ( strlen($sDate)==4 ) return $sDate;

  // check requested formats

  QTargs( 'QTdatestr',array($sOutDate,$sOutTime,$bFriendlyDate,$bDropOldTime,$bTitle,$e),array('str','str','boo','boo','boo','str') );
  if ( $sOutDate==='$' )
  {
    $sOutDate='Ymd'; // system date format cannot be empty
    if ( isset($_SESSION[QT]['formatdate']) && !empty($_SESSION[QT]['formatdate']) ) $sOutDate = $_SESSION[QT]['formatdate'];
  }
  if ( $sOutTime==='$' )
  {
    $sOutTime='';  // system time format can be empty
    if ( isset($_SESSION[QT]['formattime']) && !empty($_SESSION[QT]['formattime']) ) $sOutTime = $_SESSION[QT]['formattime'];
  }

  // Analyse date time

  $intDate = FALSE;
  switch(strlen($sDate))
  {
  case 6:  $intDate = mktime(0,0,0,substr($sDate,4,2),1,substr($sDate,0,4)); break;
  case 8:  $intDate = mktime(0,0,0,substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
  case 10: $intDate = mktime(substr($sDate,-2,2),0,0,substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
  case 12: $intDate = mktime(substr($sDate,-4,2),substr($sDate,-2,2),0,substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
  case 14: $intDate = mktime(substr($sDate,-6,2),substr($sDate,-4,2),substr($sDate,-2,2),substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
  default: return $e;
  }
  if ( $intDate===FALSE ) return $e;
  $bRecent = ( date('Y-m-d')==date('Y-m-d',$intDate) || date('Y-m-d')==date('Y-m-d',$intDate+86400) );

  // Exceptions (used by rss xml)

  if ( $sOutDate=='RFC-3339' )
  {
    $sDate = date('Y-m-d\TH:i:s',$intDate);
    $sGMT = date('O',$intDate);
    $sGMT = substr($sGMT,0,3).':'.substr($sGMT,-2,2);
    return $sDate.$sGMT;
  }

  // Output format

  $prefix = '';
  if ( $bRecent )
  {
    if ( $bFriendlyDate )
    {
    if ( date('Y-m-d')==date('Y-m-d',$intDate) )       { $prefix = 'Today '; $sOutDate=''; }
    if ( date('Y-m-d')==date('Y-m-d',$intDate+86400) ) { $prefix = 'Yesterday '; $sOutDate=''; }
    }
  }
  else
  {
    if ( $bDropOldTime ) $sOutTime = '';
  }
  $format = trim($sOutDate.' '.$sOutTime);

  $sDate = $prefix.(empty($format) ?  '' : date($format,$intDate));
  if ( empty($sDate) )  return $e;
  $sDateFull = date('j F Y'.(empty($sOutTime) ? '' : ', '.$sOutTime),$intDate);

  // Translating

  global $L;
  if ( isset($L['dateSQL']) ) {
  if ( is_array($L['dateSQL']) ) {
    $sDate = str_replace(array_keys($L['dateSQL']),array_values($L['dateSQL']),$sDate);
    $sDateFull = str_replace(array_keys($L['dateSQL']),array_values($L['dateSQL']),$sDateFull);
  }}

  // Exit

  if ( $bTitle ) return '<span title="'.$sDateFull.'">'.$sDate.'</span>';
  return trim($sDate);
}

/* ============
 * QTbbc
 * ------------
 * Convert bbc to html
 * ------------
 * $str       : [mandatory] a string than can contains bbc tags
 * $nl        : convert \r\n, \r or \n to $nl. Use FALSE to not convert.
 * $beforediv : (optional) tag to use before a bloc ([quote] or [code])
 * $afterdiv  : (optional) tag to use after a bloc ([quote] or [code])
 * ------------
 * Examples
 * QTbbc( '[b]Text[/b]')        -->   <b>Text</b>
 * QTbbc( '[i]<b>Text<b>[/i]')  -->   <i>&lt;b&gt;Text&lt;/b&gt;</i>
 * ============ */

function QTbbc($str,$nl='<br />',$beforediv='',$afterdiv='')
{
  // check

  if ( !is_string($str) ) die('QTbbc: arg #1 must be a string');
  if ( !is_string($nl) ) die('QTbbc: arg #3 must be a string');

  // process

  $arrSearch = array (
  '/</',
  '/>/',
  '/\[b\](.*?)\[\/b\]/',
  '/\[i\](.*?)\[\/i\]/',
  '/\[u\](.*?)\[\/u\]/',
  '/\[\*\]/',
  '/\[img\](.*?)\[\/img\]/',
  '/\[url\](.*?)\[\/url\]/',
  '/\[url\=(.*?)\](.*?)\[\/url\]/',
  '/\[mail\](.*?)\[\/mail\]/',
  '/\[mail\=(.*?)\](.*?)\[\/mail\]/',
  '/\[color\=(.*?)\](.*?)\[\/color\]/',
  '/\[size=(.*?)\](.*?)\[\/size\]/',
  '/\[quote\]/',
  '/\[quote\=(.*?)\]/',
  '/\[\/quote\]/',
  '/\[code\]/',
  '/\[\/code\]/');

  $arrReplace = array (
  '&lt;',
  '&gt;',
  '<b>$1</b>',
  '<i>$1</i>',
  '<span class="u">$1</span>',
  '&bull;',
  '<div class="imgmsg"><img class="imgmsg" src="$1" alt="[image]" title=""/></div>',
  '<a class="msgbody" href="http://$1" target="_blank">$1</a>',
  '<a class="msgbody" href="http://$2" target="_blank">$1</a>',
  '<a class="msgbody" href="mailto:$1">$1</a>',
  '<a class="msgbody" href="mailto:$2">$1</a>',
  '<font color="$1">$2</font>',
  '<span style="font-size:$1pt">$2</span>',
  $beforediv.'<div class="quotetitle">Quotation:</div><div class="quote">',
  '<div class="quotetitle">Quotation by $1:</div><div class="quote">',
  '</div>'.$afterdiv,
  $beforediv.'<div class="codetitle">Code:</div><div class="code">',
  '</div>'.$afterdiv);

  $str = preg_replace( $arrSearch, $arrReplace, $str );
  $str = str_replace( array('http://http','http://ftp:','http://mailto:','mailto:mailto:'), array('http','ftp:','mailto:','mailto'), $str ); // special check for the href error
  if ( is_string($nl) ) $str = str_replace( array("\r\n","\r","\n"), $nl, $str );

  return $str;
}

// --------

function QTencode($str='',$arrSymbols='Q A L R')
{
  // This will encode (or decode) special characters: quote, apostrophe, open, close, amp
  // $arrSymbols is the list of symbols to encode (noted Q A L R or &). Use - to decode
  // Note: $arrSymbols can be a string with space separated values
  // Note: If you want to convert &, you must make it first.

  if ( empty($str) ) return $str;
  if ( is_string($arrSymbols) ) $arrSymbols = explode(' ',$arrSymbols);
  if ( empty($arrSymbols) ) return $str;
  if ( !is_array($arrSymbols) ) return $str;

  foreach($arrSymbols as $strSymbol) {
  switch($strSymbol) {
  case '&': $str = str_replace('&','&amp;',$str); break;
  case 'A': $str = str_replace("'",'&apos;',$str); break;
  case 'Q': $str = str_replace('"','&quot;',$str); break;
  case 'L': $str = str_replace('<','&lt;',$str); break;
  case 'R': $str = str_replace('>','&gt;',$str); break;
  case '-A': $str = str_replace(array('&apos;','&#039;','&#39;'),"'",$str); break;
  case '-Q': $str = str_replace(array('&quot;','&#034;','&#34;'),'"',$str); break;
  case '-L': $str = str_replace(array('&lt;','&#060;','&#60;'),'<',$str); break;
  case '-R': $str = str_replace(array('&gt;','&#062;','&#62;'),'>',$str); break;
  case '-&': $str = str_replace(array('&amp;','&#038;','&#38;'),'&',$str); break;
  }}
  return $str;
}

// --------

function QTconv($str,$to='1',$bConvAmp=false,$bDroptags=true)
{
  if ( empty($str) ) return $str;
  if ( !is_string($str) ) die('QTconv: arg #1 must be a string');
  if ( !is_string($to) ) die('QTconv: arg #2 must be a string');
  if ( !is_bool($bConvAmp) ) die('QTconv: arg #3 must be a boolean');
  if ( !is_bool($bDroptags) ) die('QTconv: arg #4 must be a boolean');

  // optional drop tags and &

  if ( $bDroptags ) $str = strip_tags($str);
  if ( $to=='3' && $bConvAmp ) $to='4';

  // U special for username and password
  // I special for input form: convert & alone to &amp;
  // 1 converts "          // -1 converts &quot;
  // 2 converts " '        // -2 converts &quot; &#039;
  // 3 converts " ' < >    // -3 converts &quot; &#039; &lt; &gt;
  // 4 converts " ' < > &  // -4 converts &quot; &#039; &lt; &gt; &amp;
  // 5 converts to htmlentities but restore the &amp; > &
  // 6 converts to htmlentities
  // K special for keycode
  // F special for filename
  // T convert to time HHMMSS (from HHMM,HH:MM[:SS],...) add 00 if no second

  switch ($to)
  {
  case 'U':
    return substr(htmlspecialchars(trim($str),ENT_QUOTES),0,24);
    break;
  case 'I':
    if ( strstr($str,'&') )
    {
    $str = str_replace('&','&amp;',$str);
    $str = str_replace('&amp;quot;','&quot;',$str);
    $str = str_replace('&amp;#039;','&#039;',$str);
    }
    break;
  case '1':
    $str = str_replace('"','&quot;',$str);
    break;
  case '2':
    $str = str_replace('"','&quot;',$str);
    $str = str_replace("'",'&#039;',$str);
    break;
  case '3':
    $str = str_replace('"','&quot;',$str);
    $str = str_replace("'",'&#039;',$str);
    $str = str_replace('<','&lt;',$str);
    $str = str_replace('>','&gt;',$str);
    break;
  case '4':
    $str = htmlspecialchars($str,ENT_QUOTES);
    break;
  case '5':
    $str = htmlentities($str,ENT_QUOTES);
    if ( strstr($str,'&') ) $str = str_replace('&amp;','&', $str);
    break;
  case '6':
    $str = htmlentities($str,ENT_QUOTES);
    break;
  case '-1':
    $str = str_replace('&quot;','"',$str);
    break;
  case '-2':
    $str = str_replace('&quot;','"',$str);
    $str = str_replace('&#039;',"'",$str);
    break;
  case '-3':
    $str = str_replace('&quot;','"',$str);
    $str = str_replace('&#039;',"'",$str);
    $str = str_replace('&lt;','<',$str);
    $str = str_replace('&gt;','>',$str);
    break;
  case '-4':
    $str = str_replace('&quot;','"', $str);
    $str = str_replace('&#039;',"'", $str);
    if ( strstr($str,'&') )
    {
    $str = str_replace('&amp;','&', $str);
    $str = str_replace('&#39;',"'", $str);
    $str = str_replace('&lt;','<', $str);
    $str = str_replace('&gt;','>', $str);
    }
    break;
  case 'K':
    $str=strtr($str,'ÈËÍÎ…» À·‡‚‰¡¿¬ƒ≈ÂÌÏÓÔÕÃŒœÛÚÙˆ”“‘÷ı’˙˘˚¸⁄Ÿ€‹','eeeeeeeeaaaaaaaaaaiiiiiiiioooooooooouuuuuuuu');
    $str=strtolower($str);
    $str=preg_replace('/[^a-z0-9_\-\.]/', '_', $str);
    break;
  case 'F':
    $str=strtr($str,'ÈËÍÎ…» À·‡‚‰¡¿¬ƒ≈ÂÌÏÓÔÕÃŒœÛÚÙˆ”“‘÷ı’˙˘˚¸⁄Ÿ€‹','eeeeeeeeaaaaaaaaaaiiiiiiiioooooooooouuuuuuuu');
    $str=strtolower($str);
    $str=preg_replace('/[^a-z0-9_\-]/', '_', $str); // replace symbol by '_' (but keep the '.' and '-')
    break;
  case 'T':
    $str = strtr(trim($str),':,.;-HhMmSsUu','             ');
    $arr = explode(' ',$str); for ($i=0;$i<3;$i++) { if ( !isset($arr[$i]) ) $arr[$i]='00'; }
    if ( !QTisbetween($arr[0],0,24) ) $arr[0]='00';
    if ( !QTisbetween($arr[1],0,59) ) $arr[1]='00';
    if ( !QTisbetween($arr[2],0,59) ) $arr[2]='00';
    $str = $arr[0].$arr[1].$arr[2];
    break;
  }
  if ( strlen($str)>4000 ) $str = substr($str,0,4000);
  return trim($str);
}

// ------------
// QTispassword / islogin / ismail /isbetween / isvaliddate
// ------------
// These functions shows an error message when the principal argument(s) is not of the correct type.
// About login/password:
//   Return FALSE if the text is not trimmed
//   Return FALSE when text includes unacceptable characters
//     a login can contain the ' caracter while a password cannot.
//     both login and password cannot contain " < > \ /
//     for caracters after z, only a few accents are supported .
// About validdate:
//   This function will check date like YYYYMMDD (as string or as number). Options allow also to rejet past/futur year.
// ------------

function QTislogin($str,$intMin=4,$intMax=24)
{
  if ( !is_string($str) ) die('QTislogin: arg #1 must be a string');
  if ( !is_int($intMin) ) die('QTislogin: arg #2 must be an int');
  if ( !is_int($intMax) ) die('QTislogin: arg #3 must be an int');

  if ( $str!=trim($str) ) return false;
  if ( strstr($str,'\\') ) return false; //' check this
  if ( strstr($str,'<') ) return false;
  if ( strstr($str,'>') ) return false;
  if ( strstr($str,'&lt;') ) return false;
  if ( strstr($str,'&gt;') ) return false;
  if ( !preg_match("/^[#-z ÈËÁÙÓÍÒﬂß\!]+$/",$str) ) return false;
  if ( $str!=strip_tags($str) ) return false;
  if ( strlen($str)>$intMax ) return false;
  if ( strlen($str)<$intMin ) return false;
  return true;
}
function QTispassword($str,$intMin=4,$intMax=24)
{
  if ( !is_string($str) ) die('QTispassword: arg #1 must be a string');
  if ( !is_int($intMin) ) die('QTispassword: arg #2 must be an int');
  if ( !is_int($intMax) ) die('QTispassword: arg #3 must be an int');

  // password cannot contain apostrophe while login can
  if ( strstr($str,"'") ) return false;
  // uses QTislogin
  if ( !QTislogin($str,$intMin,$intMax) ) return false;
  return true;
}
function QTismail($str)
{
  if ( !is_string($str) ) die('QTismail: arg #1 must be a string');

  if ( $str!=trim($str) ) return false;
  if ( $str!=strip_tags($str) ) return false;
  if ( !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i",$str) ) return false;
  return true;
}
function QTisbetween($intValue,$intMin=0,$intMax=99999)
{
  if ( $intValue==='') return false;
  if ( !is_numeric($intValue) ) return false;
  if ( !is_numeric($intMin) ) die('QTisbetween: arg #2 must be a numeric (or a number as string)');
  if ( !is_numeric($intMax) ) die('QTisbetween: arg #3 must be a numeric (or a number as string)');
  if ( $intValue<$intMin ) return false;
  if ( $intValue>$intMax ) return false;
  return true;
}
function QTisvaliddate($d,$bPast=true,$bFutur=false) // allow past year, disallow futur year
{
  if ( is_string($d) ) { if ( substr($d,0,6)=='Cannot' ) return false; }
  if ( !is_numeric($d) ) return false;
  if ( !is_bool($bPast) ) die('QTisvaliddate: arg #2 must be a bolean');
  if ( !is_bool($bFutur) ) die('QTisvaliddate: arg #3 must be a bolean');

  $str = strval($d);
  if ( strlen($str)!=8 ) return false;
  $intY = intval(substr($str,0,4));
  $intM = intval(substr($str,4,2));
  $intD = intval(substr($str,-2,2));
  if ( $intY<1900 ) return false;
  if ( $intM<1 || $intM>12 ) return false;
  if ( $intD<1 || $intD>31 ) return false;
  if ( !$bPast ) { if ( $intY<date('Y') ) return false; }
  if ( !$bFutur ) { if ( $intY>date('Y') ) return false; }
  if ( !checkdate($intM,$intD,$intY) ) return false;
  return true;
}
function QTisvalidtime($d)
{
  if ( is_string($d) ) { if ( substr($d,0,6)=='Cannot' ) return false; }
  if ( !is_numeric($d) ) return false;

  $d = strval($d);
  if ( strlen($d)!=4 && strlen($d)!=6 ) return false;
  if ( !QTisbetween(substr($d,0,2),0,23) ) return false;
  if ( !QTisbetween(substr($d,2,2),0,59) ) return false;
  if ( strlen($d)==6 ) { if ( !QTisbetween(substr($d,4,2),0,59) ) return false; }
  return true;
}
function QTiswebimageext($str)
{
  $str = strrchr($str,'.');
  if ( $str=='.jpeg' || $str=='.jpg' || $str=='.png' || $str=='.gif' ) return true;
  return false;
}

// --------

function QTarradd($arr,$strKey,$strValue=null)
{
  // Add (or remove) a key+value to the array.
  // When $strValue is null, the key is not set (or removed if existing)
  if ( !is_array($arr) ) die('QTarradd: arg #1 must be an array');
  if ( !is_string($strKey) ) die('QTarradd: arg #2 must be a string');
  if ( isset($arr[$strKey]) ) unset($arr[$strKey]);
  if ( is_null($strValue) ) return $arr;
  $arr[$strKey] = $strValue;
  return $arr;
}

// --------

function QTarrget($arr,$key='title')
{
  // Converts an array of arrays into a simple array where the values are the [$key]element of each array (indexes are preserved).
  // When the [$key]element doesn't existing, the result will include a NULL.
  // If on element of $arr is not an array, it REMAINS in the result. $key can be integer or string.
  if ( !is_array($arr) ) die('QTarrget: arg #1 must be an array');
  foreach($arr as $k=>$a) {
  if ( is_array($a) ) {
    if ( isset($a[$key]) ) { $arr[$k]=$a[$key]; } else { $arr[$k]=null; }
  }}
  return $arr;
}

// --------

function QTexplode($str,$sep=';',$function='')
{
  // From a string "key1=value1;key2=value2" returns an array of key=>value.
  // When $str is empty or when there is no "=" the function returns an empty array
  // Keys without =, trailing or duplicate seprators are skipped. For duplicate keys, last value overwrites previous.
  // A $function can be applied to each value (ex "urldecode" or "strtolower")

  if ( empty($str) ) return array();
  if ( !empty($function) && !function_exists($function) ) die('QTexplode: requested function ['.$function.'] is unknown');
  $arr = explode($sep,$str);
  $arrArgs = array();
  foreach($arr as $str)
  {
    if ( strstr($str,'=') )
    {
    $arrPart = explode('=',$str);
    $arrArgs[$arrPart[0]]= (empty($function) ? $arrPart[1] : $function($arrPart[1]));
    }
  }
  return $arrArgs;
}
function QTexplodeUri($str=null,$urldecode=false)
{
  // Same as QTexplode() but for url arguments (separated by & or by &amp;)
  // If $str is empty, the current URI is used.
  // By default each argument is urldecoded.

  if ( is_null($str) )
  {
    $arr = parse_url($_SERVER['REQUEST_URI']);
    if ( !isset($arr['query']) ) return array();
    $str = $arr['query'];
    if ( empty($str) ) return array();
  }
  $str = str_replace('&amp;','&',$str);
  return QTexplode($str,'&',($urldecode ? 'urldecode' : ''));
}
function QTexplodeValue($str,$key='',$sep=';',$function='')
{
  if ( empty($str) ) return null;
  if ( !empty($function) && !function_exists($function) ) die('QTexplodevalue: requested function ['.$function.'] is unknown');
  if ( !is_string($key) || $key==='' ) die('QTexplodevalue: arg #2 must be a string');
  $value = null;
  $arr = QTexplode($str,$sep,$function);
  if ( isset($arr[$key]) ) $value = (empty($function) ? $arr[$key] : $function($arr[$key]));
  return $value;
}
function QTexplodeUriValue($str=null,$key='',$urldecode=false,$function='')
{
  if ( !empty($function) && !function_exists($function) ) die('QTexplodevalue: requested function ['.$function.'] is unknown');
  if ( !is_string($key) || $key==='' ) die('QTexplodevalue: arg #2 must be a string');
  $value = null;
  $arr = QTexplodeUri($str,$urldecode);
  if ( isset($arr[$key]) ) $value = (empty($function) ? $arr[$key] : $function($arr[$key]));
  return $value;
}

// --------

function QTimplode($arr,$sep=';',$function='')
{
  // Build a string "key1=value1;key2=value2" from the array. Returns '' when the array is empty.
  // A $function can be applied to each value (ex "urlencode" or "strtolower")

  if ( !is_array($arr) ) die('QTimplode: arg #1 must be an array');
  if ( !is_string($sep) ) die('QTimplode: arg #2 must be a string');
  if ( !empty($function) && !function_exists($function) ) die('QTimplode: requested function ['.$function.'] is unknown');

  if ( count($arr)==0 ) return '';
  $str = '';
  foreach($arr as $key=>$value)
  {
  if ( !empty($function) ) $value = $function($value);
  $str .= ($str==='' ? '' : $sep).$key.'='.$value;
  }
  return $str;
}
function QTimplodeUri($arr,$sep='&amp;',$urlencode=false)
{
  // Same as QTimplode() but for url arguments
  return QTimplode($arr,$sep,($urlencode ? 'urlencode' : ''));
}

// --------

function QTargexplode($str=null) { return QTexplodeUri($str); } // Reference to the old function
function QTargimplode($arr,$sep='&amp;') { return QTimplodeUri($arr,$sep); } // Reference to the old function

// --------

function QTunbbc($str,$bDeep=true)
{
  if ( !is_string($str) ) die('QTunbbc: arg #1 must be a string');
  if ( empty($str) ) return $str;
  return preg_replace( array('/\[b\](.*?)\[\/b\]/','/\[i\](.*?)\[\/i\]/', '/\[u\](.*?)\[\/u\]/', '/\[\*\]/', '/\[img\](.*?)\[\/img\]/', '/\[url\](.*?)\[\/url\]/', '/\[url\=(.*?)\](.*?)\[\/url\]/', '/\[mail\](.*?)\[\/mail\]/', '/\[mail\=(.*?)\](.*?)\[\/mail\]/', '/\[color\=(.*?)\](.*?)\[\/color\]/', '/\[size=(.*?)\](.*?)\[\/size\]/', '/\[quote\]/', '/\[quote\=(.*?)\]/', '/\[\/quote\]/', '/\[code\]/', '/\[\/code\]/') , array('$1','$1','$1','$1','$1','$1','$1','$1','$1','$1','$1',($bDeep ? '' : 'Quotation: '),($bDeep ? '' : 'Quotation by $1'),'',($bDeep ? '' : 'Code: '),'') , $str );
}

// --------

function QTcompact($str,$max=200,$nl="\r\n")
{
  if ( !is_string($str) ) die('QTcompact: arg #1 must be a string');
  if ( empty($str) ) return $str;
  if ($max>0 && strlen($str)>$max ) $str=substr($str,0,$max).' ...';
  $str = str_replace("\r\n\r\n\r\n",$nl,$str);
  $str = str_replace("\r\n\r\n",$nl,$str);
  if ( strpos($str,'[')!==FALSE )
  {
  $str = str_replace("[/quote]\r\n",'[/quote]',$str);
  $str = str_replace("[/code]\r\n",'[/code]',$str);
  }
  return $str;
}

// --------

function QThttpvar($arrV,$arrT,$bStriptags=true,$bGet=true,$bPost=true)
{
  // Assign values Http GET or POST to the variables. The values are assigned with the specific type.
  // $arrV      is the list of variables to create from the http get/post [can be a string of names separated by space]
  // $arrT      is the list of desired variable types: 'int','str','boo' or 'flo' [can be a string of names separated by space]
  // $bStiptags strip the tags when the type string is requested (to avoid injection)
  // $bGet      accept/reject variables send by Http GET method
  // $bPost     accept/reject variables send by Http POST method
  // Ex: QThttpvar('a b c','str boo int');
  // Note #1: When a user try to inject new variables, they will not be created (only variables in the list are parsed).
  // Note #2: It's recommended to initialise the variables before using this assigment function.
  // Note #3: When values are not send by Http get/post, the initial variable remains unchanged (can be a new variable with NULL value if the variable was not initialised).
  // Note #4: When you request the type 'boo' (boolean), the variable is set to TRUE when http get/post is '1', for all other values the variable is set to FALSE.
  if ( is_string($arrV) ) $arrV=explode(' ',$arrV);
  if ( is_string($arrT) ) $arrT=explode(' ',$arrT);
  if ( !is_array($arrV) || !is_array($arrT)) die('QThttpvar: arrV and arrT must be arrays.');
  if ( count($arrV)!=count($arrT) ) die('QThttpvar: arrV and arrT must be the same size.');

  $i=0;
  foreach($arrV as $strV)
  {
    $strT = $arrT[$i];
    global $$strV;
    if ( $bGet && isset($_GET[$strV]) )
    {
      $_GET[$strV]=trim($_GET[$strV]);
      switch($strT)
      {
      case 'int': $$strV=intval($_GET[$strV]); break;
      case 'str': $$strV=($bStriptags ? strip_tags($_GET[$strV]) : $_GET[$strV]); break;
      case 'boo': $$strV=($_GET[$strV]==='1' ? true : false); break;
      case 'flo': $$strV=floatval($_GET[$strV]); break;
      default: die('QThttpvar: Invalid data type ['.$strT.']');
      }
    }
    if ( $bPost && isset($_POST[$strV]) )
    {
      $_POST[$strV]=trim($_POST[$strV]);
      switch($strT)
      {
      case 'int': $$strV=intval($_POST[$strV]); break;
      case 'str': $$strV=($bStriptags ? strip_tags($_POST[$strV]) : $_POST[$strV]); break;
      case 'boo': $$strV=($_POST[$strV]==='1' ? true : false); break;
      case 'flo': $$strV=floatval($_POST[$strV]); break;
      default: die('QThttpvar: Invalid data type ['.$strT.']');
      }
    }
    $i++;
  }
}