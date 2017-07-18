<?php

class AssetsScss extends AssetPlugins {
  public static $extension = 'css';
  public $code = '.scss.';

  public function config(array $arrConfig) {

    $_settings = [];
    foreach($arrConfig as $key => $flag)
      if(is_int($key) && !is_array($flag))
        $_settings[$flag] = true;
      elseif(is_int($key) && is_array($flag)) $_settings = array_merge($_settings,$flag);
      else $_settings[$this->format($key)] = $flag;

    if(empty($this->config))
      $this->config = array_merge([
        'compressed' => 'normal'
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
    
    $FileCode = $this->code.($_settings['compress']?'min.':'');

    $minFile = $fileData['dirname'].'/'.$fileData['filename'].$FileCode.static::$extension;
    if(($FileContent = rex_file::get($file,'unreadable')) !== 'unreadable') {
      rex_file::put($minFile,'1');

      ignore_user_abort(true);
      set_time_limit(0);

      // $FileContent = str_replace('@font-face','#FONT_FACE_PLACEHOLDER',$FileContent);

      $compiler = new scssc();

      if(!empty($_settings['import'])) {
        $compiler->addImportPath(function($path) use ($fileData) {
          if(strpos($path,'.') === false)
            $path .= '.scss';

          $_path = str_replace('.scss','.sass',$path);
          if(file_exists($fileData['dirname'].'/'.$_path))
            $path = $_path;

          if (!file_exists($fileData['dirname'].'/'.$path)) return null;

          return $fileData['dirname'].'/'.$path;
        });
      }

      $compiler->setNumberPrecision(10);
      if(!in_array('stripComments',$fileData['flags']))
        $compiler->stripComments = true;

      // set css formatting (normal, nested or minimized), @see http://leafo.net/scssphp/docs/#output_formatting
      $formatter = (!empty($fileData['flags']['formatter'])?$fileData['flags']['formatter']:'');
      $formatter = $this->format($formatter);

      $compiler->setFormatter('scss_formatter'.($formatter?'_'.$formatter:''));

      try {
        $css = '';
        if(!empty($_settings['file_comments']))
          $css .= "\n\n/".str_pad("", strlen($file)+10, "*")."/\n".'/* File: '.$file." */\n/".str_pad("", strlen($file)+10, "*")."/\n\n";
        $css .= $compiler->compile($FileContent);

        if($_settings['sprog'] = 1)
          $css = parent::sprog($css);

        rex_file::put($minFile,$css);

        if($this->config['addon'] && empty($this->config['plugin']))
          rex_file::put(rex_addon::get($this->config['addon'])->getAssetsPath($fileData['filename'].$FileCode.static::$extension),$css);
        elseif($this->config['addon'] && $this->config['plugin'])
          rex_file::put(rex_plugin::get($this->config['addon'],$this->config['plugin'])->getAssetsPath($fileData['filename'].$FileCode.static::$extension),$css);

        // $css = preg_replace("|#FONT_FACE_PLACEHOLDER[^\{]*\{([^\}]+)\}|is",'@font-face{$1}'."\n\n",$css);

        return [
          'file'=>$fileData['filename'].$FileCode.static::$extension,
          'data'=>$css
        ];
      } catch (Exception $e) {
        echo $e->getMessage();
        exit();
      }
    }
  }

  protected function format($key) {
    $format = ['compres'=>'compressed','compress'=>'compressed','min'=>'compressed','minimized'=>'compressed'];
    if(!empty($format[$key]))
      return $format[$key];
    return $key;
  }
}