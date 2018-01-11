<?php
	
	class receipt_save extends EditTable {
		var $gltranAmount;
		var $cbtranAmount;


		public function isGltranExist($ccode,$glcode,$year,$period){
			
			$query = "select glaccount,actamount".$period." from finance.glmasdtl where compcode='{$this->compcode}' and year='{$year}' and costcode = '{$ccode}' and glaccount = '{$glcode}'";
			echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			$this->gltranAmount = $resultarr["actamount".$period];
			return !empty($resultarr);
		}

		public function isCBtranExist($bankcode,$year,$period){
			
			$query = "select bankcode,actamount".$period." from finance.bankdtl where compcode='{$_SESSION['company']}' and year='{$year}' and bankcode = '{$bankcode}'";
			echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$resultarr = $result->fetch(PDO::FETCH_ASSOC);
			$this->cbtranAmount = $resultarr["actamount".$period];
			return !empty($resultarr);
		}

		public function getcardcent($paymode){

			$query = "select paymode.cardcent from debtor.paymode where compcode='{$_SESSION['company']}' and source='AR' and paymode = '$paymode'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC);
		}

		public function getCbtranTotamt($bankcode,$year,$period){

			$query = "select SUM(amount) AS amount from finance.cbtran where compcode='{$_SESSION['company']}' and bankcode='$bankcode' and year='$year' and period='$period'";
			//echo $query;
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }

			return $result->fetch(PDO::FETCH_ASSOC)['amount'];
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
				
				if($this->oper=='add'){
				
				//1st step add dbacthdr
					if(empty($this->post['outamount'])){
						$this->post['outamount']=$this->post['amount'];
					}else if($this->post['amount']>$this->post['outamount']){
						$this->post['amount'] = $this->post['outamount'];
					}

					$addarrField=['compcode','entrytime','entryuser','adduser','adddate','recstatus','RCOSbalance','outamount'];//extra field
					$addarrValue=[$this->compcode,'NOW()',$this->user,$this->user,'NOW()','A', $this->post['outamount'],$this->post['amount']];
					
					if(!empty($this->sysparam)){
						$tempsysparam = $this->sysparam();
						array_push($addarrField,$this->sysparam['useOn']);
						array_push($addarrValue,$tempsysparam);
					}
				
					$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
					$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

					$this->save($prepare,$arrayValue);

				//2nd step edit till
					$this->oper = 'edit';
					$this->table = 'debtor.till';
					$this->column = ['lastrcnumber'];
					$this->columnid = 'tillcode';
					$this->returnVal = false;

					$updarrField=['compcode','upduser','upddate','recstatus'];//extra field
					$updarrValue=[$this->compcode,$this->user,'NOW()','A'];
					
					$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,!empty($this->filterCol));

					$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
					if(!empty($this->columnid)){
						array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);
					}else{
						array_push($arrayValue,$this->compcode);
					}
					$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;

					$this->save($prepare,$arrayValue);

				//3rd step add gltran
					$this->oper = 'add';
					$this->table = 'finance.gltran';
					$this->yearperiod = 'dbacthdr_entrydate';
					$this->column = ['compcode','adduser','adddate','auditno','lineno_','source','trantype','reference','description','year','period','drcostcode','crcostcode','dracc','cracc','amount','idno','postdate'];
					$this->columnid = 'auditno';

					$addarrField=['compcode','adduser','adddate','auditno','description','idno','postdate'];//extra field
					$addarrValue=[$this->compcode,$this->user,'NOW()',$tempsysparam,$_POST['dbacthdr_remark'],$_POST['dbacthdr_recptno'],'NOW()'];

					if(!empty($this->yearperiod)){
						if(empty($_POST[$this->yearperiod])){
							$date = 'now';
						}else{
							$date = $_POST[$this->yearperiod];
						}
						$yearperiod = $this->getyearperiod($date);
						array_push($addarrField,'year');
						array_push($addarrField,'period');
						array_push($addarrValue,$yearperiod->year);
						array_push($addarrValue,$yearperiod->period);
					}
				
					$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
					$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

					$this->save($prepare,$arrayValue);

				//4th step add glmasdtl
					if(!empty($_POST['dbacthdr_drcostcode']) && $this->isGltranExist($_POST['dbacthdr_drcostcode'],$_POST['dbacthdr_dracc'],$yearperiod->year,$yearperiod->period)){
						$this->oper = 'edit';
						$this->table = 'finance.glmasdtl';
						$this->yearperiod = null;
						$this->column = ['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];
						$this->columnid = null;
						$this->filterCol = ['costcode','glaccount','year'];
						$this->filterVal = [$_POST['dbacthdr_drcostcode'],$_POST['dbacthdr_dracc'],$yearperiod->year];

						$updarrField=['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];//extra field
						$updarrValue=[$this->compcode,$this->user,'NOW()',$_POST['dbacthdr_amount']+$this->gltranAmount,'A'];
						
						$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,!empty($this->filterCol));
						$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
						if(!empty($this->columnid)){
							array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);
						}else{
							array_push($arrayValue,$this->compcode);
						}
						$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;

						$this->save($prepare,$arrayValue);
					}else if(!empty($_POST['dbacthdr_drcostcode'])){
						$this->oper = 'add';
						$this->table = 'finance.glmasdtl';
						$this->yearperiod = null;
						$this->column = ['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];
						$this->columnid = 'compcode';

						$addarrField=['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];//extra field
						$addarrValue=[$this->compcode,$_POST['dbacthdr_drcostcode'],$_POST['dbacthdr_dracc'],$yearperiod->year,$_POST['dbacthdr_amount'],$this->user,'NOW()','A'];
					
						$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
						$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

						$this->save($prepare,$arrayValue);
					}

					if(!empty($_POST['dbacthdr_crcostcode']) && $this->isGltranExist($_POST['dbacthdr_crcostcode'],$_POST['dbacthdr_cracc'],$yearperiod->year,$yearperiod->period)){
						$this->oper = 'edit';
						$this->table = 'finance.glmasdtl';
						$this->yearperiod = null;
						$this->column = ['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];
						$this->columnid = null;
						$this->filterCol = ['costcode','glaccount','year'];
						$this->filterVal = [$_POST['dbacthdr_crcostcode'],$_POST['dbacthdr_cracc'],$yearperiod->year];

						$updarrField=['compcode','upduser','upddate','actamount'.$yearperiod->period,'recstatus'];//extra field
						$updarrValue=[$this->compcode,$this->user,'NOW()',$this->gltranAmount-$_POST['dbacthdr_amount'],'A'];
						
						$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,!empty($this->filterCol));
						$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
						if(!empty($this->columnid)){
							array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);
						}else{
							array_push($arrayValue,$this->compcode);
						}
						$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;

						$this->save($prepare,$arrayValue);
					}else if(!empty($_POST['dbacthdr_crcostcode'])){
						$this->oper = 'add';
						$this->table = 'finance.glmasdtl';
						$this->yearperiod = null;
						$this->column = ['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];
						$this->columnid = 'compcode';

						$addarrField=['compcode','costcode','glaccount','year','actamount'.$yearperiod->period,'adduser','adddate','recstatus'];//extra field
						$addarrValue=[$this->compcode,$_POST['dbacthdr_crcostcode'],$_POST['dbacthdr_cracc'],$yearperiod->year,-$_POST['dbacthdr_amount'],$this->user,'NOW()','A'];
					
						$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
						$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

						$this->save($prepare,$arrayValue);
					}
				}

				// check if its autodebit 
				if($_POST['dbacthdr_paytype']=='#f_tab-debit'){
					$cardcent = $this->getcardcent($_POST['dbacthdr_paymode']);

					//5th step, insert cbtran
					$this->oper = 'add';
					$this->table = 'finance.cbtran';
					$this->yearperiod = null;
					$this->column = ['compcode','bankcode','source','trantype','auditno','postdate','year','period','cheqno','amount','remarks','upduser','upddate','stat'];
					$this->columnid = 'compcode';

					$addarrField=['compcode','bankcode','source','trantype','auditno','postdate','year','period','cheqno','amount','remarks','upduser','upddate','stat'];
					$addarrValue=[$this->compcode,$cardcent['cardcent'],$_POST['dbacthdr_source'],$_POST['dbacthdr_trantype'],$tempsysparam,$_POST['dbacthdr_entrydate'],$yearperiod->year,$yearperiod->period,$_POST['dbacthdr_reference'],$_POST['dbacthdr_amount'],$_POST['dbacthdr_remark'],$this->user,'NOW()','A'];
				
					$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
					$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

					$this->save($prepare,$arrayValue);
				}

				if($_POST['dbacthdr_paytype']=='#f_tab-debit'){
					//6th step, update bankdtl
					$totamt = $this->getCbtranTotamt($cardcent['cardcent'],$yearperiod->year,$yearperiod->period);

					if($this->isCBtranExist($cardcent['cardcent'],$yearperiod->year,$yearperiod->period)){
						$this->oper = 'edit';
						$this->table = 'finance.bankdtl';
						$this->yearperiod = null;
						$this->column = ['upduser','upddate','actamount'.$yearperiod->period];
						$this->columnid = null;
						$this->filterCol = ['bankcode','year'];
						$this->filterVal = [$cardcent['cardcent'],$yearperiod->year];

						$updarrField=['upduser','upddate','actamount'.$yearperiod->period];
						$updarrValue=[$this->user,'NOW()',$this->cbtranAmount+$_POST['dbacthdr_amount']];
						
						$prepare = $this->autoSyntaxUpd($updarrField,$updarrValue,!empty($this->filterCol));
						$arrayValue = $this->arrayValue($updarrField,$updarrValue,false);
						if(!empty($this->columnid)){
							array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);
						}else{
							array_push($arrayValue,$this->compcode);
						}
						$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;

						$this->save($prepare,$arrayValue);
					}else{
						$this->oper = 'add';
						$this->table = 'finance.bankdtl';
						$this->yearperiod = null;
						$this->column = ['compcode','bankcode','year','upduser','upddate','actamount'.$yearperiod->period];
						$this->columnid = null;

						$addarrField=['compcode','bankcode','year','actamount'.$yearperiod->period,'upduser','upddate'];
						$addarrValue=[$this->compcode,$cardcent['cardcent'],$yearperiod->year,$_POST['dbacthdr_amount'],$this->user,'NOW()'];
					
						$prepare=$this->autoSyntaxAdd($addarrField,$addarrValue);
						$arrayValue=$this->arrayValue($addarrField,$addarrValue,false);

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
