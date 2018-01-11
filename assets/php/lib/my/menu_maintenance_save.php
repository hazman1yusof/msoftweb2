<?php
	
	class menu_maintenance_save{

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

		public function readableSyntax($prepare,array $arrayValue){
			foreach($arrayValue as $val){
				$prepare=preg_replace("/\?/", "'".$val."'", $prepare,1);
			}
			return $prepare."\r\n";
		}

		public function duplicate($code,$table,$codetext){
			$sqlDuplicate="select $code from $table where $code = '$codetext'";
			$result = $this->db->query($sqlDuplicate);if (!$result) { print_r($this->db->errorInfo()); }
			return $result->rowCount();
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

		public function getmaxlineno(){
			$sql="SELECT max(lineno)+1 AS maxlineno FROM sysdb.programtab WHERE compcode='{$this->company}' and programmenu='{$_POST['programmenu']}'";
			$result = $this->db->query($sql);if (!$result) { print_r($this->db->errorInfo()); }
			return $result->fetch(PDO::FETCH_ASSOC)['maxlineno'];
		}

		public function getchildno(){
			$sql="SELECT count(programid) as child FROM sysdb.programtab where programmenu='{$_POST['programid']}'";
			echo $sql;
			$result = $this->db->query($sql);if (!$result) { print_r($this->db->errorInfo()); }
			return $result->fetch(PDO::FETCH_ASSOC)['child'];
		}

		public function directSave($sql){

			/////////////////check sytax//////////////////////////////
			//echo $prepare;print_r($arrayValue);
			echo $sql;
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

			try{
				if($commit){
					$this->db->beginTransaction();
				}
				
				if($this->oper=='add'){
					if($this->duplicate('programid','sysdb.programtab',$_POST['programid'])){
						throw new Exception('Duplicate key');
					}
					$at_where = $_POST['whereat'];

					if($at_where=='after'){
						$prepare1="UPDATE sysdb.programtab SET lineno = lineno + 1 WHERE compcode=? and programmenu=? and lineno > ? order by lineno DESC";
						$arrayValue1=[$this->company,$_POST['programmenu'],$_POST['idAfter']];

						$prepare2="UPDATE sysdb.groupacc SET lineno = lineno + 1 WHERE compcode=? and programmenu=? and lineno > ? order by lineno DESC";
						$arrayValue2=[$this->company,$_POST['programmenu'],$_POST['idAfter']];

						$prepare3="INSERT INTO sysdb.programtab (compcode,programmenu,lineno,programname,programid,programtype,url,remarks,condition1,condition2,adduser) VALUES (?,?,?,?,?,?,?,?,?,?,?) ";
						$arrayValue3=[$this->company,$_POST['programmenu'],$_POST['idAfter']+1,$_POST['programname'],$_POST['programid'],$_POST['programtype'],$_POST['url'],$_POST['remarks'],$_POST['condition1'],$_POST['condition2'],$this->user];

						$this->save($prepare1,$arrayValue1);

						$this->save($prepare2,$arrayValue2);

						$this->save($prepare3,$arrayValue3);

					}else if($at_where=='first'){
						$prepare1="UPDATE sysdb.programtab SET lineno = lineno + 1 WHERE compcode=? and programmenu=? order by lineno DESC";
						$arrayValue1=[$this->company,$_POST['programmenu']];

						$prepare2="UPDATE sysdb.groupacc SET lineno = lineno + 1 WHERE compcode=? and programmenu=? order by lineno DESC";
						$arrayValue2=[$this->company,$_POST['programmenu']];

						$prepare3="INSERT INTO sysdb.programtab (compcode,programmenu,lineno,programname,programid,programtype,url,remarks,condition1,condition2,adduser) VALUES (?,?,?,?,?,?,?,?,?,?,?) ";
						$arrayValue3=[$this->company,$_POST['programmenu'],1,$_POST['programname'],$_POST['programid'],$_POST['programtype'],$_POST['url'],$_POST['remarks'],$_POST['condition1'],$_POST['condition2'],$this->user];

						$this->save($prepare1,$arrayValue1);

						$this->save($prepare2,$arrayValue2);

						$this->save($prepare3,$arrayValue3);

					}else if($at_where=='last'){

						$maxlineno = $this->getmaxlineno();

						$prepare3="INSERT INTO sysdb.programtab (compcode,programmenu,lineno,programname,programid,programtype,url,remarks,condition1,condition2,adduser) VALUES (?,?,?,?,?,?,?,?,?,?,?) ";
						$arrayValue3=[$this->company,$_POST['programmenu'],$maxlineno,$_POST['programname'],$_POST['programid'],$_POST['programtype'],$_POST['url'],$_POST['remarks'],$_POST['condition1'],$_POST['condition2'],$this->user];

						$this->save($prepare3,$arrayValue3);

					}

					$sql4="SELECT programmenu,lineno FROM sysdb.programtab where compcode='{$this->company}' and programid='{$_POST['programmenu']}'";
					$result4 = $this->db->query($sql4);if (!$result4) { print_r($this->db->errorInfo()); }
			        echo $sql4.'</br>';

					while($row = $result4->fetch(PDO::FETCH_ASSOC)){

						$sql5 = "SELECT groupid from sysdb.groupacc where programmenu='{$row['programmenu']}' and lineno='{$row['lineno']}' and compcode='{$this->company}' and yesall='1'";
						$result5 = $this->db->query($sql5);if (!$result5) { print_r($this->db->errorInfo()); }
						echo $sql5.'</br>';

			            while($row1 = $result5->fetch(PDO::FETCH_ASSOC)){
			                $sql6 = "INSERT INTO sysdb.groupacc (compcode,groupid,programmenu,lineno,canrun,yesall) SELECT '{$this->company}','{$row1['groupid']}','{$_POST['programmenu']}',lineno,1,0 FROM sysdb.programtab where compcode='{$this->company}' and programid='{$_POST['programid']}' and programmenu='{$_POST['programmenu']}'";
			                echo $sql6.'</br>';
							$result6 = $this->db->query($sql6);if (!$result6) { print_r($this->db->errorInfo()); }
			            }
					}
				
				}else if($this->oper=='edit'){

					$prepare3="UPDATE sysdb.programtab 
						SET programname=?,condition1=?,condition2=?,remarks=?,url=? WHERE compcode=? and programid=?";
					$arrayValue3=[$_POST['programname'],$_POST['condition1'],$_POST['condition2'],$_POST['remarks'],$_POST['url'],$this->company,$_POST['programid']];


					$this->save($prepare3,$arrayValue3);

				}else if($this->oper=='del'){
					$child=$this->getchildno();
					if($child!="0"){

//update delete id jd lineno=0 ; nak elak child jd orphen
						$sql1='UPDATE sysdb.programtab,(SELECT max(lineno)+1 as maxline FROM sysdb.programtab WHERE programmenu="" and compcode="'.$this->company.'")subq SET programmenu="" , lineno=subq.maxline WHERE lineno="'.$_POST['lineno'].'" and programmenu="'.$_POST['programmenu'].'" and programid="'.$_POST['programid'].'" and compcode="'.$this->company.'"';
						$sql2='DELETE FROM sysdb.groupacc WHERE lineno='.$_POST['lineno'].' and programmenu="'.$_POST['programmenu'].'"  and compcode="'.$this->company.'"';
						$sql3='UPDATE sysdb.programtab SET lineno = lineno - 1 WHERE compcode="'.$this->company.'" and programmenu="'.$_POST['programmenu'].'" and lineno > '.$_POST['lineno'].' order by lineno';
						$sql4='UPDATE sysdb.groupacc SET lineno = lineno - 1 WHERE compcode="'.$this->company.'" and programmenu="'.$_POST['programmenu'].'" and lineno > '.$_POST['lineno'].' order by lineno';

						$this->directSave($sql1);
						$this->directSave($sql2);
						$this->directSave($sql3);
						$this->directSave($sql4);

					}else{
						$sql1='DELETE FROM sysdb.programtab WHERE lineno="'.$_POST['lineno'].'" and programmenu="'.$_POST['programmenu'].'" and programid="'.$_POST['programid'].'" and compcode="'.$this->company.'"';

						$sql2='DELETE FROM sysdb.groupacc WHERE lineno="'.$_POST['lineno'].'" and programmenu="'.$_POST['programmenu'].'"  and compcode="'.$this->company.'"';

						$sql3='UPDATE sysdb.programtab SET lineno = lineno - 1 WHERE compcode="'.$this->company.'" and programmenu="'.$_POST['programmenu'].'" and lineno > '.$_POST['lineno'].' order by lineno';

						$sql4='UPDATE sysdb.groupacc SET lineno = lineno - 1 WHERE compcode="'.$this->company.'" and programmenu="'.$_POST['programmenu'].'" and lineno > '.$_POST['lineno'].' order by lineno';

						$this->directSave($sql1);
						$this->directSave($sql2);
						$this->directSave($sql3);
						$this->directSave($sql4);

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
