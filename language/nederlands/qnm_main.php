<?php

if ( !defined('QNM_HTML_DTD') ) define ('QNM_HTML_DTD', '<!DOCTYPE html>'); // html 5
if ( !defined('QNM_HTML_CHAR') ) define ('QNM_HTML_CHAR', 'UTF-8');
if ( !defined('QNM_HTML_DIR') ) define ('QNM_HTML_DIR', 'ltr');
if ( !defined('QNM_HTML_LANG') ) define ('QNM_HTML_LANG', 'nl');

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
$L['Items']='Elementen'; $L['items']='elementen';
$L['Group']='Groep';
$L['Groups']='Groeps';
$L['Sub-item']='Sub-'.$L['item'];
$L['Sub-items']='Sub-'.$L['items'];
$L['No_sub-item']='no sub-'.$L['item'];
$L['Item_add']='Nieuw '.$L['item'];
$L['Item_upd']=' Bewerken '.$L['item'];
$L['Line']='Lijn';
$L['Lines']='Lijnen';
$L['Connector']='Connector';
$L['Connectors']='Connectoren';
$L['H_Item_g']='Gebruik dit om container-elementen te cre&euml;ren (rooms, stations or any group of equipments). Een '.$L['Group'].' kan '.$L['Sub-items'].' bevatten, maar geen connector.';
$L['H_Item_e']='Gebruik dit om apparatuur, apparaten te cre&euml;ren. Een netwerk '.$L['item'].' kan sub-'.$L['items'].' of connectoren bevatten.';
$L['H_Item_l']='Gebruik dit om kabels, buizen of wegen te cre&euml;ren. Een netwerk lijn kan connectoren (aan beide uiteinden) bevatten.';
$L['H_Item_c']='Gebruik dit om stopcontacten, plugger of lijn eindknopen te cre&euml;ren. Een netwerk connector bestaat alleen binnen een '.$L['item'].' of een lijn, en kan nooit sub-'.$L['item'].' hebben.';
$L['Relation']='Verbinding';
$L['Relations']='Verbindingen';
$L['No_relation']='Geen verbinding';
$L['Edit_relations']='Verbindingen bewerken';
$L['Contained_items']='Inbegrepen '.$L['items'];

$L['Direction']='Richting';
$L['Direction_specific']='Specifieke richting';
$L['Direction_multiple']='Meerdere richtingen';
$L['Direction0']='Onbepaald';
$L['Direction1']='Direct';
$L['Direction2']='Bidirectionele';
$L['Direction-1']='Achterzijde';
$L['Direction3']='Niet onbepaald';
$L['Direction4']='Niet direct';
$L['Direction5']='Niet bidirectionele';
$L['Direction6']='niet achterzijde';
$L['Not_class_e']='Niet '.$L['item'];
$L['Not_class_l']='Niet lijn';
$L['Not_class_c']='Niet connector';

$L['Class_specific']='Specifieke klasse';
$L['Class_multiple']='Meerdere klassen';
$L['Exactly']='Precies';
$L['Any']='Elk';

// The top level words (are re-used here after)

$L['I']='(?)'; // help-info symbol
$L['Y']='Ja';
$L['N']='Nee';
$L['Ok']='Ok';
$L['Id']='Id';
$L['M']='M'; // field M

// Specific vocabulary

$L['Class']='Class';
  $L['Classes']='Classes';
$L['Domain']='Domein';
  $L['Domains']='Domeins';
$L['Section']='Sectie';
  $L['Sections']='Secties';
$L['User']='Gebrijker';
  $L['Users']='Gebruikers';
  $L['User_add']='Nieuw gebruiker';
  $L['User_del']='Gebruiker verwijderen';
  $L['User_upd']='Profiel bewerken';
$L['Status']='Statuut';
  $L['Statuses']='Statussen';
  $L['Status_add']='Nieuw statuut';
  $L['Status_upd']='Statuut wijzigen';
$L['Message']='Post';
  $L['Messages']='Posten';
  $L['First_message']='Eerste post';
  $L['Last_message']='Laatste post';
$L['Forward']='Verstuurd bericht';
  $L['Forwards']='verstuurd berichten';
$L['Link']='Verbinding';
  $L['Links']='Verbindingen';
  $L['Links_as_relation']='Is verbonden met';
  $L['Links_as_parent']='Is binnen een ouder';
  $L['Plugged']='Plugged';
$L['In_process']='In proces';
  $L['Set_in_process']='In proces';
  $L['In_process_note']='Post in proces';
  $L['In_process_notes']='Posten in proces';
  $L['Closed_note']='Geslote post';
  $L['Closed_notes']='Gesloten posten';
$L['Field']='Veld';
$L['Fields']='Velden';
$L['Username']='Gebruikersnaam';
$L['Role']='Rol';
  $L['Userrole_a']='Administrateur'; $L['Userrole_as']='Administrateurs';
  $L['Userrole_m']='Staff';          $L['Userrole_ms']='Staffs';
  $L['Userrole_u']='Lid';            $L['Userrole_us']='Leden';
  $L['Userrole_v']='Bezoeker';       $L['Userrole_vs']='Bezoekers';
  $L['Userrole_c']='Sectie moderator';
$L['Joined']='Geregistreerd op';
$L['Avatar']='Foto';
$L['Signature']='Onderschrift';
$L['Modified_by']='Bewerkt door';
$L['Deleted_by']='Geschrapt door';
$L['Top_participants']='Top deelnemers';

// User preference and Table top commands

$L['My_preferences']='Mijn voorkeuren';
$L['Ascending']='Oplepend';
$L['Descending']='Aflopend';
$L['Show_all_status']='Toon alle statussen';
$L['Show_inactives']='Toon inactieven';
$L['Show_actives']='Toon actieven';
$L['Last_column']='Laatste colonne';
$L['Schematic_view']='Schematisch';
$L['Detailed_lists']='Gedetailleerde lijst';
$L['Show_large_box']='Large schemas';
$L['Show_small_box']='Short schemas';
$L['View_compact']='Compacte weergave';
$L['View_large']='Grote weergave';
$L['Edit_patching']='Patching bewerken';

$L['Activate']='Activeren';
  $L['cmd_Activate']='Geselecteerd '.$L['items'].' activeren';
$L['Add_note']='Post toevoegen';
  $L['cmd_Add_note']='Voeg een nieuwe opmerking voor de geselecteerde '.$L['items'];
  $L['cmd_Delete']='Verwijder geselecteerde '.$L['items'];
  $L['cmd_Delete_help']='('.$L['items'].' ontkoppeld, connectoren verwijderd)';
$L['Inactivate']='Inactiveren';
  $L['cmd_Inactivate']='Uitschakelen geselecteerde '.$L['items'];
$L['Create_relations']='Verbindingen toevoegen';
$L['Remove_relations']='Verbindingen verwijderen';
  $L['cmd_Remove_relations']='Verwijder de geselecteerde verbindingen';
$L['More']='Meer';
  $L['cmd_More']='Type wijzigen, verplaatsen...';
$L['Change_status']='Statuut&nbsp;wijzigen';
  $L['cmd_Change_status']='Wijzig de status van de geselecteerde '.$L['items'];
$L['Change_type']='Type&nbsp;wijzigen';
  $L['cmd_Change_type']='Type wijzigen van de geselecteerde '.$L['items'];
  $L['cmd_Edit_links']='Koppelen/ontkoppelen '.$L['items'].', statuut of richting wijzigen';
  $L['cmd_Edit_content']='Toevoegen/verwijderen bestaande sub-'.$L['items'].', statuut wijzigen';
$L['Change_descr']='Beschrijving&nbsp;wijzigen';
  $L['cmd_Change_descr']='Wijzig de beschrijving van de '.$L['items'];
$L['Show']='Tonen';
$L['All_types']='Alle types';
$L['All_statuses']='Alle statussen';

// Common

$L['Action']='Actie';
$L['Active']='Actief';   $L['Inactive']='Inactief';
$L['Actives']='Actieven'; $L['Inactives']='Inactieven';
$L['Add']='Toevoegen';
$L['Add_inside']='Toevoegen binnen';
$L['Add_categories']='Categorie&euml;n toevoegen';
$L['Add_selected']='Geselecteerd toevoegen';
$L['Address']='Adres';
$L['All']='Alle';
$L['And']='En';
$L['Attachment']='Aanhechting';
$L['Author']='Auteur';
$L['Back']='Terug';
$L['Birthday']='Geboortedatum';
$L['Birthdays_calendar']='Verjaardagen kalender';
$L['By']='Door';
$L['By_date']='Per datum';
$L['Change']='Wisselen';
$L['Change_address']='Adres wijzigen';
$L['Change_name']='Gebruikersnaam wijzigen';
$L['Changed']='Gewijzigd';
$L['Close']='Gesloten';
$L['Closed']='Gesloten'; // closed notes
$L['Contact']='Contact';
$L['Contains']='Bevat';
$L['Containing']='Inhoud';
$L['Content']='Inhoud';
$L['Continue']='Voortzetten';
$L['Coord']='Co&ouml;rdinaten';
$L['Coord_latlon']='(lat,lon)';
$L['Csv']='Exporteren'; $L['H_Csv']='Downloaden naar spreadsheet';
$L['Create']='Aanmaken';
$L['Created']='Aanmaakdatum';
$L['Creation_date']='Aanmaakdatum';
$L['Date']='Datum';
$L['Dates']='Data';
$L['Day']='Dag';
$L['Days']='Dagen';
$L['Delete']='Verwijderen';
$L['Deleted']='Verwijderd';
$L['Delete_tags']='Verwijderen (click op een woord of typ * om alles to verwijderen)';
$L['Descr']='Beschrijving'; // field name
$L['Description']='Beschrijving';
$L['Destination']='Bestemming';
$L['Details']='Details';
$L['Display_at']='Tonen op datum';
$L['Documents']='Documenten';
$L['Document_add']='File';
$L['Document_name']='Naam';
$L['Edit']='Wijzigen';
$L['Email']='E-mail'; $L['No_Email']='Geen e-mail';
$L['Exit']='Uitrit';
$L['Favorites']='Favorieten';
$L['File']='File';
$L['First']='Eerst';
$L['Free']='Vrij';
$L['Goodbye']='Wordt verbroken... Tot ziens';
$L['Goto']='Ga naar';
$L['Having']='Met';
$L['H_Website']='Url van uw website (met http://)';
$L['Help']='Hulp';
$L['Hidden']='Verborgen';
$L['In']='Binnen';
$L['Information']='Informatie';
$L['In_process_first']='In proces eerst';
$L['Items_per_month']='Elementen per maand';
$L['Items_per_month_cumul']='Cumulatieve '.$L['items'].' per maand';
$L['Last']='Laatst';
$L['Legend']='Legende';
$L['Location']='Locatie';
$L['Maximum']='Maximum';
$L['Minimum']='Minimum';
$L['Missing']='Ontbrekende informatie';
$L['Modified']='Gewijzigd';
$L['More_criterias']='Meer criteria';
$L['Month']='Maand';
$L['Move']='Verplaatsen';
$L['Move_to_section']='Verplaatsen naar sectie';
$L['Next']='Volgende';
$L['None']='Niets';
$L['Notification']='Kennisgeving';
$L['Of']='Van';
$L['Open']='Open';
$L['Options']='Opties';
$L['Or']='Of';
$L['Others']='Anderen';
$L['Page']='Pagina';
$L['Pages']='Pagina\'s';
$L['Password']='Wachtwoord';
$L['Picture']='Foto';
$L['Phone']='Telefoon';
$L['Preferences']='Voorkeuren';
$L['Preview']='Voorbeeld';
$L['Previous']='Vorig';
$L['Privacy']='Geheimhouding';
$L['Properties']='Eigenschappen';
$L['Reason']='Reed';
$L['Remove']='Verwijderen';
$L['Rename']='Hernoemen';
$L['Result']='Resultaat';
$L['Results']='Resultaten';
$L['Save']='Saven';
$L['Search_results']='Zoekresultaten';
$L['Seconds']='Seconden';
$L['Select']='Selecteren';
$L['Selected_from']='Geselecteerd uit';
$L['Send']='Sturen';
$L['Send_on_behalf']='Stuur voor rekening van';
$L['Show_more_notes']='Toon meer posten';
$L['Sort']='Sorteren';
$L['State']='Staat';
$L['Statistics']='Statistiek';
$L['Style']='Stijl';
$L['Tag']='Categorie';
$L['Tags']='Categorie&euml;n';
$L['Tags_add']='Categorie&euml;n toevoegen';
$L['Tags_remove']='Categorie&euml;n verwijderen';
$L['Time']='Tijd';
$L['Title']='Titel';
$L['Today']='Vandaag';
$L['Total']='Totaal';
$L['Type']='Type';
$L['Undefined']='Onbepaald';
$L['Unknown']='Onbekende';
$L['Url']='Url';
$L['Website']='Website'; $L['No_Website']='Geen website';
$L['Welcome']='Welkom';
$L['Welcome_to']='Welkom voor een nieuwe gebruiker, ';
$L['Welcome_not']='Ik ben %s niet !';
$L['Welcome_not']='Ik ben niet %s !';
$L['Year']='Jaar';

// Menu

$L['FAQ']='FAQ';
$L['Search']='Zoeken';
$L['Memberlist']='Gebruikerslijst';
$L['Login']='Inloggen';
$L['Logout']='Uitloggen';
$L['Register']='Registreer';
$L['Profile']='Profiel';
$L['Administration']='Administratie';
$L['Legal']='Privacybeleid';

// Section // use &nbsp; to avoid double ligne buttons

$L['Create_items']=$L['Items'].'&nbsp;aanmaken';
$L['Create_sub-items']='Sub-'.$L['items'].'&nbsp;aanmaken';
$L['Create_items_in']=$L['Items'].'&nbsp;aanmaken binnen';
$L['Create_connectors']='Connectoren&nbsp;aanmaken';
$L['Goto_message']='[<b>&raquo;</b>]';
$L['H_Goto_message']='Laatste post tonen';
$L['Previous_notes']='Vorige posten';
$L['Edit_start']='Bewerken beginnen';
$L['Edit_stop']='Bewerken stoppen';
$L['Item_closed_show']='Gesloten '.$L['items'].' tonen';
$L['Item_closed_hide']='Gesloten '.$L['items'].' verbergen';

// creation date criteria

$L['Date_on']='Op';
$L['Date_near']='Dichtbij';
$L['Date_before']='Voor';
$L['Date_after']='Na';
$L['H_datesearch']='U kunt een dag (jjjj-mm-dd), een maand (yyyy-mm) of een jaar (jjjj).<br/>
Zoeken dichtbij een dag betekent zoeken elementen gemaakt tijdens dit week (tussen dag-3 en dag+3). Zoeken dichtbij een maand betekent zoeken elementen gemaakt dit kwartaal (tussen maand-1 en maand+1). Zoeken dichtbij dit jaar betekent zoeken elementen gemaakt tussen jaar-1 en jaar+1.';

// Editing

$L['Select...']='Kiezen...';
$L['f_add_ne_id']='%s om meerdere '.$L['items'].' te cre&euml;ren';
$L['f_add_ne_mirror']='spiegel-connector met dezelfde id (eind-connector van de lijn)';
$L['f_add_ne_using']=$L['items'].' met %s';
$L['f_add_ne_starting']='vanaf';
$L['f_add_ne_number']='aantal '.$L['items'];
$L['f_add_ne_az']='[a-z] karakters nodig';
$L['f_add_ne_int']='number is nodig';
$L['Change_insertdate']='Wijzig aanmaakdatum';

$L['f_info_delete']='Sub-connectoren worden ook verwijdered. Sub-'.$L['items'].' bestaan.';
$L['f_info_replace']='Bestaande waarden worden vervangen';
$L['f_info_add_note']='De post wordt toegevoegd in elk '.$L['item'].'.';
$L['f_info_add_tags']='U kunt verschillende categorie&euml;n toevoegen gescheiden door '.QNM_QUERY_SEPARATOR.'<br/>De voorgestelde categorie&euml;n komen uit de sectie van de geselecteerd '.$L['item'].'(en).';
$L['f_info_remove_tags']='U kunt meerdere categorie&euml;n verwijderen gescheiden door '.QNM_QUERY_SEPARATOR.' of verwijderen alle categorie&euml;n met *<br/>De voorgestelde categorie&euml;n komen uit de geselecteerd '.$L['item'].'(en).';
$L['f_warning_delete']='Aandacht, uw bent het verwijderen van een netwerk '.$L['items'];
$L['f_Also_sub_items']='Ook voor sub-'.$L['items'];
$L['f_Close_all_notes']='Alle posten sluiten';
$L['f_Delete_all_closed_notes']='Verwijder alle gesloten posten';

$L['f_Search_other_section']='Zoeken andere '.$L['items'].' uit sectie';
$L['f_Show_only_type']='Tonen alleen type';
$L['f_Enter_id']='Voer een id of een deel van een id';
$L['f_Add_direction']='Voeg geselecteerd met richting';
$L['f_Add_parent']='(P) betekent '.$L['item'].' al binnen een ouder. Toevoegen betekent verandering van zijn ouder.';
$L['Confirm_delete_notes']='U verwijdert post(en). Doorgaan?';

// Search

$L['Recent_notes']='Recente&nbsp;posten';
$L['All_my_notes']='Al&nbsp;mijn&nbsp;posten';
$L['Advanced_search']='Geavanceerd zoeken';
$L['H_Search']='(Id of typenaam)';
$L['H_Search_criteria']='U kunt verschillende waarden van elkaar gescheiden door '.QNM_QUERY_SEPARATOR.' (ex.: c1,c2 betekent '.$L['items'].' "c1" of "c2").';
$L['Search_options']='Zoekopties';
$L['Search_criteria']='Zoeken netwerk '.$L['items'];
$L['Search_by_key']='Met posten met woord(en)';
$L['Search_by_id']='Zoeken '.$L['item'].' per Id';
$L['Search_by_status']='Zoeken per statuut';
$L['Search_by_tag']='Zoeken per categorie';
$L['Search_by_field']='Zoeken in velden';

$L['Search_result']='Zoekresultaten';
$L['All_sections']='Alle secties';
$L['Only_notes_in_process']='Alleen posten in proces';
$L['Any_type']='Alle type';
$L['Any_status']='Alle statuut';
$L['Too_many_keys']='Te veel woorden';
$L['Search_by_words']='Zoeken elk woord afzonderlijk';
$L['Search_exact_words']='Zoeken woorden samen';
$L['Search_by_date']='Zoeken per datum';
$L['This_week']='Deze week';
$L['This_month']='Deze maand';
$L['This_year']='Dit jaar';
$L['With_tag']= 'Categorie';
$L['Show_only_tag']='Sommige '.$L['items'].' hebben de volgende categorie&euml;n:<br/>(click om te zoeken '.$L['items'].' met de categorie)';

// Search result

$L['Search_results_id']='%s '.$L['items'].' met id %s';
$L['Search_results_keyword']='%s '.$L['items'].' met posten met %s';
$L['Search_results_user']='%s posten door %s';
$L['Search_results_last']='%s recente posten (vorige week)';
$L['Search_results_field']='%s resultaten voor %s';
$L['Search_results_date']='%s '.$L['items'].' met aanmaakdatum ';
$L['Only_in_section']='Alleen in sectie';
$L['Having_typename_containing']='Met typenaam met';
$L['Only_status']='Alleen statuut';

// Ajax helper

$L['All_categories']='Alle categorie&euml;n';
$L['Category_not_yet_used']='Categorie nog niet gebruikt';
$L['Impossible']='Onmogelijk';
$L['No_result']='Geen resultaat';
$L['Try_other_lettres']='Probeer andere karakter';
$L['Try_without_options']='Probeer zonder filter';

// Privacy

$L['Privacy_visible_0']='Verborgen';
$L['Privacy_visible_1']='Zichtbaar voor leden alleen';
$L['Privacy_visible_2']='Zichtbaar voor iedereen';

// Restrictions

$L['R_member']='De toegang is beperkt tot slechts leden.<br /><br />Gelieve in te loggen, of ga naar Registreerd om lid te worden.';
$L['R_staff']='De toegang is beperkt tot slechts staff. <a href="qti_index.php">Exit</a>';
$L['R_security']='De veiligheid instellingen laten deze functie geen toe.';

// Errors

$L['No_item']='Geen '.$L['item'].' gevonden';
$L['No_selected_row']='Ten minste een '.$L['item'].' moet worden geselecteerd.\nOm meerdere '.$L['items'].' te selecteren, u kan SHIFT+click gebruiken.';
$L['E_access']='De toegang is beperkt...';
$L['E_already_used']='Reeds gebruikt';
$L['E_char_max']='(maximum %d karakters)';
$L['E_editing']='Data zijn verandered. Verlaten zonder saven?';
$L['E_file_size']='File is te groot';
$L['E_invalid']='ongeldig';
$L['E_javamail']='Veiligheid: java is nodig om e-mail te zien';
$L['E_line_max']='(maximum %d lijnen)';
$L['E_min_4_char']='Minimum 4 karakters';
$L['E_missing_http']='url moet met http:// of https:// beginnen';
$L['E_missing_items']='Aantal '.$L['items'].' is verplicht';
$L['E_no_desc']='Geen bescrhijving';
$L['E_no_public_section']='Dit systeem bevat geen openbaar sectie. De sectie toegang vereist login.';
$L['E_no_title']='Een titel is verplicht';
$L['E_no_visible_section']='Dit systeem bevat geen sectie zichtbaar voor u.';
$L['E_pwd_char']='Wachtwoord bevat ongeldige karakters.';
$L['E_section_closed']='Sectie is gesloten';
$L['E_save']='Kan niet opslaan...';
$L['E_text']='Probleem met uw bericht.';
$L['E_wait']='Gelieve te wachten een paar seconden';
$L['No_sub-item']='Geen sub-'.$L['item'];

// Success

$L['S_update']='Voltooide update...';
$L['S_delete']='Schrap voltooid...';
$L['S_insert']='Succesvolle verwezenlijking...';
$L['S_preferences']='Voorkeuren bijgewerkt';
$L['S_save']='Saven voltooid...';
$L['S_message_saved']='Het bericht wordt bewaard...<br />Dank u';

$L['Item_added']='Element toegevoegd';
$L['Item_removed']='Element verwijderd';
$L['Relation_added']='Verbinding toegevoegd';
$L['Relation_removed']='Verbinding verwijderd';

// Dates

$L['dateMMM']=array(1=>'Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','Septembre','Oktober','November','December');
$L['dateMM'] =array(1=>'Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec');
$L['dateM']  =array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag','Zondag');
$L['dateDD'] =array(1=>'Ma','Di','Wo','Do','Vr','Za','Zo');
$L['dateD']  =array(1=>'M','D','W','D','V','Z','Z');
$L['dateSQL']=array(
  'January'  => 'januari',
  'February' => 'februari',
  'March'    => 'maart',
  'April'    => 'april',
  'May'      => 'mei',
  'June'     => 'juni',
  'July'     => 'juli',
  'August'   => 'augustus',
  'September'=> 'september',
  'October'  => 'oktober',
  'November' => 'november',
  'December' => 'december',
  'Monday'   => 'maandag',
  'Tuesday'  => 'dinsdag',
  'Wednesday'=> 'woensdag',
  'Thursday' => 'donderdag',
  'Friday'   => 'vrijdag',
  'Saturday' => 'zaterdag',
  'Sunday'   => 'zondag',
  'Today'=>'Vandaag',
  'Yesterday'=>'Gisteren',
  'Jan'=>'jan',
  'Feb'=>'feb',
  'Mar'=>'mrt',
  'Apr'=>'apr',
  'May'=>'mei',
  'Jun'=>'jun',
  'Jul'=>'jul',
  'Aug'=>'aug',
  'Sep'=>'sep',
  'Oct'=>'okt',
  'Nov'=>'nov',
  'Dec'=>'dec',
  'Mon'=>'ma',
  'Tue'=>'di',
  'Wed'=>'wo',
  'Thu'=>'do',
  'Fri'=>'vr',
  'Sat'=>'za',
  'Sun'=>'zo');