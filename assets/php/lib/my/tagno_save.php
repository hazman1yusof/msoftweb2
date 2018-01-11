<?php
	
	class tagno_save{
		var $oper;
		var $seldata;
		var $responce;

		public function __construct(PDO $db){
			include_once('sschecker.php');
			$this->db = $db;
			$this->oper = $_GET['oper'];
			$this->seldata = $_POST['seldata'];
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
			$seldata = $this->seldata;
			//$tempobj = $this->getyearperiod($seldata['actdate']);

			try{
				if($commit){
					$this->db->beginTransaction();
				}

				foreach ($seldata as $value) {

				    ////2. insert into faregister
				    $assetcode = ( $value['assetcode'] );
				    $assettype = ( $value['assettype'] );
				    $idno = ( $value['idno'] );
				    $description = ( $value['description'] );
				    $deptcode = ( $value['deptcode'] );
				    $loccode = ( $value['loccode'] );
				    $suppcode = ( $value['suppcode'] );
				    $purordno = ( $value['purordno'] );
                    $delordno = ( $value['delordno'] );
                    $delorddate = ( $value['delorddate'] );
                    $itemcode = ( $value['itemcode'] );
                    $invno = ( $value['invno'] );
                    $invdate= ( $value['invdate'] );
                    $purdate = ( $value['purdate'] );
                    $purprice = ( $value['purprice'] );
                    $origcost = ( $value['origcost'] );
                    $qty= ( $value['qty'] );
                    $lstytddep = ( $value['lstytddep'] );
                    $cuytddep = ( $value['cuytddep'] );
                    $recstatus = ( $value['recstatus'] );
                    $individualtag= ( $value['individualtag'] );
                    $statdate= ( $value['statdate'] );
                    $trantype = ( $value['trantype'] );
                    $nprefid = ( $value['nprefid'] );
                    $trandate = ( $value['trandate'] );
                    $adduser = ( $value['adduser'] );
                    $adddate = ( $value['adddate'] );

                    ////1. select facode tagnextno
                    $queryGETTaxno = "SELECT tagnextno FROM finance.facode WHERE compcode = '{$_SESSION['company']}' AND assettype = '$assettype'";
                    $result = $this->db->query($queryGETTaxno);if (!$result) { print_r($this->db->errorInfo()); }
							$row = $result->fetch(PDO::FETCH_ASSOC);
					$assetno=($row['tagnextno']);
					$tagnextno =intval($row['tagnextno'])+1;
                    /*$assetno=intval($row['tagnextno']);*/
                   $assetno2 = str_pad($assetno,6,"0",STR_PAD_LEFT);

                                       ////2. insert into faregister
				    $prepare = "INSERT INTO finance.faregister (compcode, assetcode, assettype,assetno,description,deptcode,loccode,suppcode,purordno,delordno ,delorddate,itemcode,invno,invdate,purdate,purprice,origcost,qty,lstytddep,cuytddep,recstatus,individualtag,statdate,trantype,nprefid,trandate,adduser,adddate) VALUES (?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";

					$arrayValue = array($_SESSION['company'],$assetcode,$assettype,$assetno2,$description,$deptcode,$loccode,$suppcode,$purordno,$delordno,$delorddate,$itemcode,$invno,$invdate,$purdate,$purprice,$origcost,$qty,$lstytddep,$cuytddep,$recstatus,$individualtag,$statdate,$trantype,$nprefid,$trandate,$_SESSION['username']);

					$this->save($prepare,$arrayValue);


					/////3. update fatemp compcode ='xx'
					/////3. delete from fatemp

					$prepare="DELETE FROM finance.fatemp WHERE idno=?";
					//$prepare = "UPDATE finance.fatemp SET compcode=? WHERE idno=?";

						$arrayValue = array($idno);

						$this->save($prepare,$arrayValue);

					////4. update facode tagnextno
					$queryGETTaxno = "UPDATE finance.facode SET tagnextno='$tagnextno'  WHERE compcode = ? AND assettype = ?";
						$arrayValue = array($_SESSION['company'], $assettype);
						$this->save($queryGETTaxno,$arrayValue);

				}

				
				//if($this->oper=='add'){


					/*$prepare = "INSERT INTO finance.faregister (compcode,assetcode, assettype, assetno, description, serialno, lotno, casisno, engineno, deptcode, loccode, suppcode, purordno, delordno, delorddate, itemno, itemcode, invno, invdate, purdate, purprice, origcost, insval, qty, startdepdate, currentcost, lstytddep, cuytddep, recstatus, individualtag, statdate, trantype, trandate, lstdepdate, nprefid, adduser, adddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

					$arrayValue = array($_SESSION['company'], $seldata['assetcode'], $seldata['assettype'], $seldata['assetno'], $seldata['description'], $seldata['serialno'], $seldata['lotno'], $seldata['casisno'], $seldata['engineno'], $seldata['deptcode'], $seldata['loccode'], $seldata['suppcode'], $seldata['purordno'], $seldata['delordno'], $seldata['delorddate'], $seldata['itemno'], $seldata['itemcode'], $seldata['invno'], $seldata['invdate'], $seldata['purdate'], $seldata['purprice'],$seldata['origcost'], $seldata['insval'], $seldata['qty'], $seldata['startdepdate'], $seldata['currentcost'], $seldata['lstytddep'], $seldata['cuytddep'], $seldata['recstatus'], $seldata['individualtag'], $seldata['statdate'], $seldata['trantype'], $seldata['trandate'], $seldata['lstdepdate'], $seldata['nprefid'],$_SESSION['username'], $seldata['adddate']);

					$this->save($prepare,$arrayValue);*/
			//	}
					



					
				

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
