<?php

use CeusMedia\Common\UI\Image;
use CeusMedia\Common\UI\Image\Processing as ImageProcessing;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

class View_Helper_Work_Issue_Graph
{
	protected Environment $env;

	protected array $data	= [];
	protected array $words	= [];
	protected string $type	= '';

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	Builds HTML of Graph (with Link Map).
	 *	@return 	string
	 *	@todo		 finish integration (words, config etc.)
	 */
	public function render(): string
	{
		if( '' === $this->type )
			throw new RuntimeException( 'No type set' );

		$config			= $this->env->getConfig();

		$graphConfig	= array_merge(
			$config->getAll( 'module.work_issues.graph.all.' ),
			$config->getAll( 'module.work_issues.graph.'.$this->type.'.' )
		);

		$legend	= [];
		$targ	= [];
		$alts	= [];
		$data	= [];
		$total	= 0;
		foreach( $this->data[$this->type] as $stat ){
			$data[]		= $stat['count'];
			$legend[]	= $stat['name']." (".$stat['count'].")";
			$targ[]		= "work/issue/filter?".$this->type."[]=".$stat[$this->type];
			$alts[]		= $stat['name'];
			$total		+= $stat['count'];
		}
		if( !$total )
			return "No bugs found";
		$graph = new Graph\PieGraph( $graphConfig['width'], $graphConfig['height'], 'graph_'.$this->type );
		$graph->SetFrame( !TRUE, [255,255,255], 10);

		$graph->SetShadow( $graphConfig['shadow'] );
		$graph->SetAntiAliasing( $graphConfig['antialias'] );
		$graph->title->Set( utf8_decode( $this->words['graph']['title'.ucFirst( $this->type )] ) );
//		$graph->title->SetFont( FF_VERDANA,FS_NORMAL, 11 );
		$graph->title->SetFont( FF_FONT2, FS_NORMAL );
		$graph->title->SetPos( 0,  0, 'left', 'top');
		$graph->legend->Pos( $graphConfig['legend.marginX'], $graphConfig['legend.marginY'], $graphConfig['legend.alignX'], $graphConfig['legend.alignY'] );
//		$graph->legend->SetShadow( $graphConfig['legend_shadow'] );
		$graph->legend->SetShadow( false );
		$graph->legend->SetFrameWeight( 1 );
		$graph->legend->SetColor( "black", "gray" );
//		$graph->legend->SetShadow( TRUE );
		$graph->legend->SetFillColor( [255,255,255] );
#		$graph->legend->SetShadow( 'darkgray', 1 );
//		$graph->legend->SetFont( FF_VERDANA, FS_NORMAL, 8 );
		$graph->legend->SetFont( FF_FONT1, FS_NORMAL, 8 );
		$graph->legend->SetLayout( $graphConfig['legend.layout'] );
		$graph->legend->SetHColMargin( $graphConfig['legend.margin.hcol'] );
		$p1 = new Plot\PiePlot3D( $data );
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
		$uri	= $config->get( 'path.images' ).'graph_'.$this->type.'.png';
		$graph->Stroke( $uri );
		$map	= $graph->GetHTMLImageMap( 'graph-'.$this->type );
		$image	= "<img src='./".$uri."?".time()."' ISMAP USEMAP='#graph-".$this->type."' border='0'>";

		if( !( empty( $graphConfig['cropX'] ) && empty( $graphConfig['cropY'] ) ) ){
			$offsetX	= (int) $graphConfig['cropX'];
			$offsetY	= (int) $graphConfig['cropY'];
			$matches	= [];
			preg_match_all( '/ coords="(.+)"/U', $map, $matches );
			foreach( $matches[1] as $nr => $match ){
				$coords	= [];
				foreach( explode( ",", $match ) as $a => $coord ){
					$coord	-= $a % 2 ? $offsetY : $offsetX;
					$coords[]	= $coord;
				}
				$map	= str_replace( $match, join( ',', $coords ), $map );
			}
			$file	= new Image( $uri );
			$proc	= new ImageProcessing( $file );
			$width	= (int) $graphConfig['width'] - 2 * $offsetX;
			$height	= (int) $graphConfig['height'] - 2 * $offsetY;
			$proc->crop( $offsetX, $offsetY, $width, $height );
			$file->save();
		}
		return $map.$image;
	}

	public function setData( array $data ): self
	{
		$this->data		= $data;
		return $this;
	}

	public function setType( string $type ): self
	{
		$this->type		= trim( $type );
		return $this;
	}

	public function setWords( array $words ): self
	{
		$this->words		= $words;
		return $this;
	}
}