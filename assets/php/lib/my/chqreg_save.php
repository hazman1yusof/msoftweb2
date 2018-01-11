<?php
	
	class Chqreg_save{

	    protected $db;
	    var $company;
	    var $user;
	    var $oper;

	    public function __construct(PDO $db)
	    {
	        $this->db = $db;
	        $this->oper = $_POST['oper'];
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
					
					if ($_POST['endno']<$_POST['startno']){
						
						throw new Exception('endno > startno');

					}else{

						$sqlCheck="SELECT * FROM finance.chqtran WHERE bankcode='{$_POST['bankcode']}' AND (cheqno BETWEEN {$_POST['startno']} AND {$_POST['endno']})";
						$result=$this->db->query($sqlCheck);
						$count=$result->rowCount();

						if(intval($count)>0){
							throw new Exception('check number already used');
						}else{

							$prepare="INSERT INTO chqreg (compcode,bankcode,startno,endno,cheqqty,stat,adduser,adddate) VALUES (?,?,?,?,?,?,?,NOW())";
							$arrayValue=[$company,$_POST['bankcode'],$_POST['startno'],$_POST['endno'],$_POST['endno']-$_POST['startno']+1,'ACTIVE',$user];

							//echo $this->readableSyntax($prepare,$arrayValue);
							$sth=$this->db->prepare($prepare);
							$sth->execute($arrayValue);

							$bankcode = $_POST['bankcode'];
							$startno = $_POST['startno'];
							$endno = $_POST['endno'];

							$prepare="INSERT INTO finance.chqtran (compcode, bankcode, cheqno, stat) VALUES (?,?,?,?)";
							$sth=$this->db->prepare($prepare);

							while($startno <= $endno){
								$arrayValue=[$company,$bankcode,$startno,'ACTIVE'];
								$sth->execute($arrayValue);
								$startno++;

								//echo $this->readableSyntax($prepare,$arrayValue);
							}
						}
					}

					$this->db->commit();

				}else if($this->oper=='edit'){
					
				}else if($this->oper=='del'){
					
				}
			}catch( Exception $e ){
				$this->db->rollback();
				http_response_code(400);
				echo $e->getMessage();
				
			}
		}
	}

?>
