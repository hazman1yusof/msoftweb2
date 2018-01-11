<?php
	
	class invTranPost_save{
		var $oper;
		var $seldata;
		var $responce;
		var $sysparam;

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

		public function stockExpExist($deptcode,$itemcode,$uomcode,$expdate,$batchno){
	
			$query = "SELECT balqty FROM material.stockexp WHERE compcode='{$_SESSION['company']}' AND deptcode='{$deptcode}' AND itemcode = '{$itemcode}' AND uomcode = '{$uomcode}' AND expdate = '{$expdate}' AND batchno = '{$batchno}'";
			//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			return !empty($resultarr);
		}

		public function getbalqty($deptcode,$itemcode,$uomcode,$expdate,$batchno){

			$query="SELECT balqty FROM material.stockexp WHERE compcode='{$_SESSION['company']}' AND deptcode='{$deptcode}' AND itemcode = '{$itemcode}' AND uomcode = '{$uomcode}' AND expdate = '{$expdate}' AND batchno = '{$batchno}'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$row = $result->fetch(PDO::FETCH_ASSOC);
			
			$balqty=intval($row['balqty']);
			
			return $balqty;
		}

		public function stocklocExist($year,$itemcode,$uomcode,$deptcode){
	
			$query = "SELECT * FROM material.stockloc WHERE compcode='{$_SESSION['company']}' AND year = '{$year}'  AND itemcode = '{$itemcode}' AND uomcode = '{$uomcode}' AND deptcode='{$deptcode}'";
			//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			return !empty($resultarr);
		}

		public function getoldqtyonhand($year,$month,$itemcode,$uomcode,$deptcode){

			$query="SELECT qtyonhand,netmvqty$month,netmvval$month FROM material.stockloc WHERE compcode='{$_SESSION['company']}' AND year = '{$year}'  AND itemcode = '{$itemcode}' AND uomcode = '{$uomcode}' AND deptcode='{$deptcode}'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$row = $result->fetch(PDO::FETCH_NUM);
			
			return $row;
		}

		public function getCRDBFl($trantype){

			$query="SELECT crdbfl, isstype FROM material.ivtxntype WHERE compcode = '{$_SESSION['company']}' AND trantype = '$trantype'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC);
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
			try{
				if($commit){
					$this->db->beginTransaction();
				}

				$seldata = $this->seldata;
				
				if($this->oper=='add'){

					//$recno=$seldata['recno'];
					$cy = date('Y'); 
					$getTrandate = $seldata['trandate'];
					
					// 1) COPY 
						//a- ivtmphd --> ivtxnhd
						$prepare = "INSERT INTO material.ivtxnhd (compcode,recno,source,reference,txndept,trantype,docno,srcdocno,sndrcvtype,sndrcv,trandate,datesupret,dateactret,trantime,ivreqno,amount,respersonid,remarks,recstatus,adduser,adddate,upduser,upddate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

						$arrayValue = array($_SESSION['company'],$seldata['recno'],$seldata['source'],null,$seldata['txndept'],$seldata['trantype'],$seldata['srcdocno'],$seldata['srcdocno'],$seldata['sndrcvtype'],$seldata['sndrcv'],$seldata['trandate'],null,null,null,null,$seldata['amount'],null,null,$seldata['recstatus'],$seldata['adduser'],$seldata['adddate'],$seldata['upduser'],$seldata['upddate']);

						$this->save($prepare,$arrayValue);

				//		b- ivtmpdt --> ivtxndt

						$queryDetailInvTran = "SELECT ivt.compcode,ivt.recno, ivt.lineno_,ivt.itemcode,ivt.uomcode
							,p.description
							,s.qtyonhand
							,(SELECT s.qtyonhand
							FROM material.ivtmpdt ivt
							LEFT JOIN material.stockloc s ON ivt.itemcode = s.itemcode AND ivt.uomcode=s.uomcode 
							LEFT JOIN material.product p ON ivt.itemcode = p.itemcode 
							WHERE  ivt.recno = '{$seldata['recno']}' AND s.deptcode = '{$seldata['sndrcv']}' AND s.year = '$cy' AND ivt.compcode = '{$_SESSION['company']}' AND ivt.recstatus = 'A') AS recvqtyonhand  
							,s.maxqty
							,ivt.txnqty,ivt.netprice,ivt.amount,ivt.expdate,ivt.batchno,ivt.adduser,ivt.adddate, ivt.upduser, ivt.upddate 
							FROM material.ivtmpdt ivt
							LEFT JOIN material.stockloc s ON ivt.itemcode = s.itemcode AND ivt.uomcode=s.uomcode AND s.deptcode = '{$seldata['txndept']}' AND s.year = '$cy'
							LEFT JOIN material.product p ON ivt.itemcode = p.itemcode 
							WHERE  ivt.recno = '{$seldata['recno']}'  AND ivt.compcode = '{$_SESSION['company']}'  
							AND ivt.recstatus = 'A'";

							//echo $queryDetailInvTran; 

						$result = $this->db->query($queryDetailInvTran);if (!$result) { print_r($this->db->errorInfo()); }

						while($row = $result->fetch(PDO::FETCH_ASSOC)) {
							$compcode=($row['compcode']);
							$recno=($row['recno']);
							$lineno_=($row['lineno_']);
							$itemcode=($row['itemcode']);
							$uomcode=($row['uomcode']);
							$txnqty=($row['txnqty']);
							$netprice=($row['netprice']);
							$adduser=($row['adduser']);
							$adddate=($row['adddate']);
							$upduser=($row['upduser']);
							$upddate=($row['upddate']);
							$expdate=($row['expdate']);
							$qtyonhand=($row['qtyonhand']);
							$batchno=($row['batchno']);
							$amount=($row['amount']);


							$prepare = "INSERT INTO material.ivtxndt (compcode,recno,lineno_,itemcode,uomcode,txnqty,netprice,adduser,adddate,upduser,upddate,expdate,qtyonhand,batchno,amount,trandate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

							$arrayValue = array($_SESSION['company'],$recno, $lineno_,$itemcode,$uomcode,$txnqty,$netprice,$adduser,$adddate,$upduser,$upddate,$expdate,$qtyonhand,$batchno,$amount,$getTrandate);

							$this->save($prepare,$arrayValue);

							 

							$crdbfl = $this->getCRDBFl($seldata['trantype']);

							switch($crdbfl['crdbfl']){
								case 'In':
										//3)Stock Exp
										if($this->stockExpExist($seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno)){

											$bal = $this->getbalqty($seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno);

											$prepare = "UPDATE material.stockexp SET balqty = '$bal' + '$txnqty'  WHERE compcode=? AND deptcode = ? AND itemcode = ? AND uomcode =? AND expdate=? AND batchno=?";

											$arrayValue = array($_SESSION['company'],$seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno);

											$this->save($prepare,$arrayValue); 

										}else{///
											$prepare = "INSERT INTO material.stockexp (compcode,deptcode,itemcode,uomcode,expdate,batchno,balqty,adduser,adddate,addtime,upduser,upddate,updtime,lasttt,year) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

											$arrayValue = array($_SESSION['company'],$seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno,$txnqty,$adduser,$adddate,null,$upduser,$upddate,null,null,null);

											$this->save($prepare,$arrayValue);
										}

										//4)Stockloc
										if($this->stocklocExist($cy,$itemcode,$uomcode,$seldata['txndept'])){

											$trandateMonth = date("n",strtotime($seldata['trandate']));
											//$getMonth = $this->getDateValue($seldata['trandate']);

											$oldqtyonhand = $this->getoldqtyonhand($cy,$trandateMonth,$itemcode,$uomcode,$seldata['txndept'])[0];
											$oldnetqty = $this->getoldqtyonhand($cy,$trandateMonth,$itemcode,$uomcode,$seldata['txndept'])[1];
											$oldnetval = $this->getoldqtyonhand($cy,$trandateMonth,$itemcode,$uomcode,$seldata['txndept'])[2];

											$prepare = "UPDATE material.stockloc SET qtyonhand = '$oldqtyonhand' + '$txnqty', netmvqty$trandateMonth = '$oldnetqty' + '{$qtyonhand}', netmvval$trandateMonth = $oldnetval + '{$amount}' WHERE compcode=? AND year=? AND itemcode = ? AND uomcode =?  AND deptcode = ?";

											$arrayValue = array($_SESSION['company'],$cy,$itemcode,$uomcode,$seldata['txndept']);

											$this->save($prepare,$arrayValue);
										}else{
											//echo '{"msg":$seldata['txndept']+" not avaliable at stockloc"}<br>';
										}

										//4)Product
										$prepare = "UPDATE material.product SET qtyonhand =   '$oldqtyonhand' + '$txnqty' WHERE compcode=? AND itemcode = ? AND uomcode =?";

										$arrayValue = array($_SESSION['company'],$itemcode,$uomcode
									);

										$this->save($prepare,$arrayValue);	
								break;
								case 'Out':
									//$oldTxnQty = -$txnqty;
									//echo $oldTxnQty;

									if($crdbfl['isstype'] == 'Transfer'){
										$trandateMonth = date("n",strtotime($seldata['trandate']));

										$oldqtyonhand = $this->getoldqtyonhand($cy,$trandateMonth,$itemcode,$uomcode,$seldata['txndept'])[0];
										//******FOR TRANSFER DEPT (-)
										//3) StockLoc 

										$prepare = "UPDATE material.stockloc SET qtyonhand = '$oldqtyonhand' - '$txnqty' WHERE compcode=? AND year=? AND itemcode = ? AND uomcode =?  AND deptcode = ?";

										$arrayValue = array($_SESSION['company'],$cy,$itemcode,$uomcode,$seldata['txndept']);

											$this->save($prepare,$arrayValue);


										//4)Product
										$prepare = "UPDATE material.product SET qtyonhand = '$oldqtyonhand' - '$txnqty' WHERE compcode=? AND itemcode = ? AND uomcode =?";

										$arrayValue = array($_SESSION['company'],$itemcode,$uomcode
									);

										$this->save($prepare,$arrayValue);	
									

										//******FOR TRANSFER DEPT (+)
										//3) StockLoc 

										$prepare = "UPDATE materil.stockloc SET qtyonhand = '$oldqtyonhand' + '$txnqty' WHERE compcode=? AND year=? AND itemcode = ? AND uomcode =?  AND deptcode = ?";

										$arrayValue = array($_SESSION['company'],$cy,$itemcode,$uomcode,$seldata['sndrcv']);

											$this->save($prepare,$arrayValue);


										//4)Product
										$prepare = "UPDATE material.product SET qtyonhand = '$oldqtyonhand' - '$txnqty' WHERE compcode=? AND itemcode = ? AND uomcode =?";

										$arrayValue = array($_SESSION['company'],$itemcode,$uomcode
									);

										$this->save($prepare,$arrayValue);	


									}else if ($crdbfl['isstype'] == 'Others') {
										//3)Stock Exp
										if($this->stockExpExist($seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno)){

											$bal = $this->getbalqty($seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno);

											$prepare = "UPDATE material.stockexp SET balqty = '$bal' - '$txnqty'  WHERE compcode=? AND deptcode = ? AND itemcode = ? AND uomcode =? AND expdate=? AND batchno=?";

											$arrayValue = array($_SESSION['company'],$seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno);

											$this->save($prepare,$arrayValue); 

										}else{///
											$prepare = "INSERT INTO material.stockexp (compcode,deptcode,itemcode,uomcode,expdate,batchno,balqty,adduser,adddate,addtime,upduser,upddate,updtime,lasttt,year) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

											$arrayValue = array($_SESSION['company'],$seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno,$txnqty,$adduser,$adddate,null,$upduser,$upddate,null,null,null);

											$this->save($prepare,$arrayValue);
										}

										//4)Stockloc
										if($this->stocklocExist($cy,$itemcode,$uomcode,$seldata['txndept'])){

											$trandateMonth = date("n",strtotime($seldata['trandate']));
											//$getMonth = $this->getDateValue($seldata['trandate']);

											$oldqtyonhand = $this->getoldqtyonhand($cy,$trandateMonth,$itemcode,$uomcode,$seldata['txndept'])[0];
											$oldnetqty = $this->getoldqtyonhand($cy,$trandateMonth,$itemcode,$uomcode,$seldata['txndept'])[1];
											$oldnetval = $this->getoldqtyonhand($cy,$trandateMonth,$itemcode,$uomcode,$seldata['txndept'])[2];

											$prepare = "UPDATE material.stockloc SET qtyonhand = '$oldqtyonhand' - '$txnqty', netmvqty$trandateMonth = '$oldnetqty' + '{$qtyonhand}', netmvval$trandateMonth = $oldnetval + '{$amount}' WHERE compcode=? AND year=? AND itemcode = ? AND uomcode =?  AND deptcode = ?";

											$arrayValue = array($_SESSION['company'],$cy,$itemcode,$uomcode,$seldata['txndept']);

											$this->save($prepare,$arrayValue);
										}else{
											//echo '{"msg":$seldata['txndept']+" not avaliable at stockloc"}<br>';
										}

										//4)Product
										$prepare = "UPDATE material.product SET qtyonhand =   '$oldqtyonhand' - '$txnqty' WHERE compcode=? AND itemcode = ? AND uomcode =?";

										$arrayValue = array($_SESSION['company'],$itemcode,$uomcode
									);

										$this->save($prepare,$arrayValue);
									}else {
										
										
									}

									
									
								break;
								default:
								break;
							}

							

						}	
					
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
