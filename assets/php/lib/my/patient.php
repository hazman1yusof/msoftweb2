<?php
/*
1. get patient list
2. get patient detail
3. edit patient info
4. 
*/
class PatientModel
{
	protected $db;

	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	function get_patient_list($rq){
		$where =" compcode = '9A' ";
		$whereq = "";
		$order_by="`mrn`";
		$rows=25;
		$current=1;
		$limit_l=($current * $rows) - ($rows);
		$limit_h=$rows  ;


		if (isset($_REQUEST['sort']) && is_array($_REQUEST['sort']) ){
			$order_by="";
			foreach($_REQUEST['sort'] as $key=> $value)
				$order_by.=" $key $value";
		}

		//if (isset($_REQUEST['searchPhrase']) ){
		if (!empty($_REQUEST['searchPhrase'])){
			$arraySP = explode(" ",$_REQUEST['searchPhrase']);

			foreach($arraySP as $value){
				$where.="AND {$_REQUEST['Scol']} LIKE '%{$value}%' ";
			}
		}

		if (!empty($_REQUEST['listtype']) && $_REQUEST['listtype'] == "GP"){
			$where.="AND MRN >= 5999999 ";
		}else{
			$where.="AND MRN <= 5999999 ";
		}

		if (isset($_REQUEST['rowCount']) ){
			$rows=$_REQUEST['rowCount'];
		}


		if (isset($_REQUEST['current']) ){
			$current=$_REQUEST['current'];
			$limit_l=($current * $rows) - ($rows);
			$limit_h=$rows ;
		}

		if ($rows==-1){
			$limit="";  //no limit
		}else{
			$limit=" LIMIT $limit_l,$limit_h  ";
		}

		// TOTAL RECORD TO DISPLAY
		$sqlCount = "SELECT COUNT(*) as count FROM hisdb.pat_mast WHERE $where ORDER BY $order_by";
//		echo $sqlCount;
		$res = $this->db->query($sqlCount);
		$totalRecords = $res->fetch(PDO::FETCH_ASSOC)['count'];

		// TOTAL RECORD LIMIT TO DISPLAY
		$sql = "SELECT * FROM hisdb.pat_mast WHERE $where ORDER BY $order_by $limit";
//			echo  $sql;

		$stmt = $this->db->query($sql);
		$arr = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$arr[] = $row;
		}

		$json_data = array(
			"sql"				=> $sql,
			"current"           => intval($current),
			"rowCount"          => intval($rows),
			"total"    			=> intval( $totalRecords ),
			"rows"				=> $arr   // total data array
		);
		return json_encode($json_data);
	}

	function get_patient_detail($patid){
		try{
			$sql = "SELECT * FROM hisdb.pat_mast WHERE `mrn` = '$patid'"; // <-- kena add join other table
//				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			return  '{"patient":' . json_encode($arr) . '}';
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}

	function get_patient_last(){
		try{
			$sql = "SELECT * FROM hisdb.pat_mast ORDER BY `mrn` DESC LIMIT 0,1";
//    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			return  '{"mrn":' . json_encode($arr) . '}';
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}

	function get_patient_title(){
		try{
			$sql = "SELECT *, code AS code, description AS description FROM hisdb.title";
//    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}

	}
	function get_patient_idtype(){
		try{
//			$sql = "SELECT * FROM hisdb.title";
////    				echo $sql;
//			$stmt = $this->db->query($sql);
//			$arr = array();
//			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
//			{
//				$arr[] = $row;
//			}
			return  '[{"sysno":"5","Comp":"","Code":"O","Description":"Own IC","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"F","Description":"Father","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"8","Comp":"","Code":"M","Description":"Mother","createdBy":"","createdDate":"0000-00-00","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"9","Comp":"","Code":"P","Description":"Polis","createdBy":"","createdDate":"0000-00-00","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"10","Comp":"","Code":"T","Description":"Tentera","createdBy":"","createdDate":"0000-00-00","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""}]';

		} catch(PDOException $e) {
			echo $e->getMessage();
		}

	}
	function get_patient_occupation(){
		try{
			$sql = "SELECT *, occupcode AS code, description AS description FROM hisdb.occupation";
//    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}

	}
	function get_patient_areacode(){
		try{
			$sql = "SELECT *, areacode AS code, description AS description FROM hisdb.areacode WHERE compcode='9A'";
//    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}

	}
	function get_patient_sex(){
		try{
			$sql = "SELECT * FROM hisdb.sex";
//    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			return  json_encode($arr);
		} catch(PDOException $e) {
			echo $e->getMessage();
		}

	}
	function get_patient_race(){
		try{
			$sql = "SELECT * FROM hisdb.racecode";
//    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			return  json_encode($arr);
		} catch(PDOException $e) {
			echo $e->getMessage();
		}

	}
	function get_patient_religioncode(){
		try{
			$sql = "SELECT * FROM hisdb.religion";
//	    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			return  json_encode($arr);
		} catch(PDOException $e) {
			echo $e->getMessage();
		}

	}
	function get_patient_urlmarital(){
		try{
			$sql = "SELECT * FROM hisdb.marital";
//	    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			return  json_encode($arr);
		} catch(PDOException $e) {
			echo $e->getMessage();
		}

	}
	function get_patient_language(){
		try{
			$sql = "SELECT * FROM hisdb.languagecode";
//	    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			return  json_encode($arr);
		} catch(PDOException $e) {
			echo $e->getMessage();
		}

	}
	
	function get_patient_relationship()
	{
		try{
			$sql = "SELECT *, relationshipcode AS code, description AS description FROM hisdb.relationship";
			//	    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}
	}

	function get_patient_citizen()
	{
		try{
			$sql = "SELECT *, code AS code, description AS description FROM hisdb.citizen";
			//    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}
	}
	
	function get_all_company(){
		try{
			$sql = "SELECT *, compcode AS code, `name` AS description FROM hisdb.company";
			//	    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}
	}
	
	function get_reg_dept(){
		try{
			$sql = "SELECT *, deptcode AS code FROM sysdb.department WHERE compcode='9A' AND regdept<>0";
//			    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;

//			return $sql;
		} catch(PDOException $e) {
			echo $e->getMessage();
		}

	}

	function get_reg_source(){
		try{
			$sql = "SELECT *, deptcode AS code FROM sysdb.department ";
//			    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;

//			return $sql;
		} catch(PDOException $e) {
			echo $e->getMessage();
		}

	}

	function get_reg_case(){
		try{
			$sql = "SELECT *, case_code AS code FROM hisdb.casetype";
//			    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;

//			return $sql;
		} catch(PDOException $e) {
			echo $e->getMessage();
		}

	}

	function get_reg_doctor($discipline=""){
		try{
			$sql = "SELECT *, doctorcode AS code, doctorname AS description FROM hisdb.doctor ";

			if ($discipline != "")
			{
				$sql .= " WHERE disciplinecode='" . $discipline . "' ";
			}
			//echo $sql;

			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;

//			return $sql;
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
	function get_reg_fin(){
		try{
			$sql = "SELECT *, debtortycode AS code FROM debtor.debtortype";
			//			    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			//return  json_encode($arr);
			
			$result = '{"data":' . json_encode($arr) . '}';
			return $result;

			//			return $sql;
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
	function get_reg_pay1(){
		try{
//			$sql = "SELECT * FROM hisdb.debtortype";
//			//			    				echo $sql;
//			$stmt = $this->db->query($sql);
//			$arr = array();
//			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
//			{
//				$arr[] = $row;
//			}
//			return  json_encode($arr);


			return  '[{"sysno":"5","Comp":"","Code":"C","Description":"CASH","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"CC","Description":"Card","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"WGL","Description":"WAITING GL","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"OC","Description":"OPEN CARD","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"PWD","Description":"CONSULTANT GUARANTEE (PWD)","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""}]';




		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
	function get_reg_pay2()
	{
		try
		{
//			$sql = "SELECT * FROM hisdb.debtortype";
//			//			    				echo $sql;
//			$stmt = $this->db->query($sql);
//			$arr = array();
//			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
//			{
//				$arr[] = $row;
//			}
//			return  json_encode($arr);

			return  '[{"sysno":"5","Comp":"","Code":"P","Description":"PANEL","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"GC","Description":"GUARANTEE LETTER","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"WGL","Description":"WAITING GL","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""}]';




		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}
	}


	function get_debtor_list($type, $mrn)
	{
		try
		{
			if ( ($type == 1) )
			{
				$ty = " debtortype IN ('PR', 'PT') ";
			}
			else
			{
				$ty = " debtortype NOT IN ('PR', 'PT') ";
			}
		
			$sql = "SELECT * FROM debtor.debtormast WHERE debtorcode='" . ltrim($mrn, '0') . "' AND " . $ty;
			    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			
			$result = '{"data":' . json_encode($arr) . '}';
			//$result = json_encode($arr);
			
			return $result;

//			return $sql;
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}

	}
	
	function get_billtype_list($type)
	{
		try
		{
			if ( ($type == 'OP') )
			{
				$ty = " opprice=1 ";
			}
			else
			{
				$ty = " opprice=0 ";
			}
		
			$sql = "SELECT * FROM hisdb.billtymst WHERE compcode = '9a' AND " . $ty;
//			    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			
			$result = '{"data":' . json_encode($arr) . '}';
			//$result = json_encode($arr);
			
			return $result;

//			return $sql;
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}

	}
	
	function get_refno_list($debtorcode, $mrn)
	{
		try
		{		
			$sql = "SELECT * FROM hisdb.guarantee WHERE compcode = '9a' AND debtorcode='" . $debtorcode . "' AND mrn='" . ltrim($mrn, '0') . "' ";
//			    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			
			$result = '{"data":' . json_encode($arr) . '}';
			//$result = json_encode($arr);
			
			return $result;

//			return $sql;
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}

	}


	function get_patient_active(){
		try{
//				$sql = "SELECT * FROM hisdb.re";
//				//	    				echo $sql;
//				$stmt = $this->db->query($sql);
//				$arr = array();
//				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
//				{
//					$arr[] = $row;
//				}
//				return  json_encode($arr);
			return  '[{"sysno":"5","Comp":"","Code":"Yes","Description":"Yes","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"No","Description":"No","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""}]';
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
	function get_patient_urlconfidential(){
		try{
//				$sql = "SELECT * FROM hisdb.re";
//				//	    				echo $sql;
//				$stmt = $this->db->query($sql);
//				$arr = array();
//				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
//				{
//					$arr[] = $row;
//				}
//				return  json_encode($arr);
			return  '[{"sysno":"5","Comp":"","Code":"Yes","Description":"Yes","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"No","Description":"No","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""}]';
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
	function get_patient_mrfolder(){
		try{
//				$sql = "SELECT * FROM hisdb.re";
//				//	    				echo $sql;
//				$stmt = $this->db->query($sql);
//				$arr = array();
//				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
//				{
//					$arr[] = $row;
//				}
//				return  json_encode($arr);
			return  '[{"sysno":"5","Comp":"","Code":"Yes","Description":"Yes","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"No","Description":"No","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""}]';
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
	function get_patient_patientcat(){
		try{
//				$sql = "SELECT * FROM hisdb.re";
//				//	    				echo $sql;
//				$stmt = $this->db->query($sql);
//				$arr = array();
//				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
//				{
//					$arr[] = $row;
//				}
//				return  json_encode($arr);
			return  '[{"sysno":"5","Comp":"","Code":"S","Description":"Suspend","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"BL","Description":"Black List","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""},{"sysno":"7","Comp":"","Code":"L","Description":"Legal","createdBy":"admin","createdDate":"2013-04-11","LastUpdate":"0000-00-00","LastUser":"","RecStatus":""}]';
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}

	function get_episode($mrn)
	{

		try
		{
			$sql = "SELECT * FROM hisdb.episode WHERE compcode = '9A' AND mrn='" . $mrn . "' ORDER BY episno DESC LIMIT 1";
			echo $sql;

			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}

			$result = '{"data":' . json_encode($arr) . '}';
			//$result = json_encode($arr);

			return $result;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}

	function get_latest_mrn()
	{

		try
		{
			$sql = "SELECT MAX(mrn) + 1 AS latest_mrn FROM hisdb.pat_mast WHERE compcode = '9A' LIMIT 1";
			//echo $sql;

			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}

			//$result = '{"data":' . json_encode($arr) . '}';
			$result = json_encode($arr);

			return $result;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
	
	function get_relationship()
	{
		try
		{
			$sql = "SELECT relationshipcode, description FROM hisdb.relationship";
//			    				echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			
			//$result = '{"data":' . json_encode($arr) . '}';
			$result = json_encode($arr);
			
			return $result;

//			return $sql;
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}

	}

	function save_new_episode()
	{
		$epis_mrn = $_POST["epis_mrn"]; 
		$epis_no = $_POST["epis_no"];
		$epis_type = $_POST["epis_type"];
		$epis_maturity = $_POST["epis_maturity"];
		$epis_date = $_POST["epis_date"];
		$epis_time = $_POST["epis_time"];
		$epis_dept = $_POST["epis_dept"];
		$epis_src = $_POST["epis_src"];
		$epis_case = $_POST["epis_case"];
		$epis_doctor = $_POST["epis_doctor"];
		$epis_fin = $_POST["epis_fin"];
		$epis_paymode = $_POST["epis_pay"];
		$epis_payer = $_POST["epis_payer"];
		$epis_billtype = $_POST["epis_billtype"];
		$epis_refno = $_POST["epis_refno"];
		$epis_ourrefno = $_POST["epis_ourrefno"];
		$epis_preg = $_POST["epis_preg"];
		$epis_fee = $_POST["epis_fee"];

		if ($epis_maturity == "1")
		{
			$epis_newcase = "1";
			$epis_followup = "0";			
		}
		else
		{
			$epis_newcase = "0";
			$epis_followup = "1";			
		}

		$epis_nc_preg = $epis_preg;
		$epis_fu_preg = $epis_preg;

		$sql= "INSERT INTO hisdb.episode (
												compcode,
												mrn,
												episno,
												epistycode,
												newcase,
												followup,
												reg_date,
												reg_time,
												regdept,
												admsrccode,
												case_code,
												admdoctor,
												pay_type,
												pyrmode,
												billtype,
												pregnant,
												newpregnant,
												adminfees,
												add_date,													
												adduser,
												lastupdate,
												lasttime,
												lastuser,
												episactive,
												allocpayer) 
				VALUES (
						:epis_compcode,
						:epis_mrn,
						:epis_no, 
						:epis_type,
						:epis_newcase,
						:epis_followup,
						:epis_date,
						:epis_time,
						:epis_dept,
						:epis_src,
						:epis_case,
						:epis_doctor,
						:epis_paytype,
						:epis_paymode,
						:epis_billtype,
						:epis_fu_preg,
						:epis_nc_preg,
						:epis_fee, 
						:epis_createdate,
						:epis_createuser,
						:epis_updatedate,
						:epis_updatetime,							
						:epis_upduser,
						:epis_active,
						:epis_allocpayer); ";

		$stmt = $this->db->prepare($sql);
		$stmt->execute(array(
								"epis_compcode" => "9A",
								"epis_mrn" => $epis_mrn,
								"epis_no" => $epis_no,
								"epis_type" => $epis_type,
								"epis_newcase" => $epis_newcase,
								"epis_followup" => $epis_followup,
								"epis_date" => $epis_date,
								"epis_time" => $epis_time,
								"epis_dept" => $epis_dept,
								"epis_src" => $epis_src,
								"epis_case" => $epis_case,
								"epis_doctor" => $epis_doctor,
								"epis_paytype" => $epis_fin,
								"epis_paymode" => $epis_paymode,
								"epis_billtype" => $epis_billtype,
								"epis_fu_preg" => $epis_fu_preg,
								"epis_nc_preg" => $epis_nc_preg,
								"epis_fee" => $epis_fee,
								"epis_createdate" => date("Y-m-d"),
								"epis_createuser" => $_SESSION["username"],
								"epis_updatedate" => date("Y-m-d"),
								"epis_updatetime" => date('h:i:s'),
								"epis_upduser" => $_SESSION["username"],
								"epis_active" => 1,
								"epis_allocpayer" => 1
							));

		return "";
		// json_encode(array(
		// 						"sql" => $sql,
		// 						"epis_compcode" => "9A",
		// 						"epis_mrn" => $epis_mrn,
		// 						"epis_no" => $epis_no,
		// 						"epis_type" => $epis_type,
		// 						"epis_newcase" => $epis_newcase,
		// 						"epis_followup" => $epis_followup,
		// 						"epis_date" => $epis_date,
		// 						"epis_time" => $epis_time,
		// 						"epis_dept" => $epis_dept,
		// 						"epis_src" => $epis_src,
		// 						"epis_case" => $epis_case,
		// 						"epis_doctor" => $epis_doctor,
		// 						"epis_pay" => $epis_pay,
		// 						"epis_payer" => $epis_payer,
		// 						"epis_billtype" => $epis_billtype,
		// 						"epis_fu_preg" => $epis_fu_preg,
		// 						"epis_nc_preg" => $epis_nc_preg,
		// 						"epis_fee" => $epis_fee,
		// 						"epis_createdate" => date("Y-m-d"),
		// 						"epis_createuser" => $_SESSION["username"],
		// 						"epis_updatedate" => date("Y-m-d"),
		// 						"epis_updatetime" => date('h:i:s'),
		// 						"epis_upduser" => $_SESSION["username"],
		// 						"epis_active" => 1,
		// 						"epis_allocpayer" => 1
		// 					));
	}
	
	function get_patient_payer_guarantee($patid, $limit)
	{
		try
		{
			$sql = "SELECT *, b.description AS relatedesc FROM hisdb.guarantee a
					LEFT JOIN hisdb.relationship b ON a.relatecode = b.relationshipcode
					WHERE a.`mrn` = '1' ORDER BY a.`adddate` ";
			
			if ($limit == 1) { $sql .= " LIMIT 1 "; }
			// echo $sql;
			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}
			return  '{"payer":' . json_encode($arr) . '}';
		} 
		catch(PDOException $e) 
		{
			echo $e->getMessage();
		}
	}
	
	function save_new_guarantor($rq)
	{
		try    
		{
			$apptdate = $rq['schDateTime'];
			$appttime = $rq['schTime'];
							
			$case = $rq['patCaseid'];
			$icnum = $rq['patIc'];
			$mrn = $rq['cmb_mrn'];
			$pat_name = $rq['patName'];
			$remarks = $rq['patNote'];
			$docid = $rq['patDoc-id'];
			$remarks = $rq['patNote'];
			$status = $rq['patStatus'];
			$telno = $rq['patContact'];
			$telhp = $rq['patHp'];
			$faxno = $rq['patFax'];
			
			$sql = "INSERT INTO hisdb.apptbook (icnum,apptdate,appttime,case_code,mrn,pat_name,remarks,location,telno,telhp,faxno,apptstatus) VALUES (
					'{$icnum}',
					STR_TO_DATE('{$apptdate}', '%m/%d/%Y'),
					'{$appttime}',
					'{$case}',
					'{$mrn}',
					'{$pat_name}',
					'{$remarks}',
					'{$docid}',
					'{$telno}',
					'{$telhp}',
					'{$faxno}',
					'Open')";
					
					//print_r($sql);return;

			$stmt = $this->db->prepare($sql);
			
			$stmt->execute();
			
			$result = '{"result":{"msg":"New records created successfully"}}';
			return $result;
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}

}

?>
