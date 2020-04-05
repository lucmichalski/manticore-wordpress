<?php
if(!function_exists('themeum_social_share')):
    
    function themeum_social_share( $post_id ){
        
        $output ='';
        $media_url = '';
        $title = get_the_title( $post_id );
        $permalink = get_permalink( $post_id );

        if( has_post_thumbnail( $post_id ) ){
            $thumb_src =  wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' ); 
            $media_url = $thumb_src[0];
        }

        $output .= '<div class="moview-post-share-social">';
            $output .= '<a href="#" data-type="facebook" data-url="'.esc_url( $permalink ).'" data-title="'.esc_html( $title ).'" data-description="'. esc_html( $title ).'" data-media="'.esc_url( $media_url ).'" class="prettySocial facebook fa fa-facebook"></a>';

            $output .= '<a href="#" data-type="twitter" data-url="'.esc_url( $permalink ).'" data-description="'.esc_html( $title ).'" data-via="" class="prettySocial twitter fa fa-twitter"></a>';

            $output .= '<a href="#" data-type="googleplus" data-url="'.esc_url( $permalink ).'" data-description="'.esc_html( $title ).'" class="prettySocial google fa fa-google-plus"></a>';
            
            $output .= '<a href="#" data-type="pinterest" data-url="'.esc_url( $permalink ).'" data-description="'.esc_html( $title ).'" data-media="'.esc_url( $media_url ).'" class="prettySocial pinterest fa fa-pinterest"></a>';

            $output .= '<a href="#" data-type="linkedin" data-url="'.esc_url( $permalink ).'" data-title="'.esc_html( $title ).'" data-description="'.esc_html( $title ).'" data-via="" data-media="'.esc_url( $media_url ).'" class="prettySocial linkedin fa fa-linkedin"></a>';
        
        $output .= '</div>';

        return $output;
    }

endif;