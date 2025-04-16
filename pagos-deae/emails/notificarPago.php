<?php
function notificarResultadoPago($data) {
    $cliente = $data['cliente'];
    $transaccion = $data['transaccion'];
    $estado = $data['estado'];

    $admin_email = 'admin@ulpik.com';

    if (empty($cliente['email'])) {
        throw new Exception('El correo del cliente está vacío.');
    }

    $asuntoCliente = ($estado === 'exitoso')
        ? "✅ Confirmación de tu pago en ULPIK"
        : "❌ Problema con tu pago en ULPIK";

    $asuntoAdmin = "🧾 Resultado de pago procesado por Datafast";

    $mensajeCliente = "Hola {$cliente['nombre']},\n\n"
        . ($estado === 'exitoso'
            ? "Gracias por tu pago de \${$transaccion['monto']}."
            : "Tu intento de pago no se completó correctamente.")
        . "\n\nCódigo: {$transaccion['codigo']}\nDescripción: {$transaccion['mensaje']}\n\nAtentamente,\nEl equipo ULPIK";

    $mensajeAdmin = "📥 Resultado de pago:\n\nCliente: {$cliente['nombre']}\nEmail: {$cliente['email']}\nTeléfono: {$cliente['telefono']}\nMonto: \${$transaccion['monto']}\nCódigo: {$transaccion['codigo']}\nMensaje: {$transaccion['mensaje']}\nTransacción ID: {$transaccion['id']}\n";

    if (!wp_mail($cliente['email'], $asuntoCliente, $mensajeCliente)) {
        throw new Exception("No se pudo enviar el correo al cliente: {$cliente['email']}");
    }

    if (!wp_mail($admin_email, $asuntoAdmin, $mensajeAdmin)) {
        throw new Exception("No se pudo enviar el correo al administrador.");
    }
}