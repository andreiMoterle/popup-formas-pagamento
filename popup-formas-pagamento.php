<?php
/**
 * Plugin Name: Popup Formas de Pagamento
 * Description: Exibe um popup com as formas de pagamento nos produtos do WooCommerce.
 * Version: 2.4
 * Author: Andrei Moterle
 * Author URI: https://github.com/andreiMoterle
 */

if (!defined('ABSPATH')) {
    exit;
}

// Carregar scripts e estilos
add_action('wp_enqueue_scripts', 'pfp_enqueue_scripts');
function pfp_enqueue_scripts() {
    wp_enqueue_style('pfp-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('pfp-script', plugin_dir_url(__FILE__) . 'assets/js/custom.js', array('jquery'), '1.0', true);

    wp_localize_script('pfp-script', 'custom_parcelas', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'pix_discount' => get_option('pfp_pix_discount', 0),
        'boleto_discount' => get_option('pfp_boleto_discount', 0),
    ));
}

// Incluir configurações do admin
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

// Shortcode para o popup
add_shortcode('popup_formas_pagamento', 'pfp_render_modal');
function pfp_render_modal() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/modal-parcelamento.php';
    return ob_get_clean();
}

// Função para gerar tabela de parcelamento
function gerar_tabela_parcelamento($preco) {
    $preco = floatval($preco); // Garantir que seja float
    $taxasJuros = get_option('pfp_interest_rates', array());
    if (!is_array($taxasJuros)) {
        $taxasJuros = json_decode($taxasJuros, true);
        if (!is_array($taxasJuros)) $taxasJuros = array();
    }
    ob_start();
    ?>
    <ul class="modal-parcel-list">
        <li class="modal-parcel-list-item">
            <span>À vista</span>
            <span>Total: <span id="total-a-vista">R$<?php echo number_format($preco, 2, ',', '.'); ?></span></span>
        </li>
        <?php
        for ($i = 2; $i <= 12; $i++) {
            $taxaJuros = isset($taxasJuros[$i]) ? floatval($taxasJuros[$i]) : 0;
            if ($taxaJuros == 0) {
                $valorParcela = $preco / $i;
                $total = $preco;
            } else {
                $total = $preco * (1 + ($taxaJuros / 100));
                $valorParcela = $total / $i;
            }
            ?>
            <li class="modal-parcel-list-item">
                <span><?php echo $i ?>x <?php echo ($taxaJuros == 0) ? 'sem juros' : 'de R$' . number_format($valorParcela, 2, ',', '.'); ?></span>
                <span>Total: <span id="total-<?php echo $i ?>-parcela">R$<?php echo number_format($total, 2, ',', '.'); ?></span></span>
            </li>
            <?php
        }
        ?>
    </ul>
    <?php
    return ob_get_clean();
}

// AJAX para atualizar tabela de parcelamento
add_action('wp_ajax_atualizar_parcelamento', 'pfp_atualizar_parcelamento');
add_action('wp_ajax_nopriv_atualizar_parcelamento', 'pfp_atualizar_parcelamento');
function pfp_atualizar_parcelamento() {
    if (isset($_POST['preco'])) {
        $preco = floatval($_POST['preco']);
        echo gerar_tabela_parcelamento($preco);
    }
    wp_die();
}

// Função para obter o maior número de parcelas sem juros
function pfp_get_parcelas_sem_juros() {
    $taxasJuros = get_option('pfp_interest_rates', array());
    if (!is_array($taxasJuros)) {
        $taxasJuros = json_decode($taxasJuros, true);
        if (!is_array($taxasJuros)) $taxasJuros = array();
    }

    $sem_juros = array_filter($taxasJuros, function($taxa) { return floatval($taxa) == 0; });
    if (empty($sem_juros)) return 1;
    return max(array_map('intval', array_keys($sem_juros)));
}

/**
 * Exibe ou retorna o valor parcelado com base nas configurações do admin.
 * @param float $preco
 * @param string $class
 * @param bool $echo
 * @return string|null
 */
function pfp_exibir_valor_parcelado($preco, $class = 'preco-parcelado', $echo = true) {
    $preco = floatval($preco); // Garantir que seja float
    $parcelas = pfp_get_parcelas_sem_juros();
    if ($parcelas < 2) return null;

    $enable_complete_info = get_option('pfp_enable_complete_info');
    
    if ($enable_complete_info) {
        // Formato completo: "R$ 218,41 no Pix ou 6x de R$ 38,32 sem juros"
        $pix_discount = floatval(get_option('pfp_pix_discount', 0));
        $pix_price = $preco * (1 - ($pix_discount / 100));
        $valor_parcela = $preco / $parcelas;
        
        $pix_formatado = wc_price($pix_price);
        $valor_parcela_formatado = wc_price($valor_parcela);
        
        $html = '<p class="' . esc_attr($class) . '">' . $pix_formatado . ' no Pix ou ' . $parcelas . 'x de ' . $valor_parcela_formatado . ' sem juros</p>';
    } else {
        // Formato simples: "Ou 6x de R$ 38,32 sem juros"
        $valor_parcela = $preco / $parcelas;
        $valor_parcela_formatado = wc_price($valor_parcela);
        $html = '<p class="' . esc_attr($class) . '">Ou ' . $parcelas . 'x de ' . $valor_parcela_formatado . ' sem juros</p>';
    }

    if ($echo) {
        echo $html;
        return null;
    }
    return $html;
}

// Página do produto (só se auto display estiver habilitado)
function pfp_mostrar_preco_parcelado() {
    if (!get_option('pfp_enable_auto_display')) return;
    global $product;
    if (!$product) return;
    $preco = floatval($product->get_price());
    pfp_exibir_valor_parcelado($preco, 'preco-parcelado', true);
}
add_action('woocommerce_single_product_summary', 'pfp_mostrar_preco_parcelado', 15);

// Listagem de produtos (só se auto display estiver habilitado)
function pfp_mostrar_preco_parcelado_em_listagem() {
    if (!get_option('pfp_enable_auto_display')) return;
    global $product;
    if (!$product) return;
    $preco = floatval($product->get_price());
    pfp_exibir_valor_parcelado($preco, 'preco-parcelado-listagem', true);
}
add_action('woocommerce_after_shop_loop_item_title', 'pfp_mostrar_preco_parcelado_em_listagem', 15);

// Shortcode
function pfp_shortcode_valor_parcelado($atts) {
    $atts = shortcode_atts(array(
        'class' => 'preco-parcelado-shortcode',
        'preco' => null
    ), $atts);
    
    global $product;
    if (!$product && !$atts['preco']) return '';
    
    $preco = $atts['preco'] ? floatval($atts['preco']) : floatval($product->get_price());
    return pfp_exibir_valor_parcelado($preco, $atts['class'], false);
}
add_shortcode('valor_parcelado', 'pfp_shortcode_valor_parcelado');

// Shortcode específico para informações completas
function pfp_shortcode_info_completa($atts) {
    $atts = shortcode_atts(array(
        'class' => 'info-pagamento-completa',
        'preco' => null
    ), $atts);
    
    global $product;
    if (!$product && !$atts['preco']) return '';
    
    $preco = $atts['preco'] ? floatval($atts['preco']) : floatval($product->get_price());
    $parcelas = pfp_get_parcelas_sem_juros();
    if ($parcelas < 2) return '';

    $pix_discount = floatval(get_option('pfp_pix_discount', 0));
    $pix_price = $preco * (1 - ($pix_discount / 100));
    $valor_parcela = $preco / $parcelas;
    
    $pix_formatado = wc_price($pix_price);
    $valor_parcela_formatado = wc_price($valor_parcela);
    
    return '<p class="' . esc_attr($atts['class']) . '">' . $pix_formatado . ' no Pix ou ' . $parcelas . 'x de ' . $valor_parcela_formatado . ' sem juros</p>';
}
add_shortcode('info_pagamento_completa', 'pfp_shortcode_info_completa');
?>