<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $instances */

$w				= (object) $words['index.filter'];

$iconFilter		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-search' ) );
$iconReset		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-trash' ) );

$from		= 'admin/log/exception'.($page ? '/'.$page : '' );

$selectInstance	= '';
if( count( $instances ) > 1 ){
	$optInstance	= [];
	foreach( $instances as $instanceKey => $instanceData )
		$optInstance[$instanceKey]	= $instanceData->title;
	$optInstance	= HtmlElements::Options( $optInstance, $currentInstance );
	$selectInstance	= HtmlTag::create( 'select', $optInstance, array(
		'oninput'	=> 'document.location.href = "./admin/log/exception/setInstance/" + jQuery(this).val();',
		'class'		=> '',
		'style'		=> 'width: 100%',
	) );

}


if( $exceptionTypes ){
	$optType	= ['' => '- alle-'];
	foreach( $exceptionTypes as $type )
		$optType[$type]	= $type;
	$optType	= HtmlElements::Options( $optType, $filterType );
}

$buttonFilter   = HtmlTag::create( 'button', $iconFilter.' '.$w->buttonFilter, ['type' => 'submit', 'class' => 'btn btn-primary'] );
$buttonReset    = HtmlTag::create( 'a', $iconReset.' '.$w->buttonReset, ['class' => 'btn btn-small', 'href' => './admin/log/exception/filter/true'] );

return '
<div class="content-panel">
	<h3>Filter</h3>
	<div class="content-panel-inner">
		<form action="./admin/log/exception/filter" method="post">
			<div class="row-fluid">
				<div class="span12">
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_message">'.$w->labelMessage.'</label>
					<input type="text" name="message" id="input_message" class="span12" value="'.htmlentities( $filterMessage, ENT_QUOTES, 'utf-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_type">'.$w->labelType.'</label>
					<select name="type" id="input_type" class="span12">'.$optType.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_dateStart">'.$w->labelDateStart.'</label>
					<input type="date" name="dateStart" id="input_dateStart" class="span12" value="'.htmlentities( $filterDateStart, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_dateEnd">'.$w->labelDateEnd.'</label>
					<input type="date" name="dateEnd" id="input_dateEnd" class="span12" value="'.htmlentities( $filterDateEnd, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>

<!--			<div class="row-fluid">
				<div class="span12">
					<label for="input_instance">Instanz</label>
					'.$selectInstance.'
				</div>
			</div>-->
			<div class="buttonbar">
				'.$buttonFilter.'
				'.$buttonReset.'
			</div>
		</form>
	</div>
</div>';
