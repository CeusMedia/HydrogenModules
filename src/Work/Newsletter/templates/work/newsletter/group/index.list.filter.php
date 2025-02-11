<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var ?string $filterStatus */
/** @var ?string $filterQuery */

if( isset( $groups ) && is_array( $groups ) && count( $groups ) < 3 )
	return "";

$optStatus		= ['' => 'alle'];
foreach( $words['states'] as $key => $label )
	$optStatus[$key]	= $label;
$optStatus		= HtmlElements::Options( $optStatus, $filterStatus );

$iconFilter	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] ).'&nbsp;';
$iconReset	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] ).'&nbsp;';

return '
<form action="./work/newsletter/group/filter" method="post">
	<div class="row-fluid">
		<div class="span2">
			<label for="input_query">Suchwort</label>
			<input type="text" name="query" id="input_query" class="span12" value="'.htmlentities( $filterQuery ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="input_status">Zustand</label>
			<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
		</div>
		<div class="span4">
			<label>&nbsp;</label>
			<button type="submit" name="filter" class="btn btn-small btn-info">'.$iconFilter.'suchen</button>
			<a href="./work/newsletter/group/filter/reset" class="btn btn-small btn-inverse">'.$iconReset.'alle</a>
		</div>
	</div>
</form>
<hr/>';
