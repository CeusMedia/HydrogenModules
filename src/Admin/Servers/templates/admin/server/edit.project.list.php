<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $serverProjects */

$wordsProjectVersionStates	= $words['states-project'];

//  --  VERSIONS  --  //
$list	= [];
foreach( $serverProjects as $relation ){
	$label		= $relation->project->title;
	$version	= '';
	if( $relation->projectVersionId ){
		$version	= $relation->projectVersion->version;
		if( $relation->projectVersion->title )
			$version	.= ':&nbsp;'.$relation->projectVersion->title;
		if( $relation->projectVersion->status )
			$version	.= ':&nbsp;<small>('.$wordsProjectVersionStates[$relation->projectVersion->status].')</small>';
	}
	$status		= $words['states-project'][$relation->status];
	$remove	= HtmlElements::LinkButton( './admin/project/removeServer/'.$relation->serverProjectId, '', 'button icon tiny remove' );
	$label	= HtmlTag::create( 'a', $label, ['href' => './admin/project/edit/'.$relation->projectId] );
	if( $relation->title )
		$label	.= ': '.$relation->title;
	$list[]	= '<tr><td>'.$label.'</td><td>'.$version.'</td><td style="text-align: right">'.$remove.'</td></tr>';
}
$listServers	= '<table>'.join( $list ).'</table>';
$panelList		= '
	<fieldset>
		<legend>Projekte</legend>
		'.$listServers.'
	</fieldset>
	<br/>
';
return $panelList;
