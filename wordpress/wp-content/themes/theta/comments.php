<?php
/**
 * The template for displaying Comments
 *
 * The area of the page that contains comments and the comment form.
 *
 */
/*
 * If the current post is protected by a password and the visitor has not yet
 * entered the password we will return early without loading the comments.
 */
 if ( is_singular() ) wp_enqueue_script( "comment-reply" ); 
if ( post_password_required() ) {
	return;
}
?>
<div class="theta-blog-comments">
<div id="comments" class="comments-area theta-comments">
	<?php if ( have_comments() ) : ?>
	<h2 class="comments-title">
			<?php
			$comments_number = get_comments_number();
			if ( '1' === $comments_number ) {
				/* translators: %s: post title */
				printf( _x( 'One Reply to &ldquo;%s&rdquo;', 'comments theta', 'theta' ), get_the_title() );
			} else {
				printf(
					/* translators: 1: number of comments, 2: post title */
					_nx(
						'%1$s Reply to &ldquo;%2$s&rdquo;',
						'%1$s Replies to &ldquo;%2$s&rdquo;',
						$comments_number,
						'comments title',
						'theta'
					),
					number_format_i18n( $comments_number ),
					get_the_title()
				);
			}
			?>
        
	</h2>
    
	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
	<nav id="comment-nav-above" class="navigation comment-navigation" role="navigation">
		<h1 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'theta' ); ?></h1>
		<div class="nav-previous"><?php previous_comments_link( __( '&lt; Older Comments', 'theta' ) ); ?></div>
		<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &gt;', 'theta' ) ); ?></div>
	</nav><!-- #comment-nav-above -->
	<?php endif; // Check for comment navigation. ?>
    
	<ol class="comment-list">
		<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'short_ping' => true,
				'avatar_size'=> 34,
				'callback' => 'theta_better_comments',
			) );
		?>
	</ol><!-- .comment-list -->
    
    
    
	<?php if ( ! comments_open() ) : ?>
	<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'theta' ); ?></p>
	<?php endif; ?>
	<?php endif; // have_comments() ?>
	<?php 
		$req      = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );
		$html_req = ( $req ? " required='required'" : '' );
		$pre_req = ( $req ? "(required)" : '' );
		
		
		comment_form(
    		array(
				'comment_field' => '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" placeholder="'.esc_attr__('Comment...', 'theta').'" aria-required="true" required="required" ></textarea></p>',
				'comment_notes_before' => '',
						
				'fields' => array(
					'author' => '<p class="comment-form-author"><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" maxlength="245" placeholder="'.esc_attr__('Name ', 'theta').$pre_req.'" ' . $aria_req . $html_req . ' /></p>',
					'email'  => '<p class="comment-form-email"><input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" maxlength="100" aria-describedby="email-notes"' . $aria_req . $html_req  . '  placeholder="'.esc_attr__('Email ', 'theta').$pre_req.'" /></p>',
					'url' => '<p class="comment-form-url"><input id="url" name="url" type="text" placeholder="'.esc_attr__('Website', 'theta').'"  value="" size="30" maxlength="200"></p>'
			)			
				
				
    		)
    	); 
	?>
</div></div><!-- #comments -->