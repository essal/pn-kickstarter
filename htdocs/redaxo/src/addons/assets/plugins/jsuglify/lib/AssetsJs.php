<?php

class AssetsJs extends AssetPlugins {
  public static $extension = 'js';
  public $code = '.jsmin.';

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
        'sourceMapWriteTo' => $fileData['dirname'].'/'.$fileData['filename'].'.less.css.map',
        'sourceMapURL' => $fileData['filename'].'.less.css.map'
      ));
      $_settings['sourceMap'] = true;
    }
    
    $FileCode = $this->code.($_settings['compress']?'min.':'');


    $minFile = $fileData['dirname'].'/'.$fileData['filename'].$FileCode.static::$extension;
    if(($FileContent = rex_file::get($file,'unreadable')) !== 'unreadable') {
      rex_file::put($minFile,'1');

      try {
        $js = '';
        if(!empty($_settings['file_comments']))
          $js .= "\n\n/".str_pad("", strlen($file)+10, "*")."/\n".'/* File: '.$file." */\n/".str_pad("", strlen($file)+10, "*")."/\n\n";
        if(!in_array('compressed',$fileData['flags']) && !empty($_settings['compress']))
          $js .= JSMin::minify($FileContent);
        else $js .= $FileContent;

        if($_settings['sprog'] == 1) {
          $langs = rex_clang::getAll(true);
          foreach($langs as $lang) {
            rex_file::put(str_replace($this->code,$this->code.$lang->getCode().'.',$minFile),parent::sprog($js,$lang->getId()));
          }
        } else rex_file::put($minFile,$js);

        if(!in_array('compressed',$fileData['flags']) && !empty($_settings['compress'])) {
          if($this->config['addon'] && empty($this->config['plugin']))
            rex_file::put(rex_addon::get($this->config['addon'])->getAssetsPath($fileData['filename'].$FileCode.static::$extension),$js);
          elseif($this->config['addon'] && $this->config['plugin'])
            rex_file::put(rex_plugin::get($this->config['addon'],$this->config['plugin'])->getAssetsPath($fileData['filename'].$FileCode.static::$extension),$js);
        }
        
        return [
          'file'=>$fileData['filename'].(!in_array('compressed',$fileData['flags'])?$FileCode:'.').static::$extension,
          'data'=>$js,
          'sprog'=>$_settings['sprog'],
        ];
      } catch (exception $e) {
        echo $e->getMessage();
      }
    }
  }

  protected function format($key) {
    $format = ['compres'=>'compress','compressed'=>'compress','min'=>'compress','minimized'=>'compress'];
    if(!empty($format[$key]))
      return $format[$key];
    return $key;
  }
  
}