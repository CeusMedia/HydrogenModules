<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_DevProfiler
{
	public static function render( Environment $env )
	{
		$runtime	= $env->getRuntime();
		$words		= $env->getLanguage()->getWords( 'ui.dev.layer.profiler' );
		$options	= $env->getConfig()->getAll( 'module.ui_devlayers_profiler.', TRUE );
		$filter		= $options->get( 'filter' ) ? $options->get( 'filter.type' ) : NULL;
		$threshold	= $options->get( 'filter.threshold' );
		$runtime->reach( 'UI:Helper:Dev:Profiler::render: init' );
		$timeTotal	= $runtime->get( 6, 0 );

		$current	= 0;
		$list		= [];
		foreach( $runtime->getGoals() as $task ){
			$width		= $task->timeMicro / $timeTotal * 100;
			if( $filter === "ms" && $task->timeMicro / 1000 <= $threshold )
				continue;
			if( $filter === "%" && $width <= $threshold )
				continue;

			$offset		= $current / $timeTotal * 100;
			$style		= 'width: '.$width.'%; left: '.$offset.'%';
			$bar		= '<span class="task-bar" style="'.$style.'"></span>';

			$about		= round( $width / 5 ) * 5;
			$classes	= ['task-line', 'about-'.$about];
			$cells		= array(
				'<td class="task-title">'.$task->label.'</td>',
				'<td class="task-measure">'.round( $task->timeMicro / $timeTotal * 100 ).'%</td>',
				'<td class="task-measure">'.self::formatTime( $task->timeMicro ).'s</td>',
				'<td><div class="task-line">'.$bar.'</div></td>'
			);
			$list[]		= '<tr class="'.join( ' ', $classes ).'">'.join( $cells ).'</tr>';
			$current	= $task->totalMicro;
		}
		$total		= '<tr class="total"><td>Total</td><td colspan="2" class="task-measure">'.self::formatTime( $timeTotal ).'s</td><td></td></tr>';
		$list[]		= HtmlTag::create( 'tfoot', $total );
		$colgroup	= HtmlElements::ColumnGroup( "400", "50", "75", "" );
		$heads		= array(
			'<th>'.$words['layer']['headTask'].'</th>',
			'<th class="task-measure">'.$words['layer']['headPercentage'].'</th>',
			'<th class="task-measure">'.$words['layer']['headTime'].'</th>',
			'<th>'.$words['layer']['headProportion'].'</th>'
		);
		$thead		= HtmlTag::create( 'thead', HtmlTag::create( 'tr', join( $heads ) ) );
		$tbody		= HtmlTag::create( 'tbody', join( $list ) );
		$content	= $colgroup.$thead.$tbody;
		return HtmlTag::create( 'table', $content, ['class' => 'profiler'] );
	}

	protected static function formatTime( $microseconds ): string
	{
		$time	= Alg_UnitFormater::formatMicroSeconds( $microseconds );
		return substr( str_replace( ' ', '', $time ), 0, -1 );
	}
}
