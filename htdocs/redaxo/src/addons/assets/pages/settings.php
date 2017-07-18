<?php

if(rex_post('btn_save', 'string') != '') {

  $pValues = rex_post('assets', [
    ['paths', 'array'],
  ]);

  $pValues['paths'] = array_filter($pValues['paths']);
  $pValues['paths'] = array_unique($pValues['paths']);
  $this->setConfig(['paths'=>$pValues['paths']]);
  $message = $this->i18n('config_saved_successfull');
}

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

$Values = $this->getConfig('paths');
if(empty($Values))
  $Values = [''];
else $Values[] = '';

foreach($Values as $key => $value) {
  $fragment = new rex_fragment();
  $fragment->setVar('name', 'assets[paths][]', false);
  $fragment->setVar('value', $value, false);
  $fragment->setVar('label', rex_i18n::msg('assets_paths').' '.($key+1).'.)', false);
  $fragment->addDirectory($this->getAddon()->getPath());
  $content .= $fragment->parse('form/input.php');
}

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('assets_general'));
$fragment->setVar('body', $content, false);
$sections .= $fragment->parse('core/page/section.php');
$content = '';


$arrContent = rex_extension::registerPoint(new rex_extension_point('EXTEND_ASSETS_SETTINGS_FORM', $sections, [
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
<form action="<?php echo rex_url::currentBackendPage();?>" method="post">
  <fieldset>
    <?php if(!empty($message)) echo rex_view::success($message);?>
    <?php echo $sections;?>
  </fieldset>
</form>

