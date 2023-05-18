<?php
include_once 'classes/class-rcl-group-widget.php';

add_action( 'init', 'rcl_group_add_primary_widget', 10 );
function rcl_group_add_primary_widget() {
	rcl_group_register_widget( 'Group_Primary_Widget' );
}

class Group_Primary_Widget extends Rcl_Group_Widget {
	function __construct() {
		parent::__construct( array(
				'widget_id'    => 'group-primary-widget',
				'widget_place' => 'sidebar',
				'widget_title' => __( 'Control panel', 'wp-recall' )
			)
		);
	}

	function options( $instance ) {

		$defaults = array( 'title' => __( 'Control panel', 'wp-recall' ) );
		$instance = wp_parse_args( ( array ) $instance, $defaults );

		echo '<label>' . esc_html__( 'Title', 'wp-recall' ) . '</label>'
		     . '<input type="text" name="' . esc_attr( $this->field_name( 'title' ) ) . '" value="' . esc_attr( $instance['title'] ) . '">';
	}

	function widget( $args ) {
		extract( $args );

		global $rcl_group, $user_ID;

		if ( ! $user_ID || rcl_is_group_can( 'admin' ) ) {
			return false;
		}

		//if($rcl_group->current_user=='banned') return false;

		if ( rcl_is_group_can( 'reader' ) ) {
			/**
			 * @var $before string additional info
			 */
			echo $before;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			echo '<form method="post">'
			     . wp_kses( rcl_get_button( array(
					'icon'   => 'fa-sign-out',
					'label'  => esc_html__( 'Leave group', 'wp-recall' ),
					'submit' => true
				) ), rcl_kses_allowed_html() )
			     . '<input type="hidden" name="group-submit" value="1">'
			     . '<input type="hidden" name="group-action" value="leave">'
			     //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			     . wp_nonce_field( 'group-action-' . $user_ID, '_wpnonce', true, false )
			     . '</form>';
			/**
			 * @var $after string additional info
			 */
			echo $after;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {

			if ( rcl_get_group_option( $rcl_group->term_id, 'can_register' ) ) {
				/**
				 * @var $before string additional info
				 */
				echo $before;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				if ( $rcl_group->current_user == 'banned' ) {

					echo wp_kses_post( rcl_get_notice( [
						'text' => esc_html__( 'You have been banned from the group', 'wp-recall' ),
						'type' => 'error'
					] ) );
				} else {
					if ( $rcl_group->group_status == 'open' ) {
						echo '<form method="post">'
						     . wp_kses( rcl_get_button( array(
								'icon'   => 'fa-sign-in',
								'label'  => __( 'Join group', 'wp-recall' ),
								'submit' => true
							) ), rcl_kses_allowed_html() )
						     . '<input type="hidden" name="group-submit" value="1">'
						     . '<input type="hidden" name="group-action" value="join">'
						     //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						     . wp_nonce_field( 'group-action-' . $user_ID, '_wpnonce', true, false )
						     . '</form>';
					}

					if ( $rcl_group->group_status == 'closed' ) {

						$requests = rcl_get_group_option( $rcl_group->term_id, 'requests_group_access' );

						if ( $requests && false !== array_search( $user_ID, $requests ) ) {
							echo wp_kses_post( rcl_get_notice( [ 'text' => esc_html__( 'The access request has been sent', 'wp-recall' ) ] ) );
						} else {

							echo '<form method="post">'
							     . wp_kses( rcl_get_button( array(
									'icon'   => 'fa-paper-plane',
									'label'  => __( 'The request of access', 'wp-recall' ),
									'submit' => true
								) ), rcl_kses_allowed_html() )
							     . '<input type="hidden" name="group-submit" value="1">'
							     . '<input type="hidden" name="group-action" value="ask">'
							     //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							     . wp_nonce_field( 'group-action-' . $user_ID, '_wpnonce', true, false )
							     . '</form>';
						}
					}
				}
				/**
				 * @var $after string additional info
				 */
				echo $after;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

}

add_action( 'init', 'rcl_group_add_users_widget', 10 );
function rcl_group_add_users_widget() {
	rcl_group_register_widget( 'Group_Users_Widget' );
}

class Group_Users_Widget extends Rcl_Group_Widget {
	function __construct() {
		parent::__construct( array(
				'widget_id'    => 'group-users-widget',
				'widget_place' => 'sidebar',
				'widget_title' => __( 'Users', 'wp-recall' )
			)
		);
	}

	function widget( $args, $instance ) {

		if ( ! rcl_get_member_group_access_status() ) {
			return false;
		}

		global $rcl_group, $user_ID;

		extract( $args );

		$user_count = ( isset( $instance['count'] ) ) ? $instance['count'] : 12;
		$template   = ( isset( $instance['template'] ) ) ? $instance['template'] : 'mini';
		/**
		 * @var $before string additional info
		 */
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $before;
		echo wp_kses( rcl_group_users( $user_count, $template ), rcl_kses_allowed_html() );
		echo wp_kses( rcl_get_group_link( 'rcl_get_group_users', esc_html__( 'All users', 'wp-recall' ) ), rcl_kses_allowed_html() );
		/**
		 * @var $after string additional info
		 */
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $after;
	}

	function options( $instance ) {

		$defaults = array( 'title' => __( 'Users', 'wp-recall' ), 'count' => 12, 'template' => 'mini' );
		$instance = wp_parse_args( ( array ) $instance, $defaults );

		echo '<label>' . esc_html__( 'Title', 'wp-recall' ) . '</label>'
		     . '<input type="text" name="' . esc_attr( $this->field_name( 'title' ) ) . '" value="' . esc_attr( $instance['title'] ) . '">';
		echo '<label>' . esc_html__( 'Amount', 'wp-recall' ) . '</label>'
		     . '<input type="number" name="' . esc_attr( $this->field_name( 'count' ) ) . '" value="' . esc_attr( $instance['count'] ) . '">';
		echo '<label>' . esc_html__( 'Template', 'wp-recall' ) . '</label>'
		     . '<select name="' . esc_attr( $this->field_name( 'template' ) ) . '">'
		     . '<option value="mini" ' . selected( 'mini', $instance['template'], false ) . '>Mini</option>'
		     . '<option value="avatars" ' . selected( 'avatars', $instance['template'], false ) . '>Avatars</option>'
		     . '<option value="rows" ' . selected( 'rows', $instance['template'], false ) . '>Rows</option>'
		     . '</select>';
	}

}

add_action( 'init', 'rcl_group_add_publicform_widget', 10 );
function rcl_group_add_publicform_widget() {
	rcl_group_register_widget( 'Group_PublicForm_Widget' );
}

class Group_PublicForm_Widget extends Rcl_Group_Widget {
	function __construct() {
		parent::__construct( array(
				'widget_id'    => 'group-public-form-widget',
				'widget_title' => __( 'Publication form', 'wp-recall' ),
				'widget_place' => 'content',
				'widget_type'  => 'hidden'
			)
		);
	}

	function widget( $args, $instance ) {

		if ( ! rcl_is_group_can( 'author' ) ) {
			return false;
		}

		extract( $args );

		global $rcl_group;
		/**
		 * @var $before string additional info
		 */
		echo $before;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo do_shortcode( '[public-form post_type="post-group" select_type="select" select_amount="1" group_id="' . $rcl_group->term_id . '"]' );
		/**
		 * @var $after string additional info
		 */
		echo $after;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	function options( $instance ) {

		$defaults = array( 'title' => __( 'Publication form', 'wp-recall' ), 'type_form' => 0 );
		$instance = wp_parse_args( ( array ) $instance, $defaults );

		echo '<label>' . esc_html__( 'Title', 'wp-recall' ) . '</label>'
		     . '<input type="text" name="' . esc_attr( $this->field_name( 'title' ) ) . '" value="' . esc_attr( $instance['title'] ) . '">';
	}

}

add_action( 'init', 'rcl_group_add_categorylist_widget', 10 );
function rcl_group_add_categorylist_widget() {
	rcl_group_register_widget( 'Group_CategoryList_Widget' );
}

class Group_CategoryList_Widget extends Rcl_Group_Widget {
	function __construct() {
		parent::__construct( array(
				'widget_id'    => 'group-category-list-widget',
				'widget_title' => __( 'Group categories', 'wp-recall' ),
				'widget_place' => 'unuses'
			)
		);
	}

	function options( $instance ) {

		$defaults = array( 'title' => __( 'Group categories', 'wp-recall' ) );
		$instance = wp_parse_args( ( array ) $instance, $defaults );

		echo '<label>' . esc_html__( 'Title', 'wp-recall' ) . '</label>'
		     . '<input type="text" name="' . esc_attr( $this->field_name( 'title' ) ) . '" value="' . esc_attr( $instance['title'] ) . '">';
	}

	function widget( $args ) {

		if ( ! rcl_get_member_group_access_status() ) {
			return false;
		}

		extract( $args );

		global $rcl_group;

		$category = rcl_get_group_category_list();
		if ( ! $category ) {
			return false;
		}
		/**
		 * @var $before string additional info
		 */
		echo $before;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		echo wp_kses( $category, rcl_kses_allowed_html() );
		/**
		 * @var $after string additional info
		 */
		echo $after;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

}

add_action( 'init', 'rcl_group_add_admins_widget', 10 );
function rcl_group_add_admins_widget() {
	rcl_group_register_widget( 'Group_Admins_Widget' );
}

class Group_Admins_Widget extends Rcl_Group_Widget {
	function __construct() {
		parent::__construct( array(
				'widget_id'    => 'group-admins-widget',
				'widget_place' => 'sidebar',
				'widget_title' => __( 'Management', 'wp-recall' )
			)
		);
	}

	function widget( $args, $instance ) {

		global $rcl_group, $user_ID;

		extract( $args );

		$user_count = ( isset( $instance['count'] ) ) ? $instance['count'] : 12;
		$template   = ( isset( $instance['template'] ) ) ? $instance['template'] : 'mini';
		/**
		 * @var $before string additional info
		 */
		echo $before;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wp_kses( $this->get_group_administrators( $user_count, $template ), rcl_kses_allowed_html() );
		/**
		 * @var $after string additional info
		 */
		echo $after;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	function add_admins_query( $query ) {
		global $rcl_group;

		$query['join'][]  = "LEFT JOIN " . RCL_PREF . "groups_users AS groups_users ON wp_users.ID = groups_users.user_id";
		$query['where'][] = "(groups_users.user_role IN ('admin','moderator') AND groups_users.group_id='$rcl_group->term_id') OR (wp_users.ID='$rcl_group->admin_id')";
		$query['groupby'] = "wp_users.ID";

		return $query;
	}

	function get_group_administrators( $number, $template = 'mini' ) {
		global $rcl_group;
		if ( ! $rcl_group ) {
			return false;
		}

		switch ( $template ) {
			case 'rows':
				$data = 'descriptions,rating_total,posts_count,comments_count,user_registered';
				break;
			case 'avatars':
				$data = 'rating_total';
				break;
			default:
				$data = '';
		}

		add_filter( 'rcl_users_query', array( $this, 'add_admins_query' ) );

		return rcl_get_userlist( array( 'number' => $number, 'template' => $template, 'data' => $data ) );
	}

	function options( $instance ) {

		$defaults = array( 'title' => __( 'Management', 'wp-recall' ), 'count' => 12, 'template' => 'mini' );
		$instance = wp_parse_args( ( array ) $instance, $defaults );

		echo '<label>' . esc_html__( 'Title', 'wp-recall' ) . '</label>'
		     . '<input type="text" name="' . esc_attr( $this->field_name( 'title' ) ) . '" value="' . esc_attr( $instance['title'] ) . '">';
		echo '<label>' . esc_html__( 'Template', 'wp-recall' ) . '</label>'
		     . '<select name="' . esc_attr( $this->field_name( 'template' ) ) . '">'
		     . '<option value="mini" ' . selected( 'mini', $instance['template'], false ) . '>Mini</option>'
		     . '<option value="avatars" ' . selected( 'avatars', $instance['template'], false ) . '>Avatars</option>'
		     . '<option value="rows" ' . selected( 'rows', $instance['template'], false ) . '>Rows</option>'
		     . '</select>';
	}

}

add_action( 'init', 'rcl_group_add_posts_widget', 10 );
function rcl_group_add_posts_widget() {

	if ( ! rcl_get_option( 'group-output' ) && ! rcl_get_option( 'groups_posts_widget' ) ) {
		return false;
	}

	rcl_group_register_widget( 'Group_Posts_Widget' );
}

class Group_Posts_Widget extends Rcl_Group_Widget {
	function __construct() {
		parent::__construct( array(
				'widget_id'    => 'group-posts-widget',
				'widget_place' => 'content',
				'widget_title' => __( 'Group posts', 'wp-recall' )
			)
		);
	}

	function widget( $args, $instance ) {

		global $rcl_group, $post, $user_ID;

		extract( $args );

		if ( ! rcl_get_member_group_access_status() ) {
			/**
			 * @var $before string additional info
			 */
			echo $before;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wp_kses( rcl_close_group_post_content(), rcl_kses_allowed_html() );

			if ( ! $user_ID ) {
				echo wp_kses_post( rcl_get_notice( [
					'text' => esc_html__( 'Login and send a request to receive an access of the group', 'wp-recall' ),
				] ) );
			}
			/**
			 * @var $after string additional info
			 */
			echo $after;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			return;
		}

		$defaults = array(
			'title'     => __( 'Group posts', 'wp-recall' ),
			'count'     => 12,
			'excerpt'   => 1,
			'thumbnail' => 1
		);

		$instance = wp_parse_args( ( array ) $instance, $defaults );
		/**
		 * @var $before string additional info
		 */
		echo $before;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>

		<?php
		if ( rcl_get_option( 'group-output' ) ) { //если вывод через шорткод на странице
			$term_id = ( ! empty( $_GET['group-tag'] ) ) ? sanitize_key( $_GET['group-tag'] ) : $rcl_group->term_id;

			$args = array(
				'post_type'   => 'post-group',
				'numberposts' => - 1,
				'fields'      => 'ids',
				'tax_query'   => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'groups',
						'field'    => ( $term_id == $rcl_group->term_id ) ? 'id' : 'slug',
						'terms'    => $term_id
					)
				)
			);

			$groupPosts = get_posts( $args );

			$numberPosts = count( $groupPosts );

			$pagenavi = new Rcl_PageNavi( 'rcl-group', $numberPosts, array( 'in_page' => $instance['count'] ) );

			$args = array(
				'post_type'   => 'post-group',
				'numberposts' => $instance['count'],
				'offset'      => $pagenavi->offset,
				'tax_query'   => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'groups',
						'field'    => ( $term_id == $rcl_group->term_id ) ? 'id' : 'slug',
						'terms'    => $term_id
					)
				)
			);

			$args = apply_filters( 'rcl_group_pre_get_posts', $args );

			$posts = get_posts( $args );

			if ( $posts ) {
				?>

                <nav class="rcl-group-pagination">
					<?php echo $pagenavi->pagenavi();//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </nav>

				<?php foreach ( $posts as $post ): setup_postdata( $post ); ?>

					<?php rcl_include_template( 'group-posts.php', __FILE__, $instance ); ?>

				<?php endforeach; ?>

				<?php wp_reset_postdata(); ?>

                <nav class="rcl-group-pagination">
					<?php echo $pagenavi->pagenavi();//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </nav>

			<?php } else { ?>

				<?php echo rcl_get_notice( [ 'text' => esc_html__( "You do not have any publications", "wp-recall" ) ] );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<?php } ?>

		<?php } else { //если вывод на архивной странице  ?>

			<?php if ( have_posts() ) { ?>

                <nav class="rcl-group-pagination">
					<?php if ( function_exists( 'wp_pagenavi' ) ): ?>
						<?php wp_pagenavi(); ?>
					<?php else: ?>
                        <ul class="group">
                            <li class="prev left"><?php previous_posts_link(); ?></li>
                            <li class="next right"><?php next_posts_link(); ?></li>
                        </ul>
					<?php endif; ?>
                </nav>

				<?php while ( have_posts() ): the_post(); ?>

					<?php rcl_include_template( 'group-posts.php', __FILE__, $instance ); ?>

				<?php endwhile; ?>

                <nav class="rcl-group-pagination">
					<?php if ( function_exists( 'wp_pagenavi' ) ): ?>
						<?php wp_pagenavi(); ?>
					<?php else: ?>
                        <ul class="group">
                            <li class="prev left"><?php previous_posts_link(); ?></li>
                            <li class="next right"><?php next_posts_link(); ?></li>
                        </ul>
					<?php endif; ?>
                </nav>

			<?php } else { ?>

				<?php echo rcl_get_notice( [ 'text' => esc_html__( "You do not have any publications", "wp-recall" ) ] );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<?php } ?>

		<?php } ?>

		<?php
		/**
		 * @var $after string additional info
		 */
		echo $after;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	function options( $instance ) {

		$defaults = array(
			'title'     => __( 'Group posts', 'wp-recall' ),
			'count'     => 12,
			'excerpt'   => 1,
			'thumbnail' => 1
		);
		$instance = wp_parse_args( ( array ) $instance, $defaults );

		echo '<label>' . esc_html__( 'Title', 'wp-recall' ) . '</label>'
		     . '<input type="text" name="' . esc_attr( $this->field_name( 'title' ) ) . '" value="' . esc_attr( $instance['title'] ) . '">';
		echo '<label>' . esc_html__( 'Summary', 'wp-recall' ) . '</label>'
		     . '<select name="' . esc_attr( $this->field_name( 'excerpt' ) ) . '">'
		     . '<option value="0" ' . selected( 0, $instance['excerpt'], false ) . '>' . esc_html__( 'Do not display', 'wp-recall' ) . '</option>'
		     . '<option value="1" ' . selected( 1, $instance['excerpt'], false ) . '>' . esc_html__( 'Display', 'wp-recall' ) . '</option>'
		     . '</select>';
		echo '<label>' . esc_html__( 'Thumbnail', 'wp-recall' ) . '</label>'
		     . '<select name="' . esc_attr( $this->field_name( 'thumbnail' ) ) . '">'
		     . '<option value="0" ' . selected( 0, $instance['thumbnail'], false ) . '>' . esc_html__( 'Do not display', 'wp-recall' ) . '</option>'
		     . '<option value="1" ' . selected( 1, $instance['thumbnail'], false ) . '>' . esc_html__( 'Display', 'wp-recall' ) . '</option>'
		     . '</select>';
	}

}
