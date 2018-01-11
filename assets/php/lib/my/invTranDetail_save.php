<?php
	
	class invTranDetail_save{

	    protected $db;
	    var $company;
	    var $user;
	    var $oper;
	    var $total;
 
	    public function __construct(PDO $db)
	    {
	        $this->db = $db;
	        $this->oper = $_POST['oper'];
	        $this->total;
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
			}else{
				return 'NULL';
			}
		}
		public function sqlsave($prepare,$arrayValue){

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

	    function save(){

			$company=$this->company;
			$user=$this->user;

			try{
				$this->db->beginTransaction();

				if($this->oper=='add'){

					$recno = $_GET['recno'];

					////1. calculate lineno_ by recno
					$sqlln = "SELECT COUNT(lineno_) as COUNT from material.ivtmpdt WHERE compcode='$company' AND recno='$recno'";

					$result = $this->db->query($sqlln);if(!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);

					$li=intval($row['COUNT'])+1;

					///2. insert detail
					$prepare="INSERT INTO material.ivtmpdt (compcode, recno, lineno_, itemcode, uomcode, txnqty, netprice, adduser, adddate, expdate,  batchno, amount, recstatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)";	
					$arrayValue=[
						$this->company,
						$recno,
						$li,
						$_POST['itemcode'], 
						$_POST['uomcode'],
						$_POST['txnqty'],
						$_POST['netprice'],
						$this->user,
						$this->chgDate($_POST['expdate']),
						$_POST['batchno'],
						$_POST['amount'],
						'A'
					];

					$this->sqlsave($prepare,$arrayValue);

					///3. calculate total amount from detail
					$amount="SELECT SUM(amount) AS AMOUNT FROM material.ivtmpdt WHERE compcode = '$company' AND recno = '$recno' AND recstatus = 'A'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);


					///4. then update to header
					$uptAmount="UPDATE material.ivtmphd SET amount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";

					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=$totalAmount;

				}else if($this->oper=='edit'){

					///1. update detail
					$prepare="UPDATE material.ivtmpdt  SET itemcode=? ,uomcode=? ,txnqty=?, netprice=? ,expdate=?, batchno=?, amount=?, upduser= ?, upddate=NOW() WHERE compcode = ? AND recno = ? AND lineno_ = ?";

					$arrayValue=[
								$_POST['itemcode'],
								$_POST['uomcode'],
								$_POST['txnqty'],
								$_POST['netprice'],
								$this->chgDate($_POST['expdate']),
								$_POST['batchno'],
								$_POST['amount'],
								$this->user,
								$company,
								$_GET['recno'],
								$_POST['lineno_']
							];

					$this->sqlsave($prepare,$arrayValue);


					///2. recalculate total amount
					$recno = $_GET['recno'];
					$amount="SELECT SUM(amount) AS AMOUNT FROM material.ivtmpdt WHERE compcode = '$company' AND recno = '$recno' AND recstatus = 'A'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);

					///3. update total amount to header
					$uptAmount="UPDATE material.ivtmphd SET amount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";
					$arrayValue=[$totalAmount];
					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=$totalAmount;

				}else if($this->oper=='del'){
					$recno=$_GET['recno'];

					///1. update detail
					$prepare="UPDATE material.ivtmpdt SET deluser=?, deldate=NOW(), recstatus='D' WHERE compcode = ? AND recno = ? AND lineno_ = ?";
				
					$arrayValue=[
								$this->user,
								$company,
								$_GET['recno'],
								$_GET['lineno_']
							];

					$this->sqlsave($prepare,$arrayValue);

					///2. recalculate total amount
					$amount="SELECT SUM(amount) AS AMOUNT FROM material.ivtmpdt WHERE compcode = '$company' AND recno = '$recno' AND recstatus = 'A'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);

					///3. update total amount to header
					$uptAmount="UPDATE material.ivtmphd SET amount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";
					$arrayValue=[$totalAmount];
					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=$totalAmount;

				}
				$this->db->commit();
				echo $this->total;

			}catch( Exception $e ){
				$this->db->rollback();
				http_response_code(400);
				echo $e->getMessage();
				
			}
		}
	}

?>
