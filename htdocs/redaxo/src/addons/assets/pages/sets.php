<?php
  $func = rex_request('func', 'string');

  if(rex_post('btn_save', 'string') != '') {
    $CAssets = new Assets();
    $CAssets->clearCache();

    $sql = rex_sql::factory();
    $sql->setTable(rex::getTablePrefix().'assets_sets');

    $sql->setValue('title',rex_post('title'));
    $sql->setValue('description',rex_post('description'));
    $sql->setValue('media_query',rex_post('media_query','string'));
    $sql->setValue('files',json_encode(rex_post('files','array')));
    $sql->setValue('settings',json_encode(rex_post('settings','array')));

    rex_dir::delete(rex_path::addonCache('templates'), false);
    Assets::clearAll();

    rex_extension::registerPoint(new rex_extension_point('SAVE_ASSETS_SET_FORM', $sql, [
      'addon'=>$this->getAddon()
    ]));

    if(rex_post('id','string') == '') {
      try {
        $sql->insert();
        echo rex_view::success(rex_i18n::msg('assets_sets_added'));
      } catch(rex_sql_exception $e) {
        echo rex_view::warning(rex_i18n::msg('assets_sets_add_error'));
        echo rex_view::warning($e->getMessage());
      }
    } else {
      try {
        $sql->setWhere(['id'=>rex_post('id')]);
        $sql->update();
        echo rex_view::success(rex_i18n::msg('assets_sets_updated'));
      } catch(rex_sql_exception $e) {
        echo rex_view::warning(rex_i18n::msg('assets_sets_update_error'));
        echo rex_view::warning($e->getMessage());
      }
    }
  }
  

  if ($func == 'delete') {
    $id = rex_request('id', 'int');
    
    $del = rex_sql::factory();
    $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'assets_sets WHERE `id` = "'.$id.'"');

    Assets::clearAll();
    rex_dir::delete(rex_path::addonCache('templates'), false);
    echo rex_view::success(rex_i18n::msg('assets_sets_deleted'));
  }

  
  if ($func == '' || $func == 'delete' || rex_post('btn_save', 'string') != '') {
    $list = rex_list::factory("SELECT `id`, `title`, `description`, `media_query`, `files` FROM `".rex::getTablePrefix()."assets_sets` ORDER BY `title` ASC");
    $list->addTableAttribute('class', 'table-striped');
    $list->setNoRowsMessage($this->i18n('assets_sets_norowsmessage'));
    
    $thIcon = '<a href="'.$list->getUrl(['func' => 'add']).'" title="'.$this->i18n('column_hashtag').' '.rex_i18n::msg('add').'"><i class="rex-icon rex-icon-add-action"></i></a>';
    $tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);
    
    $list->setColumnLabel('title', $this->i18n('assets_sets_column_title'));
    $list->setColumnLabel('description', $this->i18n('assets_sets_column_description'));
    $list->setColumnLabel('media_query', $this->i18n('assets_sets_column_media_query'));
    $list->setColumnLabel('files', $this->i18n('assets_sets_column_files'));
    
    $list->setColumnFormat('media_query',
      'custom',
      function($params) use ($list) {
        return (empty($params['subject'])?'all':str_replace(',','<br>',$params['subject']));
      }
    );

    $list->addColumn('REX_VAR', 'REX_VAR', -2, ['<th class="rex-table-action">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnFormat('REX_VAR',
      'custom',
      function($params) use ($list) {
        $Title = $params['list']->getValue('title');
        return 'REX_ASSETS[type=css&nbsp;file='.rex_string::normalize($Title).']&nbsp;&nbsp;&nbsp;<br>REX_ASSETS[type=js&nbsp;file='.rex_string::normalize($Title).']&nbsp;&nbsp;&nbsp;';
      }
    );

    rex_extension::registerPoint(new rex_extension_point('EXTEND_ASSETS_SET_LIST', $list, [
      'addon'=>$this->getAddon(),
    ]));

    $funcs = $this->i18n('assets_sets_column_functions');
    
    $list->addColumn($funcs, '<i class="rex-icon rex-icon-edit"></i> '.rex_i18n::msg('edit'), -1, ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams($funcs, ['id' => '###id###', 'func' => 'edit']);
    
    $delete = 'deleteCol';
    $list->addColumn($delete, '<i class="rex-icon rex-icon-delete"></i> '.rex_i18n::msg('delete'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams($delete, ['id' => '###id###', 'func' => 'delete']);
    $list->addLinkAttribute($delete, 'data-confirm', rex_i18n::msg('delete').' ?');
    
    $list->removeColumn('id');
    $list->removeColumn('files');

    rex_extension::registerPoint(new rex_extension_point('EXTEND_ASSETS_SET_LIST_END', $list, [
      'addon'=>$this->getAddon(),
    ]));
    
    $content = $list->get();
    
    $fragment = new rex_fragment();
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');
    
    echo $content;
  } else if ($func == 'add' || $func == 'edit') {

    $Values = [];

    if(($id = rex_get('id'))) {
      $Values = rex_sql::factory()
        ->setTable(rex::getTablePrefix().'assets_sets')
        ->setWhere(['id'=>$id])
        ->select()
        ->getArray()[0];

      $Values['files'] = json_decode($Values['files'],1);
    }

    if(!empty($Values['settings']))
      $Values['settings'] = json_decode($Values['settings'],1);

    $content = $sections = '';

    $Addon = rex_addon::get('assets');
    $Plugins = $Addon->getAvailablePlugins();

    $fragment = new rex_fragment();
    $content .= $fragment->parse('settings.php');

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'info', false);
    $fragment->setVar('title', rex_i18n::msg('assets_info'));
    $fragment->setVar('body', $content, false);
    $sections .= $fragment->parse('core/page/section.php');
    $content = '';

    $fragment = new rex_fragment();
    $fragment->setVar('name', 'title', false);
    $fragment->setVar('value', $Values['title'], false);
    $fragment->setVar('label', rex_i18n::msg('assets_sets_label_title'), false);
    $fragment->addDirectory($this->getAddon()->getPath());
    $content .= $fragment->parse('form/input.php');

    $fragment = new rex_fragment();
    $fragment->setVar('name', 'description', false);
    $fragment->setVar('value', $Values['description'], false);
    $fragment->setVar('label', rex_i18n::msg('assets_sets_label_description'), false);
    $fragment->addDirectory($this->getAddon()->getPath());
    $content .= $fragment->parse('form/input.php');

    $fragment = new rex_fragment();
    $fragment->setVar('name', 'media_query', false);
    $fragment->setVar('value', $Values['media_query'], false);
    $fragment->setVar('label', rex_i18n::msg('assets_sets_label_media_query'), false);
    $fragment->addDirectory($this->getAddon()->getPath());
    $content .= $fragment->parse('form/input.php');

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', rex_i18n::msg('assets_general'));
    $fragment->setVar('body', $content, false);
    $sections .= $fragment->parse('core/page/section.php');
    $content = '';

    $arrContent = rex_extension::registerPoint(new rex_extension_point('EXTEND_ASSETS_SET_FORM', $sections, [
      'config'=>$Values,
      'addon'=>$this->getAddon(),
    ]));

    if(!empty($arrContent) && is_array($arrContent)) {
      foreach($arrContent as $key => $fieldset) { if(empty($fieldset['body'])) continue;
        $fragment = new rex_fragment();
        $fragment->setVar('class', !empty($fieldset['class'])?$fieldset['class']:'edit', false);
        $fragment->setVar('title', $fieldset['title']);
        $fragment->setVar('body', $fieldset['body'], false);
        $sections .= $fragment->parse('core/page/section.php');
        $content = '';
      }
    } 

    $Ignore = ['addons'];
    foreach($Plugins as $pluginName => $plugin) {
      $class = 'Assets'.ucfirst($plugin->getProperty('preprozessor'));
      if(class_exists($class)) {
        $classObj = new $class();
        $Ignore[] = $classObj->code;
      }
    }

    $Paths = rex_addon::get('assets')->getConfig('paths');
    $eAssets = [];
    foreach($Plugins as $pluginName => $plugin) {
      $Prozessor = $plugin->getProperty('preprozessor');
      $Extension = $plugin->getProperty('extension');
    
      if(empty($eAssets[$Extension])) $eAssets[$Extension] = [];
      $eAssets[$Extension] = array_merge($eAssets[$Extension],Assets::listAssets($plugin->getProperty('preprozessor'),$Paths,$Ignore));
    }


    foreach($eAssets as $Extension => $Assets) {

      if(!empty($Values['files'][$Extension]))
        $Assets = array_merge(
          $Values['files'][$Extension],
          array_diff($Assets,$Values['files'][$Extension])
        );

      foreach($Assets as $key => $asset) {
        $FileInfo = pathinfo($asset);
        $fragment = new rex_fragment();
        $fragment->setVar('name', 'files['.$Extension.'][]', false);
        $fragment->setVar('id', 'files_'.$Extension.'_'.$key, false);
        $fragment->setVar('value', $asset, false);
        $fragment->setVar('checked', in_array($asset,(array)$Values['files'][$Extension]), false);
        $fragment->setVar('sortable', 1, false);
        $fragment->setVar('asset', $FileInfo, false);
        if(!empty($Values['settings'][$FileInfo['basename']]))
          $fragment->setVar('settings', $Values['settings'][$FileInfo['basename']], false);
        $fragment->setVar('extension', $Extension, false);

        $fragment->setVar('label', rex_i18n::msg('assets_file').': '.$asset, false);
        $content .= $fragment->parse('form/add_asset.php');
      }

      if(!empty($content)) {
        $fragment = new rex_fragment();
        $fragment->setVar('class', 'edit input-sortable', false);
        $fragment->setVar('title', rex_i18n::msg('assets_include_files').' ['.$Extension.']');
        $fragment->setVar('body', $content, false);
        $sections .= $fragment->parse('core/page/section.php');
        $content = '';
      }
    }

    $formElements = [];
    $n = [];
    $n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="' . $this->i18n('save') . '">' . $this->i18n('assets_save') . '</button>';
    $formElements[] = $n;
    $n = [];
    $n['field'] = '<button class="btn btn-reset" type="reset" name="btn_reset" value="' . $this->i18n('reset') . '" data-confirm="' . $this->i18n('assets_reset_info') . '">' . $this->i18n('assets_reset') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('flush', true);
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('body', $content, false);
    $fragment->setVar('buttons', $buttons, false);
    $sections .= $fragment->parse('core/page/section.php');

    ?>
    <form action="<?php echo rex_url::currentBackendPage(['funk'=>'']);?>" method="post">
      <?php if(($id = rex_get('id'))) {?><input type="hidden" name="id" value="<?php echo $id;?>"><?php }?>
      <fieldset>
        <?php echo $sections;?>
      </fieldset>
    </form>
    <?php }?>