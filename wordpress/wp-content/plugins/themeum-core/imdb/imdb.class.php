<?php

ini_set('display_errors', 1);

/**
*
*/
class TM_IMDB
{
	
	/**
	 * Get Movie data as an array. Just enter the IMDB movie id and it will return movie data.
	 * If data is not found, it will return empty array.
	 * 
	 * @param  (string)       $id   IMDB movie id
	 * @return (array|bool)         movie data as an array()
	 */
	public static function get_movie_data( $id ){

		// Global variables
		global $wpdb;

		// Check if movie is exist
		$query_sql = "select post_id from $wpdb->postmeta where meta_key='imdb_id' AND meta_value='{$id}' ";
		$exist_post_id = $wpdb->get_results( $query_sql );

		// If movie is exist, return false
		if (!empty($exist_post_id)) {
			return false;
		}

		$movie_data = array();

		// Get IMDB API key
		$api_key = get_option( 'tm_imdb_api_key' );

		// Check if IMDB API is empty, return empty array
		if (empty($api_key)) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				trigger_error("IMDB API key is not set or Can's access to this API key", E_USER_NOTICE);
			}

			return $movie_data;
		}

		// Get request URL
		$request_url = self::get_url( $id, $api_key );

		// Send request and get movie data
		$the_data = wp_remote_get($request_url, array(
			'timeout'     => 120,
		));

		// Check wp error, if not wp error and data body is set, return movie data as an array
		if (!is_wp_error( $the_data ) && !empty($the_data)) {
			if (isset($the_data['body']) && !empty($the_data['body'])) {
				$movie_data = json_decode($the_data['body'], true);
			}
		}

		return $movie_data;
	}

	/**
	 * Get director data as an array. Just enter the IMDB name id and it will return director data.
	 * If data is not found, it will return empty array.
	 * 
	 * @param  (string)       $id   IMDB name id
	 * @return (array|bool)         director data as an array()
	 */
	public static function get_director_data($id){

		// Global variables
		global $wpdb;

		// Check if director is exist
		$query_sql = "select post_id from $wpdb->postmeta where meta_key='imdb_id' AND meta_value='{$id}' ";
		$exist_post_id = $wpdb->get_results( $query_sql );

		// If director is exist, return false
		if (!empty($exist_post_id)) {
			return false;
		}

		$director_data = array();

		// Get IMDB API key
		$api_key = get_option( 'tm_imdb_api_key' );

		// Check if IMDB API is empty, return empty array
		if (empty($api_key)) {
			if (defined('WP_DEBUG') && WP_DEBUG) {
				trigger_error("IMDB API key is not set or Can's access to this API key", E_USER_NOTICE);
			}

			return $director_data;
		}

		// Get request URL
		$request_url = self::get_url( $id, $api_key, 'director' );

		// Send request and get director data
		$the_data = wp_remote_get($request_url, array(
			'timeout'     => 120,
		));

		// Check wp error, if not wp error and data body is set, return director data as an array
		if (!is_wp_error( $the_data ) && !empty($the_data)) {
			if (isset($the_data['body']) && !empty($the_data['body'])) {
				$director_data = json_decode($the_data['body'], true);
			}
		}

		return $director_data;
	}

	/**
	 * Build IMDB API URL by ID, API key and Type.
	 * 
	 * @param  (string)  $id        IMDB ID
	 * @param  (string)  $api_key   IMDB API key
	 * @param  (string)  $type      Type of ID (Only two value accepted: movie & name)
	 * @return (string)             Retrun the API URL
	 */
	public static function get_url($id, $api_key, $type = 'movie'){
		
		$args = array();

		// Check Type
		if ( $type == 'movie' ) {
			$args['idIMDB'] = $id;
		} else {
			$args['idName'] = $id;
		}
		
		// Set those args
		$args['token'] = $api_key;
		$args['format'] = 'json';
		$args['language'] = 'en-us';
		$args['actors'] = 1;
		$args['actorActress'] = 1;
		$args['biography'] = 1;
		$args['trailers'] = 1;

		// Build API URL
		$api_url = add_query_arg( $args, 'http://www.myapifilms.com/imdb/idIMDB' );

		return $api_url;
	}

	/**
	 * Import movie by passing Movie data
	 * 
	 * @param (array)   $movie_data   Movie data as an array
	 */
	public static function add_movie($movie_data){

		// Prefix of metabox
		$prefix = 'themeum_';

		// Global variables
		global $wpdb;

		// Check if not movie data is empty and set movie data
		$movies = isset($movie_data['data']['movies']) ? $movie_data['data']['movies'] : array();

		// Check movie data is empty, if empty, return an array
		if (!empty($movies)) {

			// foreach every movie data
			foreach ($movies as $movie) {

				// Process Release Date
				$date = '';
				if (isset($movie['releaseDate'])) {
					$year = substr($movie['releaseDate'], 0, 4);
					$month = substr($movie['releaseDate'], 4, 2);
					$day = substr($movie['releaseDate'], 6, 2);

					$date = $year.'-'.$month.'-'.$day;
				}

				// Process Movie Genres
				$genres_id = array();
				if (isset($movie['genres']) && is_array($movie['genres'])) {
					foreach ($movie['genres'] as $genre) {

						// Add Genres to Movie Categories
						$genre_id = self::add_term($genre);

						if ($genre_id) {
							$genres_id[] = $genre_id;
						}
					}
				}

				// Import actors data
				$actors_id = array();
				if (isset($movie['actors']) && !empty($movie['actors'])) {
					foreach ($movie['actors'] as $actor) {

						// Add actor, if not exist
						$act_ids = self::add_celebrity($actor);

						if (!empty($act_ids)) {
							foreach ($act_ids as $act_id) {
								
								if (is_object($act_id)) {
									$actors_id[] = $act_id->post_id;
								} else {
									$actors_id[] = $act_id;
								}
							}
						}
					}
				}

				// Get actor slugs by actor ID
				$actors_slug = array();
				if (!empty($actors_id)) {
					foreach ($actors_id as $actor_id) {
						$actor_post = get_post( $actor_id );

						if (!is_wp_error( $actor_post ) && !empty( $actor_post )) {
							$actors_slug[] = $actor_post->post_name;
						}
					}
				}

				// Import Directors
				$directors_id = array();
				if (isset($movie['directors']) && !empty($movie['directors'])) {
					foreach ($movie['directors'] as $director) {
						$director_imdb_id = $director['id'];

						// Check director ID
						if (!empty($director_imdb_id)) {

							// Get Director data by director IMDB ID
							$director_data = self::get_director_data($director_imdb_id);

							// Check if director data is set and not empty
							if (!empty($director_data) && isset($director_data['data']) && isset($director_data['data']['names'])) {
								foreach ($director_data['data']['names'] as $name_data) {

									// Import director data
									$tmp_director_id = self::add_director($name_data);

									if ($tmp_director_id) {
										$directors_id[] = $tmp_director_id;
									}
									
								}
							} else {

								// Query to check if director is exist at our database
								$query_sql_tmp = "select post_id from $wpdb->postmeta where meta_key='imdb_id' AND meta_value='{$director_imdb_id}' ";
								$exist_director_id = $wpdb->get_results( $query_sql_tmp );

								// Check is director exist
								if (!empty($exist_director_id)) {
									foreach ($exist_director_id as $ext_director_id) {
										// Set director id
										$directors_id[] = $ext_director_id->post_id;

										// Director type meta
										$ext_director_type = get_post_meta( $ext_director_id, "{$prefix}movie_type", true );

										$ext_director_type_array = explode( ", ", $ext_director_type);

										// If "Director" not in director type, set it
										if (!in_array('Director', $ext_director_type_array)) {
											$ext_director_type_array[] = 'Director';

											update_post_meta($ext_director_id, "{$prefix}movie_type", implode(", ", $ext_director_type_array), $ext_director_type);
										}
									}
								}
							}
						}
						
					}
				}

				// Get director slugs by director ID
				$directors_slug = array();
				if (!empty($directors_id)) {
					foreach ($directors_id as $director_id) {
						$director_post = get_post( $director_id );

						if (!is_wp_error( $director_post ) && !empty( $director_post )) {
							$directors_slug[] = $director_post->post_name;
						}
					}
				}

				$movie_info = array();

				// Add movie Country Info
				if (isset($movie['countries']) && !empty($movie['countries'])) {
					$country_title = esc_html__('Countries:', 'themeum-core');

					if (count($movie['countries']) == 1) {
						$country_title = esc_html__('Country:', 'themeum-core');
					}
					$movie_info[] = array(
						"{$prefix}movie_info_type" => $country_title,
						"{$prefix}movie_info_description" => implode(', ', $movie['countries'])
					);
				}

				// Add movie Genres Info
				if (isset($movie['genres']) && !empty($movie['genres'])) {
					$genres_title = esc_html__('Genres:', 'themeum-core');

					if (count($movie['genres']) == 1) {
						$genres_title = esc_html__('Genre:', 'themeum-core');
					}
					$movie_info[] = array(
						"{$prefix}movie_info_type" => $genres_title,
						"{$prefix}movie_info_description" => implode(', ', $movie['genres'])
					);
				}

				// Add movie Languages Info
				if (isset($movie['languages']) && !empty($movie['languages'])) {
					$languages_title = esc_html__('Languages:', 'themeum-core');

					if (count($movie['languages']) == 1) {
						$languages_title = esc_html__('Language:', 'themeum-core');
					}
					$movie_info[] = array(
						"{$prefix}movie_info_type" => $languages_title,
						"{$prefix}movie_info_description" => implode(', ', $movie['languages'])
					);
				}

				// Add movie Metascore Info
				if (isset($movie['metascore']) && !empty($movie['metascore'])) {
	
					$movie_info[] = array(
						"{$prefix}movie_info_type" => esc_html__('Metascore:', 'themeum-core'),
						"{$prefix}movie_info_description" => $movie['metascore']
					);
				}

				// Movie Metadata
				$meta_input = array(
					"{$prefix}movie_release_year" => isset($movie['year']) ? $movie['year'] : '',
					"{$prefix}movie_type" => (isset($movie['genres']) && is_array($movie['genres'])) ? implode(', ', $movie['genres']) : '',
					"{$prefix}movie_length" => (isset($movie['runtime'])) ? $movie['runtime'] : '',
					"{$prefix}release_date" => $date,
					// "{$prefix}movie_actor" => $actors_slug,
					"imdb_id" => (isset($movie['idIMDB'])) ? $movie['idIMDB'] : '',
					"rating" => (isset($movie['rating'])) ? floatval(str_replace(',', '.', $movie['rating'])) : 0,
					"themeum_movie_info" => $movie_info
				);
				
				// Movie post type data
				$post_data = array(
					'post_content' => isset($movie['plot']) ? $movie['plot'] : '',
					'post_title' => isset($movie['title']) ? $movie['title'] : '',
					'post_status' => 'publish',
					'post_type' => 'movie',
					'post_author'  => get_current_user_id(),
					'meta_input' => $meta_input
				);

				// Insert Movie data to Movie post type
				$post_id = wp_insert_post($post_data);

				// Check is post imported or not
				if (!is_wp_error( $post_id ) && !empty($post_id)) {

					// Add actors to movie
					if (!empty($actors_slug)) {
						foreach ($actors_slug as $actor_slug) {
							add_post_meta( $post_id, "{$prefix}movie_actor", $actor_slug );
						}
					}

					// Add directors to movie
					if (!empty($directors_slug)) {
						foreach ($directors_slug as $director_slug) {
							add_post_meta( $post_id, "{$prefix}movie_director", $director_slug );
						}
					}

					// Movie poster URL
					$poster_url = str_replace('UX182_CR0,0,182,268_AL_', '', $movie['urlPoster']);

					// Download Movie poster and upload it to wordpress media manager
					$movie_poster = self::upload_image_by_url($poster_url, sanitize_title_with_dashes($movie['title']), $post_id);

					// Set post Thumbnail to movie
					if ($movie_poster) {
						set_post_thumbnail($post_id, $movie_poster);

						if (isset($movie['trailer']) && isset($movie['trailer']['videoURL']) && !empty($movie['trailer']['videoURL'])) {

							preg_match('/vi\d+/', $movie['trailer']['videoURL'], $matches);

							$video_id = (isset($matches[0]) && !empty($matches[0])) ? $matches[0] : '';

							if (!empty($video_id)) {
								add_post_meta( $post_id, "themeum_movie_trailer_info", array(
									array(
										"{$prefix}video_info_title" => 'Trailer: '.$movie['title'],
										"{$prefix}video_source" => 'imdb',
										"{$prefix}video_link" => $video_id,
										"{$prefix}video_trailer_image" => array($movie_poster)
									)
								) );
							}
						}

						
					}
					
					// Set Movie categories
					wp_set_post_terms( $post_id, $genres_id, 'movie_cat' );

					return array('id' => $post_id, 'movie' => $post_data['post_title']);
				} else {
					return array();
				}


			}
		} else {
			return array();
		}
		
	}

	public static function add_term($term, $taxonomy = 'movie_cat' ) {
		$term_slug = sanitize_title_with_dashes($term);

		$is_exists = term_exists( $term_slug, $taxonomy );

		if ( !empty($is_exists) ) {
			return $is_exists['term_id'];
		} else {
			$the_trem_id = wp_insert_term( $term, $taxonomy, array( 'slug' => $term_slug ) );

			if (!is_wp_error($the_trem_id)) {
				return $the_trem_id;
			} else {
				return false;
			}
		}
	}

	public static function add_celebrity($celebrity_data) {
		global $wpdb;

		$prefix = 'themeum_';

		$query_sql = "select post_id from $wpdb->postmeta where meta_key='imdb_id' AND meta_value='{$celebrity_data['actorId']}' ";
		$exist_celebrity_id = $wpdb->get_results( $query_sql );

		$celebrity_info = array();

		if (isset($celebrity_data['biography'])) {

			if (isset($celebrity_data['biography']['birthName'])) {
				$celebrity_info[] = array(
					'themeum_info_type' => esc_html__('Birth Name:', 'themeum-core'),
					'themeum_info_description' => $celebrity_data['biography']['birthName'],
				);
			}

			if (isset($celebrity_data['biography']['dateOfBirth'])) {
				$celebrity_info[] = array(
					'themeum_info_type' => esc_html__('Date of Birth:', 'themeum-core'),
					'themeum_info_description' => $celebrity_data['biography']['dateOfBirth'],
				);
			}

			if (isset($celebrity_data['biography']['placeOfBirth'])) {
				$celebrity_info[] = array(
					'themeum_info_type' => esc_html__('Place Of Birth:', 'themeum-core'),
					'themeum_info_description' => $celebrity_data['biography']['placeOfBirth'],
				);
			}

			if (isset($celebrity_data['biography']['height'])) {
				$celebrity_info[] = array(
					'themeum_info_type' => esc_html__('Height:', 'themeum-core'),
					'themeum_info_description' => $celebrity_data['biography']['height'],
				);
			}
			
		}

		if (empty($exist_celebrity_id)) {
			$post_data = array(
				'post_content' => isset($celebrity_data['biography']['bio']) ? $celebrity_data['biography']['bio'] : '',
				'post_title' => isset($celebrity_data['actorName']) ? $celebrity_data['actorName'] : '',
				'post_status' => 'publish',
				'post_type' => 'celebrity',
				'post_author'  => get_current_user_id(),
				'meta_input' => array(
					"{$prefix}movie_type" => isset($celebrity_data['biography']['actorActress']) ? $celebrity_data['biography']['actorActress'] : '',
					"imdb_id" => (isset($celebrity_data['actorId'])) ? $celebrity_data['actorId'] : '',
					"celebrity_info" => $celebrity_info
				)
			);

			$post_id = wp_insert_post($post_data);

			if (isset($celebrity_data['biography']['actorActress'])) {
				$type_id = self::add_term($celebrity_data['biography']['actorActress'], 'celebrity_cat');
			}
			

			if (!is_wp_error( $post_id ) && !empty($post_id)) {
				wp_set_post_terms( $post_id, array($type_id), 'celebrity_cat' );

				if (isset($celebrity_data['urlPhoto']) && !empty($celebrity_data['urlPhoto'])) {
					$celebrity_photo_url = $celebrity_data['urlPhoto'];

					$pattern = '/\._V1_U.*?\./i';
					$replacement = '._V1_.';
					$celebrity_photo_url = preg_replace($pattern, $replacement, $celebrity_photo_url);

					$celebrity_photo = self::upload_image_by_url($celebrity_photo_url, sanitize_title_with_dashes($post_data['post_title']), $post_id);



					if ($celebrity_photo) {
						set_post_thumbnail($post_id, $celebrity_photo);
					}
				}

				return array( $post_id );
			} else {
				return array();
			}
		} else {

			$actor_type = isset($celebrity_data['biography']['actorActress']) ? $celebrity_data['biography']['actorActress'] : '';

			if (!empty($exist_celebrity_id) && !empty($actor_type)) {
				foreach ($exist_celebrity_id as $exist_celeb_id) {

					$ext_celebrity_type = get_post_meta( $exist_celeb_id, "{$prefix}movie_type", true );
					$ext_celebrity_type_array = explode( ", ", $ext_celebrity_type);

					if (!in_array($actor_type, $ext_celebrity_type_array)) {
						$ext_celebrity_type_array[] = $actor_type;

						update_post_meta($exist_celeb_id, "{$prefix}movie_type", implode(", ", $ext_celebrity_type_array), $ext_celebrity_type);
					}


				}
			}
			return $exist_celebrity_id;
		}
	}

	public static function add_director($director_data) {

		$prefix = 'themeum_';

		$celebrity_info = array();

	

		if (isset($director_data['birthName'])) {
			$celebrity_info[] = array(
				'themeum_info_type' => esc_html__('Birth Name:', 'themeum-core'),
				'themeum_info_description' => $director_data['birthName'],
			);
		}

		if (isset($director_data['dateOfBirth'])) {
			$celebrity_info[] = array(
				'themeum_info_type' => esc_html__('Date of Birth:', 'themeum-core'),
				'themeum_info_description' => $director_data['dateOfBirth'],
			);
		}

		if (isset($director_data['placeOfBirth'])) {
			$celebrity_info[] = array(
				'themeum_info_type' => esc_html__('Place Of Birth:', 'themeum-core'),
				'themeum_info_description' => $director_data['placeOfBirth'],
			);
		}

		if (isset($director_data['height'])) {
			$celebrity_info[] = array(
				'themeum_info_type' => esc_html__('Height:', 'themeum-core'),
				'themeum_info_description' => $director_data['height'],
			);
		}
			
		

		$post_data = array(
			'post_content' => isset($director_data['bio']) ? $director_data['bio'] : '',
			'post_title' => isset($director_data['name']) ? $director_data['name'] : '',
			'post_status' => 'publish',
			'post_type' => 'celebrity',
			'post_author'  => get_current_user_id(),
			'meta_input' => array(
				"{$prefix}movie_type" => 'Director',
				"imdb_id" => (isset($director_data['idIMDB'])) ? $director_data['idIMDB'] : '',
				"celebrity_info" => $celebrity_info
			)
		);

		$post_id = wp_insert_post($post_data);

		$type_id = self::add_term('Director', 'celebrity_cat');
		

		if (!is_wp_error( $post_id ) && !empty($post_id)) {
			wp_set_post_terms( $post_id, array($type_id), 'celebrity_cat' );

			if (isset($director_data['urlPhoto']) && !empty($director_data['urlPhoto'])) {
				$celebrity_photo_url = $director_data['urlPhoto'];

				$pattern = '/\._V1_U.*?\./i';
				$replacement = '._V1_.';
				$celebrity_photo_url = preg_replace($pattern, $replacement, $celebrity_photo_url);

				$celebrity_photo = self::upload_image_by_url($celebrity_photo_url, sanitize_title_with_dashes($post_data['post_title']), $post_id);



				if ($celebrity_photo) {
					set_post_thumbnail($post_id, $celebrity_photo);
				}
			}

			return $post_id;
		} else {
			return false;
		}

	}

	public static function upload_image_by_url($url, $img_name, $post_id = "0"){
		$file = array();

		$filetype = wp_check_filetype(basename($url));

		$file['name'] = $img_name.'.'.$filetype['ext'];
		$file['tmp_name'] = download_url($url, 600);


		if (!is_wp_error( $file['tmp_name'] )) {
			$img_id = media_handle_sideload($file, $post_id);


			$img_data = wp_generate_attachment_metadata( $img_id,  get_attached_file($img_id));

			wp_update_attachment_metadata( $img_id,  $img_data );

			return $img_id;
		} else {

			@unlink($file['tmp_name']);

			return false;
		}
		
	}
}

include_once( 'admin-page.php' );

add_action( 'wp_ajax_tm_add_movie_from_imdb', 'tm_add_movie_from_imdb_cb' );

function tm_add_movie_from_imdb_cb()
{
	set_time_limit(get_option( 'tm_max_time_out', 300 ));

	$imdb_id = $_POST['imdb_id'];

	$movie_data = TM_IMDB::get_movie_data($imdb_id);

	$output = array();

	if (isset($movie_data['error'])) {
		$output['type'] = 'error';
		$output['message'] = "Error: ".$movie_data['error']['message'];

		echo json_encode($output);
		wp_die();
	} elseif ($movie_data == false){
		$output['type'] = 'error';
		$output['message'] = "Sorry this movie is exist.";
		
		echo json_encode($output);
		wp_die();
	} elseif (empty($movie_data)){
		$output['type'] = 'error';
		$output['message'] = "Error: Failed to make request. Please check your server or API server is down.";

		echo json_encode($output);
		wp_die();
	}

	$movie_saved_data = array();

	if ($movie_data) {
		$movie_saved_data = TM_IMDB::add_movie($movie_data);
	}

	if (!empty($movie_saved_data)) {
		$output['type'] = 'success';
		$output['message'] = "Success: Your Movie <a href='".get_permalink($movie_saved_data['id'])."' target='_blank'><strong>".$movie_saved_data['movie']."</strong></a> (<a href='".get_edit_post_link($movie_saved_data['id'])."' target='_blank'>Edit<span style='font-size: 14px;' class='dashicons dashicons-external'></span></a>) is imported.";

		echo json_encode($output);
		wp_die();
	} else {
		$output['type'] = 'error';
		$output['message'] = "Error: Failed to import this movie. Please try again.";

		echo json_encode($output);
		wp_die();
	}

	wp_die();
}
