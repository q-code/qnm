<?php

/**
* PHP versions 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QNM
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2013 The PHP Group
* @version    1.0 build:20130410
*/

session_start();
require_once 'bin/qnm_init.php';
if ( !$oVIP->user->CanView('A') ) HtmlPage(11);
include 'bin/qnm_fn_sql.php';

$q = '';   // query

QThttpvar('q','str');

if ( get_magic_quotes_gpc() ) $q = stripslashes($q);

// --------
// HTML START
// --------

$oHtml->scripts = array();
include 'qnm_inc_hd.php';


// Dataset (form)

echo '<form id="form_q" method="post" action="checksql.php">
<textarea id="q" name="q" cols="100">'.$q.'</textarea>
<input type="submit" name="ok" value="query"/>
';

echo '
</form>
<br/>
';

// ---------
// SUBMITTED
// ---------

if ( isset($_POST['ok']) )
{
  $q = str_replace('TABSETTING',$qnm_prefix.'qnmsetting',$q);
  $q = str_replace('TABDOMAIN',$qnm_prefix.'qnmdomain',$q);
  $q = str_replace('TABSECTION',$qnm_prefix.'qnmsection',$q);
  $q = str_replace('TABNE',$qnm_prefix.'qnmelement',$q);
  $q = str_replace('TABNC',$qnm_prefix.'qnmconn',$q);
  $q = str_replace('TABNL',$qnm_prefix.'qnmlink',$q);
  $q = str_replace('TABPOST',$qnm_prefix.'qnmpost',$q);
  $q = str_replace('TABDOC',$qnm_prefix.'qnmdoc',$q);

  $oDB->Query( $q );

  echo '<table class="hidden">';N;
  $i=0;
  while($row=$oDB->Getrow())
  {
    if ( $i==0 ) printf( '<tr class="hidden"><td>%s</td></tr>',implode('</td><td>',array_keys($row)) );
    printf( '<tr class="hidden"><td>%s</td></tr>',implode('</td><td>',$row) );
    if ( $i>100 ) break;
    $i++;
  }
  echo '</table>';N;

}

// ---------
// HTML END
// ---------

include 'qnm_inc_ft.php';