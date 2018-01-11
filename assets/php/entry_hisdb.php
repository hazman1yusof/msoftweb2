<?php 

	include("config.php"); 
	include("db/" . $dbtype . ".php"); 	

	include("lib/" . $dbtype . "/get_table_default.php"); 
	include("lib/" . $dbtype . "/save_table_default.php"); 
	include("lib/" . $dbtype . "/inputCheck.php"); 
	include("lib/" . $dbtype . "/patient.php");

	// Create an instance for all available Model classes
	$PatientModel = new PatientModel($conn);									 
	
	// getting all possible and valid action URL
	$possible_url = array(
							"get_table_default",
							"get_value_default",
							"save_table_default",
							"save_table_default_arr",
							"get_patient_list",
                            "get_patient_detail",
                            "get_patient_last",
                            "get_patient_title",
							"get_patient_idtype","get_patient_occupation","get_patient_areacode",
							"get_patient_sex","get_patient_citizen","get_patient_race",
							"get_patient_religioncode","get_patient_urlmarital","get_patient_language",
							"get_patient_relationship", "get_all_company", "get_patient_active","get_patient_urlconfidential",
							"get_patient_mrfolder","get_patient_patientcat","get_reg_dept","get_reg_source",
							"get_reg_case","get_doctor_by_discipline","get_reg_doctor","get_reg_fin",
							"get_reg_pay1","get_reg_pay2",
							"get_debtor_list", "get_billtype_list", "get_refno_list",
							"get_episode", "save_new_episode",
							"get_all_relationship",
							"get_patient_payer_guarantee",
							"check_existing_patient",
							"add_new_guarantor",
							"get_latest_mrn",
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
		   	case "get_patient_list":
		    	$PatientModel = new PatientModel($conn);
		        $value = $PatientModel->get_patient_list("");
		        break;
			case "get_patient_detail":
                $patid = $_REQUEST["patid"];
		    	$PatientModel = new PatientModel($conn);
		        $value = $PatientModel->get_patient_detail($patid);
		        break;
            case "get_patient_last":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_last();
				break;
            case "get_patient_title":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_title();
				break;
			case "get_patient_idtype":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_idtype();
				break;
			case "get_patient_occupation":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_occupation();
				break;
			case "get_patient_areacode":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_areacode();
				break;
			case "get_patient_sex":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_sex();
				break;
			case "get_patient_citizen":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_citizen();
				break;
			case "get_patient_race":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_race();
				break;
			case "get_patient_religioncode":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_religioncode();
				break;
			case "get_patient_urlmarital":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_urlmarital();
				break;
			case "get_patient_language":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_language();
				break;
			case "get_patient_relationship":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_relationship();
				break;
			case "get_all_company":
		    	$value = $PatientModel->get_all_company();
		        break;
			case "get_reg_dept":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_reg_dept();
				break;
			case "get_patient_active":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_active();
				break;
			case "get_patient_urlconfidential":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_urlconfidential();
				break;
			case "get_patient_mrfolder":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_mrfolder();
				break;
			case "get_patient_patientcat":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_patientcat();
				break;
			case "get_reg_source":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_reg_source();
				break;
			case "get_reg_case":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_reg_case();
				break;
			case "get_doctor_by_discipline":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_reg_doctor($_GET["disc"]);
				break;
			case "get_reg_doctor":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_reg_doctor();
				break;
			case "get_reg_fin":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_reg_fin();
				break;
			case "get_reg_pay1":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_reg_pay1();
				break;
			case "get_reg_pay2":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_reg_pay2();
				break;
			case "get_debtor_list":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_debtor_list($_GET["tp"], $_GET["mrn"]);
				break;
			case "get_billtype_list":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_billtype_list($_GET["tp"]);
				break;
			case "get_refno_list":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_refno_list($_GET["tp"], $_GET["mrn"]);
				break;
			case "get_episode":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_episode($_GET["mrn"]);
				break;
			case "get_latest_mrn":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_latest_mrn();
				break;
			case "save_new_episode":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->save_new_episode();
				break;
			case "get_patient_payer_guarantee":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_payer_guarantee($_REQUEST["patid"], $_REQUEST["limit"]);
				break;
			case "get_all_relationship":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_relationship();
				break;
			case "add_new_guarantor":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->save_new_guarantor($_REQUEST);
				break;
			case "check_existing_patient":
				$PatientModel = new PatientModel($conn);
				$value = $PatientModel->get_patient_list($_REQUEST);
				break;
	    }
	}

	echo $value;

?>