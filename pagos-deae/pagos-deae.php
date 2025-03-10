<?php
/*
Plugin Name: Pagos DEAE
Description: Plugin personalizado para realizar pagos mediante Datafast desde las páginas /pagos-deae y /card-deae.
Version: 1.0
Author: David Castillo
*/

if (!defined('ABSPATH')) {
    exit; // Evitar accesos directos
}

// Registrar las páginas personalizadas
function pagos_deae_register_pages() {
    add_rewrite_rule('^pagos-deae/?$', 'index.php?pagos_deae_page=1', 'top');
    add_rewrite_rule('^card-deae/?$', 'index.php?card_deae_page=1', 'top');
    add_rewrite_rule('^finalizar-deae/?$', 'index.php?finalizar_deae_page=1', 'top');
}
add_action('init', 'pagos_deae_register_pages');

// Añadir query vars
function pagos_deae_add_query_vars($vars) {
    $vars[] = 'pagos_deae_page'; // Página /pagos-deae
    $vars[] = 'card_deae_page'; // Página /card-deae
    $vars[] = 'finalizar_deae_page'; // Página /finalizar_deae
    $vars[] = 'checkoutId'; // Parámetro adicional para /card-deae
    return $vars;
}
add_filter('query_vars', 'pagos_deae_add_query_vars');

// Interceptar y renderizar /pagos-deae
function pagos_deae_template_redirect() {
    if (get_query_var('pagos_deae_page') == 1) {
        include plugin_dir_path(__FILE__) . 'pagos-deae-template.php';
        exit;
    }
}
add_action('template_redirect', 'pagos_deae_template_redirect');

// Interceptar y renderizar /card-deae
function card_deae_template_redirect() {
    if (get_query_var('card_deae_page') == 1) {
        include plugin_dir_path(__FILE__) . 'card-deae-template.php';
        exit;
    }
}
add_action('template_redirect', 'card_deae_template_redirect');

// Interceptar y renderizar /finalizar_deae
function finalizar_deae_template_redirect() {
    if (get_query_var('finalizar_deae_page') == 1) {
        include plugin_dir_path(__FILE__) . 'finalizar-deae-template.php';
        exit;
    }
}
add_action('template_redirect', 'finalizar_deae_template_redirect');




// Activar permalinks al activar el plugin
function pagos_deae_flush_rewrite_rules() {
    pagos_deae_register_pages();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'pagos_deae_flush_rewrite_rules');

// Limpiar las reglas al desactivar
function pagos_deae_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'pagos_deae_deactivate');



// CREACION DE LA TABLA DE TRANSACCIONES
function deae_create_transactions_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "deae_transactions";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        transaction_id VARCHAR(100) NOT NULL,
        registration_id VARCHAR(100) NOT NULL,
        payment_brand VARCHAR(50) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_email VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(50) NOT NULL,
        customer_doc_type VARCHAR(50) NOT NULL,
        customer_doc_id VARCHAR(50) NOT NULL,
        card_bin VARCHAR(10) NOT NULL,
        card_last4 VARCHAR(10) NOT NULL,
        card_expiry VARCHAR(10) NOT NULL,
        cart_name VARCHAR(100) NOT NULL,
        cart_description TEXT NOT NULL,
        cart_price DECIMAL(10,2) NOT NULL,
        cart_quantity INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'deae_create_transactions_table');



// creacion de tabla de clientes



//// ACTUALIZACION DE TABLA CLIENTES
function deae_update_customers_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "deae_customers";
    $charset_collate = $wpdb->get_charset_collate();

    // Verificar si la tabla ya existe antes de crearla
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            phone VARCHAR(50) NOT NULL,
            document_type VARCHAR(50) NOT NULL,
            document_id VARCHAR(50) NOT NULL UNIQUE,
            registration_id VARCHAR(100) NOT NULL,
            tipo_suscripcion VARCHAR(100) NOT NULL,
            monto_suscripcion DECIMAL(10,2) NOT NULL,
            estado_suscripcion BOOLEAN NOT NULL DEFAULT 1,
            ultimo_pago_suscripcion DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'deae_update_customers_table');






// Agregar un menú en el administrador de WordPress
function deae_admin_menu() {
    add_menu_page(
        'Pagos DEAE',          // Título de la página
        'Pagos DEAE',          // Nombre del menú
        'manage_options',      // Capacidad requerida
        'deae_dashboard',      // Slug del menú
        'deae_dashboard_page', // Función para mostrar la página principal
        'dashicons-money',     // Icono del menú
        6                      // Posición en el menú
    );

    // Submenú: Clientes
    add_submenu_page(
        'deae_dashboard',
        'Clientes DEAE',
        'Clientes',
        'manage_options',
        'deae_customers',
        'deae_customers_page'
    );

    // Submenú: Transacciones
    add_submenu_page(
        'deae_dashboard',
        'Transacciones DEAE',
        'Transacciones',
        'manage_options',
        'deae_transactions',
        'deae_transactions_page'
    );
}
add_action('admin_menu', 'deae_admin_menu');




function deae_dashboard_page() {
    echo '<div class="wrap">';
    echo '<h1>Panel de Administración - Pagos DEAE</h1>';
    echo '<p>Desde aquí puedes gestionar los clientes y transacciones.</p>';
    echo '<ul>
            <li><a href="' . admin_url('admin.php?page=deae_customers') . '">📋 Ver Clientes</a></li>
            <li><a href="' . admin_url('admin.php?page=deae_transactions') . '">💳 Ver Transacciones</a></li>
          </ul>';
    echo '</div>';
}

function deae_customers_page() {
    global $wpdb;
    $table_customers = $wpdb->prefix . "deae_customers";

    // Obtener filtros
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    // Construir consulta con filtro
    $query = "SELECT * FROM $table_customers WHERE 1=1";
    if ($search) {
        $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR document_id LIKE '%$search%')";
    }

    $customers = $wpdb->get_results($query);

    echo '<div class="wrap">';
    echo '<h1>Clientes Registrados</h1>';

    // Formulario de búsqueda
    echo '<form method="GET">';
    echo '<input type="hidden" name="page" value="deae_customers">';
    echo '<input type="text" name="search" placeholder="Buscar por nombre, email o documento" value="' . esc_attr($search) . '">';
    echo '<button type="submit" class="button">🔍 Buscar</button>';
    echo '</form>';

    // Botón de exportación CSV
    echo '<a href="' . admin_url('admin-post.php?action=export_deae_customers') . '" class="button button-primary">📤 Exportar CSV</a>';

    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Documento</th>
                <th>Registro</th>
                <th>Suscripción</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Última Suscripción</th>
                <th>Acciones</th>
            </tr>
          </thead>';
    echo '<tbody>';

    foreach ($customers as $customer) {
        $estado = $customer->estado_suscripcion ? "✅ Activa" : "❌ Inactiva";
        echo "<tr>
                <td>{$customer->id}</td>
                <td>{$customer->name}</td>
                <td>{$customer->email}</td>
                <td>{$customer->phone}</td>
                <td>{$customer->document_id}</td>
                <td>{$customer->registration_id}</td>
                <td>{$customer->tipo_suscripcion}</td>
                <td>\${$customer->monto_suscripcion}</td>
                <td>{$estado}</td>
                <td>{$customer->ultimo_pago_suscripcion}</td>
                <td>
                    <a href='" . admin_url("admin-post.php?action=process_subscription_payment&id={$customer->id}") . "' class='button button-primary'>💳 Pagar</a>
                    <a href='" . admin_url("admin.php?page=deae_customers_edit&id={$customer->id}") . "' class='button'>✏️ Editar</a>
                    <a href='" . admin_url("admin-post.php?action=delete_deae_customer&id={$customer->id}") . "' class='button button-danger' onclick='return confirm(\"¿Eliminar este cliente?\");'>🗑️ Eliminar</a>
                </td>
              </tr>";
    }

    echo '</tbody></table>';
    echo '</div>';
}



function deae_transactions_page() {
    global $wpdb;
    $table_transactions = $wpdb->prefix . "deae_transactions";

    // Obtener filtros
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

    // Construir consulta con filtro
    $query = "SELECT * FROM $table_transactions WHERE 1=1";
    if ($search) {
        $query .= " AND (customer_name LIKE '%$search%' OR customer_email LIKE '%$search%')";
    }

    $transactions = $wpdb->get_results($query);

    echo '<div class="wrap">';
    echo '<h1>Transacciones Registradas</h1>';

    // Formulario de búsqueda
    echo '<form method="GET">';
    echo '<input type="hidden" name="page" value="deae_transactions">';
    echo '<input type="text" name="search" placeholder="Buscar por cliente o email" value="' . esc_attr($search) . '">';
    echo '<button type="submit" class="button">🔍 Buscar</button>';
    echo '</form>';

    // Botón de exportación CSV
    echo '<a href="' . admin_url('admin-post.php?action=export_deae_transactions') . '" class="button button-primary">📤 Exportar CSV</a>';

    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Transacción</th><th>Monto</th><th>Cliente</th><th>Email</th><th>Tarjeta</th><th>Estado</th><th>Fecha</th><th>Acciones</th></tr></thead>';
    echo '<tbody>';

    foreach ($transactions as $transaction) {
        echo "<tr>
                <td>{$transaction->id}</td>
                <td>{$transaction->transaction_id}</td>
                <td>\${$transaction->amount}</td>
                <td>{$transaction->customer_name}</td>
                <td>{$transaction->customer_email}</td>
                <td>{$transaction->card_last4}</td>
                <td>✅ Pago Exitoso</td>
                <td>{$transaction->created_at}</td>
                <td>
                    <a href='" . admin_url("admin-post.php?action=delete_deae_transaction&id={$transaction->id}") . "' class='button button-danger' onclick='return confirm(\"¿Eliminar esta transacción?\");'>🗑️ Eliminar</a>
                </td>
              </tr>";
    }

    echo '</tbody></table>';
    echo '</div>';
}


function export_deae_customers() {
    global $wpdb;
    $table_customers = $wpdb->prefix . "deae_customers";
    $customers = $wpdb->get_results("SELECT * FROM $table_customers", ARRAY_A);

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=clientes_deae.csv");

    $output = fopen("php://output", "w");
    fputcsv($output, ["ID", "Nombre", "Email", "Teléfono", "Documento", "Fecha Creación"]);

    foreach ($customers as $customer) {
        fputcsv($output, [
            $customer['id'],
            $customer['name'],
            $customer['email'],
            $customer['phone'],
            $customer['document_id'],
            $customer['created_at']
        ]);
    }

    fclose($output);
    exit;
}
add_action('admin_post_export_deae_customers', 'export_deae_customers');



function export_deae_transactions() {
    global $wpdb;
    $table_transactions = $wpdb->prefix . "deae_transactions";
    $transactions = $wpdb->get_results("SELECT * FROM $table_transactions", ARRAY_A);

    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=transacciones_deae.csv");

    $output = fopen("php://output", "w");
    fputcsv($output, ["ID", "Transacción", "Monto", "Cliente", "Email", "Tarjeta", "Fecha"]);

    foreach ($transactions as $transaction) {
        fputcsv($output, $transaction);
    }

    fclose($output);
    exit;
}
add_action('admin_post_export_deae_transactions', 'export_deae_transactions');







function delete_deae_customer() {
    global $wpdb;
    $table_customers = $wpdb->prefix . "deae_customers";

    if (isset($_GET['id'])) {
        $wpdb->delete($table_customers, ['id' => $_GET['id']], ['%d']);
    }

    wp_redirect(admin_url('admin.php?page=deae_customers'));
    exit;
}
add_action('admin_post_delete_deae_customer', 'delete_deae_customer');

function delete_deae_transaction() {
    global $wpdb;
    $table_transactions = $wpdb->prefix . "deae_transactions";

    if (isset($_GET['id'])) {
        $wpdb->delete($table_transactions, ['id' => $_GET['id']], ['%d']);
    }

    wp_redirect(admin_url('admin.php?page=deae_transactions'));
    exit;
}
add_action('admin_post_delete_deae_transaction', 'delete_deae_transaction');



/// FUNCION PARA REALIZAR EL PAGO
function process_subscription_payment() {
    global $wpdb;
    $table_customers = $wpdb->prefix . "deae_customers";
    $table_transactions = $wpdb->prefix . "deae_transactions";

    if (!isset($_GET['id'])) {
        wp_redirect(admin_url('admin.php?page=deae_customers'));
        exit;
    }

    $customer_id = intval($_GET['id']);
    $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_customers WHERE id = %d", $customer_id));

    if (!$customer || empty($customer->registration_id)) {
        echo "Error: Cliente no encontrado o no tiene un Registration ID.";
        exit;
    }

    // Generar ID de transacción único
    $trx = uniqid("trx_");

    // Definir valores base para la transacción
    $base0 = 0.00;
    $baseImponible = $customer->monto_suscripcion;
    $iva = $baseImponible * 0.12;

    // Datos de la solicitud de pago
    $url = "https://test.oppwa.com/v1/registrations/" . $customer->registration_id . "/payments";
    $data = "entityId=8ac7a4c994bb78290194bd40497301d5" .
        "&amount=" . number_format($customer->monto_suscripcion, 2, '.', '') .
        "&currency=USD" .
        "&paymentType=DB" .
        "&recurringType=REPEATED" .
        "&merchantTransactionId=" . $trx .
        "&customParameters[SHOPPER_MID]=1000000505" .
        "&customParameters[SHOPPER_TID]=PD100406" .
        "&customParameters[SHOPPER_ECI]=0103910" .
        "&customParameters[SHOPPER_PSERV]=17913101 " .
        "&customParameters[SHOPPER_VAL_BASE0]=" . number_format($base0, 2, '.', '') .
        "&customParameters[SHOPPER_VAL_BASEIMP]=" . number_format($baseImponible, 2, '.', '') .
        "&customParameters[SHOPPER_VAL_IVA]=" . number_format($iva, 2, '.', '') .
        "&customParameters[SHOPPER_VERSIONDF]=2" .
        "&testMode=EXTERNAL";

    // Realizar la solicitud con CURL
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
    curl_close($ch);

    $response = json_decode($responseData, true);

    // Si el pago fue exitoso, actualizar la última fecha de pago y guardar la transacción
    if ($response['result']['code'] === "000.100.110" || $response['result']['code'] === "000.100.112") {
        $wpdb->update(
            $table_customers,
            ['ultimo_pago_suscripcion' => current_time('mysql')],
            ['id' => $customer_id]
        );

        // Insertar en la tabla de transacciones
        $wpdb->insert(
            $table_transactions,
            [
                'transaction_id' => $trx,
                'registration_id' => $customer->registration_id,
                'payment_brand' => 'RECURRING',
                'amount' => $customer->monto_suscripcion,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'customer_doc_type' => $customer->document_type,
                'customer_doc_id' => $customer->document_id,
                'cart_name' => $customer->tipo_suscripcion,
                'cart_price' => $customer->monto_suscripcion,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s']
        );

        echo "✅ Pago recurrente exitoso.";
    } else {
        echo "❌ Error en el pago: " . $response['result']['description'];
    }

    wp_redirect(admin_url('admin.php?page=deae_customers'));
    exit;
}
add_action('admin_post_process_subscription_payment', 'process_subscription_payment');

