<?php

$fragment = new rex_fragment();
$content = $fragment->parse('assets_help.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'info', false);
$fragment->setVar('title', $this->i18n('assets_help'), false);
$fragment->setVar('body', $content.'<code>'.highlight_file('codes/rex_assets.php',1).'</code>', false);
echo $fragment->parse('core/page/section.php');
