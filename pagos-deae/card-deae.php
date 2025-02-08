<?php
// Verificar que se proporcione el parámetro checkoutId
if (!isset($_GET['checkoutId'])) {
    echo "Error: No se proporcionó un checkoutId.";
    exit;
}

// Obtener el checkoutId desde el parámetro GET
$checkoutId = sanitize_text_field($_GET['checkoutId']);

// Configuración del entorno
$baseUrl = home_url('/'); // URL base del sitio
$url = "https://eu-test.oppwa.com/v1/"; // URL del entorno de pruebas

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar con Tarjeta</title>
    <!-- Incluir el script de pago con el checkoutId -->
    <script src="<?php echo $url; ?>paymentWidgets.js?checkoutId=<?php echo $checkoutId; ?>"></script>
</head>
<body>
    <h1>Formulario de Pago</h1>
    <p>Completa la información de tu tarjeta para proceder con el pago.</p>
    
    <!-- Formulario de pago -->
    <form action="<?php echo $baseUrl; ?>" class="paymentWidgets" data-brands="VISA MASTER DINERS DISCOVER AMEX">
    </form>
</body>
</html>