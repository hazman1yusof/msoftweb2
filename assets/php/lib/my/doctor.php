<?php

/*
1. get doctor list
2. get doctor info
3. get doctor available
5. add doctor leave
6. edit doctor leave
7. add doctor break
8. edit doctor break
*/

	class DoctorModel
	{
	    protected $db;

	    public function __construct(PDO $db)
	    {
	        $this->db = $db;
	    }

	    function get_doctor_list($f,$d)
		{
			
			try    
			{
				$strwhere = '';
				
				if($d != ''){
					$d = str_replace(" ","%",$d);
					
					$strwhere = " AND resourcecode LIKE '%{$d}%' OR description LIKE '%{$d}%'";
				}

				$sql = "SELECT resourcecode as id,description FROM hisdb.apptresrc WHERE type = 'doc' {$strwhere}";

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
		
	    function get_doctor_info($f)
		{
			
			try    
			{
				$sql = "SELECT * FROM hisdb.apptresrc WHERE type = 'doc' AND description LIKE '%{$f}%'";

				$stmt = $this->db->query($sql);
				$arr = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$arr[] = $row;
				}

				$result = '{"doctor-info":' . json_encode($arr) . '}';

				return $result;
			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}
		
	    function get_doctor_break($f)
		{
			try    
			{
				$sql = "SELECT * FROM hisdb.apptbreak WHERE doctorcode = '{$f}'";

				$stmt = $this->db->query($sql);
				$arr = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$arr[] = $row;
				}

				return json_encode($arr);
			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}
		
	    function get_doctor_leave($f)
		{
			try    
			{
				$sql = "SELECT * FROM hisdb.apptleave WHERE resourcecode = '{$f}'";

				$stmt = $this->db->query($sql);
				$arr = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$arr[] = $row;
				}

				return json_encode($arr);
			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}
		
	    function get_doctor_available($t,$f)
		{
			
			try    
			{
			
$sql = "select * from apptbook limit 10";
$q=$this->db->prepare($sql);
if (($res = $q->execute()) === FALSE) {
    echo 'Query failed!'.$this->db->errorInfo();
    exit;
}
$v = $q->fetchColumn();

return $v;



			$t = 1460520000;
			$f = 'amir';
			
				$sql = "SELECT UNIX_TIMESTAMP(CONCAT(startdate,' ',starttime)) as stm,UNIX_TIMESTAMP(CONCAT(startdate,' ',endtime)) as etm FROM apptbreak WHERE doctorcode = '{$f}'
						AND {$t} BETWEEN UNIX_TIMESTAMP(CONCAT(startdate,' ',starttime)) AND UNIX_TIMESTAMP(CONCAT(startdate,' ',endtime))
						UNION ALL
						SELECT UNIX_TIMESTAMP(CONCAT(datefr,' ','00:00:00')) as stm,UNIX_TIMESTAMP(CONCAT(dateto,' ','23:59:59')) as etm FROM apptleave WHERE resourcecode = '{$f}'
						AND {$t} BETWEEN UNIX_TIMESTAMP(CONCAT(datefr,' ','00:00:00')) AND UNIX_TIMESTAMP(CONCAT(dateto,' ','23:59:59'))
						UNION ALL
						SELECT UNIX_TIMESTAMP(CONCAT(datefr,' ','00:00:00')) as stm,UNIX_TIMESTAMP(CONCAT(dateto,' ','23:59:59')) as etm FROM apptph
						WHERE {$t} BETWEEN UNIX_TIMESTAMP(CONCAT(datefr,' ','00:00:00')) AND UNIX_TIMESTAMP(CONCAT(dateto,' ','23:59:59'))";


				//$sql = "SELECT * FROM hisdb.apptresrc WHERE type = 'doc' AND description LIKE '%{$f}%'";

				$stmt = $this->db->query($sql);
				
				//error case
				if(!$stmt)
				{
				  return("Execute query error, because: ". $this->db->errorInfo());
				}
								
				$arr = array();
				while ($row = $stmt->fetchAll())
				{
					$arr[] = $row;
				}

				$result = '{"doctor_available":' . json_encode($arr) . '}';

				return $result;
			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}
		
	}

?>
