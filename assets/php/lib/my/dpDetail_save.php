<?php
	
	class dpHeader_save{

	    protected $db;
	    var $company;
	    var $user;
	    var $oper;	
	    var $returnVal=false;
	    var $total;
	    
	    //var $lineno;
	    //var $operDetail;
 
	    public function __construct(PDO $db)
	    {
	        $this->db = $db;
	        //$this->oper = $_GET['oper'];
	        //$this->returnVal=$_GET['returnVal'];
	        $this->total;
	        //$this->operDetail = $_POST['oper'];
	        session_start();
	        $this->company = $_SESSION['company'];
	        $this->user = $_SESSION['username'];
	    }

		private function readableSyntax($prepare,array $arrayValue){
			foreach($arrayValue as $val){
				$prepare=preg_replace("/\?/", "'".$val."'", $prepare,1);
			}
			return $prepare."\r\n";
		}

	    function save(){

			$table1='finance.apacthdr';
			$table2='finance.apactdtl';

			$company=$this->company;
			$user=$this->user;

			try{
				$this->db->beginTransaction();

				/*if($this->oper=='add'){
					
					$source = $_REQUEST['source'];
					$trantype = $_REQUEST['trantype'];


					$sqlSysparam="SELECT pvalue1 FROM sysdb.sysparam WHERE source = '$source' AND trantype = '$trantype'";
					$result = $this->db->query($sqlSysparam);if (!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);
			
					$pvalue1=intval($row['pvalue1'])+1;
			
					$sqlSysparam="UPDATE sysdb.sysparam SET pvalue1 = '$pvalue1' WHERE source = '$source' AND trantype = '$trantype'";
					$arrayValue=[$pvalue1];
					$sth=$this->db->prepare($sqlSysparam);
					$sth->execute($arrayValue);
					///echo $sqlSysparam;

					$prepare="INSERT INTO {$table1} (compcode, source, trantype, auditno, bankcode, payto, actdate, amount, remarks, adduser, adddate, pvno, paymode, cheqno, cheqdate,recstatus) 
					VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,?,'O')"; 
					$arrayValue=[
								$this->company,
								$_POST['source'],
								$_POST['trantype'],
								$pvalue1,
								$_POST['bankcode'],
								$_POST['payto'],
								$_POST['actdate'],
								$_POST['amount'],
								$_POST['remarks'],
								$this->user,
								$_POST['pvno'],							
								$_POST['paymode'],
								$_POST['cheqno'],
								$_POST['cheqdate']
							];

					//echo $Id=mysql_insert_id();

					echo $this->readableSyntax($prepare,$arrayValue);
					$sth=$this->db->prepare($prepare);
					$sth->execute($arrayValue);

					/*$prepare = "SELECT TRUE FROM {table1} WHERE auditno = '$auditno' AND compcode = '$company' ";
					$g=intval($row["TRUE"])
					$arrayValue=[$g];
					echo $this->readableSyntax($prepare,$arrayValue);
					//////////////////////////////////////////////////////////
					$sth=$this->db->prepare($prepare);
					if(!$sth->execute($arrayValue)){throw new Exception('error');}

					$i=0;
					while($row = $sth->fetch(PDO::FETCH_OBJ)) {
						$this->responce->rows[$i]=$row;
						$i++;
					}*/
						//$this->db->commit();

				/*}else if($this->oper=='edit'){

					$prepare = "UPDATE {$table1}  SET bankcode=?, payto=?, actdate=?, amount=?, remarks=?, 
						pvno=?, paymode=?, cheqno=?, cheqdate=?, upduser= ?, upddate=NOW(), recstatus='O'
					WHERE compcode = ? AND auditno = ? AND trantype = ?";

					$arrayValue=[
								$_POST['bankcode'],
								$_POST['payto'],
								$_POST['actdate'],
								$_POST['amount'],
								$_POST['remarks'],
								$_POST['pvno'],							
								$_POST['paymode'],
								$_POST['cheqno'],
								$_POST['cheqdate'],
								$this->user,
								$this->company,
								$_POST['auditno'],
								$_POST['trantype']
							];

					//echo $this->readableSyntax($prepare,$arrayValue);
					$sth=$this->db->prepare($prepare);
					$sth->execute($arrayValue);
				}

				/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				///////////////////////////////////////////////DETAIL////////////////////////////////////////////////////////////
				/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				else*/ if($this->oper = $_POST['oper']=='add'){

					$source = $_REQUEST['source'];
					$trantype = $_REQUEST['trantype'];
					$auditno = $_REQUEST['auditno'];

					$sqlln = "SELECT COUNT(lineno_) as COUNT from $table2 WHERE compcode='$company' AND source='$source' AND trantype='$trantype' AND auditno='$auditno'";

							$result = $this->db->query($sqlln);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					
							$li=intval($row['COUNT'])+1;
							$arrayValue=[$li];
							//echo $this->readableSyntax($sqlln,$arrayValue);

					$prepare="INSERT INTO {$table2} (compcode, source, trantype, auditno, lineno_, deptcode, category, document, amount,  adduser, adddate, recstatus,GSTCode,AmtB4GST) 
					VALUES (?,?,?,?,?,?,?,?,?,?,NOW(),'A',?,?)"; 
					$arrayValue=[
								$this->company,
								$source,
								$trantype,
								$auditno,
								$li,
								$_POST['deptcode'],
								$_POST['category'],
								$_POST['document'],
								$_POST['amount'],
								$this->user,
								$_POST['GSTCode'],
								$_POST['AmtB4GST']
							];

					$sth=$this->db->prepare($prepare);
					$sth->execute($arrayValue);

					$amount="SELECT SUM(amount) AS AMOUNT FROM {$table2} WHERE compcode = '$company' AND source='$source' AND trantype='$trantype' AND auditno='$auditno' AND recstatus='A'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);
					$arrayValue=[$totalAmount];
					//echo $totalAmount;
					//echo $this->readableSyntax($amount,$arrayValue);

					$uptAmount="UPDATE {$table1} SET amount='$totalAmount' 
								WHERE compcode = '$company' AND source='$source' AND trantype='$trantype' AND auditno='$auditno'";
					$arrayValue=[$totalAmount];
					//echo $this->readableSyntax($uptAmount,$arrayValue);
					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=$totalAmount;

				}else if($this->oper = $_POST['oper']=='edit'){


					$prepare="UPDATE {$table2}  SET
					deptcode=?,
					category=?,
					document=?,
					amount=?,
					upduser= ?,
					upddate=NOW(),
					recstatus='A',
					GSTCode = ?,
					AmtB4GST = ?

					WHERE compcode = ? AND auditno = ? AND lineno_ = ?";

					$arrayValue=[
								$_POST['deptcode'],
								$_POST['category'],							
								$_POST['document'],
								$_POST['amount'],
								$this->user,
								$_POST['GSTCode'],
								$_POST['AmtB4GST'],
								$company,
								$_POST['auditno'],							
								$_POST['lineno_']
							];						

							//echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);

					$source = $_REQUEST['source'];
					$trantype = $_REQUEST['trantype'];
					$auditno = $_REQUEST['auditno'];

					$amount="SELECT SUM(amount) AS AMOUNT FROM {$table2} WHERE compcode = '$company' AND source='$source' AND trantype='$trantype' AND auditno='$auditno' AND recstatus='A'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);
					$arrayValue=[$totalAmount];
					//echo $totalAmount;
					//echo $this->readableSyntax($amount,$arrayValue);

					$uptAmount="UPDATE {$table1} SET amount='$totalAmount' 
								WHERE compcode = '$company' AND source='$source' AND trantype='$trantype' AND auditno='$auditno'";
					$arrayValue=[$totalAmount];
					//echo $this->readableSyntax($uptAmount,$arrayValue);
					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=$totalAmount;
					
				}else if($this->oper = $_POST['oper']=='del'){
					$lineno_ = $_REQUEST['lineno_'];
					$auditno = $_REQUEST['auditno'];
					$source = $_REQUEST['source'];
					$trantype = $_REQUEST['trantype'];

					$prepare="UPDATE {$table2} SET 
							
							deluser=?,
							deldate=NOW(),
							recstatus='D'
							
					WHERE compcode = ? AND auditno = '$auditno' AND lineno_ = '$lineno_'";
				

					
					$arrayValue=[
								$this->user,
								$company,
							];																	

							//echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);

					$amount="SELECT SUM(amount) AS AMOUNT FROM {$table2} WHERE compcode = '$company' AND source='$source' AND trantype='$trantype' AND auditno='$auditno' AND recstatus='A'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);
					$arrayValue=[$totalAmount];
					//echo $totalAmount;
					//echo $this->readableSyntax($amount,$arrayValue);

					$uptAmount="UPDATE {$table1} SET amount='$totalAmount' 
								WHERE compcode = '$company' AND source='$source' AND trantype='$trantype' AND auditno='$auditno'";
					$arrayValue=[$totalAmount];
					//echo $this->readableSyntax($uptAmount,$arrayValue);
					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=$totalAmount;

					
				}


				echo $this->total;
				$this->db->commit();

			}catch( Exception $e ){
				$this->db->rollback();
				http_response_code(400);
				echo $e->getMessage();
				
			}
		}
	}

?>
