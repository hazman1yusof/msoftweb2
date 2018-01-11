<?php
	
	class invoiceAP_save{
		var $oper;
		var $seldata;
		var $responce;
		var $sysparam;

		public function __construct(PDO $db){
			include_once('sschecker.php');
			$this->db = $db;
			$this->oper = $_GET['oper'];
			$this->seldata = $_POST;
		}

		public function sysparam($source,$trantype){
			
			$sqlSysparam="SELECT pvalue1 FROM sysdb.sysparam WHERE source = '$source' AND trantype = '$trantype'";
			$result = $this->db->query($sqlSysparam);if (!$result) { print_r($this->db->errorInfo()); }
			$row = $result->fetch(PDO::FETCH_ASSOC);
			
			$pvalue1=intval($row['pvalue1'])+1;
			
			$sqlSysparam="UPDATE sysdb.sysparam SET pvalue1 = '{$pvalue1}' WHERE source = '$source' AND trantype = '$trantype'";
			echo $sqlSysparam;
			
			$this->db->query($sqlSysparam);if (!$result) { print_r($this->db->errorInfo()); }
			
			return $pvalue1;
		}

		public function suppgroup($suppcode){
			
			$sql="SELECT SuppGroup FROM material.supplier WHERE SuppCode = '$suppcode' and compcode = '{$_SESSION['company']}'";
			$result = $this->db->query($sql);if (!$result) { print_r($this->db->errorInfo()); }
			$row = $result->fetch(PDO::FETCH_ASSOC);
			
			return $row['SuppGroup'];
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
				
				if($this->oper=='add'){

					$auditno = $this->sysparam($seldata['apacthdr_source'],$seldata['apacthdr_trantype']);
					$suppgroup = $this->suppgroup($seldata['apacthdr_suppcode']);

					$prepare = "INSERT INTO finance.apacthdr (compcode,auditno,source,trantype,suppcode,suppgroup,payto,document,category,amount,outamount,remarks,actdate,recdate,deptcode,recstatus,adduser,adddate) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";

					$arrayValue = array($_SESSION['company'],$auditno,$seldata['apacthdr_source'],$seldata['apacthdr_trantype'],$seldata['apacthdr_suppcode'],$suppgroup,$seldata['apacthdr_payto'],$seldata['apacthdr_document'],$seldata['apacthdr_category'],$seldata['apacthdr_amount'],$seldata['apacthdr_amount'],$seldata['apacthdr_remarks'],$seldata['apacthdr_actdate'],$seldata['apacthdr_recdate'],$seldata['apacthdr_deptcode'],'O',$_SESSION['username']);

					$this->save($prepare,$arrayValue);
					
				}else if($this->oper=='edit'){

					$suppgroup = $this->suppgroup($seldata['apacthdr_suppcode']);

					$prepare = "UPDATE finance.apacthdr SET source=?,trantype=?,suppcode=?,suppgroup=?,payto=?,document=?,category=?,amount=?,outamount=?,remarks=?,actdate=?,recdate=?,deptcode=?,upduser=?,upddate=NOW() WHERE idno=?";

					$arrayValue = array($seldata['apacthdr_source'],$seldata['apacthdr_trantype'],$seldata['apacthdr_suppcode'],$suppgroup,$seldata['apacthdr_payto'],$seldata['apacthdr_document'],$seldata['apacthdr_category'],$seldata['apacthdr_amount'],$seldata['apacthdr_amount'],$seldata['apacthdr_remarks'],$seldata['apacthdr_actdate'],$seldata['apacthdr_recdate'],$seldata['apacthdr_deptcode'],$_SESSION['username'],$seldata['idno']);

					$this->save($prepare,$arrayValue);
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
