<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos DEAE</title>
</head>
<body>
    <h1>Realizar Pago</h1>
    <form method="POST">

        <label for="firstName">Primer Nombre</label>
        <input type="text" id="firsName" name="firstName" required>">
        
        <label for="secondName">Segundo Nombre</label>
        <input type="text" id="secondName" name="secondName" required>">

        <label for="lastName">Apellido</label>
        <input type="text" id="lastName" name="lastName" required>">

        
        --
        
        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>
        --
        
        <label for="cedula">Cédula:</label>
        <input type="text" id="cedula" name="cedula" required>">

        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" required>">

        <label for="direccion_cliente">Dirección:</label>
        <input type="text" id="direccion_cliente" name="direccion_cliente" required>">


        <br>
        <button type="submit">Pagar</button>
    </form>
    

    <?php

    
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = sanitize_text_field($_POST['firstName']);
        $secondName = sanitize_text_field($_POST['secondName']);
        $lastName = sanitize_text_field($_POST['lastName']);
        $email = sanitize_text_field($_POST['email']);

        $cedula = sanitize_text_field($_POST['cedula']);
        $telefono = sanitize_text_field($_POST['telefono']);

        $direccion_cliente = sanitize_text_field($_POST['direccion_cliente']);


        function request($firstName, $secondName, $lastName, $email, $cedula, $telefono, $direccion_cliente) {
            $amount = 29;
            
            
            // Calcular los impuestos correctamente
            $baseImponible = round($amount / 1.15, 2); // Base sin IVA (asumiendo 12% de IVA)
            $iva = round($amount - $baseImponible, 2); // IVA calculado

            // Si el producto no tiene IVA, entonces todo el monto es base 0%
            $base0 = ($iva == 0) ? $amount : 0.00;


            $url = "https://eu-test.oppwa.com/v1/checkouts";
            $data = "entityId=8ac7a4c994bb78290194bd40497301d5" .
                    "&amount =" . $amount .
                    "&currency=USD" .
                    "&paymentType=DB" .

                    "&customer.givenName=" . $firstName .
                    "&customer.middleName=" . $secondName .
                    "&customer.surname=" . $lastName .

                    "&customer.givenName=Nestor" .
                    "&customer.middleName=David" .
                    "&customer.surname=Castillo" .
                    
                    "&customer.ip=" . $_SERVER['REMOTE_ADDR'] .

                    "&customer.email=" . $email .
                    "&customer.identificationDocType=IDCARD".
                    "&customer.identificationDocId=".$cedula.

                    "&customer.phone=".$telefono.
                    
                    "&billing.street=".$direcion_cliente.
                    "&billing.country=EC".

                    "&shipping.street=".$direccion_cliente.
                    "&shipping.country=EC".
                    

                    "&customParameters[SHOPPER_ECI]=0103910" .
                    "&customParameters[SHOPPER_PSERV]=17913101" .

                    "&customParameters[SHOPPER_VAL_BASE0]=0.00" .
                    "&customParameters[SHOPPER_VAL_BASEIMP]=" . number_format($baseImponible, 2, '.', '') .
                    "&customParameters[SHOPPER_VAL_IVA]=" . number_format($iva, 2, '.', '') .
                    "&customParameters[SHOPPER_MID]=1000000505" .
                    "&customParameters[SHOPPER_TID]=PD100406" .

                    "&risk.parameters[USER_DATA2]=DATAFAST" .
                    "&customParameters[SHOPPER_VERSIONDF]=2" .
                    "&testMode=EXTERNAL".
                    
                    
                    "&cart.items[0].name=DEAESUSCRIPCION".
                    "&cart.items[0].description=suscripcion".
                    "&cart.items[0].price=29".
                    "&cart.items[0].quantity=1".
                    ;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization:Bearer OGE4Mjk0MTg1YTY1YmY1ZTAxNWE2YzhjNzI4YzBkOTV8YmZxR3F3UTMyWA=='
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Cambiar a true en producción
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $responseData = curl_exec($ch);
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            curl_close($ch);
            return $responseData;
        }

        $response = request($amount);
        $responseArray = json_decode($response, true);

        // Extraer el checkoutId de la respuesta si existe
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
    ?>

</body>
<script type="text/javascript" src="https://www.datafast.com.ec/js/dfAdditionalValidations1.js"> 
</html> 