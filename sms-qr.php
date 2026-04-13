<!DOCTYPE html>
<html>
<head>
    <title>SMS to QR Code</title>
</head>
<body>

<?php
require_once("vendor/autoload.php");

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;

$qrImage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $color = $_POST['color'] ?? 'black';

    switch ($color) {
        case 'red':    $rgb = [255,0,0]; break;
        case 'blue':   $rgb = [0,191,255]; break;
        case 'yellow': $rgb = [255,204,0]; break;
        case 'violet': $rgb = [128,0,255]; break;
        default:       $rgb = [0,0,0];
    }

    // Basic validation
    if (empty($phone)) {
        die("Phone number is required!");
    }

    // Limit message to 45 chars
    if (strlen($message) > 45) {
        die("Message must be max 45 characters!");
    }

    // Optional: clean phone (only digits + +)
    if (!preg_match('/^\+?[0-9]+$/', $phone)) {
        die("Invalid phone number!");
    }

    // SMS format
    $smsData = "SMSTO:$phone:$message";

    $writer = new PngWriter();

    $qrCode = new QrCode(
        data: $smsData,
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        size: 300,
        margin: 10,
        foregroundColor: new Color($rgb[0], $rgb[1], $rgb[2]),
        backgroundColor: new Color(255,255,255)
    );

    $result = $writer->write($qrCode);
    $qrImage = $result->getDataUri();
}
?>

<form method="POST">
    <label>Enter Phone Number:</label><br>
    <input type="text" name="phone" placeholder="+919876543210" required>
    <br><br>

    <label>Enter Message (max 45 chars):</label><br>
    <textarea name="message" maxlength="45" placeholder="Type your message..."></textarea>
    <br><br>

    <br><br>

    <label>Select QR Color:</label>
    <select name="color">
        <option value="black">Black</option>
        <option value="red">Red</option>
        <option value="blue">Blue</option>
        <option value="yellow">Yellow</option>
        <option value="violet">Violet</option>
    </select>

    <button type="submit">Generate SMS QR</button>
</form>

<?php if (!empty($qrImage)): ?>
    <h3>Your SMS QR Code:</h3>
    <img src="<?= $qrImage ?>" alt="SMS QR Code">
<?php endif; ?>

</body>
</html>