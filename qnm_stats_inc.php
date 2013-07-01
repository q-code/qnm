<?php

// QNM 1.0 build:20130410

// GLOBAL STATISTICS included in: qnm_stats.php (and _csv,_pdf)

include 'bin/qt_lib_graph.php';

// Initialise array values. When a value is missing the display will show &middot;

if ( !isset($intStartyear) )
{
  $oDB->Query( 'SELECT min(insertdate) as startdate, max(insertdate) as lastdate FROM '.TABNE );
  $row = $oDB->Getrow();
  if ( empty($row['startdate']) ) $row['startdate']=strval($y-1).'0101';
  if ( empty($row['lastdate']) ) $row['lastdate']=strval($y).'1231';
  $strLastdaysago = substr($row['lastdate'],0,8);
  $strTendaysago = DateAdd($strLastdaysago,-10,'day');
  $intStartyear = intval(substr($row['startdate'],0,4));
  $intStartmonth = intval(substr($row['startdate'],4,2));
  $intEndyear = intval(date('Y'));
  $intEndmonth = intval(date('n'));
}

switch($ch['time'])
{
case 'q': $intMaxBt=4; break;
case 'd': $intMaxBt=10; break;
case 'm': $intMaxBt=12; break;
default: die('Invalid blocktime');
}

$intCurrentYear = $y;
$strCurrentTendaysago = $strTendaysago;

$arrA = array(); // Abscise

// =========
// COUNT GLOBAL & GLOBAL TRENDS
// =========
if ( $tt=='g' || $tt=='gt' ) {
// =========

$arrT = array(); $arrTs = array(); // Elements,   Elements sum,   ($arrT can have null)
$arrM = array(); $arrMs = array(); // Messages (open or closed, not deleted)
$arrU = array(); $arrUs = array(); // Connectors

foreach($arrYears as $intYear) // GLOBAL has 1 year, GLOBAL TRENDS has 2 years
{
  $arrT[$intYear] = array();
  $arrM[$intYear] = array();
  $arrU[$intYear] = array();
  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;$i++) { $arrA[$i]='Q'.$i;                                        $arrT[$intYear][$i]=null; $arrM[$intYear][$i]=null; $arrU[$intYear][$i]=null; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;$i++) { $arrA[$i]=$L['dateMM'][$i];                              $arrT[$intYear][$i]=null; $arrM[$intYear][$i]=null; $arrU[$intYear][$i]=null; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;$i++) { $arrA[$i]=substr(DateAdd($strTendaysago,$i,'day'),-2,2); $arrT[$intYear][$i]=null; $arrM[$intYear][$i]=null; $arrU[$intYear][$i]=null; } break;
  }
  $arrTs[$intYear] = 0;
  $arrMs[$intYear] = 0;
  $arrUs[$intYear] = 0;
}

// -----
foreach($arrYears as $intYear) {
// -----

if ( $intCurrentYear==$intYear ) { $strTendaysago = $strCurrentTendaysago; } else { $strTendaysago = DateAdd(substr($strTendaysago,0,8),-1,'year'); }

// COUNT ElementS

for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+$intBt),'insertdate',6) );
  if ( $ch['time']=='q') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+($intBt-1)*3),'insertdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'insertdate',6,'<=') );
  if ( $ch['time']=='d') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0'.$strQfilter.' AND '.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'insertdate',8) );

  $row = $oDB->Getrow();
  $arrT[$intYear][$intBt] = intval($row['countid']);
  $arrTs[$intYear] += $arrT[$intYear][$intBt]; // total
}

// COUNT MESSAGES

for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query( 'SELECT sum(posts) as countid FROM '.TABNE.' WHERE status>=0'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+$intBt),'insertdate',6) );
  if ( $ch['time']=='q') $oDB->Query( 'SELECT sum(posts) as countid FROM '.TABNE.' WHERE status>=0'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+($intBt-1)*3),'insertdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'insertdate',6,'<=') );
  if ( $ch['time']=='d') $oDB->Query( 'SELECT sum(posts) as countid FROM '.TABNE.' WHERE status>=0'.$strQfilter.' AND '.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'insertdate',8) );

  $row = $oDB->Getrow();
  $arrM[$intYear][$intBt] = intval($row['countid']);
  $arrMs[$intYear] += $arrM[$intYear][$intBt]; // total
}

// COUNT CONNECTORS

for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query( 'SELECT sum(conns) as countid FROM '.TABNE.' WHERE status>=0'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+$intBt),'insertdate',6) );
  if ( $ch['time']=='q') $oDB->Query( 'SELECT sum(conns) as countid FROM '.TABNE.' WHERE status>=0'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+($intBt-1)*3),'insertdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'insertdate',6,'<=') );
  if ( $ch['time']=='d') $oDB->Query( 'SELECT sum(conns) as countid FROM '.TABNE.' WHERE status>=0'.$strQfilter.' AND '.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'insertdate',8) );

  $row = $oDB->Getrow();
  $arrU[$intYear][$intBt] = intval($row['countid']);
  $arrUs[$intYear] += $arrU[$intYear][$intBt]; // total
}


// -----
}
// -----

// =========
}
// =========

// =========
// COUNT DETAIL & DETAIL TRENDS
// =========
if ( $tt=='d' || $tt=='dt' ) {
// =========

$arrN = array(); $arrNs = array(); // Normal active (status=1)
$arrC = array(); $arrCs = array(); // Closed inactive (status=0)
$arrT = array(); $arrTs = array(); // Deeleted (status=-1)

foreach($arrYears as $intYear)
{
  $arrN[$intYear] = array();
  $arrC[$intYear] = array();
  $arrT[$intYear] = array();
  switch($ch['time'])
  {
  case 'q': for ($i=1;$i<=$intMaxBt;$i++) {  $arrA[$i]='Q'.$i;                                        $arrT[$intYear][$i]=null; $arrN[$intYear][$i]=null; $arrC[$intYear][$i]=null; } break;
  case 'm': for ($i=1;$i<=$intMaxBt;$i++) {  $arrA[$i]=$L['dateMM'][$i];                              $arrT[$intYear][$i]=null; $arrN[$intYear][$i]=null; $arrC[$intYear][$i]=null; } break;
  case 'd': for ($i=1;$i<=$intMaxBt;$i++) {  $arrA[$i]=substr(DateAdd($strTendaysago,$i,'day'),-2,2); $arrT[$intYear][$i]=null; $arrN[$intYear][$i]=null; $arrC[$intYear][$i]=null; } break;
  }
  $arrNs[$intYear] = 0;
  $arrCs[$intYear] = 0;
  $arrTs[$intYear] = 0;
}

// -----
foreach($arrYears as $intYear) {
// -----

if ( $intCurrentYear==$intYear ) { $strTendaysago = $strCurrentTendaysago; } else { $strTendaysago = DateAdd(substr($strTendaysago,0,8),-1,'year'); }

// INACTIVE ElementS

for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0 AND status=1'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+$intBt),'insertdate',6) );
  if ( $ch['time']=='q') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0 AND status=1'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+($intBt-1)*3),'insertdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'insertdate',6,'<=') );
  if ( $ch['time']=='d') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0 AND status=1'.$strQfilter.' AND '.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'insertdate',8) );

  $row = $oDB->Getrow();
  $arrN[$intYear][$intBt] = intval($row['countid']);
  $arrNs[$intYear] += $arrN[$intYear][$intBt]; // total
}

// ACTIVES ElementS

for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0 AND status=0'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+$intBt),'insertdate',6) );
  if ( $ch['time']=='q') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0 AND status=0'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+($intBt-1)*3),'insertdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'insertdate',6,'<=') );
  if ( $ch['time']=='d') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0 AND status=0'.$strQfilter.' AND '.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'insertdate',8) );

  $row = $oDB->Getrow();
  $arrC[$intYear][$intBt] = intval($row['countid']);
  $arrCs[$intYear] += $arrC[$intYear][$intBt]; // total
}

// DELETED elements

for ($intBt=1;$intBt<=$intMaxBt;$intBt++)
{
  // check limits (startdate/enddate)

  if ( $intYear<$intStartyear ) continue;
  if ( $intYear==$intStartyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt<$intStartmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9) ) {
    continue;
    }}
  }
  if ( $intYear>=$intEndyear )
  {
    if ( $ch['time']=='m' ) {
    if ( $intBt>$intEndmonth ) {
    continue;
    }}
    if ( $ch['time']=='q' ) {
    if ( ($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10) ) {
    continue;
    }}
  }

  // compute per blocktime

  if ( $ch['time']=='m') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0 AND status=-1'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+$intBt),'insertdate',6) );
  if ( $ch['time']=='q') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0 AND status=-1'.$strQfilter.' AND '.SqlDateCondition(($intYear*100+($intBt-1)*3),'insertdate',6,'>').' AND '.SqlDateCondition(($intYear*100+($intBt*3)),'insertdate',6,'<=') );
  if ( $ch['time']=='d') $oDB->Query( 'SELECT count(uid) as countid FROM '.TABNE.' WHERE uid>0 AND status=-1'.$strQfilter.' AND '.SqlDateCondition((DateAdd($strTendaysago,$intBt,'day')),'insertdate',8) );

  $row = $oDB->Getrow();
  $arrT[$intYear][$intBt] = intval($row['countid']);
  $arrTs[$intYear] += $arrT[$intYear][$intBt]; // total
}

// -----
}
// -----

// =========
}
// =========