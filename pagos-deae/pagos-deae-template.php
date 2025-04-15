<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos DEAE</title>
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
    
            width: 80%;
            max-width: 900px;
            background: white;
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
          padding:20px;
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
          align-items:center;
        
            padding: 10px;
            background-color: #470078;
            border-radius: 0px 10px 10px 0;
          color:white;
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
        color:white;
                    background: 470078;
                    padding: 20px;
                    text-align: center;
                    margin: 10px auto;
                }
                .modal h2 {
                    font-size: 22px;
                }
                .modal .precio {
                    font-size: 64px;
                    font-weight: bold;
                    color: #7D2AE8;
                    margin: 10px 0;
                }
                .modal .ahorro {
                    font-size: 14px;
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
                    margin: 10px 0;
                }
                .pago-recurrente {
                    font-size: 12px;
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
    
    // Verificar si el parámetro 'months_subscription' está presente en la URL
    if (!isset($_GET['months_subscription'])) {
        echo "Error: No se proporcionó el plan de suscripción.";
        exit;
    }

    // Obtener el ID de la suscripción y asegurarse de que es un número entero
    $months_subscription = intval($_GET['months_subscription']);
    $precio = 29;
    $name_product = "";
    $days_product = 0;
    $ahorro = 0;

    // Determinar el precio basado en el número de meses de suscripción
    switch ($months_subscription) {
        case 1:
            $precio = 29;
            $name_product = "Suscripción DEAE 1 Mes";
            $days_product = 30;
            break;
        case 3:
            $precio = 67;
            $name_product = "Suscripción DEAE 3 Meses";
            $days_product = 90;
            $ahorro = 20;
            break;
        case 6:
            $precio = 126;
            $name_product = "Suscripción DEAE 6 Meses";
            $days_product = 180;
            $ahorro = 48;
            break;
        default:
            echo "Error: Plan de suscripción no válido.";
            exit;
    }

    // Mostrar el precio final
    // echo "El precio es: $" . $precio;

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
    // echo generarIdentificador();

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $precio > 0) {
        require_once plugin_dir_path(__FILE__) . 'env/env.php';
        echo $url_datafast;
        // Reemplazar sanitize_text_field() con htmlspecialchars() porque WordPress no está cargado aquí
        function limpiar_input($data) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }

        $firstName = limpiar_input($_POST['firstName']);
        $secondName = "";
        $lastName = limpiar_input($_POST['lastName']);
        $email = limpiar_input($_POST['email']);
        $cedula = limpiar_input($_POST['cedula']);
        $telefono = limpiar_input($_POST['telefono']);
        $direccion_cliente = limpiar_input($_POST['direccion_cliente']);



        function request($firstName, $secondName, $lastName, $email, $cedula, $telefono, $direccion_cliente, $precio) {
            $amount = $precio;

            // Calcular impuestos correctamente (IVA 15%)
            $baseImponible = round($amount / 1.15, 2);
            $iva = round($amount - $baseImponible, 2);
            $base0 = ($iva == 0) ? $amount : 0.00;

            $url = $url_datafast . "/v1/checkouts";
            $data = "entityId=" . $id_entidad_datafast .
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
                    "&customParameters[SHOPPER_MID]=" . $mid_datafast .
                    "&customParameters[SHOPPER_TID]=" . $tid_datafast .

                    "&risk.parameters[USER_DATA2]=DATAFAST" .
                    "&customParameters[SHOPPER_VERSIONDF]=2" .
                    // "&testMode=EXTERNAL" .

                    "&cart.items[0].name=" . $name_product .
                    "&cart.items[0].description=" . $name_product .
                    "&cart.items[0].price=" . $amount .
                    "&cart.items[0].quantity=1";

            echo "<p>" . $data . "</p>";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization:Bearer ' . $access_token_datafast
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

        $response = request($firstName, $secondName, $lastName, $email, $cedula, $telefono, $direccion_cliente, $precio);
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

    ?>


<div class="logo">
    <img src="https://ulpik.com/wp-content/uploads/2025/03/Diseño-sin-título-4.png" alt="De Emprendedor a Empresario">
</div>

<div class="container">
        
        <div class="formulario">
            <h2>Datos para tu suscripción</h2>
            <form method="POST">
                <label for="firstName">Primer Nombre</label>
                <input type="text" id="firstName" name="firstName" required>
                
                <!-- <label for="secondName">Segundo Nombre</label>
                <input type="text" id="secondName" name="secondName" required> -->
                
                <label for="lastName">Apellido</label>
                <input type="text" id="lastName" name="lastName" required>
                
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>
                
                <label for="cedula">Cédula / RUC:</label>
                <input type="text" id="cedula" name="cedula" required>
                
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" required>
                
                <label for="direccion_cliente">Dirección:</label>
                <input type="text" id="direccion_cliente" name="direccion_cliente" required>
                
                <button type="submit">Pagar</button>
                <img class="cards" src="https://ulpik.com/wp-content/uploads/2024/08/meetodos_de_pago_ulpik.png" alt="Pagos por Visa y Mastercard">
            </form>
        </div>
        <div class="beneficios">
            

            <div class="modal">
                
                <?php 
                    if($months_subscription==1){
                        echo "<h2>PLAN " . $months_subscription . "MES</h2>";
                    }
                    elseif($months_subscription==3 || $months_subscription==6){
                        echo "<h2>PLAN <?php echo $months_subscription ?> MESES</h2>";
                    }
                ?>

                <div class="precio">$<?php echo $precio; ?></div>
                
                <?php 
                    if ($ahorro > 0) {
                        echo "<div class='ahorro'>(Ahorra $$ahorro)</div>";
                    }
                ?>

                <div class="seguridad">
                    <span>&#10004;</span>
                    <span>COMPRA 100% SEGURA</span>
                </div>
                <div class="pago-recurrente">Pago recurrente cada <?php echo $days_product; ?> días</div>
            </div>



            <h2>Beneficios de la Suscripción</h2>
            <ul>
                <li>3 clases en vivo con el equipo de ULPIK</li>
                <li>Acceso a un grupo privado de WhatsAp</li>
                <li>2 eBooks cada mes sobre visión de triple impacto</li>
                <li>1 workbook mensual de marketing digital</li>
                <li>Replays ilimitados de cada sesión en vivo</li>
                <li>Descuentos exclusivos en programas ULPIK y acceso preferencial a eventos VIP.</li>
            </ul>
        </div>
    </div>


</body>

<script type="text/javascript" src="https://www.datafast.com.ec/js/dfAdditionalValidations1.js"></script>
</html>