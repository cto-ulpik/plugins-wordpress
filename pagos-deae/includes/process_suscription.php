<?php

/// FUNCION PARA REALIZAR EL PAGO
function process_subscription_payment() {
    global $wpdb;
    $table_customers = $wpdb->prefix . "deae_customers";
    $table_transactions = $wpdb->prefix . "deae_transactions";

    if (!isset($_GET['id'])) {
        wp_redirect(admin_url('admin.php?page=deae_customers'));
        exit;
    }

    $customer_id = intval($_GET['id']);
    $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_customers WHERE id = %d", $customer_id));

    if (!$customer || empty($customer->registration_id)) {
        echo "Error: Cliente no encontrado o no tiene un Registration ID.";
        exit;
    }

    // Generar ID de transacción único
    $trx = uniqid("trx_");

    // Definir valores base para la transacción
    $baseImponible = round($customer->monto_suscripcion / 1.12, 2);
    $iva = round($customer->monto_suscripcion - $baseImponible, 2);
    $base0 = ($iva == 0) ? $customer->monto_suscripcion : 0.00;
    

    // Datos de la solicitud de pago
    $url = "https://test.oppwa.com/v1/registrations/" . $customer->registration_id . "/payments";
    $data = "entityId=8ac7a4c994bb78290194bd40497301d5" .
        "&amount=" . number_format($customer->monto_suscripcion, 2, '.', '') .
        "&currency=USD" .
        "&paymentType=DB" .
        "&recurringType=REPEATED" .
        "&merchantTransactionId=" . $trx .
        "&customParameters[SHOPPER_MID]=1000000505" .
        "&customParameters[SHOPPER_TID]=PD100406" .
        "&customParameters[SHOPPER_ECI]=0103910" .
        "&customParameters[SHOPPER_PSERV]=17913101 " .
        "&customParameters[SHOPPER_VAL_BASE0]=" . number_format($base0, 2, '.', '') .
        "&customParameters[SHOPPER_VAL_BASEIMP]=" . number_format($baseImponible, 2, '.', '') .
        "&customParameters[SHOPPER_VAL_IVA]=" . number_format($iva, 2, '.', '') .
        "&customParameters[SHOPPER_VERSIONDF]=2" .
        "&testMode=EXTERNAL";

    // Realizar la solicitud con CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization:Bearer OGE4Mjk0MTg1YTY1YmY1ZTAxNWE2YzhjNzI4YzBkOTV8YmZxR3F3UTMyWA=='
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $responseData = curl_exec($ch);
    
    curl_close($ch);

    $response = json_decode($responseData, true);

    // Si el pago fue exitoso, actualizar la última fecha de pago y guardar la transacción
    if ($response['result']['code'] === "000.100.110" || $response['result']['code'] === "000.100.112") {
        $wpdb->update(
            $table_customers,
            ['ultimo_pago_suscripcion' => current_time('mysql')],
            ['id' => $customer_id]
        );

        // Insertar en la tabla de transacciones
        $wpdb->insert(
            $table_transactions,
            [
                'transaction_id' => $trx,
                'registration_id' => $customer->registration_id,
                'payment_brand' => 'RECURRING',
                'amount' => $customer->monto_suscripcion,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'customer_doc_type' => $customer->document_type,
                'customer_doc_id' => $customer->document_id,
                'cart_name' => $customer->tipo_suscripcion,
                'cart_price' => $customer->monto_suscripcion,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s']
        );

        echo "✅ Pago recurrente exitoso.";
    } else {
        echo "❌ Error en el pago: " . $response['result']['description'];
    }

    wp_redirect(admin_url('admin.php?page=deae_customers'));
    exit;
}

add_action('admin_post_process_subscription_payment', 'process_subscription_payment');

