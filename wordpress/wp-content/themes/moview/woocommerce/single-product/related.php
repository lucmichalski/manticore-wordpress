<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce_loop;
$woocommerce_loop['columns'] = 3;

if ( $related_products ) : ?>
	<section class="related products">
		<h3 class="related-title"><?php _e( 'Related Products', 'themeum' ); ?></h3>
		<?php woocommerce_product_loop_start(); $count=1; ?>
			<?php foreach ( $related_products as $related_product ) : ?>
				<?php
					if( $count<=3 ){
				 	$post_object = get_post( $related_product->get_id() );
					setup_postdata( $GLOBALS['post'] =& $post_object );
					wc_get_template_part( 'content', 'product' ); ?>
			<?php $count++; }  endforeach; ?>
		<?php woocommerce_product_loop_end(); ?>
	</section>
<?php endif;

wp_reset_postdata();
