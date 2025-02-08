# bookme

Booking calendar - an appointment request app build with html, css, javacript, js fullcalendar library and php

## ICS soruce
The calendar requires an .ics source link for calculating booking options (realtime)
 
### Configuration
Create a folder 'config' and add following variables:
$icsurl = '<https://mysourcelink.ics>';
$smtp_server = 'mail.example.info';
$smtp_username = '<mysmtpusername>';
$smtp_password = '<mysmtppassword>';
$smtp_port = <portnumber>;
$to = "<mymailbox@example.info>";
$toname = '<receivername>';
$subject = "<messagetitle>";
$locallang = '<languagecode.UTF-8>';

#### Using PHPMailer class library for sending smtp mail
Required in send_email.php
Create a subfolder 'lib' and copy PHPMailer library folder into folder 'lib' (lib/PHPmailer)
