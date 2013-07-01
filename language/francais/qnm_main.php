<?php
if ( !defined('QNM_HTML_DTD') ) define ('QNM_HTML_DTD', '<!DOCTYPE html>'); // html 5
if ( !defined('QNM_HTML_CHAR') ) define ('QNM_HTML_CHAR', 'UTF-8');
if ( !defined('QNM_HTML_DIR') ) define ('QNM_HTML_DIR', 'ltr');
if ( !defined('QNM_HTML_LANG') ) define ('QNM_HTML_LANG', 'fr');

// It is recommended to always use capital on first letter in the translation, script changes to lower case if necessary.
// It is recommended to use html entities for accent or special characters
// When lowercase uses accent as first lettre, you can declare it to overwrite the default lowercase feature.
// The character doublequote ["] is FORBIDDEN (reserved for html tags)
// To make a single quote use slash [\']

// -------------
// TOP LEVEL VOCABULARY
// -------------

// Use the top level vocabulary to give the most appropriate name
// for the item (object elements) managed by this application.
// e.g. Element, Asset, Object,...

$L['Item']='El&eacute;ment';   $L['item']='&eacute;l&eacute;ment'; // 'Item' is the only word where lowercase definition is required
$L['Items']='El&eacute;ments'; $L['items']='&eacute;l&eacute;ments';
$L['Group']='Groupe';
$L['Groups']='Groupes';
$L['Sub-item']='Sous-'.$L['item'];
$L['Sub-items']='Sous-'.$L['items'];
$L['No_sub-item']='Aucun sous-'.$L['item'];
$L['Item_add']='Nouvel '.$L['Item'];
$L['Item_upd']='Modifer l\''.$L['item'];
$L['Line']='Ligne';
$L['Lines']='Lignes';
$L['Connector']='Connecteur';
$L['Connectors']='Connecteurs';
$L['H_Item_g']='Utilisez ceci pour cr&eacute;er des conteneurs (stations, salles techniques ou tout groupes d\'&eacute;quipements). Un '.$L['Group'].' peut contenir des '.$L['Sub-items'].', mais pas de connecteur.';
$L['H_Item_e']='Utilisez ceci pour cr&eacute;er des &eacute;quipements, appareils ou stations. Un "'.$L['item'].'" peut contenir des sous-'.$L['items'].' ou des connecteurs.';
$L['H_Item_l']='Utilisez ceci pour cr&eacute;er des cables, tuyaux ou routes. Une "ligne" peut contenir des connecteurs (&agrave; chaque extr&eacute;mit&eacute;).';
$L['H_Item_c']='Utilisez ceci pour cr&eacute;er connecteurs, prises ou noeud de ligne. Un "connecteur" ne peut exister qu\'&agrave; l\'int&eacute;rieur d\'un '.$L['item'].' ou d\'une ligne et ne peut pas contenir de sous-'.$L['item'].'.';
$L['Relation']='Relation';
$L['Relations']='Relations';
$L['No_relation']='Aucune relation';
$L['Edit_relations']='Editer relations';
$L['Contained_items']=$L['Items'].' contenus';

$L['Direction']='Direction';
$L['Direction_specific']='Direction sp&eacute;cifique';
$L['Direction_multiple']='Directions multiple';
$L['Direction0']='Ind&eacute;finie';
$L['Direction1']='Directe';
$L['Direction2']='Bidirectionnelle';
$L['Direction-1']='Inverse';
$L['Direction3']='Pas ind&eacute;finie';
$L['Direction4']='Pas directe';
$L['Direction5']='Pas bidirectionnelle';
$L['Direction6']='Pas inverse';
$L['Not_class_e']='Pas '.$L['item'];
$L['Not_class_l']='Pas line';
$L['Not_class_c']='Pas connecteur';
$L['Class_specific']='Classe sp&eacute;cifique';
$L['Class_multiple']='Classes multiple';
$L['Exactly']='Exactement';

// The top level words are re-used in a lot of translations defined here after

$L['I']='(?)'; // help-info symbol
$L['Y']='Oui';
$L['N']='Non';
$L['Ok']='Ok';
$L['Id']='Id';
$L['M']='M'; // field M


// Specific vocabulary

$L['Class']='Classe';
  $L['Classes']='Classes';
$L['Domain']='Domaine';
  $L['Domains']='Domaines';
$L['Section']='Section';
  $L['Sections']='Sections';
$L['User']='Utilisateur';
  $L['Users']='Utilisateurs';
  $L['User_add']='Nouvel utilisateur';
  $L['User_del']='Effacer l\'utilisateur';
  $L['User_upd']='Editer le profil';
$L['Status']='Statut';
  $L['Statuses']='Statuts';
  $L['Status_add']='Nouveau statut';
  $L['Status_upd']='Editer le statut';
$L['Message']='Note';
  $L['Messages']='Notes';
  $L['First_message']='Premi&egrave;re&nbsp;note';
  $L['Last_message']='Derni&egrave;re&nbsp;note';
$L['Forward']='Transfers';
  $L['Forwards']='Transfers';
$L['Link']='Lien';
  $L['Links']='Liens';
  $L['Links_as_relation']='Est impliqu&eacute; dans';
  $L['Links_as_parent']='est inclus dans un parent';
  $L['Plugged']='Connect&eacute;';
$L['In_process']='Ouvertes';
  $L['Set_in_process']='Ouvrir';
  $L['In_process_note']='Note ouverte';
  $L['In_process_notes']='Notes ouvertes';
  $L['Closed_note']='Note ferm&eacute;e';
  $L['Closed_notes']='Notes ferm&eacute;es';
  $L['Field']='Champ';
  $L['Fields']='Champs';
$L['Notify_also']='Notifier aussi';
$L['Drop_attachment']='Effacer la pi&egrave;ce jointe';
$L['Username']='Nom d\'utilisateur';
$L['Role']='R&ocirc;le';
  $L['Userrole_a']='Administrateur'; $L['Userrole_as']='Administrateurs';
  $L['Userrole_m']='Staff';          $L['Userrole_ms']='Staffs';
  $L['Userrole_u']='Utilisateur';    $L['Userrole_us']='Utilisateurs';
  $L['Userrole_v']='Visiteur';       $L['Userrole_vs']='Visiteurs';
  $L['Userrole_c']='M&eacute;diateur de section';$L['Userrole_cs']='M&eacute;diateurs de section';
$L['Joined']='Depuis';
$L['Avatar']='Photo';
$L['Signature']='Signature';
$L['Modified_by']='Modifi&eacute; par';
$L['Deleted_by']='Effac&eacute; par';
$L['Top_participants']='Top participants';
$L['Register_completed']='Inscription termin&eacute;e...';

// User preference and Table top commands

$L['My_preferences']='Mes pr&eacute;f&eacute;rences';
$L['Ascending']='Ascendant';
$L['Descending']='Descendant';
$L['Show_all_status']='Afficher tous les statuts';
$L['Show_inactives']='Afficher les d&eacute;sactiv&eacute;s';
$L['Show_actives']='Afficher les actifs';
$L['Last_column']='Derni&egrave;re colonne';
$L['Schematic_view']='Vue sch&eacute;matique';
$L['Detailed_lists']='Listes d&eacute;taill&eacute;es';
$L['Show_large_box']='Sch&eacute;mas larges';
$L['Show_small_box']='Sch&eacute;mas compacts';
$L['View_compact']='Vue compacte';
$L['View_large']='Vue large';
$L['Edit_patching']='Editer le patching';

$L['Activate']='Activer';
  $L['cmd_Activate']='Activer les '.$L['items'].' s&eacute;lectionn&eacute;s';
$L['Add_note']='Ajouter&nbsp;une&nbsp;note';
  $L['cmd_Add_note']='Ajouter une note aux '.$L['items'].' s&eacute;lectionn&eacute;s';
  $L['cmd_Delete']='Effacer les '.$L['items'].' s&eacute;lectionn&eacute;s';
  $L['cmd_Delete_help']='(les &eacute;quipements sont d&eacute;tach&eacute;s, les connecteurs sont supprim&eacute;s)';
$L['Inactivate']='D&eacute;sactiver';
  $L['cmd_Inactivate']='D&eacute;sactiver les '.$L['items'].' s&eacute;lectionn&eacute;s';
$L['Create_relations']='Cr&eacute;er des relations';
$L['Remove_relations']='Effacer relations';
  $L['cmd_Remove_relations']='Effacer les relations s&eacute;lectionn&eacute;es';
  $L['cmd_Edit_links']='Relier/D&eacute;lier '.$L['items'].', changer statut ou directions';
  $L['cmd_Edit_content']='Ajouter/Enlever des sous-'.$L['items'].', changer statut';
$L['More']='Plus';
  $L['cmd_More']='Changer le type, d&eacute;placer...';
$L['Change_status']='Changer&nbsp;le&nbsp;statut'; $L['cmd_Change_status']='Changer le statut des '.$L['items'].' s&eacute;lectionn&eacute;s';
$L['Change_type']='Changer&nbsp;le&nbsp;type';      $L['cmd_Change_type']='Changer le type des '.$L['items'].' s&eacute;lectionn&eacute;s';
$L['Change_descr']='Changer&nbsp;la&nbsp;description';
  $L['cmd_Change_descr']='Changer la description des '.$L['items'];
$L['Show']='Afficher';
$L['All_types']='Tous les types';
$L['All_statuses']='Tous les statuts';

// Common

$L['Action']='Action';
$L['Active']='Actif';   $L['Inactive']='D&eacute;sactiv&eacute;';
$L['Actives']='Actifs'; $L['Inactives']='D&eacute;sactiv&eacute;s';
$L['Add']='Ajouter';
$L['Add_inside']='Ajouter dans';
$L['Add_selected']='Ajouter la s&eacute;lection';
$L['Address']='Adresse';
$L['All']='Tout';
$L['And']='Et';
$L['Attachment']='Pi&egrave;ce jointe';
$L['Author']='Auteur';
$L['Back']='Retour';
$L['Birthday']='Date de naissance';
$L['Birthdays_calendar']='Calendrier des anniversaires';
$L['By']='Par';
$L['By_date']='Par date';
$L['Change']='Changer';
$L['Change_address']='Changer l\'adresse';
$L['Change_name']='Changer l\'identifiant';
$L['Changed']='Chang&eacute;';
$L['Close']='Fermer';
$L['Closed']='Ferm&eacute;es'; // closed notes
$L['Contact']='Contact';
$L['Contains']='Contient';
$L['Containing']='Contenant';
$L['Content']='Contenu';
$L['Continue']='Continuer';
$L['Coord']='Coordonn&eacute;es';
$L['Coord_latlon']='(lat,lon)';
$L['Csv']='Export'; $L['H_Csv']='Ouvrir dans un tableur';
$L['Create']='Cr&eacute;er';
$L['Created']='Cr&eacute;&eacute;';
$L['Creation_date']='Date de cr&eacute;ation';
$L['Date']='Date';
$L['Dates']='Dates';
$L['Day']='Jour';
$L['Days']='Jours';
$L['Delete']='Effacer';
$L['Deleted']='Effac&eacute;';
$L['Delete_tags']='Effacer (clickez un mot ou tappez * pour tout effacer)';
$L['Descr']='Description'; // field name
$L['Description']='Description';
$L['Destination']='Destination';
$L['Details']='D&eacute;tails';
$L['Display_at']='Afficher &agrave; la date';
$L['Documents']='Documents';
$L['Document_add']='Fichier';
$L['Document_name']='Nom';
$L['Edit']='Editer';
$L['Email']='E-mail'; $L['No_Email']='Pas d\'e-mail';
$L['Exit']='Quitter';
$L['Favorites']='Favoris';
$L['File']='Fichier';
$L['First']='Premier';
$L['Free']='Libre';
$L['Goodbye']='Vous &ecirc;tes d&eacute;connect&eacute;... Au revoir';
$L['Goto']='Allez';
$L['H_Website']='Url avec http://';
$L['Having']='Ayant';
$L['Help']='Aide';
$L['Hidden']='Cach&eacute;';
$L['I_wrote']='J\'ai &eacute;crit';
$L['In']='Dans';
$L['Information']='Information';
$L['In_process_first']='Notes ouvertes';
$L['Items_per_month']='El&eacute;ments par mois';
$L['Items_per_month_cumul']='Cumul des '.$L['items'].' par mois';
$L['Last']='Dernier';
$L['Legend']='L&eacute;gende';
$L['Location']='Localisation';
$L['Maximum']='Maximum';
$L['Minimum']='Minimum';
$L['Missing']='Information manquante';
$L['Modified']='Modifi&eacute;';
$L['Month']='Mois';
$L['More_criterias']='Autres crit&egrave;res';
$L['Move']='D&eacute;placer';
$L['Move_to_section']='D&eacute;placer vers la section';
$L['None']='Aucun';
$L['Next']='Next';
$L['Notification']='Notification';
$L['Open']='Ouvrir';
$L['Options']='Options';
$L['of']='de';
$L['Or']='Ou';
$L['Others']='Autres';
$L['Page']='Page';
$L['Pages']='Pages';
$L['Password']='Mot de passe';
$L['Picture']='Photo';
$L['Phone']='T&eacute;l&eacute;phone';
$L['Preferences']='Pr&eacute;f&eacute;rences';
$L['Preview']='Pr&eacute;visualisation';
$L['Previous']='Pr&eacute;c&eacute;dente';
$L['Privacy']='Vie&nbsp;prive';
$L['Properties']='Propri&eacute;t&eacute;s';
$L['Reason']='Raison';
$L['Remove']='Enlever';
$L['Rename']='Renommer';
$L['Reset']='Annuler';
$L['Result']='R&eacute;sultat';
$L['Results']='R&eacute;sultats';
$L['Save']='Sauver';
$L['Search_results']='R&eacute;sultats de la recherche';
$L['Seconds']='Secondes';
$L['Select']='S&eacute;lectionner';
$L['Selected_from']='S&eacute;lectionn&eacute;s sur';
$L['Send']='Envoyer';
$L['Send_on_behalf']='Au nom de';
$L['Show_more_notes']='Afficher toutes les notes';
$L['Sort']='Trier';
$L['State']='Etat';
$L['Statistics']='Statistiques';
$L['Style']='Style';
$L['Tag']='Cat&eacute;gorie';
$L['Tags']='Cat&eacute;gories';
$L['Tags_add']='Ajouter des cat&eacute;gories';
$L['Tags_remove']='Enlever des cat&eacute;gories';
$L['Time']='Heure';
$L['Title']='Titre';
$L['Today']='Aujourd\'hui';
$L['Total']='Total';
$L['Type']='Type';
$L['Undefined']='Ind&eacute;fini';
$L['Unknown']='Inconnu';
$L['Url']='Url';
$L['Website']='Site web'; $L['No_Website']='Aucun site web';
$L['Welcome']='Bienvenue';
$L['Welcome_to']='Bienvenue &agrave; un nouvel utilisateur, ';
$L['Welcome_not']='Je ne suis pas %s !';
$L['Year']='Ann&eacute;e';

// Menu

$L['FAQ']='FAQ';
$L['Search']='Chercher';
$L['Memberlist']='Utilisateurs';
$L['Login']='Connexion';
$L['Logout']='D&eacute;connexion';
$L['Register']='S\'enregistrer';
$L['Profile']='Profil';
$L['Administration']='Administration';
$L['Legal']='Notices l&eacute;gales';

// Section // use &nbsp; to avoid double ligne buttons

$L['Create_items']='Cr&eacute;er&nbsp;des&nbsp;'.$L['items'];
$L['Create_sub-items']='Cr&eacute;er&nbsp;des&nbsp;sous-'.$L['items'];
$L['Create_items_in']='Cr&eacute;er&nbsp;des&nbsp;'.$L['items'].'&nbsp;dans';
$L['Create_connectors']='Cr&eacute;er&nbsp;des&nbsp;connecteurs';
$L['Goto_message']='[<b>&raquo;</b>]';
$L['H_Goto_message']='Voir la derni&egrave;re note';
$L['Previous_notes']='Notes pr&eacute;c&eacute;dentes';
$L['Edit_start']='Mode &eacute;dition';
$L['Edit_stop']='Quitter le mode &eacute;dition';
$L['Item_closed_show']='Afficher les '.$L['items'].' inactifs';
$L['Item_closed_hide']='Masquer les '.$L['items'].' inactifs';

// creation date criteria

$L['Date_on']='Le';
$L['Date_near']='Autour de';
$L['Date_before']='Avant';
$L['Date_after']='Apr&egrave;s';
$L['H_datesearch']='Vous pouvez entrer un jour (aaaa-mm-jj), un mois (aaaa-mm) ou une ann&eacute;e (aaaa).<br/>
En sp&eacute;cifiant autour d\'un jour, vous cherchez les &eacute;l&eacute;ments cr&eacute;&eacute;s cette semaine (entre le jour-3 et le jour+3). Autour d\'un mois, permet de chercher ceux cr&eacute;&eacute;s durant ce trimestre. Autour d\une ann&eacute;e, permet de chercher ceux cr&eacute;&eacute;s entre l\'ann&eacute;e-1 et ann&eacute;e+1.';

// Editing

$L['Select...']='S&eacute;lectionnez...';
$L['f_add_ne_id']='ajoutez %s afin de cr&eacute;er plusieurs '.$L['items'];
$L['f_add_ne_mirror']='cr&eacute;er les connecteurs oppos&eacute;s avec le m&ecric;me id (connecteurs de fin de ligne)';
$L['f_add_ne_using']=$L['items'].' utilisant %s';
$L['f_add_ne_starting']='&agrave; partir de';
$L['f_add_ne_number']='nombre d\''.$L['items'];
$L['f_add_ne_az']='requi&egrave;re une lettre [a-z]';
$L['f_add_ne_int']='requi&egrave;re un chiffre';
$L['Change_insertdate']='Changer la date de cr&eacute;ation';

$L['f_info_delete']='Les connecteurs sont aussi effac&eacute;s. Les sous-'.$L['items'].' ne sont pas effac&eacute;s.';
$L['f_info_replace']='Les valeurs actuelles seront remplac&eacute;es.';
$L['f_info_add_note']='La note sera ajout&eacute;e &agrave; chaque '.$L['item'].'.';
$L['f_info_add_tags']='Vous pouvez ajouter plusieurs cat&eacute;gories en les s&eacute;parant par '.QNM_QUERY_SEPARATOR.'<br/>Les cat&eacute;gories propos&eacute;es sont celles utilis&eacute;es dans la(les) section(s) de ces '.$L['items'];
$L['f_info_remove_tags']='Vous pouvez enlever plusieurs cat&eacute;gories en les s&eacute;parant par '.QNM_QUERY_SEPARATOR.' ou enlever toutes les cat&eacute;gories en utilisant *<br/>Les cat&eacute;gories propos&eacute;es sont celles des &eacute;l&eacute;m&eacute;nts s&eacute;lectionn&eacute;s.';
$L['f_warning_delete']='Attention, vous effacez des '.$L['items'].' du r&eacute;seau';
$L['f_Also_sub_items']='Les sous-'.$L['items'].' aussi';
$L['f_Close_all_notes']='Fermer toutes les notes';
$L['f_Delete_all_closed_notes']='Effacer les notes ferm&eacute;es';

$L['f_Search_other_section']='Chercher d\'autres '.$L['items'].' depuis la section';
$L['f_Show_only_type']='Afficher uniquement le type';
$L['f_Enter_id']='Entrez un id ou une partie d\'id';
$L['f_Add_direction']='Ajouter la s&eacute;lection avec la direction';
$L['f_Add_parent']='(P) indique un '.$L['item'].' d&eacute;j&agrave; inclus dans un parent. En l\'ajoutant vous le changez de parent.';

$L['Confirm_delete_notes']='Ceci va effacer la(les) note(s). Vous confirmez ?';

// Stats

$L['General_site']='Site en g&eacute;n&eacute;ral';
$L['Board_start_date']='Date de d&eacute;but du site';

// Search

$L['Recent_notes']='Notes&nbsp;r&eacute;centes';
$L['All_my_notes']='Toutes&nbsp;mes&nbsp;notes';
$L['Advanced_search']='Recherche avanc&eacute;e';
$L['H_Search']='(Id ou type)';
$L['H_Search_criteria']='Vous pouvez entrer plusieurs valeurs s&eacute;par&eacute;es par '.QNM_QUERY_SEPARATOR.' (ex.: c1,c2 signifie "c1" ou "c2").';
$L['Search_options']='Options de recherche';
$L['Search_criteria']='Rechercher les '.$L['items'];
$L['Search_by_key']='Ayant une note contentant le(s) mot(s)';
$L['Search_by_id']='Chercher un '.$L['item'].' ayant l\'Id';
$L['Search_by_status']='Chercher par statut';
$L['Search_by_tag']='Chercher par cat&eacute;gories';
$L['Search_by_field']='Chercher par attribut';
$L['Search_result']='R&eacute;sultat de la recherche';
$L['All_sections']='Toutes les sections';
$L['Only_notes_in_process']='Notes ouvertes uniquement';
$L['Any_type']='Tout type';
$L['Any_status']='Tout status';
$L['Too_many_keys']='Trop de mots';
$L['Search_by_words']='Chercher chaque mot s&eacute;par&eacute;ment';
$L['Search_exact_words']='Chercher ces mots exactements';
$L['Search_by_date']='Chercher par date';
$L['This_week']='Cette semaine';
$L['This_month']='Ce mois';
$L['This_year']='Cette ann&eacute;e';
$L['With_tag']= 'Cat&eacute;gorie';
$L['Show_only_tag']='Certains '.$L['items'].' ont les cat&eacute;gories suivantes :<br/>(clickez pour rechercher les '.$L['items'].' ayant cette cat&eacute;gorie)';

// Search result

$L['Search_results_id']='%s '.$L['items'].' avec id %s';
$L['Search_results_keyword']='%s '.$L['item'].' ayant des notes contenant %s';
$L['Search_results_user']='%s notes cr&eacute;&eacute;es par %s';
$L['Search_results_last']='%s notes r&eacute;centes (derni&egrave;re semaine)';
$L['Search_results_field']='%s r&eacute;sultats pour %s';
$L['Search_results_date']='%s '.$L['items'].' avec date de creation ';
$L['Only_in_section']='Uniquement dans la section';
$L['Having_typename_containing']='Uniquement de type';
$L['Only_status']='Uniquement le statut';

// Ajax helper

$L['All_categories']='Toutes les cat&eacute;gories';
$L['Category_not_yet_used']='Cat&eacute;gorie non utilis&eacute;e';
$L['Impossible']='Impossible';
$L['No_result']='Aucun r&eacute;sultat';
$L['Try_other_lettres'] = 'Essayez d\'autres lettres';
$L['Try_without_options'] = 'Essayez sans option';

// Privacy

$L['Privacy_visible_0']='Non visible';
$L['Privacy_visible_1']='Visible uniquement par les membres';
$L['Privacy_visible_2']='Visible par tout le monde';

// Restrictions

$L['R_member']='Acc&egrave;s r&eacute;serv&eacute; aux seuls membres.<br/><br/>Veuillez vous connecter pour pouvoir continuer. Pour devenir membre, utilisez le menu s\'enregistrer.';
$L['R_staff']='Acc&egrave;s r&eacute;serv&eacute; aux seuls membres du staff. <a href="qtr_index.php">Exit</a>';
$L['R_security']='Les param&egrave;tres de s&eacute;curit&eacute;s ne permettent pas d\'utiliser cette fonction.';

// Errors

$L['No_item']='Aucun '.$L['item'].' trouv&eacute;';
$L['No_selected_row']='Au moins un '.$L['item'].' doit &ecirc;tre s&eacute;lectionn&eacute;.\nPour s&eacute;lectionner un '.$L['item'].', cochez la case en d&eacute;but de ligne.\nPour s&eacute;lectionner plusieurs '.$L['items'].', vous pouvez utiliser SHIFT+click.';
$L['E_already_used']='D&eacute;j&agrave; utilis&eacute;';
$L['E_char_max']='(maximum %d caracteres)';
$L['E_editing']='Des donn&eacute;es sont modifi&eacute;es. Quitter sans sauver?';
$L['E_file_size']='Fichier trop gros';
$L['E_invalid']='non invalide';
$L['E_javamail']='Protection: activez java pour voir les adresses e-mail';
$L['E_line_max']='(maximum %d lignes)';
$L['E_min_4_char']='Minimum 4 caract&egrave;res';
$L['E_missing_http']='The url must start with http:// or https://';
$L['E_missing_items']='Missing number of '.$L['items'];
$L['E_no_desc']='Aucune description';
$L['E_no_public_section']='Le site ne contient pas de section publique. Pour acc&eacute;der aux sections priv&eacute;s, vous devez vous identifier.';
$L['E_no_title']='Veuillez donner un titre &agrave; ce nouvel '.$L['item'];
$L['E_no_visible_section']='Le site ne contient pas de section visible pour vous.';
$L['E_pwd_char']='Le mot de passe contient des carac&egrave;tres non-valides.';
$L['E_save']='Impossible de sauver...';
$L['E_section_closed']='Section&nbsp;ferm&eacute;e'; // use &nbsp; as space to avoid double ligne buttons
$L['E_text']='Probl&egrave;me avec votre texte.';
$L['E_too_long']='Message trop long';
$L['E_wait']='Veuillez patienter quelques secondes...';
$L['No_sub-item']='Aucun sous-'.$L['item'];

// Success

$L['S_update']='Changement effectu&eacute;...';
$L['S_delete']='Effacement effectu&eacute;...';
$L['S_insert']='Cr&eacute;ation termin&eacute;e...';
$L['S_preferences']='Pr&eacute;f&eacute;rences enregistr&eacute;es';
$L['S_save']='Sauvegarde r&eacute;ussie...';
$L['S_message_saved']='Message sauv&eacute;...<br/>Merci';
$L['Item_added']='El&eacute;ment ajout&eacute;';
$L['Item_removed']='El&eacute;ment enlev&eacute;';
$L['Relation_added']='Relation ajout&eacute;e';
$L['Relation_removed']='Relation enlev&eacute;e';

// Dates

$L['dateMMM']=array(1=>'Janvier','F&eacute;vrier','Mars','Avril','Mai','Juin','Juillet','Ao&ucirc;t','Septembre','Octobre','Novembre','D&eacute;cembre');
$L['dateMM'] =array(1=>'Jan','Fev','Mar','Avr','Mai','Juin','Juil','Aout','Sept','Oct','Nov','Dec');
$L['dateM']  =array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche');
$L['dateDD'] =array(1=>'Lu','Ma','Me','Je','Ve','Sa','Di');
$L['dateD']  =array(1=>'L','M','M','J','V','S','D');
$L['dateSQL']=array(
  'January'  => 'Janvier',
  'February' => 'F&eacute;vrier',
  'March'    => 'Mars',
  'April'    => 'Avril',
  'May'      => 'Mai',
  'June'     => 'Juin',
  'July'     => 'Juillet',
  'August'   => 'Ao&ucirc;t',
  'September'=> 'Septembre',
  'October'  => 'Octobre',
  'November' => 'Novembre',
  'December' => 'D&eacute;cembre',
  'Monday'   => 'Lundi',
  'Tuesday'  => 'Mardi',
  'Wednesday'=> 'Mercredi',
  'Thursday' => 'Jeudi',
  'Friday'   => 'Vendredi',
  'Saturday' => 'Samedi',
  'Sunday'   => 'Dimanche',
  'Today'    => 'Aujourd\'hui',
  'Yesterday'=> 'Hier',
  'Now'=>'Maintenant',
  'Jan'=>'Jan',
  'Feb'=>'F&eacute;v',
  'Mar'=>'Mars',
  'Apr'=>'Avr',
  'May'=>'Mai',
  'Jun'=>'Jun',
  'Jul'=>'Jul',
  'Aug'=>'Ao&ucirc;t',
  'Sep'=>'Sep',
  'Oct'=>'Oct',
  'Nov'=>'Nov',
  'Dec'=>'D&eacute;c',
  'Mon'=>'Lu',
  'Tue'=>'Ma',
  'Wed'=>'Me',
  'Thu'=>'Je',
  'Fri'=>'Ve',
  'Sat'=>'Sa',
  'Sun'=>'Di');
