<?php

function sendEmailSuccess(
        $customerEmail, 
        $customerName, 
        $customerPhone, 
        $montoSuscripcion, 
        $transactionId
    ) {

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

    // -------- 2. Correo al Administrador, Contador y Directorx de la comunidad --------
    
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
    // wp_mail($contadora_email, $asunto_admin, $mensaje_admin);
    // wp_mail($directora_comunidad_email, $asunto_admin, $mensaje_admin);
}


function sendEmailFailed(
        $customerEmail, 
        $customerName, 
        $customerPhone, 
        $montoSuscripcion, 
        $transactionId
){



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
// wp_mail($contadora_email, $asunto_admin, $mensaje_admin);
// wp_mail($directora_comunidad_email, $asunto_admin, $mensaje_admin);


}
