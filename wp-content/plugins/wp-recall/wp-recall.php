<?php
/*
  Plugin Name: WP-Recall
  Plugin URI: https://codeseller.ru/?p=69
  Description: Фронт-енд профиль, система личных сообщений и рейтинг пользователей на сайте вордпресс.
  Version: 16.25.9
  Author: Plechev Andrey
  Author URI: https://codeseller.ru/
  Text Domain: wp-recall
  Domain Path: /languages
  GitHub Plugin URI: https://github.com/plechev-64/wp-recall-current
  License: GPLv2 or later (license.txt)
 */

/*  Copyright 2012  Plechev Andrey  (email : support {at} codeseller.ru)  */

final class WP_Recall {

	public $version = '16.25.9';
	public $child_addons = array();
	public $need_update = false;
	public $fields = array();
	protected static $_instance = null;

	/*
	 * Основной экземпляр класса WP_Recall
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Are you cheating, bastard?' ), esc_attr( $this->version ) );
	}

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Are you cheating, bastard?' ), esc_attr( $this->version ) );
	}

	/*
	 * Тут происходит магия
	 * Будем возвращать методы класса WP_Recall через переменные класса.
	 */
	public function __get( $key ) {

		/*
		 * Пока что только метод для отправки писем
		 */
		if ( $key == 'mailer' ) {
			return $this->$key();
		}
	}

	/*
	 * Конструктор нашего WP_Recall
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ), 10 );

		$this->define_constants(); //Определяем константы.
		$this->includes(); //Подключаем все нужные файлы с функциями и классами
		$this->init_hooks(); //Тут все наши хуки

		do_action( 'wp_recall_loaded' ); //Оставляем кручёк
	}

	private function init_hooks() {

		register_activation_hook( __FILE__, array( 'RCL_Install', 'install' ) );

		add_action( 'init', array( $this, 'init' ), 0 );

		if ( is_admin() ) {
			add_action( 'save_post', 'rcl_postmeta_update', 0 );
			add_action( 'admin_init', 'rcl_admin_scripts', 10 );
		} else {
			add_action( 'rcl_enqueue_scripts', 'rcl_frontend_scripts', 1 );
			add_action( 'wp_head', 'rcl_update_timeaction_user', 10 );
		}
	}

	private function define_constants() {
		global $wpdb, $rcl_options;

		$upload_dir = $this->upload_dir();

		$this->define( 'VER_RCL', $this->version );

		$this->define( 'RCL_URL', $this->plugin_url() . '/' );
		$this->define( 'RCL_PREF', $wpdb->base_prefix . 'rcl_' );

		$this->define( 'RCL_PATH', trailingslashit( $this->plugin_path() ) );

		$this->define( 'RCL_UPLOAD_PATH', $upload_dir['basedir'] . '/rcl-uploads/' );
		$this->define( 'RCL_UPLOAD_URL', $upload_dir['baseurl'] . '/rcl-uploads/' );

		$this->define( 'RCL_TAKEPATH', WP_CONTENT_DIR . '/wp-recall/' );

		$this->define( 'RCL_SERVICE_HOST', 'http://downloads.codeseller.ru' );

		$rcl_options = get_site_option( 'rcl_global_options' );
	}

	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/*
	 * Узнаём тип запроса
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	public function includes() {
		/*
		 * Здесь подключим те фалы которые нужны глобально для плагина
		 * Остальные распихаем по соответсвующим функциям
		 */

		require_once 'classes/class-rcl-cache.php';
		require_once 'classes/class-rcl-custom-fields.php';
		require_once 'classes/class-rcl-custom-fields-manager.php';

		require_once 'classes/query/class-rcl-old-query.php';
		require_once 'classes/query/class-rcl-query.php';
		require_once 'classes/query/class-rq.php';

		require_once 'classes/class-rcl-query-tables.php';

		require_once 'classes/fields/class-rcl-field-abstract.php';
		require_once 'classes/fields/class-rcl-field.php';
		require_once 'classes/fields/class-rcl-fields.php';
		require_once 'classes/fields/class-rcl-fields-manager.php';
		require_once 'classes/fields/types/class-rcl-field-agree.php';
		require_once 'classes/fields/types/class-rcl-field-checkbox.php';
		require_once 'classes/fields/types/class-rcl-field-color.php';
		require_once 'classes/fields/types/class-rcl-field-custom.php';
		require_once 'classes/fields/types/class-rcl-field-date.php';
		require_once 'classes/fields/types/class-rcl-field-dynamic.php';
		require_once 'classes/fields/types/class-rcl-field-editor.php';
		require_once 'classes/fields/types/class-rcl-field-select.php';
		require_once 'classes/fields/types/class-rcl-field-multiselect.php';
		require_once 'classes/fields/types/class-rcl-field-radio.php';
		require_once 'classes/fields/types/class-rcl-field-range.php';
		require_once 'classes/fields/types/class-rcl-field-runner.php';
		require_once 'classes/fields/types/class-rcl-field-text.php';
		require_once 'classes/fields/types/class-rcl-field-tel.php';
		require_once 'classes/fields/types/class-rcl-field-number.php';
		require_once 'classes/fields/types/class-rcl-field-textarea.php';
		require_once 'classes/fields/types/class-rcl-field-uploader.php';
		require_once 'classes/fields/types/class-rcl-field-file.php';
		require_once 'classes/fields/types/class-rcl-field-hidden.php';

		require_once 'classes/class-rcl-user.php';
		require_once 'classes/class-rcl-form.php';
		require_once 'classes/class-rcl-walker.php';
		require_once 'classes/class-rcl-includer.php';
		require_once 'classes/class-rcl-pagenavi.php';
		require_once 'classes/class-rcl-install.php';
		require_once 'classes/class-rcl-log.php';
		require_once 'classes/class-rcl-table.php';
		require_once 'classes/class-rcl-button.php';
		require_once 'classes/class-rcl-uploader.php';

		require_once 'functions/activate.php';
		require_once 'functions/ajax.php';
		require_once 'functions/files.php';
		require_once 'functions/plugin-pages.php';
		require_once 'functions/addons.php';
		require_once 'functions/addons-update.php';
		require_once 'functions/enqueue-scripts.php';
		require_once 'functions/cron.php';
		require_once 'functions/loginform.php';
		require_once 'functions/currency.php';
		require_once 'functions/functions-media.php';
		require_once 'functions/deprecated.php';
		require_once 'functions/shortcodes.php';

		require_once 'rcl-functions.php';
		require_once 'rcl-widgets.php';

		require_once "functions/frontend.php";

		if ( $this->is_request( 'admin' ) ) {
			$this->admin_includes();
		}

		if ( $this->is_request( 'ajax' ) ) {
			$this->ajax_includes();
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}

		$this->include_addons();
	}

	/*
	 * Сюда складываем все файлы для админки
	 */
	public function admin_includes() {
		require_once 'admin/index.php';
	}

	/*
	 * Сюда складываем все файлы AJAX
	 */
	public function ajax_includes() {

	}

	/*
	 * Сюда складываем все файлы для фронт-энда
	 */
	public function frontend_includes() {
		//require_once "functions/frontend.php";
	}

	public function init() {
		global $user_ID;

		do_action( 'wp_recall_before_init' );

		$this->fields_init();

		if ( ! $user_ID ) {

			//тут подключаем файлы необходимые для регистрации и авторизации
			require_once 'functions/register.php';
			require_once 'functions/authorize.php';

			if ( class_exists( 'ReallySimpleCaptcha' ) ) {
				require_once 'functions/captcha.php';
			}

			if ( ! rcl_get_option( 'login_form_recall' ) ) {
				add_action( 'wp_footer', 'rcl_login_form', 5 );
			}
		}

		if ( $this->is_request( 'frontend' ) ) {

			if ( rcl_get_option( 'view_recallbar' ) ) {
				require_once( 'functions/recallbar.php' );
			}

			$this->init_frontend_globals();
		}

		if ( ! rcl_get_option( 'security-key' ) ) {
			rcl_update_option( 'security-key', wp_generate_password( 20, false ) );
		}

		do_action( 'rcl_init' );
	}

	function init_frontend_globals() {
		global $wpdb, $user_LK, $rcl_userlk_action, $user_ID, $rcl_office, $rcl_user_URL, $rcl_current_action, $wp_rewrite;

		if ( $user_ID ) {
			$rcl_user_URL       = rcl_get_user_url( $user_ID );
			$rcl_current_action = rcl_get_time_user_action( $user_ID );
		}

		$user_LK = 0;

		//если вывод ЛК через шорткод
		if ( rcl_get_option( 'view_user_lk_rcl' ) == 1 ) {

			$get     = rcl_get_option( 'link_user_lk_rcl', 'user' );
			$user_LK = ( isset( $_GET[ $get ] ) ) ? intval( $_GET[ $get ] ) : false;

			if ( ! $user_LK ) {
				$post_id = url_to_postid( filter_input( INPUT_SERVER, 'REQUEST_URI' ) );
				if ( rcl_get_option( 'lk_page_rcl' ) == $post_id ) {
					$user_LK = $user_ID;
				}
			}
		} else { //если ЛК выводим через author.php
			if ( '' == get_site_option( 'permalink_structure' ) ) {

				if ( isset( $_GET[ $wp_rewrite->author_base ] ) ) {
					$user_LK = intval( $_GET[ $wp_rewrite->author_base ] );
				}
			}

			if ( '' !== get_site_option( 'permalink_structure' ) || ! $user_LK ) {

				$nicename = false;

				$url    = ( isset( $_SERVER['SCRIPT_URL'] ) ) ? filter_input( INPUT_SERVER, 'SCRIPT_URL' ) : filter_input( INPUT_SERVER, 'REQUEST_URI' );
				$url    = preg_replace( '/\?.*/', '', $url );
				$url_ar = explode( '/', $url );

				foreach ( $url_ar as $key => $u ) {
					if ( $u != $wp_rewrite->author_base ) {
						continue;
					}
					$nicename = $url_ar[ $key + 1 ];
					break;
				}

				if ( ! $nicename ) {
					return false;
				}

				$user_LK = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->prefix . "users WHERE user_nicename=%s", $nicename ) );
			}
		}

		$user_LK = $user_LK && get_user_by( 'id', $user_LK ) ? $user_LK : 0;

		$rcl_office = $user_LK;

		if ( $user_LK && $user_LK != $user_ID ) {
			$rcl_userlk_action = rcl_get_time_user_action( $user_LK );
		} else if ( $user_LK && $user_LK == $user_ID ) {
			$rcl_userlk_action = $rcl_current_action;
		}
	}

	function fields_init() {

		$this->fields = apply_filters( 'rcl_fields', array(
			'text'        => array(
				'label' => __( 'Text', 'wp-recall' ),
				'class' => 'Rcl_Field_Text'
			),
			'time'        => array(
				'label' => __( 'Time', 'wp-recall' ),
				'class' => 'Rcl_Field_Text'
			),
			'hidden'      => array(
				'label' => __( 'Hidden field', 'wp-recall' ),
				'class' => 'Rcl_Field_Hidden'
			),
			'password'    => array(
				'label' => __( 'Password', 'wp-recall' ),
				'class' => 'Rcl_Field_Text'
			),
			'url'         => array(
				'label' => __( 'Url', 'wp-recall' ),
				'class' => 'Rcl_Field_Text'
			),
			'textarea'    => array(
				'label' => __( 'Multiline text area', 'wp-recall' ),
				'class' => 'Rcl_Field_TextArea'
			),
			'select'      => array(
				'label' => __( 'Select', 'wp-recall' ),
				'class' => 'Rcl_Field_Select'
			),
			'multiselect' => array(
				'label' => __( 'MultiSelect', 'wp-recall' ),
				'class' => 'Rcl_Field_MultiSelect'
			),
			'checkbox'    => array(
				'label' => __( 'Checkbox', 'wp-recall' ),
				'class' => 'Rcl_Field_Checkbox'
			),
			'radio'       => array(
				'label' => __( 'Radiobutton', 'wp-recall' ),
				'class' => 'Rcl_Field_Radio'
			),
			'email'       => array(
				'label' => __( 'E-mail', 'wp-recall' ),
				'class' => 'Rcl_Field_Text'
			),
			'tel'         => array(
				'label' => __( 'Phone', 'wp-recall' ),
				'class' => 'Rcl_Field_Tel'
			),
			'number'      => array(
				'label' => __( 'Number', 'wp-recall' ),
				'class' => 'Rcl_Field_Number'
			),
			'date'        => array(
				'label' => __( 'Date', 'wp-recall' ),
				'class' => 'Rcl_Field_Date'
			),
			'agree'       => array(
				'label' => __( 'Agreement', 'wp-recall' ),
				'class' => 'Rcl_Field_Agree'
			),
			'file'        => array(
				'label' => __( 'File', 'wp-recall' ),
				'class' => 'Rcl_Field_File'
			),
			'dynamic'     => array(
				'label' => __( 'Dynamic', 'wp-recall' ),
				'class' => 'Rcl_Field_Dynamic'
			),
			'runner'      => array(
				'label' => __( 'Runner', 'wp-recall' ),
				'class' => 'Rcl_Field_Runner'
			),
			'range'       => array(
				'label' => __( 'Range', 'wp-recall' ),
				'class' => 'Rcl_Field_Range'
			),
			'color'       => array(
				'label' => __( 'Color', 'wp-recall' ),
				'class' => 'Rcl_Field_Color'
			),
			'custom'      => array(
				'label' => __( 'Custom content', 'wp-recall' ),
				'class' => 'Rcl_Field_Custom'
			),
			'editor'      => array(
				'label' => __( 'Text editor', 'wp-recall' ),
				'class' => 'Rcl_Field_Editor'
			),
			'uploader'    => array(
				'label' => __( 'File uploader', 'wp-recall' ),
				'class' => 'Rcl_Field_Uploader'
			)
		) );
	}

	function include_addons() {
		global $active_addons, $rcl_template;

		if ( is_admin() ) {
			global $rcl_error;
			$rcl_error = ( isset( $_GET['error-text'] ) ) ? sanitize_text_field( wp_unslash( $_GET['error-text'] ) ) : '';
			register_shutdown_function( 'rcl_register_shutdown' );
		}

		$active_addons = get_site_option( 'rcl_active_addons' );

		if ( isset( $active_addons[''] ) ) {
			unset( $active_addons[''] );
		}

		$rcl_template = get_site_option( 'rcl_active_template' );

		do_action( 'rcl_before_include_addons' );

		if ( $active_addons ) {

			$addons = array();

			foreach ( $active_addons as $addon => $data ) {

				if ( ! $addon ) {
					unset( $active_addons[ $addon ] );
					continue;
				}

				if ( isset( $data['template'] ) && $rcl_template != $addon ) {
					continue;
				}

				if ( isset( $data['parent-addon'] ) ) {

					if ( isset( $active_addons[ $data['parent-addon'] ] ) ) {
						$this->child_addons[ $data['parent-addon'] ][] = $addon;
					} else {
						unset( $active_addons[ $addon ] );
						$this->need_update = true;
					}

					continue;
				}

				if ( isset( $data['priority'] ) ) {
					$addons[ $data['priority'] ][ $addon ] = $data;
				} else {
					$addons[0][ $addon ] = $data;
				}
			}

			ksort( $addons );

			foreach ( $addons as $priority => $adds ) {

				foreach ( $adds as $addon => $data ) {

					if ( ! $addon ) {
						continue;
					}

					if ( isset( $data['parent-addon'] ) ) {
						continue;
					}

					$this->include_addon( $addon, $data['path'] );
				}
			}


			$this->update_active_addons();
		}

		do_action( 'rcl_addons_included' );
	}

	function update_active_addons() {
		global $active_addons;

		if ( $this->need_update ) {
			update_site_option( 'rcl_active_addons', $active_addons );
		}
	}

	function include_child_addons( $parenID ) {
		global $active_addons;

		if ( ! isset( $this->child_addons[ $parenID ] ) ) {
			return false;
		}

		foreach ( $this->child_addons[ $parenID ] as $addonID ) {

			$child = $active_addons[ $addonID ];

			$this->include_addon( $addonID, $child['path'] );
		}

		return true;
	}

	function include_addon( $addonID, $path ) {
		global $active_addons;

		$path = untrailingslashit( $path );

		if ( file_exists( $path . '/index.php' ) ) {

			rcl_include_addon( $path . '/index.php', $addonID );

			$this->include_child_addons( $addonID );

			return true;
		}

		unset( $active_addons[ $addonID ] );
		$this->need_update = true;

		return false;
	}

	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wp-recall', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	public function mailer() {
		/*
		 * TODO: Сюда добавить подключение класса отправки сообщений
		 */
	}

	public function upload_dir() {

		if ( defined( 'MULTISITE' ) ) {
			$upload_dir = array(
				'basedir' => WP_CONTENT_DIR . '/uploads',
				'baseurl' => WP_CONTENT_URL . '/uploads'
			);
		} else {
			$upload_dir = wp_upload_dir();
		}

		if ( is_ssl() ) {
			$upload_dir['baseurl'] = str_replace( 'http://', 'https://', $upload_dir['baseurl'] );
		}

		return apply_filters( 'wp_recall_upload_dir', $upload_dir, $this );
	}

	public function User() {
		return Rcl_User::instance();
	}

}

/*
 * Возвращает класс WP_Recall
 * @return WP_Recall
 */
function RCL() {
	return WP_Recall::instance();
}

/*
 * Теперь у нас есть глобальная переменная $wprecall
 * Которая содержит в себе основной класс WP_Recall
 */
$GLOBALS['wprecall'] = RCL();
function wp_recall() {
	global $user_LK;

	do_action( 'rcl_area_before' );
	?>

    <div id="rcl-office" <?php rcl_office_class(); ?> data-account="<?php echo (int) $user_LK; ?>">

		<?php do_action( 'rcl_area_notice' ); ?>

		<?php rcl_include_template_office(); ?>

    </div>

	<?php
	do_action( 'rcl_area_after' );
}
