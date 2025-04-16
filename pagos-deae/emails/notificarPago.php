<?php
function notificarResultadoPago($data) {
    $cliente = $data['cliente'];
    $transaccion = $data['transaccion'];
    $estado = $data['estado'];

    $admin_email = 'cto@ulpik.com';

    $asuntoCliente = "✅ Confirmación de tu pago en ULPIK";
    $asuntoAdmin = "🧾 Nuevo pago procesado por Datafast";

    $mensajeCliente = "Hola {$cliente['nombre']},\n\nGracias por tu pago de \${$transaccion['monto']}. Tu transacción fue exitosa.\n\nCódigo: {$transaccion['codigo']}\nDescripción: {$transaccion['mensaje']}\n\nAtentamente,\nEl equipo ULPIK";

    $mensajeAdmin = "📥 Nuevo pago registrado:\n\nCliente: {$cliente['nombre']}\nEmail: {$cliente['email']}\nTeléfono: {$cliente['telefono']}\nMonto: \${$transaccion['monto']}\nCódigo: {$transaccion['codigo']}\nMensaje: {$transaccion['mensaje']}\nTransacción ID: {$transaccion['id']}\n";

    // Enviar al cliente
    wp_mail($cliente['email'], $asuntoCliente, $mensajeCliente);

    // Enviar al admin
    wp_mail($admin_email, $asuntoAdmin, $mensajeAdmin);
}