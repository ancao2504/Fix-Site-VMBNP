<?php
/**
 * Plugin Name: Cheap Flight
 * Plugin URI:  https://vietjet.net/
 * Description: Đây là plugin tìm vé giá rẻ theo tháng. 
 * Version: 2.0 
 * Author: Đoan Trinh 
 * Author URI: https://www.facebook.com/profile.php?id=100004174754705 
 * License: GPLv2 
 */

require_once(plugin_dir_path( __FILE__ ) . 'lib/lib-cheapflight.php');

function cheapflStartSession() {
    if(!session_id()) {
        session_start();
    }

}
add_action('init', 'cheapflStartSession', 1);

class Cheapflight_Setup_Class {
	//A reference to an instance of this class
	private static $instance;

 	//Returns an instance of this class
 	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new Cheapflight_Setup_Class();
		} 

		return self::$instance;
	}

 	public function __construct() {	

		add_action( 'wp_enqueue_scripts',  array( $this, 'enqueue_scripts_and_styles' ), 10 );

 		add_shortcode( 'FORM_TIMVERETHEOTHANG', array( $this, 'create_sanvegiare_shortcode' ) );

 		add_shortcode( 'VE_RE_THEO_THANG', array( $this, 'create_veretheothang_shortcode' ) );

 		add_filter('template_include', array ($this, 'page_cus_template' ) );

 		add_action( 'admin_menu', array( $this, 'register_cheapflight_admin_page' ) );

		add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles') );
 	}

 	public function create_sanvegiare_shortcode() {
		return create_form_search();
	}

	public function create_veretheothang_shortcode( $atts ) {
		$month = date('m', strtotime('next month'));
		$year = date('Y');
		$atts = shortcode_atts( 	
								array( 
										'dep' => 'SGN',
										'arv' => 'HAN',
										'month' => $month,
										'year' => $year,
										'multi' => 0,
									), $atts, 'VE_RE_THEO_THANG' );
		
		$form_vr = '<div class="col-md-12 calendar ve-re-calendar">' .
						'<h4 class="cal-title"><span class="cus-title">Giá vé máy bay </span><div class="visible-xs"><br></div> ' .
							'<select id="depCode">';
								$names = pl_checkactive($atts['dep'], $atts['arv'], true);
								foreach ($names as $name) {
									if($name != 'CXR') {
										if( $atts['dep'] == $name ) {
											$form_vr .= '<option value="' . $name . '" selected="selected">' . $GLOBALS['PLCODECITY'][$name] . '</option>';
										} else {
											$form_vr .= '<option value="' . $name . '">' . $GLOBALS['PLCODECITY'][$name] . '</option>';
										}
									}
								}
		$form_vr .=			'</select>' . 
							' đi '; 
								if( $atts['multi'] == 0 ) {
									$form_vr .= '<span id="desCode" value="' . $atts['arv'] . '">' . $GLOBALS['PLCODECITY'][$atts['arv']] . '</span>'; 
								} else {
									$form_vr .= '<select id="des-select">' . create_city_select($atts['dep'], $atts['arv']) . '</select>';

								}
		$form_vr .= 		' tháng ' .
							'<select class="depdate cus-depdate">' .
								$my_options = create_dropdown_my($atts['month'].'/'.$atts['year']);
								foreach ($my_options as $my_option) {
									$form_vr .= $my_option;
								}
		$form_vr .=			'</select>' .
						'</h4>' .
						'<div class="calendar-content"><table class="table"><div class="loading"></div><tr><th>Thứ 2</th><th>Thứ 3</th><th>Thứ 4</th><th>Thứ 5</th><th>Thứ 6</th><th>Thứ 7</th><th>CN</th></tr></table>' .
							'<div class="note-1">* Nhấn vào ngày bất kỳ để tìm giá vé mới nhất. Lưu ý giá vé hiện tại có thể đã thay đổi</div>' .
							'<div class="note-2">* Giá vé tốt nhất cho 1 người lớn (đã bao gồm thuế phí) được tìm thấy trong vòng 48 giờ</div>' . 
							'<div class="note-3">Hotline đặt vé máy bay giá rẻ: <span class="note-hotline"><strong>';
		if(function_exists('get_option')) { 
			$form_vr .=	get_option('opt_hotline'); 
		}
		
		$form_vr .=			'</strong></span></div>' .
						'</div>' .
					'</div>';
		$form_vr .= create_formsearch_dialog(get_bloginfo('url').'/chon-hanh-trinh', $GLOBALS['PLCODECITY'][$atts['dep']], $GLOBALS['PLCODECITY'][$atts['arv']], $_SESSION['fl_btn_search']);
		return $form_vr;
	}

	public static function activate_plugin_cheapflight() {
		$args1 = array( 
	                    'post_title'  => 'Tìm vé giá rẻ',
	                    'post_status' => 'publish',
	                    'post_type'   => 'page',
	                    'post_name'   => 'tim-ve-gia-re'
	                );
		create_page($args1, 'pl_find_cfl_page_id', 'tpl-tim-ve-gia-re.php', 'find_cfl_page_template', 'tim-ve-gia-re');

		$args2 = array(
	                    'post_title'  => 'Vé rẻ trong tháng',
	                    'post_status' => 'publish',
	                    'post_type'   => 'page',
	                    'post_name'   => 've-re-trong-thang'
	                );
		create_page($args2, 'pl_cfl_ticket_page_id', 'tpl-ve-re-trong-thang.php', 'cfl_ticket_page_template', 've-re-trong-thang');
	}

	public static function deactivate_plugin_cheapflight() {
		update_page_status('find_cfl_page_id');
		update_page_status('cfl_ticket_page_id');
	}

	// public static function unistall_plugin_cheapflight() {
	// 	delete_page('find_cfl_page_id', 'find_cfl_page_template');
	// 	delete_page('cfl_ticket_page_id', 'cfl_ticket_page_template');
	// 	// if ( __FILE__ != WP_UNINSTALL_PLUGIN )
 //        	// return;
	// }

	public function enqueue_scripts_and_styles() {
		if(!empty($_SESSION['fl_btn_search'])) {
			$script_var = array (
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'fl_token' => $_SESSION['fl_btn_search']
			);
		}
		

		$post_to_check = get_post(get_the_ID());

		if( !is_home() && !preg_match('/\/chon-hanh-trinh\//', $_SERVER['REQUEST_URI']) && !is_admin() || isset($post_to_check->post_content) && has_shortcode($post_to_check->post_content, 'VE_RE_THEO_THANG') || isset($post_to_check->post_content) && has_shortcode($post_to_check->post_content, 'FORM_TIMVERETHEOTHANG') && !is_admin()  || is_page('ve-re-trong-thang')) {

			wp_enqueue_style("style_cheapflight",plugins_url("/css/cheapflight.css",__FILE__),null,'2.0',false);
			$cus_css = get_custom_css();
			wp_add_inline_style("style_cheapflight",$cus_css);

			wp_enqueue_style("pl-popup.css",plugins_url("/css/popup.css",__FILE__),null,'2.0',false);
			wp_enqueue_style("go-font.css",plugins_url("/css/go-font.css",__FILE__),null,'2.0',false);

			wp_enqueue_script("pl-jquery-1.11.1.min",plugins_url("/js/jquery-1.11.1.min.js",__FILE__),null,'2.0',true);
			wp_enqueue_script("pl-jquery-ui.1.10.4.min",plugins_url("/js/jquery-ui.1.10.4.min.js",__FILE__),null,'2.0',true);
		 	wp_enqueue_script("pl-wow",plugins_url("/js/wow.min.js",__FILE__),null,'2.0',true);
		    wp_enqueue_script("pl-JQuery.home",plugins_url("/js/JQuery.home.js",__FILE__),null,'2.0',true);
		    wp_localize_script("pl-JQuery.home",'cheapflight',$script_var);
		}

		if( is_page('ve-re-trong-thang') && !is_admin() || isset($post_to_check->post_content) && has_shortcode($post_to_check->post_content, 'VE_RE_THEO_THANG') && !preg_match('/\/chon-hanh-trinh\//', $_SERVER['REQUEST_URI']) && !is_home() && !is_admin()) {
			wp_enqueue_script("san-ve-gia-re",plugins_url("/js/san-ve-gia-re.js",__FILE__),null,'2.0',true);
			wp_localize_script("san-ve-gia-re",'cheapflight',$script_var);
		} 

		if( is_admin() ) {
			wp_enqueue_media();
			wp_enqueue_script('wp-color-picker');
			wp_enqueue_style('wp-color-picker');
		}
	}

	public function page_cus_template( $template ) {
	   	if ( is_page_template('tpl-ve-re-trong-thang.php') ) {
			$template = plugin_dir_path( __FILE__ ) . 'templates/tpl-ve-re-trong-thang.php';
	    } elseif ( is_page_template('tpl-tim-ve-gia-re.php') ) {
	    	$template = plugin_dir_path( __FILE__ ) . 'templates/tpl-tim-ve-gia-re.php';
	    } 
	    return $template;
	}

	// create cheapflight admin page
	public function register_cheapflight_admin_page() {
		add_menu_page(
						'Cheap Flight', 
						'Cheap Flight',
						'manage_options',
						plugin_dir_path(__FILE__) . 'inc/cheapflight-admin-page.php'
					);
	}
}
add_action( 'plugins_loaded', array( 'Cheapflight_Setup_Class', 'get_instance' ) );
register_activation_hook( __FILE__ , array( 'Cheapflight_Setup_Class', 'activate_plugin_cheapflight' ) );
register_deactivation_hook( __FILE__, array( 'Cheapflight_Setup_Class', 'deactivate_plugin_cheapflight' ) );
// register_uninstall_hook( __FILE__, array( 'Cheapflight_Setup_Class', 'unistall_plugin_cheapflight' ) );




function create_page($args, $page_name_option, $page_template, $page_template_option, $post_name) {
	$page_id_option = get_option($page_name_option); 
	$template_id_option = get_option($template_name_option);

    if( !$page_id_option && !$template_id_option  ) {
	    $page_id = wp_insert_post($args); 
	    $template_id = add_post_meta($page_id, '_wp_page_template', $page_template);
	    
	    if( $page_id && !is_wp_error($page_id) ) {
		    if( isset($page_id) ) {
		    	update_option( $page_name_option, $page_id );
		    } else {
		    	add_option( $page_name_option, $page_id );
		    }
		}

		if( $template_id && !is_wp_error($template_id) ) {
		    if( isset($template_id) ) {
		    	update_option( $page_template_option, $template_id );
		    } else {
		    	add_option( $page_template_option, $template_id );
		    }
		}
	} else {
		wp_update_post( 
						array(
								'ID' => $page_id_option, 
								'post_status' => 'publish',
								'post_name' =>  $post_name
							) 
					);
		update_post_meta($page_id_option, '_wp_page_template',  $page_template);
	}
}

function update_page_status($name_option) {
	$page_id = get_option($name_option); 

	if( $page_id ) {
		wp_update_post( 
						array(
								'ID' => $page_id, 
								'post_status' => 'draft' 
							) 
					);
	}
}

function get_custom_css() {
	$css = ".pl-searchform .button{ border: 1px solid ". get_option('cheapfl-btn-search-color') ."; background-color:" . get_option('cheapfl-btn-search-color'). ";} .pl-searchform form { background-color:" . get_option('cheapfl-form-color') . ";} 
.calendar h4.cal-title { background-color:" . get_option('cheapfl-form-color-2'). "; } @media(min-width:1200px){ .pl-searchform form { width:" . get_option('cheapfl-form-w') . "px;} .ve-re-calendar{float:none; margin:0 auto; width:" . get_option('cheapfl-form-w-2') . "%;}}";
	return $css;
}

// function delete_page($name_option, $template_name_option) {
	// $page_id = get_option($name_option);

// 	// if( $page_id ) {
// 	// 	wp_update_post( 
// 	// 					array(
// 	// 							'ID' => $page_id_option, 
// 	// 							'post_status' => 'publish' 
// 	// 						) 
// 	// 				);
// 	// }

	// delete_option($name_option);
	// delete_option($template_name_option);
// }


add_action('wp_ajax_bestprice', 'bestprice_function');
add_action('wp_ajax_nopriv_bestprice', 'bestprice_function');
function bestprice_function() {
	if (isset($_POST[$_SESSION['fl_btn_search']])
	    || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')
	    || empty($_POST['dep_input'])
	    || empty($_POST['des_input'])
	    || empty($_POST['month_input'])
	    || empty($_POST['year_input'])
	   	|| ($_POST['fl_token'] != $_SESSION['fl_btn_search'])
	) {
	    header('HTTP/1.0 403 Forbidden');
	    exit;
	}

	$dep = isset($_POST['dep_input']) ? esc_attr($_POST['dep_input']) : false;
	$des = isset($_POST['des_input']) ? esc_attr($_POST['des_input']) : false;
	$month = isset($_POST['month_input']) ? esc_attr($_POST['month_input']) : false;
	$year = isset($_POST['year_input']) ? esc_attr($_POST['year_input']) : false;

	$dep = preg_replace("/[^A-Z]/", "", $_POST['dep_input']);
	if($dep == 'CXR') {
		$dep = 'NHA';
	}
	$des = preg_replace("/[^A-Z]/", "", $_POST['des_input']);
	if($des == 'CXR') {
		$des = 'NHA';
	}
	$month = preg_replace("/[^0-9]/", "", $_POST['month_input']);
	$year = preg_replace("/[^0-9]/", "", $_POST['year_input']);

	$result = get_best_price_in_month_ws($dep,$des,$month,$year,get_option('cheapfl-form-api-key'));  
	$data = $result['data'];
	$best_price = $result['best_price'];
	
	$timestamp = mktime(0, 0, 0, intval($month), 1, intval($year));
	$day_count = date('t', $timestamp);
	$cheap_price = array();
	$price_vna = array();
	$price_vj = array();
	$price_js = array();
	$price_bba = array();
	
	for($i = 1; $i <= $day_count; $i++) {
		if($i < 10) {
			$i = '0'.$i;
		}

		$date = $year.$month.$i;
		if(!empty($data) && !empty($data[$date])) {
			$cheap_price[] = $data[$date]['best_price'];
			$aircode[] = $data[$date]['best_aircode'];
			$price_vna[] = $data[$date]['price_vn'];
			$price_vj[] = $data[$date]['price_vj'];
			$price_js[] = $data[$date]['price_bl'];
			$price_bba[] = $data[$date]['price_qh'];
		} else {
			$cheap_price[] = -1;
			$aircode[] = -1;
			$price_vna[] = -1;
			$price_vj[] = -1;
			$price_js[] = -1;
			$price_bba[] = -1;
		}
	}

	$month = $month.'/'.$year;
	$weeks = create_calendar($month,$dep,$des,$cheap_price,$price_vna,$price_vj,$price_js,$price_bba,$best_price,$aircode);
	foreach ($weeks as $week) {
		echo $week;
	}

	die();
}

?>

