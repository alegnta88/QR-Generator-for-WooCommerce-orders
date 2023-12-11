<?php
/*
Plugin Name: WooCommerce QR Code Generator
Description: This plugin Generates a QR code with order details using phpqrcode.
Version: 1.0
Author: Alegnta Lolamo
*/

// Include phpqrcode library
require_once(plugin_dir_path(__FILE__) . 'phpqrcode/qrlib.php');

// Hook to add custom fields to the checkout page
add_action('woocommerce_after_checkout_form', 'add_custom_checkout_fields');

function add_custom_checkout_fields() {
    echo '<div id="qr-code-container"></div>';
}

// Hook to generate QR code after order creation
add_action('woocommerce_thankyou', 'generate_qr_code_on_thankyou_page', 10, 1);

function generate_qr_code_on_thankyou_page($order_id) {
    $order = wc_get_order($order_id);

    // Get order details
    $order_number = $order->get_order_number();
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $customer_phone = $order->get_billing_phone();

    // Combine order details
    $qr_data = get_order_qr_data($order);

    // Generate QR code with a unique filename
    $qr_code_url = generate_qr_code($qr_data, $order_id);

    // Display QR code on the thank you page
    echo '<div id="qr-code-container-thankyou" style="text-align: center; margin-top: 20px;">';
    echo '<img src="' . esc_url($qr_code_url) . '" alt="QR Code" style="max-width: 60%;"><br>';
    echo '<span style="font-size: 14px;">Scan the QR code for order details.</span>';
    echo '</div>';
}

// Hook to add QR code to invoice
add_action('wpo_wcpdf_after_order_details', 'add_qr_code_to_invoice', 10, 2);

if (!function_exists('add_qr_code_to_invoice')) {
function add_qr_code_to_invoice($template_type, $order) {
    error_log('Adding QR code to invoice. Template Type: ' . $template_type);

    // Check if the template type is invoice
    if ($template_type === 'invoice') {
        // Get order details
        $order_id = $order->get_id();
        $qr_code_url = generate_qr_code(get_order_qr_data($order), $order_id);

        // Display QR code on the invoice
        echo '<div id="qr-code-container-invoice" style="position: absolute;">';
        echo '</div>';             
    }
}
}

// Function to get order details for QR code
function get_order_qr_data($order) {
    $order_number = $order->get_order_number();
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $customer_phone = $order->get_billing_phone();

    $qr_data = $order_number . ',' . $customer_name . ',' . $customer_phone;
    return $qr_data;
}

// Function to generate QR code using phpqrcode
function generate_qr_code($data, $order_id) {
    // Save the QR code image to the uploads directory
    $upload_dir = wp_upload_dir();
    
    // Generate a unique filename (use order ID, timestamp, or any unique identifier)
    $filename = 'qr-code-' . $order_id . '-' . time() . '.png';

    $upload_path = $upload_dir['path'] . '/' . $filename;

    QRcode::png($data, $upload_path, QR_ECLEVEL_L, 4);

    return $upload_dir['url'] . '/' . $filename;
}