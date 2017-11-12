<?php

include 'graph_lib/src/jpgraph.php';
include 'graph_lib/src/jpgraph_line.php';

class StatsGraph
{

	private $graph;
	private $minXTicks=40;

	public function __construct($hSize, $vSize)
	{
		$this->graph = new Graph($hSize,$vSize);    
		$this->graph->SetScale("textlin");
		// Adjust the margin
		$this->graph->img->SetMargin(60,200,20,150);
		$this->graph->SetColor('#d3d1d2');
		$this->graph->SetMarginColor('#d3d1d2');
		$this->graph->SetFrame(true,'#d3d1d2');


	}

	public function __destruct()
	{

	}

	public function SetXLabels($xData)
	{
		$this->graph->xaxis->SetTickLabels($xData);
		$this->graph->xaxis->SetLabelAngle(90);
		$tickSpacing=0;
		$numPoints=count($xData);
		if($numPoints>$this->minXTicks)
		{
			$tickSpacing=floor($numPoints/$this->minXTicks);
			$this->graph->xaxis->SetTextTickInterval($tickSpacing);
		}
	}
	

	public function SetGraphColors($mainColor,$marginColor,$frameColor)
	{
		$this->graph->SetColor($mainColor);
                $this->graph->SetMarginColor($marginColor);
                $this->graph->SetFrame(true,$frameColor);
	}	

	public function AddLineGraph($yData,$plotName,$yAxis,$color)
	{
		if($yAxis==0)
		{
			$linePlot=new LinePlot($yData);
			$this->graph->Add($linePlot);
			$linePlot->SetColor($color);
			$linePlot->SetWeight(2);
			$linePlot->SetLegend($plotName);
		}
		if($yAxis==1)
		{
			$this->graph->SetY2Scale("lin");
			$linePlot= new LinePlot($yData);
			$this->graph->AddY2($linePlot);
			$linePlot->SetColor($color);
			$linePlot->SetWeight(2);
			$linePlot->SetStyle('dashed'); 
			$linePlot->SetLegend($plotName);
			$this->graph->y2axis->SetColor("orange");
		}
	}

	public function DrawLineGraph()
	{
		$this->graph->yaxis->SetColor("blue");
		//$this->graph->legend->SetLayout(LEGEND_VERT);
		//$this->graph->legend->Pos(0.4,0.95,"right","top");
		return $this->graph->Stroke();	
	}
}

?>
