<?php

abstract class AssetPlugins {

  protected $config = []; 

  abstract public function config(array $arrConfig);
  abstract public function compile(array $fileData,$fileConfig = []);

  protected function sprog($str,$clang_id = null) {
    if(rex_addon::get('sprog')->isAvailable()) {
      return Wildcard::parse($str,$clang_id);
    }
    return $str;
  }
}