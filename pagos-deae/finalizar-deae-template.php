<?php
// Verificar si el parámetro ID está presente
if (!isset($_GET['id'])) {
    echo "Error: No se proporcionó un ID de transacción.";
    exit;
}

// Obtener el ID de la transacción desde la URL
$transactionId = sanitize_text_field($_GET['id']);

// Función para consultar el estado de la transacción en Datafast
function obtener_estado_transaccion($transactionId) {
    $url = "https://eu-test.oppwa.com/v1/checkouts/{$transactionId}/payment";
    $data = "?entityId=8a8294174b7ecb28014b9699220015ca";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization:Bearer OGE4Mjk4MjV...' // Sustituir con tu token real
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Cambiar a true en producción
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $responseData = curl_exec($ch);
    if (curl_errno($ch)) {
        return curl_error($ch);
    }
    curl_close($ch);

    return json_decode($responseData, true);
}

// Obtener la respuesta de la API
$response = obtener_estado_transaccion($transactionId);

// Verificar si la respuesta es válida
if (!$response || !isset($response['result']['code'])) {
    echo "<h2>Error al obtener el estado de la transacción.</h2>";
    echo "<pre>" . htmlentities(print_r($response, true)) . "</pre>";
    exit;
}

// Mostrar el estado de la transacción en la página
$resultadoPago = $response['result']['code'];
$mensajePago = $response['result']['description'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de la Transacción</title>
</head>
<body>
    <h1>Estado de la Transacción</h1>
    <p><strong>ID de Transacción:</strong> <?php echo htmlspecialchars($transactionId); ?></p>
    <p><strong>Resultado:</strong> <?php echo htmlspecialchars($resultadoPago); ?></p>
    <p><strong>Mensaje:</strong> <?php echo htmlspecialchars($mensajePago); ?></p>

    <?php if ($resultadoPago === "000.100.110") { ?>
        <h2 style="color: green;">✅ Pago Exitoso</h2>
    <?php } else { ?>
        <h2 style="color: red;">❌ Pago Fallido</h2>
    <?php } ?>

    <a href="<?php echo home_url('/'); ?>">Volver a la página principal</a>
</body>
</html>
