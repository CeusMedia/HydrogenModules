<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $template */
/** @var string $appPath */
/** @var string $appUrl */

$iconOpen		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-folder-open"] );
$iconView		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-eye"] );
$iconExists		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-2x fa-check"] );
$iconMissing	= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-2x fa-warning"] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => "fa fa-fw fa-remove"] );

$modalStyle		= new View_Helper_Input_Resource( $env );
$modalStyle->setModalId( 'modal-admin-mail-template-select-style' );
//$modalStyle->setInputId( 'input_template_css' );
$trigger	= new View_Helper_Input_ResourceTrigger( $env );
$trigger->setLabel( $iconOpen );
$trigger->setModalId( 'modal-admin-mail-template-select-style' );
$trigger->setInputId( 'input_template_style' );
$trigger->setMode( View_Helper_Input_ResourceTrigger::MODE_STYLE );

$listStyles	= '<em class="muted">Keine.</em>';
if( $template->styles ){
	$list	= [];
	foreach( json_decode( $template->styles, TRUE ) as $item ){
		$rowClass	= 'error';
		$buttonOpen	= HtmlTag::create( 'button', $iconView, [
			'type'		=> 'button',
			'class'		=> 'btn btn-info disabled',
			'title'		=> 'Style-Datei existiert nicht im angegebenen Pfad (in Frontend-Applikation).',
			'disabled'	=> 'disabled',
		] );
		if( file_exists( $appPath.$item ) ){
			$rowClass	= 'not-success';
			$buttonOpen	= HtmlTag::create( 'a', $iconView, [
				'href'		=> $appUrl.$item,
				'class'		=> 'btn btn-info',
				'target'	=> '_blank',
			] );
		}
		$itemFile		= HtmlTag::create( 'big', pathinfo( $item, PATHINFO_BASENAME ) );
		$itemPath		= HtmlTag::create( 'small', pathinfo( $item, PATHINFO_DIRNAME ), ['class' => 'muted'] );
		$buttonRemove	= HtmlTag::create( 'a', $iconRemove, [
			'class'	=> 'btn btn-inverse pull-right',
			'href'	=> './admin/mail/template/removeStyle/'.$template->mailTemplateId.'/'.base64_encode( $item ),
			'title'	=> 'Style-Verweis entfernen',
		] );
		$buttons	= HtmlTag::create( 'div', [
			$buttonOpen,
			$buttonRemove,
		], ['class' => 'btn-group'] );
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $itemFile.'<br/>'.$itemPath ),
			HtmlTag::create( 'td', $buttons, ['style' => 'text-align: right'] ),
		], ['class' => $rowClass] );
	}
	$listStyles	= HtmlTag::create( 'table', [
		HtmlElements::ColumnGroup( [
			'',
			'120px'
		] ),
		HtmlTag::create( 'thead', HtmlElements::TableHeads( [
			$words['edit-image-list']['headPath'],
			$words['edit-image-list']['headActions'],
		] ) ),
		HtmlTag::create( 'tbody', $list ),
	], [
		'class'	=> 'table table-fixed table-striped',
	] );
}


return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h4>'.$words['edit-style-list']['heading'].'</h4>
			<div class="content-panel-inner">
				'.$listStyles.'
				'.HtmlTag::create( 'div', $buttonList, ['class' => 'buttonbar'] ).'
			</div>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<form action="./admin/mail/template/edit/'.$template->mailTemplateId.'" method="post">
			<div class="content-panel">
				<h4>'.$words['edit-style-add']['heading'].'</h4>
				<div class="content-panel-inner">
					<div class="row-fluid">
						<div class="span8">
							<label for="input_template_style">'.$words['edit-style-add']['labelPath'].'</label>
							<input type="text" name="template_style" id="input_template_style" class="span12"/>
						</div>
						<div class="span4">
							<label>&nbsp;</label>
							<div class="btn-group">
								'.$trigger.'
								<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i> '.$words['edit-style-add']['buttonSave'].'</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
'.$modalStyle;
