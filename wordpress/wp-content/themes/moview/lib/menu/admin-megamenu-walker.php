<?php
class Themeum_Megamenu_Walker extends Walker_Nav_Menu {


	    /**
         * Starts the list before the elements are added.
         *
         * @see Walker_Nav_Menu::start_lvl()
         *
         * @since 3.0.0
         *
         * @param string $output Passed by reference.
         * @param int    $depth  Depth of menu item. Used for padding.
         * @param array  $args   Not used.
         */
        function start_lvl( &$output, $depth = 0, $args = array() ) {}



        /**
         * Ends the list of after the elements are added.
         *
         * @see Walker_Nav_Menu::end_lvl()
         *
         * @since 3.0.0
         *
         * @param string $output Passed by reference.
         * @param int    $depth  Depth of menu item. Used for padding.
         * @param array  $args   Not used.
         */
        function end_lvl( &$output, $depth = 0, $args = array() ) {}


        /**
         * Start the element output.
         *
         * @see Walker_Nav_Menu::start_el()
         * @since 3.0.0
         *
         * @param string $output Passed by reference. Used to append additional content.
         * @param object $item   Menu item data object.
         * @param int    $depth  Depth of menu item. Used for padding.
         * @param array  $args   Not used.
         * @param int    $id     Not used.
         */
        function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 )
        {
            global $_wp_nav_menu_max_depth;
            $_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

            ob_start();
            $item_id = esc_attr( $item->ID );
            $removed_args = array(
                    'action',
                    'customlink-tab',
                    'edit-menu-item',
                    'menu-item',
                    'page-tab',
                    '_wpnonce',
            );

            $original_title = '';
            if ( 'taxonomy' == $item->type ) {
                    $original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
                    if ( is_wp_error( $original_title ) )
                            $original_title = false;
            } elseif ( 'post_type' == $item->type ) {
                    $original_object = get_post( $item->object_id );
                    $original_title = get_the_title( $original_object->ID );
            }

            $classes = array(
                    'menu-item menu-item-depth-' . $depth,
                    'menu-item-' . esc_attr( $item->object ),
                    'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
            );

            $title = $item->title;

            if ( ! empty( $item->_invalid ) ) {
                    $classes[] = 'menu-item-invalid';
                    /* translators: %s: title of menu item which is invalid */
                    $title = sprintf( ( "%s ('Invalid', 'moview')" ), $item->title );
            } elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
                    $classes[] = 'pending';
                    /* translators: %s: title of menu item in draft status */
                    $title = sprintf( ("%s ('Pending', 'moview')"), $item->title );
            }

            $title = ( ! isset( $item->label ) || '' == $item->label ) ? $title : $item->label;

            $submenu_text = '';
            if ( 0 == $depth )
                    $submenu_text = 'style="display: none;"';

            ?>
            <li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
                <dl class="menu-item-bar">
                    <dt class="menu-item-handle">
                        <span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ); ?></span> <span class="is-submenu" <?php echo $submenu_text; ?>><?php esc_html_e( 'sub item','moview' ); ?></span></span>
                        <span class="item-controls">
                            <span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
                            <span class="item-order hide-if-js">
                                <a href="<?php
                                    echo wp_nonce_url(
                                        add_query_arg(
                                            array(
                                                'action' => 'move-up-menu-item',
                                                'menu-item' => $item_id,
                                            ),
                                            remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                        ),
                                        'move-menu_item'
                                    );
                                ?>" class="item-move-up"><abbr title="<?php esc_html_e('Move up', 'moview'); ?>">&#8593;</abbr></a>
                                |
                                <a href="<?php
                                        echo wp_nonce_url(
                                            add_query_arg(
                                                array(
                                                    'action' => 'move-down-menu-item',
                                                    'menu-item' => $item_id,
                                                ),
                                                remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                            ),
                                            'move-menu_item'
                                        );
                                ?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down','moview'); ?>">&#8595;</abbr></a>
                            </span>
                            <a class="item-edit" id="edit-<?php echo $item_id; ?>" title="<?php esc_html_e('Edit Menu Item','moview'); ?>" href="<?php
                                    echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
                            ?>"><?php esc_html_e( 'Edit Menu Item','moview' ); ?></a>
                        </span>
                    </dt>
                </dl>

                <div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
                    <?php if( 'custom' == $item->type ) : ?>
                        <p class="field-url description description-wide">
                            <label for="edit-menu-item-url-<?php echo $item_id; ?>">
                                <?php esc_html_e( 'URL','moview' ); ?><br />
                                <input type="text" id="edit-menu-item-url-<?php echo $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->url ); ?>" />
                            </label>
                        </p>
                    <?php endif; ?>

                    <p class="description description-thin">
                        <label for="edit-menu-item-title-<?php echo $item_id; ?>">
                            <?php esc_html_e( 'Navigation Label','moview' ); ?><br />
                            <input type="text" id="edit-menu-item-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
                        </label>
                    </p>

                    <p class="description description-thin">
                        <label for="edit-menu-item-attr-title-<?php echo $item_id; ?>">
                            <?php esc_html_e( 'Title Attribute','moview' ); ?><br />
                            <input type="text" id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />
                        </label>
                    </p>

                    <p class="field-link-target description">
                        <label for="edit-menu-item-target-<?php echo $item_id; ?>">
                            <input type="checkbox" id="edit-menu-item-target-<?php echo $item_id; ?>" value="_blank" name="menu-item-target[<?php echo $item_id; ?>]"<?php checked( $item->target, '_blank' ); ?> />
                            <?php esc_html_e( 'Open link in a new window/tab','moview' ); ?>
                        </label>
                    </p>

                    <p class="field-css-classes description description-thin">
                        <label for="edit-menu-item-classes-<?php echo $item_id; ?>">
                            <?php esc_html_e( 'CSS Classes (optional)','moview' ); ?><br />
                            <input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
                        </label>
                    </p>

                    <p class="field-xfn description description-thin">
                        <label for="edit-menu-item-xfn-<?php echo $item_id; ?>">
                            <?php esc_html_e( 'Link Relationship (XFN)','moview' ); ?><br />
                            <input type="text" id="edit-menu-item-xfn-<?php echo $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
                        </label>
                    </p>

                    <p class="field-description description description-wide">
                        <label for="edit-menu-item-description-<?php echo $item_id; ?>">
                            <?php esc_html_e( 'Description','moview' ); ?><br />
                            <textarea id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html( $item->description ); // textarea_escaped ?></textarea>
                            <span class="description"><?php esc_html_e('The description will be displayed in the menu if the current theme supports it.','moview'); ?></span>
                        </label>
                    </p>
                    <?php if($depth == 0): ?>
                        <div id="custom-option-<?php echo $item_id; ?>" class="custom-option">
                        
    	                    <p class="field-megamenu description description-thin megamenu">
    	                        <label for="edit-menu-item-megamenu-<?php echo $item_id; ?>">
    	                            <?php esc_html_e( 'Megamenu Settings','moview' ); ?><br />
    	                            <?php
    	                            	if(isset($item->megamenu)){
    	                            		$mn_type = esc_attr( $item->megamenu);
    	                            	}else{
    	                            		$mn_type = 0;
    	                            	}
    	                            ?>
    	                            <input type="radio" id="megamenu0-<?php echo $item_id; ?>" name="menu-item-megamenu[<?php echo $item_id; ?>]" <?php echo checked(1,$mn_type,false); ?> value="1" />ON
    	                            <input type="radio" id="megamenu1-<?php echo $item_id; ?>" name="menu-item-megamenu[<?php echo $item_id; ?>]" <?php echo checked(0,$mn_type,false); ?> value="0" />OFF
    	                        </label>
    	                    </p>

    	                    <p class="field-column description description-thin column-no <?php if($mn_type == 0) echo "display-none"; ?>" >
    	                        <label for="edit-menu-item-column-<?php echo $item_id; ?>">
    	                            <?php esc_html_e( 'Coulumn Number','moview' ); ?><br />
                                    <select id="edit-menu-item-column-<?php echo $item_id; ?>" name="menu-item-column[<?php echo $item_id; ?>]" class="widefat code edit-menu-item-column" >
                                        <option value="1" <?php selected( $item->column , 1 ); ?>>1</option>
                                        <option value="2" <?php selected( $item->column , 2 ); ?>>2</option>
                                        <option value="3" <?php selected( $item->column , 3 ); ?>>3</option>
                                        <option value="4" <?php selected( $item->column , 4 ); ?>>4</option>
                                    </select>
    	                        </label>
    	                    </p>

                        </div>
                    <?php endif; ?>
                    <p class="field-move hide-if-no-js description description-wide">
                        <label>
                            <span><?php esc_html_e( 'Move','moview' ); ?></span>
                            <a href="#" class="menus-move-up"><?php esc_html_e( 'Up one','moview' ); ?></a>
                            <a href="#" class="menus-move-down"><?php esc_html_e( 'Down one','moview' ); ?></a>
                            <a href="#" class="menus-move-left"></a>
                            <a href="#" class="menus-move-right"></a>
                            <a href="#" class="menus-move-top"><?php esc_html_e( 'To the top','moview' ); ?></a>
                        </label>
                    </p>

                    <div class="menu-item-actions description-wide submitbox">
                        <?php if( 'custom' != $item->type && $original_title !== false ) : ?>
                            <p class="link-to-original">
                                    <?php printf( esc_html_e('Original: %s','moview'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
                            </p>
                        <?php endif; ?>
                        <a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
                        echo wp_nonce_url(
                            add_query_arg(
                                    array(
                                            'action' => 'delete-menu-item',
                                            'menu-item' => $item_id,
                                    ),
                                    admin_url( 'nav-menus.php' )
                            ),
                            'delete-menu_item_' . $item_id
                        ); ?>"><?php esc_html_e( 'Remove','moview' ); ?></a> <span class="meta-sep hide-if-no-js"> | </span> <a class="item-cancel submitcancel hide-if-no-js" id="cancel-<?php echo $item_id; ?>" href="<?php echo esc_url( add_query_arg( array( 'edit-menu-item' => $item_id, 'cancel' => time() ), admin_url( 'nav-menus.php' ) ) );
                                ?>#menu-item-settings-<?php echo $item_id; ?>"><?php esc_html_e('Cancel','moview'); ?></a>
                    </div>

                    <input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
                    <input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
                    <input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
                    <input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
                    <input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
                    <input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
                </div><!-- .menu-item-settings-->
                <ul class="menu-item-transport"></ul>
            <?php
            $output .= ob_get_clean();
        }

} // Walker_Nav_Menu_Edit


add_filter('wp_setup_nav_menu_item','add_custom_nav_fields');

add_action( 'wp_update_nav_menu_item', 'update_custom_nav_fields', 10, 3 );


function add_custom_nav_fields( $item )
{
    $item->megamenu 	= get_post_meta( $item->ID, '_menu_item_megamenu', true );
    $item->column 		= get_post_meta( $item->ID, '_menu_item_column', true );
    
    return $item;	    
}


/**
 * Save menu custom fields
*/
function update_custom_nav_fields( $menu_id, $menu_item_db_id, $args )
{
    // Check if element is properly sent
    if ( isset( $_REQUEST['menu-item-megamenu']) && is_array( $_REQUEST['menu-item-megamenu']) ) {
        $megamenu_value = isset($_REQUEST['menu-item-megamenu'][$menu_item_db_id])? $_REQUEST['menu-item-megamenu'][$menu_item_db_id] : 0;
        update_post_meta( $menu_item_db_id, '_menu_item_megamenu', $megamenu_value );
    }

    if ( isset( $_REQUEST['menu-item-column']) && is_array( $_REQUEST['menu-item-column']) ) {
        $column_value = isset($_REQUEST['menu-item-column'][$menu_item_db_id])? $_REQUEST['menu-item-column'][$menu_item_db_id] : '';
        update_post_meta( $menu_item_db_id, '_menu_item_column', $column_value );
    }
}

if(is_admin())
{
	add_action('admin_footer', 'my_custom_navigation_style');

	function my_custom_navigation_style()
	{
	  	echo '<style>
			    .display-none{
			    	display:none;
			    }
	  		</style>';

	  	echo '<script type="text/javascript">
	  			jQuery(document).ready(function($){
	  				$(".megamenu input").on("click",function(){

	  					var mvalue = $(this).val();
	  					var part = $(this).closest(".custom-option").attr("id");

	  					var column = $("#"+part+" p:last-child")

	  					if(mvalue == 1){
	  						column.removeClass("display-none");
	  					}else{
	  						column.addClass("display-none");
	  					}
	  					console.log(mvalue);
	  					
	  				})
	  			})
	  		</script>';

	}
}