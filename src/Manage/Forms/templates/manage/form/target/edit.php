<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
declare(strict_types=1);

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var Entity_Form_Transfer_Target $target */
/** @var array<object> $fails List of failed fills */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['edit'];


$iconCancel		= new Icon( 'arrow-left' );
$iconSave		= new Icon( 'check' );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, ['class' => 'btn btn-small', 'href' => './manage/form/target'] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, ['type' => 'submit', 'class' => 'btn btn-primary'] );

$optStatus		= $words['statuses'];
$optStatus		= HtmlElements::Options( $optStatus, $target->status );

$table		= '';
if( count( $fails ) ){
	$rows	= [];
	/**
	 * @var int $nr
	 * @var Entity_Form_Fill $fail
	 */
	foreach( $fails as $nr => $fail )
	{
		$link	= HtmlTag::create( 'a', $fail->fillId, ['href' => './manage/form/fill/view/'.$fail->fillId] );
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $nr + 1 ),
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', '<tt>'.$fail->fillTransferMessage.'</tt>' ),
			HtmlTag::create( 'td', date( 'd.m.Y H:i:s', $fail->createdAt ) ),
			HtmlTag::create( 'td', date( 'd.m.Y H:i:s', $fail->failedAt ) ),
		] );
	}
	$thead	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'th', '#' ),
		HtmlTag::create( 'th', $w->headFill ),
		HtmlTag::create( 'th', $w->headMessage ),
		HtmlTag::create( 'th', $w->headCreatedAt ),
		HtmlTag::create( 'th', $w->headFailedAt ),
	] );
	$colgroup	= HtmlElements::ColumnGroup( '40px', '60px', '*', '120px', '120px' );
	$tbody	= HtmlTag::create( 'tbody', $rows );
	$table	= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-fixed table-striped'] );

	$table	= HtmlTag::create( 'div', [
		HtmlTag::create( 'h3', $w->headingFails.' <small class="muted">'.$w->headingFails_suffix.'</small>' ),
		HtmlTag::create( 'div', $table, ['class' => 'content-panel-inner'] ),
	], ['class' => 'content-panel'] );

}


return '<div class="content-panel">
	<h3><span class="muted">'.$w->headingPrefix.'</span>'.$target->title.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/form/target/edit/'.$target->formTransferTargetId.'" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $target->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span6">
					<label for="input_className" class="mandatory">'.$w->labelClassName.' <small class="muted">'.$w->labelClassName_suffix.'</small></label>
					<input type="text" name="className" id="input_className" class="span12" required="required" value="'.htmlentities( $target->className, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span2">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span9">
					<label for="input_baseUrl" class="mandatory">'.$w->labelBaseUrl.'</label>
					<input type="text" name="baseUrl" id="input_baseUrl" class="span12" required="required" value="'.htmlentities( $target->baseUrl, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_apiKey">'.$w->labelApiKey.'</label>
					<input type="text" name="apiKey" id="input_apiKey" class="span12"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>'.$table;
