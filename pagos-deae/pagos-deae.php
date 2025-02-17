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