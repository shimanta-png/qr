<!DOCTYPE html>
<html>

<head>
    <title>URL to QR Code</title>
</head>

<body>

    <?php
    require_once('vendor/autoload.php');

    use chillerlan\QRCode\QRCode;
    use chillerlan\QRCode\QROptions;
    use chillerlan\QRCode\Data\QRMatrix;

    $qrImage = '';

    if (isset($_POST['url']) && !empty($_POST['url'])) {

        $url = trim($_POST['url']);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            die("Invalid URL!");
        }

        $logo = $_POST['logo'] ?? '';

        $color = $_POST['color'] ?? 'black';

        switch ($color) {
            case 'red':
                $rgb = [255, 0, 0];
                break;
            case 'blue':
                $rgb = [0, 191, 255];
                break;
            case 'yellow':
                $rgb = [255, 204, 0];
                break;
            case 'violet':
                $rgb = [128, 0, 255];
                break;
            default:
                $rgb = [0, 0, 0];
        }

        $options = new QROptions([
            'version' => 5,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_H, // High because logo
            'scale' => 6,
            'addLogoSpace' => !empty($logo), // reserve space only if logo selected
            'logoSpaceWidth' => 13,
            'logoSpaceHeight' => 13,
            
            'moduleValues' => [
                QRMatrix::M_DATA_DARK => $rgb,
                QRMatrix::M_FINDER_DARK => $rgb,
                QRMatrix::M_ALIGNMENT_DARK => $rgb,
                QRMatrix::M_TIMING_DARK => $rgb,
                QRMatrix::M_FORMAT => $rgb,
                QRMatrix::M_VERSION => $rgb,
            ],
        ]);

        $qr = new QRCode($options);
        $qrImage = $qr->render($url);

        // If logo selected → merge it
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

    <form method="POST">
        <label>Enter URL:</label>
        <input type="text" name="url" placeholder="https://example.com" required>
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

        <button type="submit">Generate QR</button>
    </form>

    <?php if ($qrImage): ?>
        <h3>Your QR Code:</h3>
        <img src="<?php echo $qrImage; ?>" alt="QR Code">
    <?php endif; ?>

</body>

</html>