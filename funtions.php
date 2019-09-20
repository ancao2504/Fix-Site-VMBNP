<?php
	// FIX AUTOPTIMIZE CACHE BIG SIZE 
	if (class_exists('autoptimizeCache')) {
	  $siteMaxSize = 2000000;
	  $statArr = autoptimizeCache::stats();
	  $cacheSize = round($statArr[1]/1024);

	  if ($cacheSize > $siteMaxSize){
	     autoptimizeCache::clearall();
	     header("Refresh:0");
	  }
	}

	// TÙY CHỈNH NGÔN NGỮ WEBSITE
	add_filter('language_attributes', 'custom_lang_attr');
	function custom_lang_attr() {
	    return 'lang="vi-VN"';
	}

	add_filter('wpseo_locale', 'override_og_locale');
	function override_og_locale($locale) {
	    return "vi_VN";
	}

?>
