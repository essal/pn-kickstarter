<?php

$fields = [
  'active',
  'online_from',
  'online_to',
];

$cols = rex_sql::showColumns(rex::getTablePrefix().'article_slice');
foreach($fields as $fieldname) {
  $found = false;
  foreach($cols as $field) {
    if($field['name'] === $fieldname) {
      $found = true;
      break;
    }
  }

  if($found) {
    $sql = rex_sql::factory();
    $sql->setQuery("ALTER TABLE `".rex::getTablePrefix().'article_slice'."` DROP ".$fieldname,array());
  }
}