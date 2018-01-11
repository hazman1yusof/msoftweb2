<?php
	class EditTable{
		
		protected $db;
		
		var $oper;
		var $table;
		var $column;
		var $columnid;
		var $user;
		var $compcode;
		var $post;

		var $filterCol;
		var $filterVal;
		var $filterInCol;
		var $filterInType;
		var $filterInVal;

		var $sysparam;
		var $lineno;
		var $yearperiod;
		var $fixPost;
		var $skipduplicate;
		var $returnVal=false;
		var $responce;
		
		public function __construct(PDO $db,$get,$post){
			include_once('sschecker.php');
			//set operation
			if(!empty($get['oper'])){
				$this->oper = $get['oper'];
			}else{
				$this->oper = $post['oper'];
			}
			//skip duplicate
			if(isset($get['skipduplicate'])){
				$this->skipduplicate = $get['skipduplicate'];	
			}
			//set connection
			$this->db = $db;
			//set table
			$this->table = $get['table_name'];
			//throw away the dot in post field, because table is in array
			if(isset($get['fixPost']) && $get['fixPost'] == 'true' ){
				$this->fixPost = true;
			}else{
				$this->fixPost = false;
			}
			$this->post=$post;
			//set tableid
			if(!empty($get['table_id'])){
				$this->columnid = $get['table_id'];
			}
			//set column
			if($get['oper']!='del_hard' && isset($get['field']) && !empty($get['field'])){
				$this->column=$this->pushSomeIntoArray($get['field']);
			}
			//set sysparam if any
			if(isset($get['sysparam']) && !empty($get['sysparam']))$this->sysparam = $get['sysparam'];
			//set lineno if any
			if(isset($get['lineno']) && !empty($get['lineno']))$this->lineno = $get['lineno'];
			//set yearperiod if any
			if(isset($get['yearperiod']) && !empty($get['yearperiod']))$this->yearperiod = $get['yearperiod'];
			//set filter if any
			if(!empty($get['returnVal'])){
				$this->returnVal=$get['returnVal'];
				$this->responce = new stdClass();
			}
			if(!empty($get['filterCol'])){
				$this->filterCol=$get['filterCol'];
				$this->filterVal=$get['filterVal'];
			}
			if(!empty($get['filterInCol'])){
				$this->filterInCol=$get['filterInCol'];
				$this->filterInType=$get['filterInType'];
				$this->filterInVal=$get['filterInVal'];
			}
			//set others
			$this->user = $_SESSION['username'];
			$this->compcode = $_SESSION['company'];
		}

		public function fixPost(array $column,$rep,$isPost){
			$temp=[];
			if(!$isPost){
				foreach($column as $value){
					array_push($temp, substr(strstr($value, $rep),1));
				}	
			}else{
				foreach($column as $key => $value){
					$newkey = substr(strstr($key, $rep),1);
					$temp[$newkey] = $value;
				}
			}
			return $temp;
		}

		public function pushSomeIntoArray(array $column){//masuk sini utk extra..
			switch($this->oper){
				case 'add':
					$arr=['compcode','recstatus','adduser','adddate'];

					if(!empty($_GET['saveip'])){
						array_push($arr,'computerid');
						array_push($arr,'ipaddress');
					}

					break;
				case 'edit':
					$arr=['compcode','recstatus','upduser','upddate'];
					break;
				case 'del':
					$arr=['compcode','recstatus','deluser','deldate'];
					break;
				default:
					return 0; 
					break;
			}

			if($this->fixPost){
				$this->post = $this->fixPost($this->post,'_',true);
				$column = $this->fixPost($column,'_',false);
				$this->columnid = substr(strstr($this->columnid, '_'),1);
			}

			foreach($arr as $value){
				if(!in_array($value, $column)){
					array_push($column, $value);
				}
			}
			return $column;
		}
		
		public function getAllColumnFromTable(array $except){//get all column field
			$SQL = "SHOW COLUMNS FROM {$this->table}";
			$temp=array();
			$result = $this->db->query($SQL);if (!$result) { print_r($this->db->errorInfo()); }
			
			while($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$key=array_search($row['Field'], $except);
				if($key>-1){
				}else{
					array_push($temp,$row['Field']);
				}
			}
			return $temp;
		}
		
		public function chgDate($date){
			$newstr=explode("-", $date);
			return $newstr[2].'-'.$newstr[1].'-'.$newstr[0];
		}
		
		public function duplicate($code,$table,$codetext){
			$sqlDuplicate="select $code from $table where $code = '$codetext'";
			$result = $this->db->query($sqlDuplicate);if (!$result) { print_r($this->db->errorInfo()); }
			return $result->rowCount();
		}
		
		public function arrayValue(array $fixColName,array $fixColValue,$del){
			$column=$this->column;$temp = array();
			
			if($del){
			
				for($x=0;$x<count($fixColName);$x++){
					if($fixColValue[$x] == 'NOW()')continue;
					array_push($temp,$fixColValue[$x]);
				}
				
			}else{
				
				for($x=0;$x<count($column);$x++){
					$key=array_search($column[$x], $fixColName);
					if($column[$x] == 'sysno')continue;
					
					if($key>-1){
						if($fixColValue[$key] == 'NOW()') continue;
						array_push($temp,$fixColValue[$key]);
					}else if(isset($this->post[$column[$x]])){
						if(isset($this->post[$column[$x]]) && $this->post[$column[$x]] == 'NOW()') continue;
						array_push($temp,$this->post[$column[$x]]);
					}else{
						array_push($temp,NULL);
					}
				}
			
			}
			return $temp;
		}

		public function arrayValueFilter(array $fixColValue){
			$filter=$this->filterVal;$temp = $fixColValue;
			
			foreach ($filter as $key => $value) {
				if(strpos($value, '.') !== false){ /// kalu ada .(dot)
					$pieces = explode(".", $value, 2);
					if($pieces[0] == 'skip'){
						continue;
					}else if($pieces[0] == 'session'){
						if(isset($_SESSION[$pieces[1]]))array_push($temp,$_SESSION[$pieces[1]]);
					}else{
						continue;
					}
				}else if($value == 'IS NULL'){
					continue;
				}else{
					array_push($temp,$value);
				}
			}
			return $temp;
		}

		private function filter($first){
			$addSql='';
			if(!isset($this->filterCol) || empty($this->filterCol)){
				return $addSql;
			}else{
				foreach ($this->filterCol as $key => $col) {
					$val=$this->filterVal[$key];
					if(strpos($val, '.') !== false){ /// kalu ada .(dot)
						$pieces = explode(".", $val, 2);
						if($pieces[0] == 'skip'){
							$addSql.="AND {$col} = {$pieces[1]} ";
						}else if($pieces[0] == 'session'){
							$addSql.="AND {$col} = ? ";
						}else{
							$addSql.="AND {$col} {$pieces[0]} {$pieces[1]} ";
						}
					}else if($val == 'IS NULL'){
						$addSql.="AND {$col} IS NULL ";
					}else{
						$addSql.="AND {$col} = ? ";
					}
				}
				if($first){
					return strstr($addSql,' ');
				}else{
					return $addSql;
				}
			}
		}

		private function filterIn($first){
			$addSql='';
			if(!isset($this->filterInCol) || empty($this->filterInCol)){
				return $addSql;
			}else{
				foreach ($this->filterInCol as $key => $col) {
					$type=$this->filterInType[$key];
					$val=$this->filterInVal[$key];
					$addSql.="AND {$col} {$type} (";
					foreach ($val as $key2 => $value2) {
							$addSql.="'{$value2}',";
						
					}
					$addSql=rtrim($addSql,',');
					$addSql.=")";
				}
				if($first){
					return strstr($addSql,' ');
				}else{
					return $addSql;
				}
			}
		}

		public function autoSyntaxAdd(array $fixColName,array $fixColValue){
			$column=$this->column;$table=$this->table;

			$string='INSERT INTO '.$table.' (';
			
			for($x=0;$x<count($column);$x++){
				if($column[$x]=='sysno')continue;

				$string.=$column[$x].',';
			}
			
			$string=rtrim($string,',');
			$string.=') VALUES (';
				
			for($x=0;$x<count($column);$x++){
			
				$key=array_search($column[$x], $fixColName);
				if($column[$x]=='sysno')continue;

				//fix cant add NOW()to prepare statement
				if($key>-1 && $fixColValue[$key] == 'NOW()'){
					$string.="NOW(),";
				}else if(isset($this->post[$column[$x]]) && $this->post[$column[$x]] == 'NOW()'){
					$string.="NOW(),";
				}else{
					$string.="?,";
				}
			}
			
			$string=rtrim($string,',');
			$string.=')';
			
			return $string;
		}
		
		public function autoSyntaxUpd(array $fixColName,array $fixColValue,$needFilter){
			$column=$this->column;$first=false;
			
			$string='UPDATE '.$this->table.' SET ';
			
			for($x=0;$x<count($column);$x++){
				$key=array_search($column[$x], $fixColName);
				if($column[$x]=='sysno')continue;
				
				//fix cant add NOW()to prepare statement
				if($key>-1 && $fixColValue[$key] == 'NOW()'){
					$string.=$fixColName[$key].' = NOW(),';
				}else if(isset($this->post[$column[$x]]) && $this->post[$column[$x]] == 'NOW()'){
					$string.=$column[$x].' = NOW(),';
				}else{
					$string.=$column[$x].' = ?,';
				}
				
			}
			$string=rtrim($string,',');
			if(!empty($this->columnid)){
				$string.=" WHERE compcode = ? AND ".$this->columnid." = ? ";
			}else{
				$string.=" WHERE compcode = ? ";
			}
			
			if($needFilter){
				$string.=$this->filter($first);	
			}
			
			if(!empty($this->filterInCol)){
				$string.=$this->filterIn($first);	
			}
			return $string;
		}
		
		public function autoSyntaxDel(array $fixColName,array $fixColValue,$needFilter){
			$string='UPDATE '.$this->table.' SET ';$first=false;
			
			for($x=0;$x<count($fixColName);$x++){
				if($fixColValue[$x] == 'NOW()'){
					$string.=$fixColName[$x].' = NOW(),';
				}else{
					$string.=$fixColName[$x].' = ?,';
				}
			}
			$string=rtrim($string,',');
			if(!empty($this->columnid)){
				$string.=" WHERE compcode = ? AND ".$this->columnid." = ? ";
			}else{
				$string.=" WHERE compcode = ? ";
			}
			
			if($needFilter){
				$string.=$this->filter($first);	
			}
			return $string;
		}
		
		public function sysparam(){
			$sysparam = $this->sysparam;
			
			$sqlSysparam="SELECT pvalue1 FROM sysdb.sysparam WHERE source = '{$sysparam['source']}' AND trantype = '{$sysparam['trantype']}'";
			$result = $this->db->query($sqlSysparam);if (!$result) { print_r($this->db->errorInfo()); }
			$row = $result->fetch(PDO::FETCH_ASSOC);
			
			$pvalue1=intval($row['pvalue1'])+1;
			
			$sqlSysparam="UPDATE sysdb.sysparam SET pvalue1 = '{$pvalue1}' WHERE source = '{$sysparam['source']}' AND trantype = '{$sysparam['trantype']}'";
			//echo $sqlSysparam;
			
			$this->db->query($sqlSysparam);if (!$result) { print_r($this->db->errorInfo()); }
			
			return $pvalue1;
		}

		public function lineno(){
			$lineno=$this->lineno;
			$first=false;
			$prepare="SELECT COUNT({$lineno['useOn']}) AS count FROM {$this->table} WHERE compcode=? 
				AND {$lineno['useOn']} = '{$lineno['useVal']}' ";

			if(!empty($this->filterCol)){
				$prepare.=$this->filter($first);
			}
			$arrayValue = [$this->compcode];
			$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;
			/////////////////check sytax//////////////////////////////
			//echo $prepare;print_r($arrayValue);
			//echo $this->readableSyntax($prepare,$arrayValue);
			//////////////////////////////////////////////////////////
			$sth=$this->db->prepare($prepare);
			if(!$sth->execute($arrayValue)){throw new Exception('error');}
			$row = $sth->fetch(PDO::FETCH_ASSOC);

			return intval($row['count']) + 1;
		}

		public function returnVal($ret){
			$first=false;
			$prepare = "SELECT ".implode(",", $this->returnVal)." FROM {$this->table} WHERE {$this->columnid} = ? AND compcode = ? ";

			if(!empty($this->filterCol)){
				$prepare.=$this->filter($first);
			}
			$arrayValue=[$ret,$this->compcode];
			$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;
			/////////////////check sytax//////////////////////////////
			//echo $prepare;print_r($arrayValue);
			echo $this->readableSyntax($prepare,$arrayValue);
			//////////////////////////////////////////////////////////
			$sth=$this->db->prepare($prepare);
			if(!$sth->execute($arrayValue)){throw new Exception('error');}

			$i=0;
			while($row = $sth->fetch(PDO::FETCH_OBJ)) {
				$this->responce->rows[$i]=$row;
				$i++;
			}
		}

		public function readableSyntax($prepare,array $arrayValue){
			foreach($arrayValue as $val){
				$prepare=preg_replace("/\?/", "'".$val."'", $prepare,1);
			}
			return $prepare."\r\n";
		}

		public function getyearperiod($date){
			
			$query = "select * from sysdb.period where compcode='{$this->compcode}'";
			$result = $this->db->query($query);if (!$result) { print_r($this->db->errorInfo()); }
			$seldate = new DateTime($date);

			while($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$year= $row['year'];
				$period=0;
				for($x=1;$x<=12;$x++){
					$period = $x;

					$datefr = new DateTime($row['datefr'.$x]);
					$dateto = new DateTime($row['dateto'.$x]);
					if (($datefr <= $seldate) &&  ($dateto >= $seldate)){
						$responce = new stdClass();
						$responce->year = $year;
						$responce->period = $period;
						return $responce;
					}
				}
			}

		}
		
		public function edit_table($commit){

			try{
				if($commit){
					$this->db->beginTransaction();
				}
				
				//$ip = $_SERVER['REMOTE_ADDR'];
				
				if($this->oper=='add'){
					
					$addarrField=['compcode','adduser','adddate','recstatus'];//extra field, 'computerid', 'ipaddress'
					$addarrValue=[$this->compcode,$this->user,'NOW()','A'];//, gethostbyaddr($ip), $ip

					if(!empty($_GET['saveip'])){
						array_push($addarrField,'computerid');
						array_push($addarrValue,$_POST['lastcomputerid']);
						array_push($addarrField,'ipaddress');
						array_push($addarrValue,$_POST['lastipaddress']);
					}
					
					if(!empty($this->sysparam)){
						$tempsysparam = $this->sysparam();
						array_push($addarrField,$this->sysparam['useOn']);
						array_push($addarrValue,$tempsysparam);

						if($this->oper=='add' && $this->returnVal){
							$this->responce->auditno=$tempsysparam;
							if(isset($_POST['auditno'])){
								$_POST['auditno'] = $tempsysparam;
							}
						}
					}

					if(!empty($this->lineno)){
						array_push($addarrField,$this->lineno['useBy']);
						array_push($addarrValue,$this->lineno());
					}

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
					
				}else if($this->oper=='edit'){
				
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
					
				}else if($this->oper=='del'){
				
					$delarrField=['compcode','deluser','deldate','recstatus'];//extra field
					$delarrValue=[$this->compcode,$this->user,'NOW()','D'];
				
					$prepare = $this->autoSyntaxDel($delarrField,$delarrValue,!empty($this->filterCol));
					
					$arrayValue = $this->arrayValue($delarrField,$delarrValue,true);
					if(!empty($this->columnid)){
						array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);
					}else{
						array_push($arrayValue,$this->compcode);
					}
					$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;
					
				}else if($this->oper=='del_hard'){
					
					$prepare = "DELETE FROM {$this->table} ";

					if(!empty($this->columnid)){
						$prepare.=" WHERE compcode = ? AND ".$this->columnid." = ? ";
					}else{
						$prepare.=" WHERE compcode = ? ";
					}
					
					if(!empty($this->filterCol)){
						$prepare.=$this->filter(false);	
					}

					$arrayValue=[];
					if(!empty($this->columnid)){
						array_push($arrayValue,$this->compcode,$this->post[$this->columnid]);
					}else{
						array_push($arrayValue,$this->compcode);
					}
					$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;
					
				}
				/////////////////check sytax//////////////////////////////
				//echo $prepare;print_r($arrayValue);
				echo $this->readableSyntax($prepare,$arrayValue);
				//////////////////////////////////////////////////////////
				
				if($this->columnid!='idno' && $this->oper=='add' && !$this->skipduplicate && !empty($this->post[$this->columnid]) && $this->duplicate($this->columnid,$this->table,$this->post[$this->columnid])){
					throw new Exception('Duplicate key');
				}
				
				$sth=$this->db->prepare($prepare);
				if (!$sth->execute($arrayValue)) {
					throw new Exception($this->readableSyntax($prepare,$arrayValue));
				}

				if($commit){
					$this->db->commit();
				}

				if($this->oper=='add' && !empty($this->returnVal)){
					echo json_encode($this->responce);
				}else{
					echo '{"msg":"success"}';
				}
				
			}catch( Exception $e ){
				$this->db->rollback();
				http_response_code(400);
				echo $e->getMessage();
				
			}
		}		
	}
?>