<?php
defined( 'ABSPATH' ) || exit;

if ( $related_products ) : ?>

<section class="related products">

    <div class="milco-related-head" style="text-align:center; margin-bottom:1.5rem;">
        <span style="font-size:11px; letter-spacing:.2em; text-transform:uppercase; color:var(--milco-red); font-weight:700;">
            Hand-picked for you
        </span>

        <h2 style="font-family:'Playfair Display',serif; font-size:1.75rem;">
            <?php esc_html_e( 'Customers also bought', 'milco' ); ?>
        </h2>

        <p style="color:var(--milco-muted); max-width:46ch; margin:.5rem auto 0;">
            Other goods our shoppers loved alongside this one.
        </p>
    </div>

    <?php woocommerce_product_loop_start(); ?>

        <?php foreach ( $related_products as $related_product ) : ?>
            <?php
                $post_object = get_post( $related_product->get_id() );
                setup_postdata( $GLOBALS['post'] =& $post_object );
                wc_get_template_part( 'content', 'product' );
            ?>
        <?php endforeach; ?>

    <?php woocommerce_product_loop_end(); ?>

    <div style="text-align:center; margin-top:2rem;">
        <a class="button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
            Browse the full catalogue
        </a>
    </div>

</section>

<?php endif;

wp_reset_postdata();