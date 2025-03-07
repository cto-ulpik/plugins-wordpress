<?php
/*
Plugin Name: Pagos DEAE
Description: Plugin personalizado para realizar pagos mediante Datafast desde las páginas /pagos-deae y /card-deae.
Version: 1.0
Author: Tu Nombre
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
function deae_create_customers_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "deae_customers";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        customer_id VARCHAR(100) NOT NULL UNIQUE,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(50) NOT NULL,
        document_type VARCHAR(50) NOT NULL,
        document_id VARCHAR(50) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'deae_create_customers_table');







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
    $customers = $wpdb->get_results("SELECT * FROM $table_customers");

    echo '<div class="wrap">';
    echo '<h1>Clientes Registrados</h1>';
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Documento</th><th>Fecha Registro</th></tr></thead>';
    echo '<tbody>';

    foreach ($customers as $customer) {
        echo "<tr>
                <td>{$customer->id}</td>
                <td>{$customer->name}</td>
                <td>{$customer->email}</td>
                <td>{$customer->phone}</td>
                <td>{$customer->document_id}</td>
                <td>{$customer->created_at}</td>
              </tr>";
    }

    echo '</tbody></table>';
    echo '</div>';
}





function deae_transactions_page() {
    global $wpdb;
    $table_transactions = $wpdb->prefix . "deae_transactions";
    $transactions = $wpdb->get_results("SELECT * FROM $table_transactions");

    echo '<div class="wrap">';
    echo '<h1>Transacciones Registradas</h1>';
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Transacción</th><th>Monto</th><th>Cliente</th><th>Email</th><th>Tarjeta</th><th>Estado</th><th>Fecha</th></tr></thead>';
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
              </tr>";
    }

    echo '</tbody></table>';
    echo '</div>';
}