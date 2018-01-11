<?php
	
	class stockReq_detail_save{

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
			}
		}
		public function sqlsave($prepare,$arrayValue){

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

	    function save(){

			$company=$this->company;
			$user=$this->user;

			try{
				$this->db->beginTransaction();

				if($this->oper=='add'){

					$recno = $_GET['recno'];
					$ivreqno = $_GET['ivreqno'];
					$reqdept = $_GET['reqdept'];

					////1. calculate lineno_ by recno
					$sqlln = "SELECT COUNT(lineno_) as COUNT from material.ivreqdt WHERE compcode='$company' AND recno='$recno'";

					$result = $this->db->query($sqlln);if(!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);

					$li=intval($row['COUNT'])+1;

					///2. insert detail
					$prepare="INSERT INTO material.ivreqdt (compcode, reqdept, ivreqno, lineno_, itemcode, uomcode, pouom, qtyrequest, adduser, adddate, recno, recstatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";	
					$arrayValue=[
						$this->company,
						$reqdept,
						$ivreqno,
						$li,
						$_POST['itemcode'], 
						$_POST['uomcode'],
						$_POST['pouom'],
						$_POST['qtyrequest'],
						$this->user,
						$recno,
						$_POST['recstatus']
					];

					$this->sqlsave($prepare,$arrayValue);

					///3. calculate total amount from detail
					// $amount="SELECT SUM(amount) AS AMOUNT FROM material.ivreqdt WHERE compcode = '$company' AND recno = '$recno' AND recstatus = 'A'";

					// $result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					// $row = $result->fetch(PDO::FETCH_ASSOC);
					// $totalAmount=($row['AMOUNT']);


					// ///4. then update to header
					// $uptAmount="UPDATE material.ivreqhd SET amount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";

					// $sth=$this->db->prepare($uptAmount);
					// $sth->execute($arrayValue);

					// $this->total=$totalAmount;

				}else if($this->oper=='edit'){

					///1. update detail
					$prepare="UPDATE material.ivreqdt  SET itemcode=? ,uomcode=?, pouom=? ,qtyrequest=? ,upduser= ?, recstatus=?, upddate=NOW() WHERE compcode = ? AND recno = ? AND lineno_ = ? ";

					$arrayValue=[
								$_POST['itemcode'],
								$_POST['uomcode'],
								$_POST['pouom'],
								$_POST['qtyrequest'],
								$this->user,
								$_POST['recstatus'],
								$company,
								$_GET['recno'],
								$_POST['lineno_']
							];

					$this->sqlsave($prepare,$arrayValue);


					///2. recalculate total amount

					// $recno = $_GET['recno'];
					// $amount="SELECT SUM(amount) AS AMOUNT FROM material.ivreqdt WHERE compcode = '$company' AND recno = '$recno' AND recstatus = 'A'";

					// $result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					// 		$row = $result->fetch(PDO::FETCH_ASSOC);
					// $totalAmount=($row['AMOUNT']);

					// ///3. update total amount to header
					// $uptAmount="UPDATE material.ivreqhd SET amount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";
					// $arrayValue=[$totalAmount];
					// $sth=$this->db->prepare($uptAmount);
					// $sth->execute($arrayValue);

					// $this->total=$totalAmount;

				}else if($this->oper=='del'){
					// $lineno_ = $_GET['lineno_'];
					$recno = $_GET['recno'];

					///1. update detail
					$prepare="UPDATE material.ivreqdt SET deluser=?, deldate=NOW(), recstatus='DELETE' WHERE compcode = ? AND recno = ? AND lineno_ = ?";
				
					$arrayValue=[
								$this->user,
								$company,
								$_GET['recno'],
								$_GET['lineno_']
							];

					$this->sqlsave($prepare,$arrayValue);

					///2. recalculate total amount
					// $amount="SELECT SUM(amount) AS AMOUNT FROM material.ivreqdt WHERE compcode = '$company' AND recno = '$recno' AND recstatus = 'A'";

					// $result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					// 		$row = $result->fetch(PDO::FETCH_ASSOC);
					// $totalAmount=($row['AMOUNT']);

					// ///3. update total amount to header
					// $uptAmount="UPDATE material.ivreqhd SET amount='$totalAmount' WHERE compcode = '$company' AND recno='$recno'";
					// $arrayValue=[$totalAmount];
					// $sth=$this->db->prepare($uptAmount);
					// $sth->execute($arrayValue);

					// $this->total=$totalAmount;

				}

				$this->db->commit();

			}catch( Exception $e ){
				$this->db->rollback();
				http_response_code(400);
				echo $e->getMessage();
				
			}
		}
	}

?>
