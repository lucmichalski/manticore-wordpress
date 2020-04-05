<?php 
    $output = ''; 
    $sub_img = array();
    global $post;
    if(!function_exists('moview_call_sub_header')){
        function moview_call_sub_header(){
            if( moview_options_url('blog-banner','url')){
                $output = 'style="background-image:url('.esc_url( moview_options_url('blog-banner','url')).');background-size: cover;background-position: 50% 50%;padding: 65px 0;"';
                return $output;
            }else{
                if( moview_options('blog-subtitle-bg-color') ){
                    $output = 'style="background-color:'.esc_attr(moview_options('blog-subtitle-bg-color')).';padding: 65px 0;"';
                    return $output;
                }else{
                    $output = 'style="background-color:#ccc; padding: 65px 0;"';
                    return $output;
                }
            }
        }
    }
    
    if( isset($post->post_name) ){
        if(!empty($post->ID)){
            if(function_exists('rwmb_meta')){
                $image_attached = esc_attr(get_post_meta( $post->ID , 'themeum_subtitle_images', true ));
                if(!empty($image_attached)){
                    $sub_img = wp_get_attachment_image_src( $image_attached , 'blog-full'); 
                    $output = 'style="background-image:url('.esc_url($sub_img[0]).');background-size: cover;background-position: 50% 50%;padding: 70px 0;"';
                    if(empty($sub_img[0])){
                        $output = 'style="background-color:'.esc_attr(rwmb_meta("themeum_subtitle_color")).';padding: 70px 0;"';
                        if(rwmb_meta("themeum_subtitle_color") == ''){
                            $output = moview_call_sub_header();
                        }
                    }
                }else{
                    if(rwmb_meta("themeum_subtitle_color") != "" ){
                        $output = 'style="background-color:'.esc_attr(rwmb_meta("themeum_subtitle_color")).';padding: 70px 0;"';
                    }else{
                        $output = moview_call_sub_header();
                    }
                }
            }else{
                $output = moview_call_sub_header();
            } 
        }else{
            $output = moview_call_sub_header();
        }
    }else{
        $output = moview_call_sub_header();
    }

?>
<?php if (!is_front_page()) { ?>

<div class="sub-title" <?php echo $output;?>>
    <div class="container">
        <div class="sub-title-inner">
            <div class="row">
                <div class="col-sm-12">
                    <?php 
                    global $wp_query; 

                    if(isset($wp_query->queried_object->name)){
                        if($wp_query->queried_object->name != ''){
                            if($wp_query->queried_object->name == 'product' ){
                                echo '<h2>'.__('Shop','moview').'</h2>';
                            }else{
                                echo '<h2>'.$wp_query->queried_object->name.'</h2>';    
                            }
                        }else{
                            echo '<h2>'.get_the_title().'</h2>';
                        }
                    }else{
                        if( is_search() ){
                            $first_char = __('Search','moview');
                            echo '<h2>'.$first_char.'</h2>';
                        }else{
                            echo '<h2>'.get_the_title().'</h2>';
                        }
                    }
                    ?>
                    <?php moview_breadcrumbs(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php } ?>