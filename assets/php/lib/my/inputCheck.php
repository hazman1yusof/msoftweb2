<?php
	class InputCheck{
		
		protected $db;
		
		var $field;
		var $value;
		var $table;
		var $columnid;
		var $compcode;

	    public function __construct(PDO $db)
	    {	
			include_once('sschecker.php');
			$this->table = $_GET['table'];
			$this->field = $_GET['field'];
			$this->value = $_GET['value'];
	        $this->db = $db; // connection
			$this->compcode=$_SESSION['company'];
		}
		
		public function check(){ ;
			$json = new stdClass();
			$sql="select ".implode(",", $this->field)." from {$this->table} where {$this->field[0]} = '{$this->value}'";
			$result = $this->db->query($sql);if (!$result) { print_r($this->db->errorInfo()); }
			
			if($result->rowCount()){
				$row = $result->fetch(PDO::FETCH_ASSOC);
				$json->msg='success';
				$json->row=$row;
			}else{
				$json->msg='fail';
			}
			
			return json_encode($json);
		}
		

	}
?>