<?php
// Verificar que se proporcione el parámetro checkoutId
if (!isset($_GET['checkoutId'])) {
    echo "Error: No se proporcionó un checkoutId.";
    exit;
}

// Obtener el checkoutId desde el parámetro GET
$checkoutId = sanitize_text_field($_GET['checkoutId']);

// Configuración del entorno
$baseUrl = home_url('/finalizar-deae'); // URL base del sitio
$url = "https://eu-test.oppwa.com/v1/"; // URL del entorno de pruebas

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar con Tarjeta</title>
    <!-- Incluir el script de pago con el checkoutId -->
    <script src="<?php echo $url; ?>paymentWidgets.js?checkoutId=<?php echo $checkoutId; ?>"></script>
    
</head>
<body>
    <h1>Formulario de Pago</h1>
    <p>Completa la información de tu tarjeta para proceder con el pago.</p>
    
    <!-- Formulario de pago -->
    <form action="<?php echo $baseUrl; ?>" class="paymentWidgets" data-brands="VISA MASTER DINERS DISCOVER AMEX">
    </form>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script type="text/javascript">
    var wpwlOptions = {
        onReady: function(onReady) {
            var createRegistrationHtml = '<div class="customLabel">Desea guardar de manera segura sus datos?</div>'+
                '<div class="customInput"><input type="checkbox" name="createRegistration" /></div>';
            $('form.wpwl-form-card').find('.wpwl-button').before(createRegistrationHtml);
        },
        style: "card",
        locale: "es",

        labels: {cvv: "Código de verificación", cardHolder: "Nombre(Igual que en la tarjeta),"},
        
        registrations: {
            requireCvv: true,
            hideInitialPaymentForms: true
        }
    }
    </script>
    
    <script>
        window.addEventListener("message", function(event) {
            console.log("Evento recibido:", event);

            if (event.origin.includes("oppwa.com")) {
                console.log("Contenido del event.data:", event.data);

                try {
                    let paymentResponse = JSON.parse(event.data);
                    console.log("Respuesta de Pago:", paymentResponse);

                    if (paymentResponse.result && paymentResponse.result.code === "000.100.110") {
                        document.getElementById("payment-status").innerHTML = "<h2>Pago Exitoso ✅</h2>";
                    } else {
                        document.getElementById("payment-status").innerHTML = "<h2>Error en el Pago ❌</h2>";
                    }
                } catch (error) {
                    console.error("Error al procesar la respuesta del pago:", error);
                }
            }
        }, false);
    </script>
</body>
</html>