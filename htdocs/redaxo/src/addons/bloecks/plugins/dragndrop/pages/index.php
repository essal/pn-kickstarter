<?php
    $plugin = BloecksBackend::getPlugin('dragndrop');
    $addon_dir = rex_addon::get('bloecks')->getPath('pages');

    if(file_exists($addon_dir . '/_index.php'))
    {
        include($addon_dir . '/_index.php');
    }
