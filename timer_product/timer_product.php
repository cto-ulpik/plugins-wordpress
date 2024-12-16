<?php
/*
Plugin Name: Price Product
Plugin URI: https://ulpik.com/
Description: Muestra el precio del producto con descuentos y temporizador.
Version: 1.0
Author: David Castillo
Author URI: https://davidcastillo.dev
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente.
}

// Función principal del plugin
function price_product_register_shortcode() {
    function show_price($atts) {
        date_default_timezone_set('America/Guayaquil');
        // Recibe el ID del producto desde el shortcode
        $atts = shortcode_atts(array(
            'product_id' => '',
        ), $atts);

        // Obtiene el precio del producto
        $product = wc_get_product($atts['product_id']);
        
        if ($product) {
            $product_price = $product->get_regular_price();
            $product_sale_price = $product->get_sale_price();
            
            $fecha_actual = new DateTime();
            $dia = $fecha_actual->format('w');
            
            if ($dia > 0) {
                // Establecer la hora a las 8:00 AM
                $fecha_8am = clone $fecha_actual; // Crear una copia para no modificar la fecha actual
                $fecha_8am->setTime(8, 0, 0);

                // Establecer la hora a las 11:00 PM
                $fecha_5pm = clone $fecha_actual;
                $fecha_5pm->setTime(23, 0, 0);

                $diferencia = $fecha_actual->diff($fecha_5pm);
                $tiempo_restante = abs(intval($diferencia->format('%h')) * 3600 + intval($diferencia->format('%i')) * 60 + intval($diferencia->format('%s')));
                if (intval($fecha_actual->format('H')) < 23) {
                    
                    if ($product_sale_price) {
                        $ahorrance = $product_price - $product_sale_price;    
                        return '
                        <div class="container-price">
                            <h3 class="real-price-marca">De <span style="text-decoration: line-through;">$' . $product_price . '</span> a</h3>
                            <p class="discount-price-marca">$' . $product_sale_price . '</p>
                            <div class="temporizador-descuento" data-tiempo-restante="' . esc_attr($tiempo_restante) . '"></div>
                        </div>';
                    } else {
                        return '<div class="container-price">
                                <p class="discount-price-marca">USD$' . $product_price . '</p>
                            </div>';
                    }
                } else {
                    return '<div class="container-price">
                                <p class="discount-price-marca">USD$' . $product_price . '</p>
                            </div>';
                }
            } else {
                $product->set_sale_price($product_price);
                $product->save();
                return '<div class="container-price">
                            <p class="discount-price-marca">USD$' . $product_price . '</p>
                        </div>';
            }
        } else {
            return '<div class="container-price">
                    <p class="discount-price">Precio no disponible</p>
                </div>';
        }
    }

    // Registrar el shortcode
    add_shortcode('price_product', 'show_price');
}

// Hook para inicializar el plugin
add_action('init', 'price_product_register_shortcode');