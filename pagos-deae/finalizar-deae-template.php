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
    $url = "https://eu-prod.oppwa.com/v1/checkouts/{$transactionId}/payment";
    $data = "?entityId=8acda4cc95f5c7b70196112c671c0531";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization:Bearer OGFjOWE0Y2E4YWIxZjZlMzAxOGFjY2E2MTgzYzcwOTZ8NzlUTkpkd0ZqZA=='
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Cambiar a true en producción
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
// echo "<h2>Respuesta Completa de Datafast:</h2>";
// echo "<p>" . print_r($response, true) . "</p>";

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
if ($resultadoPago === "000.100.110" || $resultadoPago === "000.100.112" || $resultadoPago === "000.000.000") {
    
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


    
    echo "
            <h3 style='color:green;'>✅ Cliente y pago registrados en la base de datos</h3>
            <p>En las próximas 24 horas laborales te daremos acceso al material 🤗</p>
            <p>Si tienes preguntas puedes escribirnos al Whatsapp con el número <a href='https://wa.me/593984338645'>+593984338645</a>, o atraves del correo legal2@ulpik.com.
</p>    
        ";



    // Datos necesarios
    $admin_email = get_option('admin_email'); // Correo del admin configurado en WordPress
    $contadora_email = "cpa@ulpik.com";
    $directora_comunidad_email = "legal2@ulpik.com";
    $cliente_email = $customerEmail ?? null;
    $monto = $montoSuscripcion ?? '0.00';
    $moneda = 'USD';
    $estado = 'Aprobado';
    $mensaje = "Puedes verificar la transaccion en el sistema de Administración:";
    $transaccion = $transactionId;

    // -------- 1. Correo al Cliente --------
    if ($cliente_email && filter_var($cliente_email, FILTER_VALIDATE_EMAIL)) {
        $asunto_cliente = "📄 Ulpik - Confirmación de tu pago en la suscripción";
        $mensaje_cliente = "
        
        Hola,

        Gracias por tu pago, te damos la bienvenida a la comunidad de Ulpriv. 

        Si tienes preguntas puedes escribirnos al Whatsapp con el número +593 98 433 8645, o atraves del correo legal2@ulpik.com.

        Saludos,
        El equipo de Ulpik
        ";

        wp_mail($cliente_email, $asunto_cliente, $mensaje_cliente);
    }

    // -------- 2. Correo al Administrador --------
    
    $asunto_admin = "💳 Nueva transacción procesada: $transaccion";
    $mensaje_admin = "
    Se ha procesado una nueva transacción.

    Detalles:

    - Transacción: $transaccion
    - Monto: $monto $moneda
    - Estado: $estado
    - Mensaje: $mensaje

    Datos del cliente:
    - Nombre: $customerName
    - Email del cliente: $cliente_email
    - Número de teléfono: $customerPhone

    Att.
    Ulpik
    ";

    wp_mail($admin_email, $asunto_admin, $mensaje_admin);
    wp_mail($contadora_email, $asunto_admin, $mensaje_admin);
    wp_mail($directora_comunidad_email, $asunto_admin, $mensaje_admin);


}

else{
    // Si el pago no fue exitoso, mostrar mensaje de error
    echo "<h2 style='color:red;'>❌ Pago Fallido</h2>";
    echo "<p>Estado de la transacción: $resultadoPago</p>";
    echo "<p>Descripción: $mensajePago</p>";
    echo "<p>Por favor, verifica los detalles de tu pago y vuelve a intentarlo.</p>";
// Asegurarse de que estas variables existen
$monto = $montoSuscripcion ?? '0.00';
$moneda = 'USD';
$estado = 'Rechazado';
$mensaje = 'Error en el procesamiento del pago';
$customerName = $customerName ?? 'No disponible';
$customerPhone = $customerPhone ?? 'No disponible';

// Correos
$admin_email = get_option('admin_email');
$contadora_email = "cpa@ulpik.com";
$directora_comunidad_email = "legal2@ulpik.com";
$cliente_email = $customerEmail ?? null;
$transaccion = $transactionId;

// Activar contenido HTML
add_filter('wp_mail_content_type', function() {
    return 'text/html';
});

// Correo al cliente
if ($cliente_email && filter_var($cliente_email, FILTER_VALIDATE_EMAIL)) {
    $asunto_cliente = "❌ Ulpik - Error en tu pago en la suscripción";
    $mensaje_cliente = "
        <p>Hola,</p>
        <p>No hemos logrado procesar tu pago.</p>
        <p>Por favor contáctate por WhatsApp al número 
        <a href='https://wa.me/593984338645'>+593984338645</a>, o al correo 
        <a href='mailto:legal2@ulpik.com'>legal2@ulpik.com</a>.</p>
        <p>Saludos,<br>El equipo de Ulpik</p>
    ";
    wp_mail($cliente_email, $asunto_cliente, $mensaje_cliente);
}

// Correo a administración
$asunto_admin = "❌ Ulpik - Error en la transacción: $transaccion";
$mensaje_admin = "
    <p>Hubo un error con el pago del cliente.</p>
    <p><strong>Detalles:</strong></p>
    <ul>
        <li>Transacción: $transaccion</li>
        <li>Monto: $monto $moneda</li>
        <li>Estado: $estado</li>
        <li>Mensaje: $mensaje</li>
    </ul>
    <p><strong>Datos del cliente:</strong></p>
    <ul>
        <li>Nombre: $customerName</li>
        <li>Email: $cliente_email</li>
        <li>Teléfono: $customerPhone</li>
    </ul>
    <p>Att.<br>Ulpik</p>
";

wp_mail($admin_email, $asunto_admin, $mensaje_admin);
wp_mail($contadora_email, $asunto_admin, $mensaje_admin);
wp_mail($directora_comunidad_email, $asunto_admin, $mensaje_admin);

// Restablecer formato de correo a texto plano
remove_filter('wp_mail_content_type', 'set_html_content_type');


}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de la Transacción</title>


    <style>
       body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            height: 100vh;
            margin: 0;
            background-color: #470078;
        }
        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr;
    
            gap: 20px;
            width: 80%;
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            max-width:300px;
            grid-column: span 2;
            text-align: center;
        }
        .logo img {
            max-width: 100%;
            height: auto;
        }
        .formulario {
            display: flex;
            flex-direction: column;
          justify-content:center;
          text-align:center;
        }
        .formulario label {
            display:block;
            margin-top: 10px;
            font-weight: bold;
        }
        .formulario input {
          width:80%;
            padding: 8px;
            margin: 0 auto;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .formulario button {
          display:block;
          width:100%;
            margin-top: 15px;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .formulario button:hover {
            background-color: #218838;
        }
        .beneficios {
          display:flex;
          flex-direction:column;
          justify-content:center;
        
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
        }
        .beneficios h2 {
            margin-top: 0;
            text-align: center;
        }
        .beneficios ul {
            padding-left: 20px;
        }

.descuento {
            grid-column: 2;
            grid-row: 2;
            text-align: center;
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            color: white;
            font-size: 1.5em;
            font-weight: bold;
            padding: 20px;
            border-radius: 10px;
  margin: 20px auto;
            box-shadow: 0 0 15px rgba(255, 75, 43, 0.5);
/*             animation: pulse 1.5s infinite; */
        }

.cards{
  margin: 10px auto;
  width:50%
}

.logo{
  margin: 10px auto;
  width:50%
}
        


.modal {
            background: #EAEAEA;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            width: 300px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            margin: 0 auto;
        }
        .modal h2 {
            font-size: 18px;
            color: #000;
        }
        .modal .precio {
            font-size: 36px;
            font-weight: bold;
            color: #7D2AE8;
            margin: 10px 0;
        }
        .modal .ahorro {
            font-size: 14px;
            color: #555;
        }
        .modal button {
            background: #7D2AE8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin: 15px 0;
        }
        .modal button:hover {
            background: #5A1EA8;
        }
        .seguridad {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-size: 12px;
            color: #555;
            margin: 10px 0;
        }
        .pago-recurrente {
            font-size: 12px;
            color: #555;
        }




@media only screen and (max-width: 600px) {
  .container{
    display:flex;
    flex-direction:column;
  }
}
    </style>

    
</head>
   
<body>
        <?php 
        $resultadoPago = $resultadoPago ?? null; // prevenir error de variable no definida
        if ($resultadoPago === "000.100.110" || $resultadoPago === "000.100.112" || $resultadoPago === "000.000.000") { 
        ?>
        <h2 style="color: green;">✅ Pago Exitoso, Revisa tu correo electrónico, en las próximas 24 horas laborales te daremos acceso a todos los beneficios de la suscripción.</h2>
    <?php } else { ?>
        <h2 style="color: red;">❌ Pago Fallido</h2>
    <?php } ?>
</body>
</html>
