<!DOCTYPE html>
<html>
<head>
    <title>Email QR Code</title>
</head>
<body>

<?php
require_once("vendor/autoload.php");

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Logo\Logo;

$qrImage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $color   = $_POST['color'] ?? 'black';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address!");
    }

    // Optional limits (good UX)
    if (strlen($subject) > 40) {
        die("Subject too long! Max 40 chars.");
    }

    if (strlen($message) > 150) {
        die("Message too long! Max 150 chars.");
    }

    // Encode values
    $encodedSubject = $subject;
    $encodedMessage = $message;

    // Mailto link
    $mailLink = "mailto:$email?subject=$encodedSubject&body=$encodedMessage";

    // Color selection
    switch ($color) {
        case 'red':    $rgb = [255,0,0]; break;
        case 'blue':   $rgb = [0,191,255]; break;
        case 'yellow': $rgb = [255,204,0]; break;
        case 'violet': $rgb = [128,0,255]; break;
        case 'green': $rgb =  [0,128,0]; break;
        default:       $rgb = [0,0,0];
    }

    $writer = new PngWriter();

    $qrCode = new QrCode(
        data: $mailLink,
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        size: 300,
        margin: 10,
        foregroundColor: new Color($rgb[0], $rgb[1], $rgb[2]),
        backgroundColor: new Color(255,255,255)
    );

    // ✅ Email logo
    $logoPath = __DIR__ . '/logos/mail.png';
    $logo = null;

    if (file_exists($logoPath)) {
        $logo = new Logo(
            path: $logoPath,
            resizeToWidth: 100,
            punchoutBackground: true
        );
    }

    $result = $writer->write($qrCode, $logo);
    $qrImage = $result->getDataUri();
}
?>

<form method="POST">
    <label>Receiver Email:</label><br>
    <input type="email" name="email" placeholder="example@gmail.com" required>
    <br><br>

    <label>Subject:</label><br>
    <input type="text" name="subject" maxlength="40" placeholder="Enter subject">
    <br><br>

    <label>Message:</label><br>
    <textarea name="message" maxlength="150" placeholder="Type your message..."></textarea>
    <br><br>

    <label>Select QR Color:</label><br>
    <select name="color">
        <option value="black">Black</option>
        <option value="red">Red</option>
        <option value="blue">Blue</option>
        <option value="yellow">Yellow</option>
        <option value="violet">Violet</option>
         <option value="green">Green</option>
    </select>
    <br><br>

    <button type="submit">Generate Email QR</button>
</form>

<?php if (!empty($qrImage)): ?>
    <h3>Your Email QR Code:</h3>
    <img src="<?= $qrImage ?>" alt="Email QR Code">
<?php endif; ?>

</body>
</html>