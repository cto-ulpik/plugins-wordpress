<?php
/*
Plugin Name: ULPIK - DSAC PRICE
Plugin URI: https://ulpik.com/
Description: Muestra el precio del producto dsac
Version: 1.0
Author: David Castillo
Author URI: https://davidcastillo.dev
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente.
}

// Registrar los scripts y estilos
function price_product_enqueue_assets() {
    wp_register_style('price-dsac-styles', plugin_dir_url(__FILE__) . 'css/price-dsac-styles.css');
}
add_action('wp_enqueue_scripts', 'price_product_enqueue_assets');

// Función para renderizar el shortcode
function show_price_dsac($atts) {
    date_default_timezone_set('America/Guayaquil');

    // Validar y sanitizar atributos
    $atts = shortcode_atts(array(
        'product_id' => '',
    ), $atts);

    $product_id = intval($atts['product_id']);
    if (!$product_id) {
        return '<div class="container-price"><p>Producto no válido.</p></div>';
    }

    // Encolar estilos
    wp_enqueue_style('price-dsac-styles');

    // Obtiene el producto
    $product = wc_get_product($product_id);

    if ($product && $product instanceof WC_Product) {
        $product_price = $product->get_regular_price();

        if ($product->get_id() == 12209) {
            $product->set_sale_price(190);
            $product->save();
        }

        $product_sale_price = $product->get_sale_price();

        $fecha_actual = new DateTime('now', new DateTimeZone('America/Guayaquil'));
        $dia = $fecha_actual->format('w');

        if ($dia > 0) {
            if ($product_sale_price) {
                return '
                <div class="container-price">
                    <h3 class="real-price-marca">De <span style="text-decoration: line-through;">$' . esc_html($product_price) . '</span> a</h3>
                    <p class="discount-price-marca">$' . esc_html($product_sale_price) . '</p>
                </div>';
            } else {
                return '<div class="container-price">
                        <p class="discount-price-marca">USD$' . esc_html($product_price) . '</p>
                    </div>';
            }
        } else {
            $product->set_sale_price($product_price);
            $product->save();
            return '<div class="container-price">
                        <p class="discount-price-marca">USD$' . esc_html($product_price) . '</p>
                    </div>';
        }
    } else {
        return '<div class="container-price">
                <p class="discount-price">Precio no disponible</p>
            </div>';
    }
}

// Registrar el shortcode
add_shortcode('price_dsac', 'show_price_dsac');