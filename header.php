<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="milco-header">
  <div class="milco-header__inner">

    <!-- Logo -->
    <a href="<?php echo esc_url( home_url('/') ); ?>" class="milco-logo">
      <?php
        $logo_id = get_theme_mod('custom_logo');
        if ( $logo_id ) {
          echo wp_get_attachment_image( $logo_id, 'full', false, ['class' => 'milco-logo-img'] );
        } else {
          echo '<span class="milco-logo__text">MILCO<small>MADE IN LESOTHO</small></span>';
        }
      ?>
    </a>

    <!-- Primary Nav -->
    <nav class="milco-nav" aria-label="Primary navigation">
      <?php
        wp_nav_menu([
          'theme_location' => 'milco-primary',
          'container'      => false,
          'menu_class'     => 'milco-nav__list',
          'fallback_cb'    => function() {
            // Fallback: show page list if no menu assigned
            echo '<ul class="milco-nav__list">';
            wp_list_pages(['title_li' => '', 'echo' => true]);
            echo '</ul>';
          },
        ]);
      ?>
    </nav>

    <!-- Actions -->
    <div class="milco-header__actions">
      <?php if ( is_user_logged_in() && current_user_can('manage_woocommerce') ) : ?>
      <button class="milco-action-btn milco-bell" aria-label="Notifications">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <span class="milco-notif-dot">0</span>
      </button>
      <?php endif; ?>

      <?php if ( function_exists('WC') ) : ?>
      <a href="<?php echo esc_url( wc_get_cart_url() ); ?>"
         class="milco-action-btn milco-cart" aria-label="Cart">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <?php
          $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
          if ( $count > 0 ) echo '<span class="milco-notif-dot">' . $count . '</span>';
        ?>
      </a>
      <?php endif; ?>
    </div>

  </div>
</header>