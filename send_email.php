<?php
    // use phpmailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    require 'lib/PHPMailer/src/Exception.php';
    require 'lib/PHPMailer/src/PHPMailer.php';
    require 'lib/PHPMailer/src/SMTP.php';
    require 'config/config.php';
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data and validate
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $size = filter_input(INPUT_POST, 'size', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $aantal = filter_input(INPUT_POST, 'bezoekers', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $info = filter_input(INPUT_POST, 'info', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $slot = filter_input(INPUT_POST, 'slot', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);




    // variable email data 1
    $headercontent = "Nieuwe afspraak aanvraag";
    $footercontent = "Bookme - Uit-liefde";

    // see config
    //$to = "support@webdesigndenhaag.net"; // Replace with your email address
    //$toname = 'Webbouwer Test Server';
    //$subject = "Nieuwe Afspraak Aanvraag";

    $fmtDate = new IntlDateFormatter('nl_NL', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $fmtTime = new IntlDateFormatter('nl_NL', IntlDateFormatter::NONE, IntlDateFormatter::SHORT);
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { background-color: #f9f9f9; padding: 20px; border-radius: 10px; }
            .header { background-color: #c40079; padding: 10px; border-radius: 10px 10px 0 0; }
            .header h1 { margin: 0; color: #ffffff; }
            .content { padding: 20px; }
            .content p { margin: 10px 0; }
            .footer { background-color: #c40079; padding: 10px; border-radius: 0 0 10px 10px; text-align: center; color: #ffffff; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>$headercontent</h1>
            </div>
            <div class='content'>
                <p><strong>Datum:</strong> " . $fmtDate->format(new DateTime($slot['start'])) . "</p>
                <p><strong>Tijdslot:</strong> " . $fmtTime->format(new DateTime($slot['start'])) . " - " . $fmtTime->format(new DateTime($slot['end'])) . " </p>
                <p><strong>Naam:</strong> $name</p>
                <p><strong>E-mail:</strong> $email</p>
                <p><strong>Telefoon:</strong> $telephone</p>
                <p><strong>Plaats:</strong> $city</p>
                <p><strong>Kledingmaat:</strong> $size</p>
                <p><strong>Aantal bezoekers:</strong> $aantal</p>
                <p><strong>Aanvullende Info:</strong> $info</p>
            </div>
            <div class='footer'>
                <p>$footercontent</p>
            </div>
        </div>
    </body>
    </html>";
    $altbody = "Naam: $name\nE-mail: $email\nTelefoon: $telephone\nStad: $city\nGrootte: $size\nAantal bezoekers: $aantal\nAanvullende Info: $info\nDatum: $date\nTijdslot: " . $fmtDate->format(new DateTime($slot['start'])) . " (" . $fmtTime->format(new DateTime($slot['start'])) . " - " . $fmtTime->format(new DateTime($slot['end'])) . ")";
    
    // guest = sender
    $send_from_address= $email; 
    $send_from_name = $name;
    // booking manager = receiver
    $send_to_address = $to;
    $send_to_name = $toname;
    
    $mail = new PHPMailer(true);
    
        // Server settings
        $mail->SMTPDebug = 2;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = $smtp_server;                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $smtp_username;                     // SMTP username
        $mail->Password   = $smtp_password;                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  //'tls';       // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = $smtp_port;         // 587;                           // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        // Recipients
        $mail->setFrom($send_from_address, $send_from_name);
        $mail->addAddress($send_to_address, $send_to_name);     // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional
        //$mail->addReplyTo('support@webdesigndenhaag.net', 'Information');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');
    
        // Content
        $mail->isHTML(true);            // Set email format to HTML
        $mail->Subject = $subject;      //'Here is the subject';
        $mail->Body    = $body;         //'This is the HTML message body <b>in bold!</b>';
        $mail->AltBody = $altbody; //'This is the body in plain text for non-HTML mail clients';
    
        // Send the email
    if(!$mail->send()){
        echo json_encode(["status" => "error", "message" => "E-mail verzenden mislukt..."]); // $mail->ErrorInfo;
        exit();
    } 




    // variable email data 2
    $headercontent = "Nieuwe afspraak aanvraag in behandeling";
    $footercontent = "Bookme - Uit-liefde";

    $from = $to; // see config
    $fromname = $toname; // see config
    // $subject = "Nieuwe afspraak aanvraag"; // see config

    $fmtDate = new IntlDateFormatter('nl_NL', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $fmtTime = new IntlDateFormatter('nl_NL', IntlDateFormatter::NONE, IntlDateFormatter::SHORT);
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { background-color: #f9f9f9; padding: 20px; border-radius: 10px; }
            .header { background-color: #c40079; padding: 10px; border-radius: 10px 10px 0 0; }
            .header h1 { margin: 0; color: #ffffff; }
            .content { padding: 20px; }
            .content p { margin: 10px 0; }
            .footer { background-color: #c40079; padding: 10px; border-radius: 0 0 10px 10px; text-align: center; color: #ffffff; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>$headercontent</h1>
            </div>
            <div class='content'>
                <p>Beste $name</p>
                <p>Wat leuk dat je jouw trouwjurk bij ons hoopt te vinden, daar gaan we ons best voor doen.</p>
                <p>We mailen je binnen 24 uur of op de door jou aangevraagde datum en tijdstip een vrijwilligster beschikbaar is.</p>
                <p><strong>Datum:</strong> " . $fmtDate->format(new DateTime($slot['start'])) . "</p>
                <p><strong>Tijdslot:</strong> " . $fmtTime->format(new DateTime($slot['start'])) . " - " . $fmtTime->format(new DateTime($slot['end'])) . " </p>
                <p><strong>Naam:</strong> $name</p>
                <p><strong>E-mail:</strong> $email</p>
                <p><strong>Telefoon:</strong> $telephone</p>
                <p><strong>Plaats:</strong> $city</p>
                <p><strong>Kledingmaat:</strong> $size</p>
                <p><strong>Aantal bezoekers:</strong> $aantal</p>
                <p><strong>Aanvullende Info:</strong> $info</p>
            </div>
            <div class='footer'>
                <p>$footercontent</p>
            </div>
        </div>
    </body>
    </html>";
    $altbody = "Naam: $name\nE-mail: $email\nTelefoon: $telephone\nStad: $city\nGrootte: $size\nAantal bezoekers: $aantal\nAanvullende Info: $info\nDatum: $date\nTijdslot: " . $fmtDate->format(new DateTime($slot['start'])) . " (" . $fmtTime->format(new DateTime($slot['start'])) . " - " . $fmtTime->format(new DateTime($slot['end'])) . ")";
    
    // guest = receiver
    $send_from_address= $from; 
    $send_from_name = $fromname;
    // booking manager = sender
    $send_to_address = $email;
    $send_to_name = $name;
    
    $mail = new PHPMailer(true);
    
        // Server settings
        $mail->SMTPDebug = 2;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = $smtp_server;                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $smtp_username;                     // SMTP username
        $mail->Password   = $smtp_password;                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  //'tls';       // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = $smtp_port;         // 587;                           // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        // Recipients
        $mail->setFrom($send_from_address, $send_from_name);
        $mail->addAddress($send_to_address, $send_to_name);     // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional
        //$mail->addReplyTo('support@webdesigndenhaag.net', 'Information');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');
    
        // Content
        $mail->isHTML(true);            // Set email format to HTML
        $mail->Subject = $subject;      //'Here is the subject';
        $mail->Body    = $body;         //'This is the HTML message body <b>in bold!</b>';
        $mail->AltBody = $altbody; //'This is the body in plain text for non-HTML mail clients';
    
        // Send the email
    if(!$mail->send()){
        echo json_encode(["status" => "error", "message" => "E-mail verzenden mislukt..."]); // $mail->ErrorInfo;
    } else {
        echo json_encode(["status" => "success", "message" => "E-mails succesvol verzonden naar $to en $email..."]);
        
    }
    
    
} else {
    echo json_encode(["status" => "error", "message" => "Ongeldige aanvraagmethode."]);
}


?>