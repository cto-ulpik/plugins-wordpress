<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <meta http-equiv="Content-Security-Policy" content="script-src 'self' https://www.datafast.com.ec https://test.datfast.com.ec 'unsafe-inline';"> -->
    <title>Pagar con Tarjeta</title>


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
            /* color: white; */
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr;
    
            gap: 20px;
            width: 80%;
            max-width: 900px;
            background: white;
            padding: 20px;
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
        
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
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
        /*             animation: pulse 1.5s infinite; */
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
                    background: #EAEAEA;
                    padding: 20px;
                    border-radius: 12px;
                    text-align: center;
                    width: 300px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
                    margin: 0 auto;
                }
                .modal h2 {
                    font-size: 18px;
                    color: #000;
                }
                .modal .precio {
                    font-size: 36px;
                    font-weight: bold;
                    color: #7D2AE8;
                    margin: 10px 0;
                }
                .modal .ahorro {
                    font-size: 14px;
                    color: #555;
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
                    color: #555;
                    margin: 10px 0;
                }
                .pago-recurrente {
                    font-size: 12px;
                    color: #555;
                }




        @media only screen and (max-width: 600px) {
        .container{
            display:flex;
            flex-direction:column;
        }
        }
</style>

<?php
require_once plugin_dir_path(__FILE__) . 'env/env.php';

$id_entidad_datafast = $id_entidad_datafast ?? null;
        $access_token_datafast = $access_token_datafast ?? null;
        $mid_datafast = $mid_datafast ?? null;
        $tid_datafast = $tid_datafast ?? null;
        $serv_datafast = $serv_datafast ?? null;
        $url_datafast = $url_datafast ?? null;
        // Verificar que las variables globales estén definidas
        if (is_null($id_entidad_datafast) || is_null($access_token_datafast) || is_null($mid_datafast) || is_null($tid_datafast) || is_null($serv_datafast) || is_null($url_datafast)) {
            echo "Error: Variables de configuración no definidas.";
            exit;
        }

// Verificar que se proporcione el parámetro checkoutId
if (!isset($_GET['checkoutId'])) {
    echo "Error: No se proporcionó un checkoutId.";
    exit;
}

// Obtener el checkoutId desde el parámetro GET
$checkoutId = sanitize_text_field($_GET['checkoutId']);

// Configuración del entorno
$baseUrl = home_url('/finalizar-deae'); // URL base del sitio
$url = $url_datafast . "/v1"; // URL del entorno
echo $url;

$inputJSON = file_get_contents('php://input');
$decodedData = json_decode($inputJSON, true);

?>

    
    <!-- Incluir el script de pago con el checkoutId -->
    <script src="<?php echo $url; ?>/paymentWidgets.js?checkoutId=<?php echo $checkoutId; ?>"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    
    
    <script type="text/javascript">
    var wpwlOptions = {
        onReady: function(onReady) {
            var createRegistrationHtml = 
            
            '<div class="customLabel">Desea guardar de manera segura sus datos?</div>'+
            '<div class="customInput"><input type="checkbox" name="createRegistration" /></div><br/><br/><img src='+
            '"https://www.datafast.com.ec/images/verified.png" style='+
            '"display:block;margin:0 auto; width:100%;">';
            
            $('form.wpwl-form-card').find('.wpwl-button').before(createRegistrationHtml);
        },
        
        style: "card",
        locale: "es",

        labels: {cvv: "CVV", cardHolder: "Nombre (Igual que en la tarjeta)"},
        
        registrations: {
            requireCvv: true,
            hideInitialPaymentForms: true
        }
    }
    </script>

</head>
<body>
    
<div style="color:white;">
    <h1>Formulario de Pago</h1>
    <p>Completa la información de tu tarjeta para proceder con el pago.</p>
</div>
    <!-- Formulario de pago -->
    <form action="<?php echo $baseUrl; ?>" class="paymentWidgets" data-brands="VISA MASTER DINERS DISCOVER AMEX">
    </form>


   
</body>
</html>