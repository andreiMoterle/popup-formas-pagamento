<?php
if (!defined('ABSPATH')) {
    exit;
}

// Create the admin settings page
add_action('admin_menu', 'pfp_add_admin_menu');
function pfp_add_admin_menu() {
    add_menu_page(
        'Popup Formas de Pagamento Settings',
        'Popup Payment Settings',
        'manage_options',
        'popup-formas-pagamento',
        'pfp_settings_page'
    );
}

// Register settings
add_action('admin_init', 'pfp_settings_init');
function pfp_settings_init() {
    register_setting('pfp_settings_group', 'pfp_interest_rates');
    register_setting('pfp_settings_group', 'pfp_pix_discount');
    register_setting('pfp_settings_group', 'pfp_boleto_settings');
    register_setting('pfp_settings_group', 'pfp_boleto_discount');

    add_settings_section(
        'pfp_settings_section',
        'Payment Settings',
        null,
        'popup-formas-pagamento'
    );

    add_settings_field(
        'pfp_interest_rates',
        'Interest Rates (JSON)',
        'pfp_interest_rates_render',
        'popup-formas-pagamento',
        'pfp_settings_section'
    );

    add_settings_field(
        'pfp_pix_discount',
        'PIX Discount (%)',
        'pfp_pix_discount_render',
        'popup-formas-pagamento',
        'pfp_settings_section'
    );

    add_settings_field(
        'pfp_boleto_settings',
        'Boleto Payment Settings',
        'pfp_boleto_settings_render',
        'popup-formas-pagamento',
        'pfp_settings_section'
    );

    add_settings_field(
        'pfp_boleto_discount',
        'Boleto Discount (%)',
        'pfp_boleto_discount_render',
        'popup-formas-pagamento',
        'pfp_settings_section'
    );
}

function pfp_interest_rates_render() {
    $options = get_option('pfp_interest_rates');
    echo '<textarea rows="5" cols="50" name="pfp_interest_rates">' . esc_textarea($options) . '</textarea>';
    echo '<p>Enter interest rates as a JSON object, e.g., {"2": 0, "3": 0, "4": 10.86, ...}</p>';
}

function pfp_pix_discount_render() {
    $options = get_option('pfp_pix_discount');
    echo '<input type="number" name="pfp_pix_discount" value="' . esc_attr($options) . '" step="0.01" min="0" max="100" /> %';
}

function pfp_boleto_settings_render() {
    $options = get_option('pfp_boleto_settings');
    echo '<textarea rows="5" cols="50" name="pfp_boleto_settings">' . esc_textarea($options) . '</textarea>';
    echo '<p>Enter any specific settings for boleto payments.</p>';
}

function pfp_boleto_discount_render() {
    $options = get_option('pfp_boleto_discount');
    echo '<input type="number" name="pfp_boleto_discount" value="' . esc_attr($options) . '" step="0.01" min="0" max="100" /> %';
}

// Render the settings page
function pfp_settings_page() {
    ?>
    <form action="options.php" method="post">
        <h1>Popup Formas de Pagamento Settings</h1>
        <?php
        settings_fields('pfp_settings_group');
        do_settings_sections('popup-formas-pagamento');
        submit_button();
        ?>
    </form>
    <?php
}
?>