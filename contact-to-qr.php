<!DOCTYPE html>
<html>
<head>
    <title>Contact to QR Code</title>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['first_name']) && !empty($_POST['phone'])) {

    // Sanitize Inputs
    $firstName = trim($_POST['first_name']);
    $lastName  = trim($_POST['last_name'] ?? '');
    $phone     = trim($_POST['phone']);
    $email     = trim($_POST['email'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $state     = trim($_POST['state'] ?? '');
    $postcode  = trim($_POST['postcode'] ?? '');
    $country   = trim($_POST['country'] ?? '');

    // Build vCard
    $vCard = "BEGIN:VCARD\n";
    $vCard .= "VERSION:3.0\n";
    $vCard .= "N:$lastName;$firstName\n";
    $vCard .= "FN:$firstName $lastName\n";
    $vCard .= "TEL;TYPE=CELL:$phone\n";

    if (!empty($email)) {
        $vCard .= "EMAIL:$email\n";
    }

    if (!empty($address)) {
        $vCard .= "ADR;TYPE=HOME:;;$address;$state;;$postcode;$country\n";
    }

    $vCard .= "END:VCARD";

    // Color Selection
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
        data: $vCard,
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        size: 300,
        margin: 10,
        foregroundColor: new Color($rgb[0], $rgb[1], $rgb[2]),
        backgroundColor: new Color(255,255,255)
    );

    // Logo handling
    $logo = $_POST['logo'] ?? '';
    $logoObj = null;

    if (!empty($logo) && file_exists(__DIR__ . '/logos/' . $logo . '.png')) {
        $logoObj = new Logo(
            path: __DIR__ . '/logos/' . $logo . '.png',
            resizeToWidth: 90,
            punchoutBackground: true
        );
    }

    $result = $writer->write($qrCode, $logoObj);
    $qrImage = $result->getDataUri();
}
?>

<h2>Contact to QR Code Generator</h2>

<form method="POST">

    <label>First Name:</label>
    <input type="text" name="first_name" required><br><br>

    <label>Last Name:</label>
    <input type="text" name="last_name"><br><br>

    <label>Phone:</label>
    <input type="text" name="phone" required><br><br>

    <label>Email:</label>
    <input type="email" name="email"><br><br>

    <label>Address:</label>
    <input type="text" name="address"><br><br>

    <label>State:</label>
    <input type="text" name="state"><br><br>

    <label>Postcode:</label>
    <input type="text" name="postcode"><br><br>

    <label>Country:</label>
    <input type="text" name="country"><br><br>

    <label>Select Logo:</label>
    <select name="logo">
        <option value="">None</option>
        <option value="facebook">Facebook</option>
        <option value="github">GitHub</option>
        <option value="instagram">Instagram</option>
    </select><br><br>

    <label>Select QR Color:</label>
    <select name="color">
        <option value="black">Black</option>
        <option value="red">Red</option>
        <option value="blue">Blue</option>
        <option value="yellow">Yellow</option>
        <option value="violet">Violet</option>
    </select><br><br>

    <button type="submit">Generate QR</button>

</form>

<?php if (!empty($qrImage)): ?>
    <h3>Your Contact QR Code:</h3>
    <img src="<?= $qrImage ?>" alt="Contact QR Code">
<?php endif; ?>

</body>
</html>