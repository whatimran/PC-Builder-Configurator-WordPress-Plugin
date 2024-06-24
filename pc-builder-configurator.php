<?php
/*
Plugin Name: PC Builder Configurator
Description: A custom PC builder/configurator.
Version: 1.4
Author: whatimran
Github: https://github.com/whatimran
*/

function pcbuilder_enqueue_scripts() {
    wp_enqueue_style('pcbuilder-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('pcbuilder-script', plugin_dir_url(__FILE__) . 'script.js', array('jquery'), null, true);
    wp_localize_script('pcbuilder-script', 'pcbuilder_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'pcbuilder_enqueue_scripts');

// menu
function pcbuilder_create_menu() {
    add_menu_page(
        'PC Builder Configurator Settings',
        'PC Builder Configurator',
        'manage_options',
        'pc-builder-configurator',
        'pcbuilder_settings_page',
        'dashicons-admin-generic',
        80
    );
    add_action('admin_init', 'pcbuilder_register_settings');
}
add_action('admin_menu', 'pcbuilder_create_menu');


function pcbuilder_register_settings() {
    register_setting('pcbuilder-settings-group', 'pcbuilder_categories');
}


function pcbuilder_settings_page() {
    ?>
    <div class="wrap">
        <h1>PC Builder Configurator Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('pcbuilder-settings-group'); ?>
            <?php do_settings_sections('pcbuilder-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Categories</th>
                    <td>
                        <textarea name="pcbuilder_categories" rows="10" cols="50" class="large-text"><?php echo esc_attr(get_option('pcbuilder_categories', 'Graphic Card, CPU, Motherboard, RAM, Cooling, SSD, Power supply, Cases, Fans, Wi-Fi')); ?></textarea>
                        <p class="description">Enter categories separated by commas.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// shortcode
function pcbuilder_display() {
    ob_start();
    $categories = explode(',', get_option('pcbuilder_categories', 'Graphic Card, CPU, Motherboard, RAM, Cooling, SSD, Power supply, Cases, Fans, Wi-Fi'));
    ?>
    <div id="pc-builder-wrapper">
        <div id="pc-builder-sidebar">
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li><a href="#<?php echo strtolower(str_replace(' ', '-', trim($category))); ?>"><?php echo trim($category); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div id="pc-builder-content">
            <div id="pc-builder">
                <h2>PC Builder Configurator</h2>
                <form id="pc-builder-form">
                    <?php
                    foreach ($categories as $category) {
                        $category = trim($category);
                        echo '<div class="pc-builder-category" id="' . strtolower(str_replace(' ', '-', $category)) . '">';
                        echo '<label for="' . strtolower(str_replace(' ', '-', $category)) . '">' . $category . '</label>';
                        echo '<select id="' . strtolower(str_replace(' ', '-', $category)) . '" class="pc-builder-select" data-category="' . $category . '">';
                        echo '<option value="">Select ' . $category . '</option>';
                        
                        // query to fetch products
                        $products = get_posts(array(
                            'post_type' => 'product',
                            'posts_per_page' => -1,
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'product_cat',
                                    'field'    => 'slug',
                                    'terms'    => $category,
                                ),
                            ),
                            'meta_query' => array(
                                array(
                                    'key' => '_stock_status',
                                    'value' => 'instock'
                                )
                            )
                        ));
                        
                        foreach ($products as $product) {
                            $product_obj = wc_get_product($product->ID);
                            echo '<option value="' . $product->ID . '" data-price="' . $product_obj->get_price() . '" data-image="' . wp_get_attachment_url($product_obj->get_image_id()) . '">' . $product_obj->get_name() . ' - AED ' . $product_obj->get_price() . '</option>';
                        }
                        echo '</select>';
                        echo '<div class="pc-builder-image" id="' . strtolower(str_replace(' ', '-', $category)) . '-image"></div>';
                        echo '</div>';
                    }
                    ?>
                    <div id="pc-builder-total">
                        <h3>Total: AED <span id="pc-builder-total-amount">0.00</span></h3>
                    </div>
                    <button type="button" id="pc-builder-add-to-cart">Add to Cart</button>
                    <button type="button" id="pc-builder-buy-now">Buy Now</button>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('pc_builder', 'pcbuilder_display');

// AJAX handler
function pcbuilder_add_to_cart() {
    if (isset($_POST['products']) && is_array($_POST['products'])) {
        WC()->cart->empty_cart();
        foreach ($_POST['products'] as $product_id) {
            WC()->cart->add_to_cart($product_id);
        }
        wp_send_json_success(array('cart_url' => wc_get_cart_url(), 'checkout_url' => wc_get_checkout_url()));
    } else {
        wp_send_json_error('No products selected.');
    }
}
add_action('wp_ajax_pcbuilder_add_to_cart', 'pcbuilder_add_to_cart');
add_action('wp_ajax_nopriv_pcbuilder_add_to_cart', 'pcbuilder_add_to_cart');
