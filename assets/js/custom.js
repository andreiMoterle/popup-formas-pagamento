jQuery(document).ready(function($) {
    $("form.variations_form").on("show_variation", function(event, variation) {
        let precoVariacao = parseFloat(variation.display_price);

        // Atualiza parcelamento
        $.ajax({
            url: custom_parcelas.ajax_url,
            type: 'POST',
            data: {
                action: 'atualizar_parcelamento',
                preco: precoVariacao
            },
            success: function(response) {
                $('.modal-parcel-method.card-method').html(response);
            }
        });

        // Atualiza PIX
        let pixDiscount = parseFloat(custom_parcelas.pix_discount) || 0;
        let pixPrice = precoVariacao * (1 - (pixDiscount / 100));
        $('.pix-method .modal-parcel-price-cash').html('R$' + pixPrice.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        // Atualiza Boleto
        let boletoDiscount = parseFloat(custom_parcelas.boleto_discount) || 0;
        let boletoPrice = precoVariacao * (1 - (boletoDiscount / 100));
        $('.boleto-method .modal-parcel-price-cash').html('R$' + boletoPrice.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    });
});