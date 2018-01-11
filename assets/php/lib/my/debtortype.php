<?php include("header.php") ?>
	  
	<div class="container" style="margin-bottom:1em">
		<div class='row'>
			<form class="form-horizontal">
				<fieldset>

				</fieldset>
            </form>
         </div>
            
            <form id="searchForm" style='width:99%'>
				<fieldset>
                    <div id="searchInContainer">
                            <div id="Scol">Search By : </div>
                   </div>
                
					<div style="padding-left: 65px;margin-top: 25px;padding-right: 60%;"><input id="Stext" name="Stext" type="search" placeholder="Search here ..." class="form-control text-uppercase"></div>
                 </fieldset>  
			</form>
		<br>
        
		
		<div class='row'>
			<div class='col-md-12'>
				<table id="jqGrid" class="table table-striped"></table>
				<div id="jqGridPager"></div>
			</div>
		</div>
		
		<div id="dialogForm" title="Dialog Form" >
			<form class='form-horizontal' style='width:99%' id='formdata'>
            
				<div class="form-group">
				  <label class="col-md-2 control-label" for="debtortycode">Financial Class</label>  
				  <div class="col-md-4">
					<input id="debtortycode" name="debtortycode" type="text" class="form-control input-sm" 
                    data-validation="required">
				  </div>
				</div>

				<div class="form-group">
				  <label class="col-md-2 control-label" for="description">Description</label>  
				  <div class="col-md-4">
				  <input id="description" name="description" type="text" class="form-control input-sm" 
                  data-validation="required">
				  </div>
				</div>
                
                
                
                <div class="form-group">
					  <label class="col-md-2 control-label" for="actdebccode">Actual Cost Center</label>  
					  <div class="col-md-4">
						  <div class='input-group'>
							<input id="actdebccode" name="actdebccode" type="text" class="form-control input-sm" 
							data-validation="required">
							<a class='input-group-addon btn btn-primary'><span class='ion-more'></span></a>
						  </div>
						  <span class="help-block"></span>
					  </div>
					  
					  <label class="col-md-2 control-label" for="actdebglacc">Actual GL Account</label>  
					  <div class="col-md-4">
						  <div class='input-group'>
							<input id="actdebglacc" name="actdebglacc" type="text" class="form-control input-sm" 
							data-validation="required">
							<a class='input-group-addon btn btn-primary'><span class='ion-more'></span></a>
						  </div>
						  <span class="help-block"></span>
					  </div>
				</div>
				
				<div class="form-group">
				  <label class="col-md-2 control-label" for="depccode">Deposit Cost Center</label>  
				  <div class="col-md-4">
					  <div class='input-group'>
						<input id="depccode" name="depccode" type="text" class="form-control input-sm" 
                        data-validation="required">
						<a class='input-group-addon btn btn-primary'><span class='ion-more'></span></a>
					  </div>
					  <span class="help-block"></span>
				  </div>
				
				
				
				  <label class="col-md-2 control-label" for="depglacc">Deposit GL Account</label>  
				  <div class="col-md-4">
					  <div class='input-group'>
						<input id="depglacc" name="depglacc" type="text" class="form-control input-sm" 
                        data-validation="required">
						<a class='input-group-addon btn btn-primary'><span class='ion-more'></span></a>
					  </div>
					  <span class="help-block"></span>
				  </div>
                </div>
               
				
				
				<div class="form-group">
				 <label class="col-md-2 control-label" for="typegrp" ></label>  
				  <div class="col-md-5">
					<label class="radio-inline"><input type="radio" name="typegrp" value='Trade' data-validation="required">Trade</label>
					<label class="radio-inline"><input type="radio" name="typegrp" value='Related' data-validation="">Related</label>
                    <label class="radio-inline"><input type="radio" name="typegrp" value='Miscellanous' data-validation="">Miscellanous</label>
				  </div>
				</div>
				
				
			</form>
		</div>
		
		<div id="dialog" title="title">
         	 <form id="searchForm" style="width:99%">
				<fieldset>
                    <div id="searchInContainer">
                    	Search By : <div id="Dcol" style="float:right; margin-right: 80px;"></div>
                   
                   		<input  style="float:left; margin-left: 73px;" id="Dtext" type="search" placeholder="Search here ..." class="form-control text-uppercase">
                   </div>
				</fieldset>
			</form>
            
			<div class='col-xs-12' align="center">
            <br>
				<table id="gridDialog" class="table table-striped"></table>
				<div id="gridDialogPager"></div>
			</div>
		</div>
	</div><!--/.container-->

<!-- JS Global Compulsory -->
<script src="assets/plugins/jquery.min.js"></script>  
<script src="assets/plugins/bootstrap-3.3.5-dist/js/bootstrap.min.js"></script>

<!-- JS Implementing Plugins -->
<script src="assets/js/profile.js"></script>

<!-- JS Customization -->
<script src="assets/js/custom.js"></script>

<!-- JS Page Level -->

<script>
    jQuery(document).ready(function() 
    {
        Custom.init_cmb_companies();
    });

    function signing_in()
    {
        Profile.signing_in($("#username").val(), $("#inputPassword").val(), $("#cmb_companies").val());
    }
</script>

<?php include("footer.php") ?>