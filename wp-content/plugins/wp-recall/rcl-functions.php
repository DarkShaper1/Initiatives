<?php

//регистрируем вкладку личного кабинета
function rcl_tab( $tab_data, $deprecated_callback = false, $deprecated_name = '', $deprecated_args = false ) {
	global $rcl_tabs;

	if ( ! is_array( $tab_data ) ) { //поддержка старого варианта регистрации вкладки
		if ( $deprecated_callback ) {
			_deprecated_argument( __FUNCTION__, '15.2.0' );
		}

		$args_tab = array(
			'id'      => $tab_data,
			'name'    => $deprecated_name,
			'content' => array(
				array(
					'id'       => $tab_data,
					'name'     => $deprecated_name,
					'icon'     => ( isset( $deprecated_args['class'] ) ) ? $deprecated_args['class'] : 'fa-cog',
					'callback' => array(
						'name' => $deprecated_callback
					)
				)
			)
		);

		if ( isset( $deprecated_args['cache'] ) && $deprecated_args['cache'] ) {
			$args_tab['supports'][] = 'cache';
		}

		if ( isset( $deprecated_args['ajax-load'] ) && $deprecated_args['ajax-load'] ) {
			$args_tab['supports'][] = 'ajax';
		}

		$args_tab['counter'] = ( isset( $deprecated_args['counter'] ) ) ? $deprecated_args['counter'] : null;
		$args_tab['public']  = ( isset( $deprecated_args['public'] ) ) ? $deprecated_args['public'] : 0;
		$args_tab['icon']    = ( isset( $deprecated_args['class'] ) ) ? $deprecated_args['class'] : 'fa-cog';
		$args_tab['output']  = ( isset( $deprecated_args['output'] ) ) ? $deprecated_args['output'] : 'menu';

		$tab_data = $args_tab;
	}

	if ( ! isset( $tab_data['icon'] ) || ! $tab_data['icon'] ) {
		$tab_data['icon'] = 'fa-cog';
	}

	$tab_data = apply_filters( 'rcl_tab', $tab_data );

	if ( ! $tab_data ) {
		return false;
	}

	$rcl_tabs[ $tab_data['id'] ] = $tab_data;
}

//регистрируем созданные произвольные вкладки
add_action( 'init', 'rcl_init_custom_tabs', 10 );
function rcl_init_custom_tabs() {

	$areas = rcl_get_area_options();

	foreach ( $areas as $area_id => $tabs ) {

		if ( ! $tabs ) {
			continue;
		}

		foreach ( $tabs as $tab ) {

			if ( isset( $tab['default-tab'] ) ) {
				continue;
			}

			$supports = ! empty( $tab['supports-tab'] ) && is_array( $tab['supports-tab'] ) ? $tab['supports-tab'] : array();

			$tab_data = array(
				'id'         => $tab['slug'],
				'name'       => $tab['title'],
				'public'     => ( isset( $tab['public-tab'] ) && $tab['public-tab'] ) ? 1 : 0,
				'icon'       => ( $tab['icon'] ) ? $tab['icon'] : 'fa-cog',
				'output'     => $area_id,
				'custom-tab' => true,
				'content'    => array(
					array(
						'id'       => 'subtab-1',
						'name'     => $tab['title'],
						'icon'     => ( $tab['icon'] ) ? $tab['icon'] : 'fa-cog',
						'callback' => array(
							'name' => 'rcl_custom_tab_content',
							'args' => array( $tab['content'] )
						)
					)
				)
			);

			if ( in_array( 'cache', $supports ) ) {
				$tab_data['supports'][] = 'cache';
			}

			if ( in_array( 'ajax', $supports ) ) {
				$tab_data['supports'][] = 'ajax';
			}

			if ( in_array( 'dialog', $supports ) ) {
				$tab_data['supports'][] = 'dialog';
			}

			rcl_tab( $tab_data );
		}
	}
}

//регистрация дочерней вкладки
function rcl_add_sub_tab( $tab_id, $subtab ) {
	global $rcl_tabs;
	if ( ! isset( $rcl_tabs[ $tab_id ] ) ) {
		return false;
	}
	$rcl_tabs[ $tab_id ]['content'][] = $subtab;
}

function rcl_get_tabs() {
	global $rcl_tabs;

	return $rcl_tabs;
}

function rcl_get_tab( $tab_id ) {
	$rcl_tabs = rcl_get_tabs();

	return isset( $rcl_tabs[ $tab_id ] ) ? $rcl_tabs[ $tab_id ] : false;
}

function rcl_get_subtab( $tab_id, $subtab_id ) {
	$tab = rcl_get_tab( $tab_id );
	if ( ! $tab ) {
		return false;
	}
	foreach ( $tab['content'] as $subtab ) {
		if ( $subtab['id'] == $subtab_id ) {
			return $subtab;
		}
	}

	return false;
}

function rcl_get_tab_button( $tab_id, $master_id ) {

	$tab = rcl_get_tab( $tab_id );

	if ( ! class_exists( 'Rcl_Tab' ) ) {
		require_once RCL_PATH . 'classes/class-rcl-tab.php';
	}

	$Rcl_Tab = new Rcl_Tab( $tab );

	return $Rcl_Tab->get_tab_button( $master_id );
}

function rcl_get_tab_content( $tab_id, $master_id, $subtab_id = '' ) {

	if ( ! class_exists( 'Rcl_Tab' ) ) {
		require_once RCL_PATH . 'classes/class-rcl-tab.php';
	}

	$tab = rcl_get_tab( $tab_id );

	if ( ! $tab ) {
		return false;
	}

	$tab['first'] = 1;

	$Rcl_Tab = new Rcl_Tab( $tab );

	return $Rcl_Tab->get_tab( $master_id, $subtab_id );
}

//приводим структуру вкладки к окончательному виду
add_action( 'wp_loaded', 'rcl_setup_tabs', 10 );
function rcl_setup_tabs() {
	global $rcl_tabs;

	do_action( 'rcl_setup_tabs' );

	if ( $rcl_tabs ) {

		foreach ( $rcl_tabs as $k => $rcl_tab ) {

			if ( ! isset( $rcl_tab['icon'] ) ) {
				$rcl_tab['icon'] = 'fa-cog';
			}

			if ( isset( $rcl_tab['content'] ) && $rcl_tab['content'] ) {
				foreach ( $rcl_tab['content'] as $s => $subtab ) {
					if ( ! isset( $rcl_tab['content'][ $s ]['id'] ) ) {
						$rcl_tabs[ $k ]['content'][ $s ]['id'] = $rcl_tab['id'];
					}

					if ( ! isset( $rcl_tab['content'][ $s ]['name'] ) ) {
						$rcl_tabs[ $k ]['content'][ $s ]['name'] = $rcl_tab['name'];
					}

					if ( ! isset( $rcl_tab['content'][ $s ]['icon'] ) ) {
						$rcl_tabs[ $k ]['content'][ $s ]['icon'] = $rcl_tab['icon'];
					}

					break;
				}
			}
		}
	}

	$rcl_tabs = apply_filters( 'rcl_tabs', $rcl_tabs );
}

//регистрируем вкладки для вывода в личном кабинете
add_action( 'wp', 'rcl_register_tabs', 10 );
function rcl_register_tabs() {

	if ( is_admin() || ! rcl_is_office() ) {
		return false;
	}

	$rcl_tabs = rcl_get_tabs();

	if ( ! $rcl_tabs ) {
		return false;
	}

	if ( ! class_exists( 'Rcl_Tab' ) ) {
		require_once RCL_PATH . 'classes/class-rcl-tab.php';
	}

	foreach ( $rcl_tabs as $tab ) {
		$Rcl_Tab = new Rcl_Tab( $tab );
		$Rcl_Tab->register_tab();
	}
}

//сортируем вкладки и изменяем их данные согласно настроек
add_filter( 'rcl_tabs', 'rcl_add_custom_tabs', 5 );
function rcl_add_custom_tabs( $tabs ) {

	$areas = rcl_get_area_options();

	if ( ! $areas ) {
		return $tabs;
	}

	if ( $tabs ) {

		foreach ( $tabs as $tab_id => $tab ) {

			$tabArea = ( isset( $tab['output'] ) ) ? $tab['output'] : 'menu';

			if ( ! isset( $areas[ $tabArea ] ) || ! $areas[ $tabArea ] ) {
				continue;
			}

			foreach ( $areas[ $tabArea ] as $k => $field ) {

				if ( $field['slug'] != $tab_id ) {
					continue;
				}

				$tabs[ $tab_id ]['icon']   = $field['icon'];
				$tabs[ $tab_id ]['hidden'] = isset( $field['hidden'] ) ? $field['hidden'] : 0;
				$tabs[ $tab_id ]['name']   = $field['title'];
				$tabs[ $tab_id ]['order']  = ++ $k;
			}
		}
	}

	return $tabs;
}

//выясняем какую вкладку ЛК показывать пользователю,
//если ни одна не указана для вывода
add_filter( 'rcl_tabs', 'rcl_get_order_tabs', 10 );
function rcl_get_order_tabs( $rcl_tabs ) {
	global $user_ID, $user_LK;

	if ( isset( $_GET['tab'] ) || ! $rcl_tabs ) {
		return $rcl_tabs;
	}

	$counter = array();
	$a       = 10;
	foreach ( $rcl_tabs as $id => $data ) {

		if ( isset( $data['output'] ) && $data['output'] != 'menu' ) {
			continue;
		}

		if ( isset( $data['hidden'] ) && $data['hidden'] ) {
			continue;
		}

		if ( ! isset( $data['public'] ) || $data['public'] != 1 ) {

			if ( ! $user_ID ) {
				continue;
			}

			if ( $data['public'] < 0 && $user_ID == $user_LK ) {
				continue;
			}

			if ( $data['public'] == 0 && $user_ID != $user_LK ) {
				continue;
			}
		}

		$order                    = ( isset( $data['order'] ) ) ? $data['order'] : ++ $a;
		$rcl_tabs[ $id ]['order'] = $order;
		$counter[ $order ]        = $id;
	}

	if ( count( $counter ) == 1 ) {

		foreach ( $counter as $id_tab ) {
			$rcl_tabs[ $id_tab ]['first'] = 1;
			break;
		}

		return $rcl_tabs;
	}

	if ( count( $counter ) == 1 || ! $counter ) {

		foreach ( $rcl_tabs as $id_tab => $data ) {
			$rcl_tabs[ $id_tab ]['first'] = 1;
			break;
		}

		return $rcl_tabs;
	}

	ksort( $counter );
	$id_first                       = array_shift( $counter );
	$rcl_tabs[ $id_first ]['first'] = 1;

	return $rcl_tabs;
}

//регистрируем контентые блоки
function rcl_block( $place, $callback, $args = false ) {
	global $rcl_blocks;

	$rcl_blocks[ $place ][] = apply_filters( 'block_data_rcl', array(
		'place'    => $place,
		'callback' => $callback,
		'args'     => $args
	) );

	$rcl_blocks = apply_filters( 'rcl_blocks', $rcl_blocks );
}

//формируем вывод зарегистрированных контентных блоков в личном кабинете
add_action( 'wp', 'rcl_setup_blocks' );
function rcl_setup_blocks() {
	global $rcl_blocks, $user_LK;

	if ( is_admin() || ! $user_LK ) {
		return false;
	}

	if ( ! $rcl_blocks ) {
		return false;
	}

	if ( ! class_exists( 'Rcl_Blocks' ) ) {
		require_once RCL_PATH . 'classes/class-rcl-blocks.php';
	}

	foreach ( $rcl_blocks as $place_id => $blocks ) {
		if ( ! $blocks ) {
			continue;
		}
		foreach ( $blocks as $data ) {
			$Rcl_Blocks = new Rcl_Blocks( $data );
			$Rcl_Blocks->add_block();
		}
	}

	do_action( 'rcl_setup_blocks' );
}

function rcl_is_office( $user_id = null ) {
	global $rcl_office;

	if ( isset( $_POST['action'], $_POST['post'] ) && $_POST['action'] == 'rcl_ajax_tab' ) {
		$post = rcl_decode_post( sanitize_text_field( wp_unslash( $_POST['post'] ) ) );

		if ( $post->master_id ) {
			$rcl_office = intval( $post->master_id );
		}
	}

	if ( $rcl_office ) {

		if ( isset( $user_id ) ) {
			if ( $user_id == $rcl_office ) {
				return true;
			}

			return false;
		}

		return true;
	}

	return false;
}

//регистрируем список публикаций указанного типа записи
function rcl_postlist( $id, $post_type, $name = '', $args = false ) {
	global $rcl_postlist;

	if ( ! rcl_get_option( 'publics_block_rcl' ) ) {
		return false;
	}

	$rcl_postlist[ $post_type ] = array( 'id' => $id, 'post_type' => $post_type, 'name' => $name, 'args' => $args );
}

//регистрация recalolbar`a
add_action( 'after_setup_theme', 'rcl_register_recallbar' );
function rcl_register_recallbar() {

	if ( ! rcl_get_option( 'view_recallbar' ) ) {
		return false;
	}

	register_nav_menus( array( 'recallbar' => __( 'Recallbar', 'wp-recall' ) ) );
}

function rcl_key_addon( $path_parts ) {
	if ( ! isset( $path_parts['dirname'] ) ) {
		return false;
	}

	return rcl_get_addon_dir( $path_parts['dirname'] );
}

//очищаем кеш плагина раз в сутки
add_action( 'rcl_cron_daily', 'rcl_clear_cache', 20 );
function rcl_clear_cache() {
	$rcl_cache = new Rcl_Cache();
	$rcl_cache->clear_cache();
}

//удаление определенного файла кеша
function rcl_delete_file_cache( $string ) {
	$rcl_cache = new Rcl_Cache();
	$rcl_cache->get_file( $string );
	$rcl_cache->delete_file();
}

function rcl_cache_get( $string, $force = false ) {

	$cache = new Rcl_Cache();

	if ( $cache->is_cache || $force ) {

		$file = $cache->get_file( $string );

		if ( ! $file->need_update ) {

			return $cache->get_cache();
		}
	}

	return false;
}

function rcl_cache_add( $string, $content, $force = false ) {

	$cache = new Rcl_Cache();

	if ( $cache->is_cache || $force ) {

		$file = $cache->get_file( $string );

		if ( $file->need_update ) {

			return $cache->update_cache( $content );
		}
	}

	return false;
}

if ( ! function_exists( 'get_called_class' ) ) :
	function get_called_class() {
		$arr       = array();
		$arrTraces = debug_backtrace();
		foreach ( $arrTraces as $arrTrace ) {
			if ( ! array_key_exists( "class", $arrTrace ) ) {
				continue;
			}
			if ( count( $arr ) == 0 ) {
				$arr[] = $arrTrace['class'];
			} else if ( get_parent_class( $arrTrace['class'] ) == end( $arr ) ) {
				$arr[] = $arrTrace['class'];
			}
		}

		return end( $arr );
	}

endif;
function rcl_encode_post( $array ) {
	return base64_encode( json_encode( $array ) );
}

function rcl_decode_post( $string ) {
	return json_decode( base64_decode( $string ) );
}

//запрещаем доступ в админку
add_action( 'init', 'rcl_admin_access', 1 );
function rcl_admin_access() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}
	if ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) {
		return;
	}

	if ( is_admin() ) {

		global $user_ID;

		$access = rcl_check_access_console();

		if ( $access ) {
			return true;
		}

		if ( isset( $_POST['short'] ) && intval( $_POST['short'] ) == 1 || isset( $_POST['fetch'] ) && intval( $_POST['fetch'] ) == 1 ) {

			return true;
		} else {

			if ( ! $user_ID ) {
				return true;
			}

			wp_safe_redirect( '/' );
			exit;
		}
	}
}

function rcl_check_access_console() {
	global $current_user;

	$need_access = rcl_get_option( 'consol_access_rcl', 7 );

	if ( $current_user->user_level ) {

		$access = ( $current_user->user_level < $need_access ) ? false : true;
	} else if ( isset( $current_user->allcaps[ 'level_' . $need_access ] ) ) {

		$access = ( $current_user->allcaps[ 'level_' . $need_access ] == 1 ) ? true : false;
	} else {

		$roles = array(
			10 => 'administrator',
			7  => 'editor',
			2  => 'author',
			1  => 'contributor'
		);

		$access = ( isset( $roles[ $need_access ] ) && current_user_can( $roles[ $need_access ] ) ) ? true : false;
	}

	return $access;
}

/* Удаление поста вместе с его вложениями */
add_action( 'before_delete_post', 'rcl_delete_attachments_with_post' );
function rcl_delete_attachments_with_post( $postid ) {
	$attachments = get_posts( array(
		'post_type'      => 'attachment',
		'posts_per_page' => - 1,
		'post_status'    => null,
		'post_parent'    => $postid
	) );
	if ( $attachments ) {
		foreach ( ( array ) $attachments as $attachment ) {
			wp_delete_attachment( $attachment->ID, true );
		}
	}
}

//регистрируем размеры миниатюра загружаемого аватара пользователя
add_action( 'init', 'rcl_init_avatar_sizes' );
function rcl_init_avatar_sizes() {
	global $rcl_avatar_sizes;

	$sizes = array( 70, 150, 300 );

	$rcl_avatar_sizes = apply_filters( 'rcl_avatar_sizes', $sizes );
	asort( $rcl_avatar_sizes );
}

//указание url до загруженного изображения аватарки
add_filter( 'pre_get_avatar_data', 'rcl_avatar_data_replacement', 20, 2 );
function rcl_avatar_data_replacement( $args, $id_or_email ) {
	global $rcl_user;

	$size = $args['size'];

	$user_id     = 0;
	$avatar_data = false;

	if ( $rcl_user && $rcl_user->ID == $id_or_email ) {

		$user_id = $rcl_user->ID;

		if ( isset( $rcl_user->avatar_data ) && $rcl_user->avatar_data ) {
			$avatar_data = $rcl_user->avatar_data;
		}
	} else {

		if ( is_numeric( $id_or_email ) ) {
			$user_id = $id_or_email;
		} elseif ( is_object( $id_or_email ) ) {
			$user_id = $id_or_email->user_id;
		} elseif ( is_email( $id_or_email ) ) {
			if ( $user = get_user_by( 'email', $id_or_email ) ) {
				$user_id = $user->ID;
			}
		}
	}

	if ( $user_id ) {

		if ( ! $avatar_data ) {
			$avatar_data = get_user_meta( $user_id, 'rcl_avatar', 1 );
		}

		if ( ! $avatar_data ) {
			$avatar_data = rcl_get_option( 'default_avatar', false );
		}

		if ( $avatar_data ) {

			$url = false;

			if ( is_numeric( $avatar_data ) ) {
				$image_attributes = wp_get_attachment_image_src( $avatar_data, array( $size, $size ) );
				if ( $image_attributes ) {
					$url = $image_attributes[0];
				}
			} else if ( is_string( $avatar_data ) ) {
				$url = rcl_get_url_avatar( $avatar_data, $user_id, $size );
			}

			if ( $url && file_exists( rcl_path_by_url( $url ) ) ) {
				$args['url'] = $url;
			}
		}
	}

	return $args;
}

function rcl_get_url_avatar( $url_image, $user_id, $size ) {
	global $rcl_avatar_sizes;

	if ( ! $rcl_avatar_sizes ) {
		return $url_image;
	}

	$optimal_path = false;
	$name         = explode( '.', basename( $url_image ) );
	foreach ( $rcl_avatar_sizes as $rcl_size ) {
		if ( $size > $rcl_size ) {
			continue;
		}

		$optimal_size = $rcl_size;
		$optimal_url  = RCL_UPLOAD_URL . 'avatars/' . $user_id . '-' . $optimal_size . '.' . $name[1];
		$optimal_path = RCL_UPLOAD_PATH . 'avatars/' . $user_id . '-' . $optimal_size . '.' . $name[1];
		break;
	}

	if ( $optimal_path && file_exists( $optimal_path ) ) {
		$url_image = $optimal_url;
	}

	return $url_image;
}

function rcl_sanitize_string( $title, $sanitize = true ) {

	$title = mb_strtolower( $title );

	switch ( get_site_option( 'rtl_standard' ) ) {
		case 'off':
			return $title;
		case 'gost':
			$title = strtr( $title, apply_filters( 'rcl_sanitize_gost', array(
				"Є" => "EH",
				"І" => "I",
				"і" => "i",
				"№" => "#",
				"є" => "eh",
				"А" => "A",
				"Б" => "B",
				"В" => "V",
				"Г" => "G",
				"Д" => "D",
				"Е" => "E",
				"Ё" => "JO",
				"Ж" => "ZH",
				"З" => "Z",
				"И" => "I",
				"Й" => "JJ",
				"К" => "K",
				"Л" => "L",
				"М" => "M",
				"Н" => "N",
				"О" => "O",
				"П" => "P",
				"Р" => "R",
				"С" => "S",
				"Т" => "T",
				"У" => "U",
				"Ф" => "F",
				"Х" => "KH",
				"Ц" => "C",
				"Ч" => "CH",
				"Ш" => "SH",
				"Щ" => "SHH",
				"Ъ" => "'",
				"Ы" => "Y",
				"Ь" => "",
				"Э" => "EH",
				"Ю" => "YU",
				"Я" => "YA",
				"а" => "a",
				"б" => "b",
				"в" => "v",
				"г" => "g",
				"д" => "d",
				"е" => "e",
				"ё" => "jo",
				"ж" => "zh",
				"з" => "z",
				"и" => "i",
				"й" => "jj",
				"к" => "k",
				"л" => "l",
				"м" => "m",
				"н" => "n",
				"о" => "o",
				"п" => "p",
				"р" => "r",
				"с" => "s",
				"т" => "t",
				"у" => "u",
				"ф" => "f",
				"х" => "kh",
				"ц" => "c",
				"ч" => "ch",
				"ш" => "sh",
				"щ" => "shh",
				"ъ" => "",
				"ы" => "y",
				"ь" => "",
				"э" => "eh",
				"ю" => "yu",
				"я" => "ya",
				"—" => "-",
				"«" => "",
				"»" => "",
				"…" => ""
			) ) );
			break;
		default:
			$title = strtr( $title, apply_filters( 'rcl_sanitize_iso', array(
				"Є" => "YE",
				"І" => "I",
				"Ѓ" => "G",
				"і" => "i",
				"№" => "#",
				"є" => "ye",
				"ѓ" => "g",
				"А" => "A",
				"Б" => "B",
				"В" => "V",
				"Г" => "G",
				"Д" => "D",
				"Е" => "E",
				"Ё" => "YO",
				"Ж" => "ZH",
				"З" => "Z",
				"И" => "I",
				"Й" => "J",
				"К" => "K",
				"Л" => "L",
				"М" => "M",
				"Н" => "N",
				"О" => "O",
				"П" => "P",
				"Р" => "R",
				"С" => "S",
				"Т" => "T",
				"У" => "U",
				"Ф" => "F",
				"Х" => "X",
				"Ц" => "C",
				"Ч" => "CH",
				"Ш" => "SH",
				"Щ" => "SHH",
				"Ъ" => "'",
				"Ы" => "Y",
				"Ь" => "",
				"Э" => "E",
				"Ю" => "YU",
				"Я" => "YA",
				"а" => "a",
				"б" => "b",
				"в" => "v",
				"г" => "g",
				"д" => "d",
				"е" => "e",
				"ё" => "yo",
				"ж" => "zh",
				"з" => "z",
				"и" => "i",
				"й" => "j",
				"к" => "k",
				"л" => "l",
				"м" => "m",
				"н" => "n",
				"о" => "o",
				"п" => "p",
				"р" => "r",
				"с" => "s",
				"т" => "t",
				"у" => "u",
				"ф" => "f",
				"х" => "x",
				"ц" => "c",
				"ч" => "ch",
				"ш" => "sh",
				"щ" => "shh",
				"ъ" => "",
				"ы" => "y",
				"ь" => "",
				"э" => "e",
				"ю" => "yu",
				"я" => "ya",
				"—" => "-",
				"«" => "",
				"»" => "",
				"…" => ""
			) ) );
	}

	return $sanitize ? sanitize_title_with_dashes( $title, '', 'save' ) : $title;
}

add_filter( 'author_link', 'rcl_author_link', 999, 2 );
function rcl_author_link( $link, $author_id ) {

	if ( rcl_get_option( 'view_user_lk_rcl' ) != 1 ) {
		return $link;
	}

	return rcl_get_user_url( $author_id );
}

function rcl_get_user_url( $user_id ) {

	if ( rcl_get_option( 'view_user_lk_rcl' ) != 1 ) {
		return get_author_posts_url( $user_id );
	}

	return add_query_arg(
		array(
			rcl_get_option( 'link_user_lk_rcl', 'user' ) => $user_id
		), get_permalink( rcl_get_option( 'lk_page_rcl' ) )
	);
}

function rcl_format_in( $array ) {
	$separats = array_fill( 0, count( $array ), '%d' );

	return implode( ', ', $separats );
}

function rcl_get_postmeta_array( $post_id ) {
	global $wpdb;

	$cachekey = json_encode( array( 'rcl_get_postmeta_array', (int) $post_id ) );
	$cache    = wp_cache_get( $cachekey );
	if ( $cache ) {
		return $cache;
	}

	$mts   = array();
	$metas = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "postmeta WHERE post_id=%d", $post_id ) );
	if ( ! $metas ) {
		return false;
	}
	foreach ( $metas as $meta ) {
		$mts[ $meta->meta_key ] = $meta->meta_value; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	}

	wp_cache_add( $cachekey, $mts );

	return $mts;
}

function rcl_setup_chartdata( $mysqltime, $data ) {
	global $chartArgs;

	$day = date( "Y.m.j", strtotime( $mysqltime ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

	$price = $data / 1000;

	if ( ! isset( $chartArgs[ $day ] ) ) {
		$chartArgs[ $day ]['summ'] = $price;
		$chartArgs[ $day ]['cnt']  = 1;
	} else {
		$chartArgs[ $day ]['summ'] += $price;
		$chartArgs[ $day ]['cnt']  += 1;
	}
	$chartArgs[ $day ]['days'] = date( "t", strtotime( $mysqltime ) );// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

	return $chartArgs;
}

function rcl_get_chart( $arr = false ) {
	global $chartData;

	if ( ! $arr ) {
		return false;
	}

	foreach ( $arr as $month => $data ) {
		$cnt                 = ( isset( $data['cnt'] ) ) ? $data['cnt'] : 0;
		$summ                = ( isset( $data['summ'] ) ) ? $data['summ'] : 0;
		$chartData['data'][] = array( '"' . $month . '"', $cnt, $summ );
	}

	if ( ! $chartData ) {
		return false;
	}

	krsort( $chartData['data'] );
	array_unshift( $chartData['data'], array_pop( $chartData['data'] ) );

	return rcl_get_include_template( 'chart.php' );
}

//добавляем уведомление в личном кабинете
function rcl_notice_text( $text, $type = 'warning' ) {
	if ( is_admin() ) {
		return false;
	}
	if ( ! class_exists( 'Rcl_Notify' ) ) {
		include_once RCL_PATH . 'functions/notify.php';
	}
	$block = new Rcl_Notify( $text, $type );
}

function rcl_get_smiles( $id_area ) {
	$smiles = '<div class="rcl-smiles" data-area="' . $id_area . '">';
	$smiles .= '<i class="rcli fa-smile-o" aria-hidden="true"></i>';
	$smiles .= '<div class="rcl-smiles-list">
                        <div class="smiles"></div>
                    </div>';
	$smiles .= '</div>';

	return $smiles;
}

function rcl_mail( $email, $title, $text, $from = false, $attach = false ) {

	$from_name = ( isset( $from['name'] ) ) ? $from['name'] : get_bloginfo( 'name' );
	$from_mail = ( isset( $from['email'] ) ) ? $from['email'] : 'noreply@' . ( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' );

	add_filter( 'wp_mail_content_type', function () {
		return "text/html";
	} );

	$headers = 'From: ' . $from_name . ' <' . $from_mail . '>' . "\r\n";

	$text .= '<p><small>-----------------------------------------------------<br/>
    ' . __( 'This letter was created automatically, no need to answer it.', 'wp-recall' ) . '<br/>
    "' . get_bloginfo( 'name' ) . '"</small></p>';

	return wp_mail( $email, $title, $text, $headers, $attach );
}

/*todo remove ?*/
function rcl_multisort_array( $array, $key, $type = SORT_ASC, $cmp_func = 'strcmp' ) {
	$GLOBALS['ARRAY_MULTISORT_KEY_SORT_KEY'] = $key;
	usort( $array, create_function( '$a, $b', '$k = &$GLOBALS["ARRAY_MULTISORT_KEY_SORT_KEY"];
        return ' . $cmp_func . '($a[$k], $b[$k]) * ' . ( $type == SORT_ASC ? 1 : - 1 ) . ';' ) );

	return $array;
}

function rcl_a_active( $param1, $param2 ) {
	if ( $param1 == $param2 ) {
		return 'filter-active';
	}
}

function rcl_get_useraction( $user_action = false ) {
	global $rcl_userlk_action;

	if ( ! $user_action ) {
		$user_action = $rcl_userlk_action;
	}

	$unix_time_user = strtotime( $user_action );

	if ( ! $unix_time_user || $user_action == '0000-00-00 00:00:00' ) {
		return __( 'long ago', 'wp-recall' );
	}

	$timeout          = ( $time = rcl_get_option( 'timeout' ) ) ? $time * 60 : 600;
	$unix_time_action = strtotime( current_time( 'mysql' ) );

	if ( $unix_time_action > $unix_time_user + $timeout ) {
		return human_time_diff( $unix_time_user, $unix_time_action );
	} else {
		return false;
	}
}

function rcl_get_useraction_html( $user_id, $type = 1 ) {

	$action = rcl_get_time_user_action( $user_id );

	switch ( $type ) {
		case 1:

			$last_action = rcl_get_useraction( $action );

			if ( ! $last_action ) {
				return '<span class="status_user online"><i class="rcli fa-circle"></i></span>';
			} else {
				return '<span class="status_user offline" title="' . __( 'offline', 'wp-recall' ) . ' ' . $last_action . '"><i class="rcli fa-circle"></i></span>';
			}

			break;
		case 2:

			return rcl_get_miniaction( $action );

			break;
	}
}

function rcl_human_time_diff( $time_action ) {
	$unix_current_time = strtotime( current_time( 'mysql' ) );
	$unix_time_action  = strtotime( $time_action );

	return human_time_diff( $unix_time_action, $unix_current_time );
}

function rcl_update_timeaction_user() {
	global $user_ID, $wpdb;

	if ( ! $user_ID ) {
		return false;
	}

	$rcl_current_action = rcl_get_time_user_action( $user_ID );

	$last_action = rcl_get_useraction( $rcl_current_action );

	if ( $last_action ) {

		$time = current_time( 'mysql' );

		$res = $wpdb->update(
			RCL_PREF . 'user_action', array( 'time_action' => $time ), array( 'user' => $user_ID )
		);

		if ( ! isset( $res ) || $res == 0 ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$act_user = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(time_action) FROM " . RCL_PREF . "user_action WHERE user =%d", $user_ID ) );
			if ( $act_user == 0 ) {
				$wpdb->insert(
					RCL_PREF . 'user_action', array(
						'user'        => $user_ID,
						'time_action' => $time
					)
				);
			}
			if ( $act_user > 1 ) {
				rcl_delete_user_action( $user_ID );
			}
		}
	}

	do_action( 'rcl_update_timeaction_user' );
}

function rcl_get_button( $args, $depr_url = false, $depr_args = false ) {

	if ( is_array( $args ) ) {
		$bttn = new Rcl_Button( $args );

		return $bttn->get_button();
	}

	//_deprecated_argument( __FUNCTION__, '16.21.0' );

	$button = '<a href="' . $depr_url . '" ';
	if ( isset( $depr_args['attr'] ) && $depr_args['attr'] ) {
		$button .= $depr_args['attr'] . ' ';
	}
	if ( isset( $depr_args['id'] ) && $depr_args['id'] ) {
		$button .= 'id="' . $depr_args['id'] . '" ';
	}
	$button .= 'class="recall-button ';
	if ( isset( $depr_args['class'] ) && $depr_args['class'] ) {
		$button .= $depr_args['class'];
	}
	$button .= '">';
	if ( isset( $depr_args['icon'] ) && $depr_args['icon'] ) {
		$button .= '<i class="rcli ' . $depr_args['icon'] . '"></i>';
	}
	$button .= '<span>' . $args . '</span>';
	$button .= '</a>';

	return $button;
}

function rcl_add_balloon_menu( $data, $args ) {
	if ( $data['id'] != $args['tab_id'] ) {
		return $data;
	}
	$data['name'] = sprintf( '%s <span class="rcl-menu-notice">%s</span>', $data['name'], $args['ballon_value'] );

	return $data;
}

/* 14.0.0 */
function rcl_verify_ajax_nonce() {
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
		return false;
	}
	if ( ! isset( $_POST['ajax_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['ajax_nonce'] ) ), 'rcl-post-nonce' ) ) {
		wp_send_json( array( 'error' => __( 'Signature verification failed', 'wp-recall' ) . '!' ) );
	}
}

function rcl_office_class() {
	global $active_addons, $user_LK, $user_ID;

	$class = array( 'wprecallblock', 'rcl-office' );

	$active_template = get_site_option( 'rcl_active_template' );

	if ( $active_template ) {
		if ( isset( $active_addons[ $active_template ] ) ) {
			$class[] = 'office-' . strtolower( str_replace( ' ', '-', $active_addons[ $active_template ]['template'] ) );
		}
	}

	if ( $user_ID ) {
		$class[] = ( $user_LK == $user_ID ) ? 'visitor-master' : 'visitor-guest';
	} else {
		$class[] = 'visitor-guest';
	}

	$class[] = ( rcl_get_option( 'buttons_place' ) == 1 ) ? "vertical-menu" : "horizontal-menu";

	echo 'class="' . esc_attr( implode( ' ', $class ) ) . '"';
}

function rcl_template_support( $support ) {

	switch ( $support ) {
		case 'avatar-uploader':

			if ( rcl_get_option( 'avatar_weight', 1024 ) > 0 ) {
				include_once 'functions/supports/uploader-avatar.php';
			}

			break;
		case 'cover-uploader':

			add_filter( 'rcl_options', 'rcl_add_cover_options', 10 );

			if ( rcl_get_option( 'cover_weight', 1024 ) > 0 ) {
				include_once 'functions/supports/uploader-cover.php';
			}

			break;
		case 'modal-user-details':
			include_once 'functions/supports/modal-user-details.php';
			break;
	}
}

function rcl_is_user_role( $user_id, $role ) {
	$user_data = get_userdata( $user_id );

	if ( ! isset( $user_data->roles ) || ! $user_data->roles ) {
		return false;
	}

	$roles = $user_data->roles;

	$current_role = array_shift( $roles );

	if ( is_array( $role ) ) {
		if ( in_array( $current_role, $role ) ) {
			return true;
		}
	} else {
		if ( $current_role == $role ) {
			return true;
		}
	}

	return false;
}

function rcl_is_register_open() {
	return apply_filters( 'rcl_users_can_register', get_site_option( 'users_can_register' ) );
}

/* 16.0.0 */
function rcl_update_profile_fields( $user_id, $profileFields = false ) {
	global $user_ID;

	require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
	require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
	require_once( ABSPATH . "wp-admin" . '/includes/media.php' );

	if ( ! $profileFields ) {
		$profileFields = rcl_get_profile_fields();
	}

	if ( $profileFields ) {

		$defaultFields = array(
			'user_email',
			'description',
			'user_url',
			'first_name',
			'last_name',
			'display_name',
			'primary_pass',
			'repeat_pass',
			'show_admin_bar_front'
		);

		foreach ( $profileFields as $field ) {

			$field = apply_filters( 'rcl_pre_update_profile_field', $field, $user_id );

			if ( ! $field || ! $field['slug'] ) {
				continue;
			}

			$slug = $field['slug'];

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$value = ( isset( $_POST[ $slug ] ) ) ? wp_unslash( $_POST[ $slug ] ) : false;

			if ( isset( $field['admin'] ) && $field['admin'] == 1 && ! is_admin() && ! rcl_is_user_role( $user_ID, [ 'administrator' ] ) ) {

				if ( in_array( $slug, array( 'display_name', 'user_url' ) ) ) {

					if ( get_the_author_meta( $slug, $user_id ) ) {
						continue;
					}
				} else {

					if ( get_user_meta( $user_id, $slug, $value ) ) {
						continue;
					}
				}
			}

			if ( $field['type'] == 'file' ) {

				$attach_id = get_user_meta( $user_id, $slug, 1 );

				if ( $attach_id && $value != $attach_id ) {
					wp_delete_attachment( $attach_id );
					delete_user_meta( $user_id, $slug );
				}
			}

			if ( $field['type'] != 'editor' ) {
				$value = rcl_recursive_map( 'sanitize_text_field', $value );
			} else {
				$value = rcl_recursive_map( 'esc_html', $value );
			}

			if ( in_array( $slug, $defaultFields ) ) {

				if ( $slug == 'repeat_pass' ) {
					continue;
				}

				if ( $slug == 'primary_pass' && $value ) {

					if ( ! isset( $_POST['repeat_pass'] ) || $value != $_POST['repeat_pass'] ) {
						continue;
					}

					$slug = 'user_pass';
				}

				if ( $slug == 'user_email' ) {

					if ( ! $value ) {
						continue;
					}

					$currentEmail = get_the_author_meta( 'user_email', $user_id );

					if ( $currentEmail == $value ) {
						continue;
					}
				}

				wp_update_user( array( 'ID' => $user_id, $slug => $value ) );

				continue;
			}

			if ( $field['type'] == 'checkbox' ) {

				$vals = array();

				if ( is_array( $value ) ) {

					$vals = array();

					foreach ( $value as $val ) {
						if ( in_array( $val, $field['values'] ) ) {
							$vals[] = $val;
						}
					}
				}

				if ( $vals ) {
					update_user_meta( $user_id, $slug, $vals );
				} else {
					delete_user_meta( $user_id, $slug );
				}
			} else {

				if ( $value ) {

					if ( in_array( $field['type'], [ 'select', 'radio' ] ) ) {
						if ( ! in_array( $value, $field['values'] ) ) {
							continue;
						}
					}

					update_user_meta( $user_id, $slug, $value );
				} else {

					if ( get_user_meta( $user_id, $slug, $value ) ) {
						delete_user_meta( $user_id, $slug, $value );
					}
				}
			}

			if ( $value ) {

				if ( $field['type'] == 'uploader' ) {
					foreach ( $value as $val ) {
						rcl_delete_temp_media( $val );
					}
				} else if ( $field['type'] == 'file' ) {
					rcl_delete_temp_media( $value );
				}
			}
		}
	}

	do_action( 'rcl_update_profile_fields', $user_id );
}

/* 16.0.0 */
function rcl_get_profile_fields( $args = false ) {

	$fields = get_site_option( 'rcl_profile_fields' );

	$fields = apply_filters( 'rcl_profile_fields', $fields, $args );

	$profileFields = array();

	if ( $fields ) {

		foreach ( $fields as $k => $field ) {

			if ( isset( $args['include'] ) && ! in_array( $field['slug'], $args['include'] ) ) {

				continue;
			}

			if ( isset( $args['exclude'] ) && in_array( $field['slug'], $args['exclude'] ) ) {

				continue;
			}

			$profileFields[] = $field;
		}
	}

	return $profileFields;
}

function rcl_get_profile_field( $field_id ) {

	$fields = rcl_get_profile_fields( array( 'include' => array( $field_id ) ) );

	return $fields[0];
}

function rcl_get_area_options() {

	return array(
		'menu'     => get_site_option( 'rcl_fields_area-menu' ),
		'counters' => get_site_option( 'rcl_fields_area-counters' ),
		'actions'  => get_site_option( 'rcl_fields_area-actions' ),
	);
}

function rcl_add_log( $title, $data = false, $force = false ) {

	if ( ! $force && ! rcl_get_option( 'rcl-log' ) ) {
		return false;
	}

	$RclLog = new Rcl_Log();

	$RclLog->insert_title( $title );

	if ( $data ) {
		$RclLog->insert_log( $data );
	}
}

function rcl_get_addon_paths() {

	$paths = array(
		RCL_TAKEPATH . 'add-on',
		RCL_PATH . 'add-on'
	);

	return apply_filters( 'rcl_addon_paths', $paths );
}

function rcl_get_tab_permalink( $user_id, $tab_id, $subtab_id = false ) {
	return rcl_format_url( rcl_get_user_url( $user_id ), $tab_id, $subtab_id );
}

function rcl_get_option( $option, $default = false ) {
	global $rcl_options;

	$pre = apply_filters( "rcl_pre_option_{$option}", false, $option, $default );

	if ( false !== $pre ) {
		return $pre;
	}

	if ( ! $rcl_options ) {
		$rcl_options = get_site_option( 'rcl_global_options' );
	}

	if ( isset( $rcl_options[ $option ] ) ) {
		if ( $rcl_options[ $option ] || is_numeric( $rcl_options[ $option ] ) ) {
			return $rcl_options[ $option ];
		}
	}

	return $default;
}

function rcl_update_option( $name, $value ) {
	global $rcl_options;

	if ( ! $rcl_options ) {
		$rcl_options = get_site_option( 'rcl_global_options' );
	}

	$rcl_options[ $name ] = $value;

	return update_site_option( 'rcl_global_options', $rcl_options );
}

function rcl_delete_option( $name ) {
	global $rcl_options;

	if ( ! $rcl_options ) {
		$rcl_options = get_site_option( 'rcl_global_options' );
	}

	unset( $rcl_options[ $name ] );

	return update_site_option( 'rcl_global_options', $rcl_options );
}

function rcl_get_commerce_option( $option, $default = false ) {
	global $rmag_options;

	if ( ! $rmag_options ) {
		$rmag_options = get_site_option( 'primary-rmag-options' );
	}

	if ( isset( $rmag_options[ $option ] ) ) {
		if ( $rmag_options[ $option ] || is_numeric( $rmag_options[ $option ] ) ) {
			return $rmag_options[ $option ];
		}
	}

	return $default;
}

function rcl_update_commerce_option( $name, $value ) {
	global $rmag_options;

	if ( ! $rmag_options ) {
		$rmag_options = get_site_option( 'primary-rmag-options' );
	}

	$rmag_options[ $name ] = $value;

	return update_site_option( 'primary-rmag-options', $rmag_options );
}

function rcl_delete_commerce_option( $name ) {
	global $rmag_options;

	if ( ! $rmag_options ) {
		$rmag_options = get_site_option( 'primary-rmag-options' );
	}

	unset( $rmag_options[ $name ] );

	return update_site_option( 'primary-rmag-options', $rmag_options );
}

//вывод контента произвольной вкладки
add_filter( 'rcl_custom_tab_content', 'do_shortcode', 11 );
add_filter( 'rcl_custom_tab_content', 'wpautop', 10 );
function rcl_custom_tab_content( $content ) {
	return apply_filters( 'rcl_custom_tab_content', stripslashes_deep( $content ) );
}

add_filter( 'rcl_custom_tab_content', 'rcl_filter_custom_tab_vars', 6 );
function rcl_filter_custom_tab_vars( $content ) {
	global $user_ID, $user_LK;

	$matchs = array(
		'{USERID}'   => $user_ID,
		'{MASTERID}' => $user_LK
	);

	$matchs = apply_filters( 'rcl_custom_tab_vars', $matchs );

	if ( ! $matchs ) {
		return $content;
	}

	return strtr( $content, $matchs );
}

add_filter( 'rcl_custom_tab_content', 'rcl_filter_custom_tab_usermetas', 5 );
function rcl_filter_custom_tab_usermetas( $content ) {
	global $rcl_office;

	preg_match_all( '/{RCL-UM:([^}]+)}/', $content, $metas );

	if ( ! $metas[1] ) {
		return $content;
	}

	$tblUsers = [
		'display_name',
		'user_url',
		'user_login',
		'user_nicename',
		'user_email',
		'user_registered'
	];

	$matchs = array();

	foreach ( $metas[1] as $meta ) {

		if ( in_array( $meta, $tblUsers ) ) {
			$value = get_the_author_meta( $meta, $rcl_office );
		} else {
			$value = get_user_meta( $rcl_office, $meta, 1 );
		}

		if ( ! $value ) {
			$value = __( 'not specified', 'wp-recall' );
		}

		$matchs[ '{RCL-UM:' . $meta . '}' ] = ( is_array( $value ) ) ? implode( ', ', $value ) : $value;
	}

	return strtr( $content, $matchs );
}

/* * * */
function rcl_get_form( $args ) {
	$Form = new Rcl_Form( $args );

	return $Form->get_form();
}

add_action( 'delete_user', 'rcl_delete_user_action', 10 );
function rcl_delete_user_action( $user_id ) {
	global $wpdb;

	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	return $wpdb->query( $wpdb->prepare( "DELETE FROM " . RCL_PREF . "user_action WHERE user = %d", $user_id ) );
}

add_action( 'delete_user', 'rcl_delete_user_avatar', 10 );
function rcl_delete_user_avatar( $user_id ) {
	array_map( "unlink", glob( RCL_UPLOAD_URL . 'avatars/' . $user_id . '-*.jpg' ) );
}

function rcl_is_gutenberg() {
	if ( ! is_admin() ) {
		return false;
	}

	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	if ( get_current_screen()->base !== 'post' ) {
		return false;
	}

	if ( isset( $_GET['classic-editor'] ) ) {
		return false;
	}

	/* if ( ! gutenberg_can_edit_post( $post ) ) {
	  return false;
	  } */

	// Gutenberg plugin is installed and activated.
	$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

	// Block editor since 5.0.
	$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

	if ( ! $gutenberg && ! $block_editor ) {
		return false;
	}

	/* if ( self::is_classic_editor_plugin_active() ) {
	  $editor_option       = get_site_option( 'classic-editor-replace' );
	  $block_editor_active = array( 'no-replace', 'block' );

	  return in_array( $editor_option, $block_editor_active, true );
	  } */

	return true;
}

function rcl_get_notice( $args ) {
	require_once 'classes/class-rcl-notice.php';
	$Notice = new Rcl_Notice( $args );

	return $Notice->get_notice();
}

//getting array of pages IDs and titles
//for using in settings: ID => post_title
function rcl_get_pages_ids() {

	$pages = RQ::tbl( new Rcl_Posts_Query() )->select( [ 'ID', 'post_title' ] )
	           ->where( [ 'post_type' => 'page', 'post_status' => 'publish' ] )
	           ->limit( - 1 )
	           ->orderby( 'post_title', 'ASC' )
	           ->get_walker()->get_index_values( 'ID', 'post_title' );

	return array( __( 'Not selected', 'wp-recall' ) ) + $pages;
}

/**
 * @param $beatName
 * @param $actions - array with allowed function callbacks for beat
 */
function rcl_init_beat( $beatName, $actions = [] ) {
	global $rcl_beats;
	$rcl_beats[ $beatName ] = [
		'actions' => $actions
	];
}

function rcl_beat_action_exist( $beatName, $action ) {
	global $rcl_beats;

	$beat_actions = $rcl_beats[ $beatName ]['actions'] ?? [];

	return in_array( $action, $beat_actions );
}

function rcl_recursive_map( $callback, $data ) {

	if ( is_array( $data ) ) {
		foreach ( $data as $k => $v ) {
			$data[ $k ] = rcl_recursive_map( $callback, $v );
		}
	} else {
		$data = is_scalar( $data ) ? $callback( $data ) : $data;
	}

	return $data;
}

function rcl_kses_allowed_html() {

	static $html_tags = [];

	if ( ! $html_tags ) {
		$html_tags = array_merge_recursive(
			wp_kses_allowed_html( 'post' ),
			[
				'script'   => [
					'type'    => [],
					'src'     => [],
					'charset' => [],
					'async'   => [],
				],
				'a'        => [ 'onclick' => true ],
				'style'    => [ 'type' => [] ],
				'input'    => [
					'type'        => true,
					'id'          => true,
					'class'       => true,
					'value'       => true,
					'onclick'     => true,
					'placeholder' => true,
					'pattern'     => true,
					'name'        => true,
					'data-*'      => true,
					'checked'     => true,
					'min'         => true,
					'max'         => true,
					'step'        => true,
					'maxlength'   => true,
					'size'        => true,
					'required'    => true
				],
				'select'   => [
					'id'       => true,
					'class'    => true,
					'name'     => true,
					'data-*'   => true,
					'required' => true,
					'multiple' => true
				],
				'option'   => [
					'data-key' => true,
					'selected' => true,
					'value'    => true
				],
				'textarea' => [
					'required'  => true,
					'maxlength' => true,
					'onkeyup'   => true
				],
				'form'     => [
					'method'   => true,
					'action'   => true,
					'enctype'  => true,
					'id'       => true,
					'class'    => true,
					'name'     => true,
					'data-*'   => true,
					'onsubmit' => true,
					'target'   => true
				],
				'span'     => [ 'onclick' => true ],
				'div'      => [ 'onclick' => true, 'account' => true ],
				'link'     => [
					'rel'   => true,
					'id'    => true,
					'href'  => true,
					'media' => true
				]
			]
		);
	}

	return $html_tags;
}