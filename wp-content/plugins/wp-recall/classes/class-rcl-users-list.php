<?php

class Rcl_Users_List extends Rcl_Users_Query {

	public $id;
	public $template = 'rows';
	public $usergroup = '';
	public $group_id = '';
	public $only = false;
	public $filters = 0;
	public $search_form = 1;
	public $data;
	public $orderby = 'time_action';
	public $add_uri;
	public $width;

	function __construct( $args = array() ) {

		if ( ! $args ) {
			$args = array();
		}

		if ( isset( $args['inpage'] ) ) {
			$args['number'] = $args['inpage'];
		}

		if ( isset( $args['include'] ) ) {
			$args['ID__in'] = array_map( 'trim', explode( ',', $args['include'] ) );
		}

		if ( isset( $args['exclude'] ) ) {
			$args['ID__not_in'] = array_map( 'trim', explode( ',', $args['exclude'] ) );
		}

		parent::__construct();

		if ( $args ) {
			$this->init_properties( $args );
		}

		$args['select'] = array(
			'ID',
			'display_name',
			'user_nicename'
		);

		$this->parse( $args );

		$this->data = ( $this->data ) ? array_map( 'trim', explode( ',', $this->data ) ) : array();

		if ( isset( $_GET['usergroup'] ) ) {
			$this->usergroup = sanitize_key( $_GET['usergroup'] );
		}

		if ( $this->filters ) {

			if ( isset( $_GET['users-filter'] ) ) {
				$this->orderby = sanitize_key( $_GET['users-filter'] );
			}

			if ( isset( $_GET['users-order'] ) ) {
				$this->query['order'] = sanitize_key( $_GET['users-order'] );
			}

			add_filter( 'rcl_users_query', array( $this, 'add_query_search' ) );
		}

		$this->add_uri['users-filter'] = $this->query['order'];

		add_filter( 'rcl_users', array( $this, 'add_avatar_data' ) );

		if ( $this->data( 'description' ) ) {
			add_filter( 'rcl_users', array( $this, 'add_descriptions' ) );
		}

		if ( $this->data( 'profile_fields' ) ) {
			add_filter( 'rcl_users', array( $this, 'add_profile_fields' ) );
		}

		if ( $this->usergroup ) {
			add_filter( 'rcl_users_query', array( $this, 'add_query_usergroup' ) );
		}

		if ( $this->data( 'user_registered' ) || $this->orderby == 'user_registered' ) {
			add_filter( 'rcl_users_query', array( $this, 'add_query_user_registered' ) );
		}

		//получаем данные рейитнга
		if ( $this->orderby == 'rating_total' ) {
			add_filter( 'rcl_users_query', array( $this, 'add_query_rating_total' ) );
		} else if ( $this->data( 'rating_total' ) ) {
			add_filter( 'rcl_users', array( $this, 'add_rating_total' ) );
		}

		//считаем публикации
		if ( $this->orderby == 'posts_count' ) {
			add_filter( 'rcl_users_query', array( $this, 'add_query_posts_count' ) );
		} else if ( $this->data( 'posts_count' ) ) {
			add_filter( 'rcl_users', array( $this, 'add_posts_count' ) );
		}

		//считаем комментарии
		if ( $this->orderby == 'comments_count' ) {
			add_filter( 'rcl_users_query', array( $this, 'add_query_comments_count' ) );
		} else if ( $this->data( 'comments_count' ) ) {
			add_filter( 'rcl_users', array( $this, 'add_comments_count' ) );
		}

		if ( $this->orderby == 'time_action' ) {
			add_filter( 'rcl_users_query', array( $this, 'add_query_time_action' ) );
		} else {
			add_filter( 'rcl_users', array( $this, 'add_time_action' ) );
		}

		if ( $this->only == 'action_users' ) {
			add_filter( 'rcl_users_query', array( $this, 'add_query_only_actions_users' ) );
		}

		$this->query = apply_filters( 'rcl_users_query', $this->query );
	}

	function remove_filters() {
		remove_all_filters( 'rcl_users_query' );
		remove_all_filters( 'rcl_users' );
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	function setup_userdata( $userdata ) {
		global $rcl_user;
		$rcl_user = ( object ) $userdata;

		return $rcl_user;
	}

	function data( $needle ) {
		if ( ! $this->data ) {
			return false;
		}
		$key = array_search( $needle, $this->data );

		return ( false !== $key ) ? true : false;
	}

	function get_users() {

		return apply_filters( 'rcl_users', $this->get_data() );
	}

	function search_request() {
		global $user_LK;

		$rqst = '';

		if ( isset( $_GET['usergroup'] ) || isset( $_GET['search-user'] ) || $user_LK ) {
			$rqst = array();
			foreach ( $_GET as $k => $v ) {
				if ( $k == 'rcl-page' || $k == 'users-filter' ) {
					continue;
				}
				$rqst[ $k ] = esc_html( $k ) . '=' . esc_html( $v );
			}
		}

		if ( $this->add_uri ) {
			foreach ( $this->add_uri as $k => $v ) {
				$rqst[ $k ] = $k . '=' . $v;
			}
		}

		return apply_filters( 'rcl_users_uri', $rqst );
	}

	function add_query_only_actions_users( $query ) {

		$timeout          = intval( rcl_get_option( 'timeout', 10 ) );
		$query['where'][] = "actions.time_action > date_sub('" . current_time( 'mysql' ) . "', interval $timeout minute)";

		if ( $this->orderby != 'time_action' ) {
			$query['join'][] = "RIGHT JOIN " . RCL_PREF . "user_action AS actions ON wp_users.ID = actions.user";
		}

		return $query;
	}

	//добавляем данные полей профиля, если перечислены через usergroup
	function add_query_usergroup( $query ) {
		global $wpdb;

		$usergroup = explode( '|', $this->usergroup );
		foreach ( $usergroup as $k => $filt ) {
			$f        = explode( ':', $filt );
			$uniq     = uniqid( 'meta_' );
			$search   = sanitize_text_field( $f[1] );
			$meta_key = sanitize_key( str_replace( '-', '_', $f[0] ) );

			$query['join'][] = "INNER JOIN $wpdb->usermeta AS $uniq ON wp_users.ID=$uniq.user_id";
			//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$query['where'][] = $wpdb->prepare( "($uniq.meta_key=%s AND $uniq.meta_value LIKE %s)", $meta_key, '%' . $wpdb->esc_like( $search ) . '%' );
		}

		return $query;
	}

	function add_profile_fields( $users ) {
		global $wpdb;

		$profile_fields = rcl_get_profile_fields();

		$profile_fields = apply_filters( 'rcl_userslist_custom_fields', $profile_fields );

		if ( ! $profile_fields ) {
			return $users;
		}

		$profile_fields = stripslashes_deep( $profile_fields );

		$slugs  = array();
		$fields = array();

		foreach ( $profile_fields as $custom_field ) {
			$custom_field = apply_filters( 'rcl_userslist_custom_field', $custom_field );
			if ( ! $custom_field ) {
				continue;
			}

			if ( isset( $field['req'] ) && $field['req'] ) {
				$field['public_value'] = $field['req'];
			}

			if ( isset( $custom_field['public_value'] ) && $custom_field['public_value'] == 1 ) {
				$fields[] = $custom_field;
				$slugs[]  = sanitize_key( $custom_field['slug'] );
			}
		}

		if ( ! $fields ) {
			return $users;
		}

		$ids = $this->get_users_ids( $users );

		if ( ! $ids ) {
			return $users;
		}

		$fielddata = array();
		foreach ( $fields as $k => $field ) {

			$fielddata[ $field['slug'] ]['title'] = $field['title'];
			$fielddata[ $field['slug'] ]['type']  = $field['type'];

			if ( isset( $field['filter'] ) ) {
				$fielddata[ $field['slug'] ]['filter'] = $field['filter'];
			}
		}

		$query = "SELECT meta_key,meta_value, user_id AS ID "
		         . "FROM $wpdb->usermeta "
		         . "WHERE user_id IN (" . implode( ',', $ids ) . ") AND meta_key IN ('" . implode( "','", $slugs ) . "')";
		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$metas = $wpdb->get_results( $query );

		if ( ! $metas ) {
			return $users;
		}

		$newmetas = array();
		foreach ( $metas as $k => $meta ) {
			$newmetas[ $meta->ID ]['ID']                            = $meta->ID;
			$newmetas[ $meta->ID ]['profile_fields'][ $k ]['slug']  = $meta->meta_key;
			$newmetas[ $meta->ID ]['profile_fields'][ $k ]['value'] = maybe_unserialize( $meta->meta_value );
			$newmetas[ $meta->ID ]['profile_fields'][ $k ]['title'] = $fielddata[ $meta->meta_key ]['title'];
			$newmetas[ $meta->ID ]['profile_fields'][ $k ]['type']  = $fielddata[ $meta->meta_key ]['type'];

			if ( isset( $fielddata[ $meta->meta_key ]['filter'] ) ) {
				$newmetas[ $meta->ID ]['profile_fields'][ $k ]['filter'] = $fielddata[ $meta->meta_key ]['filter'];
			}

		}

		if ( $newmetas ) {
			$users = $this->merge_objects( $users, $newmetas, 'profile_fields' );
		}

		return $users;
	}

	function add_query_user_registered( $query ) {

		$query['select'][] = "wp_users.user_registered";

		if ( $this->orderby ) {
			$query['orderby'] = "wp_users.user_registered";
		}

		return $query;
	}

	//добавляем выборку данных активности пользователей в основной запрос
	function add_query_time_action( $query ) {

		$query['select'][] = "actions.time_action";
		$query['orderby']  = "actions.time_action";

		$query['join'][] = "RIGHT JOIN " . RCL_PREF . "user_action AS actions ON wp_users.ID = actions.user";

		return $query;
	}

	//добавление данных активности пользователей после основного запроса
	function add_time_action( $users ) {
		global $wpdb;

		$ids = $this->get_users_ids( $users );

		if ( $ids ) {

			$query = "SELECT time_action, user AS ID "
			         . "FROM " . RCL_PREF . "user_action "
			         . "WHERE user IN (" . implode( ',', $ids ) . ")";

			//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$posts = $wpdb->get_results( $query );

			if ( $posts ) {
				$users = $this->merge_objects( $users, $posts, 'time_action' );
			}
		}

		return $users;
	}

	//добавляем выборку данных постов в основной запрос
	function add_query_posts_count( $query ) {
		global $wpdb;

		$query['select'][] = "posts.posts_count";
		$query['orderby']  = "posts.posts_count";

		$query['join'][] = "INNER JOIN (SELECT COUNT(post_author) AS posts_count, post_author "
		                   . "FROM $wpdb->posts "
		                   . "WHERE post_status IN ('publish', 'private') AND post_type NOT IN ('page','nav_menu_item') "
		                   . "GROUP BY post_author) posts "
		                   . "ON wp_users.ID = posts.post_author";

		return $query;
	}

	//добавление данных публикаций после основного запроса
	function add_posts_count( $users ) {
		global $wpdb;

		if ( ! $users ) {
			return null;
		}

		$ids = $this->get_users_ids( $users );

		if ( ! $ids ) {
			return $users;
		}

		$query = "SELECT COUNT(post_author) AS posts_count, post_author AS ID "
		         . "FROM $wpdb->posts "
		         . "WHERE post_status IN ('publish', 'private') AND post_type NOT IN ('page','nav_menu_item') AND post_author IN (" . implode( ',', $ids ) . ") "
		         . "GROUP BY post_author";
		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$posts = $wpdb->get_results( $query );

		if ( $posts ) {
			$users = $this->merge_objects( $users, $posts, 'posts_count' );
		}

		return $users;
	}

	//добавляем выборку данных комментариев в основной запрос
	function add_query_comments_count( $query ) {
		global $wpdb;

		$query['select'][] = "comments.comments_count";
		$query['orderby']  = "comments.comments_count";

		$query['join'][] = "INNER JOIN (SELECT COUNT(user_id) AS comments_count, user_id "
		                   . "FROM $wpdb->comments "
		                   . "GROUP BY user_id) comments "
		                   . "ON wp_users.ID = comments.user_id";

		return $query;
	}

	//добавление данных комментариев после основного запроса
	function add_comments_count( $users ) {
		global $wpdb;

		if ( ! $users ) {
			return null;
		}

		$ids = $this->get_users_ids( $users );

		if ( ! $ids ) {
			return $users;
		}

		$query = "SELECT COUNT(user_id) AS comments_count, user_id AS ID "
		         . "FROM $wpdb->comments "
		         . "WHERE user_id IN (" . implode( ',', $ids ) . ") "
		         . "GROUP BY user_id";
		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$comments = $wpdb->get_results( $query );

		if ( $comments ) {
			$users = $this->merge_objects( $users, $comments, 'comments_count' );
		}

		return $users;
	}

	//добавление данных статуса после основного запроса
	function add_descriptions( $users ) {
		global $wpdb;

		if ( ! $users ) {
			return null;
		}

		$ids = $this->get_users_ids( $users );

		if ( ! $ids ) {
			return $users;
		}

		$query = "SELECT meta_value AS description, user_id AS ID "
		         . "FROM $wpdb->usermeta "
		         . "WHERE user_id IN (" . implode( ',', $ids ) . ") AND meta_key='description'";
		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$descs = $wpdb->get_results( $query );

		if ( $descs ) {
			$users = $this->merge_objects( $users, $descs, 'description' );
		}

		return $users;
	}

	function add_avatar_data( $users ) {
		global $wpdb;

		if ( ! $users ) {
			return null;
		}

		$ids = $this->get_users_ids( $users );

		if ( ! $ids ) {
			return $users;
		}

		$query = "SELECT meta_value AS avatar_data, user_id AS ID "
		         . "FROM $wpdb->usermeta "
		         . "WHERE user_id IN (" . implode( ',', $ids ) . ") AND meta_key='rcl_avatar'";
		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$descs = $wpdb->get_results( $query );

		if ( $descs ) {
			$users = $this->merge_objects( $users, $descs, 'avatar_data' );
		}

		return $users;
	}

	//добавляем выборку данных рейтинга в основной запрос
	function add_query_rating_total( $query ) {

		$query['select'][] = "ratings.rating_total";
		$query['groupby']  = "ratings.user_id";
		$query['orderby']  = "CAST(ratings.rating_total AS DECIMAL)";

		$query['join'][] = "INNER JOIN " . RCL_PREF . "rating_users AS ratings ON wp_users.ID = ratings.user_id";

		return $query;
	}

	//добавление данных рейтинга после основного запроса
	function add_rating_total( $users ) {
		global $wpdb;

		if ( ! $users ) {
			return null;
		}

		$ids = $this->get_users_ids( $users );

		if ( ! $ids ) {
			return $users;
		}

		$query = "SELECT rating_total, user_id AS ID "
		         . "FROM " . RCL_PREF . "rating_users "
		         . "WHERE user_id IN (" . implode( ',', $ids ) . ")";
		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$descs = $wpdb->get_results( $query );

		if ( $descs ) {
			$users = $this->merge_objects( $users, $descs, 'rating_total' );
		}

		return $users;
	}

	function get_users_ids( $users ) {

		if ( ! $users ) {
			return null;
		}

		$ids = array();

		foreach ( $users as $user ) {
			if ( ! isset( $user->ID ) || ! $user->ID ) {
				continue;
			}
			$ids[] = (int) $user->ID;
		}

		return $ids;
	}

	function merge_objects( $users, $data, $key ) {
		foreach ( $users as $k => $user ) {
			foreach ( $data as $d ) {
				if ( is_array( $d ) ) {
					if ( $d['ID'] != $user->ID ) {
						continue;
					}
					$users[ $k ]->$key = $d[ $key ];
				} else {
					if ( $d->ID != $user->ID ) {
						continue;
					}
					$users[ $k ]->$key = $d->$key;
				}
			}
		}

		return $users;
	}

	function get_filters( $count_users = false ) {
		global $post, $user_LK, $active_addons;

		if ( ! $this->filters ) {
			return false;
		}

		$content = '';

		if ( $this->search_form ) {
			$content = apply_filters( 'users_search_form_rcl', $content );
		}

		$count_users = ( false !== $count_users ) ? $count_users : $this->count();

		$content .= '<h3>' . esc_html__( 'Total number of users', 'wp-recall' ) . ': ' . $count_users . '</h3>';

		if ( isset( $this->add_uri['users-filter'] ) ) {
			unset( $this->add_uri['users-filter'] );
		}

		$s_array = $this->search_request();

		$rqst = ( $s_array ) ? implode( '&', $s_array ) . '&' : '';

		if ( rcl_is_office() ) {
			$url = ( isset( $_POST['tab_url'] ) ) ? sanitize_text_field( wp_unslash( $_POST['tab_url'] ) ) : rcl_get_user_url( $user_LK );
		} else {
			$url = get_permalink( $post->ID );
		}

		$perm = rcl_format_url( $url ) . $rqst;

		$current_filter = ( isset( $_GET['users-filter'] ) ) ? sanitize_key( $_GET['users-filter'] ) : 'time_action';

		$filters = array(
			'time_action'     => esc_html__( 'Activity', 'wp-recall' ),
			'posts_count'     => esc_html__( 'Publications', 'wp-recall' ),
			'comments_count'  => esc_html__( 'Comments', 'wp-recall' ),
			'user_registered' => esc_html__( 'Registration', 'wp-recall' ),
		);

		if ( isset( $active_addons['rating-system'] ) ) {
			$filters['rating_total'] = esc_html__( 'Rated', 'wp-recall' );
		}

		$filters = apply_filters( 'rcl_users_filter', $filters );

		$content .= '<div class="rcl-data-filters">' . esc_html__( 'Filter by', 'wp-recall' ) . ': ';

		foreach ( $filters as $key => $name ) {
			$content .= rcl_get_button( array(
				'label'  => $name,
				'href'   => esc_url( $perm . 'users-filter=' . $key ),
				'class'  => 'data-filter',
				'status' => $current_filter == $key ? 'disabled' : null
			) );
		}

		$content .= '</div>';

		return $content;
	}

	function add_query_search( $query ) {

		global $wpdb;

		$search_text  = ( isset( $_GET['search_text'] ) ) ? sanitize_text_field( wp_unslash( $_GET['search_text'] ) ) : '';
		$search_field = ( isset( $_GET['search_field'] ) ) ? sanitize_key( $_GET['search_field'] ) : '';

		if ( ! $search_text || ! in_array( $search_field, [ 'display_name', 'user_login' ] ) ) {
			return $query;
		}
		//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query['where'][] = $wpdb->prepare( "wp_users.$search_field LIKE %s", '%' . $wpdb->esc_like( $search_text ) . '%' );

		return $query;
	}

}
