<?php

class AssetsCss extends AssetPlugins {
  public static $extension = 'css';
  public $code = '.cssmin.';

  public function config(array $arrConfig) {

    $_settings = [];
    foreach($arrConfig as $key => $flag)
      if(is_int($key) && !is_array($flag))
        $_settings[$flag] = true;
      elseif(is_int($key) && is_array($flag)) $_settings = array_merge($_settings,$flag);
      else $_settings[$this->format($key)] = $flag;

    if(empty($this->config))
      $this->config = array_merge([
        'sourceMap' => false,
        'compress' => false
      ],$_settings);
    else $this->config = array_merge($this->config,$_settings);
  }

  public function compile(array $fileData,$fileConfig = []) {
    $file = $fileData['dirname'].'/'.$fileData['basename'];

    if(!empty($fileConfig)) {
      $_fileConfig = [];
      foreach($fileConfig as $cKey => $config)
        $_fileConfig[$this->format($cKey)] = $config;
      $fileConfig = $_fileConfig;
    }

    $_settings = array_merge($this->config,(array)$fileConfig);
    
    if(in_array('sourceMap',$fileData['flags']) || !empty($_settings['sourceMap'])) {
      $_settings = array_merge($_settings,array(
        'sourceMapWriteTo' => $fileData['dirname'].'/'.$fileData['filename'].$this->code.'css.map',
        'sourceMapURL' => $fileData['filename'].$this->code.'css.map'
      ));
      $_settings['sourceMap'] = true;
    }
    
    $FileCode = $this->code.($_settings['compress']?'min.':'');
    
    $minFile = $fileData['dirname'].'/'.$fileData['filename'].$FileCode.static::$extension;

    if(($FileContent = rex_file::get($file,'unreadable')) !== 'unreadable') {
      rex_file::put($minFile,'1');

      try {
        $css = '';
        if(!empty($_settings['file_comments']))
          $css .= "\n\n/".str_pad("", strlen($file)+10, "*")."/\n".'/* File: '.$file." */\n/".str_pad("", strlen($file)+10, "*")."/\n\n";

        if(!empty($_settings['less_parser']) && class_exists('Less_Parser')) {
          $parser = new Less_Parser($_settings);
          // echo '<pre>'.print_r($_settings,1).'</pre>';
          $parser->parse($FileContent,$file);
          $css .= $parser->getCss();
        } else {
          if(!in_array('compressed',$fileData['flags']))
            $css .= CssMin::minify($FileContent);
          else $css .= $FileContent;
        }

        if($_settings['sprog'] = 1)
          $css = parent::sprog($css);

        rex_file::put($minFile,$css);

        if(!in_array('compressed',$fileData['flags'])) {
          if(!empty($this->config['addon']) && empty($this->config['plugin']))
            rex_file::put(rex_addon::get($this->config['addon'])->getAssetsPath($fileData['filename'].$FileCode.static::$extension),$css);
          elseif(!empty($this->config['addon']) && $this->config['plugin'])
            rex_file::put(rex_plugin::get($this->config['addon'],$this->config['plugin'])->getAssetsPath($fileData['filename'].$FileCode.static::$extension),$css);
        }
        
        return [
          'file'=>$fileData['filename'].(!in_array('compressed',$fileData['flags'])?$FileCode:'.').static::$extension,
          'data'=>$css
        ];
      } catch (exception $e) {
        echo $e->getMessage();
      }
    }
  }

  protected function format($key) {
    $format = [
      'compres'=>'compress',
      'compressed'=>'compress',
      'min'=>'compress',
      'minimized'=>'compress',
      'strict_math'=>'strictMath',
      'strict_units'=>'strictUnits',
      'source_map'=>'sourceMap',
      'source_root'=>'sourceRoot',
    ];
    if(!empty($format[$key]))
      return $format[$key];
    return $key;
  }
  
}