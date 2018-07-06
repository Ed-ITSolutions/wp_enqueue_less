<?php
function wp_enqueue_less($key, $file, $variables){
  $details = get_option('wp_enqueue_less_' . $key, new StdClass);

  if(!$details->key){
    $details->key = $key;
    $details->file = $file;
    $details->setting = 'wp_enqueue_less_' . $key;

    do_action('wp_enqueue_less_compile', $details, $variables);
  }else{
    $details->file = $file;
    
    if(md5(json_encode($variables)) != $details->variablesHash){
      do_action('wp_enqueue_less_compile', $details, $variables);
    }else{
      $filesChanged = false;

      foreach(array_keys($details->fileHashes) as $fileName){
        if(!$filesChanged && md5_file($fileName) != $details->fileHashes[$fileName]){
          $filesChanged = true;
        }
      }

      if($filesChanged){
        do_action('wp_enqueue_less_compile', $details, $variables);
      }
    }
  }

  $baseUri = apply_filters('wp_enqueue_less_css_url', wp_upload_dir()['baseurl'] . '/less');
  wp_enqueue_style($key, $baseUri . '/' . $details->key . '-' . $details->hash . '.css');

  if(!wp_next_scheduled('wp_enqueue_less_clean')){
    wp_schedule_event(time(), 'daily', 'wp_enqueue_less_clean');
  }
}

if(function_exists('add_action')){
  add_action('wp_enqueue_less_compile', function($details, $variables){
    $parser = new Less_Parser();
    $parser->parseFile($details->file, get_template_directory_uri());
    $parser->ModifyVars($variables);
    $css = $parser->getCss();
    
    $hash = md5($css);

    $dir = apply_filters('wp_enqueue_less_css_dir', wp_upload_dir()['basedir'] . '/less');
    if(!file_exists($dir)){
      mkdir($dir);
    }

    $file = $dir . '/' . $details->key . '-' . $hash . '.css';

    file_put_contents($file, $css);

    $details->hash = $hash;
    
    $details->fileHashes = array();
    foreach($parser->allParsedFiles() as $fileName){
      $details->fileHashes[$fileName] = md5_file($fileName);
    }

    $details->variablesHash = md5(json_encode($variables));

    update_option($details->setting, $details);
  }, 10, 2);

  add_action('wp_enqueue_less_clean', function(){
    $dir = apply_filters('wp_enqueue_less_css_dir', wp_upload_dir()['basedir'] . '/less');

    $files = scandir($dir);

    $keyDetails = array();

    foreach($files as $file){
      if($file != '.' && $file != '..'){
        $key = substr($file, 0, -37);
        $hash = substr($file, -36, -4);
        
        if(!isset($keyDetails[$key])){
          $keyDetails[$key] = get_option('wp_enqueue_less_' . $key, new StdClass);
        }

        if($hash != $keyDetails[$key]->hash){
          unlink($dir . '/' . $file);
        }
      }
    }
  });
}