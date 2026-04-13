<!DOCTYPE html>
<html>
<head>
    <title>WhatsApp QR Code</title>
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

    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $color = $_POST['color'] ?? 'black';
    switch ($color) {
        case 'red':    $rgb = [255,0,0]; break;
        case 'blue':   $rgb = [0,191,255]; break;
        case 'yellow': $rgb = [255,204,0]; break;
        case 'violet': $rgb = [128,0,255]; break;
        case 'green': $rgb =  [0,128,0]; break;
        default:       $rgb = [0,0,0];
    }

    // Clean phone (only numbers)
    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (empty($phone)) {
        die("Phone number is required!");
    }

    if (strlen($message) > 100) {
        die("Message too long! Max 100 characters.");
    }

    // Encode message
    $encodedMessage = urlencode($message);

    // WhatsApp link
    $waLink = "https://wa.me/$phone?text=$encodedMessage";

    $writer = new PngWriter();

    $qrCode = new QrCode(
        data: $waLink,
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        size: 300,
        margin: 10,
        foregroundColor: new Color($rgb[0], $rgb[1], $rgb[2]),
        backgroundColor: new Color(255,255,255)
    );

    // Attach WhatsApp Logo
    $logoPath = __DIR__ . '/logos/whatsapp.png';
    $logo = null;

    if (file_exists($logoPath)) {
        $logo = new Logo(
            path: $logoPath,
            resizeToWidth: 100, // adjust size if needed
            punchoutBackground: true // cleaner look
        );
    }

    // Generate QR with logo
    $result = $writer->write($qrCode, $logo);
    $qrImage = $result->getDataUri();
}
?>

<form method="POST">
    <label>Enter WhatsApp Number:</label><br>
    <input type="text" name="phone" placeholder="919876543210" required>
    <br><br>

    <label>Enter Message:</label><br>
    <textarea name="message" maxlength="100" placeholder="Type your message..."></textarea>
    <br><br>

    <label>Select QR Color:</label>
    <select name="color">
        <option value="black">Black</option>
        <option value="red">Red</option>
        <option value="blue">Blue</option>
        <option value="yellow">Yellow</option>
        <option value="violet">Violet</option>
        <option value="green">Green</option>
    </select>

    <button type="submit">Generate WhatsApp QR</button>
</form>

<?php if (!empty($qrImage)): ?>
    <h3>Your WhatsApp QR Code:</h3>
    <img src="<?= $qrImage ?>" alt="WhatsApp QR Code">
<?php endif; ?>

</body>
</html>