<?php

/* ============
 * qt_class_db.php
 * ------------
 * version: 4.2 build:20121218
 * This is a library of public class
 * ------------
 * CLASS cDB
 *  cDB()
 *  Connect()
 *  Disconnect()
 *  Getrow()
 *  Halt()
 * ============ */

class cDB
{

public $type; // server type
public $db;   // database name
public $host; // server host name
public $port; // server port number
public $dsn;  // dsn name (or FALSE)
public $user; // username
public $pwd;  // userpassword
public $ip;   // user current ip
public $sql;  // sql string
public $con;  // connection id
public $qry;  // query id
public $error = '';
public $debug = false;
public $stats;

/*
 * Constructor of class - Initializes class and connects to the database
 *
 * database types are:
 * mysql   MySQL >=5.0 (mysql4 still valid)
 * sqlsrv  Microsoft SQL Server (microsoft driver)
 * mssql   Microsoft SQL Server (old php driver)
 * ibase   InterBase FireBird
 * sqlite  SQLite
 * pg      PostgreSQL
 * db2     Ibm db2
 */

function __construct($strType,$strHost,$strDb,$strUser,$strPwd,$strPort=false,$strDsn=false,$bStats=false)
{
  if ( $bStats )
  {
    $this->stats = array('num'=>0,'start'=>(float)(vsprintf('%d.%06d', gettimeofday())),'end'=>0);
  }

  $this->type  = strtolower($strType);
  $this->host  = $strHost;
  $this->db    = $strDb;
  $this->user  = $strUser;
  $this->ip    = $_SERVER['REMOTE_ADDR'];
  $this->pwd   = $strPwd;
  $this->port  = $strPort;
  $this->dsn   = $strDsn;
  // check type
  if ( !in_array($this->type,array('mysql','mysql4','sqlsrv','mssql','pg','ibase','sqlite','db2','oci')) )
  {
    die('db_type ['.$this->type.'] not supported. Must be "mysql","sqlsrv","mssql","pg","ibase","sqlite","db2" or "oci"');
  }

  // Connect
  return $this->Connect();
}

/*
 * This function connects the database
 * @return boolean $is_connected Returns true if connection was successful otherwise false
 * @desc This function connects to the database which is set in the constructor
 */
function Connect()
{
  // check already connected
  if ( $this->con!='' )
  {
    $this->error = 'Already connected to database.';
    return $this->Halt(false);
  }

  // Selecting connection function and connecting
  if ( $this->dsn )
  {
    $this->con = odbc_connect($this->dsn,$this->user,$this->pwd);
  }
  else
  {
    switch($this->type)
    {
    case 'mysql4':
    case 'mysql':
      $strPort = '';
      if ( $this->port ) $strPort = ':'.$this->port;
      $this->con = mysql_connect($this->host.$strPort,$this->user,$this->pwd);
      break;
    case 'pg':
      $strPort = '';
      if ( $this->port ) $strPort = ' port='.$this->port;
      $this->con = pg_connect('host='.$this->host.$strPort.' dbname='.$this->db.' user='.$this->user.' password='.$this->pwd);
      break;
    case 'ibase':
      $this->con = ibase_connect($this->host.':'.$this->db,$this->user,$this->pwd);
      break;
    case 'sqlite':
      $this->con = sqlite_open($this->db,0666,$e) or die($e);
      if ( !$this->con ) echo($e);
      break;
	case 'sqlsrv':
	  $arr=array('Database'=>$this->db,'UID'=>$this->user,'PWD'=>$this->pwd);
	  // use windows authentication if no UID and no PWD
	  if ( empty($this->user) && empty($this->pwd) ) $arr=array('Database'=>$this->db);
	  $this->con = sqlsrv_connect($this->host,$arr);
	  break;
	case 'mssql': $this->con = mssql_connect($this->host,$this->user,$this->pwd); break;
    case 'db2':   $this->con = db2_connect($this->host,$this->user,$this->pwd); break;
    case 'oci':   $this->con = oci_connect($this->user,$this->pwd,$this->db); break;
    default: die('db_type ['.$this->type.'] not supported.');
    }
  }

  if ( !$this->con )
  {
    $this->error = 'Wrong connection parameters! Cannot establish connection to host.';
    return $this->Halt();
  }

  // selection database (if required)
  if ( $this->dsn )
  {
    return true;
  }
  else
  {
    switch ($this->type)
    {
    case 'mysql4':
    case 'mysql': if ( mysql_select_db($this->db,$this->con) ) return true; break;
    case 'mssql': if ( mssql_select_db($this->db,$this->con) ) return true; break; // no select with sqlsrv
    case 'db2':   if ( db2_select_db($this->db,$this->con) ) return true; break;
    default: return true;
    }
    $this->error = 'Wrong database parameters! Cannot select database.';
    return $this->Halt();
  }
}

// ---------

function Disconnect()
{
  if ( $this->dsn ) { odbc_close($this->con); return true; }

  switch($this->type)
  {
  case 'mysql4':
  case 'mysql': mysql_close($this->con); break;
  case 'mssql': mssql_close($this->con); break;
  case 'sqlsrv': sqlsrv_close($this->con); break;
  case 'pg': pg_close($this->con); break;
  case 'ibase': ibase_close($this->con); break;
  case 'sqlite': sqlite_close($this->con); break;
  case 'db2': db2_close($this->con); break;
  case 'oci': oci_close($this->con); break;
  default: die('db_type ['.$this->type.'] not supported.');
  }
  return true;
}

// ---------

function Query($strSql)
{
  $this->sql = $strSql;
  if ( $this->debug || isset($_SESSION['QTdebugsql']) ) printf('<p class="small" style="margin:1px">SQL: %s</p>',$this->sql);

  if ( $this->dsn || $this->type=='pg' || $this->type=='oci' || $this->type=='sqlsrv') $this->sql = str_replace('"',"'",$this->sql);

  if ( $this->dsn )
  {
    $this->qry = odbc_exec($this->con,$this->sql);
  }
  else
  {
    switch($this->type)
    {
    case 'mysql': $this->qry = mysql_query($this->sql,$this->con); break;
    case 'mssql': $this->qry = mssql_query($this->sql,$this->con); break;
    case 'sqlsrv': $this->qry = sqlsrv_query($this->con,$this->sql); break;
    case 'pg': $this->qry = pg_query($this->con,$this->sql); break;
    case 'ibase': $this->qry = ibase_query($this->con,$this->sql); break;
    case 'sqlite':$this->qry = sqlite_query($this->con,$this->sql); break;
    case 'db2': $this->qry = db2_query($this->con,$this->sql); break;
    case 'oci': $this->qry = oci_parse($this->con,$this->sql); oci_execute($this->qry); break;
    case 'mysql4': $this->qry = mysql_query($this->sql,$this->con); break;
    default: die('db_type ['.$this->type.'] not supported.');
    }
  }

  if ( isset($this->stats) )
  {
    $this->stats['num']++;
  }

  if ( !$this->qry ) return $this->Halt();
  return true;
}

// --------

function Nextid($table='',$field='id',$strWhere='')
{
  if ( !is_string($table) || empty($table) ) die('cDB->Nextid: argument #1 must be a string');
  if ( !is_string($field) || empty($field) ) die('cDB->Nextid: argument #2 must be a string');
  if ( !is_string($strWhere) ) die('cDB->Nextid: argument #3 must be a string');
  $this->Query("SELECT max($field)+1 as newnum FROM $table $strWhere");
  $row = $this->Getrow();
  $i = $row['newnum'];
  if ( empty($i) ) $i=1;
  return intval($i);
}

// --------
// attention: odbc can return fieldnames in uppercase
// attention: sqlite can return fieldname including the prefix alias (p.id)

function Getrow()
{
  $row = false;
  if ( $this->dsn )
  {
    if ( odbc_fetch_row($this->qry) )
    {
      for ($i=1;$i<=odbc_num_fields($this->qry);$i++)
      {
      $strName=strtolower(odbc_field_name($this->qry,$i));
      $row[$strName]=odbc_result($this->qry,$i);
      }
    }
  }
  else
  {
    switch ($this->type)
    {
    case 'mysql': $row = mysql_fetch_assoc($this->qry); break; // php 5.0.3
    case 'sqlsrv':$row = sqlsrv_fetch_array($this->qry,SQLSRV_FETCH_ASSOC); break;
    case 'mssql':
      $row = mssql_fetch_assoc($this->qry);
      // this fix a known bug in mssql_fetch_assoc that add a space to empty string
      if ( is_array($row) )
      {
        foreach($row as $strKey=>$oValue)
        {
          if ( is_string($oValue) ) {
          if ( strlen($oValue)==1 ) {
          $row[$strKey] = trim($oValue);
          }}
        }
      }
      break;
    case 'pg': $row = pg_fetch_assoc($this->qry); break;// php 4.3.0
    case 'ibase': $row = ibase_fetch_assoc($this->qry); break;// php 4.3.0
    case 'sqlite':
      $row = sqlite_fetch_array($this->qry,SQLITE_ASSOC);// php 5.0
      if ( $row===false ) return false;
      $arr = array();
      foreach($row as $strKey=>$oValue)
      {
        if ( substr($strKey,1,1)=='.') $strKey = strtolower(substr($strKey,2));
        $arr[$strKey]=$oValue;
      }
      $row = $arr;
      break;
    case 'db2': $row = db2_fetch_assoc($this->qry); break; // php unknown version
    case 'oci':
      $row = oci_fetch_assoc($this->qry);
      if ( $row===false ) return false;
      $arr = array();
      foreach($row as $strKey=>$oValue)
      {
        $arr[strtolower($strKey)]=$oValue;
      }
      $row = $arr;
      break;
    case 'mysql4': $row = mysql_fetch_assoc($this->qry); break; // php 4.0.3
    default: die('db_type ['.$this->type.'] not supported.');
    }
  }
  return $row;
}

// --------

function Halt($bShowDbError=true,$bStop=false)
{
  if ( $bShowDbError )
  {
    switch($this->type)
    {
    case 'mysql4':
    case 'mysql': echo 'Error ',mysql_errno(),': ',mysql_error(); break;
    case 'sqlsrv': $err=end(sqlsrv_errors());  echo 'Error '.$err['message']; break;
    case 'mssql': echo 'Error: ',mssql_get_last_message(); break;
    case 'pg':    echo 'Error: ',pg_last_error(); break;
    case 'ibase': echo 'Error: ',ibase_errmsg(); break;
    case 'sqlite': echo 'Error: ',sqlite_last_error($this->con),': ',sqlite_error_string(sqlite_last_error($this->con)); break;
    case 'db2':   echo 'Error: ',db2_conn_errormsg(); break;
    case 'oci':   $e=oci_error();  echo 'Error: ',$e['message']; break;
    }
  }
  if ( !empty($this->error) ) echo '<br/>'.$this->error;
  if ( $bStop ) exit;
  return false;
}

// --------

}