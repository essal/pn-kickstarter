<?php
error_reporting(0);
error_reporting(E_ALL);
// Error report should only be active during development. Deavtivate (0) on a live website

// Not-logged-in Users must not see offline Pages
if (!rex_backend_login::hasSession()) {
	// Is current article offline?
	if ($this->getValue('status') == 0) {
		// redirect to 404 page
		header ('HTTP/1.1 301 Moved Permanently');
		header('Location: '.rex_getUrl(rex_article::getNotFoundArticleId(), rex_clang::getCurrentId()));
		die();
	}
}

// set charset to utf8
header('Content-Type: text/html; charset=utf-8');
//$h = $this->getArticle('1');
// setLocale is a language meta field, set your individual locale informations per language
setlocale (LC_ALL, rex_clang::getCurrent()->getValue('clang_setlocale'));

// ############## build $JS #############################################################
// rex::setProperty('INLINEJS', $value) adds inline-JS
$JS = '';
if(rex::getProperty('INCLUDEJS') != '') $JS .= rex::getProperty('INCLUDEJS');
$JS .= 'REX_ASSETS[type=js file=all]';
if(rex::getProperty('INLINEJS') != ''){
	$JS .= PHP_EOL.'<script>'.PHP_EOL.rex::getProperty('INLINEJS').'</script>'.PHP_EOL;
	$BARBAJS = rex::getProperty('INLINEJS');
}

// ############## build $CSS ############################################################
// rex::setProperty('INLINECSS', $value) adds inline-CSS
$CSS = '';
if(rex::getProperty('INCLUDECSS') != '') $CSS .= rex::getProperty('INCLUDECSS');
$CSS .= 'REX_ASSETS[type=css file=all]';
if(rex::getProperty('INLINECSS') != ''){
	$CSS .= '<style>'.PHP_EOL.rex::getProperty('INLINECSS').'</style>'.PHP_EOL;
	$BARBACSS = rex::getProperty('INLINECSS');
}

?><!DOCTYPE html>
<html lang="<?php echo rex_clang::getCurrent()->getCode(); ?>">
<head>
<!--
  ************************
****************************
**  *******   **      **  **       PURO NECTAR
**  ********  ***     **  **       Brands&Advertising       
**  **    **  ****    **  **
**  **    **  *****   **  **       puronectar.com
**  **    **  ** ***  **  **
**  ********  **  *** **  **       0451 76091
**  *******   **   *****  **
**  **        **    ****  **
**  **        **     ***  **
**  **        **      **  **
****************************
 **************************
 -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
if(rex_addon::get('yrewrite')->isAvailable()) {
	$seo = new rex_yrewrite_seo();
	echo $seo->getTitleTag().PHP_EOL;
	echo $seo->getDescriptionTag().PHP_EOL;
	echo $seo->getRobotsTag().PHP_EOL;
	echo $seo->getHreflangTags().PHP_EOL;
	echo $seo->getCanonicalUrlTag().PHP_EOL;
} else {
	// Use article title as title-Tag, unless a custom title-tag is set
	if ($this->getValue("art_title") != "") {
		$title = htmlspecialchars($this->getValue('art_title'));
	} else {
		$title = htmlspecialchars($this->getValue('name'));
	}

	// Keywords and description
	// If current article does not have keywords and description, take them from start article
	if ($this->getValue("art_keywords") != "") {
		$keywords = $this->getValue("art_keywords");
	} else {
		$home = new rex_article_content(rex_article::getSiteStartArticleId());
		$keywords = $home->getValue('art_keywords');
	}

	if ($this->getValue("art_description") != "") {
		$description = $this->getValue("art_description");
	} else {
		$home = new rex_article_content(rex_article::getSiteStartArticleId());
		$description = $home->getValue('art_description');
	}

	echo '<title>'.$title.'</title>'.PHP_EOL;
	echo '<meta name="keywords" content="'.htmlspecialchars($keywords).'">'.PHP_EOL;
	echo '<meta name="description" content="'.htmlspecialchars($description).'">'.PHP_EOL;

}
echo $CSS;
?>
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>