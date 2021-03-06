<?php 
	include_once('../../../../header.php'); 
?>
<body>

	 
	<!-------------------------------- Search + table ---------------------->
	<div class='row'>
		<form id="searchForm" class="formclass" style='width:99%'>
			<fieldset>
				<div class="ScolClass">
						<div name='Scol'>Search By : </div>
				</div>
				<div class="StextClass">
					<input name="Stext" type="search" placeholder="Search here ..." class="form-control text-uppercase">
				</div>
			 </fieldset> 
		</form>
		
    	<div class='col-md-12' style="padding:0 0 15px 0">
            <table id="jqGrid" class="table table-striped"></table>
            <div id="jqGridPager"></div>
        </div>
    </div>
	<!-------------------------------- End Search + table ------------------>
		
		<div id="dialogForm" title="Add Form" >
			<form class='form-horizontal' style='width:99%' id='formdata'>

				<div class="prevnext btn-group pull-right">
				</div>
			
				<div class="form-group">
                	<label class="col-md-2 control-label" for="trantype">Transaction Type</label>  
                      <div class="col-md-3">
                      <input id="trantype" name="trantype" type="text" maxlength="10" class="form-control input-sm" data-validation="required" frozeOnEdit>
                      </div>
				</div>
                
                <div class="form-group">
                	<label class="col-md-2 control-label" for="description">Description</label>  
                      <div class="col-md-8">
                      <input id="description" name="description" type="text" maxlength="100" class="form-control input-sm" data-validation="required">
                      </div>
				</div>

				<div class="form-group">
				  <label class="col-md-2 control-label" for="isstype">Issue Type</label>  
				  <div class="col-md-3">
					<label class="radio-inline"><input type="radio" name="isstype" value='Issue' checked>Issue</label>
					<label class="radio-inline"><input type="radio" name="isstype" value='Transfer'>Transfer</label>
					<label class="radio-inline"><input type="radio" name="isstype" value='Others'>Others</label>
				  </div>
				
				  <label class="col-md-2 control-label" for="trbyiv">Transaction By Inventory</label>  
				  <div class="col-md-3">
					<label class="radio-inline"><input type="radio" name="trbyiv" value='1' checked>Yes</label>
					<label class="radio-inline"><input type="radio" name="trbyiv" value='0'>No</label>
				  </div>
				</div> 

				<div class="form-group">
				  <label class="col-md-2 control-label" for="updqty">Update Quantity</label>  
				  <div class="col-md-3">
					<label class="radio-inline"><input type="radio" name="updqty" value='1' checked>Yes</label>
					<label class="radio-inline"><input type="radio" name="updqty" value='0'>No</label>
				  </div>
				
				  <label class="col-md-2 control-label" for="crdbfl">Credit/Debit</label>  
				  <div class="col-md-3">
					<label class="radio-inline"><input type="radio" name="crdbfl" value='In' checked>In</label>
					<label class="radio-inline"><input type="radio" name="crdbfl" value='Out'>Out</label>
				  </div>
				</div> 

				<div class="form-group">
				  <label class="col-md-2 control-label" for="updamt">Update GL</label>  
				  <div class="col-md-3">
					<label class="radio-inline"><input type="radio" name="updamt" value='1' checked>Yes</label>
					<label class="radio-inline"><input type="radio" name="updamt" value='0'>No</label>
				  </div>
			
				  <label class="col-md-2 control-label" for="accttype">Account Type</label>  
				  <div class="col-md-3">
				    <table>
                             	<tr>
                             
                                <td><label class="radio-inline"><input type="radio" name="accttype" value='Adjustment' checked>Adjustment</label></td>
                                <td><label class="radio-inline"><input type="radio" name="accttype" value='Stock'>Stock</label></td>
                                <td><label class="radio-inline"><input type="radio" name="accttype" value='Accrual'>Accrual</label></td>
								</tr>
							
				 			<tr>
                                <td><label class="radio-inline"><input type="radio" name="accttype" value='Expense'>Expense</label></td>
                                <td><label class="radio-inline"><input type="radio" name="accttype" value='Loan'>Loan</label></td>
                                <td><label class="radio-inline"><input type="radio" name="accttype" value='Cost Of Sale'>Cost Of Sale</label></td>
							</tr>
                            
                            <tr>
				 			
                                <td><label class="radio-inline"><input type="radio" name="acctype" value='Write Off'>Write Off</label></td>
                               <td> <label class="radio-inline"><input type="radio" name="acctype" value='Others'>Others</label></td>
                               
                               </tr>
                               </table>				
                </div>
				</div>

                <div class="form-group">
				  <label class="col-md-2 control-label" for="recstatus">Record Status</label>  
				  <div class="col-md-3">
					<input id="recstatus" name="recstatus" type="text" class="form-control input-sm" frozeOnEdit hideOne>
				  </div>
				</div>
               
				<div class="form-group">
					<label class="col-md-2 control-label" for="adduser">Created By</label>  
						<div class="col-md-3">
						  	<input id="adduser" name="adduser" type="text" class="form-control input-sm" frozeOnEdit hideOne>
						</div>

						<label class="col-md-2 control-label" for="upduser">Last Entered</label>  
						  	<div class="col-md-3">
								<input id="upduser" name="upduser" type="text" maxlength="30" class="form-control input-sm" frozeOnEdit hideOne>
						  	</div>
				</div>

				<div class="form-group">
					<label class="col-md-2 control-label" for="adddate">Created Date</label>  
						<div class="col-md-3">
						  	<input id="adddate" name="adddate" type="text" class="form-control input-sm" frozeOnEdit hideOne>
						</div>

						<label class="col-md-2 control-label" for="upddate">Last Entered Date</label>  
						  	<div class="col-md-3">
								<input id="upddate" name="upddate" type="text" maxlength="30" class="form-control input-sm" frozeOnEdit hideOne>
						  	</div>
				</div>  

				<div class="form-group">
					<label class="col-md-2 control-label" for="computerid">Computer Id</label>  
						<div class="col-md-3">
						  	<input id="computerid" name="computerid" type="text" class="form-control input-sm" frozeOnEdit hideOne>
						</div>

						<label class="col-md-2 control-label" for="ipaddress">IP Address</label>  
						  	<div class="col-md-3">
								<input id="ipaddress" name="ipaddress" type="text" maxlength="30" class="form-control input-sm" frozeOnEdit hideOne>
						  	</div>
				</div>    
            </form>
		</div>

	<?php 
		include_once('../../../../footer.php'); 
	?>
	
	<!-- JS Implementing Plugins -->

	<!-- JS Customization -->

	<!-- JS Page Level -->
	<script src="tranTypeScript.js"></script>
	<script src="../../../../assets/js/utility.js"></script>
	<script src="../../../../assets/js/dialogHandler.js"></script>

<script>
		
</script>
</body>
</html>