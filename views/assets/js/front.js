$(document).ready(() => {

    if ($('body').find('.js-cart-line-product-quantity').length){
        $('body').find('.js-cart-line-product-quantity').each(function () {
            initQuantitySteps();
        });
    
        prestashop.on('updatedCart', function (event) {
            initQuantitySteps();
        });
    
        // $('body').on('click', '.js-touchspin.bootstrap-touchspin-up, .js-touchspin.bootstrap-touchspin-down', function (e) {
        //     $(this).parent().parent().prepend('<div class="quantity-preloader"></div>');
        // })
    }

    if ($('body').find('#quantity_wanted').length){

        const qty_pack = $('#quantity_wanted').closest('form').find('input[name="qty_pack"]');
        if (qty_pack && parseInt($(qty_pack).val()) > 1){
            const step = parseInt($(qty_pack).val());
            $('#quantity_wanted').trigger('touchspin.updatesettings', {
                step: step,
                forcestepdivisibility: 'none'
            });
        }

        // prestashop.on('updateProduct', function (event) {
        //     $('#quantity_wanted').trigger('touchspin.updatesettings', {
        //         step: 1,
        //         forcestepdivisibility: 'none'
        //     })
        // });
        
    }
});

const initQuantitySteps = () => {
    $('body').find('.js-cart-line-product-quantity').each(function () {
        const step = $(this).data('qty-pack-step');
        if (step) {
            $(this).trigger('touchspin.updatesettings', {
                step: step,
                forcestepdivisibility: 'none'
            });
        }
    });
}