<?php
/**
 * Plugin Name: Popup Formas de Pagamento
 * Description: Exibe um popup com as formas de pagamento nos produtos do WooCommerce.
 * Version: 2.2
 * Author: ANDREI MOTERLE
 * Text Domain: popup-formas-pagamento
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
    $taxasJuros = json_decode(get_option('pfp_interest_rates', '{}'), true);
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
    $taxasJuros = json_decode(get_option('pfp_interest_rates', '{}'), true);
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
    $parcelas = pfp_get_parcelas_sem_juros();
    if ($parcelas < 2) return null;

    $valor_parcela = $preco / $parcelas;
    $valor_parcela_formatado = wc_price($valor_parcela);
    $html = '<p class="' . esc_attr($class) . '">Ou ' . $parcelas . 'x de ' . $valor_parcela_formatado . ' sem juros</p>';

    if ($echo) {
        echo $html;
        return null;
    }
    return $html;
}

// Página do produto
function pfp_mostrar_preco_parcelado() {
    global $product;
    if (!$product) return;
    pfp_exibir_valor_parcelado($product->get_price(), 'preco-parcelado', true);
}
add_action('woocommerce_single_product_summary', 'pfp_mostrar_preco_parcelado', 15);

// Listagem de produtos
function pfp_mostrar_preco_parcelado_em_listagem() {
    global $product;
    if (!$product) return;
    pfp_exibir_valor_parcelado($product->get_price(), 'preco-parcelado-listagem', true);
}
add_action('woocommerce_after_shop_loop_item_title', 'pfp_mostrar_preco_parcelado_em_listagem', 15);

// Shortcode
function pfp_shortcode_valor_parcelado() {
    global $product;
    if (!$product) return '';
    return pfp_exibir_valor_parcelado($product->get_price(), 'preco-parcelado-listagem', false);
}
add_shortcode('valor_parcelado', 'pfp_shortcode_valor_parcelado');
?>