<?php
function notificarResultadoPago($data) {
    $cliente = $data['cliente'];
    $transaccion = $data['transaccion'];
    $estado = $data['estado'];

    $admin_email = 'cto@ulpik.com';

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
        . "\n\nDetalles de la transacción:\n"
        . "Monto: \${$transaccion['monto']}\n"
        . "Tipo de suscripcion: {$transaccion['producto']}\n"
        . "Contactanos por whatsapp <a href='https://wa.me/593984338645'>+593984338645</a>\n";

    $mensajeAdmin = "Hola Administrador,\n\n"
        . "Se ha procesado un pago con los siguientes detalles:\n"
        . "<h2> Datos del cliente</h2>" 
        . "Cliente: {$cliente['nombre']}\n"
        . "Email: {$cliente['email']}\n"
        . "Teléfono: {$cliente['telefono']}\n"
        . "Cédula: {$cliente['documento_id']}\n"
        . "Direccion: {$cliente['direccion']}\n"

        . "<h2> Datos de la transaccion</h2>"
        . "Id: {$transaccion['id']}\n"
        . "Monto: \${$transaccion['monto']}\n"
        . "Nombre del producto: {$transaccion['producto']}\n"
        . "Fecha: {$transaccion['fecha']}\n"

        . "Estado: {$estado}\n"
        . "Gracias por tu atención.\n\n";

    if (!wp_mail($cliente['email'], $asuntoCliente, $mensajeCliente)) {
        throw new Exception("No se pudo enviar el correo al cliente: {$cliente['email']}");
    }

    if (!wp_mail($admin_email, $asuntoAdmin, $mensajeAdmin)) {
        throw new Exception("No se pudo enviar el correo al administrador.");
    }
}