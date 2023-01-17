<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View\Helper\Timestamp;

/** @var object[] $tests */
/** @var Dictionary $config */
/** @var array<array<string,string>> $words */

$list		= [];
foreach( $tests as $entry )
{
	$format	= $config->get( 'layout.format.timestamp' );
	$time	= date( $format, (float) $entry['timestamp'] );
	$time	= HtmlTag::create( 'em', HtmlTag::create( 'small', '['.$time.']' ) );
	$url	= './test/table/edit/'.$entry['testId'];
	$link	= HtmlElements::Link( $url, $entry['title'] );
	$label	= $time.'&nbsp;&nbsp;'.$link;
	$list[]	= HtmlElements::ListItem( $label );	
}
$list		= $list ? HtmlElements::unorderedList( $list, 0, ['' => ''] ) : '';
$heading	= HtmlTag::create( 'h2', $words['index']['heading'] );
$add 		= HtmlElements::LinkButton( './test/table/add', 'add entry', 'button add' );

$rows		= [];
$number		= 0;
foreach( $tests as $entry )
{
	$class		= ( $number % 2 ) ? 'even' : 'odd';
	$format		= $config->get( 'layout.format.timestamp' );
	$timeHelper	= new Timestamp( $entry['timestamp'] );
	$timestamp	= $timeHelper->toPhrase( $this->env, TRUE );
	$url		= './test/table/edit/'.$entry['testId'];
	$link		= HtmlElements::Link( $url, $entry['title'] );
	$uriEdit		= './test/table/edit/'.$entry['testId'];
	$buttonEdit		= $this->html->LinkButton( $uriEdit, '', 'tiny edit' );
	$uriRemove		= './test/table/remove/'.$entry['testId'];
	$buttonRemove	= $this->html->LinkButton( $uriEdit, '', 'tiny remove' );
	$check		= $this->html->Checkbox( 'testId', $entry['testId'] );
	$rows[]		= '	<tr class="'.$class.'">
		<td>'.$check.'</td>
		<td>'.$link.'</td>
		<td>'.$timestamp.'</td>
		<td>'.$buttonEdit.$buttonRemove.'</td>
	</tr>';
	$number		++;
}
$rows	= implode( "\n", $rows );

$heads	= [
	'<input type="checkbox" class="toggler"/>',
	$words['index']['headTitle'],
	$words['index']['headTimestamp'],
	$words['index']['headAction'],
];
$heads	= HtmlElements::TableHeads( $heads );


return '

'.$heading.'
<fieldset>
	<legend>'.$words['index']['legend'].'</legend>
	'.$list.'
	<div class="buttonbar">
		'.$add.'
	</div>
</fieldset>
<br/>
<fieldset>
	<legend>'.$words['index']['legend'].'</legend>
	<table width="100%">
		<colgroup>
			<col width="3%"/>
			<col width="72%"/>
			<col width="15%"/>
			<col width="10%"/>
		</colgroup>
		'.$heads.'
		'.$rows.'
	</table>
	<div class="buttonbar">
		'.$add.'
	</div>
</fieldset>
';


$colgroup	= $this->html->ColumnGroup( '10%', '70%', '20%' );
$caption	= $this->html->TableCaption( $words['index']['caption'] );
$heads		= $this->html->TableHeads(
	array(
		$words['index']['head_id'],
		$words['index']['head_field'],
		$words['index']['head_timestamp']
	)
);

$content	= '
<h2>'.$words['filter']['heading'].'</h2>
'.$this->html->Form( 'filterTests', 'test.filter' ).'
<table class="panel" cellspacing="0" width="100%">
	'.$this->html->TableCaption( $words['filter']['caption'] ).'
	<tr><td>123</td></tr>
	<tr><td>'.$this->html->Button( 'filterTests', $words['filter']['button_filter'] ).'</td></tr>
</table>
</form>
<table class="list" cellspacing="0" width="100%">
	'.$caption.'
	'.$colgroup.'
	'.$heads.'
	'.$rows.'
</table>
';
$content	.= "
</table>
";
return $content;
?>
