<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Bootstrap\Nav\PageControl;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

/** @var WebEnvironment $env */
/** @var array<object> $mails List of form mails */
/** @var int $page */
/** @var int $pages */
/** @var array<string,array<string,string>> $words */

$w	= (object) $words['index.list'];

$iconAdd	= new Icon( 'plus' );
$iconView	= new Icon( 'eye' );
$iconEdit	= new Icon( 'pencil' );
$iconForm	= new Icon( 'th' );

$formats	= $words['formats'];

$roleTypeMap	= $words['roleTypes'];


$modelForm	= new Model_Form( $env );

$rows		= [];
foreach( $mails as $mail ){
	$linkView	= HtmlTag::create( 'a', $iconView.'&nbsp;'.$w->buttonView, [
		'href'	=> './manage/form/mail/view/'.$mail->mailId,
		'class'	=> 'btn btn-mini btn-info',
	] );
	$linkEdit	= HtmlTag::create( 'a', $mail->title, ['href' => './manage/form/mail/edit/'.$mail->mailId] );
	$nrForms	= $modelForm->countByIndex( 'customerMailId', $mail->mailId ) + $modelForm->countByIndex( 'managerMailId', $mail->mailId );
	$rows[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', HtmlTag::create( 'small', $mail->mailId ), ['style' => 'text-align: right'] ),
		HtmlTag::create( 'td', $linkEdit, ['class' => 'autocut'] ),
/*		HtmlTag::create( 'td', '<small><tt>'.$mail->identifier.'</tt></small>' ),*/
		HtmlTag::create( 'td', HtmlTag::create( 'small', $roleTypeMap[$mail->roleType] ) ),
		HtmlTag::create( 'td', $formats[$mail->format] ),
		HtmlTag::create( 'td', $nrForms, ['style' => 'text-align: right'] ),
		HtmlTag::create( 'td', $linkView ),
	] );
}
$colgroup	= HtmlElements::ColumnGroup( '40px', '', /*'30%',*/ '140px', '70px', '40px', '100px' );
$thead		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', [
	HtmlTag::create( 'th', $w->headId, ['style' => 'text-align: right'] ),
	HtmlTag::create( 'th', $w->headTitle ),
/*	HtmlTag::create( 'th', 'Shortcode' ),*/
	HtmlTag::create( 'th', $w->headRoleType ),
	HtmlTag::create( 'th', $w->headFormat ),
	HtmlTag::create( 'th', HtmlTag::create( 'abbr', $iconForm, ['title' => $w->headForms] ), ['style' => 'text-align: right'] ),
] ) );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', [$colgroup, $thead, $tbody], ['class' => 'table table-fixed table-striped table-condensed'] );

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.'&nbsp;'.$w->buttonAdd, [
	'href'	=> './manage/form/mail/add',
	'class'	=> 'btn btn-success'
] );

$pagination	= new PageControl( './manage/form/mail', $page, $pages );

return '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			'.$buttonAdd.'
			'.$pagination.'
		</div>
	</div>
</div>';
