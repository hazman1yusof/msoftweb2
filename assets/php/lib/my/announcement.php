<?php

	class Announcement
	{
	    protected $db;
		var $user;
		var $compcode;
		
	    public function __construct(PDO $db)
	    {
			include_once('sschecker.php');
	        $this->db = $db;
			$this->user = $_SESSION['username'];
			$this->compcode = $_SESSION['company'];
	    }
			
		public function generate(){
			
			$ret_str="
				<div id='myCarousel' class='carousel slide' data-ride='carousel'>
					<ol class='carousel-indicators'>";
			
		
			$SQLcnt="SELECT count(*) as COUNT from sysdb.compose WHERE type='announcement' AND NOW() BETWEEN dateFrom AND dateTo and compcode='{$this->compcode}'";
			$result = $this->db->query($SQLcnt);if (!$result) { print_r($this->db->errorInfo()); }
			$row = $result->fetch(PDO::FETCH_ASSOC);
			$count=$row['COUNT'];
			
			for($x=0;$x<intval($count);$x++){
				$ret_str.="
						<li data-target='#myCarousel' data-slide-to='{$x}' ";
				if($x==0)$ret_str.="class='active'";
				$ret_str.="></li>";
			}
			$ret_str.="
				</ol>
				<div class='carousel-inner' role='listbox'>";
			
			$sql="SELECT * FROM sysdb.compose WHERE TYPE='announcement' AND NOW() BETWEEN dateFrom AND dateTo and compcode='{$this->compcode}'";
			$result = $this->db->query($sql);if (!$result) { print_r($this->db->errorInfo()); }
			
			$x=0;
			while($row = $result->fetch(PDO::FETCH_ASSOC)){
				$ret_str.="
					<div class='item";
				if($x==0)$ret_str.=" active";
				$ret_str.="'>";
			    
				$ret_str.="
					<img src='{$row['imgLoc']}'>";
				$ret_str.="
						<div class='container'>
							<div class='carousel-caption'>";
				$ret_str.="
								<h1>{$row['title']}</h1>
									{$row['contains']}
							</div>
						</div>
					</div>";
				$x++;
			}
			
			$ret_str.="
				</div>
			      <a class='left carousel-control' href='#myCarousel' role='button' data-slide='prev'>
			        <span class='glyphicon glyphicon-chevron-left' aria-hidden='true'></span>
			        <span class='sr-only'>Previous</span>
			      </a>
			      <a class='right carousel-control' href='#myCarousel' role='button' data-slide='next'>
			        <span class='glyphicon glyphicon-chevron-right' aria-hidden='true'></span>
			        <span class='sr-only'>Next</span>
			      </a>
		    	</div>";
			
			
		
		    return '{"res":' .  json_encode($ret_str) . '}';
		}	
	}
?>