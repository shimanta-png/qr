<!DOCTYPE html>
<html>

<head>
    <title>Contact to QR Code</title>
</head>

<body>

<?php
require_once('vendor/autoload.php');

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;

$qrImage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $state     = trim($_POST['state'] ?? '');
    $postcode  = trim($_POST['postcode'] ?? '');
    $country   = trim($_POST['country'] ?? '');
    $logo      = $_POST['logo'] ?? '';
    $color     = $_POST['color'] ?? 'black';

    if (empty($firstName) || empty($phone)) {
        die("First Name and Phone are required!");
    }

    // ---------- CREATE VCARD ----------
    $vcard  = "BEGIN:VCARD\n";
    $vcard .= "VERSION:3.0\n";
    $vcard .= "N:$lastName;$firstName\n";
    $vcard .= "FN:$firstName $lastName\n";
    $vcard .= "TEL;TYPE=CELL:$phone\n";
    $vcard .= "EMAIL:$email\n";
    $vcard .= "ADR;TYPE=HOME:;;$address;$state;;$postcode;$country\n";
    $vcard .= "END:VCARD";

    // ---------- COLOR SWITCH ----------
    switch ($color) {
        case 'red':    $rgb = [255, 0, 0]; break;
        case 'blue':   $rgb = [0, 191, 255]; break;
        case 'yellow': $rgb = [255, 204, 0]; break;
        case 'violet': $rgb = [128, 0, 255]; break;
        default:       $rgb = [0, 0, 0];
    }

    // ---------- QR OPTIONS ----------
    $options = new QROptions([
        'version' => null,
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        // 'eccLevel' => QRCode::ECC_H,
        'eccLevel' => QRCode::ECC_Q,
        'scale' => 6,
        'addLogoSpace' => !empty($logo),
        'logoSpaceWidth' => 13,
        'logoSpaceHeight' => 13,

        'moduleValues' => [
            QRMatrix::M_DATA_DARK       => $rgb,
            QRMatrix::M_FINDER_DARK     => $rgb,
            QRMatrix::M_ALIGNMENT_DARK  => $rgb,
            QRMatrix::M_TIMING_DARK     => $rgb,
            QRMatrix::M_FORMAT_DARK     => $rgb,
            QRMatrix::M_VERSION_DARK    => $rgb,
        ],
    ]);

    $qr = new QRCode($options);
    $qrImage = $qr->render($vcard);

    // ---------- ADD LOGO ----------
    if (!empty($logo) && file_exists(__DIR__ . '/logos/' . $logo . '.png')) {

        $qrImg = imagecreatefromstring(file_get_contents($qrImage));
        $logoImg = imagecreatefrompng(__DIR__ . '/logos/' . $logo . '.png');

        $qrWidth = imagesx($qrImg);
        $qrHeight = imagesy($qrImg);

        $logoWidth = imagesx($logoImg);
        $logoHeight = imagesy($logoImg);

        $logoSize = $qrWidth / 4;

        imagecopyresampled(
            $qrImg,
            $logoImg,
            ($qrWidth - $logoSize) / 2,
            ($qrHeight - $logoSize) / 2,
            0,
            0,
            $logoSize,
            $logoSize,
            $logoWidth,
            $logoHeight
        );

        ob_start();
        imagepng($qrImg);
        $qrImage = 'data:image/png;base64,' . base64_encode(ob_get_clean());
    }
}
?>

<h2>Generate Contact QR Code</h2>

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

<?php if ($qrImage): ?>
    <h3>Your Contact QR Code:</h3>
    <img src="<?php echo $qrImage; ?>" alt="QR Code">
<?php endif; ?>

</body>
</html>