<?php
	
	class chat{

		protected $db;
	    var $company;
	    var $user;
	    var $oper;
	    var $date=null;
	    var $responce;
	    var $msto;
	    var $remark;


	    public function __construct(PDO $db){
	        $this->db = $db;
	        session_start();
	        if(!empty($_GET['date'])){
	        	$this->date=$_GET['date'];
	        }
	        if(!empty($_POST['msto'])){
	        	$this->msto=$_POST['msto'];
	        }
	        if(!empty($_POST['remark'])){
	        	$this->remark=$_POST['remark'];
	        }
	        $this->oper = $_GET['oper'];
	        $this->company = $_SESSION['company'];
	        $this->user = $_SESSION['username'];
			$this->responce = new stdClass();
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
			try{
				if($commit){
					$this->db->beginTransaction();
				}

				if($this->oper == 'add'){

					$prepare = "INSERT INTO sysdb.msmessanger (compcode,type,msfrom,msto,datesend,remark) VALUES (?,?,?,?,NOW(),?)";
					$arrayValue = [$this->company,'chat',$this->user,$this->msto,$this->remark];

					$this->save($prepare,$arrayValue);

				}else if($this->oper == 'get'){
					if(empty($_GET['date'])){

						$prepare = "SELECT msmessanger.datesend,msmessanger.msfrom,msmessanger.msto,msmessanger.remark,users.name FROM sysdb.msmessanger LEFT JOIN sysdb.users ON msmessanger.msfrom = users.username  WHERE msmessanger.msto IN ('all','{$this->user}') OR msfrom = '{$this->user}' ORDER BY msmessanger.datesend DESC LIMIT 0,20";

					}else{

						$prepare = "SELECT msmessanger.datesend,msmessanger.msfrom,msmessanger.msto,msmessanger.remark,users.name FROM sysdb.msmessanger LEFT JOIN sysdb.users ON msmessanger.msfrom = users.username  WHERE  msmessanger.datesend > '{$this->date}' AND (msmessanger.msto IN ('all','{$this->user}') OR msfrom = '{$this->user}') ORDER BY msmessanger.datesend DESC LIMIT 0,20";
					}

					$result = $this->db->query($prepare);if (!$result) { print_r($this->db->errorInfo()); }


					$i=0;
					while($row = $result->fetch(PDO::FETCH_OBJ)) {
						$this->responce->rows[$i]=$row;
						$i++;
					}

					$this->responce->sql = $prepare;
					return json_encode($this->responce);

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
