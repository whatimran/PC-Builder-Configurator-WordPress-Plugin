jQuery(document).ready(function($) {
    function updateTotal() {
        var total = 0;
        $('.pc-builder-select').each(function() {
            var price = $(this).find('option:selected').data('price');
            if (price) {
                total += parseFloat(price);
            }
        });
        $('#pc-builder-total-amount').text(total.toFixed(2));
    }

    $('.pc-builder-select').change(function() {
        var selectedOption = $(this).find('option:selected');
        var imageUrl = selectedOption.data('image');
        var category = $(this).data('category');
        if (imageUrl) {
            $('#' + category.toLowerCase().replace(/ /g, '-') + '-image').html('<img src="' + imageUrl + '" alt="' + selectedOption.text() + '">');
        } else {
            $('#' + category.toLowerCase().replace(/ /g, '-') + '-image').html('');
        }
        updateTotal();
    });

    $('#pc-builder-add-to-cart').click(function() {
        var products = [];
        $('.pc-builder-select').each(function() {
            var productId = $(this).val();
            if (productId) {
                products.push(productId);
            }
        });
        if (products.length > 0) {
            $.ajax({
                url: pcbuilder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pcbuilder_add_to_cart',
                    products: products
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.cart_url;
                    } else {
                        alert('Failed to add products to cart.');
                    }
                }
            });
        } else {
            alert('Please select at least one product.');
        }
    });

    $('#pc-builder-buy-now').click(function() {
        var products = [];
        $('.pc-builder-select').each(function() {
            var productId = $(this).val();
            if (productId) {
                products.push(productId);
            }
        });
        if (products.length > 0) {
            $.ajax({
                url: pcbuilder_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pcbuilder_add_to_cart',
                    products: products
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.checkout_url;
                    } else {
                        alert('Failed to add products to cart.');
                    }
                }
            });
        } else {
            alert('Please select at least one product.');
        }
    });
});
