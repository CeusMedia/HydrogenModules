<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var array<object> $groups */
/** @var ?string $filterStatus */
/** @var ?string $filterGroupId */
/** @var ?string $filterEmail */
/** @var ?string $filterFirstname */
/** @var ?string $filterSurname */
/** @var ?string $filterLimit */

if( isset( $totalReaders ) && is_array( $totalReaders ) && count( $totalReaders ) < 3 )
	return "";

$optStatus		= ['' => '- alle -'];
foreach( $words->states as $key => $value )
	$optStatus[$key]	= $value;
$optStatus		= HtmlElements::Options( $optStatus, $filterStatus );

$optGroup		= ['' => '- alle -'];
foreach( $groups as $group )
	$optGroup[$group->newsletterGroupId]	= $group->title;
$optGroup		= HtmlElements::Options( $optGroup, $filterGroupId );


$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] ).'&nbsp;';
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] ).'&nbsp;';

return '
<form action="./work/newsletter/reader/filter" method="post">
	<div class="row-fluid">
		<div class="span2">
			<label for="filter_status">'.$words->index_filter['labelStatus'].'</label>
			<select name="status" id="filter_status" class="span12">'.$optStatus.'</select>
		</div>
		<div class="span2">
			<label for="filter_email">'.$words->index_filter['labelEmail'].'</label>
			<input type="text" name="email" id="filter_email" class="span12" value="'.htmlentities( $filterEmail ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="filter_firstname">'.$words->index_filter['labelFirstname'].'</label>
			<input type="text" name="firstname" id="filter_firstname" class="span12" value="'.htmlentities( $filterFirstname ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="filter_surname">'.$words->index_filter['labelSurname'].'</label>
			<input type="text" name="surname" id="filter_surname" class="span12" value="'.htmlentities( $filterSurname ?? '', ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="filter_groupId">'.$words->index_filter['labelGroup'].'</label>
			<select name="groupId" id="filter_groupId" class="span12">'.$optGroup.'</select>
		</div>
		<div class="span2">
			<label for="filter_limit">'.$words->index_filter['labelLimit'].'</label>
			<input type="text" name="limit" id="filter_limit" class="span8" value="'.(int) $filterLimit.'"/>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<label>&nbsp;</label>
			<button class="btn btn-small btn-info" type="submit" name="filter">'.$iconFilter.$words->index_filter['buttonFilter'].'</button>
			<a class="btn btn-small btn-inverse" href="./work/newsletter/reader/filter/reset">'.$iconReset.$words->index_filter['buttonReset'].'</a>
		</div>
	</div>
</form>
<hr/>';
