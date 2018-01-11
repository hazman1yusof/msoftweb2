<?php
	
	class group_maintenance_save{

		protected $db;
	    var $company;
	    var $user;
	    var $oper;

	    public function __construct(PDO $db){
	        $this->db = $db;
	        $this->oper = $_GET['oper'];
	        session_start();
	        $this->company = $_SESSION['company'];
	        $this->user = $_SESSION['username'];
	    }

		public function directSave($sql){

			/////////////////check sytax//////////////////////////////
			//echo $prepare;print_r($arrayValue);
			echo $sql.'<br>';
			//////////////////////////////////////////////////////////

			$res=$this->db->query($sql);
			if (!$res) {
				echo '{"msg":"failure"}<br>';
				throw new Exception($sql);
			}else{
				echo '{"msg":"success"}<br>';
			}
		}
		
		public function edit_table($commit){
			$canrun=$_POST['canrun'];
			$yesall=$_POST['yesall'];
			$canrunold=$_POST['canrunold'];
			$yesallold=$_POST['yesallold'];
		    $groupid=$_POST['groupid'];
		    $programid=$_POST['programid'];
		    $programmenu=$_POST['programmenu'];
		    $lineno=$_POST['lineno'];

			try{
				if($commit){
					$this->db->beginTransaction();
				}
				
				if($canrun=="Yes" && $canrunold=="No"){//insert into groupacc
					$sql1 = "INSERT INTO sysdb.groupacc (compcode,groupid,programmenu,lineno,canrun) VALUES ('{$_SESSION['company']}', '{$groupid}', '{$programmenu}', '{$lineno}','1')";
					$this->directSave($sql1);

					if($yesall=="Yes" && $yesallold=="No"){//update groupacc and insert child
						$sql2 = "UPDATE sysdb.groupacc SET yesall=1 WHERE lineno='{$lineno}' and groupid='{$groupid}' and programmenu='{$programmenu}' and compcode='{$_SESSION['company']}'";
						$sql3 = "INSERT INTO sysdb.groupacc (compcode,groupid,programmenu,lineno,canrun) SELECT compcode,'{$groupid}',programmenu,lineno,'1' FROM sysdb.programtab WHERE programmenu='{$programid}' AND compcode='{$_SESSION['company']}'";


						$this->directSave($sql2);
						$this->directSave($sql3);
					}

				}else if($canrun=="Yes" && $canrunold=="Yes"){//check yesall

					if($yesall=="Yes" && $yesallold=="No"){//update groupacc and insert child

						$sql1 = "UPDATE sysdb.groupacc SET yesall=1 WHERE lineno='{$lineno}' and groupid='{$groupid}' and programmenu='{$programmenu}' and compcode='{$_SESSION['company']}'";
						$sql2 = "INSERT INTO sysdb.groupacc(compcode,groupid,programmenu,lineno,canrun) SELECT compcode,'{$groupid}',programmenu,lineno,'1' FROM sysdb.programtab WHERE programmenu='{$programid}' AND compcode='{$_SESSION['company']}'";

					}else if($yesall=="No" && $yesallold=="Yes"){//update from groupacc

						$sql1 = "UPDATE sysdb.groupacc SET yesall=0 WHERE lineno='{$lineno}' and groupid='{$groupid}' and programmenu='{$programmenu}' and compcode='{$_SESSION['company']}'";
						$sql2 = "DELETE FROM sysdb.groupacc WHERE groupid='{$groupid}' and programmenu='{$programid}' and compcode='{$_SESSION['company']}'";

					}

					$this->directSave($sql1);
					$this->directSave($sql2);

				}else if($canrun=="No" && $canrunold=="Yes"){//delete from groupacc
					$sql1 = "DELETE FROM sysdb.groupacc WHERE lineno='{$lineno}' and groupid='{$groupid}' and programmenu='{$programmenu}' and compcode='{$_SESSION['company']}'";
					$sql2 = "DELETE FROM sysdb.groupacc WHERE groupid='{$groupid}' and programmenu='{$programid}' and compcode='{$_SESSION['company']}'";

					$this->directSave($sql1);
					$this->directSave($sql2);
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
