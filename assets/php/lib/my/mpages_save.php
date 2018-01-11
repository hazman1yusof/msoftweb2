<?php
	
	class mpages_save{

		protected $db;
	    var $company;
	    var $user;
	    var $oper;
	    var $data_add=[];
	    var $data_del=[];


	    public function __construct(PDO $db){
	        $this->db = $db;
	        session_start();
	        if(!empty($_POST['data_add'])){
	        	$this->data_add=$_POST['data_add'];
	        }
	        if(!empty($_POST['data_del'])){
	        	$this->data_del=$_POST['data_del'];
	        }
	        $this->company = $_SESSION['company'];
	        $this->user = $_SESSION['username'];
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

		public function duplicate($pageid,$groupid){
			$sqlDuplicate="select * from sysdb.mpages_detail where pageid = '{$pageid}' and groupid = '{$groupid}'";
			$result = $this->db->query($sqlDuplicate);if (!$result) { print_r($this->db->errorInfo()); }
			return $result->rowCount();
		}
		
		public function edit_table($commit){
			try{
				if($commit){
					$this->db->beginTransaction();
				}

				foreach ($this->data_add as $obj) {
					if(!$this->duplicate($obj['pageid'],$obj['groupid'])){
						$prepare = "INSERT INTO sysdb.mpages_detail (groupid,pageid,adduser,adddate,compcode) VALUES (?,?,?,NOW(),?)";
						$arrayValue =[$obj['groupid'],$obj['pageid'],$this->user,$this->company];
						$this->save($prepare,$arrayValue);
					}
				}

				foreach ($this->data_del as $obj) {
					if($this->duplicate($obj['pageid'],$obj['groupid'])){
						$prepare = "DELETE FROM sysdb.mpages_detail WHERE groupid=? and pageid=?";
						$arrayValue =[$obj['groupid'],$obj['pageid']];
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
