<?php
/*
* Plugin Name: Themeum Tweet
* Plugin URI: http://www.themeum.com/item/themeum-tweet
* Author: Themeum
* Author URI: http://www.themeum.com
* License - GNU/GPL V2 or Later
* Description: Themeum Tweet is a Twitter feed display/slider plugin.
* Version: 1.1
*/

if( !class_exists('TwitterAPIExchange') ) include_once( plugin_dir_path( __FILE__ ).'library/TwitterAPIExchange.php' );

class Themeum_Tweet extends WP_Widget {

        /**
        * Register widget with WordPress.
        */
        public function __construct() {
            parent::__construct(
                'themeum_tweet', // Base ID
                'Themeum Tweet', // Name
                array( 'description' => __( 'Twitter feed display widget'), ) // Args
                );
        }

        /*
		* Prepare feeds
		*/			
		private static function prepareTweet( $string )
        {
			//Url
           $pattern = '/((ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?)/i';
           $replacement = '<a target="_blank" class="tweet_url" href="$1">$1</a>';
           $string = preg_replace($pattern, $replacement, $string);

			//Search
           $pattern = '/[\#]+([A-Za-z0-9-_]+)/i';
           $replacement = ' <a target="_blank" class="tweet_search" href="https://twitter.com/search?q=$1">#$1</a>';
           $string = preg_replace($pattern, $replacement, $string);

			//Mention
           $pattern = '/\s[\@]+([A-Za-z0-9-_]+)/i';
           $replacement = ' <a target="_blank" class="tweet_mention" href="http://twitter.com/$1">@$1</a>';
           $string = preg_replace($pattern, $replacement, $string);	

           return $string;
       }


		//Function for converting time
       private static function timeago( $time )
       {
           return human_time_diff( strtotime( $time ), current_time( 'timestamp' ) );
       }

       private static function getTweetsData( $params )
       {

        $settings = array(
            'consumer_key'                  => get_option('oauth_consumer_key'),
            'consumer_secret'               => get_option('consumer_secret'),
            'oauth_access_token'            => get_option('oauth_access_token'),
            'oauth_access_token_secret'     => get_option('oauth_access_token_secret')
            );

        if( empty($settings['consumer_key']) )
        {
            return NULL;
        }           
        elseif( empty($settings['consumer_secret']) )
        {
            return NULL;
        } 
        elseif( empty($settings['oauth_access_token']) )
        {
            return NULL;
        } 
        elseif( empty($settings['oauth_access_token_secret']) )
        {
            return NULL;
        }

        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
        $getfield = '?include_entities=true&include_rts=true&screen_name='.$params['username'].'&count=15';
        $requestMethod = 'GET';

        $api = new TwitterAPIExchange($settings);
        return $api->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();

    }

    public static function getTweets( $username = 'themeum', $count = 5, $layout = 'list', $id = 'themeum-tweet', $avatar = 0, $tweet_time = 0, $tweet_src = 0, $follow_us = 0 )
    {

        $themeum_tweet = array( 
            'username' => $username,
            'count'    => $count
            );
        
        $twitter_data = self::getTweetsData($themeum_tweet);

        if( $twitter_data )
        {
            //Cache path
            $cache_path =  plugin_dir_path( __FILE__ ).'cache';

            if(!file_exists($cache_path)) mkdir($cache_path);
            
            $themeum_tweet = $id . '.cache';
            $exp_time = 15; //minute
            $time = time();
            
            if(file_exists($cache_path.'/'.$themeum_tweet)) {
                $filemtime = filemtime($cache_path.'/'.$themeum_tweet) + ($exp_time*60);
                if( ($filemtime < $time) ) {
                    file_put_contents($cache_path.'/'.$themeum_tweet, $twitter_data);
                } else {
                    $twitter_data = file_get_contents($cache_path.'/'.$themeum_tweet);
                }
            } else {
                file_put_contents($cache_path.'/'.$themeum_tweet, $twitter_data);
            }
            
            $tweets = json_decode($twitter_data);


            if($layout=='list') { ?>



                <div id="widget-<?php echo $id; ?>" class="themeum-tweet">
                    <div class="list-layout">
                        <?php 
                        $num = 1;
                        foreach($tweets as $i=>$value){
                            if( $count >= $num ){

                                if($i % 2){
                                    $row_class = ' themeum-tweet-even';
                                }else{
                                    $row_class = ' themeum-tweet-odd';
                                }
                            ?>

                                <div class="themeum-tweet-item<?php echo $row_class; ?>">
                                    
                                    <?php if ( $avatar) { ?>
                                        <a target="_blank" href="http://twitter.com/<?php echo $value->user->screen_name; ?>">
                                            <img class="tweet-avatar" src="<?php echo $value->user->profile_image_url; ?>" alt="<?php echo $value->user->name; ?>" title="<?php echo $value->user->name; ?>" />
                                        </a>
                                    <?php } ?> 

                                    <?php if( $tweet_time || $tweet_src ) { ?>
                                        <div class="tweet-meta">

                                            <?php if( $tweet_time ) { ?>
                                                <div class="tweet-date">
                                                    <a target="_blank" href="http://twitter.com/<?php echo $value->user->screen_name; ?>/status/<?php echo $value->id_str; ?>"><?php echo _('about') . ' ' . self::timeago( $value->created_at ) . ' ' . _('ago'); ?></a>
                                                </div>
                                            <?php } ?>

                                            <?php if( $tweet_src ) { ?>
                                                <div class="tweet-source">
                                                    <?php echo _('from') . ' ' . $value->source; ?>
                                                </div>
                                            <?php } ?>

                                        </div>
                                    <?php } ?>

                                    <?php echo self::prepareTweet($value->text); ?>

                                </div><!--/.themeum-tweet-item-->
                            <?php 
                                 }
                             $num++;
                            }
                         //close ?>

                    </div><!--/.list-layout-->

                    <?php if ($follow_us) { ?>
                        <a class="followme" target="_blank" href="http://twitter.com/<?php echo $value->user->screen_name; ?>"><?php echo _('Follow') . ' ' . $value->user->name . ' ' . _('on Twitter');?></a>
                    <?php } ?>

                </div><!--/.themeum-tweet-->

            <?php 
            }
            else
            {
                ?>
                    <div id="<?php echo $id; ?>" class="themeum-tweet carousel slide" data-ride="carousel" data-interval="5000">

                        <!-- Indicators -->
                        <ol class="carousel-indicators">
                            <?php 
                                $var = count( $tweets );
                                $i = 1;
                                foreach ($tweets as $value) {
                                    if($count >= $i){
                                        if($i==1){
                                            echo '<li data-target="#'. $id .'" data-slide-to="0" class="active"></li>';
                                        }else{
                                            echo '<li data-target="#'. $id .'" data-slide-to="'.($i-1).'"></li>';
                                        }
                                        $i++;    
                                    }
                                }
                            ?>
                        </ol>


                        <div class="carousel-inner">
                            <?php 
                                $num = 1;
                                foreach( $tweets as $i=>$value ){

                                if( $count >= $num ){
                                    if($i % 2){
                                        $row_class = ' themeum-tweet-even';
                                    }else{
                                        $row_class = ' themeum-tweet-odd';
                                    }
                                ?>

                                    <div class="themeum-tweet-item item<?php echo $row_class; ?><?php echo ($i==0) ? ' active' : ''; ?>">
                                        <div class="themeum-tweet-items">
                                            
                                            <?php if ( $avatar) { ?>
                                                <a target="_blank" href="http://twitter.com/<?php echo $value->user->screen_name; ?>">
                                                    <img class="tweet-avatar" src="<?php echo $value->user->profile_image_url; ?>" alt="<?php echo $value->user->name; ?>" title="<?php echo $value->user->name; ?>" />
                                                </a>
                                            <?php } ?> 

                                            <p class="tweet-username">
                                                <a target="_blank" href="http://twitter.com/<?php echo $value->user->screen_name; ?>">
                                                    <?php echo $value->user->screen_name; ?>
                                                </a>
                                            </p>

                                            <?php if( $tweet_time || $tweet_src ) { ?>
                                                <div class="tweet-meta">

                                                    <?php if( $tweet_time ) { ?>
                                                        <div class="tweet-date">
                                                            <a target="_blank" href="http://twitter.com/<?php echo $value->user->screen_name; ?>/status/<?php echo $value->id_str; ?>"><?php echo _('about') . ' ' . self::timeago( $value->created_at ) . ' ' . _('ago'); ?></a>
                                                        </div>
                                                    <?php } ?>

                                                    <?php if( $tweet_src ) { ?>
                                                        <div class="tweet-source">
                                                            <?php echo _('from') . ' ' . $value->source; ?>
                                                        </div>
                                                    <?php } ?>

                                                </div>
                                            <?php } ?>
                                            <div class="themeum-tweet-text">  
                                                <p class="tweet-content">
                                                    <?php echo self::prepareTweet($value->text); ?>
                                                </p>

                                                <?php if ($follow_us) { ?>
                                                    <a class="followme" target="_blank" href="http://twitter.com/<?php echo $value->user->screen_name; ?>"><?php echo _('Follow') . ' ' . $value->user->name; ?></a>
                                                <?php } ?>
                                            </div>  
                                        </div><!--/.themeum-tweet-items-->
                                    </div><!--/.themeum-tweet-item-->
                            <?php 
                                    } 
                                $num++; 
                                }
                            ?>

                        </div><!--/.scroller-layout-->

                    </div><!--/.themeum-tweet-->
                <?php
            }
        } 
        else
        {
            ?>
                <p class="themeum-tweet-alert"><strong>Wrong Twitter API Settings.</strong><br />Please check Themeum Tweet Settings under Plugins menu.</p>
            <?php
        }   

    }


    /**
    * Front-end display of widget.
    *
    * @see WP_Widget::widget()
    *
    * @param array $args     Widget arguments.
    * @param array $instance Saved values from database.
    */
    public function widget( $args, $instance )
    {
        extract( $args );

        $title                      = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
        $username                   = $instance['username'];
        $count                      = $instance['count'];
        $avatar                     = $instance['avatar'];
        $layout                     = $instance['layout'];
        $tweet_time                 = $instance['tweet_time'];
        $tweet_src                  = $instance['tweet_src'];
        $follow_us                  = $instance['follow_us'];

        echo $before_widget;
        echo $before_title . $title . $after_title;

        $this->getTweets($username, $count, $layout, $widget_id, $avatar, $tweet_time, $tweet_src, $follow_us);

        echo $after_widget;
    }

    /**
    * Sanitize widget form values as they are saved.
    *
    * @see WP_Widget::update()
    *
    * @param array $new_instance Values just sent to be saved.
    * @param array $old_instance Previously saved values from database.
    *
    * @return array Updated safe values to be saved.
    */

    public function update( $new_instance, $old_instance )
    {

        $instance 					        = array();
        $instance['title'] 			        = strip_tags( $new_instance['title'] );
        $instance['username']		        = strip_tags( $new_instance['username'] );
        $instance['count']                  = strip_tags( $new_instance['count'] );
        $instance['layout'] 			    = $new_instance['layout'];
        $instance['avatar'] 	            = $new_instance['avatar'];
        $instance['tweet_time']             = $new_instance['tweet_time'];
        $instance['tweet_src']              = $new_instance['tweet_src'];
        $instance['follow_us']              = $new_instance['follow_us'];

        //Delete all twitter caches
        $caches = glob( plugin_dir_path( __FILE__ ).'cache/*.cache' );
        foreach($caches as $cache)
        {
            unlink( $cache );
        }
        
        return $instance;
    }


    /**
    * Back-end widget form.
    *
    * @see WP_Widget::form()
    *
    * @param array $instance Previously saved values from database.
    */

    public function form( $instance )
    {

        $default = array(   
            'title'                 => 'Themeum Tweet',
            'username'              => 'themeum',
            'count'                 => '4',
            'layout'                => 'list',
            'tweet_time'            => 1,
            'tweet_src'             => 1,
            'follow_us'             => 1
            );

        $instance = wp_parse_args((array) $instance, $default );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e( 'Username:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo $instance['username']; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Count:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo $instance['count']; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php _e( 'Layout:' ); ?></label> 
            <select class="widefat" id="<?php echo $this->get_field_id( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>" >
                <option value="list" <?php if ( $instance['layout'] == 'list' ){ echo 'selected="selected"';} ?> >List</option>
                <option value="scroller" <?php if ( $instance['layout'] == 'scroller' ) {echo 'selected="selected"';} ?> >Scroller</option>
            </select>
        </p>
        <p>
            <input type="checkbox" class="" id="<?php echo $this->get_field_id('avatar'); ?>" name="<?php echo $this->get_field_name('avatar'); ?>" value="1" <?php checked( $instance['avatar'],1 ); ?> />
            <label for="<?php echo $this->get_field_id('avatar'); ?>"><?php _e('Show Avatar'); ?></label>
        </p>
        <p>
            <input type="checkbox" class="" id="<?php echo $this->get_field_id('tweet_time'); ?>" name="<?php echo $this->get_field_name('tweet_time'); ?>" value="1" <?php checked( $instance['tweet_time'],1 ); ?> />
            <label for="<?php echo $this->get_field_id('tweet_time'); ?>"><?php _e('Tweet Time'); ?></label>
        </p>
        <p>
            <input type="checkbox" class="" id="<?php echo $this->get_field_id('tweet_src'); ?>" name="<?php echo $this->get_field_name('tweet_src'); ?>" value="1" <?php checked( $instance['tweet_src'],1 ); ?> />
            <label for="<?php echo $this->get_field_id('tweet_src'); ?>"><?php _e('Tweet Source'); ?></label>
        </p>
        <p>
            <input type="checkbox" class="" id="<?php echo $this->get_field_id('follow_us'); ?>" name="<?php echo $this->get_field_name('follow_us'); ?>" value="1" <?php checked( $instance['follow_us'],1 ); ?> />
            <label for="<?php echo $this->get_field_id('follow_us'); ?>"><?php _e('Follow Link'); ?></label>
        </p>
        <?php
    }

} // End of Classy

// register Themeum Tweet widget
add_action( 'widgets_init', create_function( '', 'register_widget( "themeum_tweet" );' ) );


//Shortcode
add_shortcode('themeum_tweet', function( $atts, $content = null )
{
    extract( shortcode_atts(
        array(
            'username'      => 'themeum',
            'count'         => '',
            'id'            => 'themeum-tweet',
            'avatar'        => 0,
            'layout'        => 'scroller',
            'tweet_time'    => 0,
            'tweet_src'     => 0,
            'follow_us'     => 0
        ), $atts ) );                          

    Themeum_Tweet::getTweets( $username, $count, $layout, $id, $avatar, $tweet_time, $tweet_src, $follow_us );

});



add_action( 'wp_enqueue_scripts', 'themeum_tweet_style' );

function themeum_tweet_style()
{
    wp_enqueue_style('themeum-tweet',plugins_url('assets/css/themeum-tweet.css',__FILE__));
}

if(is_admin())
{
    add_action( 'admin_init', 'register_themeum_tweet_style' );
    add_action( 'admin_menu', 'register_themeum_tweet_menu' );
}

function register_themeum_tweet_style()
{
    wp_register_style('themeum-tweet-admin',plugins_url('assets/css/admin.css',__FILE__));
}

function register_themeum_tweet_menu()
{
    $page = add_plugins_page('Themeum Tweet Settings', 'Themeum Tweet Settings', 'manage_options', 'themeum-tweet-settings','themeum_tweet_settings');
    add_action('admin_print_styles-'.$page,'enqueue_themeum_tweet_style');
    add_action( 'admin_init', 'register_tweet_settings', 1 );
}

function enqueue_themeum_tweet_style()
{
    wp_enqueue_style('themeum-tweet-admin');
}

function register_tweet_settings()
{
    //register our settings
    register_setting( 'tweet_ops', 'oauth_consumer_key' );
    register_setting( 'tweet_ops', 'consumer_secret' );
    register_setting( 'tweet_ops', 'oauth_access_token' );
    register_setting( 'tweet_ops', 'oauth_access_token_secret' );
}

function themeum_tweet_settings()
{
    ?>
    <form id="themeum-tweet-options" role="form" method="post" action="options.php">

        <?php settings_fields('tweet_ops'); ?>
        <?php do_settings_sections('tweet_ops'); ?>

        <h2><?php _e('Twitter API Settings', 'themeum'); ?></h2>

        <div class="form-group">
            <label><?php _e('Twitter API Help', 'themeum'); ?></label>
            <a target="_blank" class="button button-primary" href="https://code.google.com/p/socialauth-android/wiki/Twitter">Step by Step Guide to Get Twitter consumer key and secrets</a>
        </div>    
    
        <div class="form-group">
            <label for="oauth_consumer_key"><?php _e('Consumer Key', 'themeum'); ?></label>
            <input type="text" class="form-control" id="oauth_consumer_key" name="oauth_consumer_key" value="<?php echo get_option('oauth_consumer_key'); ?>" />
        </div>
        <div class="form-group">
            <label for="consumer_secret"><?php _e('Consumer Secret', 'themeum'); ?></label>
            <input type="text" class="form-control" id="consumer_secret" name="consumer_secret" value="<?php echo get_option('consumer_secret'); ?>" />
        </div>
        <div class="form-group">
            <label for="oauth_access_token"><?php _e('Access Token', 'themeum'); ?></label>
            <input type="text" class="form-control" id="oauth_access_token" name="oauth_access_token" value="<?php echo get_option('oauth_access_token'); ?>" />
        </div>
        <div class="form-group">
            <label for="oauth_access_token_secret"><?php _e('Access Token Secret', 'themeum'); ?></label>
            <input type="text" class="form-control" id="oauth_access_token_secret" name="oauth_access_token_secret" value="<?php echo get_option('oauth_access_token_secret'); ?>" />
        </div>
        <?php submit_button(); ?>
    </form>
    <?php
}

include_once( 'vc-tweet.php' );
