<?php

function booked_is_timeslot_disabled( $date = false, $timeslot = false, $calendar_id = false ){

	if ( !$date || !$timeslot )
		return false;

	$disabled_timeslots = get_option( 'booked_disabled_timeslots', array() );
	$formatted_date = date( 'Y-m-d', strtotime( $date ) );

	if ( $calendar_id ):
		if ( isset( $disabled_timeslots[$calendar_id][$formatted_date][$timeslot] ) ):
			return true;
		endif;
	else:
		if ( isset( $disabled_timeslots[0][$formatted_date][$timeslot] ) ):
			return true;
		endif;
	endif;

	return false;

}

function booked_get_users(){
	$count = 0;
	$total_users = 0;
	$users_array = array();
	do {
		$array_counter = 0;
		$offset = $count * 50;
		$_users = array();
		$total_users = 0;
		$args = array(
			'orderby' 	=> 'display_name',
		    'number'    => 50,
		    'offset'    => $offset
		);
		$_users = get_users( $args );
		$total_users = count( $_users );
		if ( $total_users > 0 ):
			$array_counter = 0;
			foreach( $_users as $user ):
				$users_array[$offset] = $user;
				$offset++; $array_counter++;
				if ( $array_counter == 50 ):
					break;
				endif;
			endforeach;
		endif;
		$count++;
	} while ( $total_users > 0 );
	return $users_array;
}


function booked_appointments_available( $year = false, $month = false, $day = false, $calendar_id = false, $return_array = false, $include_past = false ){

	$prevent_before = apply_filters('booked_prevent_appointments_before',get_option('booked_prevent_appointments_before',false));
	$prevent_after = apply_filters('booked_prevent_appointments_after',get_option('booked_prevent_appointments_after',false));
	$buffer = get_option('booked_appointment_buffer',0);
	$buffer_string = apply_filters('booked_appointment_buffer_string','+'.$buffer.' hours');
	$disabled_timeslots = get_option( 'booked_disabled_timeslots',array() );

	if ( !$include_past && $buffer ):
		$buffered_timestamp = strtotime( date_i18n( 'Y-m-d H:i:s' ) . $buffer_string );
	else:
		$buffered_timestamp = false;
	endif;

	if ( !$include_past && $prevent_before ):
		$prevent_before = date_i18n('Ymd',strtotime($prevent_before));
	endif;

	if ( !$include_past && $prevent_after ):
		$prevent_after = date_i18n('Ymd',strtotime($prevent_after));
	endif;

	if ( !$day ):

		$month = date_i18n('m',strtotime($year.'-'.$month));
		$local_time = current_time('timestamp');
		$current_month = date_i18n('Ym',$local_time);

		if (!$include_past && $year.$month < $current_month):
			return 0;
		elseif (!$include_past && $year.$month > $current_month):
			$check_timestamp = strtotime(date_i18n('Y-m-d H:i:s',$local_time).' '.$buffer_string);
			$start_timestamp = strtotime($year.'-'.$month.'-01 00:00:00');
			if ($check_timestamp > $start_timestamp):
				$hours_between = $check_timestamp - $start_timestamp;
				$hours_between = $hours_between / 3600;
			else:
				$hours_between = 0;
			endif;
			$start_timestamp = strtotime(date_i18n('Y-m-d H:i:s',$start_timestamp).' +'.floor($hours_between).' hours');
			$first_day = date_i18n('j',$start_timestamp);
			$last_day = date_i18n('t',strtotime($year.'-'.$month.'-01'));
			$end_timestamp = strtotime($year.'-'.$month.'-'.$last_day.' 23:59:59');
		elseif(!$include_past):
			$start_timestamp = strtotime($year.'-'.$month.'-'.date_i18n('d H:i:s',$local_time).' '.$buffer_string);
			$first_day = date_i18n('j',$start_timestamp);
			$last_day = date_i18n('t',strtotime($year.'-'.$month.'-01'));
			$end_timestamp = strtotime($year.'-'.$month.'-'.$last_day.' 23:59:59');
		else:
			$start_timestamp = strtotime($year.'-'.$month.'-01 '.$buffer_string);
			$first_day = date_i18n('j',$start_timestamp);
			$last_day = date_i18n('t',strtotime($year.'-'.$month.'-01'));
			$end_timestamp = strtotime($year.'-'.$month.'-'.$last_day.' 23:59:59');
		endif;

		$start_month = date_i18n('m',$start_timestamp);
		if (!$include_past && $start_month > $month):
			return 0;
		endif;

	else:

		$day = date_i18n('d',strtotime($year.'-'.$month.'-'.$day));
		$local_time = current_time('timestamp');
		$current_day = date_i18n('Ymd',$local_time);

		if (!$include_past && $year.$month.$day < $current_day):
			return 0;
		elseif (!$include_past && $year.$month.$day > $current_day):
			$check_timestamp = strtotime(date_i18n('Y-m-d H:i:s',$local_time).' '.$buffer_string);
			$start_timestamp = strtotime($year.'-'.$month.'-'.$day.' 23:59:59');
			if ($check_timestamp > $start_timestamp):
				$hours_between = $check_timestamp - $start_timestamp;
				$hours_between = floor( $hours_between / 3600 );
			else:
				$hours_between = 0;
			endif;
			$start_timestamp = strtotime(date_i18n('Y-m-d',$start_timestamp).' +'.$hours_between.' hours');
			$first_day = date_i18n('j',$start_timestamp);
			$last_day = date_i18n('j',$start_timestamp);
			$end_timestamp = strtotime($year.'-'.$month.'-'.$day.' 23:59:59');
		elseif(!$include_past):
			$start_timestamp = strtotime($year.'-'.$month.'-'.date_i18n('d H:i:s',$local_time).' '.$buffer_string);
			$first_day = date_i18n('j',$start_timestamp);
			$last_day = date_i18n('j',$start_timestamp);
			$end_timestamp = strtotime($year.'-'.$month.'-'.$day.' 23:59:59');
		else:
			$start_timestamp = strtotime( $year . '-' . $month . '-' . $day . ' 00:00:00' );
			$first_day = date_i18n('j',$start_timestamp);
			$last_day = date_i18n('j',$start_timestamp);
			$end_timestamp = strtotime( $year . '-' . $month . '-' . $day . ' 23:59:59' );
		endif;

		$start_day = date_i18n('d',$start_timestamp);
		if (!$include_past && $start_day > $day):
			return 0;
		endif;

	endif;

	$args = array(
		'post_type' => 'booked_appointments',
		'posts_per_page' => 100,
		'post_status' => 'any',
		'meta_query' => array(
			array(
				'key'     => '_appointment_timestamp',
				'value'   => array( strtotime( date_i18n('Y-m-d',$start_timestamp).' 00:00:00'), $end_timestamp ),
				'compare' => 'BETWEEN',
			)
		)
	);

	if ($calendar_id):
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'booked_custom_calendars',
				'field'    => 'term_id',
				'terms'    => $calendar_id,
			)
		);
	endif;

	$appointments_array = array();
	$appointments_booked = array();
	$available_timeslots_array = array();

	$bookedAppointments = new WP_Query($args);
	if($bookedAppointments->have_posts()):
		while ($bookedAppointments->have_posts()):
			$bookedAppointments->the_post();
			global $post;
			$timestamp = get_post_meta($post->ID, '_appointment_timestamp',true);
			$timeslot = get_post_meta($post->ID, '_appointment_timeslot',true);
			$this_day = date_i18n('d',$timestamp);
			$this_month = date_i18n('m',$timestamp);
			$appointments_booked[$year.$this_month.$this_day.'_'.$timeslot] = (isset($appointments_booked[$year.$this_month.$this_day.'_'.$timeslot]) ? $appointments_booked[$year.$this_month.$this_day.'_'.$timeslot] + 1 : 1);
		endwhile;
	endif;

	$appointments_booked = apply_filters('booked_appointments_booked_array', $appointments_booked);

	if ($calendar_id):
		$booked_defaults = get_option('booked_defaults_'.$calendar_id);
		if (!$booked_defaults):
			$booked_defaults = get_option('booked_defaults');
		endif;
	else :
		$booked_defaults = get_option('booked_defaults');
	endif;

	$booked_defaults = booked_apply_custom_timeslots_filter($booked_defaults,$calendar_id);
	$current_timestamp = $start_timestamp;
	$available_timeslots = 0;

	for($i = $first_day; $i <= $last_day; $i++){

		$day_name = date('D',strtotime($year.'-'.$month.'-'.$i));
		$date_string = date_i18n('Ymd',strtotime($year.'-'.$month.'-'.$i));

		if (isset($booked_defaults[$date_string]) && empty($booked_defaults[$date_string])):
			continue;
		endif;

		$this_date_compare = date_i18n('Ymd',strtotime($year.'-'.$month.'-'.$i));

		if (!$include_past && $prevent_before && $prevent_before > $this_date_compare || !$include_past && $prevent_after && $this_date_compare > $prevent_after):
			continue;
		endif;

		if (isset($booked_defaults[$date_string]) && !empty($booked_defaults[$date_string])):

			if (!is_array($booked_defaults[$date_string])):
				$booked_defaults[$date_string] = json_decode($booked_defaults[$date_string],true);
			endif;

			foreach($booked_defaults[$date_string] as $timeslot => $count):

				$date_check = date_i18n('Y-m-d',strtotime($date_string));
				$disabled_date_check = date('Y-m-d',strtotime($date_string));

				if ( $calendar_id && isset($disabled_timeslots[$calendar_id][$disabled_date_check][$timeslot]) || !$calendar_id && isset($disabled_timeslots[0][$disabled_date_check][$timeslot]) ):
					continue;
				endif;

				$timeslot_array = explode('-',$timeslot);
				$this_timeslot_startstamp = strtotime($year.'-'.$month.'-'.$i.' '.$timeslot_array[0]);

				if ( !$include_past || $include_past && $current_timestamp <= $this_timeslot_startstamp && apply_filters('booked_check_timeslot_startstamp',$this_timeslot_startstamp) ):
					if ( !$include_past && $buffered_timestamp <= $this_timeslot_startstamp ):
						$available_timeslots_array[$date_check][$timeslot] = ( isset($available_timeslots_array[$date_check][$timeslot]) ? $available_timeslots_array[$date_check][$timeslot] + $count : $count );
						$available_timeslots = $available_timeslots + $count;
					endif;
				endif;

				if (isset($appointments_booked[$date_string.'_'.$timeslot])):
					$available_timeslots = $available_timeslots - $appointments_booked[$date_string.'_'.$timeslot];
					$available_timeslots_array[$date_check][$timeslot] = ( isset($available_timeslots_array[$date_check][$timeslot]) ? $available_timeslots_array[$date_check][$timeslot] - $appointments_booked[$date_string.'_'.$timeslot] : 0 );
					if ($available_timeslots < 0): $available_timeslots = 0; endif;
					if ( isset($available_timeslots_array[$date_check][$timeslot]) && $available_timeslots_array[$date_check][$timeslot] < 0): $available_timeslots_array[$date_check][$timeslot] = 0; endif;
				endif;

			endforeach;

		elseif (isset($booked_defaults[$day_name]) && !empty($booked_defaults[$day_name])):

			if (!is_array($booked_defaults[$day_name])):
				$booked_defaults[$day_name] = json_decode($booked_defaults[$day_name],true);
			endif;

			foreach($booked_defaults[$day_name] as $timeslot => $count):

				$timeslot_array = explode('-',$timeslot);
				$this_timeslot_startstamp = strtotime($year.'-'.$month.'-'.$i.' '.$timeslot_array[0]);

				$date_check = date_i18n('Y-m-d',strtotime($date_string));
				$disabled_date_check = date('Y-m-d',strtotime($date_string));

				if ( $calendar_id && isset($disabled_timeslots[$calendar_id][$disabled_date_check][$timeslot]) || !$calendar_id && isset($disabled_timeslots[0][$disabled_date_check][$timeslot]) ):
					continue;
				endif;

				if ( $include_past || !$include_past && $current_timestamp <= $this_timeslot_startstamp && apply_filters('booked_check_timeslot_startstamp',$this_timeslot_startstamp)):
					if ( $include_past || !$include_past && $buffered_timestamp <= $this_timeslot_startstamp ):
						$available_timeslots_array[$date_check][$timeslot] = ( isset($available_timeslots_array[$date_check][$timeslot]) ? $available_timeslots_array[$date_check][$timeslot] + $count : $count );
						$available_timeslots = $available_timeslots + $count;
					endif;
				endif;

				if (isset($appointments_booked[$date_string.'_'.$timeslot])):
					$available_timeslots = $available_timeslots - $appointments_booked[$date_string.'_'.$timeslot];
					$available_timeslots_array[$date_check][$timeslot] = ( isset($available_timeslots_array[$date_check][$timeslot]) ? $available_timeslots_array[$date_check][$timeslot] - $appointments_booked[$date_string.'_'.$timeslot] : 0 );
					if ($available_timeslots < 0): $available_timeslots = 0; endif;
					if ( isset($available_timeslots_array[$date_check][$timeslot]) && $available_timeslots_array[$date_check][$timeslot] < 0): $available_timeslots_array[$date_check][$timeslot] = 0; endif;
				endif;

			endforeach;
		endif;

	}

	if ( $available_timeslots < 0 ):
		return 0;
	else:
		if ( $return_array ):
			return $available_timeslots_array;
		else:
			return $available_timeslots;
		endif;
	endif;

}

// Booked Front-End Calendar
function booked_fe_calendar($year = false,$month = false,$calendar_id = false,$force_calendar = false){

	do_action('booked_fe_calendar_before');

	$prevent_before = apply_filters('booked_prevent_appointments_before',get_option('booked_prevent_appointments_before',false));
	$prevent_after = apply_filters('booked_prevent_appointments_after',get_option('booked_prevent_appointments_after',false));

	if ($prevent_before):
		$prevent_before = date_i18n('Ymd',strtotime($prevent_before));
	endif;

	if ($prevent_after):
		$prevent_after = date_i18n('Ymd',strtotime($prevent_after));
	endif;

	$initial_month = $month;
	$initial_year = $year;
	$local_time = current_time('timestamp');

	if ($calendar_id == 'undefined'): $calendar_id = 0; endif;

	$year = ($year ? $year : date_i18n('Y',$local_time));
	$month = ($month ? $month : date_i18n('m',$local_time));
	$today = date_i18n('j',$local_time);
	$currentMonth = date_i18n('Y-m-01',$local_time);

	if (!$force_calendar):

		$saved_month = $month;
		$saved_year = $year;
		$counter = 0;

		do {

			$appointments_available = booked_appointments_available($year,$month,false,$calendar_id);
			if (!$appointments_available):
				if ($month == '12'):
					$month = '01';
					$year = $year + 1;
				else:
					$month = date_i18n('m',strtotime($year.'-'.$month.'-01 +1 month'));
				endif;
			else:
				break;
			endif;
			$counter++;

		} while (!$appointments_available && $counter <= 12);

		if ($counter > 12): $month = $saved_month; $year = $saved_year; endif;

		$currentMonth = date_i18n('Y-m-01',strtotime($year.'-'.$month.'-01'));

	else:

		$currentMonth = $force_calendar;

	endif;

	$last_day = date_i18n('t',strtotime($year.'-'.$month));

	$monthShown = date_i18n('Y-m-01',strtotime($year.'-'.$month.'-01'));

	$first_day_of_week = (get_option('start_of_week') == 0 ? 7 : 1); 	// 1 = Monday, 7 = Sunday, Get from WordPress Settings

	$start_timestamp = strtotime($year.'-'.$month.'-01 00:00:00 -7 days');
	$end_timestamp = strtotime($year.'-'.$month.'-'.$last_day.' 23:59:59 +7 days');

	$args = array(
		'post_type' => 'booked_appointments',
		'posts_per_page' => 100,
		'post_status' => 'any',
		'meta_query' => array(
			array(
				'key'     => '_appointment_timestamp',
				'value'   => array( $start_timestamp, $end_timestamp ),
				'compare' => 'BETWEEN',
			)
		)
	);

	if ($calendar_id):
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'booked_custom_calendars',
				'field'    => 'term_id',
				'terms'    => $calendar_id,
			)
		);
	endif;

	$appointments_array = array();

	$bookedAppointments = new WP_Query($args);
	if($bookedAppointments->have_posts()):
		while ($bookedAppointments->have_posts()):
			$bookedAppointments->the_post();
			global $post;
			$timestamp = get_post_meta($post->ID, '_appointment_timestamp',true);
			$timeslot = get_post_meta($post->ID, '_appointment_timeslot',true);
			$this_day = date_i18n('j',$timestamp);
			$this_month = date_i18n('m',$timestamp);
			$this_year = date_i18n('Y',$timestamp);
			$appointments_array[$this_year.$this_month.$this_day][$post->ID]['timeslot'] = $timeslot;
			$appointments_array[$this_year.$this_month.$this_day][$post->ID]['timestamp'] = $timestamp;
			$appointments_array[$this_year.$this_month.$this_day][$post->ID]['status'] = $post->post_status;
		endwhile;
	endif;

	$appointments_array = apply_filters( 'booked_appointments_date_array', $appointments_array );

	$hide_weekends = get_option('booked_hide_weekends',false);
	$hide_available_count = get_option('booked_hide_available_timeslots',false);
	$booked_pa_active = get_option('booked_public_appointments',false);

	// Appointments Array
	// [DAY] => [POST_ID] => [TIMESTAMP/STATUS]

	?><div class="booked-calendar<?php echo ($booked_pa_active ? ' booked-pa-active' : ''); ?>"<?php echo ($calendar_id ? ' data-calendar-id="'.$calendar_id.'"' : ''); ?><?php echo (!$force_calendar ? ' data-calendar-date="'.$currentMonth.'"' : ''); ?>>
		<div class="bc-head">

			<?php

			$next_month = date_i18n('Y-m-01', strtotime("+1 month", strtotime($year.'-'.$month.'-01')));
			$prev_month = date_i18n('Y-m-01', strtotime("-1 month", strtotime($year.'-'.$month.'-01')));

			$next_month_compare = date_i18n('Ymd',strtotime($next_month));
			if ($prevent_after && $next_month_compare > $prevent_after): $no_next_link = true; else: $no_next_link = false; endif;

			?>

			<div class="bc-row top">
				<div class="bc-col">
					<?php if ($monthShown != $currentMonth): ?><a href="#" data-goto="<?php echo $prev_month; ?>" class="page-left"><i class="fa-solid fa-arrow-left"></i></a><?php endif; ?>
					<span class="calendarSavingState">
						<i class="fa-solid fa-circle-notch fa-spin"></i>
					</span>
					<span class="monthName">
						<?php echo date_i18n("F Y", strtotime($year.'-'.$month.'-01')); ?>
						<?php if ($monthShown != $currentMonth): ?>
							<a href="#" class="backToMonth" data-goto="<?php echo $currentMonth; ?>"><?php esc_html_e('Back to','overbooked'); ?> <?php echo date_i18n('F',strtotime($currentMonth)); ?></a>
						<?php endif; ?>
					</span>
					<?php if (!$no_next_link): ?><a href="#" data-goto="<?php echo $next_month; ?>" class="page-right"><i class="fa-solid fa-arrow-right"></i></a><?php endif; ?>
				</div>
			</div>
			<div class="bc-row days">
				<?php if ($first_day_of_week == 7 && !$hide_weekends): echo '<div class="bc-col">' . date_i18n( 'D', strtotime('Sunday') ) . '</div>'; endif; ?>
				<div class="bc-col"><?php echo date_i18n( 'D', strtotime('Monday') ); ?></div>
				<div class="bc-col"><?php echo date_i18n( 'D', strtotime('Tuesday') ); ?></div>
				<div class="bc-col"><?php echo date_i18n( 'D', strtotime('Wednesday') ); ?></div>
				<div class="bc-col"><?php echo date_i18n( 'D', strtotime('Thursday') ); ?></div>
				<div class="bc-col"><?php echo date_i18n( 'D', strtotime('Friday') ); ?></div>
				<?php if (!$hide_weekends): ?><div class="bc-col"><?php echo date_i18n( 'D', strtotime('Saturday') ); ?></div><?php endif; ?>
				<?php if ($first_day_of_week == 1 && !$hide_weekends): echo '<div class="bc-col">'. date_i18n( 'D', strtotime('Sunday') ) .'</div>'; endif; ?>
			</div>
		</div>
		<div class="bc-body"><?php

			$today_date = date_i18n('Y',$local_time).'-'.date_i18n('m',$local_time).'-'.date_i18n('j',$local_time);
			$days = date_i18n("t",strtotime($year.'-'.$month.'-01'));	 	// Days in current month
			$lastmonth = date_i18n("t", mktime(0,0,0,$month-1,1,$year)); 	// Days in previous month

			$start = date_i18n("N", mktime(0,0,0,$month,1,$year)); 			// Starting day of current month
			if ($first_day_of_week == 7): $start = $start + 1; endif;
			if ($start > 7): $start = 1; endif;
			$finish = $days; 											// Finishing day of current month
			$laststart = $start - 1; 									// Days of previous month in calander

			$counter = 1;
			$nextMonthCounter = 1;

			if ($calendar_id):
				$booked_defaults = get_option('booked_defaults_'.$calendar_id);
				if (!$booked_defaults):
					$booked_defaults = get_option('booked_defaults');
				endif;
			else :
				$booked_defaults = get_option('booked_defaults');
			endif;

			$booked_defaults = booked_apply_custom_timeslots_filter($booked_defaults,$calendar_id);

			$buffer = get_option('booked_appointment_buffer',0);
			$buffer_string = apply_filters('booked_appointment_buffer_string','+'.$buffer.' hours');

			if($start > 5){ $rows = 6; } else { $rows = 5; }

			for($i = 1; $i <= $rows; $i++){
				echo '<div class="bc-row week">';
				$day_count = 0;
				for($x = 1; $x <= 7; $x++){

					$classes = array();
					$appointments_count = 0;
					$check_month = $month;

					if(($counter - $start) < 0){

						$date = (($lastmonth - $laststart) + $counter);
						$classes[] = 'prev-month';
						$check_month = $month - 1;
						if (strlen($check_month) < 2): $check_month = '0'.$check_month; endif;
						$day_name = date('D',strtotime($year.'-'.$check_month.'-'.$date));

					} else {

						if(($counter - $start) >= $days){

							$date = ($nextMonthCounter);
							$nextMonthCounter++;
							$classes[] = 'next-month';
							$check_month = $month + 1;
							if (strlen($check_month) < 2): $check_month = '0'.$check_month; endif;
							$day_name = date('D',strtotime($year.'-'.$check_month.'-'.$date));
							if ($day_count == 0): break; endif;

						} else {

							$date = ($counter - $start + 1);
							if($today == $counter - $start + 1){
								if ($today_date == $year.'-'.$month.'-'.$date):
									$classes[] = 'today';
								endif;
							}

							$day_name = date('D',strtotime($year.'-'.$month.'-'.$date));

						}

					}

					if ($buffer):
						$current_timestamp = $local_time;
						$buffered_timestamp = strtotime($buffer_string,$current_timestamp);
						$date_to_compare = $buffered_timestamp;
						$currentTime = date_i18n('H:i:s',$buffered_timestamp);
					else:
						$date_to_compare = $local_time;
						$currentTime = date_i18n('H:i:s');
					endif;

					$formatted_date = date_i18n('Ymd',strtotime($year.'-'.$check_month.'-'.$date));

					if (isset($booked_defaults[$formatted_date]) && !empty($booked_defaults[$formatted_date])):
						$full_count = (is_array($booked_defaults[$formatted_date]) ? $booked_defaults[$formatted_date] : json_decode($booked_defaults[$formatted_date],true));
					elseif (isset($booked_defaults[$formatted_date]) && empty($booked_defaults[$formatted_date])):
						$full_count = false;
					elseif (isset($booked_defaults[$day_name]) && !empty($booked_defaults[$day_name])):
						$full_count = $booked_defaults[$day_name];
					else :
						$full_count = false;
					endif;

					$total_full_count = 0;
					if ($full_count):
						foreach($full_count as $full_counter){
							$total_full_count = $total_full_count + $full_counter;
						}
					endif;

					if (isset($booked_defaults[$formatted_date]) && !is_array($booked_defaults[$formatted_date])):
						$booked_defaults[$formatted_date] = json_decode($booked_defaults[$formatted_date],true);
					endif;

					$appointments_count = 0;

					if (isset($appointments_array[$year.$check_month.$date]) && !empty($appointments_array[$year.$check_month.$date])):
						foreach($appointments_array[$year.$check_month.$date] as $appt):
							if (isset($booked_defaults[$formatted_date][$appt['timeslot']])):
								$appointments_count++;
							elseif (!isset($booked_defaults[$formatted_date]) && isset($booked_defaults[$day_name]) && !empty($booked_defaults[$day_name]) && isset($booked_defaults[$day_name][$appt['timeslot']])):
								$appointments_count = $appointments_count + 1;
							endif;
						endforeach;
					endif;

					$this_date_compare = date_i18n('Ymd',strtotime($year.'-'.$check_month.'-'.$date));

					if ($appointments_count >= $total_full_count && $total_full_count > 0):
						if ($prevent_before && $prevent_before > $this_date_compare || $prevent_after && $this_date_compare > $prevent_after):
							// No Booked Class added.
						else:
							$classes[] = 'booked';
						endif;
					endif;

					if (
						strtotime($year.'-'.$check_month.'-'.$date.' '.$currentTime) < $date_to_compare
						|| $prevent_before && $this_date_compare < $prevent_before
						|| $prevent_after && $this_date_compare > $prevent_after
						|| $appointments_count >= $total_full_count && strtotime($year.'-'.$check_month.'-'.$date.' '.$currentTime) < $date_to_compare
						|| $appointments_count >= $total_full_count && $total_full_count < 1
						|| $appointments_count >= $total_full_count && $prevent_before && $prevent_before > $this_date_compare
						|| $appointments_count >= $total_full_count && $prevent_after && $this_date_compare > $prevent_after):

						$classes[] = 'prev-date';

					endif;

					$check_year = $year;

					if ($check_month == 0):
						$check_month = 12;
						$check_year = $year - 1;
					elseif ($check_month == 13):
						$check_month = 1;
						$check_year = $year + 1;
					endif;

					$check_month = date_i18n('m',strtotime($year.'-'.$check_month.'-'.$date));
					$appointments_left = booked_appointments_available($year,$check_month,$date,$calendar_id);

					if (!$appointments_left):
						if (!$booked_pa_active):
							if (!in_array('prev-date',$classes)):
								$classes[] = 'prev-date';
							endif;
						endif;
					endif;

					$day_of_week = date_i18n('N',strtotime($check_year.'-'.$check_month.'-'.$date));

					if ($hide_weekends && $day_of_week == 6 || $hide_weekends && $day_of_week == 7):

						$html = '';

					else:

						$day_count++;

						$html = '<div data-date="'.$check_year.'-'.$check_month.'-'.$date.'" class="bc-col '.implode(' ',$classes).'">';
						$html .= '<span class="date'.(!$hide_available_count && $appointments_left > 0 && !in_array('prev-date',$classes) && !in_array('blur',$classes) ? ' tooltipster" title="'.sprintf( _n('%d Available','%d Available',$appointments_left,'overbooked'),$appointments_left) : (!$hide_available_count && !$appointments_left && $booked_pa_active && !in_array('prev-date',$classes) && !in_array('blur',$classes) ? ' tooltipster" title="'.esc_html__('None Available','overbooked').'"' : '')).'"><span class="number">'. $date .'</span></span>';
						$html .= '</div>';

						$combined_date = $year.'-'.$check_month.'-'.$date;
						echo apply_filters('booked_fe_single_date',$html,$combined_date,$classes);

					endif;

					$counter++;
					$class = '';
				}
				echo '</div>';
			} ?>
		</div>
	</div><?php

	do_action('booked_fe_calendar_after');

}

function booked_fe_calendar_date_content($date,$calendar_id = false){

	do_action('booked_fe_calendar_date_before');

	$hide_unavailable_timeslots = get_option('booked_hide_unavailable_timeslots',false);
	$hide_available_count = get_option('booked_hide_available_timeslots',false);
	$public_appointments = get_option('booked_public_appointments',false);
	$total_available = 0;

	echo '<div class="booked-appt-list">';

		/*
		Set some variables
		*/

		$local_time = current_time('timestamp');

		$year = date_i18n('Y',strtotime($date));
		$month = date_i18n('m',strtotime($date));
		$day = date_i18n('d',strtotime($date));

		$start_timestamp = strtotime($year.'-'.$month.'-'.$day.' 00:00:00');
		$end_timestamp = strtotime($year.'-'.$month.'-'.$day.' 23:59:59');

		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		$date_display = date_i18n($date_format,strtotime($date));
		$day_name = date('D',strtotime($date));

		/*
		Grab all of the appointments for this day
		*/

		$args = array(
			'post_type' => 'booked_appointments',
			'posts_per_page' => 500,
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key'     => '_appointment_timestamp',
					'value'   => array( $start_timestamp, $end_timestamp ),
					'compare' => 'BETWEEN'
				)
			)
		);

		if ($calendar_id):
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'booked_custom_calendars',
					'field'    => 'term_id',
					'terms'    => $calendar_id,
				)
			);
		endif;

		$appointments_array = array();

		$bookedAppointments = new WP_Query( apply_filters('booked_fe_date_content_query',$args) );
		if($bookedAppointments->have_posts()):
			while ($bookedAppointments->have_posts()):
				$bookedAppointments->the_post();
				global $post;
				$timestamp = get_post_meta($post->ID, '_appointment_timestamp',true);
				$timeslot = get_post_meta($post->ID, '_appointment_timeslot',true);
				$user_id = get_post_meta($post->ID, '_appointment_user',true);
				$day = date_i18n('d',$timestamp);
				$appointments_array[$post->ID]['post_id'] = $post->ID;
				$appointments_array[$post->ID]['timestamp'] = $timestamp;
				$appointments_array[$post->ID]['timeslot'] = $timeslot;
				$appointments_array[$post->ID]['status'] = $post->post_status;
				$appointments_array[$post->ID]['user'] = $user_id;
			endwhile;
		endif;

		$appointments_array = apply_filters('booked_appointments_array', $appointments_array);

		ob_start();

		if ($calendar_id):
			$booked_defaults = get_option('booked_defaults_'.$calendar_id);
			if (!$booked_defaults):
				$booked_defaults = get_option('booked_defaults');
			endif;
		else :
			$booked_defaults = get_option('booked_defaults');
		endif;

		$formatted_date = date_i18n('Ymd',strtotime($date));
		$disabled_formatted_date = date( 'Y-m-d', strtotime( $date ) );
		$booked_defaults = booked_apply_custom_timeslots_details_filter($booked_defaults,$calendar_id);

		if (isset($booked_defaults[$formatted_date]) && !empty($booked_defaults[$formatted_date])):
			$todays_defaults = (is_array($booked_defaults[$formatted_date]) ? $booked_defaults[$formatted_date] : json_decode($booked_defaults[$formatted_date],true));
			$todays_defaults_details = (is_array($booked_defaults[$formatted_date.'-details']) ? $booked_defaults[$formatted_date.'-details'] : json_decode($booked_defaults[$formatted_date.'-details'],true));
		elseif (isset($booked_defaults[$formatted_date]) && empty($booked_defaults[$formatted_date])):
			$todays_defaults = false;
			$todays_defaults_details = false;
		elseif (isset($booked_defaults[$day_name]) && !empty($booked_defaults[$day_name])):
			$todays_defaults = $booked_defaults[$day_name];
			$todays_defaults_details = ( isset($booked_defaults[$day_name]) ? $booked_defaults[$day_name.'-details'] : false );
		else :
			$todays_defaults = false;
			$todays_defaults_details = false;
		endif;

		/*
		There are timeslots available, let's loop through them
		*/

		if ($todays_defaults){

			ksort($todays_defaults);

			$temp_count = 0;

			foreach($todays_defaults as $timeslot => $count):

				$appts_in_this_timeslot = array();

				/*
				Are there any appointments in this particular timeslot?
				If so, let's create an array of them.
				*/

				foreach($appointments_array as $post_id => $appointment):
					if ($appointment['timeslot'] == $timeslot):
						$appts_in_this_timeslot[] = $post_id;
					endif;
				endforeach;

				/*
				Calculate the number of spots available based on total minus the appointments booked
				*/

				$spots_available = $count - count($appts_in_this_timeslot);
				$spots_available = ($spots_available < 0 ? 0 : $spots_available);

				/*
				Display the timeslot
				*/

				$disabled_timeslots = get_option( 'booked_disabled_timeslots', array() );

				$timeslot_parts = explode('-',$timeslot);

				$buffer = get_option('booked_appointment_buffer',0);
				$buffer_string = apply_filters('booked_appointment_buffer_string','+'.$buffer.' hours');

				if ($buffer):
					$current_timestamp = $local_time;
					$buffered_timestamp = strtotime($buffer_string,$current_timestamp);
					$current_timestamp = $buffered_timestamp;
				else:
					$current_timestamp = $local_time;
				endif;

				$this_timeslot_timestamp = strtotime($year.'-'.$month.'-'.$day.' '.$timeslot_parts[0]);
				$spots_available = apply_filters('booked_spots_available', $spots_available, $this_timeslot_timestamp);

				if ($current_timestamp < $this_timeslot_timestamp){
					$available = true;
				} else {
					$available = false;
				}

				if ( $calendar_id && isset($disabled_timeslots[$calendar_id][$disabled_formatted_date][$timeslot]) || !$calendar_id && isset($disabled_timeslots[0][$disabled_formatted_date][$timeslot]) ):
					continue;
				endif;

				if ($spots_available && $available || !$hide_unavailable_timeslots):

					$total_available = $total_available + $spots_available;

					$temp_count++;

					if ($timeslot_parts[0] == '0000' && $timeslot_parts[1] == '2400'):
						$timeslotText = esc_html__('All day','overbooked');
					else :
						$timeslotText = date_i18n($time_format,strtotime($timeslot_parts[0])) . (!get_option('booked_hide_end_times') ? ' &ndash; '.date_i18n($time_format,strtotime($timeslot_parts[1])) : '');
					endif;

					$only_titles = get_option('booked_show_only_titles',false);

					$title = '';
					if ( !empty( $todays_defaults_details[$timeslot] ) ) {
						$title = !empty($todays_defaults_details[$timeslot]['title']) ? $todays_defaults_details[$timeslot]['title'] : '';
					}

					if ($hide_unavailable_timeslots && !$available):
						$html = '';
					else:
						$button_text = (!$spots_available || !$available ? esc_html__('Unavailable','overbooked') : esc_html( _x('Book Appointment','Book a Single Appointment', 'booked') ) );
						$html = '<div class="timeslot bookedClearFix'.($title && $only_titles == true ? ' booked-hide-time' : '').($hide_available_count || !$available ? ' timeslot-count-hidden' : '').(!$available ? ' timeslot-unavailable' : '').($title ? ' has-title ' : '').'">';

							$html .= '<span class="timeslot-time'.($public_appointments ? ' booked-public-appointments' : '').'">';

								$html .= apply_filters('booked_fe_calendar_timeslot_before','',$this_timeslot_timestamp,$timeslot,$calendar_id);

								if ( $title ) {
									$html .= '<span class="timeslot-title">' . esc_html($title) . '</span>';
								}

								$html .= '<span class="timeslot-range"><i class="fa-solid fa-clock"></i>&nbsp;&nbsp;' . $timeslotText . '</span>';

								if (!$hide_available_count):
									$html .= '<span class="spots-available'.($spots_available == 0 ? ' empty' : '').'">';
										$html .= sprintf( _n( '%d space available', '%d spaces available', $spots_available, 'booked' ), $spots_available );
									$html .= '</span>';
								endif;

								if ($public_appointments && !empty($appts_in_this_timeslot)):
									$html .= '<span class="booked-public-appointment-title">'._n('Appointments in this time slot:','Appointments in this time slot:',count($appts_in_this_timeslot),'overbooked').'</span>';
									$html .= '<ul class="booked-public-appointment-list">';
									foreach($appts_in_this_timeslot as $appt_id):

										$user_id = get_post_meta($appt_id, '_appointment_user',true);
										$guest_name = get_post_meta($appt_id, '_appointment_guest_name',true);
										$guest_surname = get_post_meta($appt_id, '_appointment_guest_surname',true);
										$guest_email = get_post_meta($appt_id, '_appointment_guest_email',true);
										$post_status = get_post_status($appt_id);
										$post_status = ( $post_status == 'future' ? $post_status = 'publish' : $post_status = $post_status );

										if ($user_id):
											$html .= '<li>'.booked_get_name($user_id).($post_status != 'publish' ? ' <span class="booked-public-pending">(pending)</span>' : '').'</li>';
										elseif($guest_name):
											$html .= '<li>'.$guest_name.' '.$guest_surname.($post_status != 'publish' ? ' <span class="booked-public-pending">(pending)</span>' : '').'</li>';
										endif;

									endforeach;
									$html .= '</ul>';
								endif;

								$html .= apply_filters('booked_fe_calendar_timeslot_after','',$this_timeslot_timestamp,$timeslot,$calendar_id);

							$html .= '</span>';
							$html .= '<span class="timeslot-people"><button data-calendar-id="'.$calendar_id.'" data-title="'.esc_attr($title).'" data-timeslot="'.$timeslot.'" data-date="'.$date.'" class="new-appt button"'.(!$spots_available || !$available ? ' disabled' : '').'>'.( $title ? '<span class="timeslot-mobile-title">'.esc_html($title).'</span>' : '' ).'<span class="button-timeslot">'.apply_filters('booked_fe_mobile_timeslot_button',$timeslotText,$this_timeslot_timestamp,$timeslot,$calendar_id).'</span>'.apply_filters('booked_button_book_appointment', '<span class="button-text">' . $button_text . '</span>' . ( !$hide_available_count ? '<span class="spots-available' . ( $spots_available == 0 ? ' empty' : '' ) . '">' . sprintf( esc_html( _n( '%d space available', '%d spaces available', $spots_available, 'booked' ) ), $spots_available ) . '</span>' : '' ) ).'</button></span>';
						$html .= '</div>';
					endif;

					echo apply_filters('booked_fe_calendar_date_appointments',$html,$time_format,$timeslot_parts,$spots_available,$available,$timeslot,$date);

				endif;

			endforeach;

			if (!$temp_count):

				echo '<p>'.esc_html__('There are no appointment time slots available for this day.','overbooked').'</p>';

			endif;

		/*
		There are no default timeslots and no appointments booked for this particular day.
		*/

		} else {
			echo '<p>'.esc_html__('There are no appointment time slots available for this day.','overbooked').'</p>';
		}

		$appt_list_html = ob_get_clean();

		$single_text = _x( 'Available Appointment on %s', 'Single Appointment', 'overbooked' );
		$plural_text = _x( 'Available Appointments on %s', 'Multiple Appointments', 'overbooked' );
		echo '<h2><span>' . sprintf( _n( $single_text, $plural_text, $total_available, 'overbooked' ), '</span><strong>'.$date_display.'</strong><span>') . '</span></h2>';
		echo $appt_list_html;

	echo '</div>';

	do_action('booked_fe_calendar_date_after');

}

function booked_fe_appointment_list_content($date,$calendar_id = false,$force_day = false){

	$local_time = current_time('timestamp');
	$current_day = date_i18n('Ymd',$local_time);
	$public_appointments = get_option('booked_public_appointments',false);
	$total_available = 0;

	$prevent_before = apply_filters('booked_prevent_appointments_before',get_option('booked_prevent_appointments_before',false));
	$prevent_after = apply_filters('booked_prevent_appointments_after',get_option('booked_prevent_appointments_after',false));

	if ($prevent_before):
		$prevent_before = date_i18n('Ymd',strtotime($prevent_before));
	endif;

	if ($prevent_after):
		$prevent_after = date_i18n('Ymd',strtotime($prevent_after));
	endif;

	$year = date_i18n('Y',$local_time);
	$month = date_i18n('m',$local_time);
	$day = date_i18n('d',$local_time);
	$saved_date = $date;
	$counter = 0;

	do {

		$appointments_available = booked_appointments_available($year,$month,$day,$calendar_id);

		if (!$appointments_available):
			$new_date = strtotime($year.'-'.$month.'-'.$day.' +1 day');
			$year = date_i18n('Y',$new_date);
			$month = date_i18n('m',$new_date);
			$day = date_i18n('j',$new_date);
		else:
			break;
		endif;
		$counter++;

	} while (!$appointments_available && $counter <= 365);

	if ($counter >= 365): $day = date_i18n('d',strtotime($saved_date)); $month = date_i18n('m',strtotime($saved_date)); $year = date_i18n('Y',strtotime($saved_date)); endif;
	$earliest_date = $year.'-'.$month.'-'.$day;

	if (!$force_day):

		$date = date_i18n('Y-m-d',strtotime($year.'-'.$month.'-'.$day));
		$showing_earliest = true;

	else:

		$date = date_i18n('Y-m-d',strtotime($saved_date));
		$force_day_date = date_i18n('Ymd',strtotime($saved_date));
		if ($saved_date == $earliest_date): $showing_earliest = true; else: $showing_earliest = false; endif;

	endif;

	$prev_day = date_i18n('Ymd',strtotime($date.' -1 day'));
	$next_day = date_i18n('Ymd',strtotime($date.' +1 day'));

	$new_earliest_date = $earliest_date;

	if (isset($force_day_date) && $prev_day < $force_day_date):
		$new_earliest_date = date_i18n('Y-m-d',strtotime($force_day_date));
		$showing_earliest = true;
	endif;


	if ($prev_day >= $saved_date && $prev_day >= date_i18n('Ymd',strtotime($earliest_date))):
		$new_earliest_date = $earliest_date;
		$showing_earliest = false;
	endif;

	if ($prev_day >= date_i18n('Ymd',strtotime($earliest_date))):
		$new_earliest_date = $earliest_date;
		$showing_earliest = false;
	endif;

	$earliest_date = $new_earliest_date;

	do_action('booked_fe_calendar_date_before');
	
	

	echo '<div class="booked-appt-list" data-list-date="'.$date.'" data-min-date="'.($prevent_before ? date_i18n('Y-m-d',strtotime($prevent_before)) : $earliest_date).'" data-max-date="'.($prevent_after ? date_i18n('Y-m-d',strtotime($prevent_after)) : false).'">';
		
		/*
		Set some variables
		*/

		$year = date_i18n('Y',strtotime($date));
		$month = date_i18n('m',strtotime($date));
		$day = date_i18n('d',strtotime($date));

		$start_timestamp = strtotime($year.'-'.$month.'-'.$day.' 00:00:00');
		$end_timestamp = strtotime($year.'-'.$month.'-'.$day.' 23:59:59');

		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		$date_display = date_i18n($date_format,strtotime($date));
		$day_name = date('D',strtotime($date));

		/*
		Grab all of the appointments for this day
		*/

		$args = array(
			'post_type' => 'booked_appointments',
			'posts_per_page' => 500,
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key'     => '_appointment_timestamp',
					'value'   => array( $start_timestamp, $end_timestamp ),
					'compare' => 'BETWEEN'
				)
			)
		);

		if ($calendar_id):
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'booked_custom_calendars',
					'field'    => 'term_id',
					'terms'    => $calendar_id,
				)
			);
		endif;

		$appointments_array = array();

		$bookedAppointments = new WP_Query( apply_filters('booked_fe_date_content_query',$args) );
		if($bookedAppointments->have_posts()):
			while ($bookedAppointments->have_posts()):
				$bookedAppointments->the_post();
				global $post;
				$timestamp = get_post_meta($post->ID, '_appointment_timestamp',true);
				$timeslot = get_post_meta($post->ID, '_appointment_timeslot',true);
				$user_id = get_post_meta($post->ID, '_appointment_user',true);
				$day = date_i18n('d',$timestamp);
				$appointments_array[$post->ID]['post_id'] = $post->ID;
				$appointments_array[$post->ID]['timestamp'] = $timestamp;
				$appointments_array[$post->ID]['timeslot'] = $timeslot;
				$appointments_array[$post->ID]['status'] = $post->post_status;
				$appointments_array[$post->ID]['user'] = $user_id;
			endwhile;
		endif;

		$appointments_array = apply_filters('booked_appointments_array', $appointments_array);

		/*
		Start the list
		*/

		$this_date = date_i18n('Ymd',strtotime($date));

		if ($prevent_before && $this_date > $prevent_before || isset($current_day) && $prev_day >= $current_day && !$showing_earliest): $showing_prev = true; else: $showing_prev = false; endif;
		if ($prevent_after && $this_date >= $prevent_after): $showing_next = false; else: $showing_next = true; endif;

		ob_start();

		echo '<div class="booked-list-view-nav" data-calendar-id="'.$calendar_id.'">';
			if ($showing_prev):
				echo '<button data-date="'.date_i18n('Y-m-d',strtotime($prev_day)).'" class="booked-list-view-date-prev bb-small"><i class="fa-solid fa-arrow-left"></i>&nbsp;&nbsp;'.esc_html__('Previous','overbooked').'</button>';
			endif;
			if ($showing_next):
				echo '<button data-date="'.date_i18n('Y-m-d',strtotime($next_day)).'" class="booked-list-view-date-next bb-small">'.esc_html__('Next','overbooked').'&nbsp;&nbsp;<i class="fa-solid fa-arrow-right"></i></button>';
			endif;
			echo '<span class="booked-datepicker-wrap"><input data-min-date="'.$earliest_date.'" class="booked_list_date_picker" value="'.date_i18n('Y-m-d',strtotime($date)).'" type="hidden"><a href="#" class="booked_list_date_picker_trigger"><i class="fa-solid fa-calendar-days"></i></a></span>';
		echo '</div>';
		
		$appt_list_button_html = ob_get_clean();
		
		ob_start();

		/*
		Get today's default timeslots
		*/

		if ($calendar_id):
			$booked_defaults = get_option('booked_defaults_'.$calendar_id);
			if (!$booked_defaults):
				$booked_defaults = get_option('booked_defaults');
			endif;
		else :
			$booked_defaults = get_option('booked_defaults');
		endif;

		$formatted_date = date_i18n('Ymd',strtotime($date));
		$disabled_formatted_date = date('Y-m-d',strtotime($date));
		$booked_defaults = booked_apply_custom_timeslots_details_filter($booked_defaults,$calendar_id);

		if (isset($booked_defaults[$formatted_date]) && !empty($booked_defaults[$formatted_date])):
			$todays_defaults = (is_array($booked_defaults[$formatted_date]) ? $booked_defaults[$formatted_date] : json_decode($booked_defaults[$formatted_date],true));
			$todays_defaults_details = (is_array($booked_defaults[$formatted_date.'-details']) ? $booked_defaults[$formatted_date.'-details'] : json_decode($booked_defaults[$formatted_date.'-details'],true));
		elseif (isset($booked_defaults[$formatted_date]) && empty($booked_defaults[$formatted_date])):
			$todays_defaults = false;
			$todays_defaults_details = false;
		elseif (isset($booked_defaults[$day_name]) && !empty($booked_defaults[$day_name])):
			$todays_defaults = $booked_defaults[$day_name];
			$todays_defaults_details = isset( $booked_defaults[$day_name.'-details'] ) ? $booked_defaults[$day_name.'-details'] : false;
		else :
			$todays_defaults = false;
			$todays_defaults_details = false;
		endif;

		/*
		There are timeslots available, let's loop through them
		*/

		if ($todays_defaults){

			ksort($todays_defaults);

			$temp_count = 0;

			foreach($todays_defaults as $timeslot => $count):

				$appts_in_this_timeslot = array();

				/*
				Are there any appointments in this particular timeslot?
				If so, let's create an array of them.
				*/

				foreach($appointments_array as $post_id => $appointment):
					if ($appointment['timeslot'] == $timeslot):
						$appts_in_this_timeslot[] = $post_id;
					endif;
				endforeach;

				/*
				Calculate the number of spots available based on total minus the appointments booked
				*/

				$spots_available = $count - count($appts_in_this_timeslot);
				$spots_available = ($spots_available < 0 ? 0 : $spots_available);

				/*
				Display the timeslot
				*/

				$disabled_timeslots = get_option( 'booked_disabled_timeslots', array() );

				$timeslot_parts = explode('-',$timeslot);

				$buffer = get_option('booked_appointment_buffer',0);
				$buffer_string = apply_filters('booked_appointment_buffer_string','+'.$buffer.' hours');

				if ($buffer):
					$current_timestamp = $local_time;
					$buffered_timestamp = strtotime($buffer_string,$current_timestamp);
					$current_timestamp = $buffered_timestamp;
				else:
					$current_timestamp = $local_time;
				endif;

				$this_timeslot_timestamp = strtotime($year.'-'.$month.'-'.$day.' '.$timeslot_parts[0]);
				$spots_available = apply_filters('booked_spots_available', $spots_available, $this_timeslot_timestamp);

				if ($current_timestamp < $this_timeslot_timestamp){
					$available = true;
				} else {
					$available = false;
				}

				if ( $calendar_id && isset($disabled_timeslots[$calendar_id][$disabled_formatted_date][$timeslot]) || !$calendar_id && isset($disabled_timeslots[0][$disabled_formatted_date][$timeslot]) ):
					continue;
				endif;

				$hide_unavailable_timeslots = get_option('booked_hide_unavailable_timeslots',false);
				$hide_available_count = get_option('booked_hide_available_timeslots',false);

				if ($spots_available && $available || !$hide_unavailable_timeslots):

					$total_available = $total_available + $spots_available;

					$temp_count++;

					if ($timeslot_parts[0] == '0000' && $timeslot_parts[1] == '2400'):
						$timeslotText = esc_html__('All day','overbooked');
					else :
						$timeslotText = date_i18n($time_format,strtotime($timeslot_parts[0])) . (!get_option('booked_hide_end_times') ? ' &ndash; '.date_i18n($time_format,strtotime($timeslot_parts[1])) : '');
					endif;

					$title = '';
					if ( !empty( $todays_defaults_details[$timeslot] ) ) {
						$title = !empty($todays_defaults_details[$timeslot]['title']) ? $todays_defaults_details[$timeslot]['title'] : '';
					}

					$only_titles = get_option('booked_show_only_titles',false);

					if ($hide_unavailable_timeslots && !$available):
						$html = '';
					else:
						$button_text = (!$spots_available || !$available ? esc_html__('Unavailable','overbooked') : esc_html( _x('Book Appointment','Book a Single Appointment', 'booked') ));
						$html = '<div class="timeslot bookedClearFix'.($title && $only_titles == true ? ' booked-hide-time' : '').($hide_available_count || !$available ? ' timeslot-count-hidden' : '').(!$available ? ' timeslot-unavailable' : '').($title ? ' has-title ' : '').'">';
							$html .= '<span class="timeslot-time'.($public_appointments ? ' booked-public-appointments' : '').'">';

								$html .= apply_filters('booked_fe_calendar_timeslot_before','',$this_timeslot_timestamp,$timeslot,$calendar_id);

								if ( $title ) {
									$html .= '<span class="timeslot-title">' . esc_html($title) . '</span>';
								}

								$html .= '<span class="timeslot-range"><i class="fa-solid fa-clock"></i>&nbsp;&nbsp;' . $timeslotText . '</span>';
								if (!$hide_available_count): $html .= '<span class="spots-available'.($spots_available == 0 ? ' empty' : '').'">'.sprintf(_n('%d space available','%d spaces available',$spots_available,'overbooked'),$spots_available).'</span>'; endif;
								if ($public_appointments && !empty($appts_in_this_timeslot)):
									$html .= '<span class="booked-public-appointment-title">'._n('Appointments in this time slot:','Appointments in this time slot:',count($appts_in_this_timeslot),'overbooked').'</span>';
									$html .= '<ul class="booked-public-appointment-list">';
									foreach($appts_in_this_timeslot as $appt_id):

										$user_id = get_post_meta($appt_id, '_appointment_user',true);
										$html .= '<li>'.booked_get_name($user_id).'</li>';

									endforeach;
									$html .= '</ul>';
								endif;

								$html .= apply_filters('booked_fe_calendar_timeslot_after','',$this_timeslot_timestamp,$timeslot,$calendar_id);

							$html .= '</span>';
							$html .= '<span class="timeslot-people"><button data-calendar-id="'.$calendar_id.'" data-title="'.esc_attr($title).'" data-timeslot="'.$timeslot.'" data-date="'.$date.'" class="new-appt button"'.(!$spots_available || !$available ? ' disabled' : '').'>'.( $title ? '<span class="timeslot-mobile-title">'.esc_html($title).'</span>' : '' ).'<span class="button-timeslot">'.apply_filters('booked_fe_mobile_timeslot_button',$timeslotText,$this_timeslot_timestamp,$timeslot,$calendar_id).'</span>'.apply_filters('booked_button_book_appointment', '<span class="button-text">'.$button_text.'</span>').'</button></span>';
						$html .= '</div>';
					endif;

					echo apply_filters('booked_fe_calendar_date_appointments',$html,$time_format,$timeslot_parts,$spots_available,$available,$timeslot,$date);

				endif;

			endforeach;

			if (!$temp_count):

				echo '<p>'.esc_html__('There are no appointment time slots available for this day.','overbooked').'</p>';

			endif;

		/*
		There are no default timeslots and no appointments booked for this particular day.
		*/

		} else {
			echo '<p>'.esc_html__('There are no appointment time slots available for this day.','overbooked').'</p>';
		}

		$appt_list_html = ob_get_clean();

		echo '<div class="booked-appt-list-header">';
			echo '<h2'.(!$showing_prev ? ' class="booked-no-prev"' : '').'><span>'.sprintf(_n('Available Appointment on %s','Available Appointments on %s',$total_available,'overbooked'),'</span><strong>'.$date_display.'</strong>').'</h2>';
			echo $appt_list_button_html;
		echo '</div>';

		echo $appt_list_html;
		
	echo '</div>';

	do_action('booked_fe_calendar_date_after');

}

function booked_reset_password($user_login){

    global $wpdb, $wp_hasher;

    $user_login = sanitize_text_field( $user_login );

    if ( empty( $user_login) ) {
        return false;
    } else if ( is_email( $user_login ) ) {
        $user_data = get_user_by( 'email', trim( $user_login ) );
        if ( empty( $user_data ) ):
           return false;
        endif;
    } else {
        $login = trim($user_login);
        $user_data = get_user_by('login', $login);
    }

    do_action('lostpassword_post');

    if ( !$user_data ) return false;

    // redefining user_login ensures we return the right case in the email
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;

    do_action( 'retrieve_password', $user_login );

    $allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

    if ( ! $allow ):
        return false;
    elseif ( is_wp_error($allow) ):
        return false;
    endif;

    $key = wp_generate_password( 20, false );
    do_action( 'retrieve_password_key', $user_login, $key );

    if ( empty( $wp_hasher ) ) {
        require_once ABSPATH . 'wp-includes/class-phpass.php';
        $wp_hasher = new PasswordHash( 8, true );
    }
    $hashed = $wp_hasher->HashPassword( $key );
    $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

    $message = esc_html__('Someone requested that the password be reset for the following account:','overbooked') . "\r\n\r\n";
    $message .= network_home_url( '/' ) . "\r\n\r\n";
    $message .= sprintf(esc_html__('Username: %s','overbooked'), $user_login) . "\r\n\r\n";
    $message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.','overbooked') . "\r\n\r\n";
    $message .= esc_html__('To reset your password, visit the following address:','overbooked') . "\r\n\r\n";
    $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

    if ( is_multisite() ):
        $blogname = $GLOBALS['current_site']->site_name;
    else:
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    endif;

    $title = sprintf( esc_html__('[%s] Password Reset','overbooked'), $blogname );

    $title = apply_filters('retrieve_password_title', $title);
    $message = apply_filters('retrieve_password_message', $message, $key);

    if ( $message && !wp_mail($user_email, $title, $message) ):
    	return false;
    endif;

    return true;

}

function booked_appt_is_available($date,$timeslot,$calendar_id = false){

	$year = date_i18n('Y',strtotime($date));
	$month = date_i18n('m',strtotime($date));
	$day = date_i18n('d',strtotime($date));

	$start_timestamp = strtotime($year.'-'.$month.'-'.$day.' 00:00:00');
	$end_timestamp = strtotime($year.'-'.$month.'-'.$day.' 23:59:59');

	$date_format = get_option('date_format');
	$time_format = get_option('time_format');
	$date_display = date_i18n($date_format,strtotime($date));
	$day_name = date('D',strtotime($date));

	$args = array(
		'post_type' => 'booked_appointments',
		'posts_per_page' => 500,
		'post_status' => 'any',
		'meta_query' => array(
			array(
				'key'     => '_appointment_timestamp',
				'value'   => array( $start_timestamp, $end_timestamp ),
				'compare' => 'BETWEEN'
			)
		)
	);

	if ($calendar_id):
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'booked_custom_calendars',
				'field'    => 'term_id',
				'terms'    => $calendar_id,
			)
		);
	endif;

	$appointments_array = array();

	$bookedAppointments = new WP_Query( apply_filters('booked_fe_date_content_query',$args) );
	if($bookedAppointments->have_posts()):
		while ($bookedAppointments->have_posts()):
			$bookedAppointments->the_post();
			global $post;
			$appt_timestamp = get_post_meta($post->ID, '_appointment_timestamp',true);
			$appt_timeslot = get_post_meta($post->ID, '_appointment_timeslot',true);
			$appt_user_id = get_post_meta($post->ID, '_appointment_user',true);
			$appt_day = date_i18n('d',$appt_timestamp);
			$appointments_array[$post->ID]['post_id'] = $post->ID;
			$appointments_array[$post->ID]['timestamp'] = $appt_timestamp;
			$appointments_array[$post->ID]['timeslot'] = $appt_timeslot;
			$appointments_array[$post->ID]['status'] = $post->post_status;
			$appointments_array[$post->ID]['user'] = $appt_user_id;
		endwhile;
	endif;

	$appointments_array = apply_filters('booked_appointments_array', $appointments_array);

	if ($calendar_id):
		$booked_defaults = get_option('booked_defaults_'.$calendar_id);
		if (!$booked_defaults):
			$booked_defaults = get_option('booked_defaults');
		endif;
	else :
		$booked_defaults = get_option('booked_defaults');
	endif;

	$formatted_date = date_i18n('Ymd',strtotime($date));
	$booked_defaults = booked_apply_custom_timeslots_filter($booked_defaults,$calendar_id);

	if (isset($booked_defaults[$formatted_date]) && !empty($booked_defaults[$formatted_date])):
		$todays_defaults = (is_array($booked_defaults[$formatted_date]) ? $booked_defaults[$formatted_date] : json_decode($booked_defaults[$formatted_date],true));
	elseif (isset($booked_defaults[$formatted_date]) && empty($booked_defaults[$formatted_date])):
		$todays_defaults = false;
	elseif (isset($booked_defaults[$day_name]) && !empty($booked_defaults[$day_name])):
		$todays_defaults = $booked_defaults[$day_name];
	else :
		$todays_defaults = false;
	endif;

	$total_available = isset($todays_defaults[$timeslot]) ? $todays_defaults[$timeslot] : 0;

	foreach($appointments_array as $appt):
		if ($timeslot == $appt['timeslot'] && isset($todays_defaults[$appt['timeslot']])):
			$total_available--;
		endif;
	endforeach;

	if ($total_available < 0): $total_available = 0; endif;

	return $total_available;

}



function booked_custom_fields($calendar_id = false){

	if ($calendar_id):
		$custom_fields = json_decode(stripslashes(get_option('booked_custom_fields_'.$calendar_id)),true);
		if (empty($custom_fields)): $custom_fields = json_decode(stripslashes(get_option('booked_custom_fields')),true); endif;
	else:
		$custom_fields = json_decode(stripslashes(get_option('booked_custom_fields')),true);
	endif;

	if (!empty($custom_fields)):

		echo '<div class="cf-block">';

		$look_for_subs = false;
		$temp_count = 0;

		$data_attributes = ' data-calendar-id="' . intval($calendar_id) . '" ';
		$required_fields = [];
		
		foreach($custom_fields as $field):
	
			$field_parts = explode('---',$field['name']);
			$field_type = $field_parts[0];
			if ( $field_type == 'required' && isset( $field_parts[1] ) && isset( $field['value'] ) && $field['value'] ):
				$required_fields[] = $field_parts[1];
			endif;
		
		endforeach;

		foreach($custom_fields as $field):

			$temp_count++;

			$field_parts = explode('---',$field['name']);
			$field_type = $field_parts[0];
			$end_of_string = explode('___',$field_parts[1]);
			$numbers_only = $end_of_string[0];
			$is_required = in_array( $numbers_only, $required_fields );

			if ($look_for_subs):

				if ($field_type == 'single-checkbox'):

					?><span class="checkbox-radio-block"><input <?php echo $data_attributes ?> type="checkbox" name="<?php echo $field['name']; ?>[]" id="booked-checkbox-<?php echo $field['name'].'-'.$temp_count; ?>" value="<?php echo htmlentities($field['value'], ENT_QUOTES | ENT_IGNORE, "UTF-8"); ?>"> <label for="booked-checkbox-<?php echo $field['name'].'-'.$temp_count; ?>"><?php echo $field['value']; ?></label></span><?php

				elseif ($field_type == 'single-radio-button'):

					?><span class="checkbox-radio-block"><input <?php echo $data_attributes ?> type="radio" name="<?php echo $field['name']; ?>[]" id="booked-radio-<?php echo $field['name'].'-'.$temp_count; ?>" value="<?php echo htmlentities($field['value'], ENT_QUOTES | ENT_IGNORE, "UTF-8"); ?>"> <label for="booked-radio-<?php echo $field['name'].'-'.$temp_count; ?>"><?php echo $field['value']; ?></label></span><?php

				elseif ($field_type == 'single-drop-down'):

					?><option value="<?php echo htmlentities($field['value'], ENT_QUOTES | ENT_IGNORE, "UTF-8"); ?>"><?php echo htmlentities($field['value'], ENT_QUOTES | ENT_IGNORE, "UTF-8"); ?></option><?php

				else :

					if ($look_for_subs == 'checkboxes'):

						?></div><?php

					elseif ($look_for_subs == 'radio-buttons'):

						?></div><?php

					elseif ($look_for_subs == 'dropdowns'):

						?></select></div><?php

					endif;

					$reset_subs = apply_filters(
						'booked_custom_fields_add_template_subs',
						$field_type,
						$field['name'],
						$field['value'],
						$is_required,
						$look_for_subs,
						$numbers_only,
						$data_attributes
					);

					if ( $reset_subs ) {
						$look_for_subs = false;
					}

				endif;

			endif;

			switch($field_type):

				case 'single-line-text-label' :

					?><div class="field">
						<label class="field-label"><?php echo $field['value']; ?><?php if ($is_required): ?><i class="required-asterisk fa-solid fa-asterisk"></i><?php endif; ?></label>
						<input id="booked-textfield-<?php echo esc_attr($field['name']); ?>" <?php echo $data_attributes ?> <?php if ($is_required): echo ' required="required"'; endif; ?> type="text" name="<?php echo esc_attr($field['name']); ?>" value="" class="large textfield" />
					</div><?php

				break;

				case 'paragraph-text-label' :

					?><div class="field">
						<label class="field-label"><?php echo $field['value']; ?><?php if ($is_required): ?><i class="required-asterisk fa-solid fa-asterisk"></i><?php endif; ?></label>
						<textarea id="booked-textarea-<?php echo esc_attr($field['name']); ?>" <?php echo $data_attributes ?> <?php if ($is_required): echo ' required="required"'; endif; ?> name="<?php echo esc_attr($field['name']); ?>"></textarea>
					</div><?php

				break;

				case 'checkboxes-label' :

					?><div class="field">
						<label class="field-label"><?php echo $field['value']; ?><?php if ($is_required): ?><i class="required-asterisk fa-solid fa-asterisk"></i><?php endif; ?></label>
						<input id="booked-checkbox-label-<?php echo esc_attr($field['name']); ?>" <?php echo $data_attributes ?> <?php if ($is_required): echo ' required="required"'; endif; ?> type="hidden" name="<?php echo esc_attr($field['name']); ?>" /><?php
					$look_for_subs = 'checkboxes';

				break;

				case 'radio-buttons-label' :

					?><div class="field">
						<label class="field-label"><?php echo $field['value']; ?><?php if ($is_required): ?><i class="required-asterisk fa-solid fa-asterisk"></i><?php endif; ?></label>
						<input id="booked-radio-label-<?php echo esc_attr($field['name']); ?>" <?php echo $data_attributes ?> <?php if ($is_required): echo ' required="required"'; endif; ?> type="hidden" name="<?php echo esc_attr($field['name']); ?>" /><?php
					$look_for_subs = 'radio-buttons';

				break;

				case 'drop-down-label' :

					?><div class="field">
						<label class="field-label"><?php echo $field['value']; ?><?php if ($is_required): ?><i class="required-asterisk fa-solid fa-asterisk"></i><?php endif; ?></label>
						<input id="booked-select-label-<?php echo esc_attr($field['name']); ?>" type="hidden" name="<?php echo esc_attr($field['name']); ?>" />
						<select id="booked-select-<?php echo esc_attr($field['name']); ?>" <?php echo $data_attributes ?> <?php if ($is_required): echo ' required="required"'; endif; ?> name="<?php echo esc_attr($field['name']); ?>"><option value=""><?php esc_html_e('Choose...','overbooked'); ?></option><?php
					$look_for_subs = 'dropdowns';

				break;

				case 'plain-text-content' :

					?><div id="booked-text-<?php echo $field['name']; ?>" class="field booked-text-content">
						<?php echo wpautop($field['value']); ?>
					</div><?php

				break;

				default:
					$look_for_subs_action = apply_filters(
						'booked_custom_fields_add_template_main',
						false, // default value to return when there is no addon plugin to hook on it
						$field_type,
						$field['name'],
						$field['value'],
						$is_required,
						$look_for_subs,
						$numbers_only,
						$data_attributes
					);

					$look_for_subs = $look_for_subs_action ? $look_for_subs_action : $look_for_subs;
				break;

			endswitch;

		endforeach;

		if ($look_for_subs):

			do_action('booked_custom_fields_add_template_subs_end', $field_type, $look_for_subs);

			if ($look_for_subs == 'checkboxes'):

				?></div><?php

			elseif ($look_for_subs == 'radio-buttons'):

				?></div><?php

			elseif ($look_for_subs == 'dropdowns'):

				?></select></div><?php

			endif;

		endif;

		echo '</div>';

	endif;

}

function booked_hidden_login_field(){
	$additional_field = '<input type="hidden" name="booked_login_form" value="1">';
	return $additional_field;
}

add_action( 'wp_login_failed', 'booked_fe_login_fail' );  // hook failed login
function booked_fe_login_fail( $username ) {
	$http_referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '';
	$booked_login_form = isset($_POST['booked_login_form']) ? sanitize_text_field($_POST['booked_login_form']) : '';
	
	if ( $http_referer && $booked_login_form ):
		$referrer = explode('?', $http_referer);
		$referrer = $referrer[0];
		$woo_nonce = isset($_REQUEST['woocommerce-login-nonce']) ? sanitize_text_field($_REQUEST['woocommerce-login-nonce']) : '';
		
		if ( !$woo_nonce && !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
			wp_safe_redirect( $referrer . '?loginfailed' );
			exit;
		}
	endif;
}

function booked_send_user_approved_email($appt_id){

	$email_content = get_option('booked_approval_email_content');
	$email_subject = get_option('booked_approval_email_subject');

	if ($email_content && $email_subject):

		$token_replacements = booked_get_appointment_tokens( $appt_id );
		$email_content = booked_token_replacement( $email_content,$token_replacements );
		$email_subject = booked_token_replacement( $email_subject,$token_replacements );

		do_action( 'booked_approved_email', $token_replacements['email'], $email_subject, $email_content );

	endif;

}

function booked_user_appointments($user_id,$only_count = false,$time_format = false,$date_format = false,$historic = false){

	if (!$date_format || !$time_format){
		$time_format = get_option('time_format');
		$date_format = get_option('date_format');
	}

	$order = $historic ? 'DESC' : 'ASC';
	$count = $historic ? 50 : -1;

	$args = array(
		'post_type' => 'booked_appointments',
		'posts_per_page' => $count,
		'post_status' => 'any',
		'author' => $user_id,
		'meta_key' => '_appointment_timestamp',
		'orderby' => 'meta_value_num',
		'order' => $order
	);

	$appointments_array = array();

	$bookedAppointments = new WP_Query($args);

	if($bookedAppointments->have_posts()):
		while ($bookedAppointments->have_posts()):

			$bookedAppointments->the_post();
			global $post;
			$appt_date_value = date_i18n('Y-m-d',get_post_meta($post->ID, '_appointment_timestamp',true));
			$appt_timeslot = get_post_meta($post->ID, '_appointment_timeslot',true);
			$appt_timeslots = explode('-',$appt_timeslot);
			$appt_time_start = date_i18n('H:i:s',strtotime($appt_timeslots[0]));

			$appt_timestamp = strtotime($appt_date_value.' '.$appt_time_start);
			$current_timestamp = current_time('timestamp');

			$day = date_i18n('d',$appt_timestamp);
			$calendar_id = wp_get_post_terms( $post->ID, 'booked_custom_calendars' );

			if (!$historic && $appt_timestamp >= $current_timestamp || $historic && $appt_timestamp < $current_timestamp){
				$appointments_array[$post->ID]['post_id'] = $post->ID;
				$appointments_array[$post->ID]['timestamp'] = $appt_timestamp;
				$appointments_array[$post->ID]['timeslot'] = $appt_timeslot;
				$appointments_array[$post->ID]['calendar_id'] = $calendar_id;
				$appointments_array[$post->ID]['status'] = $post->post_status;
			}

		endwhile;
	endif;

	$appointments_array = apply_filters('booked_appointments_array', $appointments_array);

	wp_reset_postdata();
	if ($only_count):
		return count($appointments_array);
	else :
		return $appointments_array;
	endif;

}

function booked_profile_update_submit(){

	if (is_user_logged_in()):

		global $error,$post;
		$booked_current_user = wp_get_current_user();

		$error = array();

		$request_method = isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field($_SERVER['REQUEST_METHOD']) : '';
		$action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
		
		if ( 'POST' == $request_method && !empty( $action ) && $action == 'update-user' ) {

		    // Verify nonce for security
		    if ( !wp_verify_nonce( $_POST['_wpnonce'], 'update-user' ) ) {
		        $error[] = esc_html__('Security verification failed. Please try again.', 'booked');
		        return;
		    }

		    /* Update user password. */
		    $pass1 = isset($_POST['pass1']) ? $_POST['pass1'] : '';
		    $pass2 = isset($_POST['pass2']) ? $_POST['pass2'] : '';
		    
		    if ( $pass1 && $pass2 ) {
		        if ( $pass1 == $pass2 )
		            wp_update_user( array( 'ID' => $booked_current_user->ID, 'user_pass' => $pass1 ) );
		        else
		            $error[] = esc_html__('The passwords you entered do not match.  Your password was not updated.', 'profile');
		    }

		    /* Update user information. */
		    if ( isset( $_POST['url'] ) )
		    	wp_update_user( array( 'ID' => $booked_current_user->ID, 'user_url' => esc_url( $_POST['url'] ) ) );
		    	
		    if ( isset( $_POST['email'] ) ){
		    	$new_email = sanitize_email( $_POST['email'] );
		    	$email_exists = email_exists( $new_email );

		        if (!is_email( $new_email ))
		            $error[] = esc_html__('The Email you entered is not valid.  please try again.', 'profile');
		        elseif( $email_exists && $email_exists != $booked_current_user->ID )
		            $error[] = esc_html__('This email is already used by another user.  try a different one.', 'profile');
		        else{
		            wp_update_user( array ('ID' => $booked_current_user->ID, 'user_email' => $new_email ));
		        }
		    }

		    if ( isset( $_POST['nickname'] ) ):
		        $nickname = sanitize_text_field( $_POST['nickname'] );
		        update_user_meta( $booked_current_user->ID, 'nickname', $nickname );
		        wp_update_user( array ('ID' => $booked_current_user->ID, 'display_name' => $nickname ));
		    endif;

			// Avatar Upload
	        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0):
			    $avatar = isset($_FILES['avatar']) && $_FILES['avatar'] ? $_FILES['avatar'] : false;
			    if (isset($avatar,$_POST['avatar_nonce']) && $avatar && wp_verify_nonce( $_POST['avatar_nonce'], 'avatar_upload' )) {

				    require_once( ABSPATH . 'wp-admin/includes/image.php' );
				    require_once( ABSPATH . 'wp-admin/includes/file.php' );
				    require_once( ABSPATH . 'wp-admin/includes/media.php' );

				    $attachment_id = media_handle_upload( 'avatar', 0 );
				    if ( is_wp_error( $attachment_id ) ) {
				        $error[] = esc_html__('Error uploading avatar.','overbooked');
				    } else {
				        update_user_meta( $booked_current_user->ID, 'avatar', $attachment_id );
				    }

			    } else {

			    	$error[] = esc_html__('Avatar uploader security check failed.','overbooked');

			    }
			endif;
			// END AVATAR

		    /* Redirect so the page will show updated info.*/
		    if ( count($error) == 0 ) {
		        //action hook for plugins and extra fields saving
		        do_action('edit_user_profile_update', $booked_current_user->ID);
				wp_redirect( get_permalink($post->ID) );
		        exit;
		    }
		}

	endif;

}

add_action('template_redirect','booked_profile_update_submit');

function booked_wpml_ajax(){
	if ( isset( $_GET['wpml_lang'] ) && $_GET['wpml_lang'] ):
    	do_action( 'wpml_switch_language',  esc_html( $_GET['wpml_lang'] ) );
	endif;
}

function booked_profile_content_appointments(){

	// Only check sessions if they're enabled and active
	if ( apply_filters( 'booked_sessions_enabled', false ) && session_status() === PHP_SESSION_ACTIVE ){
		if (isset($_SESSION['appt_requested']) && isset($_SESSION['new_account'])){

			$_SESSION['appt_requested'] = null;
			$_SESSION['new_account'] = null;

			echo '<p class="booked-form-notice">'.esc_html__('Your appointment has been requested! We have also set up an account for you. Your login information has been sent via email. When logged in, you can view your upcoming appointments below. Be sure to change your password to something more memorable by using the Edit Profile tab above.','overbooked').'</p>';

		} else if (isset($_SESSION['appt_requested'])){

			$_SESSION['appt_requested'] = null;

			$appointment_default_status = get_option('booked_new_appointment_default','draft');

			if ($appointment_default_status == 'draft'):
				echo '<p class="booked-form-notice">'.esc_html__('Your appointment has been requested! It will be updated below if approved.','overbooked').'</p>';
			else :
				echo '<p class="booked-form-notice">'.esc_html__('Your appointment has been added to our calendar!','overbooked').'</p>';
			endif;

		}
	}

	echo do_shortcode('[booked-appointments remove_wrapper=1]');

}

function booked_profile_content_history(){

	echo do_shortcode('[booked-appointments remove_wrapper=1 historic=1]');

}

function booked_profile_content_edit(){

	$booked_current_user = wp_get_current_user();

	echo '<h4><i class="fa-solid fa-pen-to-square" style="position:relative; top:-1px;"></i>&nbsp;&nbsp;'.esc_html__('Edit Profile','overbooked').'</h4>'; ?>

    <form method="post" enctype="multipart/form-data" id="booked-page-form" action="<?php the_permalink(); ?>">

	    <div class="bookedClearFix">
            <p class="form-avatar">
                <label for="avatar"><?php esc_html_e('Update Avatar', 'booked'); ?><?php if (BOOKED_DEMO_MODE): ?> <span class="not-bold"><?php esc_html_e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label><br>
                <span class="booked-upload-wrap"><span><?php esc_html_e('Choose image ...','overbooked'); ?></span><input<?php if (BOOKED_DEMO_MODE): ?> disabled<?php endif; ?> class="field" name="avatar" type="file" id="avatar" value="" /></span>
                <?php wp_nonce_field( 'avatar_upload', 'avatar_nonce' ); ?>
                <span class="hint-p"><?php esc_html_e('Recommended size: 100px by 100px or larger', 'booked'); ?></span>
            </p><!-- .form-nickname -->
    	</div>

        <div class="bookedClearFix">
            <p class="form-nickname">
                <label for="nickname"><?php esc_html_e('Display Name', 'booked'); ?><?php if (BOOKED_DEMO_MODE): ?> <span class="not-bold"><?php esc_html_e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label>
                <input<?php if (BOOKED_DEMO_MODE): ?> disabled<?php endif; ?> class="text-input" name="nickname" type="text" id="nickname" value="<?php the_author_meta( 'nickname', $booked_current_user->ID ); ?>" />
            </p><!-- .form-nickname -->
            <p class="form-email">
                <label for="email"><?php esc_html_e('E-mail *', 'booked'); ?><?php if (BOOKED_DEMO_MODE): ?> <span class="not-bold"><?php esc_html_e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label>
                <input<?php if (BOOKED_DEMO_MODE): ?> disabled<?php endif; ?> class="text-input" name="email" type="text" id="email" value="<?php the_author_meta( 'user_email', $booked_current_user->ID ); ?>" />
            </p><!-- .form-email -->
        </div>
        <div class="bookedClearFix">
            <p class="form-password">
                <label for="pass1"><?php esc_html_e('Change Password', 'booked'); ?><?php if (BOOKED_DEMO_MODE): ?> <span class="not-bold"><?php esc_html_e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label>
                <input<?php if (BOOKED_DEMO_MODE): ?> disabled<?php endif; ?> class="text-input" name="pass1" type="password" id="pass1" />
            </p><!-- .form-password -->
            <p class="form-password last">
                <label for="pass2"><?php esc_html_e('Repeat Password', 'booked'); ?><?php if (BOOKED_DEMO_MODE): ?> <span class="not-bold"><?php esc_html_e('(disabled in demo)', 'cooked'); ?></span><?php endif; ?></label>
                <input<?php if (BOOKED_DEMO_MODE): ?> disabled<?php endif; ?> class="text-input" name="pass2" type="password" id="pass2" />
            </p><!-- .form-password -->
        </div>

        <?php
            //action hook for plugin and extra fields
            do_action('edit_user_profile',$booked_current_user);
        ?>
        <p class="form-submit">
            <input name="updateuser" type="submit" id="updateuser" class="submit button button-primary" value="<?php esc_html_e('Update', 'booked'); ?>" />
            <?php wp_nonce_field( 'update-user' ) ?>
            <input name="action" type="hidden" id="action" value="update-user" />
        </p><!-- .form-submit -->
    </form><!-- #adduser --><?php

}
