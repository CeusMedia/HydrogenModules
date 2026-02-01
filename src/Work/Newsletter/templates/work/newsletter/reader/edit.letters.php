<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_Work_Newsletter_Reader $view */
/** @var object $words */
/** @var object $reader */
/** @var bool $tabbedLinks */
/** @var array $groups */
/** @var array $readerLetters */
/** @var array $readerGroups */

$listLetters	= '<div class="alert alert-info">Dieser Abonnent hat noch keinen Newsletter erhalten.</div>';
if( $readerLetters ){
	$stats		= (object) [
		'sent'		=> 0,
		'opened'	=> 0,
		'ratio'		=> 0,
	];
	$listLetters	= [];
	foreach( $readerLetters as $letter ){
		$attributes		= [
			'href'	=> './work/newsletter/edit/'.$letter->newsletterId
		];
		$class	= 'label label-error';
		if( $letter->status >= 1 ){
			$stats->sent++;
			$class	= 'label label-warning';
		}
		if( $letter->status >= 2 ){
			$stats->opened++;
			$class	= 'label label-success';
		}
		if( $stats->sent > 0 )
			$stats->ratio	= round( $stats->opened / $stats->sent * 100, 1 );

		$indicator		= HtmlTag::create( 'span', '&nbsp;&nbsp;&nbsp;', ['class' => $class] );
		$link			= HtmlTag::create( 'a', $letter->newsletter->title, $attributes );
		$listLetters[]	= HtmlTag::create( 'li', $indicator.'&nbsp;'.$link, ['class' => 'autocut'] );
	}
	$listLetters	= HtmlTag::create( 'ul', $listLetters, ['class' => 'unstyled'] );
	$listLetters	= HtmlTag::create( 'div', $listLetters, ['id' => 'reader-newsletter-list'] );
	if( $stats->sent > 0 ){
		$list	= [];
		$list[]	= HtmlTag::create( 'dt', 'Zugestellt' );
		$list[]	= HtmlTag::create( 'dd', $stats->sent );
		$list[]	= HtmlTag::create( 'dt', 'Geöffnet' );
		$list[]	= HtmlTag::create( 'dd', $stats->opened );
		$list[]	= HtmlTag::create( 'dt', 'Rate' );
		$list[]	= HtmlTag::create( 'dd', $stats->ratio.'%' );
		$listLetters	.= '<hr/>'.HtmlTag::create( 'dl', $list, ['class' => "dl-horizontal"] );
	}
}

return '
			<div class="content-panel">
				<h3>Erhaltende Newsletter</h3>
				<div class="content-panel-inner">
					'.$listLetters.'
				</div>
			</div>
';