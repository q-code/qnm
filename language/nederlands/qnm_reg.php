<?php

// Is is recommended to always use capital on first letter in the translation
// software change to lower case if necessary.

$L['Agree']='I have read, and agree to abide by these rules.';
$L['Proceed']='Proceed to registration';

// registration

$L['Choose_name']='Kies een gebruikersnaam';
$L['Choose_password']='Kies een wachtwoord';
$L['Old_password']='Oude wachtwoord';
$L['New_password']='Nieuwe wachtwoord';
$L['Confirm_password']='Bevestig wachtwoord';
$L['Password_updated']='Warchtwoord bijgewerkt';
$L['Password_by_mail']='Temporary password will be send to your e-mail address.';
$L['Your_mail']='Your e-mail';
$L['Parent_mail']='Ouder/beschermer e-mail';
$L['Security']='Veiligheid';
$L['Reset_pwd']='Herstel wachtwoord';
$L['Reset_pwd_help']='De applicatie zal door e-mail een nieuwe wachtwoord sturen.';
$L['Type_code']='Typ de code u ziet.';
$L['Request']='Aanvraag';
$L['Request_completed']='Aanvraag gestuurd';

// login and profile

$L['Remember']='Log me automatisch in';
$L['Forgotten_pwd']='Wachtwoord vergeten';
$L['Change_password']='Watchwoord veranderen';
$L['Change_picture']='Foto bijwerken';
$L['H_Change_picture']='(maximum '.$_SESSION[QT]['avatar_width'].'x'.$_SESSION[QT]['avatar_height'].' pixels, '.$_SESSION[QT]['avatar_size'].' Kb)';
$L['Picture_thumbnail'] = 'Beeld is te groot.<br />Om uw foto te make, schetst een rechthoek in het grote beeld.';
$L['My_picture']='Mijn foto';
$L['Picture_updated']='Foto bijgewerkt';
$L['Delete_picture']='Foto verwijderen';
$L['Picture_deleted']='Foto verwijderd';
$L['Change_role']='Rol veranderen';
$L['W_Somebody_else']='Pas op... U geeft het profiel van iemand anders uit';
$L['Change_ban']='Verbannen';
$L['H_ban']='Selecteer de verbodsduur';
$L['Ban_user']='Verbannen';
$L['Is_banned']='Is verbannen';
$L['Is_banned_nomore']='<h2>Welkom terug...</h2><p>U wordt niet meer verboden.<br />U kan nu inloggen.</p>';
$L['Since']='sinds';
$L['Retry_tomorrow']='Probeer morgen opnieuw of contacteer de systembeheerder.';

// Secret question

$L['Secret_question']='Geheime vraag';
$L['H_Secret_question']='Deze vraag zal worden gevraagd of vergeet u uw wachtwoord.';
$L['Update_secret_question']='Uw profiel moet bijgewerkt worden...<br /><br />Om veiligheid te verbeteren, verzoeken wij u om uw eigen "Geheime vraag" te bepalen. Deze vraag zal worden gevraagd of vergeet u uw wachtwoord.';
$L['Secret_q']['What is the name of your first pet?']='Wat was de naam van uw eerste huisdier?';
$L['Secret_q']['What is your favorite character?']='Wie is uw favoriet personage?';
$L['Secret_q']['What is your favorite book?']='Wat is uw favoriet boek?';
$L['Secret_q']['What is your favorite color?']='Wat is uw favoriet kleur?';
$L['Secret_q']['What street did you grow up on?']='In welke straat groeide u ?';
$L['Unregister']='Afmelden';
$L['H_Unregister']='<p>By unregistering, you will stop having access to this application as a member.<br />Your profile will be deleted and your account will no more be visible in the memberlist. Your messages will remain visible.<br />If other users try to access your profile, they will got the profile of "Visitor".</p><p>Enter your password to confirm unregistration...</p>';

// Error

$L['E_pixels_max']='Pixels maximum';

// Help

$L['Reg_help']='Gelieve te vullen deze vorm in om uw registratie te voltooien.<br /><br />De gebruikersnaam en het wachtwoord moeten minstens 4 karakters zonder html markeringen of ruimten zijn.<br /><br />Het e-mail adres zal worden gebruikt om u een nieuw wachtwoord te verzenden als u het vergat. Het is zichtbaar voor registrered slechts leden. Om het onzichtbaar te maken, verander uw privacy instellingen in uw Profiel.<br /><br />Als u met gezichtsstoornissen bent of de veiligheidscode niet kunt lezen, gelieve de <a class="small" href="mailto:'.$_SESSION[QT]['admin_email'].'">Beheerder</a> te contacteren.<br /><br />';
$L['Reg_mail']='U zult binnenkort een e-mail met een tijdelijk wachtwoord ontvangen.<br /><br />U wordt verzocht om uw profiel uit te geven en uw eigen wachtwoord te bepalen.';
$L['Reg_pass']='Nieuwe wachtwoord.<br /><br />Als u uw wachtwoord hebt vergeten, gelieve uw gebruikersnaam invullen. Wij zullen u een tijdelijk wachtwoord verzenden die u zal toestaan om een nieuw wachtwoord te selecteren.';
$L['Reg_pass_reset']='Wij kunnen u een nieuw wachtwoord verzenden als u uw geheime vraag kunt beantwoorden.';