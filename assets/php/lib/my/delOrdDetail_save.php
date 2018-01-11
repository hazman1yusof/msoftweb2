<?php
	
	class delOrdDetail_save{

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

	    public function get_draccno($itemcode){

	    	$query = "select category.stockacct from material.category,material.product where category.compcode = '{$_SESSION['company']}' AND product.itemcode = '{$itemcode}' AND category.catcode = product.productcat";

	    	// echo $query;
			
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			return $resultarr['stockacct'];
		}

		public function get_drccode($deldept){

	    	$query = "select department.costcode from sysdb.department where compcode = '{$_SESSION['company']}' AND deptcode = '{$deldept}'";

	    	// echo $query;
			
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			return $resultarr['costcode'];
		}

		public function get_craccno(){

	    	$query = "select pvalue2 from sysdb.sysparam where compcode = '{$_SESSION['company']}' AND source = 'AP' AND trantype = 'ACC'";
			
	    	//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			return $resultarr['pvalue2'];
		}

		public function get_crccode(){

	    	$query = "select pvalue1 from sysdb.sysparam where compcode = '{$_SESSION['company']}' AND source = 'AP' AND trantype = 'ACC'";
			
	    	//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			return $resultarr['pvalue1'];
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

					$draccno = $this->get_draccno($_POST['itemcode']);
					$drccode = $this->get_drccode($_GET['deldept']);
					$craccno = $this->get_craccno();
					$crccode = $this->get_crccode();

					$recno = $_GET['recno'];
					$suppcode = $_GET['suppcode'];
					$trandate = $_GET['trandate'];
					$deldept = $_GET['deldept'];
					$deliverydate = $_GET['deliverydate'];

					////1. calculate lineno_ by recno
					$sqlln = "SELECT COUNT(lineno_) as COUNT from material.delorddt WHERE compcode='$company' AND recno='$recno'";

					$result = $this->db->query($sqlln);if(!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);

					$li=intval($row['COUNT'])+1;

					///2. insert detail
					//echo $this->chgDate($_POST['expdate']);
					 $prepare="INSERT INTO material.delorddt (compcode, recno, lineno_, pricecode, itemcode, uomcode, pouom, suppcode, trandate, deldept, deliverydate, qtyorder, qtydelivered,qtytag, unitprice, taxcode, perdisc, amtdisc, amtslstax, netunitprice,amount,totamount, draccno,drccode,craccno,crccode, adduser, adddate, expdate, batchno, recstatus, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(),?, ?, ?, ?)";
					$arrayValue=[
						$this->company,
						$recno,
						$li,
						$_POST['pricecode'], 
						$_POST['itemcode'], 
						$_POST['uomcode'],
						$_POST['pouom'],
						$suppcode,
						$trandate,
						$deldept,
						$deliverydate,
						$_POST['qtyorder'],
						$_POST['qtydelivered'],
						$_POST['qtyOutstand'],
						$_POST['unitprice'],
						$_POST['taxcode'],
						$_POST['perdisc'],
						$_POST['amtdisc'],
						$_POST['tot_gst'],
						$_GET['netunitprice'],
						$_GET['amount'],
						$_POST['totamount'],
						$draccno,
						$drccode,
						$craccno,
						$crccode,
						$this->user,
						$this->chgDate($_POST['expdate']),
						$_POST['batchno'],
						'OPEN',
						$_GET['remarks'] 
					];

					$this->sqlsave($prepare,$arrayValue);

					///3. calculate total amount from detail
					$amount="SELECT SUM(totamount) AS AMOUNT FROM material.delorddt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);

					//calculate tot gst from detail
					$amount="SELECT SUM(amtslstax) AS tot_gst FROM material.delorddt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);
					$tot_gst=($row['tot_gst']);



					///4. then update to header
					$uptAmount="UPDATE material.delordhd SET totamount='$totalAmount', subamount='$totalAmount', TaxAmt='$tot_gst' WHERE compcode = '$company' AND recno='$recno'";

					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=$totalAmount;

				}else if($this->oper=='edit'){

					///1. update detail
					 $prepare="UPDATE material.delorddt  SET pricecode =?, itemcode=? , uomcode=? , pouom=?, qtyorder=?, qtydelivered=?, unitprice=? ,taxcode=?, perdisc=?, amtdisc=?, amtslstax=?, netunitprice=?, amount=?, totamount=?, upduser= ?, upddate=NOW(), expdate=?,  batchno=? , remarks=? WHERE compcode = ? AND recno = ? AND lineno_ = ?";

					$arrayValue=[
								$_POST['pricecode'],
								$_POST['itemcode'],
								$_POST['uomcode'],
								$_POST['pouom'],
								$_POST['qtyorder'],
								$_POST['qtydelivered'],
								$_POST['unitprice'],
								$_POST['taxcode'],
								$_POST['perdisc'],
								$_POST['amtdisc'],
								$_POST['tot_gst'],
								$_GET['netunitprice'],
								$_GET['amount'],
								$_POST['totamount'],
								$this->user,
								$this->chgDate($_POST['expdate']),
								$_POST['batchno'],
								$_GET['remarks'],
								$company,
								$_GET['recno'],
								$_POST['lineno_']
							];

					$this->sqlsave($prepare,$arrayValue);


					///2. recalculate total amount
					$recno = $_GET['recno'];
					$amount="SELECT SUM(totamount) AS AMOUNT FROM material.delorddt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);

					//calculate tot gst from detail
					$amount="SELECT SUM(amtslstax) AS tot_gst FROM material.delorddt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);
					$tot_gst=($row['tot_gst']);

					///3. update total amount to header
					$uptAmount="UPDATE material.delordhd SET totamount='$totalAmount', subamount='$totalAmount', TaxAmt='$tot_gst' WHERE compcode = '$company' AND recno='$recno'";
					$arrayValue=[$totalAmount];
					$sth=$this->db->prepare($uptAmount);
					$sth->execute($arrayValue);

					$this->total=$totalAmount;

				}else if($this->oper=='del'){
					$recno=$_GET['recno'];

					///1. update detail
					$prepare="UPDATE material.delorddt SET deluser=?, deldate=NOW(), recstatus='DELETE' WHERE compcode = ? AND recno = ? AND lineno_ = ?";
				
					$arrayValue=[
								$this->user,
								$company,
								$_GET['recno'],
								$_GET['lineno_']
							];

					$this->sqlsave($prepare,$arrayValue);

					///2. recalculate total amount
					$amount="SELECT SUM(totamount) AS AMOUNT FROM material.delorddt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$totalAmount=($row['AMOUNT']);

					//calculate tot gst from detail
					$amount="SELECT SUM(amtslstax) AS tot_gst FROM material.delorddt WHERE compcode = '$company' AND recno = '$recno' AND recstatus != 'DELETE'";

					$result = $this->db->query($amount);if (!$result) { print_r($this->db->errorInfo()); }
					$row = $result->fetch(PDO::FETCH_ASSOC);
					$tot_gst=($row['tot_gst']);

					///3. update total amount to header
					$uptAmount="UPDATE material.delordhd SET totamount='$totalAmount', subamount='$totalAmount', TaxAmt='$tot_gst' WHERE compcode = '$company' AND recno='$recno'";
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
