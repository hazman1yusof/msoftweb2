<?php
	
	class cdreg_save{
		var $oper;
		var $gltranAmount;
		var $cbtranAmount;
		var $seldata;
		var $responce;

		public function __construct(PDO $db){
			include_once('sschecker.php');
			$this->db = $db;
			$this->oper = $_GET['oper'];
			$this->seldata = $_POST['seldata'];
		}

		public function isGltranExist($ccode,$glcode,$year,$period){
	
			$query = "select glaccount,actamount".$period." from finance.glmasdtl where compcode='{$_SESSION['company']}' and year='{$year}' and costcode = '{$ccode}' and glaccount = '{$glcode}'";
			//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			$this->gltranAmount = $resultarr["actamount".$period];
			return !empty($resultarr);
		}

		public function getyearperiod($date){
			
			$query = "select * from sysdb.period where compcode='{$_SESSION['company']}'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$seldate = new DateTime($date);

			while($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$year= $row['year'];
				$period=0;
				for($x=1;$x<=12;$x++){
					$period = $x;

					$datefr = new DateTime($row['datefr'.$x]);
					$dateto = new DateTime($row['dateto'.$x]);
					if (($datefr <= $seldate) &&  ($dateto >= $seldate)){
						$responce = new stdClass();
						$responce->year = $year;
						$responce->period = $period;
						$responce->status = $row['periodstatus'.$x];
						return $responce;
					}
				}
			}
		}

		public function gettax($source,$trantype){
			$query = "select pvalue1,pvalue2 from sysdb.sysparam where compcode='{$_SESSION['company']}' and source='$source' and trantype = '$trantype' ";
			echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC);
		}

		public function getGLcode($bankcode){

			$query = "select glccode,glaccno from finance.bank where compcode='{$_SESSION['company']}' and bankcode = '$bankcode'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC);
		}	

		public function getDept($deptcode){

			$query = "SELECT costcode FROM sysdb.department WHERE compcode='{$_SESSION['company']}' AND deptcode = '$deptcode'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC);
		}

		public function getCat($catcode){

			$query = "SELECT expacct FROM material.category WHERE compcode='{$_SESSION['company']}' AND source='CR' AND  catcode = '$catcode'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC);
		}

		public function readableSyntax($prepare,array $arrayValue){
			foreach($arrayValue as $val){
				$prepare=preg_replace("/\?/", "'".$val."'", $prepare,1);
			}
			return $prepare."\r\n";
		}

		public function getCbtranTotamt($bankcode,$year,$period){

			$query = "select SUM(amount) AS amount from finance.cbtran where compcode='{$_SESSION['company']}' and bankcode='$bankcode' and year='$year' and period='$period'";
			//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC)['amount'];
		}

		public function isCBtranExist($bankcode,$year,$period){
			
			$query = "select bankcode,actamount".$period." from finance.bankdtl where compcode='{$_SESSION['company']}' and year='{$year}' and bankcode = '{$bankcode}'";
			echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			$this->cbtranAmount = $resultarr["actamount".$period];
			return !empty($resultarr);
		}

		public function isPaytypeCheque($paymode){
			$query = "SELECT paytype from debtor.paymode WHERE compcode='{$_SESSION['company']}' AND  paymode = '$paymode' AND source= 'CM' AND paytype = 'Cheque'";
			//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			return !empty($resultarr);
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
				$tempobj = $this->getyearperiod($seldata['actdate']);
				if($tempobj->status != 'O'){
					throw new Exception("Period Closed");
				}
				$amountused = ($seldata['trantype'] == 'CA') ? -$seldata['amount'] : $seldata['amount'];
				
				if($this->oper=='add'){
				
				//1st step add cbtran -
					$prepare = "INSERT INTO finance.cbtran (compcode,bankcode,source,trantype,auditno,postdate,year,period,amount,remarks,upduser,upddate,bitype,reference,stat,refsrc,reftrantype,refauditno) VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?,?)";

					$arrayValue = array($_SESSION['company'],$seldata['bankcode'],$seldata['source'],$seldata['trantype'],$seldata['auditno'],$seldata['actdate'],$tempobj->year,$tempobj->period,$amountused,$seldata['remarks'],$_SESSION['username'],null, $seldata[ 'remarks'],'A',null,null,null);

					/*$prepare = "INSERT INTO finance.cbtran (compcode,bankcode,source,trantype,auditno,postdate,year,period,amount,remarks,upduser,upddate,bitype,reference,stat,refsrc,reftrantype,refauditno) VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?,?)";

					$arrayValue = array($_SESSION['company'],$seldata['bankcode'],$seldata['source'],$seldata['trantype'],$seldata['auditno'],$seldata['actdate'],$tempobj->year,$tempobj->period,-$seldata['amount'],$seldata['remarks'],$_SESSION['username'],null,'Payto :'. ' ' .$seldata['payto']  . ' ' . $seldata[ 'remarks'],'A',null,null,null);*/

					$this->save($prepare,$arrayValue);


				//1st step -> 2nd phases, update bankdtl
					if($this->isCBtranExist($seldata['bankcode'],$tempobj->year,$tempobj->period)){

						// $totamt = $this->getCbtranTotamt($seldata['bankcode'],$tempobj->year,$tempobj->period);

						$prepare = "UPDATE finance.bankdtl SET actamount".$tempobj->period." = ? WHERE bankcode =? AND year =?";
						$arrayValue = array($this->cbtranAmount+$amountused,$seldata['bankcode'],$tempobj->year);

						$this->save($prepare,$arrayValue);
					}else{

						$prepare = "INSERT INTO finance.bankdtl (compcode,bankcode,year,actamount".$tempobj->period.",upduser,upddate) VALUES (?,?,?,?,?,NOW())";
						$arrayValue = array($_SESSION['company'],$seldata['bankcode'],$tempobj->year,$amountused,$_SESSION['username']);

						$this->save($prepare,$arrayValue);
					}

				//2nd step step add gltran
					//$creditbank = $this->getGLcode($seldata['bankcode']);

					$queryDP = "SELECT d.compcode, d.source, d.trantype, d.auditno, d.lineno_, d.document, h.remarks, 
									 d.deptcode, d.category, h.bankcode, d.amount, h.actdate, d.AmtB4GST
								FROM finance.apacthdr h 
								LEFT JOIN finance.apactdtl d 
								ON d.auditno=h.auditno 
								AND d.compcode = h.compcode 
								AND d.source=h.source 
								AND d.trantype=h.trantype
								WHERE d.compcode = '{$_SESSION['company']}' 
								AND d.source = '{$seldata['source']}' 
								AND d.trantype = '{$seldata['trantype']}' 
								AND d.auditno = '{$seldata['auditno']}'";

					// echo $queryDP;
					$result = $this->db->query($queryDP);if (!$result) { print_r($this->db->errorInfo()); }
					//$row = $result->fetch(PDO::FETCH_ASSOC);

					if($seldata['TaxClaimable'] == 'Claimable'){
						$gst = $this->gettax('TX','BS');
					}else{
						$gst = $this->gettax('TX','PNL');
					}

					while($row = $result->fetch(PDO::FETCH_ASSOC)) {
						$compcode=($row['compcode']);
						$source=($row['source']);
						$trantype=($row['trantype']);
						$auditno=($row['auditno']);
						$lineno_=($row['lineno_']);
						$document=($row['document']);
						$remarks=($row['remarks']);
						$deptcode=($row['deptcode']);
						$category=($row['category']);
						$bankcode=($row['bankcode']);
						$amount=($row['amount']);
						$amountb4gst = $row['AmtB4GST'];
						$amount2=($seldata['trantype'] == 'CA') ? -$row['AmtB4GST'] : $row['AmtB4GST'];
						$amountgst=$row['amount'] - $row['AmtB4GST'];
						$actdate=($row['actdate']);

						$bank = $this->getGLcode($bankcode);
						$dept = $this->getDept($deptcode);
						$cat = $this->getCat($category);

						if($seldata['trantype'] == 'CA'){
							$drcostcode = $dept['costcode'];
							$dracc = $cat['expacct'];

							$crcostcode = $bank['glccode'];
							$cracc = $bank['glaccno'];
						}else{
							$drcostcode = $bank['glccode'];
							$dracc = $bank['glaccno'];

							$crcostcode = $dept['costcode'];
							$cracc = $cat['expacct'];
						}

						$prepare = "INSERT INTO finance.gltran (compcode,auditno,lineno_,source,trantype,reference,description,year,period,drcostcode,crcostcode,dracc,cracc,amount,idno,postdate,adduser,adddate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";

						$arrayValue = array($_SESSION['company'],$auditno, $lineno_.'1',$source,$trantype,$seldata['refsource'],$remarks,$tempobj->year,$tempobj->period,$drcostcode,$crcostcode,$dracc,$cracc,$amountb4gst,null,$actdate,$_SESSION['username']);

						$this->save($prepare,$arrayValue);

						if($this->isGltranExist($dept['costcode'],$cat['expacct'],$tempobj->year,$tempobj->period)){

							$prepare = "UPDATE finance.glmasdtl SET actamount".$tempobj->period." = ? WHERE costcode = ? AND glaccount = ? AND year =?";

							$arrayValue = array($this->gltranAmount+$amount2,$dept['costcode'],$cat['expacct'],$tempobj->year);

							$this->save($prepare,$arrayValue);

						}else{///
							$prepare = "INSERT INTO finance.glmasdtl (compcode,costcode,glaccount,year,actamount".$tempobj->period.",adduser,adddate,recstatus) VALUES (?,?,?,?,?,?,NOW(),?)";

							$arrayValue = array($_SESSION['company'],$dept['costcode'],$cat['expacct'],$tempobj->year,$amount2,$_SESSION['username'],'A');

							$this->save($prepare,$arrayValue);
						}

						//////for gst////////////////////////////////////////
						echo "<gl for gst>";
						$prepare = "INSERT INTO finance.gltran (compcode,auditno,lineno_,source,trantype,reference,description,year,period,drcostcode,crcostcode,dracc,cracc,amount,idno,postdate,adduser,adddate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";

						$arrayValue = array($_SESSION['company'],$auditno, $lineno_.'2',$source,$trantype,$seldata['refsource'],$remarks,$tempobj->year,$tempobj->period,$drcostcode,$crcostcode,$dracc,$cracc,$amountgst,null,$actdate,$_SESSION['username']);

						$this->save($prepare,$arrayValue);

						if($this->isGltranExist($gst['pvalue1'],$gst['pvalue2'],$tempobj->year,$tempobj->period)){

							$prepare = "UPDATE finance.glmasdtl SET actamount".$tempobj->period." = ? WHERE costcode = ? AND glaccount = ? AND year =?";

							$arrayValue = array($this->gltranAmount+$amountgst,$gst['pvalue1'],$gst['pvalue2'],$tempobj->year);

							$this->save($prepare,$arrayValue);

						}else{///
							$prepare = "INSERT INTO finance.glmasdtl (compcode,costcode,glaccount,year,actamount".$tempobj->period.",adduser,adddate,recstatus) VALUES (?,?,?,?,?,?,NOW(),?)";

							$arrayValue = array($_SESSION['company'],$gst['pvalue1'],$gst['pvalue2'],$tempobj->year,$amountgst,$_SESSION['username'],'A');

							$this->save($prepare,$arrayValue);
						}						
						/////////////////////////////////////////////////////
					}

				//3rd step add glmasdtl untuk bankcode
					if($this->isGltranExist($bank['glccode'],$bank['glaccno'],$tempobj->year,$tempobj->period)){
						$prepare = "UPDATE finance.glmasdtl SET actamount".$tempobj->period." = ? WHERE costcode = ? AND glaccount = ? AND year =?";

						$arrayValue = array($this->gltranAmount+$amountused,$bank['glccode'],$bank['glaccno'],$tempobj->year);

						$this->save($prepare,$arrayValue);
					}else{
						$prepare = "INSERT INTO finance.glmasdtl (compcode,costcode,glaccount,year,actamount".$tempobj->period.",adduser,adddate,recstatus) VALUES (?,?,?,?,?,?,NOW(),?)";

						$arrayValue = array($_SESSION['company'],$bank['glccode'],$bank['glaccno'],$tempobj->year,$amountused,$_SESSION['username'],'A');

						$this->save($prepare,$arrayValue);
					}

					//step update stat at cheqtran
					// if($this->isPaytypeCheque($seldata['paymode']) == 'Cheque') {
					// 	$prepare = "UPDATE finance.chqtran SET cheqdate=?, amount=?, remarks=?, stat = ?, upduser = ?, upddate = NOW(), trantype =?, source = ?, auditno = ? 
					// 	  WHERE cheqno = ? AND bankcode = ? AND compcode = ?";

					// 	$arrayValue = array($seldata['cheqdate'], $seldata['amount'], $seldata['remarks'], 'I',$_SESSION['username'], $seldata['trantype'], $seldata['source'], $seldata['auditno'],$seldata['cheqno'],$seldata['bankcode'],$_SESSION['company']);

					// 	$this->save($prepare,$arrayValue);

					// }
				
					//4th step change status to posted
					$prepare = "UPDATE finance.apacthdr SET recstatus = ? WHERE auditno = ? AND source = ? AND trantype =?";

					$arrayValue = array('P',$seldata['auditno'],$seldata['source'],$seldata['trantype']);

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
