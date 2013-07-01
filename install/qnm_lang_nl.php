<?php

$L['Ok'] = 'Ok';
$L['Save'] = 'Bewaar';
$L['Done'] = 'Gemaakt';
$L['Back'] = '&lt;&nbsp;Vorige';
$L['Next'] = 'Volgende&nbsp;&gt;';
$L['Finish'] = 'Eind';
$L['Restart'] = 'Nieuw begin';
$L['Board_email'] = 'Systeem beheer e-mail';
$L['User'] = 'Gebruiker';
$L['Password'] = 'Watchwoord';
$L['Installation'] = 'Installatie';
$L['Install_db'] = 'Installatie van QNM tabels';
$L['Connection_db'] = 'Parameters van aansluiting aan de database';
$L['Database_type'] = 'Database type';
$L['Database_host'] = 'Database host (host/port/dsn)';
$L['Database_name'] = 'Database naam';
$L['Database_user'] = 'Gebruiker (login/wachtwoord)';
$L['Table_prefix'] = 'Tabel prefixe';
$L['Create_tables'] = 'Tabels in uw database[%s] make';
$L['Htablecreator'] = 'Als database gebruiker geen recht heeft om de tabel te make, u can here een andere login/wachtwoord geven.';
$L['Upgrade'] = 'Als u een upgrade van versie 1.x maakt, hier zijn uw vorige parameters. U kan click Volgende.';
$L['Upgrade2'] = 'Als u een upgrade van versie 1.x maakt, u moet NIET tabel installeren.  Ga naar de volgende stap.';
$L['End_message'] = 'U kunt de forum als Admin bereiken';
$L['Check_install'] = 'Installatie controleren';

$L['Default_setting'] = 'parameters toegevoegd.';
$L['Default_domain'] = 'domain toegevoegd.';
$L['Default_section'] = 'forum toegevoegd.';
$L['Default_user'] = 'gebruikers toegevoegd.';

$L['S_connect'] = 'Aansluiting succesvol...';
$L['E_connect'] = "<br/><br/><b>Probleem met aansluiting aan de database [%s] op server [%s]</b><br/><br/>Mogelige reden :<br/>&raquo;&nbsp;De naam van de host is verkeerd.<br/>&raquo;&nbsp;De naam van de database is verkeerd.<br/>&raquo;&nbsp;De login (of wachtwoord) is verkeerd.<br/><br/>\n";
$L['S_save'] = 'Save succesvol...';
$L['E_save'] = "<br/><br/><b>Probleem om in de map /bin/ te schrijven</b><br/><br/>Mogelige reden :<br/>&raquo;&nbsp;Het bestand /bin/config.php is afwezig.<br/>&raquo;&nbsp;Het bestand /bin/config.php is 'read-only'.<br/><br/>\n";
$L['N_install'] = 'Installatie process be&euml;indigd';
$L['S_install'] = 'Installatie succesvol...';
$L['E_install'] = "<b>Probleem om de tabel [%s] int de database [%s] te maken</b><br/><br/>Mogelige reden :<br/>&raquo;&nbsp;De tabel bestaat al (u can dit uitwissen of een prefixe gebruiken).<br/>&raquo;&nbsp;De gebruiker [%s] heeft geen recht om tabel te maken.<br/><br/>\n";
$L['S_install_exit'] = 'Installatie is succesvol....<br/><br/>Vergeet niet:<br/>- Systeem on-line zetten (Administratie sectie)<br/>- Administrator wachtword veranderen<br/>- De map /install/ uitwissen<br/>';

$L['Help_1'] = '<b>Database type</b>: De type van uw database.<br/><br/><b>Database host</b> (server naam): Als de webserver en de database op de zelfde server staan, gebruik "localhost". Laat de port leeg, behalve voor PostgreSQL (port 5432). Laat de DSN leeg, behalve voor ODBC connectie.<br/><br/><b>Database naam</b>: Geef hier de naam van uw database. Voor Oracle Express de database naam is "//localhost/XE".<br/><br/><b>Tabel prefixe</b>: Als u hebt meerdere QT-registerations systeemen op de zelfde database, u can een prefixe voor de tabellen geven.<br/><br/><b>Gebruiker</b>: Gebruiker die in uw database update/delete/insert acties can maken. De tweede administrator is niet verplicht.<br/><br/><b>Wat betref database en logins</b>: De database en de gebruiker MOET bestaan. Dit installatie zal alleen tabellen maken in de bestaande database. Dit zal niet de database of de database user-account creeren.<br/>';
$L['Help_2'] = 'Als u een upgrade van versie 1.x maakt, u moet NIET tabel installeren. Ga naar de volgende stap.<br/>';
$L['Help_3'] = '<b>Contact e-mail</b>: Het is noodzakelijk om een contact e-mail te geven. Dit is zichtbaar in de pagina: Gebruiksvoorwaarden.<br/>';