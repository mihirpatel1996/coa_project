<?php
// api/images/create_placeholders.php
// Run this file once to create placeholder images

// Create directory if it doesn't exist
if (!file_exists(__DIR__)) {
    mkdir(__DIR__, 0755, true);
}

// Create placeholder logo (200x60 px)
$logo = imagecreate(200, 60);
$bg = imagecolorallocate($logo, 240, 240, 240);
$text_color = imagecolorallocate($logo, 100, 100, 100);
$border_color = imagecolorallocate($logo, 200, 200, 200);

// Draw border
imagerectangle($logo, 0, 0, 199, 59, $border_color);

// Add text
$text = "LOGO";
$font = 5; // Built-in font
$text_width = imagefontwidth($font) * strlen($text);
$text_height = imagefontheight($font);
$x = (200 - $text_width) / 2;
$y = (60 - $text_height) / 2;
imagestring($logo, $font, $x, $y, $text, $text_color);

// Save logo
imagepng($logo, 'placeholder_logo.png');
imagedestroy($logo);

// Create placeholder signature (150x50 px)
$signature = imagecreate(150, 50);
$bg = imagecolorallocate($signature, 255, 255, 255);
$line_color = imagecolorallocate($signature, 50, 50, 150);

// Draw a simple signature-like curve
$points = array();
for ($i = 0; $i < 150; $i += 5) {
    $y = 25 + sin($i / 10) * 10 + rand(-3, 3);
    $points[] = $i;
    $points[] = $y;
}

// Draw the signature line
for ($i = 0; $i < count($points) - 2; $i += 2) {
    imageline($signature, $points[$i], $points[$i + 1], 
              $points[$i + 2], $points[$i + 3], $line_color);
}

// Save signature
imagepng($signature, 'placeholder_signature.png');
imagedestroy($signature);

echo "Placeholder images created successfully!";
echo "<br>Logo: placeholder_logo.png (200x60)";
echo "<br>Signature: placeholder_signature.png (150x50)";
?>