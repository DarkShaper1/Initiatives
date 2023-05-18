<?php

class Rcl_Field {
	static function setup( $args ) {
		global $wprecall;

		if ( is_admin() ) {
			rcl_font_awesome_style();
		}

		if ( isset( $wprecall->fields[ $args['type'] ] ) ) {

			$className = $wprecall->fields[ $args['type'] ]['class'];

			return new $className( $args );
		}
	}

}
