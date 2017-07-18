<?php

rex_sql_table::get(rex::getTablePrefix().'assets_sets')
  ->ensureColumn(new rex_sql_column('media_query', 'varchar(255)'))
  ->ensureColumn(new rex_sql_column('settings', 'text'))
  ->alter();