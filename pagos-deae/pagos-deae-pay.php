<?php
// Verifica que se haya proporcionado un checkoutId
if (!isset($_GET['checkoutId'])) {
    echo "Error: No se proporcionó un checkoutId.";
    exit;
}

// Obtén el checkoutId desde el parámetro GET
$checkoutId = sanitize_text_field($_GET['checkoutId']);
$baseUrl = home_url('/'); // Dirección base del sitio
$url = "https://eu-test.oppwa.com/v1/"; // URL del API para obtener recursos

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar</title>
    <script src="<?php echo $url ?>paymentWidgets.js?checkoutId=<?php echo $checkoutId ?>"></script>
</head>
<body>
    <h1>Formulario de Pago</h1>
    <form action="<?php echo $baseUrl ?>" class="paymentWidgets" data-brands="VISA MASTER DINERS DISCOVER AMEX">
    </form>
</body>
</html>