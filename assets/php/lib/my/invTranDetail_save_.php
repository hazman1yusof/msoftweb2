<?php
	
	class invTranDetail_save{

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

		public function chgDate($date){
			if(!empty($date)){
				$newstr=explode("/", $date);
				return $newstr[2].'-'.$newstr[1].'-'.$newstr[0];
			}
		}

	    function save(){

			$table1='material.ivtmphd';
			$table2='material.ivtmpdt';

			$company=$this->company;
			$user=$this->user;

			try{
				$this->db->beginTransaction();

				if($this->oper = $_POST['oper']=='add'){

					//$source = $_REQUEST['source'];
					//$trantype = $_REQUEST['trantype'];
					$recno = $_POST['recno'];
					$expdate = $_POST['expdate'];

					$eDate = $this->chgDate($expdate); 

					$sqlln = "SELECT COUNT(lineno_) as COUNT from $table2 WHERE compcode='$company' AND recno='$recno'";

							$result = $this->db->query($sqlln);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					
							$li=intval($row['COUNT'])+1;
							$arrayValue=[$li];
							
						$prepare="INSERT INTO {$table2} (compcode, recno, lineno_, itemcode, uomcode, txnqty, netprice, adduser, adddate, expdate,  batchno, amount, recstatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?,  ?, ?, 'A')";	
						$arrayValue=[
								$this->company,
								$recno, 
								$li,
								$_POST['itemcode'], 
								$_POST['uomcode'],
								$_POST['txnqty'],
								$_POST['netprice'],
								$this->user, 
								$eDate, 
								$_POST['batchno'],
								$_POST['amount']
							];
					

					$sth=$this->db->prepare($prepare);
					$sth->execute($arrayValue);

					$amount="SELECT SUM(amount) AS AMOUNT FROM {$table2} WHERE compcode = '$company' AND recno='$recno' AND recstatus='A'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);
					$arrayValue=[$totalAmount];

					$uptAmount="UPDATE {$table1} SET amount='$totalAmount' 
								WHERE compcode = '$company' AND recno='$recno'";
					$arrayValue=[$totalAmount];
					//echo $this->readableSyntax($uptAmount,$arrayValue);
					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=$totalAmount;

				}else if($this->oper = $_POST['oper']=='edit'){

					$expdate = $_REQUEST['expdate'];

					$eDate = $this->chgDate($expdate);
					

					$prepare="UPDATE {$table2}  SET
					itemcode=?,
					uomcode=?,
					txnqty=?,
					netprice=?,
					upduser= ?,
					upddate=NOW(),
					recstatus='A',
					expdate = ?,
					batchno = ?,
					amount = ?

					WHERE compcode = ? AND recno = ? AND lineno_ = ?";

					$arrayValue=[
								$_POST['itemcode'],
								$_POST['uomcode'],							
								$_POST['txnqty'],
								$_POST['netprice'],
								$this->user,
								$eDate,
								$_POST['batchno'],
								$_POST['amount'],
								$company,
								$_POST['recno'],							
								$_POST['lineno_']
							];						

							//echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);

					$recno = $_REQUEST['recno'];

					$amount="SELECT SUM(amount) AS AMOUNT FROM {$table2} WHERE compcode = '$company' AND recno='$recno' AND recstatus='A'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);
					$arrayValue=[$totalAmount];
					//echo $totalAmount;
					//echo $this->readableSyntax($amount,$arrayValue);

					$uptAmount="UPDATE {$table1} SET amount='$totalAmount' 
								WHERE compcode = '$company' AND recno='$recno'";
					$arrayValue=[$totalAmount];
					//echo $this->readableSyntax($uptAmount,$arrayValue);
					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=$totalAmount;

				}else if($this->oper = $_POST['oper']=='del'){ 
					$lineno_ = $_POST['lineno_'];
					$recno = $_POST['recno'];
					//$itemcode = $_REQUEST['itemcode'];

					$prepare="UPDATE {$table2} SET 
							
							deluser=?,
							deldate=NOW(),
							recstatus='D'
							
					WHERE compcode = ? AND recno = '$recno' AND lineno_ = '$lineno_'";//" AND itemcode = '$itemcode'";
				

					
					$arrayValue=[
								$this->user,
								$company,
							];																	

							//echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);

					$amount="SELECT SUM(amount) AS AMOUNT FROM {$table2} WHERE compcode = '$company' AND recno='$recno' AND recstatus='A'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);
					$arrayValue=[$totalAmount];
					//echo $totalAmount;
					//echo $this->readableSyntax($amount,$arrayValue);

					$uptAmount="UPDATE {$table1} SET amount='$totalAmount' 
								WHERE compcode = '$company' AND recno='$recno'";
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
