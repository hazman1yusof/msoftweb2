<?php

	class ProfileModel
	{
	    protected $db;

	    public function __construct(PDO $db)
	    {
	        $this->db = $db;
	    }

		function login_detail_check($usrid, $pwd, $company)
		{
				$sql = "SELECT a.groupid, a.username, a.compcode, b.bgpic, b.logo1, b.logo1width ,deptcode
						FROM sysdb.users a 
						LEFT JOIN sysdb.company b ON a.compcode = b.compcode
						WHERE a.username='{$usrid}' AND a.password='{$pwd}' AND a.compcode='{$company}'";
						

			$stmt = $this->db->query($sql);

			$count = $stmt->rowCount();

			// If result matched $myusername and $mypassword, table row must be 1 row
			if($count==1)
			{			
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				session_start();
				$_SESSION['groupid']=$row['groupid'];
				$_SESSION['username']=$row['username'];
				$_SESSION['company']=$row['compcode'];
				$_SESSION['deptcode']=$row['deptcode'];
				$_SESSION['bgpic']=$row['bgpic'];
				$_SESSION['logo1']=$row['logo1'];
				$_SESSION['logo1width']=$row['logo1width'];

				$result = 1;
			}
			else 
			{
				$result = 0;
			}

			$result = '{"login_check":[ {"isValid" : "' . $result . '" } ] }';

			return $result;
		}

		function get_profile_data()
		{
			if(isset($_SESSION['etpcode']))
			{
				$sql= "SELECT * FROM tbl_entrep ";
				$sql.= "LEFT JOIN tbl_country on tbl_country.c_id = tbl_entrep.e_country ";
				$sql.= "LEFT JOIN tbl_state on tbl_state.s_id = tbl_entrep.e_state ";
				$sql.= "LEFT JOIN tbl_indcat on tbl_indcat.i_id = tbl_entrep.e_indcat ";
				$sql.= "LEFT JOIN tbl_indtype on tbl_indtype.t_id = tbl_entrep.e_indtype ";
				$sql.= "WHERE tbl_entrep.e_etpcode='" . $_SESSION['etpcode'] . "' ";			
			

				$rs  = mysql_query($sql);

				$arr = array();
				while($obj = mysql_fetch_object($rs)) 
				{
					$arr[] = $obj;
				}
				$result = '{"user_profile":' . json_encode($arr) . '}';
			}
			else
			{
				$result = '{"user_profile": "no data"}';
			}

			return $result;	
		}

		function update_profile($elm_name)
		{
			
		}
	}

?>
