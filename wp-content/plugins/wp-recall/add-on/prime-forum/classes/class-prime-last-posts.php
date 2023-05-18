<?php

class PrimeLastPosts {

	public $number = 5;
	public $name_length = 30;
	public $post_length = 120;
	public $avatar_size = 40;
	public $topics = array();
	public $posts = array();

	function __construct( $args ) {

		$this->init_properties( $args );

		$this->posts = $this->get_posts();
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	function get_posts() {
		$PrimePosts  = new PrimePosts();
		$PrimeTopics = new PrimeTopics();

		$query = RQ::tbl( $PrimeTopics )
		           ->join( 'topic_id', $PrimePosts->select( [
			           'post_id',
			           'post_content',
			           'post_author' => 'user_id'
		           ] )
		                                          ->where_string( $PrimePosts->get_colname( 'post_index' ) . " = " . $PrimeTopics->get_colname( 'post_count' ) )
		           )
		           ->limit( $this->number )
		           ->groupby( 'topic_id' )
		           ->orderby( "MAX(" . $PrimePosts->get_colname( 'post_date' ) . ")", 'DESC', false );

		$query = apply_filters( 'pfm_last_posts_query', $query );

		return $query->get_results( 'cache' );
	}

	function string_trim( $string, $length ) {

		$string = strip_shortcodes( $string );

		if ( iconv_strlen( $string = strip_tags( $string ), 'utf-8' ) > $length ) {
			$string = iconv_substr( $string, 0, $length, 'utf-8' );
			$string = preg_replace( '@(.*)\s[^\s]*$@s', '\\1', $string ) . '...';
		}

		return $string;
	}

	function get_content() {

		if ( ! $this->posts ) {
			return false;
		}

		$content = '<div class="prime-last-posts">';
		$content .= '<ul class="last-post-list">';

		foreach ( $this->posts as $post ) {

			$url = pfm_get_post_permalink( $post->post_id );

			$content .= '<li class="last-post-box">';

			if ( $this->avatar_size ) {
				$content .= '<div class="last-post-author-avatar">
                            <a href="' . $url . '">' . get_avatar( $post->post_author, $this->avatar_size ) . '</a>
                        </div>';
			}

			if ( $this->name_length ) {
				$content .= '<div class="last-post-title">
                            <a href="' . $url . '">
                                ' . ( $post->topic_closed ? '<i class="rcli fa-lock"></i>' : '' ) . ' ' . $this->string_trim( $post->topic_name, $this->name_length ) . '
                            </a>
                        </div>';
			}

			if ( $this->post_length ) {
				$content .= '<div class="last-post-content">
                            ' . $this->string_trim( $post->post_content, $this->post_length ) . ' '
				            . '<a class="last-post-more" href=' . $url . '> ' . __( 'Read more', 'wp-recall' ) . '</a>
                        </div>';
			}

			$content .= '</li>';
		}

		$content .= '</ul>';
		$content .= '</div>';

		return $content;
	}

}
