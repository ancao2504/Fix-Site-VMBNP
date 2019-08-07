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



?>
