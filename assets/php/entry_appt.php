<?php 
	include("config.php"); 
	include("db/" . $dbtype . ".php"); 	

	include("lib/" . $dbtype . "/doctor.php");
	include("lib/" . $dbtype . "/patient.php");
	//include("lib/" . $dbtype . "/calendar.php");
	include("lib/" . $dbtype . "/appointment.php");

	include("lib/" . $dbtype . "/inputCheck.php");
	
	// getting all possible and valid action URL
	$possible_url = array(
							"get_casetype",
							"get_doctor_list",
							"get_doctor_info",
							"get_doctor_break",
							"get_doctor_leave",
							"get_doctor_available",
							"get_all_patient",
							"get_patient_dtl",
							"get_appt_lst",
							"get_appointment_info",
							"add_appointment_info",
							"update_appointment_info",
							"upd_appt_dtl",
							"get_appointment_list",
							"appt_cleanup"
						 );
	
	$value = "An error has occurred getting JSON value.";

	if (isset($_GET["action"]) && in_array($_GET["action"], $possible_url))
	{
	  switch ($_GET["action"])
	    {
		    case "get_doctor_list":
		    	$DoctorModel = new DoctorModel($conn);
		    	$value = $DoctorModel->get_doctor_list($_GET["id"],$_REQUEST["searchPhrase"]);
		        break;
		    case "get_doctor_info":
		    	$DoctorModel = new DoctorModel($conn);
		    	$value = $DoctorModel->get_doctor_info($_GET["id"]);
		        break;
		    case "get_doctor_break":
		    	$DoctorModel = new DoctorModel($conn);
		    	$value = $DoctorModel->get_doctor_break($_GET["id"]);
		        break;
		    case "get_doctor_leave":
		    	$DoctorModel = new DoctorModel($conn);
		    	$value = $DoctorModel->get_doctor_leave($_GET["id"]);
		        break;
		    case "get_doctor_available":
		    	$DoctorModel = new DoctorModel($conn);
		    	$value = $DoctorModel->get_doctor_available($_GET["tm"],$_GET["id"]);
		        break;
		    case "get_casetype":
		    	$ApptModel = new ApptModel ($conn);
		    	$value = $ApptModel ->get_casetype($_GET["id"],$_REQUEST["searchPhrase"]);
		        break;
		    case "get_all_patient":
		    	$ApptModel = new ApptModel ($conn);
		    	$value = $ApptModel->get_patient_list($_GET["typ"],$_GET["term"]);
		        break;
		    case "get_patient_dtl":
		    	$ApptModel = new ApptModel ($conn);
		    	$value = $ApptModel->get_patient_dtl($_GET["typ"],$_GET["term"]);
		        break;
		    case "appt_cleanup":
		    	$CalendarModel = new CalendarModel ($conn);
		    	
		    	$value = $CalendarModel ->appt_cleanup();
		        break;
		    case "get_appt_lst":
		    	$ApptModel = new ApptModel ($conn);
		    	
		    	$value = $ApptModel ->get_appt_lst($_GET["docid"],$_GET["dt"]);
		        break;
		    case "get_appointment_info":
		    	$ApptModel = new ApptModel ($conn);
		    	
		    	$value = $ApptModel ->get_appointment_info($_GET["apptid"]);
		        break;
		    case "add_appointment_info":
		    	$ApptModel = new ApptModel ($conn);
		    	
		    	$value = $ApptModel ->add_appointment_info($_REQUEST);
		        break;
		    case "update_appointment_info":
		    	$ApptModel = new ApptModel ($conn);
		    	
		    	$value = $ApptModel ->update_appointment_info($_REQUEST);
		        break;
		    case "get_appointment_list":
		    	if($_GET['id'] == '')
		    		return;
		    
		    	$ApptModel = new ApptModel ($conn);
		    	
				$start 	= $_GET["start"];
				$end 	= $_GET["end"];
				
				if($start == '') $start = '2016-01-01';
				if($end == '') $end = '2016-01-01';
		    	$value = $ApptModel ->get_appointment_list($_GET["typ"],$_GET["id"],$start,$end);
		        break;
	    }
	}

	echo $value;

?>