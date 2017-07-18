<?php

class AssetsLess extends AssetPlugins {
  public static $extension = 'css';
  public $code = '.less.';

  public function config(array $arrConfig) {

    $_settings = [];
    foreach($arrConfig as $key => $flag)
      if(is_int($key) && !is_array($flag))
        $_settings[$flag] = true;
      elseif(is_int($key) && is_array($flag)) $_settings = array_merge($_settings,$flag);
      else $_settings[$this->format($key)] = $flag;

    if(empty($this->config))
      $this->config = array_merge([
        'strictMath' => false,
        'sourceMap' => false,
        'sourceRoot' => '/',
        'compress' => false,
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
        'sourceMapWriteTo' => $fileData['dirname'].'/'.$fileData['filename'].'.less.css.map',
        'sourceMapURL' => $fileData['filename'].'.less.css.map'
      ));
      $_settings['sourceMap'] = true;
    }
    
    $FileCode = $this->code.($_settings['compress']?'min.':'');

    $minFile = $fileData['dirname'].'/'.$fileData['filename'].$FileCode.static::$extension;
    if(($FileContent = rex_file::get($file,'unreadable')) !== 'unreadable') {
      rex_file::put($minFile,'1');

      $parser = new Less_Parser($_settings);
      
      $FileContent = str_replace('@font-face','#FONT_FACE_PLACEHOLDER',$FileContent);
      $FileContent = str_replace('url(','_url_(',$FileContent);

      if(!empty($_settings['import'])) {
        $path = str_replace($_SERVER['DOCUMENT_ROOT'],'',$fileData['dirname']);
        $FileContent = preg_replace('|\@import([^\"\']+.)([^\"\']+)|is','@import (less)$1$2',$FileContent);
      }
      $parser->parse($FileContent,$file);

      try {
        $css = '';
        if(!empty($_settings['file_comments']))
          $css .= "\n\n/".str_pad("", strlen($file)+10, "*")."/\n".'/* File: '.$file." */\n/".str_pad("", strlen($file)+10, "*")."/\n\n";
        $css .= $parser->getCss();

        $minCss = preg_replace("|#FONT_FACE_PLACEHOLDER[^\{]*\{([^\}]+)\}|is",'@font-face{$1}',$css);
        $minCss = preg_replace("|#FONT_FACE_PLACEHOLDER[^\{]*\{([^\}]+)\}|is",'@font-face{$1}'."\n\n",$minCss);
        $minCss = str_replace('_url_(','url(',$minCss);

        if($_settings['sprog'] = 1)
          $minCss = parent::sprog($minCss);

        rex_file::put($minFile,$minCss);

        if($this->config['addon'] && empty($this->config['plugin']))
          rex_file::put(rex_addon::get($this->config['addon'])->getAssetsPath($fileData['filename'].$FileCode.static::$extension),$minCss);
        elseif($this->config['addon'] && $this->config['plugin'])
          rex_file::put(rex_plugin::get($this->config['addon'],$this->config['plugin'])->getAssetsPath($fileData['filename'].$FileCode.static::$extension),$minCss);
        


        return [
          'file'=>$fileData['filename'].$FileCode.static::$extension,
          'data'=>$minCss
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