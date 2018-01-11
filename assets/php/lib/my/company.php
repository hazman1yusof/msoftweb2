<?php

	class CompanyModel
	{
	    protected $db;

	    public function __construct(PDO $db)
	    {
	        $this->db = $db;
	    }

	    function get_all_companies()
		{
			
			try    
			{
				$sql = "SELECT compcode, name FROM sysdb.company ";

				// $arr = array();
				// while($obj = mysql_fetch_object($rs)) 
				// {
				// 	$arr[] = $obj;
				// }

				$stmt = $this->db->query($sql);
				$arr = array();
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$arr[] = $row;
				}

				$result = '{"companies":' . json_encode($arr) . '}';

				return $result;
			}
			catch(PDOException $e)
		    {
		    	echo $e->getMessage();
		    }
		}
	}

?>
