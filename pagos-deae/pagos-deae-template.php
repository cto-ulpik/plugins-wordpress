<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos DEAE</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    h1 {
        background-color: #0073aa;
        color: white;
        padding: 10px;
        margin: 0;
    }

    form {
        margin: 20px;
    }

    label {
        display: block;
        margin-top: 10px;
    }

    input {
        width: 100%;
        padding: 5px;
        margin-top: 5px;
    }

    button {
        padding: 10px;
        background-color: #0073aa;
        color: white;
        border: none;
        cursor: pointer;
    }

    button:hover {
        background-color: #005f86;
    }
</style>
</head>
<body>
    <h1>Realizar Pago</h1>
    <form method="POST">
        <label for="firstName">Primer Nombre</label>
        <input type="text" id="firstName" name="firstName" required>

        <label for="secondName">Segundo Nombre</label>
        <input type="text" id="secondName" name="secondName" required>

        <label for="lastName">Apellido</label>
        <input type="text" id="lastName" name="lastName" required>

        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>

        <label for="cedula">Cédula:</label>
        <input type="text" id="cedula" name="cedula" required>

        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" required>

        <label for="direccion_cliente">Dirección:</label>
        <input type="text" id="direccion_cliente" name="direccion_cliente" required>

        <br>
        <button type="submit">Pagar</button>
    </form>

    <?php

    // Verificar si el parámetro 'months_subscription' está presente en la URL
    if (!isset($_GET['months_subscription'])) {
        echo "Error: No se proporcionó el plan de suscripción.";
        exit;
    }

    // Obtener el ID de la suscripción y asegurarse de que es un número entero
    $months_subscription = intval($_GET['months_subscription']);
    $precio = 0;
    $name_product = "";

    // Determinar el precio basado en el número de meses de suscripción
    switch ($months_subscription) {
        case 1:
            $precio = 29;
            $name_product = "Suscripción DEAE 1 Mes";
            break;
        case 3:
            $precio = 67;
            $name_product = "Suscripción DEAE 3 Meses";
            break;
        case 6:
            $precio = 126;
            $name_product = "Suscripción DEAE 6 Meses";
            break;
        default:
            echo "Error: Plan de suscripción no válido.";
            exit;
    }

    // Mostrar el precio final
    echo "El precio es: $" . $precio;

    function generarIdentificador($longitud = 16) {
        $longitud = rand(1, 16); // Define una longitud aleatoria entre 1 y 16
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $identificador = '';
        
        for ($i = 0; $i < $longitud; $i++) {
            $identificador .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        
        return $identificador;
    }
    
    // Ejemplo de uso
    echo generarIdentificador();

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $precio > 0) {
        // Reemplazar sanitize_text_field() con htmlspecialchars() porque WordPress no está cargado aquí
        function limpiar_input($data) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }

        $firstName = limpiar_input($_POST['firstName']);
        $secondName = limpiar_input($_POST['secondName']);
        $lastName = limpiar_input($_POST['lastName']);
        $email = limpiar_input($_POST['email']);
        $cedula = limpiar_input($_POST['cedula']);
        $telefono = limpiar_input($_POST['telefono']);
        $direccion_cliente = limpiar_input($_POST['direccion_cliente']);

        function request($firstName, $secondName, $lastName, $email, $cedula, $telefono, $direccion_cliente) {
            $amount = $precio;

            // Calcular impuestos correctamente (IVA 15%)
            $baseImponible = round($amount / 1.15, 2);
            $iva = round($amount - $baseImponible, 2);
            $base0 = ($iva == 0) ? $amount : 0.00;

            $url = "https://eu-test.oppwa.com/v1/checkouts";
            $data = "entityId=8ac7a4c994bb78290194bd40497301d5" .
                    "&amount=" . number_format($amount, 2, '.', '') .
                    "&currency=USD" .
                    "&paymentType=DB" .

                    "&customer.givenName=" . $firstName .
                    "&customer.middleName=" . $secondName .
                    "&customer.surname=" . $lastName .

                    "&customer.ip=" . $_SERVER['REMOTE_ADDR'] .
                    "&customer.merchantCustomerId=" . generarIdentificador() .
                    "&merchantTransactionId=transaction" . generarIdentificador() .

                    "&customer.email=" . $email .
                    "&customer.identificationDocType=IDCARD" .
                    "&customer.identificationDocId=" . $cedula .

                    "&customer.phone=" . $telefono .
                    
                    "&billing.street1=" . $direccion_cliente .
                    "&billing.country=EC" .

                    "&shipping.street1=" . $direccion_cliente .
                    "&shipping.country=EC" .

                    "&customParameters[SHOPPER_ECI]=0103910" .
                    "&customParameters[SHOPPER_PSERV]=17913101" .

                    "&customParameters[SHOPPER_VAL_BASE0]=" . number_format($base0, 2, '.', '') .
                    "&customParameters[SHOPPER_VAL_BASEIMP]=" . number_format($baseImponible, 2, '.', '') .
                    "&customParameters[SHOPPER_VAL_IVA]=" . number_format($iva, 2, '.', '') .
                    "&customParameters[SHOPPER_MID]=1000000505" .
                    "&customParameters[SHOPPER_TID]=PD100406" .

                    "&risk.parameters[USER_DATA2]=DATAFAST" .
                    "&customParameters[SHOPPER_VERSIONDF]=2" .
                    "&testMode=EXTERNAL" .

                    "&cart.items[0].name=" . $name_product .
                    "&cart.items[0].description=" . $name_product .
                    "&cart.items[0].price=" . $amount .
                    "&cart.items[0].quantity=1";

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
                return curl_error($ch);
            }
            curl_close($ch);
            return $responseData;
        }

        $response = request($firstName, $secondName, $lastName, $email, $cedula, $telefono, $direccion_cliente);
        $responseArray = json_decode($response, true);

        $checkoutId = $responseArray['id'] ?? null;

        if ($checkoutId) {
            echo "<h2>Checkout ID generado:</h2>";
            echo "<p id='checkoutIdDisplay'>" . htmlspecialchars($checkoutId, ENT_QUOTES, 'UTF-8') . "</p>";
            $redirectUrl = home_url('/card-deae?checkoutId=' . $checkoutId);
            echo "<h2>Redirigiendo al formulario de pago...</h2>";
            echo "<script>window.location.href = '$redirectUrl';</script>";
        } else {
            echo "<h2>Error en la transacción:</h2>";
            echo "<pre>" . htmlentities($response) . "</pre>";
        }
    }

    else{
        echo "<h2>Error en la transacción:</h2>";
    }
    ?>

</body>

<script type="text/javascript" src="https://www.datafast.com.ec/js/dfAdditionalValidations1.js"></script>
</html>