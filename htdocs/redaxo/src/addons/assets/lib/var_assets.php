<?php

class rex_var_assets extends rex_var {
  private $nocache = false;
  private $Subdir = '';
  private $noQuote = false;
  private $MasterFile = '';

  public function setMasterFile($MasterFile) {
    $this->MasterFile = $MasterFile;
  } 

  public function setType($Type) {
    $this->Type = $Type;
  }

  public function loadMasterFiles() {
    $this->noQuote = true;
    $output = $this->getOutput();
    $this->noQuote = false;
    return $output;
  }
  
  protected function getOutput() {
    $clang = rex_clang::getCurrent()->getCode();
    // echo dirname(__FILE__).' | '.rtrim(realpath($_SERVER['DOCUMENT_ROOT']),'/').' - '.rtrim(rex_path::base(),'/').' = '.str_ireplace($_SERVER['DOCUMENT_ROOT'],'',rex_path::base());

    // print_r($_SERVER);
    $this->Subdir = ltrim(rtrim(str_replace(rtrim(realpath($_SERVER['DOCUMENT_ROOT']),'/'),'',rtrim(rex_path::base(),'/')),'/'),'/');
    if(!empty($this->Subdir))
      $this->Subdir = '/'.$this->Subdir;

    $Addon = rex_addon::get('assets');
    $File = $this->getArg('file');
    if(!empty($this->MasterFile))
      $File = $this->MasterFile;
    $Type = $this->getArg('type',null,false);
    if(!empty($this->Type))
      $Type = $this->Type;

    $AsList = $this->hasArg('list');
    $this->nocache = $this->hasArg('nocache') && $this->getArg('nocache') != '0';
    if(empty($File))
      $File = 'master';

    if(!empty($Type)) {
      $Files = '';
      $Debug = ($this->nocache && rex_backend_login::hasSession())?'?v='.time():'';
      switch($Type) {
        case 'css':
          if($AsList) {
            $Files .= $this->loadAsList($File,'css');
          } else {
            $i_MFile = 0;
            $_MasterFile = $MasterFile = rex_string::normalize($File);
            while(rex_file::get(rtrim(rex_path::frontend(),'/').$Addon->getAssetsUrl('fe/'.rex_string::normalize($_MasterFile).'.min.css')) != '') {
              $Files .= '<link rel="stylesheet" href="'.$this->Subdir.$Addon->getAssetsUrl('fe/'.strtolower($_MasterFile).'.min.css'.$Debug).'">'."\n";
              $_MasterFile = $MasterFile.'_'.($i_MFile++);
            }
            while(rex_file::get(rtrim(rex_path::frontend(),'/').$Addon->getAssetsUrl('fe/'.rex_string::normalize($_MasterFile).'.min.'.$clang.'.css')) != '') {
              $Files .= '<link rel="stylesheet" href="'.$this->Subdir.$Addon->getAssetsUrl('fe/'.strtolower($_MasterFile).'.min.'.$clang.'.css'.$Debug).'">'."\n";
              $_MasterFile = $MasterFile.'_'.($i_MFile++);
            }
          }
        break;
        case 'js':
          if($AsList) {
            $Files .= $this->loadAsList($File,'js');
          } else {
            $i_MFile = 0;
            $_MasterFile = $MasterFile = rex_string::normalize($File);
            while(rex_file::get(rtrim(rex_path::frontend(),'/').$Addon->getAssetsUrl('fe/'.rex_string::normalize($_MasterFile).'.min.js')) != '') {
              $Files .= '<script src="'.$this->Subdir.$Addon->getAssetsUrl('fe/'.strtolower($_MasterFile).'.min.js'.$Debug).'"></script>'."\n";
              $_MasterFile = $MasterFile.'_'.($i_MFile++);
            }
            while(rex_file::get(rtrim(rex_path::frontend(),'/').$Addon->getAssetsUrl('fe/'.rex_string::normalize($_MasterFile).'.min.'.$clang.'.js')) != '') {
              $Files .= '<script src="'.$this->Subdir.$Addon->getAssetsUrl('fe/'.strtolower($_MasterFile).'.min.'.$clang.'.js'.$Debug).'"></script>'."\n";
              $_MasterFile = $MasterFile.'_'.($i_MFile++);
            }
          }
        break;
      }
      $this->MasterFile = '';
      return $this->noQuote?$Files:self::quote($Files);
    }

      
    $this->MasterFile = '';
    return self::quote('');
  }

  private function loadAsList($File,$type) {
    $clang = rex_clang::getCurrent()->getCode();
    $arrFiles = [];
    $Debug = ($this->nocache && rex_backend_login::hasSession())?'?v='.time():'';
    $Data = rex_sql::factory()->setTable(rex::getTablePrefix().'assets_sets')->setWhere('lower(title) = :title',['title'=>$File])->select();
    if(!empty($Data)) {
      $Settings = $Data->getArrayValue('settings');
      $Files = $Data->getArrayValue('files');
      if(!empty($Files[$type])) {
        foreach($Files[$type] as $file) {
          $FileData = pathinfo($file);
          $FileSettings = $Settings[$FileData['basename']];
          $Class = 'Assets'.ucfirst($FileData['extension']);
          if(class_exists($Class)) {
            $Class = new $Class();
            $file = $this->Subdir.$FileData['dirname'].'/'.$FileData['filename'].$Class->code.($FileSettings['compress'] == 1?'min.':'').($FileSettings['sprog'] == 1?$clang.'.':'').$Class::$extension;
          }

          switch($type) {
            case 'css':
              $arrFiles[] = '<link rel="stylesheet" href="'.$file.$Debug.'">';
            break;
            case 'js':
              $arrFiles[] = '<script src="'.$file.$Debug.'"></script>';
            break;
          }
        }
      }
    }
    return implode("\n",$arrFiles);
  }
}