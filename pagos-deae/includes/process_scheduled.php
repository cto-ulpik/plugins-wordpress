<?php

function deae_process_scheduled_payment($customer_id) {
    global $wpdb;
    $table_customers = $wpdb->prefix . "deae_customers";
    $table_transactions = $wpdb->prefix . "deae_transactions";

    $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_customers WHERE id = %d", $customer_id));

    if (!$customer || empty($customer->registration_id)) {
        error_log("Error: Cliente no encontrado o sin Registration ID.");
        return;
    }

    // Generar ID de transacción único
    $trx = uniqid("trx_");

    // Datos de la solicitud de pago
    $url = "https://test.oppwa.com/v1/registrations/" . $customer->registration_id . "/payments";
    $data = "entityId=8ac7a4c994bb78290194bd40497301d5" .
        "&amount=" . number_format($customer->monto_suscripcion, 2, '.', '') .
        "&currency=USD" .
        "&paymentType=DB" .
        "&recurringType=REPEATED" .
        "&merchantTransactionId=" . $trx .
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
    
    if (curl_errno($ch)) {
        error_log("Error en CURL: " . curl_error($ch));
        return;
    }
    
    curl_close($ch);
    $response = json_decode($responseData, true);

    if ($response['result']['code'] === "000.100.110" || $response['result']['code'] === "000.100.112") {
        $wpdb->update($table_customers, ['ultimo_pago_suscripcion' => current_time('mysql')], ['id' => $customer_id]);

        // Insertar en la tabla de transacciones
        $wpdb->insert($table_transactions, [
            'transaction_id' => $trx,
            'registration_id' => $customer->registration_id,
            'payment_brand' => 'RECURRING',
            'amount' => $customer->monto_suscripcion,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'created_at' => current_time('mysql')
        ], ['%s', '%s', '%s', '%f', '%s', '%s', '%s']);

        error_log("✅ Pago recurrente exitoso para cliente ID: " . $customer_id);
    } else {
        error_log("❌ Error en el pago automático: " . $response['result']['description']);
    }
}
add_action('deae_scheduled_payment', 'deae_process_scheduled_payment', 10, 1);