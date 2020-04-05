            
                <form role="search" method="get" class="theta-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php
                    printf( '<input type="search" class="theta-search-field" placeholder="%1$s" value="%2$s" name="s" title="%3$s" />',
                        esc_attr_x( 'Search &hellip;', 'placeholder', 'theta' ),
                        get_search_query(),
                        esc_attr_x( 'Search for:', 'label', 'theta' )
                    );
                ?>
                </form>
