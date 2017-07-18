# Redaxo Assets

Dieses Addon bietet die Möglichkeit, alle CSS-, Sass-, Less- und JS-Dateien zu sammeln, parsen und auszugeben. Dazu verwendet das Addon fertige Tools wie [LessPHP](https://github.com/oyejorge/less.php), [Sassphp](https://github.com/leafo/scssphp), [JS-Min](https://github.com/rgrove/jsmin-php/) und [CSS-Min](https://github.com/natxet/CssMin).

Alle Dateien können mit den extension points `BE_ASSETS` und `FE_ASSETS` hinzugefügt werden. Die Frontend-Dateien können auch von Backend-Benutzern eingefügt werden, in dem diese ihre Dateien in den Assest-Einstellungen als Set zusammen klicken. Assets lädt alle Dateien und minimiert sie, alle Datei-Endungen für Less, Sass, CSS und JS werden automatisch gefunden und mit dem entsprechenden Tool geparst.

##Features

- Beschränkung einzelner Dateien auf einzelne Seiten
- Automatische Erkennung von Less/Sass/WhatEver 
- Frontend/Backend files

## Anwendung

Sobald im Backend Sets angelegt wurden, können diese Im Fronend mit REX_ASSETS ausgegeben werden.

```
REX_ASSETS[type=css file=standard]
REX_ASSETS[type=js file=standard]
```

###REX_ASSETS

<table width="100%">
	<tr>
		<th>Option</th>
		<th>Werte</th>
		<th>Beschreibung</th>
	</tr>
	<tr>
		<td>type</td>
		<td>css | js</td>
		<td>Lädt die Javascript oder CSS Dateien als HTML-Tag</td>
	</tr>
	<tr>
		<td>file</td>
		<td>Set-Name</td>
		<td>Lädt das Set. Ein Set ist die komprimierte Datei aus allen angegebenen Assets.</td>
	</tr>
	<tr>
		<td>list</td>
		<td>1</td>
		<td>Wird list angegeben, so liefert das Addon alle Dateien statt nur der Set-Datei.</td>
	</tr>
	<tr>
		<td>nocache</td>
		<td>1</td>
		<td>Wird nocache angegeben, so hängt das Addon den aktuellen Timestamp an das Ende des Dateipfades. Diese Option ist nur zum Debuggen geeignet und muss im Live-Betrieb deaktiviert werden!</td>
	</tr>
</table>

### Media Queries

Ein Set kann eine Media-Query-Definition besitzen, damit können Sets für bestimmte Viewports generiert werden. Die Sets können dabei gleich heißen, wie die Basis-Dateien, sie werden dann automatisch mit REX_ASSETS geladen.

### Beschränkung

In manchen Fällen kann es durchaus logisch erscheinen, dass auf einigen Unterseiten andere CSS/JS-Codes ausgegeben werden, zum Beispiel wenn diese einen  komplett anderen Aufbau oder eine eigene Struktur besitzen. Durch dass Anlegen mehrerer Sets, können in unterschiedlichen Templates unterschiedliche Sets geladen werden.

### Eine/Mehrere Datei(en) hinzufügen (Addon-Entwickler)

Entwickelt eure Addons auf einer eurer Installationen und stellt sicher, dass `assets` installiert ist. Schreibt in die Boot-Datei folgende Zeilen:

```
<?php

rex_extension::register('BE_ASSETS',function($ep) {
  $Subject = $ep->getSubject()?$ep->getSubject():[];
  $Subject[$this->getPackageId()] = [
    'files' => [
      $this->getPath('assets/style.less'),
      $this->getPath('assets/script.js'),
    ],
    'addon' => $this->getPackageId(),
  ];
  return $Subject;
});
```

### Weitere Dateien für das Addon hinzufügen?

Erweitert dazu das Subject:

```
rex_extension::register('BE_ASSETS',function($ep) {
  $Subject = $ep->getSubject()?$ep->getSubject():[];
  $Subject[$this->getPackageId()]['files'][] = $this->getPath('assets/another_file.js');
  return $Subject;
});
```

### Was wenn der Kunde das Addon nicht hat?

Kein Problem. Das Addon legt die geparsten Dateien in eurem Addon-Verzeichnis ab, genauer da, wo die Less/Sass/Js-Datei liegt. Ihr könnt also in eurer `boot.php` abfragen, ob `assets` installiert ist und als alternative die geparsten Dateien einbinden.

```
<?php

// Ist installiert?
if(rex_addon::get('assets')->isInstalled()) {

  rex_extension::register('BE_ASSETS',function($ep) {
    $Subject = $ep->getSubject()?$ep->getSubject():[];
    $Subject[$this->getPackageId()] = [
      'files' => [
        $this->getPath('assets/style.less'),
        $this->getPath('assets/script.js'),
      ],
      'addon' => $this->getPackageId(),
    ];
    return $Subject;
  });

} elseif(rex::isBackend()) {

  rex_view::addCssFile($this->getAssetsUrl('styles.less.min.css'));
  rex_view::addJsFile($this->getAssetsUrl('script.jsmin.min.js'));
  
}
```
