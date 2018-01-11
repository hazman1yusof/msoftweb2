<?php
	
	class cancel_save{
		var $oper;
		var $seldata;
		var $responce;

		public function __construct(PDO $db){
			include_once('sschecker.php');
			$this->db = $db;
			$this->oper = $_GET['oper'];
			$this->seldata = $_POST['seldata'];
		}
		public function readableSyntax($prepare,array $arrayValue){
			foreach($arrayValue as $val){
				$prepare=preg_replace("/\?/", "'".$val."'", $prepare,1);
			}
			return $prepare."\r\n";
		}
		
		public function save($prepare,$arrayValue){

			/////////////////check sytax//////////////////////////////
			//echo $prepare;print_r($arrayValue);
			echo $this->readableSyntax($prepare,$arrayValue)."<br>";
			//////////////////////////////////////////////////////////

			$sth=$this->db->prepare($prepare);
			if (!$sth->execute($arrayValue)) {
				echo '{"msg":"failure"}<br>';
				throw new Exception($this->readableSyntax($prepare,$arrayValue));
			}else{
				echo '{"msg":"success"}<br>';
			}
		}
		
		public function edit_table($commit){
			$seldata = $this->seldata;
			//$tempobj = $this->getyearperiod($seldata['actdate']);

			try{
				if($commit){
					$this->db->beginTransaction();
				}
				
				if($this->oper=='add'){
				
					//4th step change status to posted
					$prepare = "UPDATE finance.apacthdr SET recstatus = ? WHERE auditno = ? AND source = ? AND trantype =?";

					$arrayValue = array('C',$seldata['auditno'],$seldata['source'],$seldata['trantype']);

					$this->save($prepare,$arrayValue);

					
				}else if($this->oper=='edit'){
				
					$updarrField=['compcode','upduser','upddate','recstatus'];//extra field
					$updarrValue=[$this->compcode,$this->user,'NOW()','A'];
					
					$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,!empty($this->filterCol));

					$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
					array_push($arrayValue,$this->post[$this->columnid],$this->compcode);
					$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;
					
				}else if($this->oper=='del'){
				
					$delarrField=['compcode','deluser','deldate','recstatus'];//extra field
					$delarrValue=[$this->compcode,$this->user,'NOW()','D'];
				
					$prepare = $this->autoSyntaxDel($delarrField,$delarrValue,!empty($this->filterCol));
					
					$arrayValue = $this->arrayValue($delarrField,$delarrValue,true);
					array_push($arrayValue,$this->post[$this->columnid],$this->compcode);
					$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;
					
				}

				if($commit){
					$this->db->commit();
				}
				
			}catch( Exception $e ){
				$this->db->rollback();
				http_response_code(400);
				echo $e->getMessage();
				
			}
		}
	}

?>
