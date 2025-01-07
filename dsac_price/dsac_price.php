<?php
/*
Plugin Name: ULPIK - DSAC PRICE
Plugin URI: https://ulpik.com/
Description: Returns the product name based on its ID.
Version: 1.0
Author: David Castillo
Author URI: https://davidcastillo.dev
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>The <strong>ULPIK - DSAC PRICE</strong> plugin requires WooCommerce to function.</p></div>';
    });
    return;
}

// Function to get the product name by ID
function get_product_name_by_id($atts) {
    // Validate and sanitize the attribute
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts);

    $product_id = intval($atts['id']);

    if (!$product_id) {
        return '<p>Please provide a valid product ID.</p>';
    }

    // Get the product
    $product = wc_get_product($product_id);

    if ($product && $product instanceof WC_Product) {
        return '<p>The product name is: <strong>' . esc_html($product->get_name()) . '</strong></p>';
    } else {
        return '<p>No product found with the provided ID.</p>';
    }
}

// Register the shortcode
add_shortcode('product_by_id', 'get_product_name_by_id');