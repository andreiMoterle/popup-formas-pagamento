<?php
if (!defined('ABSPATH')) {
    exit;
}

// Cria a página de configurações no admin
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

// Registra as opções
add_action('admin_init', 'pfp_settings_init');
function pfp_settings_init() {
    register_setting('pfp_settings_group', 'pfp_interest_rates');
    register_setting('pfp_settings_group', 'pfp_pix_discount');
    register_setting('pfp_settings_group', 'pfp_boleto_discount');

    add_settings_section(
        'pfp_settings_section',
        'Formas de Pagamento',
        null,
        'popup-formas-pagamento'
    );

    add_settings_field(
        'pfp_interest_rates',
        'Taxas de Juros por Parcela (%)',
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
        'pfp_boleto_discount',
        'Boleto Discount (%)',
        'pfp_boleto_discount_render',
        'popup-formas-pagamento',
        'pfp_settings_section'
    );
}

// Renderiza inputs para cada parcela
function pfp_interest_rates_render() {
    $rates = get_option('pfp_interest_rates', array());
    // Se vier como string (JSON antigo), tenta decodificar
    if (!is_array($rates)) {
        $rates = json_decode($rates, true);
        if (!is_array($rates)) $rates = array();
    }
    echo '<table><tr><th>Parcelas</th><th>Taxa de Juros (%)</th></tr>';
    for ($i = 2; $i <= 12; $i++) {
        $val = isset($rates[$i]) ? esc_attr($rates[$i]) : '0';
        echo '<tr>
            <td>' . $i . 'x</td>
            <td><input type="number" step="0.01" min="0" name="pfp_interest_rates[' . $i . ']" value="' . $val . '" style="width:80px"></td>
        </tr>';
    }
    echo '</table>';
    echo '<p>Preencha a taxa de juros para cada quantidade de parcelas. Use 0 para "sem juros".</p>';
}

function pfp_pix_discount_render() {
    $options = get_option('pfp_pix_discount');
    echo '<input type="number" name="pfp_pix_discount" value="' . esc_attr($options) . '" step="0.01" min="0" max="100" /> %';
}

function pfp_boleto_discount_render() {
    $options = get_option('pfp_boleto_discount');
    echo '<input type="number" name="pfp_boleto_discount" value="' . esc_attr($options) . '" step="0.01" min="0" max="100" /> %';
}

// Renderiza a página de configurações
function pfp_settings_page() {
    ?>
    <form action="options.php" method="post" id="pfp-settings-form">
        <h1>Popup Formas de Pagamento Configurações</h1>
        <?php
        settings_fields('pfp_settings_group');
        do_settings_sections('popup-formas-pagamento');
        submit_button();
        ?>
    </form>
    <script>
    document.getElementById('pfp-settings-form').addEventListener('submit', function() {
        alert('Configurações salvas com sucesso!');
    });
    </script>
    <?php
}
?>