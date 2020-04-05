<?php
/**
 * Single Product tabs
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     5.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$tabs = apply_filters( 'woocommerce_product_tabs', array() );
$i=0;
$j=0;

if ( ! empty( $tabs ) ) { ?>
<div class="woocommerce-tabs">
	<ul class="nav nav-tabs" role="tablist">
		<?php foreach ( $tabs as $key => $tab ) { ?>
		<li class="<?php echo ($i===0) ? 'active': ''; ?>">
			<a href="#tab-<?php echo $key ?>" role="tab" data-toggle="tab"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', $tab['title'], $key ) ?></a>
		</li>
		<?php $i++; ?>
		<?php } ?>
	</ul>

	<div class="tab-content">
		<?php foreach ( $tabs as $key => $tab ) { ?>
		<div class="tab-pane fade <?php echo ($j===0) ? 'active in': ''; ?>" id="tab-<?php echo $key ?>">
			<?php call_user_func( $tab['callback'], $key, $tab ) ?>
		</div>
		<?php $j++; } ?>
	</div>


</div>
<?php } ?>
