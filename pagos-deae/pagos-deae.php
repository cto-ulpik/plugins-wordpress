<?php
/*
Plugin Name: Pagos DEAE
Description: Plugin personalizado para realizar pagos mediante Datafast desde la página /pagos-deae.
Version: 1.0
Author: Nestor David Castillo cALLE
*/

if (!defined('ABSPATH')) {
    exit; // Evitar accesos directos
}

// Registrar la página personalizada
function pagos_deae_register_page() {
    add_rewrite_rule('^pagos-deae/?$', 'index.php?pagos_deae_page=1', 'top');
}
add_action('init', 'pagos_deae_register_page');

// Añadir query var para la página
function pagos_deae_add_query_var($vars) {
    $vars[] = 'pagos_deae_page';
    return $vars;
}
add_filter('query_vars', 'pagos_deae_add_query_var');

// Interceptar la carga de la página
function pagos_deae_template_redirect() {
    if (get_query_var('pagos_deae_page') == 1) {
        include plugin_dir_path(__FILE__) . 'pagos-deae-template.php';
        exit;
    }
}
add_action('template_redirect', 'pagos_deae_template_redirect');







// Registrar la página para pagos-deae-pay
function pagos_deae_pay_register_page() {
    add_rewrite_rule('^pagos-deae-pay/?$', 'index.php?pagos_deae_pay_page=1', 'top');
}
add_action('init', 'pagos_deae_pay_register_page');

// Añadir query var para la nueva página
function pagos_deae_pay_add_query_var($vars) {
    $vars[] = 'pagos_deae_pay_page'; // La página personalizada
    $vars[] = 'checkoutId'; // El parámetro que se enviará
    return $vars;
}
add_filter('query_vars', 'pagos_deae_pay_add_query_var');

// Interceptar la carga de pagos-deae-pay
function pagos_deae_pay_template_redirect() {
    if (get_query_var('pagos_deae_pay_page') == 1) {
        include plugin_dir_path(__FILE__) . 'pagos-deae-pay.php';
        exit;
    }
}
add_action('template_redirect', 'pagos_deae_pay_template_redirect');





// Activar permalinks al activar el plugin
function pagos_deae_flush_rewrite_rules() {
    pagos_deae_register_page();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'pagos_deae_flush_rewrite_rules');

// Limpiar las reglas al desactivar
function pagos_deae_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'pagos_deae_deactivate');

///

