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
        <label for="amount">Monto:</label>
        <input type="text" id="amount" name="amount" required>
        <br>
        <button type="submit">Pagar</button>
    </form>
    

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $amount = sanitize_text_field($_POST['amount']);

        function request($amount) {
            $url = "https://eu-test.oppwa.com/v1/checkouts";
            $data = "entityId=8ac7a4c994bb78290194bd40497301d5" .
                    "&amount=" . $amount .
                    "&currency=USD" .
                    "&paymentType=DB" .
                    "&customer.givenName=Nestor" .
                    "&customer.middleName=David" .
                    "&customer.surname=Castillo" .
                    "&testMode=EXTERNAL";

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