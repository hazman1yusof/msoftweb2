<?php
	
	class purReq_detail_save{

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
					$reqdept = $_GET['reqdept'];
					$purreqno = $_GET['purreqno'];

					////1. calculate lineno_ by recno
					$sqlln = "SELECT COUNT(lineno_) as COUNT from material.purreqdt WHERE compcode='$company' AND recno='$recno'";

					$result = $this->db->query($sqlln);if(!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);

					$li=intval($row['COUNT'])+1;

					///2. insert detail
					$prepare="INSERT INTO material.purreqdt (compcode, reqdept, purreqno, recno, lineno_, pricecode, itemcode, uomcode, qtyrequest, unitprice, taxcode, perdisc, amtdisc, amtslstax, amount, recstatus, remarks, adduser, adddate) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW() )"; // taxcode,, amtslstax qtydelivered,
					$arrayValue=[
						$this->company,
						$reqdept,
						$purreqno,
						$recno,
						$li,
						$_POST['pricecode'], 
						$_POST['itemcode'], 
						$_POST['uomcode'],
						$_POST['qtyrequest'],
						$_POST['unitprice'],
						$_POST['taxcode'],
						$_POST['perdisc'],
						$_POST['amtdisc'],
						$_POST['tot_gst'],
						$_POST['amount'],
						'OPEN',
						$_GET['remarks'],
						$this->user,
					];

					$this->sqlsave($prepare,$arrayValue);
					

					

					///3. calculate total amount from detail
					$amount="SELECT SUM(amount) AS AMOUNT FROM material.purreqdt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE' ";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);


					///4. then update to header
					$uptAmount="UPDATE material.purreqhd SET totamount='$totalAmount', subamount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";

					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=round($totalAmount, 4);

				}else if($this->oper=='edit'){

					///1. update detail
					$prepare="UPDATE material.purreqdt SET pricecode =?, itemcode=? ,uomcode=?, qtyrequest=?, unitprice=?, taxcode=?, amtdisc=?, amount=?, remarks=?, upduser= ?, upddate=NOW()  WHERE compcode = ? AND recno = ? AND lineno_ = ?";

					$arrayValue=[
								$_POST['pricecode'],
								$_POST['itemcode'],
								$_POST['uomcode'],
								$_POST['qtyrequest'],
								//$_POST['qtydelivered'],
								$_POST['unitprice'],
								//$_POST['perdisc'],
								$_POST['taxcode'],
								$_POST['amtdisc'],
								//$_POST['tot_gst'],
								$_POST['amount'],
								$_GET['remarks'],
								$this->user,
								$company,
								$_GET['recno'],
								$_POST['lineno_'],
								
							];

					$this->sqlsave($prepare,$arrayValue);


					///2. recalculate total amount
					$recno = $_GET['recno'];
					$amount="SELECT SUM(amount) AS AMOUNT FROM material.purreqdt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE' ";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);

					///3. update total amount to header
					$uptAmount="UPDATE material.purreqhd SET totamount='$totalAmount', subamount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";
					$arrayValue=[$totalAmount];
					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=round($totalAmount, 4);

				}else if($this->oper=='del'){
					$recno=$_GET['recno'];

					///1. update detail
					$prepare="UPDATE material.purreqdt SET deluser=?, deldate=NOW(), recstatus='DELETE' WHERE compcode = ? AND recno = ? AND lineno_ = ?";
				
					$arrayValue=[
								$this->user,
								$company,
								$_GET['recno'],
								$_GET['lineno_']
							];

					$this->sqlsave($prepare,$arrayValue);

					///2. recalculate total amount
					$amount="SELECT SUM(amount) AS AMOUNT FROM material.purreqdt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE' ";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);

					///3. update total amount to header
					$uptAmount="UPDATE material.purreqhd SET totamount='$totalAmount', subamount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";
					$arrayValue=[$totalAmount];
					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=round($totalAmount, 4);

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
