<?php

// QNM 1.0 build:20130410

switch($tt)
{

//--------
case 'g':
//--------

$arrSeries[$L['Items']] = GetSerie($arrT,$arrTs,$y,$intMaxBt);
$arrSeries[$L['Messages']] = GetSerie($arrM,$arrMs,$y,$intMaxBt);
$arrSeries[$L['Connectors']] = GetSerie($arrU,$arrUs,$y,$intMaxBt);
$arrSeriesColor = array($L['Items']=>'#000066',$L['Messages']=>'#990099',$L['Connectors']=>'#009999');

QTtablechart($arrHeader,$arrSeries,$arrSeriesColor,array('th'=>'th','td'=>'td'));

// After values display, change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrT[$intYear] = QTarrayzero($arrT[$intYear]);
$arrM[$intYear] = QTarrayzero($arrM[$intYear]);
$arrU[$intYear] = QTarrayzero($arrU[$intYear]);
}

// PCHART OR CHART

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class')  && !isset($_GET['oldgraph']) )
{
  // Abscise Label
  $arrA = GetAbscise($ch['time'],$intMaxBt,$strTendaysago);

  // Standard inclusions and prepare CACHE
  include 'pChart/pData.class';
  include 'pChart/pChart.class';
  if ( file_exists('pChart/pCache.class') )
  {
  include 'pChart/pCache.class';
  $Cache = new pCache('pChart/Cache/'); // variable must be created here (re-used as global in QTpchart function)
  CheckChartCache($Cache);
  }

  // charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul
  // note: language code is added to the filename to enable refreshing cached-graph when user change language.
  $strChart1 = QTpchart(
    $L['Items_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrT[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'1'.$y.implode('',$ch).$lang,
    1);
  $strChart2 = QTpchart(
    $L['Items_per_'.$ch['time'].'_cumul'].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrT[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'2'.$y.implode('',$ch).$lang,
    1,true); //cumul
  $strChart3 = QTpchart(
    $L['Messages_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrM[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'3'.$y.implode('',$ch).$lang,
    2);
  $strChart4 = QTpchart(
    $L['Connectors_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrU[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'4'.$y.implode('',$ch).$lang,
    3);

  // DISPLAY

  echo '<p class="small" style="text-align:right">',implode(' &middot; ',$arrMenuType),' | ',implode(' &middot; ',$arrMenuValue),'</p>';

  echo '<table class="hidden">',PHP_EOL;
  echo '<tr class="hidden">',PHP_EOL;
  echo '<td class="hidden"><img src="'.$strChart1.'"/></td>',PHP_EOL;
  echo '<td class="hidden" style="text-align:right"><img src="'.$strChart2.'"/></td>',PHP_EOL;
  echo '</tr>';
  echo '<tr class="hidden">',PHP_EOL;
  echo '<td class="hidden"><img src="'.$strChart3.'"/></td>',PHP_EOL;
  echo '<td class="hidden" style="text-align:right"><img src="'.$strChart4.'"/></td>',PHP_EOL;
  echo '</tr>';
  echo '</table>';

}
elseif ( file_exists('bin/qt_lib_graph.php') && file_exists($_SESSION[QT]['skin_dir'].'/qnm_stats.css') )
{

  echo '<p class="small" style="text-align:right">',implode(' &middot; ',$arrMenuValue),'</p>';

  // Elements & cumulated items
  if ( $ch['value']=='a' )
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrT[$y]),320,100,QTroof($arrT[$y]),2,true,$L['Items_per_'.$ch['time']],'','1');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrT[$y])),350,100,100,2,'P',$L['Items_per_'.$ch['time']].' (%)','','1');
    echo '</div>',PHP_EOL;
  }

  if ( $ch['value']=='a' )
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTcumul($arrT[$y])),320,100,QTroof($arrT[$y]),2,true,$L['Items_per_'.$ch['time'].'_cumul'],'','1');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTcumul(QTpercent($arrT[$y],2))),350,100,100,2,'P',L('Items_per_'.$ch['time'].'_cumul').' (%)','','1');
    echo '</div>',PHP_EOL;
  }

  if ( $ch['value']=='a' )
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrM[$y]),320,100,QTroof($arrM[$y]),2,true,L('Messages_per_'.$ch['time']),'','2');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrM[$y])),350,100,100,2,'P',L('Messages_per_'.$ch['time']).' (%)','','2');
    echo '</div>',PHP_EOL;
  }

  if ( $ch['value']=='a' )
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrU[$y]),320,100,QTroof($arrM[$y]),2,true,L('Connectors_per_'.$ch['time']),'','3');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrU[$y])),350,100,100,2,'P',L('Connectors_per_'.$ch['time']).' (%)','','3');
    echo '</div>',PHP_EOL;
  }
}
else
{
  echo '<p class="small">Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php, ',$_SESSION[QT]['skin_dir'].'/qnm_stats.css</p>';
}
break;

//--------
case 'gt':
//--------

foreach($arrYears as $y)
{
$arrSeries[$L['Items']] = GetSerie($arrT,$arrTs,$y,$intMaxBt);
$arrSeries[$L['Messages']] = GetSerie($arrM,$arrMs,$y,$intMaxBt);
$arrSeries[$L['Connectors']] = GetSerie($arrU,$arrUs,$y,$intMaxBt);
$arrSeriesColor = array($L['Items']=>($y==$intCurrentYear ? '#000066' : '#00AFFF'),$L['Messages']=>($y==$intCurrentYear ? '#990099' : '#F1B8FF'),$L['Connectors']=>($y==$intCurrentYear ? '#009999' : '#00E7B7'));
QTtablechart($arrHeader,$arrSeries,$arrSeriesColor,array('th'=>'th','td'=>'td'),$y);
}

echo '<p class="small" style="text-align:right">',implode(' &middot; ',$arrMenuTrend),'</p>';

$arrSeries[$L['Items']] = GetSerieDelta($arrT,$arrTs,$y,$intMaxBt,$ch['trend']=='p');
$arrSeries[$L['Messages']] = GetSerieDelta($arrM,$arrMs,$y,$intMaxBt,$ch['trend']=='p');
$arrSeries[$L['Connectors']] = GetSerieDelta($arrU,$arrUs,$y,$intMaxBt,$ch['trend']=='p');
$arrSeriesColor = array($L['Items']=>'',$L['Messages']=>'',$L['Connectors']=>'');
QTtablechart($arrHeader,$arrSeries,$arrSeriesColor,array('th'=>'th','td'=>'td'),$L['Trends']);

// After values display change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrT[$intYear] = QTarrayzero($arrT[$intYear]);
$arrM[$intYear] = QTarrayzero($arrM[$intYear]);
$arrU[$intYear] = QTarrayzero($arrU[$intYear]);
}

// -----
// GRAPH
// -----

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') && file_exists('pChart/pCache.class') && !isset($_GET['oldgraph']) )
{
  // abscise Label
  $arrA = GetAbscise($ch['time'],$intMaxBt,$strTendaysago);

  // Standard inclusions and prepare CACHE
  include 'pChart/pData.class';
  include 'pChart/pChart.class';
  include 'pChart/pCache.class';
  $Cache = new pCache('pChart/Cache/'); // variable must be created here (re-used as global in QTpchart function)
  CheckChartCache($Cache);

  // charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul
  $strChart1 = QTpchart(
    $L['Items_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrT[$y-1],'Serie2'=>$arrT[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'1'.$y.implode('',$ch).$lang,
    1);
  $strChart2 = QTpchart(
    $L['Messages_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrM[$y-1],'Serie2'=>$arrM[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'2'.$y.implode('',$ch).$lang,
    2);
  $strChart3 = QTpchart(
    $L['Connectors_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrU[$y-1],'Serie2'=>$arrU[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'3'.$y.implode('',$ch).$lang,
    3);

  // -------
  // DISPLAY
  // -------

  echo '<div style="margin:0 auto; width:600px">',PHP_EOL;
  echo '<p class="small" style="text-align:right">',implode(' &middot; ',$arrMenuType),'</p>';
  echo '<img src="'.$strChart1.'"/><br/>';
  echo '<img src="'.$strChart2.'"/><br/>';
  echo '<img src="'.$strChart3.'"/><br/>';
  echo '</div>',PHP_EOL;

}
elseif ( file_exists('bin/qt_lib_graph.php') && file_exists($_SESSION[QT]['skin_dir'].'/qnm_stats.css') )
{

  echo '<p class="small" style="text-align:right">',implode(' &middot; ',$arrMenuValue),'</p>';

  // Element first serie

  $intTopY = QTroof( array(max($arrT[$y-1]),max($arrT[$y])) );

  if ( $ch['value']=='p' )
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrT[$y-1])),350,100,$intTopY,2,'P',$L['Items_per_'.$ch['time']].' (%)'.' '.($y-1));
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrT[$y-1]),320,100,$intTopY,2,true,$L['Items_per_'.$ch['time']].' '.($y-1));
    echo '</div>',PHP_EOL;
  }

    // Element second serie

  if ( $ch['value']=='p' )
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrT[$y])),350,100,$intTopY,2,'P',$L['Items_per_'.$ch['time']].' (%)'.' '.$y);
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrT[$y]),320,100,$intTopY,2,true,$L['Items_per_'.$ch['time']].' '.$y);
    echo '</div>',PHP_EOL;
  }

  // MESSAGE first serie

  if ( $ch['value']=='p' )
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrM[$y])),350,100,$intTopY,2,'P',$L['Messages_per_'.$ch['time']].' (%)'.' '.$y,'','2');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrM[$y-1]),320,100,$intTopY,2,true,$L['Messages_per_'.$ch['time']].' '.($y-1),'','2');
    echo '</div>',PHP_EOL;
  }

  // MESSAGE second serie

  if ( $ch['value']=='p' )
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrM[$y])),350,100,$intTopY,2,'P',$L['Messages_per_'.$ch['time']].' (%)'.' '.$y,'','2');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrM[$y]),320,100,$intTopY,2,true,$L['Messages_per_'.$ch['time']].' '.$y,'','2');
    echo '</div>',PHP_EOL;
  }

  // CONNECTORS first serie

  $intTopY = QTroof( array(max($arrU[$y-1]),max($arrU[$y])) );

  if ( $ch['value']=='a' )
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrU[$y-1]),320,100,$intTopY,2,true,$L['Connectors_per_'.$ch['time']].' '.($y-1),'','3');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrU[$y-1])),350,100,$intTopY,2,'P',$L['Connectors_per_'.$ch['time']].' (%)'.' '.($y-1),'','3');
    echo '</div>',PHP_EOL;
  }

  // CONNECTORS second serie

  if ( $ch['value']=='a' )
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrU[$y]),320,100,$intTopY,2,true,$L['Connectors_per_'.$ch['time']].' '.$y,'','3');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrU[$y])),350,100,$intTopY,2,'P',$L['Connectors_per_'.$ch['time']].' (%)'.' '.$y,'','3');
    echo '</div>',PHP_EOL;
  }
}
else
{
  echo '<p class="small">Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php, ',$_SESSION[QT]['skin_dir'].'/qnm_stats.css</p>';
}

break;

//--------
case 'd':
//--------

$arrSeries[$L['Actives']]=GetSerie($arrN,$arrNs,$y,$intMaxBt);
$arrSeries[$L['Inactives']]=GetSerie($arrC,$arrCs,$y,$intMaxBt);
$arrSeries[$L['Deleted']]=GetSerie($arrT,$arrTs,$y,$intMaxBt);
$arrSeriesColor = array($L['Actives']=>'#000066',$L['Inactives']=>'#990099',$L['Deleted']=>'#009999');

QTtablechart($arrHeader,$arrSeries,$arrSeriesColor,array('th'=>'th','td'=>'td'),$L['Items']);

// After values display change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrT[$intYear] = QTarrayzero($arrT[$intYear]);
$arrN[$intYear] = QTarrayzero($arrN[$intYear]);
$arrC[$intYear] = QTarrayzero($arrC[$intYear]);
}

// PCHART OR CHART

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') && file_exists('pChart/pCache.class') )
{
  // absciseLabel
  $arrA = GetAbscise($ch['time'],$intMaxBt,$strTendaysago);

  // Standard inclusions and prepare CACHE
  include 'pChart/pData.class';
  include 'pChart/pChart.class';
  include 'pChart/pCache.class';
  $Cache = new pCache('pChart/Cache/'); // variable must be created here (re-used as global in QTpchart function)
  CheckChartCache($Cache);

  // QTpchart(charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul)
  // note: language code is added to the filename to enable refreshing cached-graph when user change language.
  $strChart1 = QTpchart(
    $L['Actives'].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrN[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'1'.$y.implode('',$ch).$lang,
    1);
  $strChart2 = QTpchart(
    $L['Inactives'].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrC[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'2'.$y.implode('',$ch).$lang,
    2);
  $strChart3 = QTpchart(
    $L['Deleted'].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrT[$y] ),
    array( 'Serie1'=>$y ),
    $ch,
    $tt.'3'.$y.implode('',$ch).$lang,
    3);

  // -------
  // DISPLAY
  // -------

  echo '<p class="small" style="text-align:right">',implode(' &middot; ',$arrMenuType),'</p>';

  echo '<table class="hidden">',PHP_EOL;
  echo '<tr class="hidden">',PHP_EOL;
  echo '<td class="hidden"><img src="'.$strChart1.'"/></td>',PHP_EOL;
  echo '<td class="hidden" style="text-align:right"><img src="'.$strChart2.'"/></td>',PHP_EOL;
  echo '</tr>';
  echo '<tr class="hidden">',PHP_EOL;
  echo '<td class="hidden"><img src="'.$strChart3.'"/></td>',PHP_EOL;
  echo '<td class="hidden" style="text-align:right"></td>',PHP_EOL;
  echo '</tr>';
  echo '</table>';

}
elseif ( file_exists('bin/qt_lib_graph.php') && file_exists($_SESSION[QT]['skin_dir'].'/qnm_stats.css') )
{

  echo '<p class="small" style="text-align:right">',implode(' &middot; ',$arrMenuValue),'</p>';

  if ( $ch['value']=='p' )
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrN[$y])),350,100,QTroof(QTpercent($arrN[$y])),2,'P',$L['Actives'].' (%)');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrN[$y]),320,100,QTroof($arrN[$y]),2,true,$L['Actives']);
    echo '</div>',PHP_EOL;
  }

  if ( $ch['value']=='p' )
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrC[$y])),350,100,QTroof(QTpercent($arrC[$y])),2,'P',$L['Inactives'].' (%)','','2');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrC[$y]),320,100,QTroof($arrC[$y]),2,true,$L['Inactives'],'','2');
    echo '</div>',PHP_EOL;
  }


  if ( $ch['value']=='p' )
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrT[$y])),350,100,100,2,'P',$L['Deleted'].' (%)','','3');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrT[$y]),320,100,QTroof($arrT[$y]),2,true,$L['Deleted'],'','3');
    echo '</div>',PHP_EOL;
  }
}
else
{
  echo '<p class="small">Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php, ',$_SESSION[QT]['skin_dir'].'/qnm_stats.css</p>';
}

break;

//--------
case 'dt':
//--------

foreach($arrYears as $y)
{
$arrSeries[$L['Actives']] = GetSerie($arrN,$arrNs,$y,$intMaxBt);
$arrSeries[$L['Inactives']] = GetSerie($arrC,$arrCs,$y,$intMaxBt);
$arrSeriesColor = array($L['Actives']=>($y==$intCurrentYear ? '#000066' : '#00AFFF'),$L['Inactives']=>($y==$intCurrentYear ? '#990099' : '#F1B8FF'));
QTtablechart($arrHeader,$arrSeries,$arrSeriesColor,array('th'=>'th','td'=>'td'),$y);
}

echo '<p class="small" style="text-align:right">',implode(' &middot; ',$arrMenuTrend),'</p>';

$arrSeries[$L['Actives']] = GetSerieDelta($arrN,$arrNs,$y,$intMaxBt,$ch['trend']=='p');
$arrSeries[$L['Inactives']] = GetSerieDelta($arrC,$arrCs,$y,$intMaxBt,$ch['trend']=='p');
$arrSeriesColor = array($L['Actives']=>'',$L['Inactives']=>'');
QTtablechart($arrHeader,$arrSeries,$arrSeriesColor,array('th'=>'th','td'=>'td'),$L['Trends']);

// After values display change the null values to zero to be able to make charts

foreach($arrYears as $intYear)
{
$arrN[$intYear] = QTarrayzero($arrN[$intYear]);
$arrC[$intYear] = QTarrayzero($arrC[$intYear]);
}

// GRAPH

if ( file_exists('pChart/pChart.class') && file_exists('pChart/pData.class') && file_exists('pChart/pCache.class') )
{
  // abscise Label (2 characters)
  $arrA = GetAbscise($ch['time'],$intMaxBt,$strTendaysago);

  // Standard inclusions and prepare CACHE
  include 'pChart/pData.class';
  include 'pChart/pChart.class';
  include 'pChart/pCache.class';
  $Cache = new pCache('pChart/Cache/'); // variable must be created here (re-used as global in QTpchart function)
  CheckChartCache($Cache);

  // QTpchart(charttitle,abscise,dataset,datasetname,graphoptions,filename,color,cumul)
  // note: language code is added to the filename to enable refreshing cached-graph when user change language.
  $strChart1 = QTpchart(
    $L['Actives_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrN[$y-1],'Serie2'=>$arrN[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'1'.$y.implode('',$ch).$lang,
    1);
  $strChart2 = QTpchart(
    $L['Inactives_per_'.$ch['time']].($ch['value']=='p' ? ' (%)' : ''),
    $arrA,
    array( 'Serie1'=>$arrC[$y-1],'Serie2'=>$arrC[$y] ),
    array( 'Serie1'=>$y-1,'Serie2'=>$y ),
    $ch,
    $tt.'2'.$y.implode('',$ch).$lang,
    2);

  // DISPLAY

  echo '<div style="margin:0 auto; width:600px">',PHP_EOL;
  echo '<p class="small" style="text-align:right">',implode(' &middot; ',$arrMenuType),'</p>';
  echo '<img src="'.$strChart1.'"/><br/>';
  echo '<img src="'.$strChart2.'"/><br/>';
  echo '</div>',PHP_EOL;

}
elseif ( file_exists('bin/qt_lib_graph.php') && file_exists($_SESSION[QT]['skin_dir'].'/qnm_stats.css') )
{

  echo '<p class="small" style="text-align:right">',implode(' &middot; ',$arrMenuValue),'</p>';

  // first serie

  $intTopY = QTroof( array(max($arrN[$y-1]),max($arrN[$y])) );

  if ( $ch['value']=='a' )
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrN[$y-1]),320,100,$intTopY,2,true,$L['Actives'].' '.($y-1));
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrN[$y-1])),350,100,100,2,'P',$L['Actives'].' (%) '.($y-1));
    echo '</div>',PHP_EOL;

  }

  // second serie

  if ( $ch['value']=='a' )
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrN[$y]),320,100,$intTopY,2,true,$L['Actives'].' '.$y);
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrN[$y])),350,100,100,2,'P',$L['Actives'].' (%) '.$y);
    echo '</div>',PHP_EOL;
  }

  // 3d serie

  $intTopY = QTroof( array(max($arrC[$y-1]),max($arrC[$y])) );

  if ( $ch['value']=='a' )
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrC[$y-1]),320,100,$intTopY,2,true,$L['Inactives'].' '.($y-1),'','2');
    echo '</div>',PHP_EOL;

  }
  else
  {
    echo '<div class="graph">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrC[$y-1])),350,100,100,2,'P',$L['Inactives'].' (%) '.($y-1),'','2');
    echo '</div>',PHP_EOL;
  }

  // 4th serie

  if ( $ch['value']=='a' )
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,$arrC[$y]),320,100,$intTopY,2,true,$L['Inactives'].' '.$y,'','2');
    echo '</div>',PHP_EOL;
  }
  else
  {
    echo '<div class="graph right">',PHP_EOL;
    QTbarchart(QTarraymerge($arrA,QTpercent($arrC[$y])),350,100,$intTopY,2,'P',$L['Inactives'].' (%) '.$y,'','2');
    echo '</div>',PHP_EOL;
  }
}
else
{
  echo '<p class="small">Graphs cannot be displayed because one of these files is missing: bin/qt_lib_graph.php, ',$_SESSION[QT]['skin_dir'].'/qnm_stats.css</p>';
}

break;

//--------
default:
//--------
die('Invalid tab');
}