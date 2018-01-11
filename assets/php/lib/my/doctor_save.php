<?php
	
	class Doctor_save{

	    protected $db;
	    var $company;
	    var $user;
	    var $oper;
	    var $columnid;

	    public function __construct(PDO $db)
	    {
	        $this->db = $db;
	        $this->oper = $_GET['oper'];
	        session_start();
	        $this->company = $_SESSION['company'];
	        $this->user = $_SESSION['username'];
	        //set tableid
			$this->columnid = $get['table_id'];
	    }

		private function readableSyntax($prepare,array $arrayValue){
			foreach($arrayValue as $val){
				$prepare=preg_replace("/\?/", "'".$val."'", $prepare,1);
			}
			return $prepare."\r\n";
		}



		private function duplicate($code,$table,$codetext){
			$sqlDuplicate="select $code from $table where $code = '$codetext'";
			$result = $this->db->query($sqlDuplicate);if (!$result) { print_r($this->db->errorInfo()); }
			return $result->rowCount();
		}

		

	    function save(){

	    	$table1='hisdb.doctor';
			$table2='hisdb.apptresrc';

			$company=$this->company;
			$user=$this->user;

			try{
				$this->db->beginTransaction();

				if($this->oper=='add'){
					$prepare = "INSERT INTO {$table1}
							(compcode,doctorcode,doctorname,department,disciplinecode,specialitycode, doctype, creditorcode,resigndate,classcode,admright,appointment,intervaltime, company,address1,address2,address3,postcode,statecode,countrycode,gstno,res_tel,tel_hp,off_tel,operationtheatre,recstatus,adduser,ADDDATE) 
							
							VALUES 

							(?,?,?,?,?,?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'A',?,NOW())";

							$arrayValue=[
								$this->company,
								$_POST['doctorcode'],
								$_POST['doctorname'],
								$_POST['department'],
								$_POST['disciplinecode'],
								$_POST['specialitycode'],
								$_POST['doctype'],
								$_POST['creditorcode'],
								$_POST['resigndate'],
								$_POST['classcode'],
								$_POST['admright'],
								$_POST['appointment'],
								$_POST['intervaltime'],
								$_POST['company'],
								$_POST['address1'],
								$_POST['address2'],
								$_POST['address3'],
								$_POST['postcode'],
								$_POST['statecode'],
								$_POST['countrycode'],
								$_POST['gstno'],
								$_POST['res_tel'],
								$_POST['tel_hp'],
								$_POST['off_tel'],
								$_POST['operationtheatre'],
								
								$this->user
							];

							echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);

							$doctor = $_REQUEST['doctorcode'];
							$doctorname = $_REQUEST['doctorname'];
							$appointment = $_REQUEST['appointment'];
							$intervaltime = $_REQUEST['intervaltime'];


							$prepare="INSERT INTO {$table2}  
							(compcode, resourcecode, description, type, recstatus, intervaltime, adduser,ADDDATE) 
							VALUES 
							(?, ?, ?, 'doc', 'A',?,?, NOW())";

							$arrayValue=[
								$this->company,
								$doctor,
								$doctorname,
								$intervaltime,
								$this->user

							];

							echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);

				}	
				
				else if($this->oper=='edit'){
					$prepare = "UPDATE {$table1}  
								SET doctorname=?, department=?, disciplinecode=?, specialitycode=?, doctype=?, creditorcode=?, resigndate=?, classcode=?, admright=?, appointment=?, intervaltime=?, company=?, address1=?, address2=?, address3=?, postcode=?, statecode=?, countrycode=?, gstno=?, res_tel=?, tel_hp=?, off_tel=?, operationtheatre=?, recstatus='A', upduser= ?, upddate=NOW()
								WHERE compcode = ? AND doctorcode=? ";

					$arrayValue=[
								$_POST['doctorname'],
								$_POST['department'],
								$_POST['disciplinecode'],
								$_POST['specialitycode'],
								$_POST['doctype'],
								$_POST['creditorcode'],
								$_POST['resigndate'],
								$_POST['classcode'],
								$_POST['admright'],
								$_POST['appointment'],
								$_POST['intervaltime'],
								$_POST['company'],
								$_POST['address1'],
								$_POST['address2'],
								$_POST['address3'],
								$_POST['postcode'],
								$_POST['statecode'],
								$_POST['countrycode'],
								$_POST['gstno'],
								$_POST['res_tel'],
								$_POST['tel_hp'],
								$_POST['off_tel'],
								$_POST['operationtheatre'],
								$this->user,
								$this->company,
								$_POST['doctorcode'],
								
							];

						echo $this->readableSyntax($prepare,$arrayValue);
						$sth=$this->db->prepare($prepare);
						$sth->execute($arrayValue);

						
						$doctorname = $_REQUEST['doctorname'];
						$intervaltime = $_REQUEST['intervaltime'];
						$appointment = $_REQUEST['appointment'];
						$doctor = $_REQUEST['doctorcode'];


						if ($appointment === '0'){
							$prepare="UPDATE {$table2} SET
										description='$doctorname', intervaltime='$intervaltime', recstatus='D', upduser='$user',upddate=NOW()
										WHERE 
										compcode='$company' AND resourcecode='$doctor'";


										$arrayValue=[
												$doctorname,
												$intervaltime,
												$user,
												$company,
												$doctor
												];

												echo $this->readableSyntax($prepare,$arrayValue);
												$sth=$this->db->prepare($prepare);
												$sth->execute($arrayValue);
						}
						
								
						else{
							$prepare="UPDATE {$table2} SET
										description='$doctorname', intervaltime='$intervaltime', recstatus='A', upduser='$user',upddate=NOW()
										WHERE 
										compcode='$company' AND resourcecode='$doctor'";


										$arrayValue=[
												$doctorname,
												$intervaltime,
												$user,
												$company,
												$doctor
												];

												echo $this->readableSyntax($prepare,$arrayValue);
												$sth=$this->db->prepare($prepare);
												$sth->execute($arrayValue);
								
								}
				}
			else if($this->oper=='del'){
						$doctor = $_REQUEST['doctorcode'];

								$prepare = "UPDATE {$table1}  SET 
										recstatus='D',
										deluser='$user',
										deldate=NOW()

										WHERE compcode = '$company' AND doctorcode='$doctor'";

								$arrayValue=[
											
											$this->user,
											$this->company,
											$doctor
											
											];

								echo $this->readableSyntax($prepare,$arrayValue);
								$sth=$this->db->prepare($prepare);
								$sth->execute($arrayValue);

					}
				
				$this->db->commit();
				
				
				
			}	catch( Exception $e ){
				$this->db->rollback();
				http_response_code(400);
				echo $e->getMessage();
			}
		}
	}