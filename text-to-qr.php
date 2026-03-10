<!DOCTYPE html>
<html>
<head>
    <title>Text to QR Code</title>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['text'])) {

    $text = trim($_POST['text']);

    $logo = $_POST['logo'] ?? '';
    $color = $_POST['color'] ?? 'black';

    switch ($color) {
        case 'red':    $rgb = [255,0,0]; break;
        case 'blue':   $rgb = [0,191,255]; break;
        case 'yellow': $rgb = [255,204,0]; break;
        case 'violet': $rgb = [128,0,255]; break;
        default:       $rgb = [0,0,0];
    }

    $writer = new PngWriter();

    $qrCode = new QrCode(
        data: $text,
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        size: 300,
        margin: 10,
        foregroundColor: new Color($rgb[0], $rgb[1], $rgb[2]),
        backgroundColor: new Color(255,255,255)
    );

    $logoObj = null;

    if (!empty($logo) && file_exists(__DIR__ . '/logos/' . $logo . '.png')) {
        $logoObj = new Logo(
            path: __DIR__ . '/logos/' . $logo . '.png',
            resizeToWidth: 100,
            punchoutBackground: false
        );
    }

    $result = $writer->write($qrCode, $logoObj);
    $qrImage = $result->getDataUri();
}
?>

<form method="POST">

    <label>Enter Text:</label><br>
    <textarea name="text" rows="6" cols="50" placeholder="Enter any text here..." required></textarea>

    <br><br>

    <label>Select Logo:</label>
    <select name="logo">
        <option value="">None</option>
        <option value="discord">Discord</option>
        <option value="facebook">Facebook</option>
        <option value="github">GitHub</option>
        <option value="instagram">Instagram</option>
        <option value="paypal">PayPal</option>
        <option value="spotify">Spotify</option>
        <option value="twitch">Twitch</option>
        <option value="twitter">Twitter</option>
        <option value="youtube">YouTube</option>
        <option value="cart">Ecommerce Cart</option>
        <option value="techivolve">Techivolve</option>
    </select>

    <br><br>

    <label>Select QR Color:</label>
    <select name="color">
        <option value="black">Black</option>
        <option value="red">Red</option>
        <option value="blue">Blue</option>
        <option value="yellow">Yellow</option>
        <option value="violet">Violet</option>
    </select>

    <br><br>

    <button type="submit">Generate QR</button>

</form>

<?php if (!empty($qrImage)): ?>
    <h3>Your QR Code:</h3>
    <img src="<?= $qrImage ?>" alt="QR Code">
<?php endif; ?>

</body>
</html>