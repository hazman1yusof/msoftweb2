<?php 

	include("config.php"); 
	include("db/" . $dbtype . ".php"); 	

	include("lib/" . $dbtype . "/profile.php"); 
	include("lib/" . $dbtype . "/company.php");
	include("lib/" . $dbtype . "/menu.php"); 
	include("lib/" . $dbtype . "/get_table_default.php"); 
	include("lib/" . $dbtype . "/save_table_default.php"); 
	include("lib/" . $dbtype . "/inputCheck.php"); 
	include("lib/" . $dbtype . "/announcement.php");

	include("lib/" . $dbtype . "/patient.php");

	// Create an instance for all available Model classes
	$ProfileModel = new ProfileModel($conn);
	$CompanyModel = new CompanyModel($conn);
	$MenuModel = new MenuModel($conn);								 
	
	// getting all possible and valid action URL
	$possible_url = array(
							"signing_in",
							"get_profile_data",
							"get_all_companies",
							"create_menu",
							"get_table_default",
							"get_value_default",
							"get_excel_default",
							"save_table_default",
							"save_table_default_arr",
							"dpHeaderSave",
							"dpDetail_save",
							"input_check",
							"announcement",
							"chqreg_save",
							"sysparam_save",
							"doctor_save",
							"bankTransfer_save",
							"receipt_save",
							"bankreg_save",
							"cancel_save",
							"dpreg_save",
							"get_effdate",
							"cdHeaderSave",
							"cdDetail_save",
							"cdreg_save",
							"ftHeaderSave",
							"tagno_save",
							"menu_maintenance_save",
							"group_maintenance_save",
							"invTran_save",
							"invTranDetail_save",
							"delOrd_save",
							"delOrdDetail_save",
							"invoiceAP_save",
							"APHeaderSave",
							"mpages_save",
							"chat",
							"invTranPost_save",
							"invoiceAP_post",
							"stockReq_header_save",
							"stockReq_detail_save",
							"purOrder_header_save",
							"purOrder_detail_save",
							"purReq_header_save",
							"purReq_detail_save",
						 );
						 
	$value = "An error has occurred getting JSON value.";

	if (isset($_GET["action"]) && in_array($_GET["action"], $possible_url))
	{
	  switch ($_GET["action"])
	    {
	    	case "signing_in":
		        $value = $ProfileModel->login_detail_check($_GET["usrid"], $_GET["pwd"], $_GET["comp"]);
		        break;
		    case "get_profile_data":
		        $value = $ProfileModel->get_profile_data();
		        break;
		    case "get_all_companies":
		    	$value = $CompanyModel->get_all_companies();
		        break;
		    case "announcement":
				$annModel = new announcement($conn);
				$value = $annModel->generate();
				break;
		    case "create_menu":
		    	$value = $MenuModel->create_main_menu();
		        break;
			case "get_table_default":
				$TableModel = new TableModel($conn,$_GET);
				$value = $TableModel->get_table();
		        break;
			case "get_value_default":
				$TableModel = new TableModel($conn,$_GET);
				$value = $TableModel->get_value();
		        break;
			case "get_excel_default":
				$TableModel = new TableModel($conn,$_GET);
				$value = $TableModel->get_excel();
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
		   case "dpHeaderSave":
		   		include("lib/" . $dbtype . "/dpHeaderSave.php");
				$EditTableCMModel = new EditTableCM($conn,$_GET,$_POST);
				$value = $EditTableCMModel->edit_table(true);
		        break;
		    case "dpDetail_save":
		    	include("lib/" . $dbtype . "/dpDetail_save.php");
				$dpDetail_save = new dpHeader_save($conn);
				$value = $dpDetail_save->save();
		        break;
			case "input_check":
				$inputCheck = new InputCheck($conn);
				$value = $inputCheck->check();
		        break;
			case "chqreg_save":
				include("lib/" . $dbtype . "/chqreg_save.php");
				$chqreg_save = new Chqreg_save($conn);
				$value = $chqreg_save->save();
		        break;
			case "sysparam_save":
				include("lib/" . $dbtype . "/sysparam_save.php");
				$sysparam_save = new Sys_save($conn);
				$value = $sysparam_save->save();
		        break;
		    case "doctor_save":
				include("lib/" . $dbtype . "/doctor_save.php");
				$doctor_save = new Doctor_save($conn, $_GET, $_POST, $_REQUEST);
				$value = $doctor_save->save();
		        break;
		    case "bankTransfer_save":
				include("lib/" . $dbtype . "/bankTransfer_save.php");
				$bankTransfer_save = new BankTransfer_save($conn, $_GET, $_POST, $_REQUEST);
				$value = $bankTransfer_save->save();
		        break;
			case "receipt_save":
				include("lib/" . $dbtype . "/receipt_save.php");
				$receipt_save = new receipt_save($conn,$_GET,$_POST);
				$value = $receipt_save->edit_table(true);
		        break;
		    case "bankreg_save":
				include("lib/" . $dbtype . "/bankreg_save.php");
				$bankreg_save = new bankreg_save($conn);
				$value = $bankreg_save->edit_table(true);
		        break;
		     case "cancel_save":
				include("lib/" . $dbtype . "/cancel_save.php");
				$cancel_save = new cancel_save($conn);
				$value = $cancel_save->edit_table(true);
		        break;
		    case "dpreg_save":
				include("lib/" . $dbtype . "/dpreg_save.php");
				$dpreg_save = new dpreg_save($conn);
				$value = $dpreg_save->edit_table(true);
		        break;
			case "get_effdate":
				include("lib/" . $dbtype . "/get_effdate.php");
				$get_effdate = new get_effdate($conn);
				$value = $get_effdate->effdate_for($_GET['type']);
		        break;
		    case "cdHeaderSave":
		   		include("lib/" . $dbtype . "/cdHeaderSave.php");
				$EditTableCMModel = new EditTableCM($conn,$_GET,$_POST);
				$value = $EditTableCMModel->edit_table(true);
		        break;
		    case "cdDetail_save":
		    	include("lib/" . $dbtype . "/cdDetail_save.php");
				$cdDetail_save = new cdHeader_save($conn);
				$value = $cdDetail_save->save();
		        break;
		    case "ftHeaderSave":
		   		include("lib/" . $dbtype . "/ftHeaderSave.php");
				$EditTableCMModel = new EditTableCM($conn,$_GET,$_POST);
				$value = $EditTableCMModel->edit_table(true);
		        break;
		    case "cdreg_save":
				include("lib/" . $dbtype . "/cdreg_save.php");
				$cdreg_save = new cdreg_save($conn);
				$value = $cdreg_save->edit_table(true);
		        break;
		    case "tagno_save":
				include("lib/" . $dbtype . "/tagno_save.php");
				$tagno_save = new tagno_save($conn);
				$value = $tagno_save->edit_table(true);
		        break;
		    case "menu_maintenance_save":
				include("lib/" . $dbtype . "/menu_maintenance_save.php");
				$menu_maintenance_save = new menu_maintenance_save($conn);
				$value = $menu_maintenance_save->edit_table(true);
		        break;
		    case "group_maintenance_save":
				include("lib/" . $dbtype . "/group_maintenance_save.php");
				$group_maintenance_save = new group_maintenance_save($conn);
				$value = $group_maintenance_save->edit_table(true);
		        break;
		    case "invTran_save":
		   		include("lib/" . $dbtype . "/invTran_save.php");
				$invTran_save = new invTran_save($conn,$_GET,$_POST);
				$value = $invTran_save->edit_table(true);
		        break;
		    case "invTranDetail_save":
		    	include("lib/" . $dbtype . "/invTranDetail_save.php");
				$invTranDetail_save = new invTranDetail_save($conn);
				$value = $invTranDetail_save->save();
		        break;
		    case "delOrd_save":
		   		include("lib/" . $dbtype . "/delOrd_save.php");
				$delOrd_save = new delOrd_save($conn,$_GET,$_POST);
				$value = $delOrd_save->edit_table(true);
		        break;
		    case "delOrdDetail_save":
		    	include("lib/" . $dbtype . "/delOrdDetail_save.php");
				$delOrdDetail_save = new delOrdDetail_save($conn);
				$value = $delOrdDetail_save->save();
		        break;
		    case "invoiceAP_save":
		   		include("lib/" . $dbtype . "/invoiceAP_save.php");
				$invoiceAP_save = new invoiceAP_save($conn,$_GET,$_POST);
				$value = $invoiceAP_save->edit_table(true);
		        break;
		    case "APHeaderSave":
		   		include("lib/" . $dbtype . "/APHeaderSave.php");
				$EditTableCMModel = new EditTableCM($conn,$_GET,$_POST);
				$value = $EditTableCMModel->edit_table(true);
		        break;
		    case "mpages_save":
				include("lib/" . $dbtype . "/mpages_save.php");
				$mpages_save = new mpages_save($conn);
				$value = $mpages_save->edit_table(true);
		        break;
		    case "chat":
				include("lib/" . $dbtype . "/chat.php");
				$chat = new chat($conn);
				$value = $chat->edit_table(true);
		        break;
		    case "invTranPost_save":
				include("lib/" . $dbtype . "/invTranPost_save.php");
				$invTranPost_save = new invTranPost_save($conn);
				$value = $invTranPost_save->edit_table(true);
		        break;
		    case "invoiceAP_post":
				include("lib/" . $dbtype . "/invoiceAP_post.php");
				$invoiceAP_post = new invoiceAP_post($conn);
				$value = $invoiceAP_post->edit_table(true);
		        break;
		    case "stockReq_header_save":
		    	include("lib/" . $dbtype . "/stockReq_header_save.php");
				$stockReq_header_save = new stockReq_header_save($conn,$_GET,$_POST);
				$value = $stockReq_header_save->edit_table(true);
		        break;
		    case "stockReq_detail_save":
		    	include("lib/" . $dbtype . "/stockReq_detail_save.php");
				$stockReq_detail_save = new stockReq_detail_save($conn);
				$value = $stockReq_detail_save->save();
		        break;
		    case "purOrder_header_save":
		    	include("lib/" . $dbtype . "/purOrder_header_save.php");
				$purOrder_header_save = new purOrder_header_save($conn,$_GET,$_POST);
				$value = $purOrder_header_save->edit_table(true);
		        break;
		     case "purOrder_detail_save":
		    	include("lib/" . $dbtype . "/purOrder_detail_save.php");
				$purOrder_detail_save = new purOrder_detail_save($conn);
				$value = $purOrder_detail_save->save();
		        break;
			case "purReq_header_save":
		    	include("lib/" . $dbtype . "/purReq_header_save.php");
				$purReq_header_save = new purReq_header_save($conn,$_GET,$_POST);
				$value = $purReq_header_save->edit_table(true);
		        break;
			case "purReq_detail_save":
		    	include("lib/" . $dbtype . "/purReq_detail_save.php");
				$purReq_detail_save = new purReq_detail_save($conn);
				$value = $purReq_detail_save->save();
		        break;
	    }
	}

	echo $value;

?>