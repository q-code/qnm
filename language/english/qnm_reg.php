<?php

// Is is recommended to always use capital on first letter in the translation
// software change to lower case if necessary.

$L['Agree']='I have read, and agree to abide by these rules.';
$L['Proceed']='Proceed to registration';

// registration

$L['Choose_name']='Choose a username';
$L['Choose_password']='Choose a password';
$L['Old_password']='Old password';
$L['New_password']='New password';
$L['Confirm_password']='Confirm the password';
$L['Password_updated']='Password updated';
$L['Password_by_mail']='Temporary password will be send to your e-mail address.';
$L['Your_mail']='Your e-mail';
$L['Parent_mail']='Parent/guardian e-mail';
$L['Security']='Security';
$L['Reset_pwd']='Reset password';
$L['Reset_pwd_help']='The application will send by e-mail a new single-use access password key.';
$L['Type_code']='Type the security code you see.';
$L['Request']='Request';
$L['Request_completed']='Request completed';

// login and profile

$L['Remember']='Remember me';
$L['Forgotten_pwd']='Forgotten password';
$L['Change_password']='Change password';
$L['Change_picture']='Change picture';
$L['H_Change_picture']='(maximum '.$_SESSION[QT]['avatar_width'].'x'.$_SESSION[QT]['avatar_height'].' pixels, '.$_SESSION[QT]['avatar_size'].' Kb)';
$L['Picture_thumbnail'] = 'The uploaded image is too large.<br />To define your picture, draw a square in the large image.';
$L['My_picture']='My picture';
$L['Picture_updated']='picture updated';
$L['Delete_picture']='Delete picture';
$L['Picture_deleted']='Picture deleted';
$L['Change_role']='Change role';
$L['W_Somebody_else']='Caution... You are edition the profile of somebody else';
$L['Change_ban']='Change ban';
$L['H_ban']='Select the ban duration';
$L['Ban_user']='Ban user';
$L['Is_banned']='Is banned';
$L['Is_banned_nomore']='<h2>Welcome back...</h2><p>Your account has been re-opened.<br/>Re-try login now...</p>';
$L['Since']='since';
$L['Retry_tomorrow']='Try again tomorrow or contact the Administrator.';

// Secret question

$L['Secret_question']='Secret question';
$L['H_Secret_question']='This question will be asked if you forget your password.';
$L['Update_secret_question']='Your profile must be updated...<br /><br />To improve security, we request you to define your own "Secret question". This question will be asked if you forget your password.';
$L['Secret_q']['What is the name of your first pet?']='What is the name of your first pet?';
$L['Secret_q']['What is your favorite character?']='What is your favorite character?';
$L['Secret_q']['What is your favorite book?']='What is your favorite book?';
$L['Secret_q']['What is your favorite color?']='What is your favorite color?';
$L['Secret_q']['What street did you grow up on?']='What street did you grow up on?';
$L['Unregister']='Unregister';
$L['H_Unregister']='<p>By unregistering, you will stop having access to this application as a user.<br />Your profile will be deleted and your account will no more be visible in the userlist.</p><p>Enter your password to confirm unregistration...</p>';

// Error

$L['E_pixels_max']='Pixels maximum';

// Help

$L['Reg_help']='Please fill in this form to complete your registration.<br/><br/>Username and password must be at least 4 characters without tags or trailing spaces.<br/><br/>E-mail address will be used to send you a new password if you forgot it. It is visible for registrered members only. To make it invisible, change your privacy settings in your profile.<br/><br/>If you are visually impaired or cannot otherwise read the security code please contact the <a class="small" href="mailto:'.$_SESSION[QT]['admin_email'].'">Administrator</a> for help.<br/><br/>';
$L['Reg_mail']='You will receive an email shortly including a temporary password.<br/><br/>You are invited to log in and edit your profile to define your own password.';
$L['Reg_pass']='Password reset.<br/><br/>If you have forgotten your password, please enter your username and email address. We will send you a single-use access password key that will allow you to select a new password.';