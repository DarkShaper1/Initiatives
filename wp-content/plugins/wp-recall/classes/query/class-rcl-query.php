<?php

class Rcl_Query extends Rcl_Old_Query {

	public $table;
	public $serialize = array();
	public $query = array();

	function __construct( $table ) {

		if ( ! isset( $table['as'] ) ) {
			$table['as'] = $table['name'];
		}

		$this->table = $table;
		$this->reset_query();
	}

	function reset_query() {
		$this->query = array(
			'table'   => $this->table, //deprecated
			'select'  => array(),
			'where'   => array(),
			'join'    => array(),
			'number'  => 30,
			'offset'  => 0,
			'orderby' => false,
			'order'   => 'DESC',
			'having'  => array(),
			'groupby' => false
		);
	}

	function get_table_data( $dataName ) {
		return isset( $this->table[ $dataName ] ) ? $this->table[ $dataName ] : false;
	}

	function get_colname( $colname ) {
		return $this->table['as'] . '.' . $colname;
	}

	function parse( $args = false ) {

		if ( ! $args ) {
			return $this;
		}

		$args = wp_unslash( $args );

		if ( isset( $args['fields'] ) ) {
			//deprecated
			$args['select'] = $args['fields'];
			unset( $args['fields'] );
		}

		if ( ! isset( $args['select'] ) && ! isset( $args['date'] ) ) {
			foreach ( $this->table['cols'] as $col_name ) {
				$this->query['select'][] = $this->table['as'] . '.' . $col_name;
			}
		}

		foreach ( $args as $operator => $data ) {

			switch ( $operator ) {
				case 'select':
					$this->select( $data );
					break;
				case 'where':
					$this->where( $data );
					break;
				case 'date':
					foreach ( $data as $dateData ) {
						if ( ! isset( $dateData['colname'] ) || ! $dateData['colname'] ) {
							break;
						}
						if ( ! isset( $dateData['compare'] ) || ! $dateData['compare'] ) {
							$dateData['compare'] = '=';
						}
						$this->date( $dateData['colname'], $dateData['compare'], $dateData['data'] );
					}
					break;
				case 'join':
					foreach ( $data as $joinData ) {
						$this->join( $joinData[0], $joinData[1] );
					}
					break;
				case 'number':
					$this->number( $data );
					break;
				case 'offset':
					$this->offset( $data );
					break;
				case 'orderby':
					$this->orderby( $data );
					break;
				case 'order':
					$this->order( $data );
					break;
				case 'groupby':
					$this->groupby( $data );
					break;
				case 'cache':
					$this->set_cache( $data );
					break;
				default:
					$this->where( array( $operator => $data ) );
			}
		}

		return $this;
	}

	function set_cache( $cache ) {
		$this->cache = $cache;
	}

	function get_operator_data( $operator, $field_name = false, $use_cache = false ) {
		global $wpdb;

		$field_name = ( $field_name ) ? $field_name : $this->table['cols'][0];

		$query = $this->get_query();

		$sql = $this->get_sql( array(
			'select'  => array( $operator . '(' . $this->table['as'] . '.' . $field_name . ')' ),
			'join'    => $query['join'],
			'where'   => $query['where'],
			'groupby' => isset( $query['groupby'] ) ? $query['groupby'] : null
		) );

		if ( $use_cache ) {
			$cachekey = md5( $sql );
			$cache    = wp_cache_get( $cachekey );
			if ( $cache !== false ) {
				return $cache;
			}
		}
		//phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		if ( isset( $query['groupby'] ) && $query['groupby'] ) {
			$result = $wpdb->query( $sql );
		} else {
			$result = $wpdb->get_var( $sql );
		}
		//phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
		if ( $use_cache ) {
			wp_cache_add( $cachekey, $result );
		}

		return $result;
	}

	function set_operator_query( $operator, $data ) {

		$opers = explode( ' ', $operator );

		$operator = isset( $opers[1] ) ? $opers : $operator;

		foreach ( $data as $as_value => $col_name ) {
			$this->query['select'][] = $this->get_operator_string( $operator, $as_value, $col_name );
		}
	}

	function get_operator_string( $operator, $as_value, $col_name ) {

		if ( is_array( $operator ) ) {

			switch ( $operator[1] ) {
				case 'DISTINCT':
					return $operator[0] . '( ' . $operator[1] . ' ' . $this->table['as'] . '.' . $col_name . ')' . ( is_string( $as_value ) ? ' AS ' . $as_value : '' );
			}
		} else {
			return $operator . '(' . $this->table['as'] . '.' . $col_name . ')' . ( is_string( $as_value ) ? ' AS ' . $as_value : '' );
		}
	}

	function distinct( $select ) {

		foreach ( $select as $as_value => $data ) {
			if ( in_array( $data, $this->table['cols'] ) ) {
				$this->query['select'][] = 'DISTINCT ' . $this->table['as'] . '.' . $data . ( is_string( $as_value ) ? ' AS ' . $as_value : '' );
			} else if ( in_array( $as_value, array( 'count' ) ) ) {
				$this->set_operator_query( strtoupper( $as_value ) . ' DISTINCT', $data );
			}
		}

		return $this;
	}

	function select( $select = false ) {

		if ( ! $select ) {
			return $this;
		}

		if ( ! is_array( $select ) ) {
			if ( $select ) {
				$this->query['select'][] = $this->table['as'] . '.*';
			}
		} else {

			foreach ( $select as $as_value => $data ) {
				if ( in_array( $data, $this->table['cols'] ) ) {
					$this->query['select'][] = $this->table['as'] . '.' . $data . ( is_string( $as_value ) ? ' AS ' . $as_value : '' );
				} else if ( in_array( $as_value, array( 'count', 'max', 'min', 'sum' ) ) ) {
					$this->set_operator_query( strtoupper( $as_value ), $data );
				} else if ( is_object( $data ) ) {
					$this->query['select'][] = '(' . $data->limit( 0 )->get_sql() . ') AS ' . $as_value;
				}
			}
		}

		return $this;
	}

	function date( $col_name, $compare, $props ) {

		if ( $compare == '=' ) {

			if ( isset( $props['year'] ) ) {
				$this->query['where'][] = "YEAR(" . $this->table['as'] . ".$col_name) = '" . $props['year'] . "'";
			}

			if ( isset( $props['month'] ) ) {
				$this->query['where'][] = "MONTH(" . $this->table['as'] . ".$col_name) = '" . $props['month'] . "'";
			}

			if ( isset( $props['day'] ) ) {
				$this->query['where'][] = "DAY(" . $this->table['as'] . ".$col_name) = '" . $props['day'] . "'";
			}

			if ( isset( $props['last'] ) ) {
				$this->date( $col_name, '>=', array( 'interval' => $props['last'] ) );
			}

			if ( isset( $props['older'] ) ) {
				$this->date( $col_name, '<', array( 'interval' => $props['older'] ) );
			}
		} else if ( $compare == 'BETWEEN' ) {

			if ( $props ) {

				if ( ! $props[1] ) {
					$props[1] = current_time( 'mysql' );
				}

				$this->query['where'][] = "(" . $this->table['as'] . ".$col_name BETWEEN CAST('" . $props[0] . "' AS DATE) AND CAST('" . $props[1] . "' AS DATE))";
			}
		} else {

			if ( is_array( $props ) && isset( $props['interval'] ) ) {
				$this->query['where'][] = $this->table['as'] . ".$col_name $compare DATE_SUB(NOW(), INTERVAL " . $props['interval'] . ")";
			} else {
				$this->query['where'][] = $this->table['as'] . ".$col_name $compare '$props'";
			}
		}

		return $this;
	}

	function where( $where ) {
		foreach ( $this->table['cols'] as $col_name ) {
			if ( isset( $where[ $col_name ] ) ) {

				$data = $where[ $col_name ];

				if ( $data === 'is_null' ) {
					$this->query['where'][] = $this->table['as'] . ".$col_name IS NULL";
				} else if ( is_string( $data ) && strpos( $data, '.' ) !== false ) {
					$this->query['where'][] = $this->table['as'] . ".$col_name = '" . esc_sql( $data ) . "'";
				} else {
					$this->query['where'][] = $this->table['as'] . ".$col_name = " . ( is_object( $data ) ? "(" . $data->limit( 0 )->get_sql() . ")" : "'" . esc_sql( $data ) . "'" );
				}
			} else if ( isset( $where[ $col_name . '__in' ] ) && $where[ $col_name . '__in' ] ) {

				$data = $where[ $col_name . '__in' ];

				$this->query['where'][] = $this->table['as'] . ".$col_name IN (" . ( is_object( $data ) ? $data->limit( 0 )->get_sql() : $this->get_string_in( esc_sql( $data ) ) ) . ")";
			} else if ( isset( $where[ $col_name . '__not_in' ] ) && $where[ $col_name . '__not_in' ] ) {

				$data = $where[ $col_name . '__not_in' ];

				$this->query['where'][] = $this->table['as'] . ".$col_name NOT IN (" . ( is_object( $data ) ? $data->limit( 0 )->get_sql() : $this->get_string_in( esc_sql( $data ) ) ) . ")";
			} else if ( isset( $where[ $col_name . '__between' ] ) && $where[ $col_name . '__between' ] ) {

				$data = esc_sql( $where[ $col_name . '__between' ] );

				$this->query['where'][] = "(" . $this->table['as'] . '.' . $col_name . " BETWEEN IFNULL(" . $data[0] . ", 0) AND '" . $data[1] . "')";
			} else if ( isset( $where[ $col_name . '__like' ] ) && $where[ $col_name . '__like' ] ) {

				$data = $where[ $col_name . '__like' ];
				//maybe
				//global $wpdb;
				//$this->query['where'][] = $wpdb->prepare( $this->table['as'] . ".$col_name LIKE %s", '%' . $wpdb->esc_like( $data ) . '%' );

				$this->query['where'][] = $this->table['as'] . ".$col_name LIKE '%" . esc_sql( $data ) . "%'";
			} else if ( isset( $where[ $col_name . '__to' ] ) ) {

				$data = $where[ $col_name . '__to' ];

				$colName = is_numeric( $data ) ? "CAST(" . $this->table['as'] . ".$col_name AS DECIMAL)" : $this->table['as'] . "." . $col_name;

				$this->query['where'][] = $colName . " <= '" . esc_sql( $data ) . "'";
			} else if ( isset( $where[ $col_name . '__from' ] ) ) {

				$data = $where[ $col_name . '__from' ];

				$colName = is_numeric( $data ) ? "CAST(" . $this->table['as'] . ".$col_name AS DECIMAL)" : $this->table['as'] . "." . $col_name;

				$this->query['where'][] = $colName . " >= '" . esc_sql( $data ) . "'";
			} else if ( isset( $where[ $col_name . '__is' ] ) ) {

				$data = $where[ $col_name . '__is' ];
				//esc_sql ?
				$this->query['where'][] = $this->table['as'] . ".$col_name IS " . $data;
			}
		}

		return $this;
	}

	function select_string( $string ) {
		$this->query['select'][] = $string;

		return $this;
	}

	function where_string( $string ) {
		$this->query['where'][] = $string;

		return $this;
	}

	function having_string( $string ) {
		$this->query['having'][] = $string;

		return $this;
	}

	function orderby_string( $string ) {
		$this->query['orderby'][] = $string;

		return $this;
	}

	function get_string_in( $data ) {

		$vars = ( is_array( $data ) ) ? $data : explode( ',', $data );

		$vars = array_map( 'trim', $vars );

		$array = array();
		foreach ( $vars as $var ) {

			if ( is_numeric( $var ) ) {
				$array[] = $var;
			} else {
				$array[] = "'$var'";//esc_sql ?
			}
		}

		return implode( ',', $array );
	}

	function join( $joinProps, $joinQuery ) {

		if ( is_array( $joinProps ) ) {
			$joinType = isset( $joinProps[2] ) ? $joinProps[2] : 'INNER';
		} else { //if colnames of join is the same you can convey a colname as a string
			$joinType  = 'INNER';
			$joinProps = [ $joinProps, $joinProps ];
		}

		$this->query['join'][] = $joinType . " JOIN " . $joinQuery->table['name'] . " AS " . $joinQuery->table['as'] . " ON " . $this->table['as'] . "." . $joinProps[0] . " = " . $joinQuery->table['as'] . "." . $joinProps[1];

		if ( ! $this->query['select'] ) {
			foreach ( $this->table['cols'] as $col_name ) {
				$this->query['select'][] = $this->table['as'] . '.' . $col_name;
			}
		}

		if ( $joinQuery->query['select'] ) {
			$this->query['select'] = array_merge( $this->query['select'], $joinQuery->query['select'] );
		}
		if ( $joinQuery->query['where'] ) {
			$this->query['where'] = array_merge( $this->query['where'], $joinQuery->query['where'] );
		}
		if ( $joinQuery->query['join'] ) {
			$this->query['join'] = array_merge( $this->query['join'], $joinQuery->query['join'] );
		}

		$joinQuery->reset_query();

		return $this;
	}

	function limit( $number, $offset = 0 ) {
		$this->number( $number );
		$this->offset( $offset );

		return $this;
	}

	function number( $number ) {
		$this->query['number'] = $number;

		return $this;
	}

	function offset( $offset ) {
		$this->query['offset'] = $offset;

		return $this;
	}

	function groupby( $groupby ) {

		$this->query['groupby'] = count( explode( '.', $groupby ) ) > 1 ? $groupby : $this->table['as'] . '.' . $groupby;

		return $this;
	}

	function orderby( $orderby, $order = false ) {

		if ( is_array( $orderby ) ) {
			foreach ( $orderby as $by => $order ) {

				$by = count( explode( '.', $by ) ) > 1 ? $by : ( in_array( $by, $this->table['cols'] ) ? $this->table['as'] . '.' . $by : $by );

				$this->query['orderby'][ $by ] = $order;
			}
		} else {

			$this->query['orderby'] = count( explode( '.', $orderby ) ) > 1 ? $orderby : ( in_array( $orderby, $this->table['cols'] ) ? $this->table['as'] . '.' . $orderby : $orderby );

			if ( $order ) {
				$this->order( $order );
			}
		}

		return $this;
	}

	function order( $order ) {
		$this->query['order'] = $order;

		return $this;
	}

	function get_query() {
		return $this->query;
	}

	function get_sql( $query = false ) {
//Возможно тут надо все ескейпить, либо делать это в методах groupby, orderby и т.д.
		$query = $query ? $query : $this->get_query();

		if ( ! isset( $query['select'] ) || ! $query['select'] ) {
			$query['select'][] = $this->table['as'] . '.*';
		}

		$sql[] = "SELECT " . implode( ', ', $query['select'] );

		$sql[] = "FROM " . $this->table['name'] . " AS " . $this->table['as'];

		if ( isset( $query['join'] ) && $query['join'] ) {
			$sql[] = implode( ' ', $query['join'] );
		}

		$where = array();

		if ( isset( $query['where'] ) && $query['where'] ) {
			$where[] = implode( ' AND ', $query['where'] );
		}

		if ( isset( $query['where_or'] ) && $query['where_or'] ) {

			if ( $query['where'] ) {
				$where_or[] = 'OR';
			}

			$where_or[] = implode( ' OR ', $query['where_or'] );

			$where[] = implode( ' ', $where_or );
		}

		if ( $where ) {
			$sql[] = "WHERE " . implode( ' ', $where );
		}

		if ( isset( $query['union'] ) ) { //support old union request
			foreach ( $query['union'] as $unionQuery ) {

				$sql[] = "UNION ALL";

				$Query = new Rcl_Query( $unionQuery['table'] );

				$sql[] = $Query->get_sql( $unionQuery );
			}
		}

		if ( isset( $query['groupby'] ) && $query['groupby'] ) {
			$sql[] = "GROUP BY " . $query['groupby'];//esc_sql ?
		}

		if ( isset( $query['having'] ) && $query['having'] ) {
			$sql[] = "HAVING " . implode( ' AND ', $query['having'] );
		}

		$orderBy = '';
		if ( isset( $query['orderby'] ) && $query['orderby'] ) {

			if ( is_array( $query['orderby'] ) ) {
				$orders = array();
				foreach ( $query['orderby'] as $orderby => $order ) {
					$orders[] = $orderby . " " . $order;
				}
				$orderBy = implode( ",", $orders );
			} else {
				$orderBy = $query['orderby'] . " " . $query['order'];
			}
		} else {
			$orderBy = $this->table['as'] . "." . $this->table['cols'][0] . " " . ( isset( $query['order'] ) ? $query['order'] : 'DESC' );
		}

		if ( $orderBy ) {
			$sql[] = "ORDER BY " . $orderBy;
		}

		if ( isset( $query['number'] ) && $query['number'] ) {

			if ( $query['number'] < 0 ) {
				$query['number'] = 0;
			}

			if ( isset( $query['offset'] ) && $query['offset'] ) {
				$sql[] = "LIMIT " . $query['offset'] . "," . $query['number'];
			} else if ( isset( $query['number'] ) && $query['number'] ) {
				$sql[] = "LIMIT " . $query['number'];
			}
		} else if ( isset( $query['offset'] ) && $query['offset'] ) {
			$sql[] = "OFFSET " . $query['offset'];
		}

		$sql = implode( ' ', $sql );

		return $sql;
	}

	function get_data( $method = 'get_results', $use_cache = false, $return_as = false ) {
		global $wpdb;

		if ( $this->return_as ) {
			$return_as = $this->return_as;
		}

		$query = $this->get_query();

		if ( $use_cache || $this->cache ) {
			$cachekey = md5( json_encode( $query ) );
			$cache    = wp_cache_get( $cachekey );
			if ( $cache !== false ) {
				return $cache;
			}
		}

		$sql = $this->get_sql( $query );

		$data = $return_as ? $wpdb->$method( $sql, $return_as ) : $wpdb->$method( $sql );

		$data = $this->maybe_unserialize( $data );

		$data = wp_unslash( $data );

		if ( $use_cache ) {
			wp_cache_add( $cachekey, $data );
		}

		return $data;
	}

	function maybe_unserialize( $data ) {

		if ( ! $this->serialize ) {
			return $data;
		}

		if ( is_string( $data ) ) {
			return maybe_unserialize( $data );
		}

		foreach ( $this->serialize as $colName ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $k => $item ) {
					if ( is_object( $item ) ) {
						if ( isset( $item->$colName ) ) {
							$data[ $k ]->$colName = maybe_unserialize( $item->$colName );
						}
					} else {
						$data[ $k ] = maybe_unserialize( $item );
					}
				}
			} else if ( is_object( $data ) ) {
				if ( isset( $data->$colName ) ) {
					$data->$colName = maybe_unserialize( $data->$colName );
				}
			}
		}

		return $data;
	}

	function get_walker() {
		return new Rcl_Walker( $this->get_results() );
	}

	function get_var( $cache = false ) {
		if ( is_array( $cache ) ) {
			return parent::get_var( $cache );
		}

		return $this->get_data( 'get_var', $cache );
	}

	function get_results( $cache = false, $return_as = false ) {
		if ( is_array( $cache ) ) {
			return parent::get_results( $cache );
		}

		return $this->get_data( 'get_results', $cache, $return_as );
	}

	function get_row( $cache = false ) {
		if ( is_array( $cache ) ) {
			return parent::get_row( $cache );
		}

		return $this->get_data( 'get_row', $cache );
	}

	function get_col( $cache = false ) {
		if ( is_array( $cache ) ) {
			return parent::get_col( $cache );
		}

		return $this->get_data( 'get_col', $cache );
	}

	function get_count( $field_name = false, $cache = false ) {
		return ( ! $result = $this->get_operator_data( 'COUNT', $field_name, $cache ) ) ? 0 : $result;
	}

	function get_sum( $field_name = false, $cache = false ) {
		return ( ! $result = $this->get_operator_data( 'SUM', $field_name, $cache ) ) ? 0 : $result;
	}

	function get_max( $field_name = false, $cache = false ) {
		return $this->get_operator_data( 'MAX', $field_name, $cache );
	}

	function get_min( $field_name = false, $cache = false ) {
		return $this->get_operator_data( 'MIN', $field_name, $cache );
	}

}
