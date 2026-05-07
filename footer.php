<?php
/**
 * MILCO Child — footer.php
 */
?>

<footer class="site-footer">
  <div class="milco-footer__inner col-full">

    <?php if ( is_active_sidebar('footer-1') || is_active_sidebar('footer-2') || is_active_sidebar('footer-3') ) : ?>
    <div class="milco-footer__widgets">
      <?php if ( is_active_sidebar('footer-1') ) : ?>
        <div class="milco-footer__col"><?php dynamic_sidebar('footer-1'); ?></div>
      <?php endif; ?>
      <?php if ( is_active_sidebar('footer-2') ) : ?>
        <div class="milco-footer__col"><?php dynamic_sidebar('footer-2'); ?></div>
      <?php endif; ?>
      <?php if ( is_active_sidebar('footer-3') ) : ?>
        <div class="milco-footer__col"><?php dynamic_sidebar('footer-3'); ?></div>
      <?php endif; ?>
      <?php if ( is_active_sidebar('footer-4') ) : ?>
        <div class="milco-footer__col"><?php dynamic_sidebar('footer-4'); ?></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="milco-footer__bottom">
      <p>© <?php echo date('Y'); ?> MILCO. Selling only Made in Lesotho products and services.</p>
    </div>

  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>