if (!function_exists('get_results_by_filters')) {
	function get_results_by_filters($table_name, $user_id = null, $or_columns, $and_columns = '', $order_by = '', $order = 'ASC', $current_page = '', $num_per_page = '', $date_column  = '', $from_date = '', $to_date = '') {
		global $wpdb;
		$sql_results = "SELECT * FROM $table_name WHERE";
		if ($user_id == null) {
			$get_user_filter = ' ';
		} else {
			if (!is_super_admin($user_id)) {
				$get_user_filter = " user_ID=" . $user_id . ' AND ';
			} else {
				$get_user_filter = ' ';
			}
		}
		if ($and_columns != '') {
			foreach ( $and_columns as $column ) {
				$and_arr[] = ' AND ' . $column[0] . " LIKE '%%" . $column[1] . "%%'";
			}
		}
		foreach ( $or_columns as $column ) {
			if ($date_column != '' && $from_date != '' && $to_date != '') {
				$from_date = strtotime($from_date);
				$from_date = date('d-m-Y',$from_date);
				$from_date = date_create($from_date);
				$from_date = date_format($from_date, 'Y-m-d') . " 00:00:00";
				$gmt_from_date = get_date_from_gmt( date( $from_date, get_option('gmt_offset') ) );
				$to_date = strtotime($to_date);
				$to_date = date('d-m-Y',$to_date);
				$to_date = date_create($to_date);
				$to_date = date_format($to_date, 'Y-m-d') . " 23:59:59";
				$gmt_to_date = get_date_from_gmt( date( $to_date, get_option('gmt_offset') ) );
				$between_results = " AND " . $date_column ." BETWEEN '" . $gmt_from_date . "' AND '" . $gmt_to_date . "'";
			}
			$or_arr[] = $get_user_filter . $column[0] . " LIKE '%%" . $column[1] . "%%'" . implode("", $and_arr) . $between_results;
		}
		$sql_results .= implode(" OR ", $or_arr);
		if ($order_by != '' && $order != '') {
			$sql_results .= ' ORDER BY ' . $order_by . ' ' . $order . ' ';
		}
		if ($current_page != '' && $num_per_page != '') {
			$start_from = ($current_page-1) * $num_per_page;
			$sql_results .= 'LIMIT ' . $start_from . ', ' . $num_per_page;
		}
		
		return $wpdb->get_results($wpdb->prepare($sql_results));
	}
}
