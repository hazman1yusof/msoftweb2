<?php
	class get_effdate{
		
		protected $db;

		var $compcode;

	    public function __construct(PDO $db)
	    {	
			include_once('sschecker.php');
	        $this->db = $db; // connection
			$this->compcode=$_SESSION['company'];
		}
		
		public function effdate_for($var){

			$json = new stdClass();

			switch($var){
				case 'forex':
					
					$sqlOuter="select forexcode,description,costcode,glaccount from debtor.forexmaster where compcode = '{$this->compcode}' and recstatus='A'";
					$resultOuter = $this->db->query($sqlOuter);if (!$resultOuter) { print_r($this->db->errorInfo());}

					$i=0;
					while($rowOuter = $resultOuter->fetch(PDO::FETCH_ASSOC)) {
						$sqlInner="select idno,rate,effdate from debtor.forex where forexcode = '{$rowOuter['forexcode']}' and effdate <= NOW() and recstatus='A' order by effdate desc";
						$resultInner = $this->db->query($sqlInner);if (!$resultInner) { print_r($this->db->errorInfo());}

						$rowInner = $resultInner->fetch(PDO::FETCH_ASSOC);

						$json->rows[$i]['id']=$rowInner['idno'];
						$json->rows[$i]['cell']=array($rowOuter['forexcode'],$rowOuter['description'],$rowOuter['costcode'],$rowOuter['glaccount'],$rowInner['rate'],$rowInner['effdate']);

						$i++;
					}
					break;

				default:
					break;
			}
			return json_encode($json);
		}
		

	}
?>