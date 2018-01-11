<?php
	class TableModel{
		
		protected $db;
		
		//////////////pager//////////////////
		var $page;
		var $limit;
		var $sidx; //order by
		var $sord; //sort, asc desc
		var $total_pages;
		var $counts;
		var $start;
		var $sort_idno=false;
		var $loadonce=false;

		////////////Search use 'LIKE'//////////
		var $searchCol;
		var $searchVal;

		////////////Search use 'LIKE - OR'//////////
		var $searchCol2;
		var $searchVal2;

		///////////Where use 'AND'//////////////
		var $filterCol;
		var $filterVal;

		///////////Where use 'OR'//////////////
		var $filterCol2;
		var $filterVal2;
		
		///////Where use 'IN'////////
		var $filterInCol;
		var $filterInType; //NOT IN or IN
		var $filterInVal;

		////////////join table/////////////////
		var $join_type;
		var $join_onCol;
		var $join_onVal;
		var $join_filterCol;
		var $join_filterVal;

		////////////group by//////////////////
		var $groupby;

		///////////Other///////////////////////
		var $fixPost;
		var $table;
		var $column;
		var $columnid;//need only for table
		var $compcode;
		var $oper;

		////////////output holder//////////////
		var $responce;

	    public function __construct(PDO $db,$get)
	    {	
			include_once('sschecker.php');
			if (empty($except)){
				$except = [''];
			}
			
			$this->responce = new stdClass();
	        $this->db = $db; // connection

	        ////////////only for get_table, not for get_value/////////////
			if(!empty($get['page']))$this->page = $get['page']; // paging number
			if(!empty($get['rows']))$this->limit = $get['rows'];// get how many rows we want to have into the grid
			if(!empty($get['sidx']))$this->sidx = $get['sidx']; // get index row - i.e. user click to sort
			if(!empty($get['sord']))$this->sord = $get['sord']; // get the direction

			if(isset($get['start']))$this->start = $get['start'];//get value
			//////////////////////////////////////////////////////////////

			$this->table = $get['table_name'];

			if(!empty($get['fixPost'])){
				$this->fixPost=true;
			}else{
				$this->fixPost=false;
			}

			if(!empty($get['join_type'])){
				$this->join_type=$get['join_type'];
				$this->join_onCol=$get['join_onCol'];
				$this->join_onVal=$get['join_onVal'];
			}

			if(!empty($get['join_filterCol'])){
				$this->join_filterCol=$get['join_filterCol'];
				$this->join_filterVal=$get['join_filterVal'];
			}

			if(!empty($get['sort_idno'])){
				$this->sort_idno=$get['sort_idno'];
			}

			if(!empty($get['loadonce'])){
				$this->loadonce=$get['loadonce'];
			}

			if(!empty($get['filterCol'])){
				$this->filterCol=$get['filterCol'];
				$this->filterVal=$get['filterVal'];
			}

			if(!empty($get['filterCol2'])){
				$this->filterCol2=$get['filterCol2'];
				$this->filterVal2=$get['filterVal2'];
			}

			if(!empty($get['filterInCol'])){
				$this->filterInCol=$get['filterInCol'];
				$this->filterInType=$get['filterInType'];
				$this->filterInVal=$get['filterInVal'];
			}

			if(!empty($get['groupby'])){
				if($this->fixPost){
					$this->groupby = substr(strstr($get['groupby'], '_'),1);
				}else{
					$this->groupby=$get['groupby'];
				}
			}

			if(!empty($get['searchCol'])){
				if($this->fixPost){
					$this->searchCol = $this->fixPost($get['searchCol'],'_');
				}else{
					$this->searchCol=$get['searchCol'];
				}
				$this->searchVal=$get['searchVal'];
			}

			if(!empty($get['searchCol2'])){
				if($this->fixPost){
					$this->searchCol2 = $this->fixPost($get['searchCol2'],'_');
				}else{
					$this->searchCol2=$get['searchCol2'];
				}
				$this->searchVal2=$get['searchVal2'];
			}

			if(empty($get['table_id'])){
				$this->columnid = 'idno';
			}else{
				if($this->fixPost){
					$this->columnid = substr(strstr($get['table_id'], '_'),1);
				}else{
					$this->columnid = $get['table_id'];
				}
			}
			
			if(isset($_SESSION['company'])){
				$this->compcode=$_SESSION['company'];
			}else{
				$this->compcode='9A';
			}
			
			if(!empty($get['field'])){
				if($this->fixPost){
					$this->column = $this->fixPost($get['field'],'_');
					if(!empty($this->sidx))$this->sidx = $this->fixIdx($this->sidx);
				}else{
					$this->column=$get['field']; 
				}
			}

		}
		
		private function getPagerInfo(){
		
			if(!$this->sidx) $this->sidx = 'NULL';
			
			if(is_array($this->table)){
				$arrayValue=[];
			}else{
				$arrayValue=[$this->compcode];
			}

			$prepare = $this->autoPrepStmt(!empty($this->searchCol),!empty($this->filterCol),true,true);
			$arrayValue = (!empty($this->searchCol)) ? $this->arrayValueSearch($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->searchCol2)) ? $this->arrayValueSearch2($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;
			//$arrayValue = (!empty($this->filterCol2)) ? $this->arrayValueFilter2($arrayValue) : $arrayValue;

			////////////////////check syntax///////////////////
			//echo $prepare;print_r($arrayValue);
			//echo $this->readableSyntax($prepare,$arrayValue);
			///////////////////////////////////////////////////

			$sth=$this->db->prepare($prepare);
			if(!$sth->execute($arrayValue)){
				throw new Exception('error: '.$this->readableSyntax($prepare,$arrayValue));
			}
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			$this->counts = $row['count'];
			
			if( $this->counts >0 ) {
				$this->total_pages = ceil($this->counts/$this->limit);
			} else {
				$this->total_pages = 0;
			}
			
			if ($this->page > $this->total_pages && $this->counts>0 ) {$this->page=$this->total_pages;}
			if(empty($this->start))$this->start = $this->limit*$this->page - $this->limit;
		}

		private function getAllColumnFromTable(array $except){//get all column field
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
		
		private function fixPost(array $column,$rep){//turn underscore to dot
			$temp=[];
			foreach($column as $value){
				$pos = strpos($value, "_");
				if ($pos !== false) {
					$newstring = substr_replace($value, ".", $pos, strlen("."));
					array_push($temp,$newstring);
				}
			}	
			return $temp;
		}

		private function fixIdx($value){
			return str_replace("_", ".", $value);
		}

		private function cellArray($row){
			$temp=array();
			for($x=0;$x<count($this->column);$x++){
				array_push($temp,$row[$x]);//dulu assoc sekarang num
			}
			return $temp;
		}
		
		private function arrayValueFilter(array $fixColValue){
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
						}else if($pieces[0] == '<>'){
							$addSql.="AND {$col} {$pieces[0]} '{$pieces[1]}' ";
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

		private function arrayValueFilter2(array $fixColValue){
			$filter=$this->filterVal2;$temp = $fixColValue;
			
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

		private function filter2($first){
			$addSql='';
			if(!isset($this->filterCol2) || empty($this->filterCol2)){
				return $addSql;
			}else{
				foreach ($this->filterCol2 as $key => $col) {
					$val=$this->filterVal2[$key];
					if(strpos($val, '.') !== false){ /// kalu ada .(dot)
						$pieces = explode(".", $val, 2);
						if($pieces[0] == 'skip'){
							$addSql.="OR {$col} = {$pieces[1]} ";
						}else if($pieces[0] == 'session'){
							$addSql.="OR {$col} = ? ";
						}else{
							$addSql.="OR {$col} {$pieces[0]} {$pieces[1]} ";
						}
					}else if($val == 'IS NULL'){
						$addSql.="OR {$col} IS NULL ";
					}else{
						$addSql.="OR {$col} = ? ";
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
					$addSql.=") ";
				}
				if($first){
					return strstr($addSql,' ');
				}else{
					return $addSql;
				}
			}
		}

		private function arrayValueSearch(array $fixColValue){
			$search=$this->searchVal;$temp = $fixColValue;
			
			foreach ($search as $value) {
				array_push($temp,$value);
			}
			return $temp;
		}

		private function groupby(){
			return " GROUP BY ".$this->groupby;
		} 

		private function search($first){
			$addSql='';
			if(!isset($this->searchCol) || empty($this->searchCol)){
				return $addSql;
			}else{
				$search=$this->searchCol;
				foreach($search as $value){
					$addSql.="AND {$value} LIKE ? ";
				}
				if($first){
					return strstr($addSql,' ');
				}else{
					return $addSql;
				}
			}
		}

		private function arrayValueSearch2(array $fixColValue){
			$search=$this->searchVal2;$temp = $fixColValue;
			
			foreach ($search as $value) {
				array_push($temp,$value);
			}
			return $temp;
		}

		private function search2($first){
			$addSql='AND (';
			$addSql2="";
			if(!isset($this->searchCol2) || empty($this->searchCol2)){
				return $addSql;
			}else{
				$search=$this->searchCol2;
				foreach($search as $value){
					$addSql2.="OR {$value} LIKE ? ";
				}
				$addSql.= strstr($addSql2,' ');
				$addSql.= ") ";
				
				if($first){
					return strstr($addSql,' ');
				}else{
					return $addSql;
				}
			}
		}

		private function autoPrepStmt($needSearch,$needFilter,$countOnly,$forTable){
			$column=$this->column;
			$first=false;
			$string='SELECT ';

			if ($countOnly) {
				 $string.="COUNT(*) AS count";
			}else{
				for($x=0;$x<count($column);$x++){
					$string.=$column[$x].',';
				}
				$string=rtrim($string,',');
			}
			
			$string.=" FROM ";

			if(is_array($this->table)){
				foreach($this->table as $index => $value){
					if($index==0){
						$string.= $value;
					}else if($index>0){
						$i=$index-1;
						$string.= ' '.$this->join_type[$i].' '.$value.' ON '.$this->join_onCol[$i].' = '.$this->join_onVal[$i].' ';
						if(!empty($this->join_filterCol[$i])){
							foreach ($this->join_filterCol[$i] as $key => $col) {
								$val=$this->join_filterVal[$i][$key];
								if(strpos($val, '.') !== false){ /// kalu ada .(dot)
									$pieces = explode(".", $val, 2);
									if($pieces[0] == 'skip'){
										$string.="AND {$col} = {$pieces[1]} ";
									}else if($pieces[0] == 'session'){
										$string.="AND {$col} = {$_SESSION[$pieces[1]]}";
									}else{
										$string.="AND {$col} {$pieces[0]} {$pieces[1]} ";
									}
								}else if($val == 'IS NULL'){
									$string.="AND {$col} IS NULL ";
								}else{
									$string.="AND {$col} = '{$val}' ";
								}
							}
						}
					}
				}
				$string.=" WHERE ";
				$first=true;

			}else{
				$string.= $this->table." WHERE compcode=? ";
			}
			
			if($needSearch){
				$string.=$this->search($first);
				$first=false;	
			}
			if(!empty($this->searchCol2)){
				$string.=$this->search2($first);	
				$first=false;	
			}
			if($needFilter){
				$string.=$this->filter($first);	
				$first=false;	
			}
			if(!empty($this->filterInCol)){
				$string.=$this->filterIn($first);	
				$first=false;	
			}
			if(!empty($this->filterCol2)){
				$string.=$this->filter2($first);	
				$first=false;	
			}
			if(!$countOnly && !empty($this->groupby)){
				$string.=$this->groupby();
			}
			if(!$countOnly && $forTable){
				$string.= " ORDER BY {$this->sidx} {$this->sord} ";
				if($this->sort_idno){
					$string.= ",idno DESC ";
				}
				$string.= " LIMIT {$this->start},{$this->limit}";
			}
			
			return $string;
		}

		private function readableSyntax($prepare,array $arrayValue){
			foreach($arrayValue as $val){
				$prepare=preg_replace("/\?/", "'".$val."'", $prepare,1);
			}
			return $prepare."\r\n";
		}
			
		public function get_value(){

			if(is_array($this->table)){
				$arrayValue=[];
			}else{
				$arrayValue=[$this->compcode];
			}

			$prepare = $this->autoPrepStmt(!empty($this->searchCol),!empty($this->filterCol),false,!empty($this->limit));
			$arrayValue = (!empty($this->searchCol)) ? $this->arrayValueSearch($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->searchCol2)) ? $this->arrayValueSearch2($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->filterCol2)) ? $this->arrayValueFilter2($arrayValue) : $arrayValue;

			////////////////////check syntax///////////////////
			//echo $prepare;print_r($arrayValue);
			//echo $this->readableSyntax($prepare,$arrayValue);
			///////////////////////////////////////////////////

			try{

				$sth=$this->db->prepare($prepare);
				if(!$sth->execute($arrayValue)){
					throw new Exception('error: '. $this->readableSyntax($prepare,$arrayValue));
				}else{
					$i=0;
					while($row = $sth->fetch(PDO::FETCH_OBJ)) {
						$this->responce->rows[$i]=$row;
						$i++;
					}

					$this->responce->sql = $this->readableSyntax($prepare,$arrayValue);
					return json_encode($this->responce);
				}
				

			}catch( Exception $e ){
				http_response_code(400);
				echo $e->getMessage();
				
			}

		}

		public function get_excel(){

			if(is_array($this->table)){
				$arrayValue=[];
			}else{
				$arrayValue=[$this->compcode];
			}

			$prepare = $this->autoPrepStmt(!empty($this->searchCol),!empty($this->filterCol),false,!empty($this->limit));
			$arrayValue = (!empty($this->searchCol)) ? $this->arrayValueSearch($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->searchCol2)) ? $this->arrayValueSearch2($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->filterCol2)) ? $this->arrayValueFilter2($arrayValue) : $arrayValue;

			////////////////////check syntax///////////////////
			//echo $prepare;print_r($arrayValue);
			//echo $this->readableSyntax($prepare,$arrayValue);
			///////////////////////////////////////////////////

			try{

				$sth=$this->db->prepare($prepare);
				if(!$sth->execute($arrayValue)){
					throw new Exception('error: '. $this->readableSyntax($prepare,$arrayValue));
				}else{

					$column=$this->column;
					$excel_prg = "";

					// $excel_prg .= 'header("Content-Type: application/xls");';
					// $excel_prg .= 'header("Content-Disposition: attachment; filename=filename.xls");';
					// $excel_prg .= 'header("Pragma: no-cache"); ';
					// $excel_prg .= 'header("Expires: 0");';

					for($x=0;$x<count($column);$x++){
						$excel_prg .= $column[$x].'\t';
					}

					$excel_prg .= '\n';

					while($row = $sth->fetch(PDO::FETCH_NUM)) {
						for($x=0;$x<count($column);$x++){
							$excel_prg .= $row[$x].'\t';
						}
						$excel_prg .= '\n';
					}

					// $this->responce->sql = $this->readableSyntax($prepare,$arrayValue);
					return $excel_prg;
				}

			}catch( Exception $e ){
				http_response_code(400);
				echo $e->getMessage();
				
			}

		}
			
		public function get_table(){
			
			$this->getPagerInfo();

			if(is_array($this->table)){
				$arrayValue=[];
			}else{
				$arrayValue=[$this->compcode];
			}

			if(!$this->loadonce){
				$prepare = $this->autoPrepStmt(!empty($this->searchCol),!empty($this->filterCol),false,true);
			}else{
				$prepare = $this->autoPrepStmt(!empty($this->searchCol),!empty($this->filterCol),false,false);
			}

			$arrayValue = (!empty($this->searchCol)) ? $this->arrayValueSearch($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->searchCol2)) ? $this->arrayValueSearch2($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->filterCol)) ? $this->arrayValueFilter($arrayValue) : $arrayValue;
			$arrayValue = (!empty($this->filterCol2)) ? $this->arrayValueFilter2($arrayValue) : $arrayValue;
			
			////////////////////check syntax///////////////////
			//echo $prepare;print_r($arrayValue);
			//echo $this->readableSyntax($prepare,$arrayValue);
			///////////////////////////////////////////////////

			try{

				$sth=$this->db->prepare($prepare);
				if(!$sth->execute($arrayValue)){
					throw new Exception('error: '. $this->readableSyntax($prepare,$arrayValue));
				}

				if(!$this->loadonce){
					$this->responce->page = $this->page;
					$this->responce->total = $this->total_pages;
					$this->responce->records = $this->counts;
				}

				$i=0;
				while($row = $sth->fetch(PDO::FETCH_BOTH)) {
					$this->responce->rows[$i]['id']=($this->columnid == 'none_')?$i:$row[$this->columnid];
					$this->responce->rows[$i]['cell']=$this->cellArray($row);
					$i++;
				}

				$this->responce->sql = $this->readableSyntax($prepare,$arrayValue);
				return json_encode($this->responce);

			}catch( Exception $e ){
				http_response_code(400);
				echo $e->getMessage();
			}
		}

	}
?>