<?php 

	include("config.php"); 
	include("db/" . $dbtype . ".php"); 	

	include("lib/" . $dbtype . "/mobile/get_table_default.php"); 
	include("lib/" . $dbtype . "/mobile/save_table_default.php");

	
	// getting all possible and valid action URL
	$possible_url = array(
							"get_table_default",
							"get_value_default",
							"save_table_default",
							"save_table_default_arr"
						 );
	
	$value = "An error has occurred getting JSON value.";

	if (isset($_GET["action"]) && in_array($_GET["action"], $possible_url))
	{
	  switch ($_GET["action"])
	    {
			case "get_table_default":
				$TableModel = new TableModel($conn,$_GET);
				$value = $TableModel->get_table();
		        break;
			case "get_value_default":
				$TableModel = new TableModel($conn,$_GET);
				$value = $TableModel->get_value();
		        break;
			case "save_table_default":
				$EditTableModel = new EditTable($conn,$_GET,$_POST);
				$value = $EditTableModel->edit_table(true);
		        break;
			case "save_table_default_arr":
				$conn->beginTransaction();
				foreach ($_GET['array'] as $val) {
					$EditTableModel = new EditTable($conn,$val,$_POST);
					$value = $EditTableModel->edit_table(false);
				}
				$conn->commit();
		        break;
		}
	}
	
	include("accesscontrol.php");
	echo $value;

?>