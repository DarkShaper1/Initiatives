<?php
require_once 'classes/class-prime-form-manager.php';
require_once 'classes/class-prime-manager.php';

add_action( 'admin_init', 'pfm_admin_scripts', 10 );
function pfm_admin_scripts() {
	wp_enqueue_style( 'pfm-admin-style', rcl_addon_url( 'admin/style.css', __FILE__ ), false, VER_RCL );
	wp_enqueue_script( 'pfm-admin-script', rcl_addon_url( 'admin/js/scripts.js', __FILE__ ), false, VER_RCL );
}

add_action( 'admin_menu', 'pfm_init_admin_menu', 10 );
function pfm_init_admin_menu() {
	global $rcl_update_notice;

	$cnt = isset( $rcl_update_notice['prime-forum'] ) ? count( $rcl_update_notice['prime-forum'] ) : 0;

	$notice = ( $cnt ) ? ' <span class="update-plugins count-' . $cnt . '"><span class="plugin-count">' . $cnt . '</span></span>' : '';

	add_menu_page( 'PrimeForum', 'PrimeForum', 'manage_options', 'pfm-menu', 'pfm_page_options' );
	add_submenu_page( 'pfm-menu', __( 'Settings', 'wp-recall' ), __( 'Settings', 'wp-recall' ), 'manage_options', 'pfm-menu', 'pfm_page_options' );
	add_submenu_page( 'pfm-menu', __( 'Structure', 'wp-recall' ), __( 'Structure', 'wp-recall' ), 'manage_options', 'pfm-forums', 'pfm_page_forums' );
	$hook = add_submenu_page( 'pfm-menu', __( 'Templates', 'wp-recall' ) . $notice, __( 'Templates', 'wp-recall' ) . $notice, 'manage_options', 'pfm-themes', 'pfm_page_themes' );
	add_action( "load-$hook", 'pfm_add_options_themes_manager' );
	add_submenu_page( 'pfm-menu', __( 'Topic form', 'wp-recall' ), __( 'Topic form', 'wp-recall' ), 'manage_options', 'manage-topic-form', 'pfm_page_topic_form' );
}

function pfm_add_options_themes_manager() {
	global $Prime_Themes_Manager;

	require_once 'themes-manager.php';

	$option = 'per_page';
	$args   = array(
		'label'   => __( 'Templates', 'wp-recall' ),
		'default' => 100,
		'option'  => 'templates_per_page'
	);

	add_screen_option( $option, $args );
	$Prime_Themes_Manager = new Prime_Themes_Manager();

	do_action( 'pfm_init_themes_manager' );
}

function pfm_page_topic_form() {

	$group_id = ( isset( $_GET['group-id'] ) ) ? intval( $_GET['group-id'] ) : 0;
	$forum_id = ( isset( $_GET['forum-id'] ) ) ? intval( $_GET['forum-id'] ) : 0;

	if ( ! $group_id ) {

		$group_id = RQ::tbl( new PrimeGroups() )
		              ->select( [ 'group_id' ] )
		              ->orderby( 'group_id', 'ASC' )->get_var();
	}

	if ( ! $group_id ) {
		echo '<p>' . esc_html__( 'The forum is not yet created any groups of forums', 'wp-recall' ) . '.</p>'
		     . '<p>' . esc_html__( 'Create a group of forums for managing the form fields of the publication of a topic', 'wp-recall' ) . '.</p>';

		return;
	}

	rcl_sortable_scripts();

	$formManager = new Prime_Form_Manager( array(
		'forum_id' => $forum_id,
		'group_id' => $group_id
	) );

	$content = '<h2>' . esc_html__( 'Manage topic form', 'wp-recall' ) . '</h2>'
	           . '<p>' . esc_html__( 'Select a forum group and manage custom fields form of publication of a topic within this group', 'wp-recall' ) . '</p>';

	$content .= $formManager->form_navi();

	$content .= $formManager->get_manager();

	echo wp_kses( $content, rcl_kses_allowed_html() );
}

function pfm_page_options() {
	require_once RCL_PATH . 'admin/classes/class-rcl-options-manager.php';

	$pages = rcl_get_pages_ids();

	$Manager = new Rcl_Options_Manager( array(
		'option_name'  => 'rcl_pforum_options',
		'page_options' => 'pfm-menu',
	) );

	$Manager->add_box( 'primary', array(
		'title' => __( 'General settings', 'wp-recall' ),
		'icon'  => 'fa-cogs'
	) )->add_group( 'primary' )->add_options( array(
		array(
			'type'   => 'select',
			'slug'   => 'home-page',
			'title'  => __( 'Forum page', 'wp-recall' ),
			'notice' => __( 'Select the needed page from the list and place the [prime-forum] shortcode on it', 'wp-recall' ),
			'values' => $pages
		),
		array(
			'type'   => 'select',
			'slug'   => 'forum-colors',
			'title'  => __( 'Forum colours', 'wp-recall' ),
			'values' => array(
				__( 'By default', 'wp-recall' ),
				__( 'Primary colours of WP-Recall', 'wp-recall' )
			)
		),
		array(
			'type'   => 'select',
			'slug'   => 'view-forums-home',
			'title'  => __( 'Output all forums of the group on the homepage', 'wp-recall' ),
			'notice' => __( 'If selected, all forums will be displayed on the homepage', 'wp-recall' ),
			'values' => array(
				__( 'Do not output', 'wp-recall' ),
				__( 'Output', 'wp-recall' )
			)
		),
		array(
			'type'    => 'text',
			'slug'    => 'forums-home-list',
			'pattern' => '([0-9,\s]+)',
			'title'   => __( 'Output forums only for the specified groups', 'wp-recall' ),
			'notice'  => __( 'If output of forums on the homepage is turned on, you may specify IDs of the groups, whose forums should be output, space separated', 'wp-recall' )
		),
		array(
			'type'       => 'runner',
			'slug'       => 'forums-per-page',
			'title'      => __( 'Forums on the group page', 'wp-recall' ),
			'value_min'  => 5,
			'value_max'  => 50,
			'value_step' => 1,
			'default'    => 20
		),
		array(
			'type'       => 'runner',
			'slug'       => 'topics-per-page',
			'title'      => __( 'Topics on the forum page', 'wp-recall' ),
			'value_min'  => 5,
			'value_max'  => 70,
			'value_step' => 1,
			'default'    => 20
		),
		array(
			'type'       => 'runner',
			'slug'       => 'posts-per-page',
			'title'      => __( 'Messages on the topic page', 'wp-recall' ),
			'value_min'  => 5,
			'value_max'  => 100,
			'value_step' => 1,
			'default'    => 20
		),
		array(
			'type'   => 'select',
			'slug'   => 'guest-post-create',
			'title'  => __( 'Publishing of messages in the topic by guests', 'wp-recall' ),
			'values' => array(
				__( 'Forbidden', 'wp-recall' ),
				__( 'Allowed', 'wp-recall' )
			)
		),
		array(
			'type'    => 'select',
			'slug'    => 'reason-edit',
			'title'   => __( 'Reason for editing a message', 'wp-recall' ),
			'default' => 1,
			'values'  => array(
				__( 'Forbidden', 'wp-recall' ),
				__( 'Allowed', 'wp-recall' )
			)
		),
		array(
			'type'       => 'runner',
			'slug'       => 'beat-time',
			'title'      => __( 'Delay on receiving a new message via AJAX', 'wp-recall' ),
			'value_min'  => 0,
			'value_max'  => 120,
			'value_step' => 1,
			'default'    => 30,
			'notice'     => __( 'In seconds. New messages in the forum topic are loaded from AJAX only for those who have already left a message in this topic. If 0 is specified, the AJAX loading is disabled', 'wp-recall' )
		),
		array(
			'type'       => 'runner',
			'slug'       => 'beat-inactive',
			'title'      => __( 'Limit of requests to receive new messages', 'wp-recall' ),
			'value_min'  => 10,
			'value_max'  => 200,
			'value_step' => 1,
			'default'    => 100,
			'notice'     => __( 'If the loading of new messages via AJAX is enabled, here we set the maximum number of requests from one user, after which they are terminated, after the publication of a new message requests are resumed', 'wp-recall' )
		),
	) );

	$Manager->add_box( 'content', array(
		'title' => __( 'Content of topic`s', 'wp-recall' )
	) )->add_group( 'content' )->add_options( array(
		array(
			'type'   => 'select',
			'slug'   => 'view-links',
			'title'  => __( 'The display of links in messages', 'wp-recall' ),
			'values' => array(
				__( 'Hiding for guests', 'wp-recall' ),
				__( 'Show for all', 'wp-recall' )
			)
		),
		array(
			'type'   => 'textarea',
			'slug'   => 'support-shortcodes',
			'title'  => __( 'Supported shortcodes', 'wp-recall' ),
			'notice' => __( 'Specify the necessary shortcodes to support them in forum messages, each should start from a new line. Specify without brackets, for example: custom-shortcode', 'wp-recall' )
		),
		array(
			'type'   => 'select',
			'slug'   => 'support-oembed',
			'title'  => __( 'Support of OEMBED in messages', 'wp-recall' ),
			'values' => array(
				__( 'Forbidden', 'wp-recall' ),
				__( 'Allowed', 'wp-recall' )
			)
		),
	) );

	$Manager->add_box( 'templates', array(
		'title' => __( 'Names of templates', 'wp-recall' )
	) )->add_group( 'templates' )->add_options( array(
		array(
			'type'    => 'custom',
			'title'   => __( 'Templates to form the title tag and name of the page', 'wp-recall' ),
			'content' => __(
				'<p>The following masks may be specified in templates:<br>'
				. '%GROUPNAME% - name of the current group of forums<br>'
				. '%FORUMNAME% - name of the current forum<br>'
				. '%TOPICNAME% - name of the current topic</p>'
				, 'wp-recall'
			)
		),
		array(
			'type'    => 'text',
			'slug'    => 'mask-tag-group',
			'title'   => __( 'Title tag in the group of forums', 'wp-recall' ),
			'default' => __( 'Group of forums', 'wp-recall' ) . ' %GROUPNAME%'
		),
		array(
			'type'    => 'text',
			'slug'    => 'mask-page-group',
			'title'   => __( 'Name of the page in the group of forums', 'wp-recall' ),
			'default' => __( 'Group of forums', 'wp-recall' ) . ' %GROUPNAME%'
		),
		array(
			'type'    => 'text',
			'slug'    => 'mask-tag-forum',
			'title'   => __( 'Title tag on the forum page', 'wp-recall' ),
			'default' => __( 'Forum', 'wp-recall' ) . ' %FORUMNAME%'
		),
		array(
			'type'    => 'text',
			'slug'    => 'mask-page-forum',
			'title'   => __( 'Name of the page of the separate forum', 'wp-recall' ),
			'default' => __( 'Forum', 'wp-recall' ) . ' %FORUMNAME%'
		),
		array(
			'type'    => 'text',
			'slug'    => 'mask-tag-topic',
			'title'   => __( 'Title tag on the topic page', 'wp-recall' ),
			'default' => '%TOPICNAME% | ' . __( 'Forum', 'wp-recall' ) . ' %FORUMNAME%'
		),
		array(
			'type'    => 'text',
			'slug'    => 'mask-page-topic',
			'title'   => __( 'Name of the page of the separate topic', 'wp-recall' ),
			'default' => '%TOPICNAME%'
		),
	) );

	$Manager->add_box( 'notices', array(
		'title' => __( 'Notifications', 'wp-recall' )
	) )->add_group( 'notices' )->add_options( array(
		array(
			'type'   => 'select',
			'slug'   => 'admin-notes',
			'title'  => __( 'Notification to the administrator about new topics', 'wp-recall' ),
			'values' => array(
				__( 'Disabled', 'wp-recall' ),
				__( 'Enabled', 'wp-recall' )
			)
		),
		array(
			'type'   => 'select',
			'slug'   => 'author-notes',
			'title'  => __( 'Notice the author of the theme about new answers', 'wp-recall' ),
			'values' => array(
				__( 'Disabled', 'wp-recall' ),
				__( 'Enabled', 'wp-recall' )
			),
			'notice' => __( 'The notice sent for each new message in the topic only when the topic`s author is offline', 'wp-recall' )
		)
	) );

	$Manager = apply_filters( 'pfm_options', $Manager );

	//support old additional options
	if ( $moreOptions = apply_filters( 'pfm_options_array', array() ) ) {
		$Manager->add_box( 'other', array(
			'title' => esc_html__( 'Other settings', 'wp-recall' )
		) )->add_group( 'options' )->add_options( $moreOptions );
	}

	$content = '<h2>' . esc_html__( 'Settings of PrimeForum', 'wp-recall' ) . '</h2>';

	$content .= $Manager->get_content();

	echo wp_kses( $content, rcl_kses_allowed_html() );
}

add_action( 'admin_init', 'pfm_flush_rewrite_rules' );
function pfm_flush_rewrite_rules() {

	if ( isset( $_POST['rcl_pforum_options'] ) ) {
		flush_rewrite_rules();
	}
}

function pfm_page_forums() {

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
	?>

    <h2><?php esc_html_e( 'Manage forums', 'wp-recall' ); ?></h2>

	<?php
	$manager = new PrimeManager();

	echo wp_kses( $manager->get_manager(), rcl_kses_allowed_html() );
}

function pfm_page_themes() {

	global $Prime_Themes_Manager;

	rcl_dialog_scripts();

	$Prime_Themes_Manager->get_templates_data();

	$cnt_all = $Prime_Themes_Manager->template_number;

	echo '<div class="wrap">';

	echo '<div id="icon-plugins" class="icon32"><br></div>
        <h2>' . esc_html__( 'Templates', 'wp-recall' ) . ' PrimeForum</h2>';

	if ( isset( $_POST['save-rcl-key'], $_POST['_wpnonce'], $_POST['rcl-key'] ) ) {
		if ( wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add-rcl-key' ) ) {
			update_site_option( 'rcl-key', sanitize_text_field( wp_unslash( $_POST['rcl-key'] ) ) );
			echo '<div id="message"><p>' . esc_html__( 'Key has been saved', 'wp-recall' ) . '!</p></div>';
		}
	}

	echo '<div class="rcl-admin-service-box rcl-key-box">';
	//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<h4>' . esc_html__( 'RCLKEY', 'wp-recall' ) . '</h4>
        <form action="" method="post">
            ' . esc_html__( 'Enter RCLKEY', 'wp-recall' ) . ' <input type="text" name="rcl-key" value="' . esc_attr( get_site_option( 'rcl-key' ) ) . '">
            <input class="button" type="submit" value="' . esc_attr__( 'Save', 'wp-recall' ) . '" name="save-rcl-key">
            ' . wp_nonce_field( 'add-rcl-key', '_wpnonce', true, false ) . '
        </form>
        <p class="install-help">' . esc_html__( 'Required to update the templates here. Get it  in  your account online', 'wp-recall' ) . ' <a href="https://codeseller.ru/" target="_blank">https://codeseller.ru</a></p>';

	echo '</div>';

	echo '<div class="rcl-admin-service-box rcl-upload-form-box upload-template">';

	echo '<h4>' . esc_html__( 'Install the add-on to WP-Recall format .ZIP', 'wp-recall' ) . '</h4>
        <p class="install-help">' . esc_html__( 'If you have an archive template for wp-recall format .zip, here you can upload and install it', 'wp-recall' ) . '</p>
        <form class="wp-upload-form" action="" enctype="multipart/form-data" method="post">
            <label class="screen-reader-text" for="addonzip">' . esc_html__( 'Add-on archive', 'wp-recall' ) . '</label>
            <input id="addonzip" type="file" name="addonzip">
            <input id="install-plugin-submit" class="button" type="submit" value="' . esc_attr__( 'Install', 'wp-recall' ) . '" name="pfm-install-template-submit">
            ' . wp_nonce_field( 'install-template-pfm', '_wpnonce', true, false ) . '
        </form>

        </div>

        <ul class="subsubsub">
            <li class="all"><b>' . esc_html__( 'All', 'wp-recall' ) . '<span class="count">(' . esc_html( $cnt_all ) . ')</span></b></li>
        </ul>';
	//phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	$Prime_Themes_Manager->prepare_items();
	?>

    <form method="post">
        <input type="hidden" name="page" value="pfm-themes">
	<?php
	$Prime_Themes_Manager->search_box( 'Search by name', 'search_id' );
	$Prime_Themes_Manager->display();
	echo '</form></div>';
}

if ( is_admin() ):
	add_action( 'profile_personal_options', 'pfm_admin_role_field' );
	add_action( 'edit_user_profile', 'pfm_admin_role_field' );
endif;
function pfm_admin_role_field( $user ) {

	$PrimeUser = new PrimeUser( array( 'user_id' => $user->ID ) );

	$values = array();
	foreach ( $PrimeUser->roles as $role => $prop ) {
		$values[ $role ] = $prop['name'];
	}

	$fields = array(
		array(
			'type'    => 'select',
			'title'   => __( 'Current role', 'wp-recall' ),
			'slug'    => 'pfm_role',
			'default' => $PrimeUser->user_role,
			'values'  => $values
		)
	);

	$content = '';
	if ( $fields ) {

		$content = '<h3>' . esc_html__( 'Role of the user on the forum', 'wp-recall' ) . ':</h3>
        <table class="form-table rcl-form">';

		foreach ( $fields as $field ) {

			$fieldObject = Rcl_Field::setup( $field );

			$content .= '<tr><th><label>' . $fieldObject->get_title() . ':</label></th>';
			$content .= '<td>' . $fieldObject->get_field_input() . '</td>';
			$content .= '</tr>';
		}

		$content .= '</table>';
	}

	echo wp_kses( $content, rcl_kses_allowed_html() );
}

add_action( 'personal_options_update', 'pfm_update_user_role' );
add_action( 'edit_user_profile_update', 'pfm_update_user_role' );
function pfm_update_user_role( $user_id ) {

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	if ( ! isset( $_POST['pfm_role'] ) ) {
		return false;
	}

	update_user_meta( $user_id, 'pfm_role', sanitize_key( $_POST['pfm_role'] ) );
}

rcl_ajax_action( 'pfm_ajax_manager_update_data' );
function pfm_ajax_manager_update_data() {

	if ( isset( $_POST['group_id'] ) ) {

		if ( isset( $_POST['forum_id'] ) ) {
			$result = pfm_manager_update_forum( [
				'forum_id'     => absint( $_POST['forum_id'] ),
				'forum_name'   => isset( $_POST['forum_name'] ) ? sanitize_text_field( wp_unslash( $_POST['forum_name'] ) ) : '',
				'forum_desc'   => isset( $_POST['forum_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['forum_desc'] ) ) : '',
				'forum_slug'   => isset( $_POST['forum_slug'] ) ? sanitize_key( $_POST['forum_slug'] ) : '',
				'forum_closed' => isset( $_POST['forum_closed'] ) && intval( $_POST['forum_closed'] ) ? 1 : 0,
				'group_id'     => absint( $_POST['group_id'] )
			] );
		} else {
			$result = pfm_manager_update_group( [
				'group_id'   => absint( $_POST['group_id'] ),
				'group_name' => isset( $_POST['group_name'] ) ? sanitize_text_field( wp_unslash( $_POST['group_name'] ) ) : '',
				'group_slug' => isset( $_POST['group_slug'] ) ? sanitize_key( $_POST['group_slug'] ) : '',
				'group_desc' => isset( $_POST['group_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['group_desc'] ) ) : ''
			] );
		}

		wp_send_json( $result );
	}

	exit;
}

function pfm_manager_update_group( $options ) {

	pfm_update_group( array(
		'group_id'   => $options['group_id'],
		'group_name' => $options['group_name'],
		'group_slug' => $options['group_slug'],
		'group_desc' => $options['group_desc']
	) );

	return array(
		'success' => __( 'Changes saved!', 'wp-recall' ),
		'title'   => $options['group_name'],
		'id'      => $options['group_id']
	);
}

function pfm_manager_update_forum( $options ) {

	$forum = pfm_get_forum( $options['forum_id'] );

	pfm_update_forum( array(
		'forum_id'     => $options['forum_id'],
		'forum_name'   => $options['forum_name'],
		'forum_desc'   => $options['forum_desc'],
		'forum_slug'   => $options['forum_slug'],
		'forum_closed' => $options['forum_closed'],
		'group_id'     => $options['group_id'],
	) );

	$result = array(
		'success' => __( 'Changes saved!', 'wp-recall' ),
		'title'   => $options['forum_name'],
		'id'      => $options['forum_id']
	);

	if ( isset( $options['group_id'] ) && $forum->group_id != $options['group_id'] ) {

		$result['update-page']    = 1;
		$result['preloader_live'] = 1;
	}

	return $result;
}

rcl_ajax_action( 'pfm_ajax_update_sort_groups' );
function pfm_ajax_update_sort_groups() {
	global $wpdb;

	if ( ! isset( $_POST['sort'] ) || ! current_user_can( 'administrator' ) ) {
		wp_send_json( [ 'error' => __( 'Error', 'wp-recall' ) ] );
	}
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$sort = rcl_recursive_map( 'sanitize_text_field', json_decode( wp_unslash( $_POST['sort'] ) ) );

	foreach ( $sort as $s => $group ) {
		$wpdb->update(
			RCL_PREF . 'pforum_groups', array(
			'group_seq' => $s + 1
		), array(
				'group_id' => absint( $group->id )
			)
		);
	}

	wp_send_json( array(
		'success' => esc_html__( 'Changes saved!', 'wp-recall' )
	) );
}

rcl_ajax_action( 'pfm_ajax_update_sort_forums' );
function pfm_ajax_update_sort_forums() {
	global $wpdb;

	if ( ! isset( $_POST['sort'] ) || ! current_user_can( 'administrator' ) ) {
		wp_send_json( [ 'error' => __( 'Error', 'wp-recall' ) ] );
	}

	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$sort = rcl_recursive_map( 'sanitize_text_field', json_decode( wp_unslash( $_POST['sort'] ) ) );

	foreach ( $sort as $s => $forum ) {
		$wpdb->update(
			RCL_PREF . 'pforums', array(
			'parent_id' => $forum->parent,
			'forum_seq' => $s + 1
		), array(
				'forum_id' => absint( $forum->id )
			)
		);
	}

	wp_send_json( array(
		'success' => __( 'Changes saved!', 'wp-recall' )
	) );
}

rcl_ajax_action( 'pfm_ajax_get_manager_item_delete_form' );
function pfm_ajax_get_manager_item_delete_form() {

	/**
	 * todo любой может получить менеджер?
	 */

	$itemType = isset( $_POST['item-type'] ) ? sanitize_key( $_POST['item-type'] ) : '';
	$itemID   = isset( $_POST['item-id'] ) ? absint( $_POST['item-id'] ) : 0;

	$fields = [];

	if ( $itemType == 'groups' ) {

		$groups = pfm_get_groups( array(
			'order'            => 'ASC',
			'orderby'          => 'group_seq',
			'group_id__not_in' => array( $itemID )
		) );

		$values = array( '' => __( 'Delete all forums inside the group', 'wp-recall' ) );

		if ( $groups ) {

			foreach ( $groups as $group ) {
				$values[ $group->group_id ] = $group->group_name;
			}
		}

		$fields = array(
			array(
				'type'   => 'select',
				'slug'   => 'migrate_group',
				'title'  => __( 'New group for child forums', 'wp-recall' ),
				'notice' => __( 'If new group is not assigned for child forums, when deleting the selected '
				                . 'group, the forums will also be deleted', 'wp-recall' ),
				'values' => $values
			),
			array(
				'type'  => 'hidden',
				'slug'  => 'group_id',
				'value' => $itemID
			),
			array(
				'type'  => 'hidden',
				'slug'  => 'pfm-action',
				'value' => 'group_delete'
			)
		);
	} else if ( $itemType == 'forums' ) {

		$forums = pfm_get_forums( array(
			'order'            => 'ASC',
			'orderby'          => 'forum_seq',
			'forum_id__not_in' => array( $itemID )
		) );

		$values = array( '' => __( 'Delete all topic inside the forum', 'wp-recall' ) );

		if ( $forums ) {

			foreach ( $forums as $forum ) {
				$values[ $forum->forum_id ] = $forum->forum_name;
			}
		}

		$fields = array(
			array(
				'type'   => 'select',
				'slug'   => 'migrate_forum',
				'title'  => __( 'New forum for child topics', 'wp-recall' ),
				'notice' => __( 'If new forum is not assigned for child forums, when deleting the selected '
				                . 'forum, the topics will also be deleted', 'wp-recall' ),
				'values' => $values
			),
			array(
				'type'  => 'hidden',
				'slug'  => 'forum_id',
				'value' => $itemID
			),
			array(
				'type'  => 'hidden',
				'slug'  => 'pfm-action',
				'value' => 'forum_delete'
			)
		);
	}

	$form = pfm_get_manager_item_delete_form( $fields );

	wp_send_json( array(
		'form' => $form
	) );
}

function pfm_get_manager_item_delete_form( $fields ) {

	$content = '<div id="manager-deleted-form" class="rcl-custom-fields-box">';
	$content .= '<form method="post">';

	foreach ( $fields as $field ) {

		$fieldObject = Rcl_Field::setup( $field );

		$content .= '<div id="field-' . $field['slug'] . '" class="form-field rcl-custom-field">';

		if ( isset( $field['title'] ) ) {
			$content .= '<label>';
			$content .= $fieldObject->get_title();
			$content .= '</label>';
		}

		$content .= $fieldObject->get_field_input();

		$content .= '</div>';
	}

	$content .= '<div class="form-field fields-submit">';
	$content .= '<input type="submit" class="button-primary" value="' . __( 'Confirm the deletion', 'wp-recall' ) . '">';
	$content .= '</div>';
	$content .= wp_nonce_field( 'pfm-nonce', '_wpnonce', true, false );
	$content .= '</form>';
	$content .= '</div>';

	return $content;
}

function pfm_get_templates() {

	$paths = array(
		rcl_addon_path( __FILE__ ) . 'themes',
		RCL_PATH . 'add-on',
		RCL_TAKEPATH . 'add-on'
	);

	$add_ons = array();
	foreach ( $paths as $path ) {
		if ( file_exists( $path ) ) {
			$addons = scandir( $path, 1 );

			foreach ( ( array ) $addons as $namedir ) {
				$addon_dir = $path . '/' . $namedir;
				$index_src = $addon_dir . '/index.php';
				if ( ! is_dir( $addon_dir ) || ! file_exists( $index_src ) ) {
					continue;
				}
				$info_src = $addon_dir . '/info.txt';
				if ( file_exists( $info_src ) ) {
					$info = file( $info_src );
					$data = rcl_parse_addon_info( $info );

					if ( ! isset( $data['custom-manager'] ) || $data['custom-manager'] != 'prime-forum' ) {
						continue;
					}

					$add_ons[ $namedir ]         = $data;
					$add_ons[ $namedir ]['path'] = $addon_dir;
				}
			}
		}
	}

	return $add_ons;
}

add_action( 'pfm_deleted_group', 'pfm_delete_group_custom_fields', 10 );
function pfm_delete_group_custom_fields( $group_id ) {
	delete_site_option( 'rcl_fields_pfm_group_' . $group_id );
}

add_action( 'pfm_deleted_forum', 'pfm_delete_forum_custom_fields', 10 );
function pfm_delete_forum_custom_fields( $forum_id ) {
	delete_site_option( 'rcl_fields_pfm_forum_' . $forum_id );
}

add_action( 'rcl_add_dashboard_metabox', 'rcl_add_forum_metabox' );
function rcl_add_forum_metabox( $screen ) {
	add_meta_box( 'rcl-forum-metabox', __( 'Last forum topics', 'wp-recall' ), 'rcl_forum_metabox', $screen->id, 'side' );
}

function rcl_forum_metabox() {

	$topics = pfm_get_topics( array( 'number' => 5 ) );

	if ( ! $topics ) {
		echo '<p>' . esc_html__( 'No topics on the forum yet', 'wp-recall' ) . '</p>';

		return;
	}

	echo '<table class="wp-list-table widefat fixed striped">';
	echo '<tr>'
	     . '<th>' . esc_html__( 'Topic', 'wp-recall' ) . '</th>'
	     . '<th>' . esc_html__( 'Messages', 'wp-recall' ) . '</th>'
	     . '<th>' . esc_html__( 'Author', 'wp-recall' ) . '</th>'
	     . '</tr>';
	foreach ( $topics as $topic ) {
		echo '<tr>'
		     . '<td><a href="' . esc_url( pfm_get_topic_permalink( $topic->topic_id ) ) . '" target="_blank">' . esc_html( $topic->topic_name ) . '</a></td>'
		     . '<td>' . esc_html( $topic->post_count ) . '</td>'
		     . '<td>' . esc_html( get_the_author_meta( 'user_login', $topic->user_id ) ) . '</td>'
		     . '</tr>';
	}
	echo '</table>';
	echo '<p><a href="' . esc_url( pfm_get_home_url() ) . '" target="_blank">' . esc_html__( 'Go to forum', 'wp-recall' ) . '</a></p>';
}

if ( ! wp_doing_ajax() ) {
	add_action( 'admin_init', 'pfm_init_admin_actions' );
}
function pfm_init_admin_actions() {
	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}

	if ( ! isset( $_REQUEST['pfm-action'] ) || ! isset( $_REQUEST['_wpnonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'pfm-nonce' ) ) {
		return;
	}

	$action = isset( $_REQUEST['pfm-action'] ) ? sanitize_key( $_REQUEST['pfm-action'] ) : '';

	switch ( $action ) {
		case 'group_create': //добавление группы

			pfm_add_group( array(
				'group_name' => isset( $_REQUEST['group_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['group_name'] ) ) : '',
				'group_slug' => isset( $_REQUEST['group_slug'] ) ? sanitize_title( wp_unslash( $_REQUEST['group_slug'] ) ) : '',
				'group_desc' => isset( $_REQUEST['group_desc'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['group_desc'] ) ) : ''
			) );

			break;
		case 'forum_create': //создание форума

			pfm_add_forum( array(
				'forum_name' => isset( $_REQUEST['forum_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['forum_name'] ) ) : '',
				'forum_desc' => isset( $_REQUEST['forum_desc'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['forum_desc'] ) ) : '',
				'forum_slug' => isset( $_REQUEST['forum_slug'] ) ? sanitize_title( wp_unslash( $_REQUEST['forum_slug'] ) ) : '',
				'group_id'   => isset( $_REQUEST['group_id'] ) ? absint( $_REQUEST['group_id'] ) : 0
			) );

			break;
		case 'group_delete': //удаление группы

			if ( empty( $_REQUEST['group_id'] ) ) {
				return false;
			}

			pfm_delete_group( absint( $_REQUEST['group_id'] ), isset( $_REQUEST['migrate_group'] ) ? sanitize_key( $_REQUEST['migrate_group'] ) : '' );

			wp_safe_redirect( admin_url( 'admin.php?page=pfm-forums' ) );
			exit;
		case 'forum_delete': //удаление форума

			if ( empty( $_REQUEST['forum_id'] ) ) {
				return false;
			}

			$group = pfm_get_forum( absint( $_REQUEST['forum_id'] ) );

			pfm_delete_forum( absint( $_REQUEST['forum_id'] ), isset( $_REQUEST['migrate_group'] ) ? sanitize_key( $_REQUEST['migrate_group'] ) : '' );

			wp_safe_redirect( admin_url( 'admin.php?page=pfm-forums&group-id=' . $group->group_id ) );
			exit;
	}

	if ( isset( $_POST['_wp_http_referer'] ) ) {
		wp_safe_redirect( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) );
	}

	exit;
}

add_action( 'rcl_addons_included', 'pfm_template_update_status' );
function pfm_template_update_status() {

	if ( wp_doing_ajax() ) {
		return false;
	}

	$page = ( isset( $_GET['page'] ) ) ? sanitize_key( $_GET['page'] ) : false;
	if ( 'pfm-themes' != $page ) {
		return;
	}

	if ( isset( $_GET['template'] ) && isset( $_GET['action'] ) ) {
		$addon  = sanitize_key( $_GET['template'] );
		$action = rcl_wp_list_current_action();

		if ( $action == 'connect' ) {

			if ( rcl_exist_addon( get_site_option( 'rcl_pforum_template' ) ) && ! isset( $_GET['redirect'] ) ) {
				rcl_deactivate_addon( get_site_option( 'rcl_pforum_template' ) );
				header( "Location: " . admin_url( 'admin.php?page=pfm-themes&action=' . $action . '&template=' . $addon . '&redirect=1' ), true, 302 );
				exit;
			}

			$templates = pfm_get_templates();

			if ( ! isset( $templates[ $addon ] ) ) {
				return false;
			}

			$template = $templates[ $addon ];

			rcl_activate_addon( $addon, true, dirname( $template['path'] ) );

			update_site_option( 'rcl_pforum_template', $addon );
			header( "Location: " . admin_url( 'admin.php?page=pfm-themes&update-template=activate' ), true, 302 );
			exit;
		}

		if ( $action == 'delete' ) {
			rcl_delete_addon( $addon );
			header( "Location: " . admin_url( 'admin.php?page=pfm-themes&update-template=delete' ), true, 302 );
			exit;
		}
	}
}
