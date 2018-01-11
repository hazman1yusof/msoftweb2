<?php

	class MenuModel
	{
	    protected $db;

	    public function __construct(PDO $db)
	    {
	        $this->db = $db;
	    }	
	    		
		function tab($loop)
		{
			return (30 + (10 * $loop)) . 'px';
		}

		var $_x=-2;
		var	$_rowX = array();
		var	$_myQueryX = array();
		var	$_resultX= array();
		var	$_class='main';
		var $_menu_str = "";
		var $_arr = array();


		function create_main_menu()
		{			
			session_start();

			$myQuery = "SELECT * FROM sysdb.programtab a INNER JOIN sysdb.groupacc b on a.programmenu=b.programmenu AND a.lineno = b.lineno ";
			$myQuery.= "WHERE b.groupid='{$_SESSION['groupid']}' AND b.compcode='{$_SESSION['company']}' AND b.programmenu='main' order by b.lineno";
			
			$result = $this->db->query($myQuery);
			
			//while($rowX[$x] = $result->fetch_array(MYSQL_ASSOC))
			while($this->_rowX[$this->_x] = $result->fetch(PDO::FETCH_ASSOC))
		    {	
				$this->_class='main';
				$this->_menu_str .= $this->create_sub_menu($this->_rowX[$this->_x],$this->_x,$this->_class,$_SESSION['company']);				
		    }

		    $this->_arr[] = $this->_menu_str;

		    $ret_result = '{"menu":' . json_encode($this->_arr) . '}';

		    return $ret_result;
		}
		
		function create_sub_menu($rowX,$x,$class,$compcode)
		{
			//global $mysqli;
	        $this->_x = $this->_x+1;

	        if($rowX['programtype']=='M')
	        {  	
				
				if($class!='main')
				{
					$this->_menu_str .= "
					<li>
						<a href='#' aria-expanded='false' style='padding-left:".$this->tab($this->_x)."'><span class='lilabel'>" .$rowX['programname'] ."</span><span class='fa plus-minus'></span></a>
						<ul aria-expanded='false'>";
				}
				else
				{
					$this->_menu_str .= "
					<li>
						<a href='#' aria-expanded='false' class='main' style='padding-left:".$this->tab($this->_x)."'><span class='fa " .$rowX['condition3'] ." fa-2x' style='padding-right:5px'></span><span class='lilabel'>". $rowX['programname'] ."</span><span class='glyphicon arrow'></span></a>
						<ul aria-expanded='false'>";
				}
				
	            $this->_myQueryX[$this->_x] = "SELECT * FROM sysdb.programtab a INNER JOIN sysdb.groupacc b ON a.programmenu=b.programmenu AND a.lineno = b.lineno ";
	            $this->_myQueryX[$this->_x].= "WHERE b.groupid='{$_SESSION['groupid']}' AND b.compcode='{$compcode}' AND b.programmenu='{$rowX['programid']}' ORDER BY b.lineno";
				
				$result = $this->db->query($this->_myQueryX[$this->_x]);

	            //while($rowX[$x]=$result->fetch_array(MYSQL_ASSOC))
	            while($rowX[$this->_x] = $result->fetch(PDO::FETCH_ASSOC))
	            {
					$class='notmain';
	                $this->create_sub_menu($rowX[$this->_x],$this->_x,$class,$compcode);
	            }
				
				$this->_menu_str .= "</ul></li>";
				
	        }
	        else
	        {
				
	            $SQL1 = "SELECT * FROM sysdb.programtab a INNER JOIN sysdb.groupacc b ON a.programmenu=b.programmenu AND a.lineno = b.lineno ";
	            $SQL1.= "WHERE b.groupid='{$_SESSION['groupid']}' and b.compcode='{$compcode}' and b.programmenu='{$rowX['programid']}'";
				
				$result = $this->db->query($SQL1);
				
	            $row1 = $result->fetch(PDO::FETCH_ASSOC);
				
				$this->_menu_str .= "<li><a style='padding-left:".$this->tab($this->_x)."' title='".$rowX["programname"]."' class='clickable' programid='".$rowX["programid"]."' targetURL='page/".$rowX["url"]."'><span class='lilabel'>".$rowX["programname"]."</span></a></li>"; 
	        }

	        $this->_x = $this->_x-1;	        

	    }
	
	}
?>