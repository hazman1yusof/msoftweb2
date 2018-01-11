<?php
	
	class bankreg_save{
		var $oper;
		var $gltranAmount;
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
						return $responce;
					}
				}
			}

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

		public function isCBtranExist($bankcode,$year){
			
			$query = "select * from finance.bankdtl where compcode='{$_SESSION['company']}' and year='{$year}' and bankcode = '$bankcode'";
			//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
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
			$seldata = $this->seldata;
			$tempobj = $this->getyearperiod($seldata['actdate']);

			try{
				if($commit){
					$this->db->beginTransaction();
				}
				
				if($this->oper=='add'){
				
				//1st step add cbtran -
					$prepare = "INSERT INTO finance.cbtran (compcode,bankcode,source,trantype,auditno,postdate,year,period,cheqno,amount,remarks,lastuser,lastupdate,bitype,reference,stat,refsrc,reftrantype,refauditno) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?,?)";

					$arrayValue = array($_SESSION['company'],$seldata['bankcode'],$seldata['source'],$seldata['trantype'],$seldata['auditno'],$seldata['actdate'],$tempobj->year,$tempobj->period,$seldata['cheqno'],-$seldata['amount'],$seldata['remarks'],$_SESSION['username'],null,'Transfer from :'. ' ' .$seldata['bankcode']  . ' ' . 'to'. ' '. $seldata[ 'payto'],'A',null,null,null);

					//$arrayValue = array($_SESSION['company'],$seldata['bankcode'],$seldata['source'],$seldata['trantype'],$seldata['auditno'],$seldata['actdate'],$tempobj->year,$tempobj->period,$seldata['cheqno'],-$seldata['amount'],$seldata['remarks'],$_SESSION['username'],null,null,'A',null,null,null);

					$this->save($prepare,$arrayValue);


				//1st step -> 2nd phases, update bankdtl
					if($this->isCBtranExist($seldata['bankcode'],$tempobj->year)){

						$totamt = $this->getCbtranTotamt($seldata['bankcode'],$tempobj->year,$tempobj->period);

						$prepare = "UPDATE finance.bankdtl SET actamount".$tempobj->period." = ? WHERE bankcode =? AND year =?";
						$arrayValue = array($totamt-$seldata['amount'],$seldata['bankcode'],$tempobj->year);

						$this->save($prepare,$arrayValue);
					}else{

						$prepare = "INSERT INTO finance.bankdtl (compcode,bankcode,year,actamount".$tempobj->period.",lastuser,lastupdate) VALUES (?,?,?,?,?,NOW())";
						$arrayValue = array($_SESSION['company'],$seldata['bankcode'],$tempobj->year,-$seldata['amount'],$_SESSION['username']);

						$this->save($prepare,$arrayValue);
					}

					//2nd step add cbtran +
					$prepare = "INSERT INTO finance.cbtran (compcode,bankcode,source,trantype,auditno,postdate,year,period,cheqno,amount,remarks,lastuser,lastupdate,bitype,reference,stat,refsrc,reftrantype,refauditno) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,?,?)";

					$arrayValue = array($_SESSION['company'],$seldata['payto'],$seldata['source'],$seldata['trantype'],$seldata['auditno'],$seldata['actdate'],$tempobj->year,$tempobj->period,$seldata['cheqno'],$seldata['amount'],$seldata['remarks'],$_SESSION['username'],null,'Transfer from :'. ' ' .$seldata['payto']  . ' ' . 'to'. ' '.  $seldata[ 'bankcode'],'A',null,null,null);

					//$arrayValue = array($_SESSION['company'],$seldata['bankcode'],$seldata['source'],$seldata['trantype'],$seldata['auditno'],$seldata['actdate'],$tempobj->year,$tempobj->period,$seldata['cheqno'],-$seldata['amount'],$seldata['remarks'],$_SESSION['username'],null,null,'A',null,null,null);

					$this->save($prepare,$arrayValue);

					
				//2nd step 2nd phases, update bankdtl
					if($this->isCBtranExist($seldata['payto'],$tempobj->year)){

						$totamt = $this->getCbtranTotamt($seldata['payto'],$tempobj->year,$tempobj->period);

						$prepare = "UPDATE finance.bankdtl SET actamount".$tempobj->period." = ? WHERE bankcode =? AND year =?";
						$arrayValue = array($totamt-$seldata['amount'],$seldata['payto'],$tempobj->year);

						$this->save($prepare,$arrayValue);
					}else{

						$prepare = "INSERT INTO finance.bankdtl (compcode,bankcode,year,actamount".$tempobj->period.",lastuser,lastupdate) VALUES (?,?,?,?,?,NOW())";
						$arrayValue = array($_SESSION['company'],$seldata['payto'],$tempobj->year,-$seldata['amount'],$_SESSION['username']);

						$this->save($prepare,$arrayValue);
					}


				//2nd step step add gltran
					//$creditbank = $this->getGLcode($seldata['bankcode']);

					$debitbank = $this->getGLcode($seldata['payto']);
					$creditbank = $this->getGLcode($seldata['bankcode']);

					$prepare = "INSERT INTO finance.gltran (compcode,auditno,lineno_,source,trantype,reference,description,year,period,drcostcode,crcostcode,dracc,cracc,amount,idno,postdate,adduser,adddate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";

					$arrayValue = array($_SESSION['company'],$seldata['auditno'],1,$seldata['source'],$seldata['trantype'],null,$seldata['remarks'],$tempobj->year,$tempobj->period,$debitbank['glccode'],$creditbank['glccode'],$debitbank['glaccno'],$creditbank['glaccno'],$seldata['amount'],null,$seldata['actdate'],$_SESSION['username']);

					$this->save($prepare,$arrayValue);


				//3th step add glmasdtl untuk bankcode
					if($this->isGltranExist($debitbank['glccode'],$debitbank['glaccno'],$tempobj->year,$tempobj->period)){

						$prepare = "UPDATE finance.glmasdtl SET actamount".$tempobj->period." = ? WHERE costcode = ? AND glaccno = ? AND year =?";

						$arrayValue = array($this->gltranAmount+$seldata['amount'],$debitbank['glccode'],$debitbank['glaccno'],$tempobj->year);

						$this->save($prepare,$arrayValue);

					}else{
						$prepare = "INSERT INTO finance.glmasdtl (compcode,costcode,glaccno,year,actamount".$tempobj->period.",adduser,adddate,recstatus) VALUES (?,?,?,?,?,?,NOW(),?)";

						$arrayValue = array($_SESSION['company'],$debitbank['glccode'],$debitbank['glaccno'],$tempobj->year,$seldata['amount'],$_SESSION['username'],'A');

						$this->save($prepare,$arrayValue);
					}

					if($this->isGltranExist($creditbank['glccode'],$creditbank['glaccno'],$tempobj->year,$tempobj->period)){
						$prepare = "UPDATE finance.glmasdtl SET actamount".$tempobj->period." = ? WHERE costcode = ? AND glaccno = ? AND year =?";

						$arrayValue = array($this->gltranAmount-$seldata['amount'],$creditbank['glccode'],$creditbank['glaccno'],$tempobj->year);

						$this->save($prepare,$arrayValue);
					}else{
						$prepare = "INSERT INTO finance.glmasdtl (compcode,costcode,glaccno,year,actamount".$tempobj->period.",adduser,adddate,recstatus) VALUES (?,?,?,?,?,?,NOW(),?)";

						$arrayValue = array($_SESSION['company'],$creditbank['glccode'],$creditbank['glaccno'],$tempobj->year,-$seldata['amount'],$_SESSION['username'],'A');

						$this->save($prepare,$arrayValue);
					}
				
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
