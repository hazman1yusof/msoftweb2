<?php
/*
1. get appointment list
2. get appointment detail
3. add appointment
4. edit appointment
5. get doctor availibility
6. data cleanup
7. get case type
8. get public holiday list
9. add public holiday info
9. edit public holiday
10. get_resources_list
*/


	class ApptModel
	{
	    protected $db;

	    public function __construct(PDO $db)
	    {
	        $this->db = $db;
	    }

	    function data_cleanup()
	    {
	    	try
	    	{
	    		$sql = "UPDATE apptbook set apptstatus = 'Not Attend' WHERE apptstatus = 'Open' AND DATE(apptdate) < DATE(NOW())";

				$stmt = $this->db->prepare($sql);
				
				$stmt->execute();
				
				return $smtp."cleanup data successfully";
				
				$stmt->close();
			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}

	    function get_patient_list($f,$d)
		{ 
			try    
			{
				$strwhere = '';
				
				if($d != ''){
					$d = str_replace(" ","%",$d);
					//$strwhere = " WHERE description LIKE '%{$f}%'";
					
					$strwhere = " WHERE {$f} LIKE '%{$d}%'";
				}
								
				$sql = "SELECT * FROM hisdb.pat_mast {$strwhere}";

				$stmt = $this->db->query($sql);
				$arr = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$arr[] = $row;
				}

				$result = 
					'{"current": 1,"rowCount": 20,"rows": ' . json_encode($arr) . ',
					  "total": "'.count($arr).'", "search":"'.$d.'"
					}';

				return $result;

			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}

	    function get_patient_dtl($f,$d)
		{ 
			try    
			{
				$strwhere = '';
				
				if($d != '' && $f != 'name'){
					$d = str_replace(" ","%",$d);
					//$strwhere = " WHERE description LIKE '%{$f}%'";
					
					$strwhere = " WHERE {$f} = '{$d}'";
				}
				
				if($d != '' && $f == 'name')
					$strwhere = " WHERE {$f} = '{$d}'";				
								
				$sql = "SELECT * FROM hisdb.pat_mast {$strwhere}";

				$stmt = $this->db->query($sql);
				$arr = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$arr[] = $row;
				}

				$result = 
					'{"current": 1,"rowCount": 20,"rows": ' . json_encode($arr) . ',
					  "total": "'.count($arr).'", "search":"'.$d.'"
					}';

				return $result;

			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}

	    function get_casetype($f,$d)
		{
			
			try    
			{
				$strwhere = '';
				
				if($d != ''){
					$d = str_replace(" ","%",$d);
					//$strwhere = " WHERE description LIKE '%{$f}%'";
					
					$strwhere = " WHERE case_code LIKE '%{$d}%' OR description LIKE '%{$d}%'";
				}
								
				$sql = "SELECT * FROM hisdb.casetype {$strwhere}";

				$stmt = $this->db->query($sql);
				$arr = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$arr[] = $row;
				}

				$result = 
					'{"current": 1,"rowCount": 20,"rows": ' . json_encode($arr) . ',
					  "total": "'.count($arr).'", "search":"'.$d.'"
					}';

				return $result;

			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}

	    function get_appointment_list($typ,$id,$start,$end)
		{
			try    
			{
				if($typ=='doctor'){				
					$sql = "
					SELECT sysno AS id, CONCAT(apptdate,'T',appttime) AS `start`
					,(SELECT COUNT(*) FROM hisdb.apptbook WHERE location = '{$id}' AND apptstatus = 'Open' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Open'
					,(SELECT COUNT(*) FROM hisdb.apptbook WHERE location = '{$id}' AND apptstatus = 'Attend' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Attend'
					,(SELECT COUNT(*) FROM hisdb.apptbook WHERE location = '{$id}' AND apptstatus = 'Not Attend' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Not Attend'
					,(SELECT COUNT(*) FROM hisdb.apptbook WHERE location = '{$id}' AND apptstatus = 'Cancel' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Cancel' 
					FROM hisdb.apptbook a WHERE location = '{$id}' AND apptdate BETWEEN '{$start}' AND '{$end}'
					GROUP BY LEFT(apptdate,10)";
					
					$stmt = $this->db->query($sql);
					$arr = array();
					$apptdt = '';
					
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$ttl = $row['Open']+$row['Attend']+$row['Not Attend']+$row['Cancel'];
						
							$row['title'] = '<table style="font-size:10px"><tr style="text-align:center"><td><img alt="Open" src="../../../../assets/img/icon/i-open.jpg" width="15" /></td><td><img alt="Open" src="../../../../assets/img/icon/i-attend.jpg" width="15" /></td><td> <img alt="Open" src="../../../../assets/img/icon/i-xattend.jpg" width="15" /></td><td> <img alt="Open" src="../../../../assets/img/icon/i-cancel.jpg" width="15" /></td><td>+</td></tr>
											<tr style="text-align:center"><td>'.$row['Open'].'</td><td>'.$row['Attend'].'</td><td>'.$row['Not Attend'].'</td><td>'.$row['Cancel'].'</td><td>'.$ttl.'</td></tr></table>';					
						$arr[] = $row;
					}
	
					$result = json_encode($arr);
				}else if ($typ == 'grid'){
					$sql = "
					SELECT a.sysno AS id,
					appttime AS `start`,
					CONCAT(apptdate,'T',appttime) AS `x-start`,
					CONCAT(apptdate,'T',(appttime + INTERVAL 30 MINUTE)) AS `end`,
					apptstatus AS title,
					#CASE apptstatus WHEN 'Open' THEN 'blue' WHEN 'Attend' THEN 'green' WHEN 'Not Attend' THEN 'brown' WHEN 'Cancel' THEN 'grey' ELSE 'red' END AS 'color',
					b.mrn,location AS 'doctor',
					b.name,b.newic as 'noic',
					#'' AS 'Open','' AS 'Attend','' AS 'Not Attend','' AS 'Cancel',
					a.*
					FROM hisdb.apptbook a
					LEFT JOIN hisdb.membership b ON a.icnum = b.newic ";
					
					if($id != '')
						$sql = $sql." WHERE location = '{$id}' AND apptdate BETWEEN '{$start}' AND '{$end}'";
					else
						$sql = $sql." WHERE apptdate = DATE(NOW())";
						
					$sql = $sql." ORDER BY apptdate,appttime ASC";

					$stmt = $this->db->query($sql);

					while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$arr[] = $row;
					}	
					
					$result = json_encode($arr);
				}else if ($typ == 'patient'){
					$sql = "SELECT sysno AS id,
							CONCAT(apptdate,'T',appttime) AS `start`,
							CONCAT(apptdate,'T',(appttime + INTERVAL 30 MINUTE)) AS `end`,
							apptstatus AS title,
							CASE apptstatus WHEN 'Open' THEN 'blue' WHEN 'Attend' THEN 'green' WHEN 'Not Attend' THEN 'brown' WHEN 'Cancel' THEN 'grey' ELSE 'red' END AS 'color',
							(SELECT COUNT(*) FROM hisdb.apptbook WHERE apptstatus = 'Open' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Open',					
							(SELECT COUNT(*) FROM hisdb.apptbook WHERE apptstatus = 'Attend' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Attend',						
							(SELECT COUNT(*) FROM hisdb.apptbook WHERE apptstatus = 'Not Attend' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Not Attend',						
							(SELECT COUNT(*) FROM hisdb.apptbook WHERE apptstatus = 'Cancel' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Cancel'						
							FROM hisdb.apptbook a
							WHERE icnum = '{$id}' AND apptdate BETWEEN '{$start}' AND '{$end}' ORDER BY id DESC ";
				}else if ($typ == 'gridpatient'){
					$sql = "SELECT a.sysno AS id,
							CONCAT(apptdate,'T',appttime) AS `start`,
							CONCAT(apptdate,'T',(appttime + INTERVAL 30 MINUTE)) AS `end`,
							apptstatus AS title,
							CASE apptstatus WHEN 'Open' THEN 'blue' WHEN 'Attend' THEN 'green' WHEN 'Not Attend' THEN 'brown' WHEN 'Cancel' THEN 'grey' ELSE 'red' END AS 'color',
							b.mrn,location AS 'doctor',
							b.name,b.newic as 'noic'
							,'' AS 'Open','' AS 'Attend','' AS 'Not Attend','' AS 'Cancel'
							FROM hisdb.apptbook a
							LEFT JOIN hisdb.membership b ON a.icnum = b.newic ";
							
							if($id != '')
								$sql = $sql." WHERE icnum = '{$id}' AND apptdate BETWEEN '{$start}' AND '{$end}'";
							else
								$sql = $sql." WHERE apptdate = DATE(NOW())";

							$sql = $sql." ORDER BY apptdate DESC";

				}else{
					$sql = "SELECT sysno AS id,
							CONCAT(apptdate,'T',appttime) AS `start`,
							CONCAT(apptdate,'T',(appttime + INTERVAL 30 MINUTE)) AS `end`,
							CONCAT(pat_name) AS title,
							CASE apptstatus WHEN 'Open' THEN 'blue' WHEN 'Attend' THEN 'green' WHEN 'Not Attend' THEN 'brown' WHEN 'Cancel' THEN 'grey' ELSE 'red' END AS 'color',
							(SELECT COUNT(*) FROM hisdb.apptbook WHERE apptstatus = 'Open' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Open',					
							(SELECT COUNT(*) FROM hisdb.apptbook WHERE apptstatus = 'Attend' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Attend',						
							(SELECT COUNT(*) FROM hisdb.apptbook WHERE apptstatus = 'Not Attend' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Not Attend',						
							(SELECT COUNT(*) FROM hisdb.apptbook WHERE apptstatus = 'Cancel' AND DATE(apptdate) = DATE(a.apptdate)) AS 'Cancel'						
							FROM hisdb.apptbook a
							WHERE apptdate = DATE(NOW())
							UNION ALL
							SELECT 
							'' AS id,
							datefr AS `start`,
							dateto AS `end`,
							remark AS title,
							'red' AS 'color',
							'' AS 'Open','' AS 'Attend','' AS 'Not Attend','' AS 'Cancel'
							FROM hisdb.apptph a";
				}
//print_r($sql);return;


				return $result;
			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}

	    function add_appointment_info($rq)
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
		
	    function update_appointment_info($rq)
		{
			
			try    
			{
				$apptdate = $rq['schDateTime'];
				$appttime = $rq['schTime'];

				$id = $rq['sysno'];
				$status = $rq['patStatus'];
				$case = $rq['patCaseid'];
				$remarks = $rq['patNote'];
				$tel = $rq['patContact'];
				$hp = $rq['patHp'];
				$fax = $rq['patFax'];	
							
				$sql = "UPDATE hisdb.apptbook SET 
						apptdate = STR_TO_DATE('{$apptdate}', '%m/%d/%Y'),
						appttime = '{$appttime}',
						apptstatus = '{$status}',
						case_code = '{$case}',
						telno = '{$tel}',
						telhp = '{$hp}',
						faxno = '{$fax}',
						remarks = '{$remarks}' 
						WHERE sysno = '{$id}'";

				$stmt = $this->db->prepare($sql);
				
				$stmt->execute();
				
				$result = '{"result":{"msg":"Records updated successfully"}}';
				return $result;
			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}
		
	    function get_appointment_info($f)
		{
			
			try    
			{
				$sql = "SELECT a.*, c.*, d.description AS casedesc, a.remarks AS patNote, b.newic AS patIc
						FROM hisdb.apptbook a
						LEFT JOIN hisdb.pat_mast b ON b.mrn = a.mrn
						LEFT JOIN hisdb.apptresrc c ON c.resourcecode = a.location
						LEFT JOIN hisdb.casetype d ON d.case_code = a.case_code
						WHERE a.sysno = '{$f}'";
						
						//return $sql;
				$stmt = $this->db->query($sql);

				$arr = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$arr[] = $row;
				}

				$result = '{"appt":' . json_encode($arr) . '}';

				return $result;
			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}
		
	    function get_appt_lst($docid,$dt)
		{
			
			try    
			{
				$sql = "SELECT a.sysno,a.icnum,a.*
						FROM hisdb.apptbook a
						WHERE a.location = '{$docid}' AND apptdate = STR_TO_DATE('{$dt}', '%m/%d/%Y')";
						
				$stmt = $this->db->query($sql);
				$arr = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$arr[] = $row;
				}

				$result = '{"appt_lst":' . json_encode($arr) . '}';

				return $result;
			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}
		
		function get_resources_list()
		{
			$sql= "SELECT * FROM hisdb.apptresrc ";
			$sql.= "WHERE compcode='9A' AND type='DOC' ";

			$stmt = $this->db->query($sql);
			$arr = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$arr[] = $row;
			}

			/*foreach($arr as $key => $value)
			{
			  $arr[$key]['register_date'] = date_format(new DateTime($value["register_date"]), "d M Y"); //date('d/m/Y',$value['register_date']);
			  $arr[$key]['period_from'] = date_format(new DateTime($value["period_from"]), "d M Y");
			  $arr[$key]['period_to'] = date_format(new DateTime($value["period_to"]), "d M Y");
			}*/
			
			$result = '{"data":' . json_encode($arr) . '}';

			return $result;
		}

	}

?>
