<?php
class View_Work_Issue extends CMF_Hydrogen_View{

	public function add(){
	}
	public function edit(){
	}
	public function index(){
	}

	public function renderOptions( $options, $key, $values, $class = '' ){
		$list		= array();
		if( !is_array( $values ) )
			$values = $values ? array( $values ) : array();
		foreach( $options as $key => $value ){
			$selected	= !strlen( $key ) && !$values;
			if( strlen( $key ) )
				$selected	= in_array( $key, $values );
			$attributes	= array(
				'value'		=> $key,
				'class'		=> strlen( $key ) ? sprintf( $class, $key ) : '',
				'selected'	=>  $selected ? 'selected' : NULL,
			);
			$list[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
		}
		return join( $list );
	}

	/**
	 *	Builds HTML of Graph (with Link Map).
	 *	@access		private
	 *	@param		string		$type		Type of graph (status|priority|type)
	 *	@return 	string
	 *	@todo		kriss: finish integration (words, config etc.)
	 */
	public function buildGraph( $data, $words, $type ){
		$config			= $this->env->getConfig();
		$request		= $this->env->getRequest();
		
		$graphConfig	= array_merge(
			$config->getAll( 'module.work_issues.graph.all.' ),
			$config->getAll( 'module.work_issues.graph.'.$type.'.' )
		);
	
		$statistics		= $data[$type];
		
		$data	= array();
		$legend	= array();
		$targ	= array();
		$alts	= array();
		$data	= array();
		$total	= 0;
		foreach( $statistics as $stat ){
			$data[]		= $stat['count'];
			$legend[]	= $stat['name']." (".$stat['count'].")";
			$targ[]		= "work/issue/filter?".$type."[]=".$stat[$type];
			$alts[]		= $stat['name'];
			$total		+= $stat['count'];
		}
		if( !$total )
			return "No bugs found";
		$graph = new PieGraph( $graphConfig['width'], $graphConfig['height'], 'graph_'.$type );
		$graph->SetFrame( !true, array(255,255,255), 10);
				
		$graph->SetShadow( $graphConfig['shadow'] );
		$graph->SetAntiAliasing( $graphConfig['antialias'] );
		$graph->title->Set( utf8_decode( $words['graph']['title'.ucFirst( $type )] ) );
//		$graph->title->SetFont( FF_VERDANA,FS_NORMAL, 11 );
		$graph->title->SetFont( FF_FONT2, FS_NORMAL );
		$graph->title->SetPos( 0,  0, 'left', 'top');
		$graph->legend->Pos( $graphConfig['legend.marginX'], $graphConfig['legend.marginY'], $graphConfig['legend.alignX'], $graphConfig['legend.alignY'] );
//		$graph->legend->SetShadow( $graphConfig['legend_shadow'] );
		$graph->legend->SetShadow( false );
		$graph->legend->SetFrameWeight( 1 );
		$graph->legend->SetColor( "black", "gray" );
//		$graph->legend->SetShadow( TRUE );
		$graph->legend->SetFillColor( array(255,255,255 ) );
#		$graph->legend->SetShadow( 'darkgray', 1 );
//		$graph->legend->SetFont( FF_VERDANA, FS_NORMAL, 8 );
		$graph->legend->SetFont( FF_FONT1, FS_NORMAL, 8 );
		$graph->legend->SetLayout( $graphConfig['legend.layout'] );
		$graph->legend->SetHColMargin( $graphConfig['legend.margin.hcol'] );
		$p1 = new PiePlot3D( $data );
		$p1->SetEdge();
		$p1->setAngle( 30 );
		$p1->value->SetFormat( "%d%%" );
		$p1->value->Show();
//		$p1->value->SetFont( FF_VERDANA, FS_NORMAL, 8 );
		$p1->value->SetFont( FF_FONT1, FS_NORMAL, 7 );
		$p1->SetLegends( $legend );
		$p1->SetCSIMTargets( $targ, $alts );
		$p1->setSliceColors( explode( ",", $graphConfig['colors'] ) );
		$p1->SetCenter( $graphConfig['centerX'], $graphConfig['centerY'] );
		$graph->Add( $p1 );
		$uri	= $config->get( 'path.images' ).'graph_'.$type.'.png';
		$graph->Stroke( $uri );
		$map	= $graph->GetHTMLImageMap( 'graph-'.$type, 0, 50 );
		$image	= "<img src='./".$uri."?".time()."' ISMAP USEMAP='#graph-".$type."' border='0'>";
		
		if( !( empty( $graphConfig['cropX'] ) && empty( $graphConfig['cropY'] ) ) ){
			$offsetX	= (int) $graphConfig['cropX'];
			$offsetY	= (int) $graphConfig['cropY'];
			$matches	= array();
			preg_match_all( '/ coords="(.+)"/U', $map, $matches );
			foreach( $matches[1] as $nr => $match ){
				$coords	= array();
				foreach( explode( ",", $match ) as $a => $coord ){
					$coord	-= $a % 2 ? $offsetY : $offsetX;
					$coords[]	= $coord;
				}
				$map	= str_replace( $match, join( ',', $coords ), $map );
			}
			$file	= new UI_Image( $uri );
			$proc	= new UI_Image_Processing( $file );
			$width	= (int) $graphConfig['width'] - 2 * $offsetX;
			$height	= (int) $graphConfig['height'] - 2 * $offsetY;
			$proc->crop( $offsetX, $offsetY, $width, $height );
			$file->save();
		}
		return $map.$image;
	}
}
?>
