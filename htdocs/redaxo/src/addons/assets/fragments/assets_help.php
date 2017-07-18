<p>Mit <strong>REX_ASSETS[type=css file=default]</strong> und <strong>REX_ASSETS[type=js file=default]</strong> können Style Sheets und Javascripte geladen werden.</p>
<h3>Ressourcen</h3>
<p>Resourcen wie Hintergrundbilder und Fonts die mit <mark>url('...');</mark> definiert werden, muss dass Addon umschreiben für die Master-Datei, da diese sonst nicht mehr gefunden werden können. Dabei ist darauf zu achten dass die Resourcen <mark>nicht</mark> im selben Verzeichniss liegen.</p>
<h4>Empfohlene Struktur</h4>
<p>ROOT/assets/default/</p>
<ul>
  <li>/css/</li>
  <li>/js/</li>
  <li>/fonts/</li>
  <li>/img/</li>
</ul>
<hr>
<h3>Media Queries</h3>
<p>Media Queries können direkt im Code einer CSS-Datei definiert werden, oder hier im Addon. Dabei sollte das Set gleich heißen wie das Set ohne Media Query, Das Addon erstellt dann aus dem Set <b>Default</b> die Dateien default.min.css und default_1.min.css.</p>
<p>Media Queries können vollständig eingetragen werden, oder kommagetrennt und ohne Klammern. Aus <mark>min-width:0px, max-width:767px</mark> wird schlussendlich <mark>(min-width:0px) AND (max-width:767px)</mark>
<hr>
<h3>Code-Beispiel für Templates</h3>