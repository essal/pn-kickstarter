<?php

$media = $this->getPath('media');
if (is_dir($media) && !is_dir(rex_path::media('assets/'))) {
  if (!rex_dir::copy($media, rex_path::media())) {
    throw new rex_functional_exception($this->i18n('install_cant_copy_files'));
  }
}
