<?php

$L['Ok'] = 'Ok';
$L['Save'] = 'Sauver';
$L['Done'] = 'Termin&eacute;';
$L['Back'] = '&lt;&nbsp;Pr&eacute;c&eacute;dent';
$L['Next'] = 'Suivant&nbsp;&gt;';
$L['Finish'] = 'Termin&eacute;';
$L['Restart'] = 'Red&eacute;marrer';
$L['Board_email'] = 'E-mail administrateur';
$L['User'] = 'Utilisateur';
$L['Password'] = 'Mot de passe';
$L['Installation'] = 'Installation';
$L['Install_db'] = 'Installation des tables QNM';
$L['Connection_db'] = 'Parm&egrave;tres de connexion &agrave; la base de donn&eacute;e (BDD)';
$L['Database_type'] = 'Type de BDD';
$L['Database_host'] = 'H&ocirc;te de la BDD (host/port/dsn)';
$L['Database_name'] = 'Nom de la BDD';
$L['Database_user'] = 'Utilisateur BDD (login/password)';
$L['Table_prefix'] = 'Pr&eacute;fixe des tables';
$L['Htablecreator'] = 'Si l\'utilisateur BDD n\'a pas le droit de cr&eacute;er des tables, vous pouvez sp&eacute;cifier ici un autre login.';
$L['Create_tables'] = 'Cr&eacute;er les tables dans la base de donn&eacute;e [%s]';
$L['End_message'] = 'Vous pouvez acc&eacute;der au forum en tant qu\'Admin';
$L['Upgrade'] = 'Si vous faites un upgrade de la version 1.x, vos pr&eacute;c&eacute;dents param&egrave;tres sont affich&eacute;s. Vous pouvez passer &agrave; l\'&eacute;tape suivante.';
$L['Upgrade2'] = 'Si vous faites un upgrade de la version 1.x, vous ne devez PAS r&eacute;installer les tables. Vous pouvez passer &agrave; l\'&eacute;tape suivante.';
$L['Check_install'] = 'Contr&ocirc;ler l\'installation';

$L['Default_setting'] = 'param&egrave;tres par d&eacute;faut ins&eacute;r&eacute;s.';
$L['Default_domain'] = 'domaine par d&eacute;faut ins&eacute;r&eacute;.';
$L['Default_section'] = 'section par d&eacute;faut ins&eacute;r&eacute;e.';
$L['Default_user'] = 'utilisateurs par d&eacute;faut ins&eacute;r&eacute;s.';

$L['S_connect'] = 'Connexion r&eacute;ussie...';
$L['E_connect'] = "<br/><br/><b>Probl&egrave;me de connexion &agrave; la base de donn&eacute;e [%s] sur le serveur [%s]</b><br/><br/>Causes possibles :<br/>&raquo;&nbsp;Le nom du serveur est incorrect.<br/>&raquo;&nbsp;La nom de la base de donn&eacute;e est incorrect.<br/>&raquo;&nbsp;Le login (ou mot de passe) est incorrect.<br/>";
$L['S_save'] = 'Savegarde r&eacute;ussie...';
$L['E_save'] = "<br/><br/><b>Probl&egrave;me pour &eacute;crire dans le r&eacute;pertoire /bin/</b><br/><br/>Causes possibles :<br/>&raquo;&nbsp;Le fichier /bin/config.php est absent.<br/>&raquo;&nbsp;Le fichier /bin/config.php est \'read-only\'.<br/>";

$L['N_install'] = 'Ici se termine la proc&eacute;dure d\'installation.';
$L['S_install'] = 'Installation termin&eacute;e...';
$L['E_install'] = "<b>Probl&egrave;me pour cr&eacute;er la table [%s] dans la base de donn&eacute;e [%s]</b><br/><br/>Causes possibles :<br/>&raquo;&nbsp;La table existe d&eacute;j&agrave; (effacez-la ou utilisez un pr&eacute;fixe).<br/>&raquo;&nbsp;L\'utilisateur [%s] n\'a pas le droit de cr&eacute;er des tables dans la base de donn&eacute;e.<br/>";
$L['S_install_exit'] = 'L\'installation s\'est correctement d&eacute;roul&eacute;e...<br/><br/>N\'oubliez pas de :<br/>- Mettre le forum en-ligne (Panneau d\'administration)<br/>- Changer le mot de passe de l\'administrateur<br/>- Effacer le r&eacute;pertoire /install/<br/><br/>';

$L['Help_1'] = '<b>Type de base de donn&eacute;e</b>: Indiquez le type de base de donn&eacute;e que vous utilisez.<br/><br/><b>H&ocirc;te de la BDD</b> (nom du serveur): Si la BDD est sur le m&ecirc;me serveur que le serveur web, utilisez "localhost". Laissez le port vide &agrave; moins que vous n\'utilisiez une base de donn&eacute;e PostgreSQL (port 5432). Laissez le DSN vide, &agrave; moins que vous n\'utilisiez une connexion ODBC.<br/><br/><b>Nom de la BDD</b>: Indiquez ici le nom de votre base de donn&eacute;e. Pour Oracle Express, utilisez "//localhost/XE".<br/><br/><b>Pr&eacute;fixe des tables</b>: Si vous avez plusieurs applications dans la m&ecirc;me BDD, vous pouvez ajouter un pr&eacute;fixe au nom des tables.<br/><br/><b>Utilisateur BDD</b>: L\'utilisateur ayant le droit d\'ajouter/modifier/effacer dans la base de donn&eacute;e. Le second administrateur n\'est pas obligatoire.<br/><br/><b>Pour la BDD et les logins</b>: Veillez a ce que la base de donn&eacute;e et les utilisateurs EXISTENT. Ce script ne fait qu\'ajouter les tables dans votre BDD. Il ne va pas cr&eacute;er la base de donn&eacute;e ni les accomptes BDD.<br/>';
$L['Help_2'] = '<b>Database tables</b>: Ceci va installer les tables dans votre base de donn&eacute;e. Si vous proc&eacute;dez  &agrave; un upgrade, veillez sauter cette &eacute;tape.<br/>';
$L['Help_3'] = '<b>E-mail administrateur</b>: Il est recommand&eacute; de donner une adresse de contact. Cette adresse est visible dans la page Conditions g&eacute;n&eacute;rales.<br/>';