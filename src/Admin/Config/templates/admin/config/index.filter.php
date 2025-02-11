<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $categories */
/** @var string|NULL $filterCategory */

$w	= (object) $words['index-filter'];

$iconSearch		= HtmlTag::create( 'i', '', ['class' => 'icon-zoom-in'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'icon-zoom-out'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconSearch		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-search'] );
	$iconReset		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-search-minus'] );
}

$optCategory	= ['' => '- alle -'];
foreach( $categories as $category => $nrModules )
	$optCategory[$category]	= $category.' ('.$nrModules.')';
$optCategory	= HtmlElements::Options( $optCategory, $filterCategory );

return '
<div class="content-panel content-panel-form content-panel-filter">
	<h3>'.$w->heading. '</h3>
	<div class="content-panel-inner">
		<form action="./admin/config/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_category">'.$w->labelCategory.'</label>
					<select name="category" id="input_category" class="span12 has-optionals" onchange="this.form.submit();">'.$optCategory.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_query">'.$w->labelQuery.'</label>
					<input type="text" name="query" id="input_query" class="span12" onchange="this.form.submit();" value="'.htmlentities( $query ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="buttonbar">
				<div class="btn-group">
					<button type="submit" name="search" class="btn btn-small btn-info">'.$iconSearch.'&nbsp;'.$w->buttonSearch.'</button>
					<a href="./admin/config/filter/reset" class="btn btn-small btn-inverse">'.$iconReset.'&nbsp;'.$w->buttonReset.'</a>
				</div>
			</div>
		</form>
	</div>
</div>';
