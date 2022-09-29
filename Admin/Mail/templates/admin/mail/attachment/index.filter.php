<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var string[] $files */
/** @var string[] $classes */

$w	= (object) $words['filter'];

ksort( $files );
$optFile	= array( '' => $w->optAll );
foreach( $files as $file )
	$optFile[$file->filePath]	= $file->filePath;
$optFile	= HtmlElements::Options( $optFile, $filterFile );

$optClass	= array( '' => $w->optAll );
foreach( $classes as $class )
	$optClass[$class]	= $class;
$optClass	= HtmlElements::Options( $optClass, $filterClass );

$optLanguage	= array( '' => $w->optAll );
foreach( $languages as $language )
	$optLanguage[$language]	= $language;
$optLanguage	= HtmlElements::Options( $optLanguage, $filterLanguage );

$optStatus	= array( '' => $w->optAll ) + $words['states'];
$optStatus	= HtmlElements::Options( $optStatus, $filterStatus );

return '
<!-- templates/admin/mail/attachment/index.filter.php -->
<div class="content-panel content-panel-form">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./admin/mail/attachment/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_file">'.$w->labelFile.'</label>
					<select name="file" id="input_file" class="span12">'.$optFile.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_class">'.$w->labelClass.'</label>
					<select name="class" id="input_class" class="span12">'.$optClass.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_language">'.$w->labelLanguage.'</label>
					<select name="language" id="input_language" class="span12">'.$optLanguage.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="filter" class="btn btn-primary"><i class="icon-zoom-in icon-white"></i>&nbsp;'.$w->buttonFilter.'</button>
				<a href="./admin/mail/attachment/filter/reset" class="btn btn-inverse btn-small"><i class="icon-zoom-out icon-white"></i>&nbsp;'.$w->buttonReset.'</a>
			</div>
		</form>
	</div>
</div>
';
