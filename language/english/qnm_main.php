<?php
if ( !defined('QNM_HTML_DTD') ) define ('QNM_HTML_DTD', '<!DOCTYPE html>'); // html 5
if ( !defined('QNM_HTML_CHAR') ) define ('QNM_HTML_CHAR', 'UTF-8');
if ( !defined('QNM_HTML_DIR') ) define ('QNM_HTML_DIR', 'ltr');
if ( !defined('QNM_HTML_LANG') ) define ('QNM_HTML_LANG', 'en');

// It is recommended to always use capital on first letter in the translation, script changes to lower case if necessary.
// It is recommended to use html entities for accent or special characters
// When lowercase uses accent as first lettre, you can declare it to overwrite the default lowercase feature.
// The character doublequote ["] is FORBIDDEN (reserved for html tags)
// To make a single quote use slash [\']

// -------------
// TOP LEVEL VOCABULARY
// -------------

// Use the top level vocabulary to give the most appropriate name
// for the items (object elements) managed by this application.
// e.g. Element, Asset, Object,...

$L['Item']='Element';   $L['item']='element'; // 'Item' is the only word where lowercase definition is required
$L['Items']='Elements'; $L['items']='elements';
$L['Group']='Group';
$L['Groups']='Groups';
$L['Sub-item']='Sub-'.$L['item'];
$L['Sub-items']='Sub-'.$L['items'];
$L['No_sub-item']='no sub-'.$L['item'];
$L['Item_add']='New '.$L['item'];
$L['Item_upd']='Update '.$L['item'];
$L['Line']='Line';
$L['Lines']='Lines';
$L['Connector']='Connector';
$L['Connectors']='Connectors';
$L['H_Item_g']='Use this to create container elements (rooms, stations or any group of equipments). A '.$L['Group'].' can contains '.$L['Sub-items'].', but no connector.';
$L['H_Item_e']='Use this to create equipments, devices. A network '.$L['item'].' can contains sub-'.$L['items'].' or connectors.';
$L['H_Item_l']='Use this to create cables, pipes or routes. A network line can contains connectors (at each end).';
$L['H_Item_c']='Used this to create sockets, plugger or line end-nodes. A network connector only exists inside an '.$L['item'].' or a line, and cannot contains sub-'.$L['item'].'.';
$L['Relation']='Relation';
$L['Relations']='Relations';
$L['No_relation']='No relation';
$L['Edit_relations']='Edit relations';
$L['Contained_items']='Contained '.$L['items'];

$L['Direction']='Direction';
$L['Direction_specific']='Specific direction';
$L['Direction_multiple']='Multiple directions';
$L['Direction0']='Undefined';
$L['Direction1']='Direct';
$L['Direction2']='Bidirectional';
$L['Direction-1']='Reverse';
$L['Direction3']='Not undefined';
$L['Direction4']='Not direct';
$L['Direction5']='Not bidirectional';
$L['Direction6']='Not reverse';
$L['Not_class_e']='Not '.$L['item'];
$L['Not_class_l']='Not line';
$L['Not_class_c']='Not connector';

$L['Class_specific']='Specific class';
$L['Class_multiple']='Multiple classes';
$L['Exactly']='Exactly';
$L['Any']='Any';

// The top level words (are re-used here after)

$L['I']='(?)'; // help-info symbol
$L['Y']='Yes';
$L['N']='No';
$L['Ok']='Ok';
$L['Id']='Id';
$L['M']='M'; // field M

// Specific vocabulary

$L['Class']='Class';
  $L['Classes']='Classes';
$L['Domain']='Domain';
  $L['Domains']='Domains';
$L['Section']='Section';
  $L['Sections']='Sections';
$L['User']='User';
  $L['Users']='Users';
  $L['User_add']='New user';
  $L['User_del']='Delete user';
  $L['User_upd']='Edit profile';
$L['Status']='Status';
  $L['Statuses']='Statuses';
  $L['Status_add']='New status';
  $L['Status_upd']='Edit status';
$L['Message']='Note';
  $L['Messages']='Notes';
  $L['First_message']='First note';
  $L['Last_message']='Last note';
$L['Forward']='Forward';
  $L['Forwards']='Forwards';
$L['Link']='Link';
  $L['Links']='Links';
  $L['Links_as_relation']='Is involved in';
  $L['Links_as_parent']='Is inside a parent';
  $L['Plugged']='Plugged';
$L['In_process']='In process';
  $L['Set_in_process']='In process';
  $L['In_process_note']='Note in process';
  $L['In_process_notes']='Notes in process';
  $L['Closed_note']='Closed note';
  $L['Closed_notes']='Closed notes';
$L['Field']='Field';
$L['Fields']='Fields';
$L['Notify_also']='Notify also';
$L['Drop_attachment']='Drop attachment';
$L['Username']='Username';
$L['Role']='Role';
  $L['Userrole_a']='Administrator'; $L['Userrole_as']='Administrators';
  $L['Userrole_m']='Staff member';  $L['Userrole_ms']='Staff members';
  $L['Userrole_u']='User';          $L['Userrole_us']='Users';
  $L['Userrole_v']='Visitor';       $L['Userrole_vs']='Visitors';
  $L['Userrole_c']='Section coordinator'; $L['Userrole_cs']='Section coordinators';
$L['Joined']='Joined';
$L['Avatar']='Photo';
$L['Signature']='Signature';
$L['Modified_by']='Modified by';
$L['Deleted_by']='Deleted by';
$L['Top_participants']='Top participants';
$L['Register_completed']='Registration completed...';

// User preference and Table top commands

$L['My_preferences']='My preferences';
$L['Ascending']='Ascending';
$L['Descending']='Descending';
$L['Show_all_status']='Show all status';
$L['Show_inactives']='Show inactives';
$L['Show_actives']='Show actives';
$L['Last_column']='Last column';
$L['Schematic_view']='Schematic view';
$L['Detailed_lists']='Detailed lists';
$L['Show_large_box']='Large schemas';
$L['Show_small_box']='Short schemas';
$L['View_compact']='Compact view';
$L['View_large']='Large view';
$L['Edit_patching']='Edit patching';

$L['Activate']='Activate';
  $L['cmd_Activate']='Activate selected '.$L['items'];
$L['Add_note']='Add&nbsp;note';
  $L['cmd_Add_note']='Add a new comment for the selected '.$L['items'];
  $L['cmd_Delete']='Delete selected '.$L['items'];
  $L['cmd_Delete_help']='('.$L['items'].' are unlinked, connectors are deleted)';
$L['Inactivate']='Inactivate';
  $L['cmd_Inactivate']='Disabled selected '.$L['items'];
$L['Create_relations']='Add relations';
$L['Remove_relations']='Remove relations';
  $L['cmd_Remove_relations']='Remove the selected relations';
$L['More']='More';
  $L['cmd_More']='Change type, move...';
$L['Change_status']='Change&nbsp;status';
  $L['cmd_Change_status']='Change status of the selected '.$L['items'];
$L['Change_type']='Change&nbsp;type';
  $L['cmd_Change_type']='Change type of the selected '.$L['items'];
  $L['cmd_Edit_links']='Link/Unlink '.$L['items'].', change statuses or directions';
  $L['cmd_Edit_content']='Add/remove existing sub-'.$L['items'].', change statuses';
$L['Change_descr']='Change&nbsp;description';
  $L['cmd_Change_descr']='Change description of the selected '.$L['items'];
$L['Show']='Show';
$L['All_types']='All types';
$L['All_statuses']='All statuses';

// Common

$L['Action']='Action';
$L['Active']='Active';   $L['Inactive']='Inactive';
$L['Actives']='Actives'; $L['Inactives']='Inactives';
$L['Add']='Add';
$L['Add_inside']='Add inside';
$L['Add_categories']='Add categories';
$L['Add_selected']='Add selected';
$L['Address']='Address';
$L['All']='All';
$L['And']='And';
$L['Attachment']='Attachment';
$L['Author']='Author';
$L['Back']='Back';
$L['Birthday']='Date of birth';
$L['Birthdays_calendar']='Birthdays calendar';
$L['By']='By';
$L['By_date']='By date';
$L['Change']='Change';
$L['Change_address']='Change address';
$L['Change_name']='Change username';
$L['Changed']='Changed';
$L['Close']='Close';
$L['Closed']='Closed'; // closed notes
$L['Contact']='Contact';
$L['Contains']='Contains';
$L['Containing']='Containing';
$L['Content']='Content';
$L['Continue']='Continue';
$L['Coord']='Coordinates';
$L['Coord_latlon']='(lat,lon)';
$L['Csv']='Export'; $L['H_Csv']='Download to spreadsheet';
$L['Create']='Create';
$L['Created']='Created';
$L['Creation_date']='Creation date';
$L['Date']='Date';
$L['Dates']='Dates';
$L['Day']='Day';
$L['Days']='Days';
$L['Delete']='Delete';
$L['Deleted']='Deleted';
$L['Delete_tags']='Delete (click a word or type * to delete all)';
$L['Descr']='Description'; // field name
$L['Description']='Description';
$L['Destination']='Destination';
$L['Details']='Details';
$L['Display_at']='Display at date';
$L['Documents']='Documents';
$L['Document_add']='File';
$L['Document_name']='Name';
$L['Edit']='Edit';
$L['Email']='E-mail'; $L['No_Email']='No e-mail';
$L['Exit']='Exit';
$L['Favorites']='Favorites';
$L['File']='File';
$L['First']='First';
$L['Free']='Free';
$L['Goodbye']='You are disconnected... Goodbye';
$L['Goto']='Jump to';
$L['Having']='Having';
$L['H_Website']='Url of your website (with http://)';
$L['Help']='Help';
$L['Hidden']='Hidden';
$L['In']='In';
$L['I_wrote']='I wrote';
$L['Information']='Information';
$L['In_process_first']='In process first';
$L['Items_per_month']='Elements per month';
$L['Items_per_month_cumul']='Cumulative '.$L['items'].' per month';
$L['Last']='Last';
$L['Legend']='Legend';
$L['Location']='Location';
$L['Maximum']='Maximum';
$L['Minimum']='Minimum';
$L['Missing']='Missing information';
$L['Modified']='Modified';
$L['More_criterias']='More criterias';
$L['Month']='Month';
$L['Move']='Move';
$L['Move_to_section']='Move to section';
$L['Next']='Next';
$L['None']='None';
$L['Notification']='Notification';
$L['of']='of';
$L['Open']='Open';
$L['Options']='Options';
$L['Or']='Or';
$L['Others']='Others';
$L['Page']='Page';
$L['Pages']='Pages';
$L['Password']='Password';
$L['Picture']='Picture';
$L['Phone']='Phone';
$L['Preferences']='Preferences';
$L['Preview']='Preview';
$L['Previous']='Previous';
$L['Privacy']='Privacy';
$L['Properties']='Properties';
$L['Reason']='Reason';
$L['Remove']='Remove';
$L['Rename']='Rename';
$L['Result']='Result';
$L['Results']='Results';
$L['Role']='Role';
$L['Save']='Save';
$L['Search_results']='Search results';
$L['Seconds']='Seconds';
$L['Select']='Select';
$L['Selected_from']='Selected from';
$L['Send']='Send';
$L['Send_on_behalf']='Send on behalf of';
$L['Show_more_notes']='Show more notes';
$L['Sort']='Sort';
$L['State']='State';
$L['Statistics']='Statistics';
$L['Style']='Style';
$L['Tag']='Category';
$L['Tags']='Categories';
$L['Tags_add']='Add categories';
$L['Tags_remove']='Remove categories';
$L['Time']='Time';
$L['Title']='Title';
$L['Today']='Today';
$L['Total']='Total';
$L['Type']='Type';
$L['Undefined']='Undefined';
$L['Unknown']='Unknown';
$L['Url']='Url';
$L['Website']='Website'; $L['No_Website']='No website';
$L['Welcome']='Welcome';
$L['Welcome_to']='We welcome a new user, ';
$L['Welcome_not']='I\'m not %s !';
$L['Year']='Year';

// Menu

$L['FAQ']='FAQ';
$L['Search']='Search';
$L['Memberlist']='Userlist';
$L['Login']='Log&nbsp;in';
$L['Logout']='Log&nbsp;out';
$L['Register']='Register';
$L['Profile']='Profile';
$L['Administration']='Administration';
$L['Legal']='Legal notices';

// Section // use &nbsp; to avoid double ligne buttons

$L['Create_items']='Create&nbsp;'.$L['items'];
$L['Create_sub-items']='Create&nbsp;sub-'.$L['items'];
$L['Create_items_in']='Create&nbsp;'.$L['items'].'&nbsp;in';
$L['Create_connectors']='Create&nbsp;connectors';
$L['Goto_message']='[<b>&raquo;</b>]';
$L['H_Goto_message']='View last note';
$L['Previous_notes']='Previous notes';
$L['Edit_start']='Start editing';
$L['Edit_stop']='Stop editing';
$L['Item_closed_show']='Show closed '.$L['items'];
$L['Item_closed_hide']='Hide closed '.$L['items'];

// creation date criteria

$L['Date_on']='On';
$L['Date_near']='Near';
$L['Date_before']='Before';
$L['Date_after']='After';
$L['H_datesearch']='You can enter a day (yyyy-mm-dd), a month (yyyy-mm) or a year (yyyy).<br/>
When searching near a day, you will search for elements created near this week (between day-3 and day+3). When searching near a month, you will search for elements created near this quarter (between month-1 and month+1). When searching near a year, you will search for elements created between year-1 and year+1.';

// Editing

$L['Select...']='Select...';
$L['f_add_ne_id']='add %s to create several '.$L['items'];
$L['f_add_ne_mirror']='create mirror connectors with the same id (as line end-connectors)';
$L['f_add_ne_using']=$L['items'].' using %s';
$L['f_add_ne_starting']='starting from';
$L['f_add_ne_number']='number of '.$L['items'];
$L['f_add_ne_az']='this requires [a-z] characters';
$L['f_add_ne_int']='this requires a number';
$L['Change_insertdate']='Change creation date';

$L['f_info_delete']='Sub-connectors are also deleted. Sub-'.$L['items'].' are not deleted.';
$L['f_info_replace']='Existing values will be replaced.';
$L['f_info_add_note']='The note will be added to each '.$L['item'].'.';
$L['f_info_add_tags']='You can add several categories separated by '.QNM_QUERY_SEPARATOR.'<br/>The proposed categories are those used in the section of the selected '.$L['item'].'(s).';
$L['f_info_remove_tags']='You can remove several categories separated by '.QNM_QUERY_SEPARATOR.' or remove all categories by using *<br/>The proposed categories are those from the selected '.$L['item'].'(s).';
$L['f_warning_delete']='warning, your are deleting network '.$L['items'];
$L['f_Also_sub_items']='Also for sub-'.$L['items'];
$L['f_Close_all_notes']='Close all notes';
$L['f_Delete_all_closed_notes']='Delete all closed notes';

$L['f_Search_other_section']='Search other '.$L['items'].' from section';
$L['f_Show_only_type']='Show only type';
$L['f_Enter_id']='Enter an id or a part of an id';
$L['f_Add_direction']='Add selected with direction';
$L['f_Add_parent']='(P) indicates '.$L['item'].' already inside a parent. Adding it will change his parent.';
$L['Confirm_delete_notes']='You are deleting note(s). Continue?';

// Search

$L['Recent_notes']='Recent&nbsp;notes';
$L['All_my_notes']='All&nbsp;my&nbsp;notes';
$L['Advanced_search']='Advanced search';
$L['H_Search']='(Id or type name)';
$L['H_Search_criteria']='You can enter several values separated by '.QNM_QUERY_SEPARATOR.' (ex.: c1,c2 means '.$L['items'].' "c1" or "c2").';
$L['Search_options']='Search options';
$L['Search_criteria']='Search network '.$L['items'];
$L['Search_by_key']='With note containing word(s)';
$L['Search_by_id']='Search '.$L['item'].' by Id';
$L['Search_by_status']='Search by status';
$L['Search_by_tag']='Search by categories';
$L['Search_by_field']='Search in field';

$L['Search_result']='Search result';
$L['All_sections']='All sections';
$L['Only_notes_in_process']='Only notes in process';
$L['Any_type']='Any type';
$L['Any_status']='Any status';
$L['Too_many_keys']='Too many keys';
$L['Search_by_words']='Search each word separately';
$L['Search_exact_words']='Search exact words';
$L['Search_by_date']='Search by date';
$L['This_week']='This week';
$L['This_month']='This month';
$L['This_year']='This year';
$L['With_tag']= 'Category';
$L['Show_only_tag']='Some '.$L['items'].' have following categories:<br/>(click to search '.$L['items'].' having this category)';

// Search result

$L['Search_results_id']='%s '.$L['items'].' with id %s';
$L['Search_results_keyword']='%s '.$L['items'].' having notes containing %s';
$L['Search_results_user']='%s notes issued by %s';
$L['Search_results_last']='%s recent notes (last week)';
$L['Search_results_field']='%s results for %s';
$L['Search_results_date']='%s '.$L['items'].' with creation date ';
$L['Only_in_section']='Only in section';
$L['Having_typename_containing']='Having type name containing';
$L['Only_status']='Only status';

// Ajax helper

$L['All_categories']='All categories';
$L['Category_not_yet_used']='Category not yet used';
$L['Impossible']='Impossible';
$L['No_result']='No result';
$L['Try_other_lettres'] = 'Try other lettres';
$L['Try_without_options'] = 'Try without options';

// Privacy

$L['Privacy_visible_0']='Not visible';
$L['Privacy_visible_1']='Visible for members only';
$L['Privacy_visible_2']='Visible for all';

// Restrictions

$L['R_member']='Access is restricted to members only.<br/><br/>Please log in, or proceed to registration to become member.';
$L['R_staff']='Access is restricted to staff members only. <a href="qnm_index.php">Exit</a>';
$L['R_security']='Security settings does not allow using this function.';

// Errors

$L['No_item']='No '.$L['item'].' found';
$L['No_selected_row']='At least one '.$L['item'].' must be selected.\nTo select an '.$L['item'].', check the box at the beginning of the row.\nTo select several '.$L['items'].', you can also use SHIFT+click.';
$L['E_access']='Access denied...';
$L['E_already_used']='Already used';
$L['E_char_max']='(maximum %d characters)';
$L['E_editing']='Data not yet saved. Quit without saving?';
$L['E_file_size']='File is too large';
$L['E_invalid']='invalid';
$L['E_javamail']='Protection: java required to see e-mail addresses';
$L['E_line_max']='(maximum %d lines)';
$L['E_min_4_char']='Minimum 4 characters';
$L['E_missing_http']='The url must start with http:// or https://';
$L['E_missing_items']='Missing number of '.$L['items'];
$L['E_no_desc']='No description';
$L['E_no_public_section']='The board does not contain any public section.<br/><br/>To access private sections, please log-in.';
$L['E_no_title']='Please give a title to this new '.$L['item'];
$L['E_no_visible_section']='The board does not contain section visible for you.';
$L['E_pwd_char']='The password contains invalid character.';
$L['E_section_closed']='Section is closed';
$L['E_save']='Unable to save...';
$L['E_text']='Problem with your text.';
$L['E_wait']='Please wait a few seconds';
$L['No_sub-item']='No sub-'.$L['item'];

// Success

$L['S_update']='Update successful...';
$L['S_delete']='Delete completed...';
$L['S_insert']='Creation successful...';
$L['S_preferences']='Preferences updated';
$L['S_save']='Successfully saved...';
$L['S_message_saved']='Message saved...<br/>Thank you';
$L['Item_added']='Element added';
$L['Item_removed']='Element removed';
$L['Relation_added']='Relation added';
$L['Relation_removed']='Relation removed';

// Dates

$L['dateMMM']=array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');
$L['dateMM'] =array(1=>'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
$L['dateM']  =array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
$L['dateDD'] =array(1=>'Mon','Tue','Wed','Thu','Fri','Sat','Sun');
$L['dateD']  =array(1=>'M','T','W','T','F','S','S');
$L['dateSQL']=array(
  'January'  => 'January',
  'February' => 'February',
  'March'    => 'March',
  'April'    => 'April',
  'May'      => 'May',
  'June'     => 'June',
  'July'     => 'July',
  'August'   => 'August',
  'September'=> 'September',
  'October'  => 'October',
  'November' => 'November',
  'December' => 'December',
  'Monday'   => 'Monday',
  'Tuesday'  => 'Tuesday',
  'Wednesday'=> 'Wednesday',
  'Thursday' => 'Thursday',
  'Friday'   => 'Friday',
  'Saturday' => 'Saturday',
  'Sunday'   => 'Sunday',
  'Today'=>'Today',
  'Yesterday'=> 'Yesterday',
  'Jan'=>'Jan',
  'Feb'=>'Feb',
  'Mar'=>'Mar',
  'Apr'=>'Apr',
  'May'=>'May',
  'Jun'=>'Jun',
  'Jul'=>'Jul',
  'Aug'=>'Aug',
  'Sep'=>'Sep',
  'Oct'=>'Oct',
  'Nov'=>'Nov',
  'Dec'=>'Dec',
  'Mon'=>'Mon',
  'Tue'=>'Tue',
  'Wed'=>'Wed',
  'Thu'=>'Thu',
  'Fri'=>'Fri',
  'Sat'=>'Sat',
  'Sun'=>'Sun');
