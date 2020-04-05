<?php
/* This comments template */

if ( post_password_required() )
    return;
?>
<div id="comments" class="comments-area comments user-reviews">
    <div class="reviews-menu">
        <div class="header-title">
            <span><i class="themeum-moviewuser-review"></i></span> <h3 class="title"><?php _e( 'Reviews','moview' ); ?> <small>( <?php echo get_comment_pages_count(); ?> )</small></h3>
        </div>        
    </div>
    <div class="clearfix"></div>
    <?php if ( have_comments() ) : ?>
        <ul class="comment-list">
            <?php
                wp_list_comments( array(
                    'style'       => 'ul',
                    'short_ping'  => true,
                    'callback' => 'moview_comment',
                    'avatar_size' => 80
                ) );
            ?>
        </ul><!-- .comment-list -->
        <?php
            // Are there comments to navigate through?
            if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
        ?>
        <nav class="navigation comment-navigation" role="navigation">
            <h1 class="screen-reader-text section-heading"><?php esc_html_e( 'Comment navigation', 'moview' ); ?></h1>
            <div class="nav-previous"><?php previous_comments_link( esc_html__( '&larr; Older Comments', 'moview' ) ); ?></div>
            <div class="nav-next"><?php next_comments_link( esc_html__( 'Newer Comments &rarr;', 'moview' ) ); ?></div>
        </nav><!-- .comment-navigation -->
        <?php endif; // Check for comment navigation ?>

        <?php if ( ! comments_open() && get_comments_number() ) : ?>
        <p class="no-comments"><?php esc_html_e( 'Comments are closed.' , 'moview' ); ?></p>
        <?php endif; ?>

    <?php endif; // have_comments() ?>
    <div class="profile-img pull-left">
        <?php 
        $current_user = wp_get_current_user();
        echo get_avatar( $current_user->user_email, 96 );
        ?>
    </div>
    <?php
        $commenter = wp_get_current_commenter();
        $req = get_option( 'require_name_email' );
        $aria_req = ( $req ? " aria-required='true'" : '' );
        $fields =  array(
            'author' => '<div class="col-md-6 input-box"><input id="author" name="author" type="text" placeholder="'. esc_html__( 'Name', 'moview' ) .'" value="" size="30"' . $aria_req . '/></div>',
            'email'  => '<div class="col-md-6 input-box"><input id="email" name="email" type="text" placeholder="'. esc_html__( 'Email', 'moview' ) .'" value="" size="30"' . $aria_req . '/></div>',
            'url'  => '<div class="col-md-12 input-box"><input id="url" name="url" type="text" placeholder="'. esc_html__( 'Website url', 'moview' ) .'" value="" size="30"/></div>',
        );
         
        $comments_args = array(
            'fields' =>  $fields,
            'comment_notes_after'       => '',
            'comment_field'             => '<div class="clearfix"></div><div class="col-md-12 input-box"><textarea id="comment" placeholder="'. esc_html__( 'Comment', 'moview' ) .'" name="comment" aria-required="true"></textarea></div>',
            'label_submit'              => 'Send Comment'
        );
        ob_start();
        comment_form($comments_args);
        $search = array('class="comment-form"','class="form-submit"');
        $replace = array('class="comment-form row"','class="form-submit col-md-12"');
        echo str_replace($search,$replace,ob_get_clean());
    ?>
</div>