<?php

function pfm_get_groups( $args = false ) {
	return RQ::tbl( new PrimeGroups() )->parse( $args )->get_results();
}

function pfm_get_group( $group_id ) {
	$groups = new PrimeGroups();

	return $groups->get_row( array( 'group_id' => $group_id ) );
}

function pfm_get_group_field( $group_id, $fieldName ) {
	return RQ::tbl( new PrimeGroups() )
	         ->select( [ $fieldName ] )
	         ->where( [ 'group_id' => $group_id ] )
	         ->get_var();
}

function pfm_add_group( $args ) {
	global $wpdb;

	if ( ! isset( $args['group_seq'] ) ) {
		$args['group_seq'] = RQ::tbl( new PrimeGroups() )->get_count() + 1;
	}

	if ( ! isset( $args['group_slug'] ) || ! $args['group_slug'] ) {

		$args['group_slug'] = str_replace( array( '_', ' ' ), '-', rcl_sanitize_string( $args['group_name'] ) );
	} else {

		$args['group_slug'] = str_replace( array( ' ' ), '-', $args['group_slug'] );
	}

	$result = $wpdb->insert( RCL_PREF . 'pforum_groups', $args );

	if ( ! $result ) {
		return false;
	}

	$group_id = $wpdb->insert_id;

	do_action( 'pfm_add_group', $group_id );

	return $group_id;
}

function pfm_delete_group( $group_id, $group_new = false ) {
	global $wpdb;

	do_action( 'pfm_pre_delete_group', $group_id );

	//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$result = $wpdb->query( "DELETE FROM " . RCL_PREF . "pforum_groups WHERE group_id='$group_id'" );

	if ( $result ) {

		if ( $group_new ) {

			$wpdb->update(
				RCL_PREF . 'pforums', array(
				'group_id' => $group_new
			), array(
					'group_id' => $group_id
				)
			);
		} else {

			$forums = pfm_get_forums( array(
				'group_id' => $group_id,
				'fields'   => array( 'forum_id' )
			) );

			if ( $forums ) {
				foreach ( $forums as $forum ) {
					pfm_delete_forum( $forum->forum_id );
				}
			}
		}

		do_action( 'pfm_deleted_group', $group_id );
	}

	return $result;
}

function pfm_update_group( $args ) {
	global $wpdb;

	if ( ! isset( $args['group_id'] ) ) {
		return false;
	}

	$group_id = $args['group_id'];

	$group = pfm_get_group( $group_id );

	if ( ! $group ) {
		return false;
	}

	unset( $args['group_id'] );

	if ( isset( $args['group_slug'] ) ) {

		if ( ! $args['group_slug'] ) {

			$group_name = ( isset( $args['group_name'] ) && $args['group_name'] ) ? $args['group_name'] : $group->group_name;

			$args['group_slug'] = str_replace( array( '_', ' ' ), '-', rcl_sanitize_string( $group_name ) );
		} else {
			$args['group_slug'] = str_replace( array( ' ' ), '-', $args['group_slug'] );
		}
	}

	$result = $wpdb->update(
		RCL_PREF . 'pforum_groups', $args, array(
			'group_id' => $group_id
		)
	);

	do_action( 'pfm_update_group', $group_id );

	return $result;
}

function pfm_get_forums( $args = false ) {
	return RQ::tbl( new PrimeForums() )->parse( $args )->get_results();
}

function pfm_get_forum( $forum_id ) {
	return RQ::tbl( new PrimeForums() )->where( [ 'forum_id' => $forum_id ] )->get_row();
}

function pfm_get_forum_field( $forum_id, $fieldName ) {
	return RQ::tbl( new PrimeForums() )->select( [ $fieldName ] )
	         ->where( [ 'forum_id' => $forum_id ] )
	         ->get_var();
}

function pfm_add_forum( $args ) {
	global $wpdb;

	if ( ! isset( $args['group_id'] ) || ! $args['group_id'] ) {
		return false;
	}

	if ( ! isset( $args['forum_seq'] ) ) {

		$seq = RQ::tbl( new PrimeForums() )
		         ->where( array( 'group_id' => $args['group_id'] ) )
		         ->get_count() + 1;

		$args['forum_seq'] = $seq;
	}

	if ( ! isset( $args['forum_slug'] ) || ! $args['forum_slug'] ) {

		$args['forum_slug'] = str_replace( array( '_', ' ' ), '-', rcl_sanitize_string( $args['forum_name'] ) );
	} else {

		$args['forum_slug'] = str_replace( array( ' ' ), '-', $args['forum_slug'] );
	}

	if ( ! isset( $args['forum_desc'] ) ) {
		$args['forum_desc'] = '';
	}

	$args['forum_status'] = 'open';

	if ( ! isset( $args['parent_id'] ) ) {
		$args['parent_id'] = 0;
	}

	if ( ! isset( $args['topic_count'] ) ) {
		$args['topic_count'] = 0;
	}

	if ( ! isset( $args['forum_closed'] ) ) {
		$args['forum_closed'] = 0;
	}

	$result = $wpdb->insert( RCL_PREF . 'pforums', $args );

	if ( ! $result ) {
		return false;
	}

	$forum_id = $wpdb->insert_id;

	do_action( 'pfm_add_forum', $forum_id );

	return $forum_id;
}

function pfm_update_forum( $args ) {
	global $wpdb;

	if ( ! isset( $args['forum_id'] ) ) {
		return false;
	}

	$forum_id = $args['forum_id'];

	$forum = pfm_get_forum( $forum_id );

	if ( ! $forum ) {
		return false;
	}

	unset( $args['forum_id'] );

	if ( isset( $args['forum_slug'] ) ) {

		if ( ! $args['forum_slug'] ) {

			$forum_name = ( isset( $args['forum_name'] ) && $args['forum_name'] ) ? $args['forum_name'] : $forum->forum_name;

			$args['forum_slug'] = str_replace( array( '_', ' ' ), '-', rcl_sanitize_string( $forum_name ) );
		} else {
			$args['forum_slug'] = str_replace( array( ' ' ), '-', $args['forum_slug'] );
		}
	}

	if ( isset( $args['group_id'] ) && $forum->group_id != $args['group_id'] ) {

		$args['parent_id'] = 0;
	}

	$result = $wpdb->update(
		RCL_PREF . 'pforums', $args, array(
			'forum_id' => $forum_id
		)
	);

	if ( $result ) {

		//если был перенос в другую группу, то также переносим и все дочерние форумы
		if ( isset( $args['group_id'] ) && $forum->group_id != $args['group_id'] ) {

			$childForums = pfm_get_forums( array(
				'parent_id' => $forum_id,
				'fields'    => array(
					'forum_id'
				)
			) );

			if ( $childForums ) {

				foreach ( $childForums as $chForum ) {
					pfm_update_forum( array(
						'forum_id' => $chForum->forum_id,
						'group_id' => $args['group_id']
					) );
				}
			}
		}
	}

	do_action( 'pfm_update_forum', $forum_id );

	return $result;
}

function pfm_delete_forum( $forum_id, $forum_new = false ) {
	global $wpdb;

	do_action( 'pfm_pre_delete_forum', $forum_id );

	//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$result = $wpdb->query( "DELETE FROM " . RCL_PREF . "pforums WHERE forum_id='$forum_id'" );

	if ( $result ) {

		//переназначаем родителя для дочерних форумов
		$wpdb->update(
			RCL_PREF . 'pforums', array(
			'parent_id' => 0
		), array(
				'parent_id' => $forum_id
			)
		);

		if ( $forum_new ) {

			//переназначаем форум для дочерних топиков
			$wpdb->update(
				RCL_PREF . 'pforum_topics', array(
				'forum_id' => $forum_new
			), array(
					'forum_id' => $forum_id
				)
			);

			pfm_update_forum_counter( $forum_new );
		} else {

			$topics = pfm_get_topics( array(
				'forum_id' => $forum_id,
				'fields'   => array( 'topic_id' )
			) );

			if ( $topics ) {
				foreach ( $topics as $topic ) {
					pfm_delete_topic( $topic->topic_id );
				}
			}
		}

		do_action( 'pfm_deleted_forum', $forum_id );
	}

	return $result;
}

function pfm_update_forum_counter( $forum_id ) {
	global $wpdb;

	$wpdb->update(
		RCL_PREF . 'pforums', array(
		'topic_count' => RQ::tbl( new PrimeTopics() )->where( [ 'forum_id' => $forum_id ] )->get_count()
	), array(
			'forum_id' => $forum_id
		)
	);
}

function pfm_subforums_topic_count( $forum_id ) {
	global $wpdb;

	$sql = "SELECT SUM(topic_count) "
	       . "FROM " . RCL_PREF . "pforums "
	       . "WHERE parent_id='$forum_id'";

	//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	return $wpdb->get_var( $sql );
}

function pfm_get_topics( $args = false ) {
	return RQ::tbl( new PrimeTopics() )->parse( $args )->get_results();
}

function pfm_get_topic( $topic_id ) {

	return RQ::tbl( new PrimeTopics() )->where( [
		'topic_id' => $topic_id
	] )
	         ->join(
		         [
			         'topic_id',
			         'topic_id'
		         ], RQ::tbl( new PrimePosts() )->select( [ 'max' => [ 'last_post_date' => 'post_date' ] ] )
	         )
	         ->get_row();
}

function pfm_add_topic( $args, $postdata = array() ) {
	global $user_ID, $wpdb;

	if ( ! isset( $args['forum_id'] ) || ! $args['forum_id'] ) {
		return false;
	}

	if ( ! isset( $args['topic_name'] ) || ! $args['topic_name'] ) {
		return false;
	}

	if ( ! isset( $args['user_id'] ) ) {

		if ( ! $user_ID ) {
			return false;
		}

		$args['user_id'] = $user_ID;
	}

	if ( ! isset( $args['topic_slug'] ) || ! $args['topic_slug'] ) {

		$args['topic_slug'] = substr( str_replace( array(
			'_',
			' '
		), '-', rcl_sanitize_string( trim( $args['topic_name'], '\s\.' ) ) ), 0, 70 );
	} else {

		$args['topic_slug'] = str_replace( array( ' ' ), '-', $args['topic_slug'] );
	}

	$topic_slug = RQ::tbl( new PrimeTopics() )->select( [ 'topic_slug' ] )->where( array(
		'topic_slug' => $args['topic_slug']
	) )->get_var();

	if ( $topic_slug ) {

		$suffix = RQ::tbl( new PrimeTopics() )->where( array(
			'topic_slug__like' => $args['topic_slug']
		) )->get_count();

		++ $suffix;

		$args['topic_slug'] = $args['topic_slug'] . '-' . $suffix;
	}

	$topic = array(
		'topic_name'   => htmlspecialchars( $args['topic_name'] ),
		'topic_slug'   => $args['topic_slug'],
		'forum_id'     => $args['forum_id'],
		'user_id'      => $args['user_id'],
		'topic_status' => isset( $args['topic_status'] ) ? $args['topic_status'] : 'open',
		'post_count'   => isset( $args['post_count'] ) ? $args['post_count'] : 0,
		'topic_fix'    => isset( $args['topic_fix'] ) ? $args['topic_fix'] : 0,
		'topic_closed' => isset( $args['topic_closed'] ) ? $args['topic_closed'] : 0
	);


	if ( isset( $args['topic_id'] ) ) {
		$topic['topic_id'] = $args['topic_id'];
	}

	$result = $wpdb->insert( RCL_PREF . 'pforum_topics', $topic );

	if ( ! $result ) {
		return false;
	}

	$topic_id = $wpdb->insert_id;

	if ( isset( $postdata['post_content'] ) && $postdata['post_content'] ) {

		$postdata['topic_id']    = $topic_id;
		$postdata['user_id']     = $args['user_id'];
		$postdata['post_date']   = isset( $postdata['post_date'] ) ? $postdata['post_date'] : current_time( 'mysql' );
		$postdata['guest_name']  = isset( $postdata['guest_name'] ) ? $postdata['guest_name'] : '';
		$postdata['guest_email'] = isset( $postdata['guest_email'] ) ? sanitize_email( wp_unslash( $postdata['guest_email'] ) ) : '';

		pfm_add_post( $postdata );
	}

	do_action( 'pfm_add_topic', $topic_id, $args );

	return $topic_id;
}

function pfm_delete_topic( $topic_id ) {
	global $wpdb;

	$topic = pfm_get_topic( $topic_id );

	if ( ! $topic ) {
		return false;
	}

	do_action( 'pfm_pre_delete_topic', $topic_id );

	//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$result = $wpdb->query( "DELETE FROM " . RCL_PREF . "pforum_topics WHERE topic_id='$topic_id'" );

	if ( $result ) {

		$posts = pfm_get_posts( array(
			'topic_id' => $topic_id,
			'fields'   => array( 'post_id' )
		) );

		if ( $posts ) {
			foreach ( $posts as $post ) {
				pfm_delete_post( $post->post_id );
			}
		}

		pfm_update_forum_counter( $topic->forum_id );

		do_action( 'pfm_delete_topic', $topic_id );
	}

	return $result;
}

function pfm_update_topic( $args ) {
	global $wpdb;

	if ( ! isset( $args['topic_id'] ) ) {
		return false;
	}

	$topic_id = $args['topic_id'];

	$topic = pfm_get_topic( $topic_id );

	if ( ! $topic ) {
		return false;
	}

	unset( $args['topic_id'] );

	if ( isset( $args['topic_slug'] ) ) {

		if ( ! $args['topic_slug'] ) {

			$forum_name = ( isset( $args['topic_name'] ) && $args['topic_name'] ) ? $args['topic_name'] : $topic->topic_name;

			$args['topic_slug'] = str_replace( array( '_', ' ' ), '-', rcl_sanitize_string( $forum_name ) );
		} else {
			$args['topic_slug'] = str_replace( array( ' ' ), '-', $args['topic_slug'] );
		}
	}

	if ( isset( $args['topic_name'] ) && $args['topic_name'] ) {
		$args['topic_name'] = htmlspecialchars( $args['topic_name'] );
	}

	$result = $wpdb->update(
		RCL_PREF . 'pforum_topics', $args, array(
			'topic_id' => $topic_id
		)
	);

	if ( $result ) {
		wp_cache_delete( json_encode( array( 'pfm_topic_permalink', $topic_id ) ) );
	}

	do_action( 'pfm_update_topic', $topic_id );

	return $result;
}

function pfm_update_topic_data( $topic_id ) {
	pfm_update_topic_counter( $topic_id );
	pfm_update_topic_indexes( $topic_id );
}

function pfm_update_topic_counter( $topic_id ) {
	global $wpdb;

	$counter = RQ::tbl( new PrimePosts() )->where( [ 'topic_id' => $topic_id ] )->get_count();

	$wpdb->update(
		RCL_PREF . 'pforum_topics', array(
		'post_count' => $counter
	), array(
			'topic_id' => $topic_id
		)
	);

	return $counter;
}

function pfm_topic_close( $topic_id ) {
	global $wpdb;

	return $wpdb->update(
		RCL_PREF . 'pforum_topics', array(
		'topic_closed' => 1
	), array(
			'topic_id' => $topic_id
		)
	);
}

function pfm_topic_unclose( $topic_id ) {
	global $wpdb;

	return $wpdb->update(
		RCL_PREF . 'pforum_topics', array(
		'topic_closed' => 0
	), array(
			'topic_id' => $topic_id
		)
	);
}

function pfm_update_topic_indexes( $topic_id ) {
	global $wpdb;

	//phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query(
		"UPDATE
			" . RCL_PREF . "pforum_posts
		SET
			post_index =(SELECT @a:= @a + 1 FROM (SELECT @a:= 0) s)
		WHERE
			topic_id = '$topic_id'
		ORDER BY
			post_date ASC"
	);
	//phpcs:enable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

function pfm_get_topic_field( $topic_id, $fieldName ) {
	return RQ::tbl( new PrimeTopics() )->select( [ $fieldName ] )->where( array(
		'topic_id' => $topic_id
	) )->get_var();
}

function get_topic_last_post( $topic_id ) {
	return RQ::tbl( new PrimePosts() )->where( [ 'topic_id' => $topic_id ] )->get_row();
}

function pfm_get_posts( $args = false ) {
	return RQ::tbl( new PrimePosts() )->parse( $args )->get_results();
}

function pfm_get_post( $post_id ) {

	return RQ::tbl( new PrimePosts() )
	         ->where( array( 'post_id' => $post_id ) )
	         ->get_row( 'cache' );
}

function pfm_get_post_field( $post_id, $fieldName ) {
	return RQ::tbl( new PrimePosts() )
	         ->select( [ $fieldName ] )
	         ->where( array( 'post_id' => $post_id ) )
	         ->get_var();
}

function pfm_delete_post( $post_id ) {
	global $wpdb;

	$post = pfm_get_post( $post_id );

	if ( ! $post ) {
		return false;
	}

	do_action( 'pfm_pre_delete_post', $post_id );

	//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$result = $wpdb->query( "DELETE FROM " . RCL_PREF . "pforum_posts WHERE post_id='$post_id'" );

	if ( $result ) {

		$post_count = pfm_get_topic_field( $post->topic_id, 'post_count' );

		if ( $post_count == 1 ) {

			pfm_delete_topic( $post->topic_id );
		} else {

			pfm_update_topic_data( $post->topic_id );
		}

		do_action( 'pfm_delete_post', $post_id );
	}

	return $result;
}

function pfm_add_post( $args ) {
	global $user_ID, $wpdb;

	if ( ! isset( $args['topic_id'] ) || ! $args['topic_id'] ) {
		return false;
	}

	if ( ! isset( $args['post_content'] ) || ! $args['post_content'] ) {
		return false;
	}

	if ( ! isset( $args['user_id'] ) ) {

		$args['user_id'] = $user_ID;
	}

	$postCount = RQ::tbl( new PrimePosts() )->where( array(
		'topic_id' => $args['topic_id']
	) )->get_count();

	$postCount ++;

	if ( ! isset( $args['post_status'] ) ) {
		$args['post_status'] = 'open';
	}

	if ( ! isset( $args['post_date'] ) ) {
		$args['post_date'] = current_time( 'mysql' );
	}

	if ( ! isset( $args['post_edit'] ) ) {
		$args['post_edit'] = '';
	}

	if ( ! isset( $args['guest_name'] ) ) {
		$args['guest_name'] = '';
	}

	if ( ! isset( $args['guest_email'] ) ) {
		$args['guest_email'] = '';
	}

	if ( ! isset( $args['post_index'] ) ) {
		$args['post_index'] = $postCount;
	}

	$args = apply_filters( 'pfm_pre_save_postdata', $args );

	$result = $wpdb->insert( RCL_PREF . 'pforum_posts', $args );

	if ( ! $result ) {
		return false;
	}

	$wpdb->update(
		RCL_PREF . 'pforum_topics', array(
		'post_count' => $postCount
	), array(
			'topic_id' => $args['topic_id']
		)
	);

	$post_id = $wpdb->insert_id;

	do_action( 'pfm_add_post', $post_id );

	return $post_id;
}

function pfm_update_post( $args ) {
	global $wpdb;

	if ( ! isset( $args['post_id'] ) ) {
		return false;
	}

	$post_id = $args['post_id'];

	$post = pfm_get_post( $post_id );

	if ( ! $post ) {
		return false;
	}

	if ( isset( $args['post_edit'] ) ) {
		$args['post_edit'] = maybe_serialize( $args['post_edit'] );
	}

	unset( $args['post_id'] );

	$result = $wpdb->update(
		RCL_PREF . 'pforum_posts', $args, array(
			'post_id' => $post_id
		)
	);

	do_action( 'pfm_after_update_post', $post_id );

	if ( ! $result ) {
		return false;
	}

	$post = RQ::tbl( new PrimePosts() )
	          ->where( array( 'post_id' => $post_id ) )
	          ->get_row();

	wp_cache_replace( json_encode( array( 'pfm_get_post', $post_id ) ), $post );

	do_action( 'pfm_update_post', $post_id );

	return $result;
}

function pfm_get_metas( $args = false ) {
	return RQ::tbl( new PrimeMeta() )->parse( $args )->get_results();
}

function pfm_get_meta( $object_id, $object_type, $meta_key ) {

	$cachekey = md5( json_encode( array( 'pfm_get_meta', $object_id, $object_type, $meta_key ) ) );
	$cache    = wp_cache_get( $cachekey );
	if ( $cache ) {
		return $cache;
	}

	$value = pfm_get_query_meta_value( $object_id, $object_type, $meta_key );

	if ( ! $value ) {
		$value = RQ::tbl( new PrimeMeta() )->select( [ 'meta_value' ] )->where( [
			'object_id'   => $object_id,
			'object_type' => $object_type,
			'meta_key'    => $meta_key,//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		] )->get_var();
	}

	wp_cache_add( $cachekey, $value );

	return $value;
}

function pfm_add_meta( $object_id, $object_type, $meta_key, $meta_value ) {
	global $wpdb;

	$args = array(
		'object_id'   => $object_id,
		'object_type' => $object_type,
		'meta_key'    => $meta_key,//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'  => maybe_serialize( $meta_value ) //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	);

	return $wpdb->insert( RCL_PREF . "pforum_meta", $args );
}

function pfm_update_meta( $object_id, $object_type, $meta_key, $meta_value ) {
	global $wpdb;

	if ( pfm_get_meta( $object_id, $object_type, $meta_key ) ) {

		$result = $wpdb->update( RCL_PREF . "pforum_meta", array(
			'meta_value' => maybe_serialize( $meta_value ) //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		), array(
				'object_id'   => $object_id,
				'object_type' => $object_type,
				'meta_key'    => $meta_key //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			)
		);
	} else {

		$result = pfm_add_meta( $object_id, $object_type, $meta_key, $meta_value );
	}

	return $result;
}

function pfm_delete_meta( $object_id, $object_type, $meta_key, $meta_value = false ) {
	global $wpdb;

	$sql = "DELETE FROM " . RCL_PREF . "pforum_meta "
	       . "WHERE "
	       . "object_id='$object_id' "
	       . "AND object_type='$object_type' "
	       . "AND meta_key='$meta_key'";

	if ( $meta_value ) {
		$sql .= " AND meta_value='$meta_value'";
	}

	//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	return $wpdb->query( $sql );
}

function pfm_get_visits( $args = false ) {
	return RQ::tbl( new PrimeVisits() )->parse( $args )->get_results();
}

function pfm_get_visit( $user_id ) {
	return RQ::tbl( new PrimeVisits() )->where( array(
		'user_id' => $user_id
	) )->get_row();
}

function pfm_add_visit( $args ) {
	global $wpdb;

	$args = array(
		'user_id'    => $args['user_id'],
		'group_id'   => ( isset( $args['group_id'] ) ) ? $args['group_id'] : 0,
		'forum_id'   => ( isset( $args['forum_id'] ) ) ? $args['forum_id'] : 0,
		'topic_id'   => ( isset( $args['topic_id'] ) ) ? $args['topic_id'] : 0,
		'visit_date' => current_time( 'mysql' )
	);

	return $wpdb->insert( RCL_PREF . "pforum_visits", $args );
}

function pfm_update_visit( $args ) {
	global $wpdb;

	if ( ! isset( $args['user_id'] ) ) {
		return false;
	}

	if ( pfm_get_visit( $args['user_id'] ) ) {

		$result = $wpdb->update( RCL_PREF . "pforum_visits", array(
			'group_id'   => ( isset( $args['group_id'] ) ) ? $args['group_id'] : 0,
			'forum_id'   => ( isset( $args['forum_id'] ) ) ? $args['forum_id'] : 0,
			'topic_id'   => ( isset( $args['topic_id'] ) ) ? $args['topic_id'] : 0,
			'visit_date' => current_time( 'mysql' )
		), array(
				'user_id' => $args['user_id']
			)
		);
	} else {

		$result = pfm_add_visit( $args );
	}

	return $result;
}

function pfm_delete_visit( $user_id ) {
	global $wpdb;

	//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	return $wpdb->query( "DELETE FROM " . RCL_PREF . "pforum_visits "
	                     . "WHERE user_id='$user_id'" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
}

function pfm_get_visitors_data( $args, $timeout = false ) {
	global $wpdb;

	if ( ! $timeout ) {
		$timeout = rcl_get_option( 'timeout', 10 );
	}

	$args = array_merge( $args, array(
		'select' => array( 'user_id' ),
		'join'   => array(
			array(
				[ 'user_id', 'ID' ],
				RQ::tbl( new Rcl_Query( array(
					'name' => $wpdb->users,
					'as'   => 'wp_users',
					'cols' => array(
						'ID',
						'display_name'
					)
				) ) )->select( [ 'display_name' ] )
			)
		)
	) );

	$visits = RQ::tbl( new PrimeVisits() )->parse( $args );

	$visits->where_string( $visits->table['as'] . ".visit_date > date_sub('" . current_time( 'mysql' ) . "', interval $timeout minute)" );

	return $visits->get_results();
}

function pfm_get_visitors() {
	global $PrimeQuery;

	$args = array();

	if ( $PrimeQuery->is_group ) {
		$args['group_id'] = $PrimeQuery->object->group_id;
	} else if ( $PrimeQuery->is_forum ) {
		$args['forum_id'] = $PrimeQuery->object->forum_id;
	} else if ( $PrimeQuery->is_topic ) {
		$args['topic_id'] = $PrimeQuery->object->topic_id;
	}

	return pfm_get_visitors_data( $args );
}
