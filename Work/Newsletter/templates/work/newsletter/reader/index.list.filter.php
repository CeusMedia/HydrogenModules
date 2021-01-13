<?php

if( isset( $totalReaders ) && is_array( $totalReaders ) && count( $totalReaders ) < 3 )
	return "";

$optStatus		= array( '' => '- alle -' );
foreach( $words->states as $key => $value )
	$optStatus[$key]	= $value;
$optStatus		= UI_HTML_Elements::Options( $optStatus, $filterStatus );

$optGroup		= array( '' => '- alle -' );
foreach( $groups as $group )
	$optGroup[$group->newsletterGroupId]	= $group->title;
$optGroup		= UI_HTML_Elements::Options( $optGroup, $filterGroupId );


$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) ).'&nbsp;';
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) ).'&nbsp;';

return '
<form action="./work/newsletter/reader/filter" method="post">
	<div class="row-fluid">
		<div class="span2">
			<label for="filter_status">'.$words->index_filter['labelStatus'].'</label>
			<select name="status" id="filter_status" class="span12">'.$optStatus.'</select>
		</div>
		<div class="span2">
			<label for="filter_email">'.$words->index_filter['labelEmail'].'</label>
			<input type="text" name="email" id="filter_email" class="span12" value="'.htmlentities( $filterEmail, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="filter_firstname">'.$words->index_filter['labelFirstname'].'</label>
			<input type="text" name="firstname" id="filter_firstname" class="span12" value="'.htmlentities( $filterFirstname, ENT_QUOTES, 'UTF-8' ).'"/>
		</div>
		<div class="span2">
			<label for="filter_surname">'.$words->index_filter['labelSurname'].'</label>
			<input type="text" name="surname" id="filter_surname" class="span12" value="'.htmlentities( $filterSurname, ENT_QUOTES, 'UTF-8' ).'"/>
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
?>