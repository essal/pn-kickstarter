<?php
$value = $this->getVar('value');
$classes = $this->getVar('classes');
$label = $this->getVar('label');
$name = $this->getVar('name');
$id = $this->getVar('id');
$sortable = $this->getVar('sortable');
$asset = $this->getVar('asset');
$extension = $this->getVar('extension');
$settings = $this->getVar('settings');
?><dl class="rex-form-group form-group<?php echo ($sortable?' parent-sortable':'')?>">
  <dd>
    <div class="checkbox">
      <label class="control-label" for="<?php echo $id;?>">
        <?php if($sortable) {?><i class="rex-icon fa-sort"></i><?php }?>
        <input type="checkbox" name="<?php echo $this->getVar('name');?>" value="<?php echo ($value?$value:1);?>" id="<?php echo $id;?>"<?php echo ($this->getVar('checked')?' checked="checked"':'');?>>
        <?php echo $label;?>
      </label>
      <span class="rex-has-icon preferences" data-toggle="modal" data-target="#<?php echo $id;?>_modal"><i class="rex-icon rex-icon-system"></i></span>
    </div>
  </dd>
</dl>
<!-- Modal -->
<div class="fdl_modal modal fade" id="<?php echo $id;?>_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Parser konfigurieren</h4>
      </div>
      <div class="modal-body">
        <h4>Allgemeine Optionen</h4>
        <hr>
        <dl class="rex-form-group form-group controlls">
          <dt><label class="control-label" for="<?php echo $id;?>_modal_filename">Dateiname:</label></dt>
          <dd>
            <input class="form-control" id="<?php echo $id;?>_modal_filename" type="text" name="settings[<?php echo $asset['basename'];?>][filename]" value="<?php if(empty($settings['filename'])) {echo $asset['filename'].'.'.$asset['extension'].'.min.'.$extension;} else {echo $settings['filename'];}?>">
            <p class="help-block">Dieser Dateiname wird verwendet für die geparste Version.</p>
          </dd>
        </dl>
        <dl class="rex-form-group form-group">
          <dd>
            <div class="checkbox">
              <label class="control-label" for="<?php echo $id;?>_modal_compress">
                <input type="hidden" value="0" name="settings[<?php echo $asset['basename'];?>][compress]">
                <input type="checkbox" name="settings[<?php echo $asset['basename'];?>][compress]"<?php echo (!isset($settings['compress']) || $settings['compress'] == 1?' checked="checked"':'');?> value="1" id="<?php echo $id;?>_modal_compress">
                Code Komprimieren?
              </label>
            </div>
          </dd>
        </dl>
        <dl class="rex-form-group form-group">
          <dd>
            <div class="checkbox">
              <label class="control-label" for="<?php echo $id;?>_modal_file_comments">
                <input type="hidden" value="0" name="settings[<?php echo $asset['basename'];?>][file_comments]">
                <input type="checkbox" name="settings[<?php echo $asset['basename'];?>][file_comments]"<?php echo ($settings['file_comments'] == 1?' checked="checked"':'');?> value="1" id="<?php echo $id;?>_modal_file_comments">
                Informationen zur Originaldatei im Quelltext behalten?
                <p class="help-block">Das ist nur notwendig, damit in der fertigen Master-Datei zu sehen ist, welche Dateien eingebunden wurden.</p>
              </label>
            </div>
          </dd>
        </dl>
        <dl class="rex-form-group form-group">
          <dd>
            <div class="checkbox">
              <label class="control-label" for="<?php echo $id;?>_modal_sprog">
                <input type="hidden" value="0" name="settings[<?php echo $asset['basename'];?>][sprog]">
                <input type="checkbox" name="settings[<?php echo $asset['basename'];?>][sprog]"<?php echo ($settings['sprog'] == 1?' checked="checked"':'');?> value="1" id="<?php echo $id;?>_modal_sprog">
                Platzhalter von Sprog ersetzen?
                <p class="help-block">Dazu muss das Addon Sprog installiert und aktiviert sein!</p>
              </label>
            </div>
          </dd>
        </dl>
        <?php 
        switch($asset['extension']) {
          case 'less':?>
          <hr>
          <h4>Less Optionen</h4>
          <hr>
          <dl class="rex-form-group form-group">
            <dd>
              <div class="checkbox">
                <label class="control-label" for="<?php echo $id;?>_modal_import">
                  <input type="hidden" value="0" name="settings[<?php echo $asset['basename'];?>][import]">
                  <input type="checkbox" name="settings[<?php echo $asset['basename'];?>][import]"<?php echo (!isset($settings['import']) || $settings['import'] == 1?' checked="checked"':'');?> value="1" id="<?php echo $id;?>_modal_import">
                  @Import: Dateiinhalt in den Quelltext einbinden
                  <p class="help-block">Imports werden direkt in die Master-Datei eingebunden und nicht mit @import geladen.</p>
                </label>
              </div>
            </dd>
          </dl>
          <dl class="rex-form-group form-group">
            <dd>
              <div class="checkbox">
                <label class="control-label" for="<?php echo $id;?>_less_source_map">
                  <input type="hidden" value="0" name="settings[<?php echo $asset['basename'];?>][source_map]">
                  <input type="checkbox" name="settings[<?php echo $asset['basename'];?>][source_map]"<?php echo ($settings['source_map'] == 1?' checked="checked"':'');?> value="1" id="<?php echo $id;?>_less_source_map">
                  SourceMap generieren
                  <p class="help-block">Zeigt die korrekte Zeilennummer des Less-Codes in den Entwicklertools und Firebug an.</p>
                </label>
              </div>
            </dd>
          </dl>
          <dl class="rex-form-group form-group">
            <dd>
              <div class="checkbox">
                <label class="control-label" for="<?php echo $id;?>_less_strict_math">
                  <input type="hidden" value="0" name="settings[<?php echo $asset['basename'];?>][strict_math]">
                  <input type="checkbox" name="settings[<?php echo $asset['basename'];?>][strict_math]"<?php echo (!isset($settings['strict_math']) || $settings['strict_math'] == 1?' checked="checked"':'');?> value="1" id="<?php echo $id;?>_less_strict_math">
                  Strict Math einschalten
                  <p class="help-block">Verhindert dass aus calc(100% - 40px) = calc(60%) wird.</p>
                </label>
              </div>
            </dd>
          </dl>
          <dl class="rex-form-group form-group">
            <dd>
              <div class="checkbox">
                <label class="control-label" for="<?php echo $id;?>_less_strict_units">
                  <input type="hidden" value="0" name="settings[<?php echo $asset['basename'];?>][strict_units]">
                  <input type="checkbox" name="settings[<?php echo $asset['basename'];?>][strict_units]"<?php echo ($settings['strict_units'] == 1?' checked="checked"':'');?> value="1" id="<?php echo $id;?>_less_strict_units">
                  Strict Units einschalten
                </label>
              </div>
            </dd>
          </dl>
          <?php break;
          case 'scss':?>
          <hr>
          <h4>Sass Optionen</h4>
          <hr>
          <dl class="rex-form-group form-group">
            <dd>
              <div class="checkbox">
                <label class="control-label" for="<?php echo $id;?>_modal_import">
                  <input type="hidden" value="0" name="settings[<?php echo $asset['basename'];?>][import]">
                  <input type="checkbox" name="settings[<?php echo $asset['basename'];?>][import]"<?php echo (!isset($settings['import']) || $settings['import'] == 1?' checked="checked"':'');?> value="1" id="<?php echo $id;?>_modal_import">
                  @Import: Dateiinhalt in den Quelltext einbinden
                  <p class="help-block">Imports werden direkt in die Master-Datei eingebunden und nicht mit @import geladen.</p>
                </label>
              </div>
            </dd>
          </dl>
          <?php break;
          case 'css':?>          
            <?php if(class_exists('Less_Parser')) {?>
            <hr>
            <h4>Less Optionen</h4>
            <hr>
            <?php
              $fragment = new rex_fragment();
              $fragment->setVar('value', '1');
              $fragment->setVar('label', 'Mit Less parsen?');
              $fragment->setVar('name', 'settings['.$asset['basename'].'][less_parser]');
              $fragment->setVar('checked', $settings['less_parser'] == 1);
              $fragment->setVar('toggleFields','.toggle_'.$id.'_css_less_parser');
              $fragment->setVar('info', 'Mit dem Less-Parser können Features wie SourceMaps generiert werden. Dazu muss das Plugin lessphp installiert sein.');
              echo $fragment->parse('form/assets_checkbox.php');
            ?>
            <?php
              $fragment = new rex_fragment();
              $fragment->setVar('value', '1');
              $fragment->setVar('group', 'toggle_'.$id.'_css_less_parser');
              $fragment->setVar('label', 'SourceMap generieren?');
              $fragment->setVar('name', 'settings['.$asset['basename'].'][source_map]');
              $fragment->setVar('checked', $settings['source_map'] == 1);
              $fragment->setVar('info', 'Zeigt die korrekte Zeilennummer des Less-Codes in den Entwicklertools und Firebug an.');
              echo $fragment->parse('form/assets_checkbox.php');
            ?>
            <?php
              $fragment = new rex_fragment();
              $fragment->setVar('value', '1');
              $fragment->setVar('group', 'toggle_'.$id.'_css_less_parser');
              $fragment->setVar('label', 'Strict Math einschalten?');
              $fragment->setVar('name', 'settings['.$asset['basename'].'][strict_math]');
              $fragment->setVar('checked', (!isset($settings['strict_math']) || $settings['strict_math'] == 1));
              $fragment->setVar('info', 'Verhindert dass aus calc(100% - 40px) = calc(60%) wird.');
              echo $fragment->parse('form/assets_checkbox.php');
            ?>
            <?php
              $fragment = new rex_fragment();
              $fragment->setVar('value', '1');
              $fragment->setVar('group', 'toggle_'.$id.'_css_less_parser');
              $fragment->setVar('label', 'Strict Units einschalten?');
              $fragment->setVar('name', 'settings['.$asset['basename'].'][strict_units]');
              $fragment->setVar('checked', ($settings['strict_units'] == 1));
              echo $fragment->parse('form/assets_checkbox.php');
            ?>
            <?php }?>

          <?php break;
          case 'js':?>

          <?php break;
        }?>
        <hr>
        <h4>Datei-Info</h4>
        <hr>
        <pre><?php print_r($asset);?></pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>