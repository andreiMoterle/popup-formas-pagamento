<?php
if (!defined('ABSPATH')) {
    exit;
}

// Cria a página de configurações no admin
add_action('admin_menu', 'pfp_add_admin_menu');
function pfp_add_admin_menu() {
    add_menu_page(
        'Popup Formas de Pagamento Settings',
        'Popup Formas de Pagamento',
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
    register_setting('pfp_settings_group', 'pfp_enable_complete_info');
    register_setting('pfp_settings_group', 'pfp_enable_auto_display');

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

    add_settings_field(
        'pfp_enable_complete_info',
        'Exibir Informações Completas',
        'pfp_enable_complete_info_render',
        'popup-formas-pagamento',
        'pfp_settings_section'
    );

    add_settings_field(
        'pfp_enable_auto_display',
        'Exibir Automaticamente',
        'pfp_enable_auto_display_render',
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

function pfp_enable_complete_info_render() {
    $checked = get_option('pfp_enable_complete_info') ? 'checked' : '';
    echo '<input type="checkbox" name="pfp_enable_complete_info" value="1" ' . $checked . ' /> 
          <label>Mostrar "R$ X no Pix ou Xx de R$ Y sem juros" ao invés de só parcelas</label>';
}

function pfp_enable_auto_display_render() {
    $checked = get_option('pfp_enable_auto_display') ? 'checked' : '';
    echo '<input type="checkbox" name="pfp_enable_auto_display" value="1" ' . $checked . ' /> 
          <label>Exibir automaticamente nas páginas de produto e listagem (desmarque para usar apenas shortcode)</label>';
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
    
    <div style="margin-top: 30px; background: #fff; border: 1px solid #ccd0d4; padding: 20px; border-radius: 4px;">
        <h2>Shortcodes Disponíveis</h2>
        <p><strong>[valor_parcelado]</strong> - Exibe as parcelas conforme configuração (simples ou completa)</p>
        <p><strong>[info_pagamento_completa]</strong> - Sempre exibe formato completo: "R$ X no Pix ou Xx de R$ Y sem juros"</p>
        <p><em>Você pode usar esses shortcodes em qualquer lugar: templates, produtos, widgets, etc.</em></p>
        
        <h3>Como usar no seu template personalizado:</h3>
        <code>&lt;?php echo do_shortcode('[info_pagamento_completa]'); ?&gt;</code>
    </div>
    
    <script>
    document.getElementById('pfp-settings-form').addEventListener('submit', function() {
        alert('Configurações salvas com sucesso!');
    });
    </script>
    <?php
}
?>