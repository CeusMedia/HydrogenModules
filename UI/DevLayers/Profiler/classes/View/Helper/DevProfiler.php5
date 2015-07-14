<?php
class View_Helper_DevProfiler{

	static public function ___onPageBuild( $env, $context, $module, $arguments = array() ){
		if( $env->getConfig()->get( 'module.ui_devlayers_profiler.enabled' ) ){
			$context->addThemeStyle( 'module.ui.dev.layer.profiler.css' );
			$content	= View_Helper_DevProfiler::render( $env );
			View_Helper_DevLayers::add( 'profiler', 'Profiler', $content );
		}
	}
	
	static protected function formatTime( $microseconds ){
		$time	= Alg_UnitFormater::formatMicroSeconds( $microseconds );
		return substr( str_replace( ' ', '', $time ), 0, -1 );
	}

	static public function render( $env ){
		$profiler	= $env->clock->profiler;
		$words		= $env->getLanguage()->getWords( 'ui.dev.layer.profiler' );
		$options	= $env->getConfig()->getAll( 'module.ui_devlayers_profiler.', TRUE );
		$filter		= $options->get( 'filter' ) ? $options->get( 'filter.type' ) : NULL;
		$threshold	= $options->get( 'filter.threshold' );
		$profiler->tick( 'UI:Helper:Dev:Profiler::render: init' );
		$timeTotal	= $env->clock->stop( 6, 0 );

		$current	= 0;
		$list		= array();
		foreach( $profiler->get() as $task ){
			$width		= $task['timeMicro'] / $timeTotal * 100;
			if( $filter === "ms" && $task['timeMicro'] / 1000 <= $threshold )
				continue;
			if( $filter === "%" && $width <= $threshold )
				continue;

			$offset		= $current / $timeTotal * 100;
			$style		= 'width: '.$width.'%; left: '.$offset.'%';
			$bar		= '<span class="task-bar" style="'.$style.'"></span>';

			$about		= round( $width / 5 ) * 5;
			$classes	= array( 'task-line', 'about-'.$about );
			$cells		= array(
				'<td class="task-title">'.$task['label'].'</td>',
				'<td class="task-measure">'.round( $task['timeMicro'] / $timeTotal * 100 ).'%</td>',
				'<td class="task-measure">'.self::formatTime( $task['timeMicro'] ).'s</td>',
				'<td><div class="task-line">'.$bar.'</div></td>'
			);
			$list[]		= '<tr class="'.join( ' ', $classes ).'">'.join( $cells ).'</tr>';
			$current	= $task['totalMicro'];
		}
		$total		= '<tr class="total"><td>Total</td><td colspan="2" class="task-measure">'.self::formatTime( $timeTotal ).'s</td><td></td></tr>';
		$list[]		= UI_HTML_Tag::create( 'tfoot', $total );
		$colgroup	= UI_HTML_Elements::ColumnGroup( "400", "50", "75", "" );
		$heads		= array(
			'<th>'.$words['layer']['headTask'].'</th>',
			'<th class="task-measure">'.$words['layer']['headPercentage'].'</th>',
			'<th class="task-measure">'.$words['layer']['headTime'].'</th>',
			'<th>'.$words['layer']['headProportion'].'</th>'
		);
		$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', join( $heads ) ) );
		$tbody		= UI_HTML_Tag::create( 'tbody', join( $list ) );
		$content	= $colgroup.$thead.$tbody;
		return UI_HTML_Tag::create( 'table', $content, array( 'class' => 'profiler' ) );
	}
}
?>