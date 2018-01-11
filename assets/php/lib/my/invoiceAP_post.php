<?php
	
	class invoiceAP_post{
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

		public function gltran_fromdept($deptcode,$catcode){
			
			$query = "SELECT costcode FROM sysdb.department WHERE compcode='{$_SESSION['company']}' AND deptcode = '$deptcode'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$responce['drcostcode'] = $result->fetch(PDO::FETCH_ASSOC)['costcode'];

			$query = "SELECT expacct FROM material.category WHERE compcode='{$_SESSION['company']}' AND catcode = '$catcode' and source='CR'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$responce['draccno'] = $result->fetch(PDO::FETCH_ASSOC)['expacct'];

			return $responce;
		}

		public function gltran_fromsupp($suppcode){

			$query = "SELECT glaccno,costcode FROM material.supplier WHERE compcode='{$_SESSION['company']}' AND suppcode = '$suppcode'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC);
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
			try{
				if($commit){
					$this->db->beginTransaction();
				}

				$seldata = $this->seldata;

				$tempobj = $this->getyearperiod($seldata['apacthdr_actdate']);
				if($tempobj->status != 'O'){
					throw new Exception("Period Closed");
				}
				
				if($this->oper=='add'){

				// 1) change recstatus from O to P

					$prepare = "UPDATE finance.apacthdr SET recstatus=?,upduser=?,upddate=NOW() WHERE idno=?";

					$arrayValue = array("P",$_SESSION['username'],$seldata['apacthdr_idno']);

					$this->save($prepare,$arrayValue);

				// 2) gltran tak siap lagi untuk supplier**

					$gldebit = $this->gltran_fromdept($seldata['apacthdr_deptcode'],$seldata['apacthdr_category']);
					$glcredit = $this->gltran_fromsupp($seldata['apacthdr_suppcode']);
					$lineno_=1;
					$prepare = "INSERT INTO finance.gltran (compcode,auditno,lineno_,source,trantype,reference,description,year,period,drcostcode,crcostcode,dracc,cracc,amount,idno,postdate,adduser,adddate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";

					$arrayValue = array($_SESSION['company'],$seldata['apacthdr_auditno'],$lineno_,$seldata['apacthdr_source'],$seldata['apacthdr_trantype'],$seldata['apacthdr_document'],$seldata['apacthdr_remarks'],$tempobj->year,$tempobj->period,$gldebit['drcostcode'],$glcredit['costcode'],$gldebit['draccno'],$glcredit['glaccno'],$seldata['apacthdr_amount'],null,$seldata['apacthdr_recdate'],$_SESSION['username']);

					$this->save($prepare,$arrayValue);


					//debit gl
					if($this->isGltranExist($gldebit['drcostcode'],$gldebit['draccno'],$tempobj->year,$tempobj->period)){

						$prepare = "UPDATE finance.glmasdtl SET actamount".$tempobj->period." = ? WHERE costcode = ? AND glaccount = ? AND year = ?";

						$arrayValue = array($this->gltranAmount+$seldata['apacthdr_amount'],$gldebit['drcostcode'],$gldebit['draccno'],$tempobj->year);

						$this->save($prepare,$arrayValue);

					}else{///
						$prepare = "INSERT INTO finance.glmasdtl (compcode,costcode,glaccount,year,actamount".$tempobj->period.",adduser,adddate,recstatus) VALUES (?,?,?,?,?,?,NOW(),?)";

						$arrayValue = array($_SESSION['company'],$gldebit['drcostcode'],$gldebit['draccno'],$tempobj->year,$seldata['apacthdr_amount'],$_SESSION['username'],'A');

						$this->save($prepare,$arrayValue);
					}

					//credit gl
					if($this->isGltranExist($glcredit['costcode'],$glcredit['glaccno'],$tempobj->year,$tempobj->period)){

						$prepare = "UPDATE finance.glmasdtl SET actamount".$tempobj->period." = ? WHERE costcode = ? AND glaccount = ? AND year = ?";

						$arrayValue = array($this->gltranAmount-$seldata['apacthdr_amount'],$glcredit['costcode'],$glcredit['glaccno'],$tempobj->year);

						$this->save($prepare,$arrayValue);

					}else{///
						$prepare = "INSERT INTO finance.glmasdtl (compcode,costcode,glaccount,year,actamount".$tempobj->period.",adduser,adddate,recstatus) VALUES (?,?,?,?,?,?,NOW(),?)";

						$arrayValue = array($_SESSION['company'],$glcredit['costcode'],$glcredit['glaccno'],$tempobj->year,-$seldata['apacthdr_amount'],$_SESSION['username'],'A');

						$this->save($prepare,$arrayValue);
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
