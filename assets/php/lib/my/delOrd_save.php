<?php
	class delOrd_save extends EditTable {

		var $oper;
		var $seldata;
		var $total;
		var $srcdocno;
		var $delordno;
		var $compcode;
		var $responce;
		var $sysparam;
		var $gltranAmount;


		// public function __construct(PDO $db){
		// 	include_once('sschecker.php');
		// 	$this->db = $db;
		// 	$this->oper = $_GET['oper'];
		// 	//$this->seldata = $_POST['seldata'];
		// 	$this->compcode = $_SESSION['company'];

		// }

		// public function readableSyntax($prepare,array $arrayValue){
		// 	foreach($arrayValue as $val){
		// 		$prepare=preg_replace("/\?/", "'".$val."'", $prepare,1);
		// 	}
		// 	return $prepare."\r\n";
		// }

		public function isGltranExist($ccode,$glcode,$year,$period){
			$query = "select glaccount,actamount".$period." from finance.glmasdtl where compcode='{$this->compcode}' and year='{$year}' and costcode = '{$ccode}' and glaccount = '{$glcode}'";
			echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			$this->gltranAmount = $resultarr["actamount".$period];
			return !empty($resultarr);
		}


		public function request_no($trantype,$dept){
			$sql="SELECT seqno FROM material.sequence WHERE trantype = '$trantype' AND dept = '$dept'";
			$result = $this->db->query($sql);if(!$result){throw new Exception("request_no: ".$this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$request_no = intval($row['seqno']);

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
			$sql="SELECT idno FROM material.delordhd WHERE docno = '$request_no' AND recno = '$recno'";
			// echo($sql);
			$result = $this->db->query($sql);if(!$result){throw new Exception("idno: ".$this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);
			
			return $row['idno'];
		}

		 public function getProduct($itemcode, $uomcode, $groupcode){

			$query = "SELECT itemcode,uomcode, groupcode from material.product where compcode='{$_SESSION['company']}' and itemcode='{$itemcode}' and uomcode='{$uomcode}' and groupcode='{$groupcode}'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC);
		}	

		public function ivtxnhdExist($recno){
			
					$query = "SELECT * FROM material.ivtxnhd WHERE compcode='{$_SESSION['company']}' AND recno = '{$recno}'";
					//echo $query;
					$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

					$resultarr = $result->fetch(PDO::FETCH_ASSOC);
					return !empty($resultarr);
				}

		public function getIvtxnhd($recno){

			$query = "SELECT recno from material.ivtxnhd where compcode='{$_SESSION['company']}' and recno='{$recno}'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC);
		}

		public function getTrantype(){

			$query = "SELECT trantype from material.ivtxntype where compcode='{$_SESSION['company']}' and trantype='DO'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			//$arr = $result->fetch(PDO::FETCH_ASSOC);

			return $result->fetchColumn(0);
		}

		public function getProductStock($itemcode, $uomcode, $groupcode){
			$query = "SELECT itemcode, uomcode, groupcode from material.product WHERE compcode='{$_SESSION['company']}' and itemcode='{$itemcode}' and uomcode='{$uomcode}' and groupcode='Stock'";
			//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			return !empty($resultarr);
		}

		public function getStockloc($deptcode, $itemcode, $uomcode,$year){
			$query = "SELECT * from material.stockloc WHERE compcode='{$_SESSION['company']}' and deptcode='{$deptcode}' and itemcode='{$itemcode}' and uomcode='{$uomcode}' and year='{$year}'";
			//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			return !empty($resultarr);
		}


		public function getdeptcodebyrecno($recno){

			$query = "SELECT deldept from material.delordhd where compcode='{$_SESSION['company']}' and recno='{$recno}'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			//$arr = $result->fetch(PDO::FETCH_ASSOC);

			return $result->fetchColumn(0);
		}

		public function getoldqtyonhand($year,$month,$itemcode,$uomcode,$deptcode){

			$query="SELECT qtyonhand,netmvqty$month,netmvval$month FROM material.stockloc WHERE compcode='{$_SESSION['company']}' AND year = '{$year}'  AND itemcode = '{$itemcode}' AND uomcode = '{$uomcode}' AND deptcode='{$deptcode}'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$row = $result->fetch(PDO::FETCH_NUM);
			
			return $row;
		}

		public function stocklocExist($year,$itemcode,$uomcode,$deptcode){
	
			$query = "SELECT * FROM material.stockloc WHERE compcode='{$_SESSION['company']}' AND year = '{$year}'  AND itemcode = '{$itemcode}' AND uomcode = '{$uomcode}' AND deptcode='{$deptcode}'";
			//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			return !empty($resultarr);
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
		


		public function toGetAllpurordhd($recno){
			$sql="SELECT srcdocno FROM material.delordhd WHERE compcode='{$_SESSION['company']}' AND recno = '{$recno}'";
			// echo("$sql");
			$result = $this->db->query($sql);if(!$result){throw new Exception("idno: ".$this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);

			$srcdocno = intval($row['srcdocno']);

			return $srcdocno;

		}

		public function chgDate($date){
			if(!empty($date)){
				$newstr=explode("/", $date);
				return $newstr[2].'-'.$newstr[1].'-'.$newstr[0];
			}else{
				return 'NULL';
			}
		}

		public function saveDetail($refer_recno,$recno){

			$company = $this->compcode;

			$sql = "SELECT compcode, recno, lineno_, pricecode, itemcode, uomcode, qtyorder, qtydelivered, unitprice, taxcode,perdisc,amtdisc, amtslstax,amount,recstatus,remarks FROM material.purorddt WHERE recno = '{$refer_recno}' AND compcode = '{$company}' AND recstatus <> 'DELETE'";
			$result = $this->db->query($sql);if(!$result) { throw new Exception(print_r($this->db->errorInfo())); }


			while($row = $result->fetch(PDO::FETCH_ASSOC)) {
				////1. calculate lineno_ by recno
				$sqlln = "SELECT COUNT(lineno_) as COUNT from material.delorddt WHERE compcode='$company' AND recno='$recno'";

				$resultln = $this->db->query($sqlln);if(!$resultln) { print_r($this->db->errorInfo()); }
				$rowln = $resultln->fetch(PDO::FETCH_ASSOC);

				$li=intval($rowln['COUNT'])+1;

				///2. insert detail
				$prepare="INSERT INTO material.delorddt (compcode, recno, lineno_, pricecode, itemcode, uomcode, qtytag, qtyorder, qtydelivered, unitprice, taxcode, perdisc, amtdisc, amtslstax, amount, adduser, adddate, recstatus, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
					$arrayValue=[
						$company,
						$recno,
						$li,
						$row['pricecode'], 
						$row['itemcode'], 
						$row['uomcode'],
						//$row['pouom'],
						0,
						$row['qtyorder'],
						$row['qtydelivered'],
						$row['unitprice'],
						$row['taxcode'],
						$row['perdisc'],
						$row['amtdisc'],
						$row['amtslstax'],
						$row['amount'],
						$this->user,
						//$this->chgDate($row['expdate']),
						//$row['batchno'],
						'A',
						$row['remarks'] 
					];

				$this->save($prepare,$arrayValue);	

			}

			///3. calculate total amount from detail
			$amount="SELECT SUM(amount) AS AMOUNT FROM material.delorddt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE' ";

			$result = $this->db->query($amount);if(!$result){throw new Exception($this->db->errorInfo()[2]);}
			$row = $result->fetch(PDO::FETCH_ASSOC);
			$totalAmount=($row['AMOUNT']);


			///4. then update to header
			$uptAmount="UPDATE material.delordhd SET totamount='$totalAmount', subamount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";

			$result = $this->db->query($sql);if(!$result){throw new Exception($this->db->errorInfo()[2]);}

			$this->total=$totalAmount;
			
		}
/*
		function sql_debug($sql_string, array $params = null) {
			    if (!empty($params)) {
			        $indexed = $params == array_values($params);
			        foreach($params as $k=>$v) {
			            if (is_object($v)) {
			                if ($v instanceof \DateTime) $v = $v->format('Y-m-d H:i:s');
			                else continue;
			            }
			            elseif (is_string($v)) $v="'$v'";
			            elseif ($v === null) $v='NULL';
			            elseif (is_array($v)) $v = implode(',', $v);

			            if ($indexed) {
			                $sql_string = preg_replace('/\?/', $v, $sql_string, 1);
			            }
			            else {
			                if ($k[0] != ':') $k = ':'.$k; //add leading colon if it was left out
			                $sql_string = str_replace($k,$v,$sql_string);
			            }
			        }
			    }
			    return $sql_string;
			}*/

		public function save($prepare,$arrayValue){

			/////////////////check syntax//////////////////////////////
			//echo $prepare;print_r($arrayValue);
			//echo $this->readableSyntax($prepare,$arrayValue)."<br>";
			//////////////////////////////////////////////////////////

			$sth=$this->db->prepare($prepare);
			// if (!$sth->execute($arrayValue)) {
			// 	//echo '{"msg":"failure"}<br>';
			// 	throw new Exception($this->readableSyntax($prepare,$arrayValue));
			// }else{
			// 	echo '{"msg":"success"}<br>';
			// }
			//print_r($arrayValue);
			$result = $sth->execute($arrayValue) or die(print_r($sth->errorInfo(), true));
			// echo $result;

			// $sth=$this->db->prepare($prepare);
			// if (!$sth->execute($arrayValue)) {
			// 	echo '{"msg":"failure"}<br>';
			// 	throw new Exception($this->readableSyntax($prepare,$arrayValue));
			// }else{
			// 	// echo '{"msg":"success"}<br>';
			// }


		}
		
		public function edit_table($commit){
				//$seldata = $this->seldata;
				//$this->seldata = $_POST['seldata'];
			try{
				if($commit){
					$this->db->beginTransaction();
				}

					if($this->oper == 'add'){


						$request_no = $this->request_no('GRN', $_POST['delordhd_deldept']);
						$recno = $this->recno('PUR','DO');
						$srcdocno = $_POST['delordhd_srcdocno'];
						$delordno = $_POST['delordhd_delordno'];


						$addarrField=['trantype','docno','recno','compcode','adduser','adddate','recstatus'];//extra field
						$addarrValue=['GRN',$request_no,$recno,$this->compcode,$this->user,'NOW()','OPEN'];
					
						$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
						$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

						$this->save($prepare,$arrayValue);

						if(!empty($_POST['referral'])){
							$this->saveDetail($_POST['referral'],$recno);
						}

						$responce = new stdClass();
						$responce->docno = $request_no;
						$responce->recno = $recno;
						$responce->idno = $this->idno($request_no,$recno);
						$responce->sql = $this->readableSyntax($prepare,$arrayValue);
						if(!empty($_POST['referral'])){
							$responce->totalAmount = $this->total;
						}
						echo json_encode($responce);

						$sql="UPDATE material.purordhd
							SET delordno = '$delordno'
							WHERE purordno = '$srcdocno' AND compcode = '{$this->compcode}'";

						$result = $this->db->query($sql);
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

					}else if($this->oper == 'edit'){
					/*	$updarrField=['compcode','upduser','upddate','recstatus'];//extra field
						$updarrValue=[$this->compcode,$this->user,'NOW()','OPEN'];
						$this->columnid = 'idno';

						$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,false);

						$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
						array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);

						$this->save($prepare,$arrayValue);

						$responce = new stdClass();
						$responce->sql = $this->readableSyntax($prepare,$arrayValue);
						echo json_encode($responce);*/

						$getPurOrdhd = $this->toGetAllpurordhd($_POST['delordhd_recno']);

						if($_POST['delordhd_srcdocno'] == $getPurOrdhd) {
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



						}else if($_POST['delordhd_srcdocno'] != $getPurOrdhd) {

							//1. update delordno lama = 0
							$sql="UPDATE material.purordhd
							SET purordno = '0'
							WHERE recno = '$getPurOrdhd' AND compcode = '{$this->compcode}'";

							$result = $this->db->query($sql);
							if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}


							//2. Delete detail from delorddt

							$sql="DELETE FROM material.delorddt WHERE recno= '{$_POST['delordhd_recno']}'";

							$result = $this->db->query($sql);
							if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}


							//3. Update srcdocno_delordhd

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

							//4. Update delorddt

							if(!empty($_POST['referral'])){
								$this->saveDetail($_POST['referral'], $_POST['delordhd_recno']);
							}

							$responce = new stdClass();
							$responce->docno = $_POST['delordhd_delordno'];
							$responce->recno = $_POST['delordhd_recno'];
							$responce->idno = $this->idno($_POST['delordhd_docno'], $_POST['delordhd_recno']);
							$responce->sql = $this->readableSyntax($prepare,$arrayValue);
							if(!empty($_POST['referral'])){
								$responce->totalAmount = $this->total;
							}
							// echo json_encode($responce);

							$sql="UPDATE material.purordhd
								SET delordno = '{$_POST['delordhd_delordno']}', recstatus = 'POSTED' 
								WHERE purordno = '{$_POST['delordhd_srcdocno']}' AND compcode = '{$this->compcode}'";

							$result = $this->db->query($sql);
							if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

						}

					} else if($this->oper == 'posted'){

						$recno = $_POST['recno']; 
						
						//$seldata['delordhd_trandate'];
						// echo 'recno=' . $recno;

						$prepare = "INSERT INTO material.ivtxnhd (compcode, recno, reference, source, txndept, trantype, docno, srcdocno, sndrcv, sndrcvtype, trandate, trantime, datesupret, respersonid, recstatus, adduser, adddate, remarks) 
									(SELECT compcode, recno, delordno, 'IV', deldept, trantype, docno, srcdocno, suppcode, 'Supplier', trandate, trantime, deliverydate, checkpersonid,'OPEN', adduser, NOW(), remarks 
									FROM material.delordhd WHERE recno=:recno and compcode=:compcode)
									ON DUPLICATE KEY UPDATE 
									material.ivtxnhd.compcode=material.delordhd.compcode, 
									material.ivtxnhd.recno=material.delordhd.recno, 
									material.ivtxnhd.reference=material.delordhd.delordno, 
									material.ivtxnhd.source='IV', 
									material.ivtxnhd.txndept=material.delordhd.deldept, 
									material.ivtxnhd.trantype=material.delordhd.trantype, 
									material.ivtxnhd.docno=material.delordhd.docno, 
									material.ivtxnhd.srcdocno=material.delordhd.srcdocno, 
									material.ivtxnhd.sndrcv=material.delordhd.suppcode, 
									material.ivtxnhd.sndrcvtype='Supplier', 
									material.ivtxnhd.trandate=material.delordhd.trandate, 
									material.ivtxnhd.trantime=material.delordhd.trantime, 
									material.ivtxnhd.datesupret=material.delordhd.deliverydate, 
									material.ivtxnhd.respersonid=material.delordhd.checkpersonid, 
									material.ivtxnhd.recstatus=material.delordhd.recstatus, 
									material.ivtxnhd.adduser=material.delordhd.adduser, 
									material.ivtxnhd.ADDDATE=NOW(), 
									material.ivtxnhd.remarks=material.delordhd.remarks";

						$arrayValue = array("recno"=>$_POST['recno'], "compcode"=>$_SESSION['company']);//, "seqno2"=>$seqno);
						$this->save($prepare, $arrayValue);

						$sql="SELECT dt.itemcode, dt.uomcode, p.groupcode, p.productcat FROM material.delorddt dt LEFT JOIN material.product p
						ON dt.itemcode=p.itemcode AND dt.uomcode=p.uomcode WHERE dt.compcode = '{$this->compcode}' and p.groupcode='Stock' and dt.recno='".$recno."'";

						$result = $this->db->query($sql);
						$row = $result->fetch(PDO::FETCH_ASSOC);
						$productcat_ = $row['productcat'];
						//echo $sql;
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}


						$recno = $_POST['recno'];

						$query="SELECT * FROM material.delorddt WHERE recno={$recno} and compcode='{$_SESSION['company']}' and recstatus != 'DELETE'";
						$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

						while($row = $result->fetch(PDO::FETCH_ASSOC)) {
							$lineno_= $row['lineno_'];
							$qtydelivered= $row['qtydelivered'];
							$deldept=$row['deldept'];

							$convfactorPOUOM="SELECT dt.itemcode, dt.pouom, u.convfactor FROM material.delorddt dt LEFT JOIN material.uom u ON dt.pouom=u.uomcode WHERE dt.compcode = '{$this->compcode}' and dt.recno='".$recno."' and dt.lineno_='".$lineno_."'";

							$convfactorPOUOM_result = $this->db->query($convfactorPOUOM);
							if(!$convfactorPOUOM_result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
							$convfactorPOUOM_row = $convfactorPOUOM_result->fetch(PDO::FETCH_ASSOC);
							$convfactorPOUOM=($convfactorPOUOM_row['convfactor']);

							$convfactorUOM="SELECT dt.itemcode, dt.uomcode, u.convfactor FROM material.delorddt dt LEFT JOIN material.uom u ON dt.uomcode=u.uomcode WHERE dt.compcode = '{$this->compcode}' and dt.recno='".$recno."' and dt.lineno_='".$lineno_."'";

							$convfactorUOM_result = $this->db->query($convfactorUOM);
							if(!$convfactorUOM_result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
							$convfactorUOM_row = $convfactorUOM_result->fetch(PDO::FETCH_ASSOC);
							$convfactorUOM=($convfactorUOM_row['convfactor']);

							$txnqty = $qtydelivered * ($convfactorPOUOM / $convfactorUOM);
							$netprice = ($row['netunitprice']) * ($convfactorUOM / $convfactorPOUOM);

							$prepare = "INSERT INTO material.ivtxndt (compcode, recno, lineno_, itemcode, uomcode, txnqty, netprice, adduser, adddate, upduser, upddate, productcat, draccno, drccode, craccno, crccode, updtime, expdate, remarks, qtyonhand, batchno, amount, trandate, deptcode, gstamount, totamount) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?,?,?,?,?)";
							$arrayValue = array(
									$row['compcode'],
									$row['recno'],
									$row['lineno_'],
									$row['itemcode'],
									$row['uomcode'],
									$txnqty,
									$netprice,
									$row['adduser'],
									$row['adddate'],
									$row['upduser'],
									$row['upddate'],
									$productcat_,
									$row['draccno'],
									$row['drccode'],
									$row['craccno'],
									$row['crccode'],
									$row['expdate'],
									$row['remarks'],
									0,
									$row['batchno'],
									$row['amount'],
									$row['trandate'],
									$deldept,
									$row['amtslstax'],
									$row['totamount']);


							echo $this->readableSyntax($prepare,$arrayValue);
							$this->save($prepare, $arrayValue);


    						/******************Posting to Stock Location*********************/
    						$query_sloc = "SELECT * FROM material.StockLoc WHERE 
								StockLoc.CompCode = '{$_SESSION['company']}' AND
								StockLoc.DeptCode = '{$deldept}' AND
								StockLoc.ItemCode = '{$row['itemcode']}' AND
								StockLoc.Year     = YEAR('{$row['trandate']}') AND
								StockLoc.UomCode  = '{$row['uomcode']}'";

							// echo $query_sloc;

							$result_sloc = $this->db->query($query_sloc);if (!$result_sloc) { print_r($this->db->errorInfo()); }
							$resultarr_sloc = $result_sloc->fetch(PDO::FETCH_ASSOC);

							if(!empty($resultarr_sloc)){

           						$month = date("m",strtotime($row['trandate']));
           						$month = (int)$month;
           						$QtyOnHand = $resultarr_sloc['qtyonhand'] + $txnqty; 
           						$NetMvQty = $resultarr_sloc['netmvqty'.$month] + $txnqty;
           						$NetMvVal = $resultarr_sloc['netmvval'.$month] + $netprice;


								$query_sloc="UPDATE material.StockLoc
									SET QtyOnHand = '{$QtyOnHand}',
								 		NetMvQty{$month} = '{$NetMvQty}', 
										NetMvVal{$month} = '{$NetMvVal}'
									WHERE 
										StockLoc.CompCode = '{$_SESSION['company']}' AND
										StockLoc.DeptCode = '{$deldept}' AND
										StockLoc.ItemCode = '{$row['itemcode']}' AND
										StockLoc.Year     = YEAR('{$row['trandate']}') AND
										StockLoc.UomCode  = '{$row['uomcode']}'";
								// echo $query_sloc;
								$result_sloc = $this->db->query($query_sloc);
								if(!$result_sloc){throw new Exception("error: ".$this->db->errorInfo()[2]);}

							}else{
								/////////////////create stock loc baru/////////////
							}

							/******************Posting to Stock Expiry*********************/
    						$query_stockexp = "SELECT * FROM material.stockexp WHERE 
								stockexp.compcode = '{$_SESSION['company']}' AND
								stockexp.deptcode = '{$deldept}' AND
								stockexp.itemcode = '{$row['itemcode']}' AND
								stockexp.expdate = '{$row['expdate']}' AND
								stockexp.year = YEAR('{$row['trandate']}') AND
								stockexp.uomcode  = '{$row['uomcode']}'";

							// echo $query_stockexp;

							$result_stockexp = $this->db->query($query_stockexp);if (!$result_stockexp) { print_r($this->db->errorInfo()); }
							$resultarr_stockexp = $result_stockexp->fetch(PDO::FETCH_ASSOC);

							if(!empty($resultarr_stockexp)){

           						$month = date("m",strtotime($row['trandate']));
           						$BalQty = $resultarr_stockexp['balqty'] + $txnqty; 

								$query_stockexp="UPDATE material.stockexp
									SET balqty = '{$BalQty}'
									WHERE 
										stockexp.compcode = '{$_SESSION['company']}' AND
										stockexp.deptcode = '{$deldept}' AND
										stockexp.itemcode = '{$row['itemcode']}' AND
										stockexp.expdate = '{$row['expdate']}' AND
										stockexp.uomcode = '{$row['uomcode']}' AND
										stockexp.batchno = '{$row['batchno']}' AND
										stockexp.lasttt = 'GRN' AND
										stockexp.year = YEAR('{$row['trandate']}') ";

								// echo $query_stockexp;
								$result_stockexp = $this->db->query($query_stockexp);
								if(!$result_stockexp){throw new Exception("error: ".$this->db->errorInfo()[2]);}

							} else {
								/////////////////create stock exp baru/////////////
								$month = date("m",strtotime($row['trandate']));
           						$BalQty = $resultarr_stockexp['balqty'] + $txnqty; 

								$prepare="INSERT INTO material.stockexp
									 (compcode, deptcode, itemcode, uomcode, expdate, batchno, balqty, adduser, adddate, addtime, upduser, upddate, updtime, lasttt, year) VALUES 
									 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
								$arrayValue = array(
										$row['compcode'],
										$deldept,
										$row['itemcode'],
										$row['uomcode'],
										$row['expdate'],
										$row['batchno'],
										$BalQty,
										$row['adduser'],
										$row['adddate'],
										null,
										$row['upduser'],
										$row['upddate'],
										$row['updtime'],
										'GRN',
										$row['trandate']);

								// echo $this->readableSyntax($prepare,$arrayValue);
								$this->save($prepare, $arrayValue);
								
							}


							/******************Posting to Product*********************/
							$query_product = "SELECT * FROM material.product WHERE 
								product.compcode = '{$_SESSION['company']}' AND
								product.itemcode = '{$row['itemcode']}' AND
								product.uomcode = '{$row['uomcode']}'";

							// echo $query_product;

							$result_product = $this->db->query($query_product);if (!$result_product) { print_r($this->db->errorInfo()); }
							$resultarr_product = $result_product->fetch(PDO::FETCH_ASSOC);

							if(!empty($resultarr_product)){

           						$month = date("m",strtotime($row['trandate']));
           						$OldQtyOnHand = $resultarr_product['qtyonhand'];
           						$Oldavgcost = $resultarr_product['avgcost'];
           						$OldAmount = $OldQtyOnHand * $Oldavgcost;
           						$NewAmount = $netprice * $txnqty; 

           						$newqtyonhand = $OldQtyOnHand + $txnqty;
           						$newAvgCost = ($OldAmount + $NewAmount) / ($OldQtyOnHand + $txnqty);



								$query_product="UPDATE material.product
									SET qtyonhand = '{$newqtyonhand}',
								 		avgcost = '{$newAvgCost}'
									WHERE 
										product.CompCode = '{$_SESSION['company']}' AND
										product.itemcode = '{$row['itemcode']}' AND
										product.uomcode = '{$row['uomcode']}'";

								// echo $query_product;
								$result_product = $this->db->query($query_product);
								if(!$result_product){throw new Exception("error: ".$this->db->errorInfo()[2]);}

							}

							/******************Posting to GL*********************/

							// 1. insert to gltran //
							$sql="SELECT * from material.ivtxnhd where recno = '".$recno."'";
							$result = $this->db->query($sql);
							if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
							$row2 = $result->fetch(PDO::FETCH_ASSOC);

							$yearperiod = $this->getyearperiod($row2['trandate']);

							$sql="SELECT * from sysdb.department 
								WHERE 
									compcode = '{$_SESSION['company']}' AND 
									deptcode = '{$row2['txndept']}'";
							$result = $this->db->query($sql);
							if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
							$row_dept = $result->fetch(PDO::FETCH_ASSOC);


							$sql="SELECT * from material.category 
								WHERE 
									compcode = '{$_SESSION['company']}' AND 
									catcode = '{$productcat_}'";
							$result = $this->db->query($sql);
							if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
							$row_cat = $result->fetch(PDO::FETCH_ASSOC);

							$sql="SELECT * from sysdb.sysparam 
								WHERE 
									compcode = '{$_SESSION['company']}' AND 
									source = 'AP' AND 
									trantype = 'ACC'";
							$result = $this->db->query($sql);
							if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
							$row_sysparam = $result->fetch(PDO::FETCH_ASSOC);

							$prepare="INSERT INTO finance.gltran
								 (compcode,adduser,adddate,auditno,lineno_,source,trantype,reference,description,postdate,year,period,drcostcode,dracc,crcostcode,cracc,amount,idno) VALUES 
								 (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
							$arrayValue = array(
									$row['compcode'],
									$row['adduser'],
									$row['recno'],
									$row['lineno_'],
									'IV',
									'GRN',
									$row2['txndept'].' '.$row2['docno'],
									$row2['sndrcv'],
									$row2['trandate'],
									$yearperiod->year,
									$yearperiod->period,
									$row_dept['costcode'],
									$row_cat['stockacct'],
									$row_sysparam['pvalue1'],
									$row_sysparam['pvalue2'],
									$row['amount'],
									$row['itemcode'],
									);

							echo $this->readableSyntax($prepare,$arrayValue);
							$this->save($prepare, $arrayValue);


							// 2. insert to glmasdtl //
							if($this->isGltranExist($row_dept['costcode'],$row_cat['stockacct'],$yearperiod->year,$yearperiod->period)){
								$this->oper = 'edit';
								$this->table = 'finance.glmasdtl';
								// $this->yearperiod = null;
								$this->column = ['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];
								$this->columnid = null;
								$this->filterCol = ['costcode','glaccount','year'];
								$this->filterVal = [$row_dept['costcode'],$row_cat['stockacct'],$yearperiod->year];

								$updarrField=['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];//extra field
								$updarrValue=[$this->compcode,$this->user,'NOW()',$row['amount']+$this->gltranAmount,'A'];
								
								$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,!empty($this->filterCol));
								$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
								if(!empty($this->columnid)){
									array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);
								}else{
									array_push($arrayValue,$this->compcode);
								}
								$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;

								$this->save($prepare,$arrayValue);
							}else{
								$this->oper = 'add';
								$this->table = 'finance.glmasdtl';
								// $this->yearperiod = null;
								$this->column = ['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];
								$this->columnid = 'compcode';

								$addarrField=['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];//extra field
								$addarrValue=[$this->compcode,$row_dept['costcode'],$row_cat['stockacct'],$yearperiod->year,$row['amount'],$this->user,'NOW()','A'];
							
								$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
								$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

								$this->save($prepare,$arrayValue);
							}

							if($this->isGltranExist($row_sysparam['pvalue1'],$row_sysparam['pvalue2'],$yearperiod->year,$yearperiod->period)){
								$this->oper = 'edit';
								$this->table = 'finance.glmasdtl';
								// $this->yearperiod = null;
								$this->column = ['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];
								$this->columnid = null;
								$this->filterCol = ['costcode','glaccount','year'];
								$this->filterVal = [$row_sysparam['pvalue1'],$row_sysparam['pvalue2'],$yearperiod->year];

								$updarrField=['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];//extra field
								$updarrValue=[$this->compcode,$this->user,'NOW()',$this->gltranAmount-$row['amount'],'A'];
								
								$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,!empty($this->filterCol));
								$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
								if(!empty($this->columnid)){
									array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);
								}else{
									array_push($arrayValue,$this->compcode);
								}
								$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;

								$this->save($prepare,$arrayValue);
							}else{
								$this->oper = 'add';
								$this->table = 'finance.glmasdtl';
								// $this->yearperiod = null;
								$this->column = ['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];
								$this->columnid = 'compcode';

								$addarrField=['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];//extra field
								$addarrValue=[$this->compcode,$row_sysparam['pvalue1'],$row_sysparam['pvalue2'],$yearperiod->year,-$row['amount'],$this->user,'NOW()','A'];
							
								$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
								$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

								$this->save($prepare,$arrayValue);
							}

							//gltran utk gst
							if($row['amtslstax']>0){

								$sql="SELECT * from sysdb.sysparam 
									WHERE 
										compcode = '{$_SESSION['company']}' AND 
										source = 'GST' AND 
										trantype = 'BS'";
								$result = $this->db->query($sql);
								if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
								$queryGSTBS = $result->fetch(PDO::FETCH_ASSOC);

								$sql="SELECT * from sysdb.sysparam 
									WHERE 
										compcode = '{$_SESSION['company']}' AND 
										source = 'GST' AND 
										trantype = 'PL'";
								$result = $this->db->query($sql);
								if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
								$queryGSTPL = $result->fetch(PDO::FETCH_ASSOC);

								$sql="SELECT * from sysdb.sysparam 
									WHERE 
										compcode = '{$_SESSION['company']}' AND 
										source = 'AP' AND 
										trantype = 'ACC'";
								$result = $this->db->query($sql);
								if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
								$queryACC = $result->fetch(PDO::FETCH_ASSOC);

								$sql="SELECT * from material.supplier 
									WHERE 
										compcode = '{$_SESSION['company']}' AND 
										suppcode = '{$row2['sndrcv']}'";
								$result = $this->db->query($sql);
								if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
								$querysupp = $result->fetch(PDO::FETCH_ASSOC);

								if($querysupp['GSTID'] == ''){
									$drcostcode_ = $queryGSTPL['pvalue1'];
									$dracc_ = $queryGSTPL['pvalue2'];
								}else{
									$drcostcode_ = $queryGSTBS['pvalue1'];
									$dracc_ = $queryGSTBS['pvalue2'];
								}

								$prepare="INSERT INTO finance.gltran
									 (compcode,adduser,adddate,auditno,lineno_,source,trantype,reference,description,postdate,year,period,drcostcode,dracc,crcostcode,cracc,amount,idno) VALUES 
									 (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
								$arrayValue = array(
										$row['compcode'],
										$row['adduser'],
										$row['recno'],
										$row['lineno_'],
										'IV',
										'GST',
										$row2['txndept'].' '.$row2['docno'],
										$row2['sndrcv'],
										$row2['trandate'],
										$yearperiod->year,
										$yearperiod->period,
										$drcostcode_,
										$dracc_,
										$queryACC['pvalue1'],
										$queryACC['pvalue2'],
										$row['amtslstax'],
										$row['itemcode'],
										);

								// echo $this->readableSyntax($prepare,$arrayValue);
								$this->save($prepare, $arrayValue);


								// 2. insert to glmasdtl //
								if($this->isGltranExist($drcostcode_,$dracc_,$yearperiod->year,$yearperiod->period)){
									$this->oper = 'edit';
									$this->table = 'finance.glmasdtl';
									// $this->yearperiod = null;
									$this->column = ['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];
									$this->columnid = null;
									$this->filterCol = ['costcode','glaccount','year'];
									$this->filterVal = [$drcostcode_,$dracc_,$yearperiod->year];

									$updarrField=['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];//extra field
									$updarrValue=[$this->compcode,$this->user,'NOW()',$row['amount']+$this->gltranAmount,'A'];
									
									$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,!empty($this->filterCol));
									$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
									if(!empty($this->columnid)){
										array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);
									}else{
										array_push($arrayValue,$this->compcode);
									}
									$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;

									$this->save($prepare,$arrayValue);
								}else{
									$this->oper = 'add';
									$this->table = 'finance.glmasdtl';
									// $this->yearperiod = null;
									$this->column = ['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];
									$this->columnid = 'compcode';

									$addarrField=['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];//extra field
									$addarrValue=[$this->compcode,$drcostcode_,$dracc_,$yearperiod->year,$row['amount'],$this->user,'NOW()','A'];
								
									$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
									$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

									$this->save($prepare,$arrayValue);
								}

								if($this->isGltranExist($queryACC['pvalue1'],$queryACC['pvalue2'],$yearperiod->year,$yearperiod->period)){
									$this->oper = 'edit';
									$this->table = 'finance.glmasdtl';
									// $this->yearperiod = null;
									$this->column = ['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];
									$this->columnid = null;
									$this->filterCol = ['costcode','glaccount','year'];
									$this->filterVal = [$queryACC['pvalue1'],$queryACC['pvalue2'],$yearperiod->year];

									$updarrField=['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];//extra field
									$updarrValue=[$this->compcode,$this->user,'NOW()',$this->gltranAmount-$row['amount'],'A'];
									
									$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,!empty($this->filterCol));
									$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
									if(!empty($this->columnid)){
										array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);
									}else{
										array_push($arrayValue,$this->compcode);
									}
									$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;

									$this->save($prepare,$arrayValue);
								}else{
									$this->oper = 'add';
									$this->table = 'finance.glmasdtl';
									// $this->yearperiod = null;
									$this->column = ['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];
									$this->columnid = 'compcode';

									$addarrField=['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];//extra field
									$addarrValue=[$this->compcode,$queryACC['pvalue1'],$queryACC['pvalue2'],$yearperiod->year,-$row['amount'],$this->user,'NOW()','A'];
								
									$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
									$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

									$this->save($prepare,$arrayValue);
								}

							}

						}

						
					// $sql="SELECT * from material.delordhd where recno = '".$recno."'";
					// 	$result = $this->db->query($sql);
					// 	if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}


						$sql="UPDATE material.delordhd
							SET postedby = '{$this->user}',
								postdate = NOW(), 
								recstatus = 'POSTED' 
							WHERE recno = '{$_POST['recno']}' AND compcode = '{$this->compcode}'";

						$result = $this->db->query($sql);
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}

						$sql="UPDATE material.delorddt 
							SET recstatus = 'POSTED' 
							WHERE recno = '{$_POST['recno']}' AND compcode = '{$this->compcode}' AND recstatus <> 'DELETE' ";

						$result = $this->db->query($sql);
						if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);
			

						//find stockloc
						// $sql="SELECT hd.txndept, hd.trandate, dt.itemcode, dt.uomcode, s.stockloc, s.deptcode, s.uomcode, s.year FROM material.ivtxnhd hd LEFT JOIN material.ivtxndt dt LEFT JOIN material.stockloc
						// ON dt.itemcode=s.itemcode AND dt.txndept=s.deptcode and dt.uomcode=s.uomcode and hd.trandate=s.year WHERE dt.compcode = '{$this->compcode}'";

						// $result = $this->db->query($sql);
						// echo $sql;
						// // if(!$result){throw new Exception("error: ".$this->db->errorInfo()[2]);}
						// // // $cy = date('Y'); 
						// // $deptcode = $_POST['txndept']; echo 'deptcode=' . $deptcode;
						// // $itemcode = $_POST['itemcode'];
						// // $uomcode = $_POST['uomcode'];
						// // $query="SELECT * FROM material.stockloc WHERE compcode='{$_SESSION['company']}'";
						// // $result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

						// $prepare = "INSERT INTO material.stockloc (compcode, deptcode, itemcode, year, uomcode, adduser, adddate) values (?, ?, ?, ?, ?, ?, NOW())";
						// 	$arrayValue = array(
						// 			$row['compcode'],
						// 			$row['deptcode'],
						// 			$row['itemcode'],
						// 			$row['trandate'],
						// 			$row['uomcode'],
						// 			$row['adduser'],
						// 			$row['adddate']);

						// 	echo $this->readableSyntax($prepare,$arrayValue);
						// 	$this->save($prepare, $arrayValue);
						//}
						//$stockloc = $this->getStockloc($deptcode, $itemcode, $uomcode, $year);
						
						// $getProduct = $this->getProduct($uomcode);

						// if($this->stocklocExist($cy,$itemcode,$uomcode,$seldata['txndept'])){

						// $trandateMonth = date("n",strtotime($seldata['trandate']));

						// 	// $oldqtyonhand = $this->getoldqtyonhand($cy,$trandateMonth,$itemcode,$uomcode,$seldata['txndept'])[0];
						// 	// $oldnetqty = $this->getoldqtyonhand($cy,$trandateMonth,$itemcode,$uomcode,$seldata['txndept'])[1];
						// 	// $oldnetval = $this->getoldqtyonhand($cy,$trandateMonth,$itemcode,$uomcode,$seldata['txndept'])[2];

						// 	// $prepare = "UPDATE material.stockloc SET qtyonhand = '$oldqtyonhand' + '$txnqty', netmvqty$trandateMonth = '$oldnetqty' + '{$qtyonhand}', netmvval$trandateMonth = $oldnetval + '{$amount}' WHERE compcode=? AND year=? AND itemcode = ? AND uomcode =?  AND deptcode = ?";

						// 	// $arrayValue = array($_SESSION['company'],$cy,$itemcode,$uomcode,$seldata['txndept']);


						// 	// $prepare = "UPDATE material.stockloc SET deptcode='$deptcode', itemcode='$itemcode', year='$year', uomcode='$uomcode', adduser='$adduser', adddate='NOW()', qtyonhand= WHERE compcode=?";

						// 	// $arrayValue = array($_SESSION['company']);
						// 					// $this->save($prepare,$arrayValue);

						//}

						// else{

						// $prepare = "INSERT INTO material.stockloc (compcode, deptcode, itemcode, year, uomcode, adduser, adddate) values (
						// 			?, ?, ?, ?, ?, ?, NOW())";
						// 	$arrayValue = array(
						// 			$row['compcode'],
						// 			$row['deptcode'],
						// 			$row['itemcode'],
						// 			$row['year'],
						// 			$row['uomcode'],
						// 			$row['adduser'],
						// 			$row['adddate']);


						// 	echo $this->readableSyntax($prepare,$arrayValue);
						// 	$this->save($prepare, $arrayValue);

						// $prepare = "INSERT INTO material.stockloc (compcode, deptcode, itemcode, year, uomcode, adduser, adddate) VALUES (?, ?, ?, ?, ?, ?, NOW())";

						// $arrayValue = array($_SESSION['company'], $seldata['deptcode'], $seldata['itemcode'], $seldata['year'], $seldata['uomcode'], $seldata['adddate']);

						// $this->save($prepare,$arrayValue);

						//}


						// 				//update stockloc

						// 				if($this->stockExpExist($seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno)){
										
						// 					$bal = $this->getbalqty($seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno);

						// 					$prepare = "UPDATE material.stockexp SET balqty = '$bal' + '$txnqty'  WHERE compcode=? AND deptcode = ? AND itemcode = ? AND uomcode =? AND expdate=? AND batchno=?";

						// 					$arrayValue = array($_SESSION['company'],$seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno);

						// 					$this->save($prepare,$arrayValue); 

						// 				}else{///
						// 					$prepare = "INSERT INTO material.stockexp (compcode,deptcode,itemcode,uomcode,expdate,batchno,balqty,adduser,adddate,addtime,upduser,upddate,updtime,lasttt,year) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

						// 					$arrayValue = array($_SESSION['company'],$seldata['txndept'],$itemcode,$uomcode,$expdate,$batchno,$txnqty,$adduser,$adddate,null,$upduser,$upddate,null,null,null);

						// 					$this->save($prepare,$arrayValue);
						// 				}
										//}



								/*	}	
							


					 //update stockloc*/






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
