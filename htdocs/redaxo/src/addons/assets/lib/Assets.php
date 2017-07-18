<?php

class Assets {

  private $addom = null;
  private $config = [];
  private $files = [];
  private $cachefile = '';
  private $precompilers = [];
  private $MediaQuery = false;

  public function __construct() {

    $this->addon = rex_addon::get('assets');

    $precompilers = rex_plugin::getRegisteredPlugins('assets');
    foreach($precompilers as $plugin => $compiler) {
      $preprozessor = $compiler->getProperty('preprozessor');
      $class = 'Assets'.ucfirst($preprozessor);
      if(class_exists($class) && !empty($preprozessor)) {
        $this->precompilers[$preprozessor] = new $class();
        $this->precompilers[$preprozessor]->config($compiler->getProperty('compiler'));
      }
    }
  }

  public static function loadMasterFile($file,$type = 'css') {

    $MasterFiles = new rex_var_assets();
    $MasterFiles->setMasterFile($file);
    $MasterFiles->setType($type);
    return $MasterFiles->loadMasterFiles();
  }

  public static function loadFiles(array $files,$arrOptions = array()) {

    if(empty($files))
      return;

    $file = md5(serialize($Assets->files));

    $options['na']['custom'] = array_merge([
      'list' => false,
      'debug' => false,
      'files' => $files,
      'file' => $file,
      'type' => false,
    ],$arrOptions);

    $Assets = new self;
    $Assets->add($options);
    $MasterFiles = [];
    if(!empty($Assets->files[$file]['files'][$options['na']['custom']['type']])) {
      switch($options['na']['custom']['type']) {
        case 'js':
          $MasterFiles[] = '<script scr="'.rex_url::addonAssets('assets','na/'.$file.'.min.js').'"></script>';
        break;
        case 'css':
          $MasterFiles[] = '<link rel="stylesheet" href="'.rex_url::addonAssets('assets','na/'.$file.'.min.css').'">';
        break;
      }
    } else {
      foreach($Assets->files[$file]['files'] as $type => $arrFiles) {
        switch($type) {
          case 'js':
            $MasterFiles[] = '<script scr="'.rex_url::addonAssets('assets','na/'.$file.'.min.js').'"></script>';
          break;
          case 'css':
            $MasterFiles[] = '<link rel="stylesheet" href="'.rex_url::addonAssets('assets','na/'.$file.'.min.css').'">';
          break;
        }
      }
    }
    return implode("\n",$MasterFiles);
  }

  public static function listAssets($type,$arrPath = [],$ignore = []) {
    $arrAssets = [];

    $ignore[] = 'lib';

    if(empty($arrPath)) $arrPath = [rex_path::media()];
    if(!is_array($arrPath)) $arrPath = [$arrPath];

    foreach($arrPath as $_path) { $path = rtrim(rex_path::frontend(),'/').$_path; if(!is_dir($path)) continue;
      foreach((array)scandir($path) as $key => $obj) { if(substr($obj,0,1) === '.' || (is_dir($path.$obj) && in_array($obj,$ignore)) || (!is_dir($path.$obj) && (end((explode('.',$obj))) !== $type || self::ignore($obj,$ignore) === false))) continue;
        if(is_dir($path.$obj))
          $arrAssets = array_merge($arrAssets,self::listAssets($type,$_path.$obj.'/',$ignore));
        else $arrAssets[] = $_path.$obj;
      }
    }
    return $arrAssets;
  }

  public static function ignore($file,$ignore) {
    foreach($ignore as $key => $value)
      if(strpos($file,$value))
        return false;

    return true;
  }

  public function add($Files) {
    foreach($Files as $area => $fileSets) {
      if(empty($fileSets)) continue;

      $this->files = [];
      $this->cachefile = '';
      foreach($fileSets as $addonId => $files) {

        $Config = $this->config;

        if(!empty($files['files'])) {
          $Config = array_merge($Config,array_diff_key($files,['files'=>'']));
          $files = $files['files'];
        }

        $MasterFile = !empty($Config['file'])?$Config['file']:'master';


        if(!empty($Config['media_query'])) { 
          $Config['media_query'] = explode(',',$Config['media_query']);
        }

        if(!empty($Config['addon'])) {
          $Config['addon'] = explode('/',$Config['addon']);
          if(!empty($Config['addon'][1]))
            $Config['plugin'] = $Config['addon'][1];
          else $Config['plugin'] = null;
          $Config['addon'] = $Config['addon'][0];
        }

        $this->files[$MasterFile]['config'] = $Config;
        if(!empty($fileSets[$MasterFile]))
          $this->files[$MasterFile]['settings'] = $fileSets[$MasterFile]['settings'];
        unset($Config['settings']);

        if(!empty($files)) {
          foreach($files as $path) {

            if(is_array($path)) continue;

            if(!empty($Config['root']))
              $path = rtrim(rex_path::frontend(),'/').$path;

            $flags = explode('|',$path);
            $path = pathinfo($flags[0]);
            $path['flags'] = !empty($flags[1])?$this->parseFlags(array_slice($flags,1)):[];
        
            if(!empty($path) && !empty(($Precompiler = $this->precompilers[$path['extension']]))) {
              $Precompiler->config($Config);
              $i_MFile = 0;
              $this->md5_of_dir(($this->files[$MasterFile]['files'][$Precompiler::$extension][] = $path));
            }
          }
        }
      }

      $this->compile($area);
    }
  }

  private function parseFlags($flags) {
    $arrFlags = [];
    foreach($flags as $flag) {
      $nested = explode(':',$flag);
      if(!empty($nested[1]))
        $arrFlags[$nested[0]] = $nested[1];
      else $arrFlags[] = $flag;
    }
    return $arrFlags;
  }

  private function findFile($fileData) {
    foreach(scandir($fileData['dirname']) as $key => $path) { if(in_array($path,['.','..','.git'])) continue;
      if(is_dir($fileData['dirname'].'/'.$path)) { 
        $file = $this->findFile(array_merge($fileData,['dirname'=>$fileData['dirname'].'/'.$path]));
        if($file !== null)
          return $file;
      }
      if($path == $fileData['basename'])
        return $fileData;
    }
    return $file;
  }

  private function compile($area) {
    $AtRules = [];

    foreach($this->files as $masterFile => $Files) {
      $sprog = false;
      $arrAsset = ['js'=>'','css'=>''];
      if(empty($Files['files'])) continue;
      foreach($Files['files'] as $ext => $files) {
        $file = ltrim($this->addon->getAssetsUrl('_cache/'.strtolower($area).'/'.$masterFile.'_'.md5($this->cachefile).'.'.$ext),'/');

        // if(1==1 || rex_file::get($file,'unreadable') === 'unreadable') {
        if(rex_file::get($file,'unreadable') === 'unreadable') {
          rex_dir::delete(rex_path::addonCache('templates'), false);
          $this->clearCache(strtolower($area),$ext);
          foreach($files as $key => $assets) {
            if(empty($this->precompilers[$assets['extension']])) continue;
            $fileConfig = null;
            if($area == 'FE')
              $fileConfig = $Files['settings'][$assets['basename']];
            $Parser = $this->precompilers[$assets['extension']]->compile($assets,$fileConfig);
            if(!empty($Parser['sprog']) && $Parser['sprog'] == true)
              $sprog = true;

            if($this->addon->hasProperty('debug') && $this->config['addon']) {
              $fileType = $this->precompilers[$assets['extension']];
              $fileType = 'add'.ucfirst($ext).'File';

              if($area === 'BE' && rex::isBackend())
                rex_view::$fileType(rex_addon::get($this->config['addon'])->getAssetsUrl($Parser['file']));
            }

            $path = str_replace($_SERVER['DOCUMENT_ROOT'],'',$assets['dirname']);
            if($ext === 'css') {
              // "|:([^url]*\s*)url[^(]*\([\"']*([^)\"\']*)[\"']*\)|is"
              // $Parser['data'] = preg_replace("|:([^url]*\s*)url[^(]*\([\"']*([^)\"\']*)[\"']*\)|is",':$1url("../../../../'.ltrim($path,'/').'/$2")',$Parser['data']);
              $Parser['data'] = preg_replace("|url[^(]*\([\"']*([^)\"\']*)[\"']*\)|is",'url("../../../../'.ltrim($path,'/').'/$1")',$Parser['data']);
              $Parser['data'] = preg_replace("|\@import(?!\s*url)[ \"\']*([^\n\"\']*)|is",'@import "../../../../'.ltrim($path,'/').'/$1',$Parser['data']);
              $Parser['data'] = preg_replace("|\@import(?=\s*url)[ \"\']+url\([\"\']*(?!http)([^ \n\"\']+)[\"\']*\)|is",'@import url("../../../../'.ltrim($path,'/').'/$1")',$Parser['data']);
              $Parser['data'] = preg_replace("#(\@import|\@charset)([^;]*;)#is","$1$2\n",$Parser['data']);
              $Parser['data'] = preg_replace("|(\@font-face{)([^}]*})|is","$1$2\n",$Parser['data']);
              $Parser['data'] = preg_replace("|(\/[^\/\.]+\/)..\/|is","/",$Parser['data']);

              if($area == 'FE') {
                $arrParserData = [];
                $Lines = explode("\n",$Parser['data']);
                foreach($Lines as $line)
                  if(substr($line,0,1) === '@')
                    $AtRules[] = $line;
                  else $arrParserData[] = $line;
                $Parser['data'] = implode("\n",$arrParserData);
              }
            }

            if(class_exists('CssMin') && $ext === 'css')
              $arrAsset[$ext] .= CssMin::minify($Parser['data']);
            else $arrAsset[$ext] .= $Parser['data'];
          }


          if(!empty($arrAsset[$ext])) {

            if($ext === 'css' && !empty($AtRules))
              $arrAsset[$ext] = implode("",$AtRules).$arrAsset[$ext];

            $MasterFile = ltrim($this->addon->getAssetsUrl(strtolower($area).'/'.rex_string::normalize($masterFile).'.min.'.$ext),'/');
            $MasterFilePath = $this->addon->getAssetsPath(strtolower($area).'/'.rex_string::normalize($masterFile).'.min.'.$ext);
            if(!empty($Files['config']['media_query']) && $ext === 'css') {
              $arrAsset[$ext] = '@media ('.implode(') and (',$Files['config']['media_query']).") {".$arrAsset[$ext]."}";
            }

            if($sprog) {
              foreach(rex_clang::getAll(true) as $lang)
                rex_file::put( str_replace('.min','.min.'.$lang->getCode(),$MasterFile) ,Wildcard::parse($arrAsset[$ext],$lang->getId()));
            } else rex_file::put($MasterFile,$arrAsset[$ext]);
            rex_file::put($file,date('Y-d-m_H-i-s'));
          }
        }
        if(!$this->addon->hasProperty('debug') && ($area === 'BE' && rex::isBackend()) || ($area === 'FE' && !rex::isBackend())) {
          $fileType = 'add'.ucfirst($ext).'File';
          rex_view::$fileType($this->addon->getAssetsUrl(strtolower($area).'/'.rex_string::normalize($masterFile).'.min.'.$ext));
        }
      }
    }
  }

  private function md5_of_dir($fileData) {
    $path = $fileData['dirname'];
    $this->cachefile .= date("YmdHis", filemtime($path.'/'.$fileData['basename'])).$fileData['basename'];
  }

  public function clearCache($area = '',$ext = false,$path = null) {
    if($path === null)
      $path = $this->addon->getAssetsPath('_cache'.($area?'/'.$area:''));

    // echo $path.'<br>';

    if(is_dir($path)) {
      foreach((array)scandir($path) as $file) {
        if(!in_array($file,['.','..'])) {
          if(is_dir($path.'/'.$file)) {
            $this->clearCache($area,$ext,$path.'/'.$file);
          } else {
            $pathInfo = pathinfo($path.'/'.$file);
            if(!$ext || $pathInfo['extension'] == $ext) {
              rex_file::delete($path.'/'.$file);
            }
          }
        }
      }
    }
  }

  public static function clearAll() {
    rex_dir::delete(rex_addon::get('assets')->getAssetsPath('_cache'), false);
    rex_dir::delete(rex_addon::get('assets')->getAssetsPath('fe'), false);
    rex_dir::delete(rex_addon::get('assets')->getAssetsPath('be'), false);
  }

  private function cacheCss($css) {
    $file = rex_plugin::get('be_style','lessphp')->getAssetsUrl($this->namespace.'.cache.css');
    $cache = rex_file::get($file);

    if($cache === '')
      rex_file::put($file,$css);
    elseif($cache != $css)
      rex_file::put($file,$css);
  }
}