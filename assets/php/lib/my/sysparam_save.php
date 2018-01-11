<?php
	
	class Sys_save{

	    protected $db;
	    var $company;
	    var $user;
	    var $oper;

	    public function __construct(PDO $db)
	    {
	        $this->db = $db;
	        $this->oper = $_GET['oper'];
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
			$company=$this->company;
			$user=$this->user;

			try{
				$this->db->beginTransaction();

				if($this->oper=='add'){
					
							$prepare="INSERT INTO sysdb.company 
							(compcode,name,adduser,adddate,recstatus,address1,bmppath1,ipaddress,logo1) 
							VALUES (?,?,?,NOW(),'A',?,?,?,?)";
							$arrayValue=[
								$_POST['compcode'],
								$_POST['name'],
								$this->user,
								$_POST['address1'],							
								$_POST['bmppath1'],
								$_POST['ipaddress'],
								$_POST['logo1']
							];

							//echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);
					

				}else if($this->oper=='edit'){

					$prepare="UPDATE sysdb.company SET

					name=?,
					upduser=?,
					upddate=NOW(),
					recstatus='A',
					address1=?,
					bmppath1=?,
					ipaddress=?,
					logo1=?

					WHERE compcode=?";

					$arrayValue=[
								$_POST['name'],
								$this->user,
								$_POST['address1'],							
								$_POST['bmppath1'],
								$_POST['ipaddress'],
								$_POST['logo1'],
								$_POST['compcode']
							];						

							//echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);
					
				}else if($this->oper=='del'){
					
					$prepare="UPDATE sysdb.company SET 
							
							deluser=?,
							deldate=NOW(),
							recstatus='D'
							
					WHERE compcode=?";
				

					
					$arrayValue=[
								$this->user,$_POST['compcode']
							];																	

							echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);

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

