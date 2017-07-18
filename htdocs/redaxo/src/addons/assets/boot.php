<?php

rex_extension::register('PACKAGES_INCLUDED',function() {
  $Assets = new Assets();

  $Sets = rex_sql::factory()
    ->setTable(rex::getTablePrefix().'assets_sets')
    ->select()
    ->getArray();

  $arrFiles = [];
  foreach($Sets as $key => $set) {
    $set['files'] = json_decode($set['files'],1);
    $aF = [];
    foreach($set['files'] as $ext => $files)
      $aF = array_merge($aF,$files);
    $set['files'] = $aF;

    $_SetTitle = $SetTitle = rex_string::normalize($set['title']);
    $i_Set = 0;
    while(!empty($arrFiles[$_SetTitle])) $_SetTitle = $SetTitle.'_'.($i_Set++);

    $arrFiles[$_SetTitle] = ['files'=>$set['files'],'root'=>true,'file'=>$_SetTitle,'settings'=>json_decode($set['settings'],1)];
    if(!empty($set['media_query']))
      $arrFiles[$_SetTitle]['media_query'] = $set['media_query'];
  }

  $BackendFiles = array_merge([
    $this->getPackageId() => [
      'files' => [
        $this->getPath('assets/be_assets.less'),
        $this->getPath('assets/be_assets.js'),
      ],
      'addon' => $this->getPackageId(),
    ]
  ],(array)rex_extension::registerPoint(new rex_extension_point('BE_ASSETS')));

  $Assets->add([
    'BE' => $BackendFiles,
    'FE' => array_merge((array)rex_extension::registerPoint(new rex_extension_point('FE_ASSETS')),$arrFiles),
  ]);
});