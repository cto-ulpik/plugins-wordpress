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
    $data = "?entityId=8ac7a4c994bb78290194bd40497301d5";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization:Bearer OGE4Mjk0MTg1YTY1YmY1ZTAxNWE2YzhjNzI4YzBkOTV8YmZxR3F3UTMyWA=='
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

// Mostrar toda la respuesta en pantalla
echo "<h2>Respuesta Completa de Datafast:</h2>";
echo "<pre>" . print_r($response, true) . "</pre>";

// Verificar si la respuesta es válida
if (!$response || !isset($response['result']['code'])) {
    echo "<h2>Error al obtener el estado de la transacción.</h2>";
    echo "<pre>" . htmlentities(print_r($response, true)) . "</pre>";
    exit;
}

// Mostrar el estado de la transacción en la página
$resultadoPago = $response['result']['code'];
$mensajePago = $response['result']['description'];

// Verificar si la transacción fue exitosa
if ($resultadoPago === "000.100.110" || $resultadoPago === "000.100.112") {
    
    global $wpdb;
    $table_transactions = $wpdb->prefix . "deae_transactions";
    $table_customers = $wpdb->prefix . "deae_customers";

    // Extraer datos del response
    $registrationId = $response['registrationId'] ?? null;
    $paymentBrand = $response['paymentBrand'] ?? null;
    $amount = $response['amount'] ?? null;
    
    $customer = $response['customer'] ?? [];
    $card = $response['card'] ?? [];
    $cart = $response['cart']['items'][0] ?? [];

    // Datos del cliente
    $customerName = trim($customer['givenName'] . ' ' . ($customer['middleName'] ?? '') . ' ' . $customer['surname']);
    $customerEmail = $customer['email'] ?? null;
    $customerPhone = $customer['phone'] ?? null;
    $customerDocType = $customer['identificationDocType'] ?? null;
    $customerDocId = $customer['identificationDocId'] ?? null;
    
    // Datos de suscripción
    $tipoSuscripcion = $cart['name'] ?? "Desconocido"; // Nombre de la suscripción
    $montoSuscripcion = $cart['price'] ?? $amount;
    $estadoSuscripcion = 1; // Activo por defecto
    $ultimoPago = current_time('mysql'); // Fecha del último pago exitoso

    // Comprobar si el cliente ya existe en la base de datos usando `customerDocId`
    $existing_customer = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM $table_customers WHERE document_id = %s",
        $customerDocId
    ));

    if ($existing_customer) {
        // Actualizar los datos de suscripción y último pago
        $wpdb->update(
            $table_customers,
            [
                'registration_id' => $registrationId,
                'tipo_suscripcion' => $tipoSuscripcion,
                'monto_suscripcion' => $montoSuscripcion,
                'estado_suscripcion' => $estadoSuscripcion,
                'ultimo_pago_suscripcion' => $ultimoPago
            ],
            ['id' => $existing_customer->id]
        );
    } else {
        // Insertar nuevo cliente
        $wpdb->insert(
            $table_customers,
            [
                'name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
                'document_type' => $customerDocType,
                'document_id' => $customerDocId,
                'registration_id' => $registrationId,
                'tipo_suscripcion' => $tipoSuscripcion,
                'monto_suscripcion' => $montoSuscripcion,
                'estado_suscripcion' => $estadoSuscripcion,
                'ultimo_pago_suscripcion' => $ultimoPago,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s', '%s']
        );
    }

    // Datos de la tarjeta
    $cardBin = $card['bin'] ?? null;
    $cardLast4 = $card['last4Digits'] ?? null;
    $cardExpiry = ($card['expiryMonth'] ?? '') . '/' . ($card['expiryYear'] ?? '');

    // Insertar datos en la tabla de transacciones
    $wpdb->insert(
        $table_transactions,
        [
            'transaction_id' => $transactionId,
            'registration_id' => $registrationId,
            'payment_brand' => $paymentBrand,
            'amount' => $amount,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'customer_doc_type' => $customerDocType,
            'customer_doc_id' => $customerDocId,
            'card_bin' => $cardBin,
            'card_last4' => $cardLast4,
            'card_expiry' => $cardExpiry,
            'cart_name' => $tipoSuscripcion,
            'cart_price' => $montoSuscripcion,
            'created_at' => current_time('mysql')
        ],
        ['%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s']
    );

    echo "<h3 style='color:green;'>✅ Cliente y pago registrados en la base de datos</h3>";
}
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

    <?php if ($resultadoPago === "000.100.110" || $resultadoPago === "000.100.112") { ?>
        <h2 style="color: green;">✅ Pago Exitoso</h2>
    <?php } else { ?>
        <h2 style="color: red;">❌ Pago Fallido</h2>
    <?php } ?>
</body>
</html>
