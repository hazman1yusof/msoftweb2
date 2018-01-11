<?php
	class purOrder_header_save extends EditTable {

		var $total;

	/*public function request_no($trantype,$dept){
			$sql="SELECT seqno FROM material.sequence WHERE trantype = '$trantype' AND dept = '$dept'";
			$result = $this->db->query($sql);if(!$result){throw new Exception("request_no: ".$this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$request_no = intval($row['seqno']);

			$sql="UPDATE material.sequence SET seqno = '$request_no' + '1' WHERE trantype = '$trantype' AND dept = '$dept'";
			$result = $this->db->query($sql);if(!$result){throw new Exception("request_no: ".$this->db->errorInfo()[2]);}

			return $request_no;

		} */

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
			$sql="SELECT idno FROM material.purordhd WHERE  purreqno='$request_no' AND  recno = '$recno'";
			$result = $this->db->query($sql);if(!$result){throw new Exception("idno: ".$this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);
			
			return $row['idno'];
		}

		public function purOrd_No($trantype,$dept){
			$sql="SELECT seqno FROM material.sequence WHERE trantype = '$trantype' AND dept = '$dept'";
			$result = $this->db->query($sql);if(!$result){throw new Exception("request_no: ".$this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$purOrd_no = intval($row['seqno']);
			if(empty($purOrd_no)){
				throw new Exception("Sequence for dept: $dept and trantype: $trantype' dont exist");
			}


			$sql="UPDATE material.sequence SET seqno = '$purOrd_no' + '1' WHERE trantype = '$trantype' AND dept = '$dept'";
			$result = $this->db->query($sql);if(!$result){throw new Exception("purOrd_no: ".$this->db->errorInfo()[2]);}

			return $purOrd_no;

		}

		public function toGetAllpurreqhd($recno){
			$sql="SELECT purreqno FROM material.purordhd WHERE recno = '$recno'";
			$result = $this->db->query($sql);if(!$result){throw new Exception("idno: ".$this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$purreqno = intval($row['purreqno']);

			return $purreqno;

		}

	

		public function saveDetail($refer_recno,$recno){

			$company = $this->compcode;

			$sql = "SELECT compcode,recno,lineno_,pricecode,itemcode,uomcode,qtyrequest,unitprice,taxcode,perdisc,amtdisc,amtslstax,amount,remarks,recstatus FROM material.purreqdt WHERE recno = '{$refer_recno}' AND compcode = '{$company}' AND recstatus <> 'DELETE'";
			$result = $this->db->query($sql);if(!$result) { throw new Exception(print_r($this->db->errorInfo())); }

			while($row = $result->fetch(PDO::FETCH_ASSOC)) {
				////1. calculate lineno_ by recno
				$sqlln = "SELECT COUNT(lineno_) as COUNT from material.purorddt WHERE compcode='$company' AND recno='$recno'";

				$resultln = $this->db->query($sqlln);if(!$resultln) { print_r($this->db->errorInfo()); }
				$rowln = $resultln->fetch(PDO::FETCH_ASSOC);

				$li=intval($rowln['COUNT'])+1;

				///2. insert detail
				$prepare="INSERT INTO material.purorddt (compcode, recno, lineno_, pricecode, itemcode, uomcode, qtyorder, qtydelivered,perslstax, unitprice, taxcode,perdisc,amtdisc, amtslstax,amount,recstatus,remarks, adduser, adddate) VALUES ( ? ,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW() )";
					$arrayValue=[
						$company,
						$recno,
						$li,
						$row['pricecode'], 
						$row['itemcode'],  
						$row['uomcode'],
						$row['qtyrequest'], //qtyorder sama dgn qtyrequest dari PR
						0,
						$row['qtyrequest'], //qtyrequest-0 = qtyrequest
						$row['unitprice'],
						$row['taxcode'],
						$row['perdisc'],
						$row['amtdisc'],
						$row['amtslstax'],
						$row['amount'],
						'A',
						$row['remarks'],
						$this->user,
					];

				$this->save($prepare,$arrayValue);	

			}

			///3. calculate total amount from detail
			$amount="SELECT SUM(amount) AS AMOUNT FROM material.purorddt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE' ";

			$result = $this->db->query($amount);if(!$result){throw new Exception($this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);
			$totalAmount=($row['AMOUNT']);


			///4. then update to header
			$uptAmount="UPDATE material.purordhd SET totamount='$totalAmount', subamount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";

			$result = $this->db->query($sql);if(!$result){throw new Exception($this->db->errorInfo()[2]);}

			$this->total=$totalAmount;
			
		}

		public function save($prepare,$arrayValue){

			/////////////////check sytax//////////////////////////////
			//echo $prepare;print_r($arrayValue);
			//echo $this->readableSyntax($prepare,$arrayValue)."<br>";
			//////////////////////////////////////////////////////////

			$sth=$this->db->prepare($prepare);
			if (!$sth->execute($arrayValue)) {
				echo '{"msg":"failure"}<br>';
				throw new Exception($this->readableSyntax($prepare,$arrayValue));
			}else{
				// echo '{"msg":"success"}<br>';
			}
		}
		
		public function edit_table($commit){

			try{
				if($commit){
					$this->db->beginTransaction();
				}
					if($this->oper == 'add'){
						$purOrd_no = $this->purOrd_no('PO',$_POST['purordhd_prdept']);
						$recno = $this->recno('PUR','PO');
						$purreqno = $_POST['purordhd_purreqno'];

						$addarrField=['recno','purordno','compcode','adduser','adddate','upduser','upddate','recstatus'];//extra field
						$addarrValue=[$recno,$purOrd_no,$this->compcode,$this->user,'NOW()',$this->user,'NOW()','OPEN'];
					
						$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
						$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

						$this->save($prepare,$arrayValue);

						if(!empty($_POST['referral'])){
							$this->saveDetail($_POST['referral'],$recno);
						}

						$responce = new stdClass();
						$responce->purordno = $purOrd_no;
						$responce->recno = $recno;
						$responce->idno = $this->idno($purOrd_no,$recno);
						$responce->sql = $this->readableSyntax($prepare,$arrayValue);
						if(!empty($_POST['referral'])){
							$responce->totalAmount = $this->total;
						}
						echo json_encode($responce);

						$sql="UPDATE material.purreqhd
							SET purordno = '$purOrd_no'
							WHERE purreqno = '$purreqno' AND compcode = '{$this->compcode}'";

						$result = $this->db->query($sql);
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

					}else if($this->oper == 'edit'){

						// $updarrField=['compcode','upduser','upddate','recstatus'];//extra field
						// $updarrValue=[$this->compcode,$this->user,'NOW()','OPEN'];
						// $this->columnid = 'recno';

						// $prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,false);

						// $arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
						// array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);

						// $this->save($prepare,$arrayValue);

						// $responce = new stdClass();
						// $responce->sql = $this->readableSyntax($prepare,$arrayValue);
						// echo json_encode($responce);

						 $getPurreqhd = $this->toGetAllpurreqhd($_POST['purordhd_recno']);

						if($_POST['purordhd_purreqno'] == $getPurreqhd) {
							$updarrField=['compcode','upduser','upddate','recstatus'];//extra field
							$updarrValue=[$this->compcode,$this->user,'NOW()','OPEN'];
							$this->columnid = 'recno';

							$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,false);

							$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
							array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);

							$this->save($prepare,$arrayValue);

							$responce = new stdClass();
							$responce->sql = $this->readableSyntax($prepare,$arrayValue);
							echo json_encode($responce);



						}else if($_POST['purordhd_purreqno'] != $getPurreqhd) {

							//1. update purordno lama = 0
							$sql="UPDATE material.purreqhd
							SET purordno = '0'
							WHERE purreqno = '$getPurreqhd' AND compcode = '{$this->compcode}'";

							$result = $this->db->query($sql);
							if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

							/*$prepare = "UPDATE material.purreqhd SET purordno=? WHERE purreqno = ? AND compcode = ?";

							$arrayValue=[
											'0',
											//$_POST['purordhd_purreqno'],
											$getPurreqhd,
											$this->compcode,
										];

							//echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);*/

							// $responce = new stdClass();
							// $responce->sql = $this->readableSyntax($prepare,$arrayValue);
							// echo json_encode($responce);

							//2. Delete detail from purorddt

							$sql="DELETE FROM material.purorddt WHERE recno= '{$_POST['purordhd_recno']}'";

							$result = $this->db->query($sql);
							if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}



       //                      $prepare="DELETE FROM material.purorddt WHERE recno=?";
					

						 //   $arrayValue = [
						 //   $_POST['purordhd_recno'],

						 //   ];
       //                    // $this->save($prepare,$arrayValue);

						 //   $sth=$this->db->prepare($prepare);
							// $sth->execute($arrayValue);


							//3. Update purreqno_purordhd

							$updarrField=['compcode','upduser','upddate','recstatus'];//extra field
							$updarrValue=[$this->compcode,$this->user,'NOW()','OPEN'];
							$this->columnid = 'recno';

							$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,false);

							$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
							array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);

							$this->save($prepare,$arrayValue);

							$responce = new stdClass();
							$responce->sql = $this->readableSyntax($prepare,$arrayValue);
							echo json_encode($responce);

							//4. Update purorddt

							if(!empty($_POST['referral'])){
								$this->saveDetail($_POST['referral'], $_POST['purordhd_recno']);
							}

							$responce = new stdClass();
							$responce->purordno =  $_POST['purordhd_purordno'];
							$responce->recno =  $_POST['purordhd_recno'];
							$responce->idno = $this->idno($_POST['purordhd_purordno'], $_POST['purordhd_recno']);
							$responce->sql = $this->readableSyntax($prepare,$arrayValue);
							
							if(!empty($_POST['referral'])){
								$responce->totalAmount = $this->total;
							}
							// echo json_encode($responce);

							$sql="UPDATE material.purreqhd
								SET purordno = '{$_POST['purordhd_purordno']}', recstatus = 'POSTED' 
								WHERE purreqno = '{$_POST['purordhd_purreqno']}' AND compcode = '{$this->compcode}'";

							$result = $this->db->query($sql);
							if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

						}


					}else if($this->oper == 'posted'){

						$sql="UPDATE material.purordhd
							SET authpersonid = '{$this->user}',
								authdate = NOW(), 
								recstatus = 'POSTED' 
							WHERE recno = '{$_POST['recno']}' AND compcode = '{$this->compcode}'";

						$result = $this->db->query($sql);
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

						$sql="UPDATE material.purorddt 
							SET recstatus = 'POSTED' 
							WHERE recno = '{$_POST['recno']}' AND compcode = '{$this->compcode}' AND recstatus <> 'DELETE' ";

						$result = $this->db->query($sql);
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

					}else if($this->oper == 'cancel'){

						$sql="UPDATE material.purordhd 
							SET authpersonid = '{$this->user}',
								authdate = NOW(), 
								recstatus = 'CANCELLED' 
							WHERE recno = '{$_POST['recno']}' AND compcode = '{$this->compcode}'";

						$result = $this->db->query($sql);
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

						$sql="UPDATE material.purorddt 
							SET recstatus = 'CANCELLED' 
							WHERE recno = '{$_POST['recno']}' AND compcode = '{$this->compcode}' AND recstatus <> 'DELETE' ";

						$result = $this->db->query($sql);
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

					}else if($this->oper == 'reopen'){

						$sql="UPDATE material.purordhd 
							SET authpersonid = '{$this->user}',
								authdate = NOW(), 
								recstatus = 'OPEN' 
							WHERE recno = '{$_POST['recno']}' AND compcode = '{$this->compcode}'";

						$result = $this->db->query($sql);
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

						$sql="UPDATE material.purorddt 
							SET recstatus = 'OPEN' 
							WHERE recno = '{$_POST['recno']}' AND compcode = '{$this->compcode}' AND recstatus <> 'DELETE' ";

						$result = $this->db->query($sql);
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

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
