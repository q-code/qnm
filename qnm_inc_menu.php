<?php
$strUsermenu='';

switch($oVIP->selfurl)
{

// ----------
case 'qnm_items.php':
// ----------

  // User's preferences (from POST)

  if ( isset($_POST['Mok']) && $_POST['Maction']!=='' )
  {
    $_POST['Maction']=substr($_POST['Maction'],0,4);
    switch($_POST['Maction'])
    {
      case 'asc': $u_dir='asc'; $bChange=true; break;
      case 'desc': $u_dir='desc'; $bChange=true; break;
      case '*': $u_fst='*'; $bChange=true; break;
      case '0': $u_fst='0'; $bChange=true; break;
      case '1': $u_fst='1'; $bChange=true; break;
      case '25': $u_size='25'; $bChange=true; break;
      case '50': $u_size='50'; $bChange=true; break;
      case '100': $u_size='100'; $bChange=true; break;
      default: $bChange=false;
    }
    // Save as coockie and reload page if changed
    if ( $bChange )
    {
      setcookie(QT.'_u_fst', $u_fst, time()+60*60*24*100, '/');
      setcookie(QT.'_u_dir', $u_dir, time()+60*60*24*100, '/');
      setcookie(QT.'_u_size', $u_size, time()+60*60*24*100, '/');
      $_SESSION['pagedialog']='L|'.L('S_preferences');
      $oHtml->redirect(Href().'?'.GetURI('Maction'));
    }
  }

  // Last column (from POST)

  if ( isset($_POST['Mok']) && !empty($_POST['o_last']) ) {
    $u_col = strip_tags(trim($_POST['o_last']));
    setcookie(QT.'_u_col', $u_col, time()+60*60*24*100, '/');
    $_SESSION['pagedialog']='L|'.L('S_preferences');
    $oHtml->redirect(Href().'?'.GetURI('o_last'));
  }

  // USER PREFERENCE Menu

  $arr = array('posts'=>L('Notes'),'status'=>L('Status'),'docs'=>L('Documents')); if ( !empty($_SESSION[QT]['tags']) ) $arr['tags']=L('Tags'); // list of last columns
  $arr['insertdate']=L('Creation_date');
  $strUsermenu .= '
  <div class="options" id="options"><form method="post" action="'.Href().'?'.GetURI('order,dir').'" id="modaction">
  <p>'.L('My_preferences').'
  <select name="Maction" class="small" onchange="document.getElementById(\'action_ok\').click();">
  <option value="">&nbsp;</option>
  <optgroup label="Default order">
  <option value="asc"'.(strtolower($u_dir)==='asc' ? ' class="bold"' : '').'>ID '.L('ascending').'</option>
  <option value="desc"'.(strtolower($u_dir)==='desc' ? ' class="bold"' : '').'>ID '.L('descending').'</option>
  </optgroup>
  <optgroup label="Display">
  <option value="*"'.($u_fst==='*' ? ' class="bold"' : '').'>'.L('Show_all_status').'</option>
  <option value="0"'.($u_fst==='0' ? ' class="bold"' : '').'>'.L('Show_inactives').'</option>
  <option value="1"'.($u_fst==='1' ? ' class="bold"' : '').'>'.L('Show_actives').'</option>
  </optgroup>
  <optgroup label="Page size">
  <option value="25"'.($u_size==='25' ? ' class="bold"' : '').'>25 '.L('items').'/'.L('page').'</option>
  <option value="50"'.($u_size==='50' ? ' class="bold"' : '').'>50 '.L('items').'/'.L('page').'</option>
  <option value="100"'.($u_size==='100' ? ' class="bold"' : '').'>100 '.L('items').'/'.L('page').'</option>
  </optgroup>
  </select> '.L('last_column').'
  <select id="o_last" name="o_last" class="small" onchange="document.getElementById(\'action_ok\').click();">
  <option value="">&nbsp;</option>
  '.QTasTag($arr,'',array('current'=>$u_col,'classC'=>'bold')).'
  </select> <input type="submit" name="Mok" value="'.$L['Ok'].'" class="small" id="action_ok"/></p>
  </form>
  </div>
  <div class="showoptions" onclick="showoptions();" title="'.L('My_preferences').'"></div>
  <script type="text/javascript">
  <!--
  var doc = document;
  doc.getElementById("options").style.display="none";
  doc.getElementById("action_ok").style.display="none";
  doc.getElementById("action_ok").value="";
  function showoptions()
  {
    var doc = document.getElementById("options");
    if ( doc ) doc.style.display=(doc.style.display!="block" ? "block" : "none");
  }
  //-->
  </script>
  ';

  break;

// ----------
case 'qnm_s_search.php':
case 'qnm_search.php':
// ----------

  echo '
  <div class="searchcmd">
  <ul>
  <li><a href="',Href('qnm_items.php'),'?q=last">',AsImg($_SESSION[QT]['skin_dir'].'/ico_topic_t_0.gif','T','','i_note','margin-right:6px'),'<span>',$L['Recent_notes'],'</span></a></li>
  ',($oVIP->user->role=='V' ? '' : '<li><a href="'.Href('qnm_items.php').'?q=user&amp;v='.$oVIP->user->id.'&amp;v2='.urlencode($oVIP->user->name).'">'.AsImg($_SESSION[QT]['skin_dir'].'/ico_user_p_1.gif','T','','i_user','margin-right:6px').'<span>'.L('All_my_notes').'</span></a></li>'),'
  </ul>
  </div>
  ';

  break;

// ----------
case 'qnm_user.php':
// ----------

  if ( $oVIP->user->role=='A')
  {
  $strUsermenu .= '
  <div class="options" id="options">
  <form method="get" action="'.Href('qnm_change.php').'" id="modaction">
  <p>'.$L['Userrole_a'].'
  <select name="a" class="small" onchange="if (this.value!=\'\') { document.getElementById(\'modaction\').submit(); }">
  <option value="">&nbsp;</option>
  <option value="pwdreset">'.L('Reset_pwd').'...</option>'.($id>1 ? '<option value="userrole">'.L('Change_role').'...</option><option value="user_del">'.L('Delete').' '.L('user').'...</option>' :'').'
  </select>
  <input type="submit" name="Mok" value="'.$L['Ok'].'" class="small" id="action_ok" />
  <input type="hidden" name="s" value="'.$id.'" />  </p>
  </form>
  </div>
  <script type="text/javascript">
  <!--
  var doc = document;
  doc.getElementById("action_ok").style.display="none";
  doc.getElementById("action_ok").value="";
  //-->
  </script>
  ';
  }

  break;

// ----------
case 'qnm_users.php':
case 'qnm_adm_users.php':
  if ( $oVIP->user->IsStaff() ) {
// ----------

  // SUBMITTED for add

  if ( isset($_POST['add']) )
  {
    // check
    if ( empty($error) )
    {
      $str = $_POST['title']; if ( get_magic_quotes_gpc() ) $str = stripslashes($str);
      $str = QTconv($str,'U');
      if ( !QTislogin($str) ) $error = $L['Username'].' '.$L['E_invalid'];
      $strTitle = $str;
    }
    if ( empty($error) )
    {
      $oDB->Query('SELECT count(*) as countid FROM '.TABUSER.' WHERE name="'.htmlspecialchars($strTitle,ENT_QUOTES).'"');
      $row = $oDB->Getrow();
      if ($row['countid']!=0) $error = $L['Username'].' '.$L['E_already_used'];
    }
    if ( empty($error) )
    {
      $str = $_POST['pass']; if ( get_magic_quotes_gpc() ) $str = stripslashes($str);
      $str = QTconv($str,'U');
      if ( !QTispassword($str) ) $error = $L['Password'].' '.$L['E_invalid'];
      $strNewpwd = $str;
    }
    if ( empty($error) )
    {
      $str = $_POST['mail']; if ( get_magic_quotes_gpc() ) $str = stripslashes($str);
      $str = QTconv($str,'U');
      if ( !QTismail($str) ) $error = $L['Email'].' '.$L['E_invalid'];
      $strMail = $str;
    }
    // save
    if ( empty($error) )
    {
      include 'bin/class/qt_class_smtp.php';

      // Add user
	  cVIP::AddUser(htmlspecialchars($strTitle,ENT_QUOTES),sha1($strNewpwd),$strMail);

      // Unregister global sys (will be recomputed on next page)
      Unset($_SESSION[QT]['sys_members']);
      Unset($_SESSION[QT]['sys_newuserid']);

      // send email
      if ( isset($_POST['notify']) )
      {
        $strSubject='Welcome';
        $strMessage='Please find here after your login and password to access the board '.$_SESSION[QT]['site_name'].PHP_EOL.'Login: %s\nPassword: %s';
        $strFile = GetLang().'mail_registred.php';
        if ( file_exists($strFile) ) include $strFile;
        $strMessage = sprintf($strMessage,$strTitle,$strNewpwd);
        QTmail($strMail,QTconv($strSubject,'-4'),QTconv($strMessage,'-4'),QNM_HTML_CHAR);
      }

      // exit
      unset($_POST['pass']);
    }
    $_SESSION['pagedialog'] = (empty($error) ? 'O|'.$L['Register_completed'] : 'E|'.$error);
  }

  $strUsermenu .= '<a onclick="ToggleForms(); return false;" href="#">'.L('User_add').'...</a>';
  $oHtml->scripts[] = '<script type="text/javascript">
  <!--
  function ValidateForm(theForm)
  {
    if (theForm.title.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Username'].'")); return false; }
    if (theForm.pass.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Password'].'")); return false; }
    if (theForm.mail.value.length==0) { alert(qtHtmldecode("'.$L['Missing'].': '.$L['Email'].'")); return false; }
    return null;
  }

  function ToggleForms()
  {
    if ( document.getElementById("adduser").style.display=="none" )
    {
    document.getElementById("adduser").style.display="block";
    }
    else
    {
    document.getElementById("adduser").style.display="none";
    }
  }
  //-->
  </script>';
  $oHtml->scripts_end[] = '<script type="text/javascript">
  <!--
  $(function() {

    // Ajax on username

    $("#title").blur(function() {
      $.post("qnm_j_exists.php",
             {f:"name",v:$("#title").val(),e1:"'.$L['E_min_4_char'].'",e2:"'.$L['E_already_used'].'"},
             function(data) { if ( data.length>0 ) document.getElementById("formerror").innerHTML=data; }
             );
      });

  });
  //-->
  </script>';
  $strUserform  = '
  <form id="adduser" style="margin:5px 0 15px 0" method="post" action="'.$oVIP->selfurl.'" onsubmit="return ValidateForm(this);">
  <table class="data_o">
  <tr class="data_o"><td class="headfirst">'.$L['Role'].'</td><td><select name="role" size="1">'.($oVIP->user->role=='A' ? '<option value="A">'.$L['Userrole_a'].'</option>' : '').'<option value="M">'.$L['Userrole_m'].'</option><option value="U"'.QSEL.'>'.$L['Userrole_u'].'</option></select></td></tr>
  <tr class="data_o"><td class="headfirst">'.$L['Username'].'</td><td><input id="title" name="title" type="text" size="12" maxlength="24" value="'.(isset($_POST['title']) ? $_POST['title'] : '').'" onfocus="document.getElementById(\'formerror\').innerHTML=\'\';" /></td></tr>
  <tr class="data_o"><td class="headfirst">'.$L['Password'].'</td><td><input id="pass" name="pass" type="text" size="12" maxlength="32"  value="'.(isset($_POST['pass']) ? $_POST['pass'] : '').'" /></td></tr>
  <tr class="data_o"><td class="headfirst">'.$L['Email'].'</td><td><input id="mail" name="mail" type="email" size="24" maxlength="255"  value="'.(isset($_POST['mail']) ? $_POST['mail'] : '').'" /></td></tr>
  <tr class="data_o"><td class="headfirst" colspan="2"><span id="formerror" class="error">'.(empty($error) ? '' : $error).'</span> <input id="notify" name="notify" type="checkbox" /> <label for="notify">'.$L['Send'].' '.L('email').'</label>&nbsp; <input type="submit" id="add" name="add" value="'.$L['Add'].'" /></td></tr>
  </table>
  </form>
  ';
  if ( !isset($_POST['title']) ) $strUserform .= '<script type="text/javascript">ToggleForms();</script>';

  }
  break;

// ----------
case 'qnm_form_link_e.php':
case 'qnm_form_link_c.php':
// ----------

  // Last column (from POST)

  if ( isset($_POST['Mok']) && !empty($_POST['o_last']) ) {
    $u_col = strip_tags(trim($_POST['o_last']));
    setcookie(QT.'_u_col', $u_col, time()+60*60*24*100, '/');
    $_SESSION['pagedialog']='L|'.L('S_preferences');
    $oHtml->redirect(Href().'?'.GetURI('o_last'));
  }

  // USER PREFERENCE Menu

  $arr = array('posts'=>L('Notes'),'status'=>L('Status'),'docs'=>L('Documents')); if ( !empty($_SESSION[QT]['tags']) ) $arr['tags']=L('Tags'); // list of last columns
  $arr['insertdate']=L('Creation_date');
  $strUsermenu .= '
  <div class="options" id="options"><form method="post" action="'.Href().'?'.GetURI('order,dir').'" id="modaction">
  <p>'.L('last_column').'
  <select id="o_last" name="o_last" class="small" onchange="document.getElementById(\'action_ok\').click();">
  <option value="">&nbsp;</option>
  '.QTasTag($arr,'',array('current'=>$u_col,'classC'=>'bold')).'
  </select> <input type="submit" name="Mok" value="'.$L['Ok'].'" class="small" id="action_ok"/></p>
  </form>
  </div>
  <div class="showoptions" onclick="showoptions();" title="'.L('My_preferences').'"></div>
  <script type="text/javascript">
  <!--
  var doc = document;
  doc.getElementById("options").style.display="none";
  doc.getElementById("action_ok").style.display="none";
  doc.getElementById("action_ok").value="";
  function showoptions()
  {
    var doc = document.getElementById("options");
    if ( doc ) doc.style.display=(doc.style.display!="block" ? "block" : "none");
  }
  //-->
  </script>
  ';

  break;

}