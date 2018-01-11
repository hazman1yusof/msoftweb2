<?php
	class invTran_save extends EditTable {


		public function request_no($trantype,$dept){
			$sql="SELECT seqno FROM material.sequence WHERE trantype = '$trantype' AND dept = '$dept'";
			$result = $this->db->query($sql);if(!$result){throw new Exception("request_no: ".$this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$request_no = intval($row['seqno']);

			if(empty($request_no)){
				throw new Exception("Sequence for dept: $dept and trantype: $trantype' dont exist");
			}

			$sql="UPDATE material.sequence SET seqno = '$request_no' + '1' WHERE trantype = '$trantype' AND dept = '$dept'";
			$result = $this->db->query($sql);if(!$result){throw new Exception("request_no: ".$this->db->errorInfo()[2]);}
			
			return $request_no;
		}

		public function recno($source,$trantype){
			$sqlSysparam="SELECT pvalue1 FROM sysdb.sysparam WHERE source = '$source' AND trantype = '$trantype'";
			$result = $this->db->query($sqlSysparam);if(!$result){throw new Exception("recno: ".$this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);
			
			$pvalue1=intval($row['pvalue1'])+1;
			
			$sqlSysparam="UPDATE sysdb.sysparam SET pvalue1 = '{$pvalue1}' WHERE source = '$source' AND trantype = '$trantype'";
			$result = $this->db->query($sqlSysparam);if(!$result){throw new Exception("recno: ".$this->db->errorInfo()[2]);}
			
			return $pvalue1;
		}

		public function idno($request_no,$recno){
			$sql="SELECT idno FROM material.ivtmphd WHERE docno = '$request_no' AND recno = '$recno'";
			$result = $this->db->query($sql);if(!$result){throw new Exception("idno: ".$this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);
			
			return $row['idno'];
		}

		public function save($prepare,$arrayValue){

			/////////////////check sytax//////////////////////////////
			//echo $prepare;print_r($arrayValue);
			//echo $this->readableSyntax($prepare,$arrayValue)."<br>";
			//////////////////////////////////////////////////////////

			$sth=$this->db->prepare($prepare);
			if (!$sth->execute($arrayValue)) {
				//echo '{"msg":"failure"}<br>';
				throw new Exception($this->readableSyntax($prepare,$arrayValue));
			}else{
				//echo '{"msg":"success"}<br>';
			}
		}
		
		public function edit_table($commit){

			try{
				if($commit){
					$this->db->beginTransaction();
				}
					if($this->oper == 'add'){
						$request_no = $this->request_no($_POST['trantype'],$_POST['txndept']);
						$recno = $this->recno('IV','IT');

						$addarrField=['source','docno','recno','compcode','adduser','adddate','recstatus'];//extra field
						$addarrValue=['IV',$request_no,$recno,$this->compcode,$this->user,'NOW()','OPEN'];
					
						$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
						$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

						$this->save($prepare,$arrayValue);

						$responce = new stdClass();
						$responce->docno = $request_no;
						$responce->recno = $recno;
						$responce->idno = $this->idno($request_no,$recno);
						$responce->sql = $this->readableSyntax($prepare,$arrayValue);
						echo json_encode($responce);

					}else if($this->oper == 'edit'){
						$updarrField=['compcode','upduser','upddate','recstatus'];//extra field
						$updarrValue=[$this->compcode,$this->user,'NOW()','OPEN'];
						$this->columnid = 'idno';

						$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,false);

						$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
						array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);

						$this->save($prepare,$arrayValue);

						$responce = new stdClass();
						$responce->sql = $this->readableSyntax($prepare,$arrayValue);
						echo json_encode($responce);
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
