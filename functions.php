<?php
/**
 * MILCO Child Theme — functions.php
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* 1. Enqueue parent + child styles */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'storefront-parent', get_template_directory_uri() . '/style.css' );

    wp_enqueue_style(
        'milco-child',
        get_stylesheet_uri(),
        [ 'storefront-parent' ],
        wp_get_theme()->get( 'Version' )
    );

    wp_enqueue_style(
        'milco-components',
        get_stylesheet_directory_uri() . '/assets/css/milco.css',
        [ 'milco-child' ],
        wp_get_theme()->get( 'Version' )
    );

    wp_enqueue_style(
        'milco-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap',
        [],
        null
    );

    wp_enqueue_script(
        'milco-js',
        get_stylesheet_directory_uri() . '/assets/js/milco.js',
        [ 'jquery' ],
        wp_get_theme()->get( 'Version' ),
        true
    );

    wp_enqueue_style(
        'milco-styles',
        get_stylesheet_directory_uri() . '/assets/css/milco.css',
        [],
        filemtime( get_stylesheet_directory() . '/assets/css/milco.css' )
    );

}, 20 );

/* 2. Custom footer credit (TOP LEVEL — not nested!) */
add_action( 'init', function () {
    remove_action( 'storefront_footer', 'storefront_credit', 20 );
    add_action( 'storefront_footer', function () {
        echo '<div class="site-info">© ' . date('Y') . ' MILCO. Selling only Made in Lesotho products and services.</div>';
    }, 20 );
} );

/* 3. WooCommerce support */
add_action( 'after_setup_theme', function () {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
} );

/* 4. Currency symbol */
add_filter( 'woocommerce_currency_symbol', function ( $symbol, $currency ) {
    if ( $currency === 'LSL' ) { return 'M '; }
    return $symbol;
}, 10, 2 );

/* 5. Low-stock thresholds */
add_filter( 'woocommerce_notify_low_stock_amount', function () { return 6; } );
add_filter( 'woocommerce_notify_no_stock_amount',  function () { return 0; } );

add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
    $args['posts_per_page'] = 4;
    $args['columns'] = 4;
    return $args;
});

/* 6. Rename Related products */
add_filter( 'gettext', function ( $translated, $original, $domain ) {
    if ( $domain === 'woocommerce' && $original === 'Related products' ) {
        return 'Customers also bought';
    }
    return $translated;
}, 20, 3 );

/* 7. Related products: 4x4 */
add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
    $args['posts_per_page'] = 4;
    $args['columns']        = 4;
    return $args;
} );

// MILCO: Related Products Shortcode
function milco_related_products_shortcode() {
    if ( ! is_product() ) return '';

    ob_start();

    woocommerce_output_related_products([
        'posts_per_page' => 4,
        'columns' => 4,
    ]);

    return ob_get_clean();
}
add_shortcode('milco_related', 'milco_related_products_shortcode');

/* 8. Shop: 12 per page, 4 per row */
add_filter( 'loop_shop_per_page', function () { return 12; }, 20 );
add_filter( 'loop_shop_columns',  function () { return 4;  }, 20 );

/* 9. "Made in Lesotho" badge */
add_action( 'woocommerce_before_shop_loop_item_title', function () {
    echo '<span class="milco-badge">Made in Lesotho</span>';
}, 9 );

add_action( 'after_setup_theme', function() {
    register_nav_menus( [
        'milco-primary' => __( 'MILCO Primary Nav', 'milco-child' ),
    ] );
} );

/* 10. Low-stock dashboard widget */
add_action( 'wp_dashboard_setup', function () {
    wp_add_dashboard_widget(
        'milco_low_stock',
        'MILCO — Low stock',
        function () {
            $threshold = (int) apply_filters( 'woocommerce_notify_low_stock_amount', 6 );
            $q = new WP_Query( [
                'post_type'      => 'product',
                'posts_per_page' => 10,
                'meta_query'     => [
                    [ 'key' => '_stock', 'value' => $threshold, 'compare' => '<=', 'type' => 'NUMERIC' ],
                    [ 'key' => '_manage_stock', 'value' => 'yes' ],
                ],
            ] );
            if ( ! $q->have_posts() ) {
                echo '<p>All products well stocked. ✅</p>';
                return;
            }
            echo '<ul>';
            while ( $q->have_posts() ) {
                $q->the_post();
                $stock = get_post_meta( get_the_ID(), '_stock', true );
                printf(
                    '<li><a href="%s">%s</a> — %s left</li>',
                    esc_url( get_edit_post_link() ),
                    esc_html( get_the_title() ),
                    esc_html( $stock )
                );
            }
            echo '</ul>';
            wp_reset_postdata();
        }
    );
} );

/* 11. MILCO Notifications — AJAX endpoint */
add_action( 'wp_ajax_milco_get_notifications', 'milco_get_notifications' );
function milco_get_notifications() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $notes = [];

    // Recent orders needing attention (last 48 hours)
    $recent_orders = wc_get_orders( [
        'status'     => [ 'wc-pending', 'wc-on-hold', 'wc-processing' ],
        'limit'      => 5,
        'date_after' => date( 'Y-m-d', strtotime( '-48 hours' ) ),
    ] );

    foreach ( $recent_orders as $order ) {
        $notes[] = [
            'type'    => 'order',
            'message' => 'Order #' . $order->get_id() . ' — ' . ucfirst( $order->get_status() ),
            'link'    => admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ),
            'time'    => human_time_diff( $order->get_date_created()->getTimestamp() ) . ' ago',
        ];
    }

    // Low stock alerts
    $low_stock = new WP_Query( [
        'post_type'      => 'product',
        'posts_per_page' => 5,
        'meta_query'     => [
            [ 'key' => '_manage_stock', 'value' => 'yes' ],
            [ 'key' => '_stock', 'value' => 5, 'compare' => '<=', 'type' => 'NUMERIC' ],
            [ 'key' => '_stock_status', 'value' => 'instock' ],
        ],
    ] );

    foreach ( $low_stock->posts as $product_post ) {
        $stock = get_post_meta( $product_post->ID, '_stock', true );
        $notes[] = [
            'type'    => 'stock',
            'message' => esc_html( $product_post->post_title ) . ' — only ' . $stock . ' left',
            'link'    => admin_url( 'post.php?post=' . $product_post->ID . '&action=edit' ),
            'time'    => 'Low stock',
        ];
    }

    wp_send_json_success( [
        'count' => count( $notes ),
        'notes' => $notes,
    ] );
}

/* 12. Pass AJAX URL to JS */
add_action( 'wp_enqueue_scripts', function() {
    wp_localize_script( 'milco-js', 'milcoData', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'milco_notifications' ),
        'isAdmin' => current_user_can( 'manage_woocommerce' ) ? 'yes' : 'no',
    ] );
}, 25 );