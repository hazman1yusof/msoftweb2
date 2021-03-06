
		$.jgrid.defaults.responsive = true;
		$.jgrid.defaults.styleUI = 'Bootstrap';
		var editedRow=0;

		$(document).ready(function () {
			/////////////////////////validation//////////////////////////
			$.validate({
			    modules : 'sanitize',
				language : {
					requiredFields: ''
				},
			});
			
			var errorField=[];
			conf = {
				onValidate : function($form) {
					if(errorField.length>0){
						return {
							element : $(errorField[0]),
							message : ' '
						}
					}
				},
			};
			//////////////////////////////////////////////////////////////

			////////////////////object for dialog handler//////////////////
			dialog_paymode=new makeDialog('debtor.paymode','#paymode',['paymode','description'],'Pay Mode','Description','--', 'Pay Mode');
			dialog_bankcode=new makeDialog('finance.bank','#bankcode',['bankcode','bankname'], 'Bank Code','Bank Name','--', 'Bank Code');
			dialog_cheqno=new makeDialog('finance.chqtran','#cheqno',['cheqno'],'Cheque No', '--','--', 'Cheque No');
			dialog_payto=new makeDialog('material.supplier','#payto',['SuppCode','Name'], 'Pay To','Description','--', 'Pay To');

			////////////////////////////////////start dialog//////////////////////////////////////
			var oper;
			var unsaved = false;

			$("#dialogForm")
			  .dialog({ 
				width: 9.5/10 * $(window).width(),
				modal: true,
				autoOpen: false,
				open: function( event, ui ) {
					parent_close_disabled(true);
					$("#jqGrid2").jqGrid ('setGridWidth', Math.floor($("#jqGrid2_c")[0].offsetWidth-$("#jqGrid2_c")[0].offsetLeft));
					switch(oper) {
						case state = 'add':
							$( this ).dialog( "option", "title", "Add Direct Payment" );	
							$("#jqGrid2").jqGrid("clearGridData", true);
							$("#jqGrid2").jqGrid('hideCol', 'action');
							$("#jqGrid2_iledit").hide();
							$("#pg_jqGridPager2 table").show();
							//$("label[for=cheqno]").text("CHEQUE No");
							////enableChequeD();
							disableFiledCheqNo();
							enableForm('#formdata');
							rdonly('#formdata');
							hideOne('#formdata');
							break;
						case state = 'edit':
							$( this ).dialog( "option", "title", "Edit Direct Payment" );
							$("#jqGrid2").jqGrid('hideCol', 'action');
							$("#jqGrid2_iledit").hide();
							$("#pg_jqGridPager2 table").show();
							enableForm('#formdata');
							frozeOnEdit("#dialogForm");
							rdonly('#formdata');
							$('#formdata :input[hideOne]').show();
							enableFiledCheqNo();
							var paymode = $('#paymode').val();
							if(paymode == "CHEQUE"){
								//$("label[for=cheqno]").text(paymode+" No");
								$("#cheqno").prop("readonly",true);
								enableChequeD();
							}else {
								$("label[for=cheqno]").text(paymode+" No");
								$("#cheqno").prop("readonly",false);
								disableChequeD();
							}
							break;
						case state = 'view':
							$( this ).dialog( "option", "title", "View Direct Payment" );
							disableForm('#formdata');
							$("#pg_jqGridPager2 table").hide();
							$("#jqGrid2").jqGrid('hideCol', 'action');
							paymode = $("#paymode").val()
							//alert($("#paymode").val());
							$("label[for=cheqno]").text(paymode+" No");
							break;
					}
					if(oper!='view'){
						dialog_paymode.handler(errorField);
						dialog_bankcode.handler(errorField);
						dialog_cheqno.handler(errorField);
						dialog_payto.handler(errorField);

					}
					if(oper!='add'){
						//toggleFormData('#jqGrid','#formdata');
						dialog_paymode.check(errorField);
						dialog_bankcode.check(errorField);
						dialog_cheqno.check(errorField);
						dialog_payto.check(errorField);
					}
					if(oper =='edit'){
						if(recstatus == 'Posted') {
							disableForm('#formdata');
							$("#pg_jqGridPager2 table").hide();
							$("#formdata a").off();
						}
					}
				},
				beforeClose: function(event, ui){
					if(unsaved){
						var r = confirm("Are you sure want to leave without save?");
						if (r == true) {
								unsaved = false
						        return true;
						} else {
						       return false;
						}
					}
					
				},
				close: function( event, ui ) {
					parent_close_disabled(false);
					emptyFormdata(errorField,'#formdata');
					emptyFormdata(errorField,'#formdata2');
					$('.alert').detach();
					$("#formdata a").off();
					$('#jqGrid2_ilcancel').click();
					$("#refresh_jqGrid").click();
					
					/*alert($("#jqGrid tbody:first tr:nth-child(2)").attr('id'));
					alert($("#jqGrid").find(">tbody>tr.jqgrow").filter(":last"));
					alert($("#jqGrid").find(">tbody>tr.jqgrow:last"));
					var rows = $("#jqGrid")[0].rows,
					lastRowDOM = rows[rows.length-1];*/
					
					//if(oper=='view'){
						//$(this).dialog("option", "buttons",butt1);
					//}
					
				},
				//buttons :butt1,
			  });
			////////////////////////////////////////end dialog///////////////////////////////////////////

			/////////////////////parameter for jqgrid url/////////////////////////////////////////////////
			var urlParam={
				action:'get_table_default',
				field:'',
				table_name:'finance.apacthdr',
				table_id:'auditno',
				sort_idno:true,
				filterCol: ['source', 'trantype'],
				filterVal: ['CM', 'DP'],
			}

			/////////////////////parameter for saving url////////////////////////////////////////////////
			var saveParam={
				//action:'dpDetail_save',
				action:'dpHeaderSave',
				field:'',
				oper:oper,
				table_name:'finance.apacthdr',
				table_id:'auditno',
				sysparam:{source:'CM',trantype:'DP',useOn:'auditno'},
				sysparam2:{source:'HIS',trantype:'PV',useOn:'pvno'},
				returnVal:true,
			};
			
			$("#jqGrid").jqGrid({
				datatype: "local",
				 colModel: [
				 	//{ label: 'compcode', name: 'compcode', width: 40, hidden:'true'},
					{ label: 'Audit No', name: 'auditno', width: 27, classes: 'wrap', canSearch: true, checked: true},
					{ label: 'Bank Code', name: 'bankcode', width: 35, classes: 'wrap', canSearch: true},
					{ label: 'Pay To', name: 'payto', width: 35, classes: 'wrap',},
					{ label: 'Post Date', name: 'actdate', width: 25, classes: 'wrap', 
						//formatter : 'date', formatoptions : {newformat : 'd/m/Y'}
					},
					{ label: 'Amount', name: 'amount', width: 30, classes: 'wrap', formatter:'currency'} ,//unformat:unformat2}
					{ label: 'Remarks', name: 'remarks', width: 40, classes: 'wrap',},
					{ label: 'Status', name: 'recstatus', width: 20, classes: 'wrap',formatter:formatterPost, unformat:unformatterPost,},
					{ label: 'Entered By', name: 'adduser', width: 35, classes: 'wrap',},
					{ label: 'Entered Date', name: 'adddate', width: 40, classes: 'wrap',},
					{ label: 'Payment Mode', name: 'paymode', width: 25, classes: 'wrap'},
					{ label: 'Cheq No', name: 'cheqno', width: 40, classes: 'wrap',formatter:formatterCheqnno, unformat:unformatterCheqnno},
					{ label: 'Pv No', name: 'pvno', width: 40, hidden:'true'},
					{ label: 'Cheq Date', name: 'cheqdate', width: 40, hidden:'true'},
					{ label: 'source', name: 'source', width: 40, hidden:'true'},
				 	{ label: 'trantype', name: 'trantype', width: 40, hidden:'true'},
				 	{ label: 'idno', name: 'idno', width: 40, hidden:'true'},
				],
				autowidth:true,
                multiSort: true,
				viewrecords: true,
				loadonce:false,
				width: 900,
				height: 350,
				rowNum: 30,
				pager: "#jqGridPager",
				onSelectRow:function(rowid, selected){
					var jg=$("#jqGrid").jqGrid('getRowData',rowid);
					auditno=rowid;
					pvno=jg.pvno;
					amount=jg.amount;
					recstatus=jg.recstatus;
					paymode=jg.paymode;

					//adtNo=rowid;
					urlParam2.filterVal[0]=rowid;
					if(rowid != null) {
						refreshGrid("#jqGrid2",urlParam2);
					}

					//////////////// hide/show button
					if (recstatus=='O'){
						$("#postedBut").show();
						$("#cancelBut").show();
					}

					else if (recstatus==='P'){
						$("#postedBut").hide();
						$("#cancelBut").hide();
					} 

					else {
						$("#postedBut").hide();
						$("#cancelBut").hide();
					}

					///////////only paymode cheq
					//if(paymode != 'CHEQUE'){
						//$("#cheqno").hide();
					//}
				},
				ondblClickRow: function(rowid, iRow, iCol, e){
					$("#jqGridPager td[title='Edit Selected Row']").click();
				},
				gridComplete: function(){
				},
				
			});
			
			/////////////////////////formatter & unformat/////////////////////////////////////////////////////////
			function formatterPost(cellvalue, options, rowObject){
				if(cellvalue == 'O'){
					return "Open";
				}else if(cellvalue == 'P') { 
					return "Posted";
				}else if (cellvalue == 'C'){
					return "Cancel";
				}
			}

			function  unformatterPost(cellvalue, options){
				if(cellvalue == 'Open'){
					return "O";
				}else if(cellvalue == 'Posted') { 
					return "P";
				}else if (cellvalue == 'Cancel'){
					return "C";
				}
			}

			function formatterCheqnno  (cellValue, options, rowObject) {
				//return rowObject[9] != "CHEQUE" ? "&nbsp;" : $.jgrid.htmlEncode(cellValue);
				return rowObject[9] != "CHEQUE" ? "<span cheqno='"+cellValue+"'></span>" : "<span cheqno='"+cellValue+"'>"+cellValue+"</span>";
			}

			function unformatterCheqnno (cellValue, options, rowObject) {
				return $(rowObject).find('span').attr('cheqno');
			}

			/////////////////////////////// for Button /////////////////////////////////////////////////////////
			var adtNo
			function sometodo(){
				$("#jqGrid2_iledit").show();
				$("#jqGrid2").jqGrid('showCol', 'action');
				$('#formdata  textarea').prop("readonly",true);
				$('#formdata :input[hideOne]').show();
				$('#formdata input').prop("readonly",true);
				$('#formdata  input[type=radio]').prop("disabled",true);
				$("input[id*='_auditno']").val(auditno);
				$("#formdata a").off();
			}

			function saveHeader(form,oper,saveParam,obj){//saveonly
			//function saveFormdata2(grid,dialog,form,oper,saveParam,urlParam,searchForm,obj){
				if(obj==null){
					obj={};
				}
				saveParam.oper=oper;

				$.each($( "input:text" ).filter('[data-sanitize-number-format]'), function( index, value ) {
					var newnum=numeral().unformat($(value).val());
					$(value).val(newnum);
				});

				$.post( "../../../../assets/php/entry.php?"+$.param(saveParam), $( form ).serialize()+'&'+ $.param(obj) , function( data ) {
				},'json').fail(function(data) {
					errorText(dialog,data.responseText);
				}).success(function(data){
					/*
					var tableid=urlParam.table_id;
					var idval=$(form+' [name='+tableid+']').val();
					$( searchForm+" [name=Stext]").val(idval);
					$( searchForm+" input :radio[name=Scol]").prop('checked',true);
					search(grid,idval,tableid,urlParam);*/

					if(oper=='add'){
						//cheqno = $('#cheqno').val();
						//bankcode = $('#bankcode').val();
						//alert(cheqno);
						auditno = data.auditno;
						pvno = data.pvno;
						sometodo();
						$('#auditno').val(auditno);
						$('#pvno').val(pvno);
						///$("#jqGrid").setGridParam({sortname:'auditno', sortorder: 'desc'}).trigger('reloadGrid');
						//alert("add->"+auditno);
					}else if(oper=='edit'){
						$("#formdata :input[name*='auditno']").val(selrowData('#jqGrid').auditno);
						sometodo();
						$('#auditno').val(auditno);
						$('#pvno').val(pvno);
						$('#amount').val(amount);
						////$("#jqGrid").setGridParam({sortname:'auditno', sortorder: 'desc'}).trigger('reloadGrid');
						//alert("edit->"+auditno+"-->"+amount);
					}
					/* else if (oper = $_POST['oper']=='add'){
						refreshGrid("#jqGrid");
						$("#formdata :input[name*='amount']").val(selrowData('#jqGrid').amount);
						$('#amount').val(amount);
						alert(amount);
					}*/
				});
			}

			$("#dialogForm").on('change keypress', '#formdata :input', '#formdata :textarea',  function(){
					unsaved = true;
			});

			$("#actdate").on('change',  function(){
				if("#actdate"){
					actdate = $('#actdate').val()
					$("input[id='cheqdate']").val(actdate);
				}else{
					$('#cheqdate').val()
				}

			});

			/*$("#remarks").on('change', function(){
				alert("xx");
				var remarks = $('#remarks').val();
				alert(remarks);
				console.log(remarks);
				 	if (this.value == "email") {
			            $('#cheqno').text("Email Address");
			        } else if (this.value == "web") {
			            $('#cheqno').text("Web Address");
			        }
			});*/

			function disableChequeD() {
				dialog_cheqno.offHandler();
				$("#cheqno_a").hide();
			}

			function enableChequeD() {
				dialog_cheqno.updateField('finance.chqtran','#cheqno',['cheqno'],'Cheque No', '--','--', 'Cheque No');
				dialog_cheqno.offHandler();
				dialog_cheqno.handler(errorField);
				$("#cheqno_a").show();
			}

			function disableFiledCheqNo() {
				$("label[for=cheqno]").hide();
				$("#cheqno_parent").hide();

				$("label[for=bankcode]").hide();
				$("#bankcode_parent").hide();
			}

			function enableFiledCheqNo() {
				$("label[for=cheqno]").show();
				$("#cheqno_parent").show();

				$("label[for=bankcode]").show();
				$("#bankcode_parent").show();
				//$("#2").removeClass("hidden");
				//$("#3").addClass("hidden");
			}

			$('#dialog').on('dblclick',function(){
				unsaved = true;
				if(selText=='#paymode'){
					enableFiledCheqNo();
					var paymode = $('#paymode').val();
					if(paymode == "CHEQUE"){
						//alert($("#paymode").val());
						$("label[for=cheqno]").text(paymode+" No");
						$("#cheqno").prop("readonly",true);
						//$("#cheqno").attr("required", "required");
						enableChequeD();
					}else {
						$("label[for=cheqno]").text(paymode+" No");
						$("#cheqno").prop("readonly",false);
						//$("#cheqno").attr('required',false);
						//$('#cheqno').removeAttr('required');
						disableChequeD();
					}

					//$('#formdata, #bankcode').trigger('reset');
					$('#bankcode').val('');
					$('#bc').html('');
					$('#cheqno').val('');
					$('#cn').html('');

					//emptyFormdata(errorField,'#bankcode');
					//$('#bankcode').val('');
					//$('#cheqno').val('');
				}

				if(selText == "#bankcode") {
					$('#cheqno').val('');
					$('#cn').html('');
				}

				/*if("#bankcode") {
					$('#cheqno').val('');
					//$('#cheqno').parent().siblings( ".help-block" ).empty();
				}


				if("#jqGrid2 input[name='GSTCode']"){
					//var rate =  parseInt("6");
					
					var amntb4gst = parseInt($("input[id*='_AmtB4GST']").val());
					var amount = amntb4gst+(amntb4gst*(rate/100));//.toFixed(2);
					//console.log(amntb4gst+"--->"+rate);
					//console.log(rate);
					//console.log(amount.toFixed(2));
					$("input[id*='_amount']").val(amount.toFixed(2));
				}*/
			});

			$("#postedBut").hide();
			$("#cancelBut").hide();

			$("#postedBut").click(function(){
				var param={
						action:'dpreg_save',
						oper:'add',
						field:'',
						table_name:'finance.cbtran',
						table_id:'auditno',
						skipduplicate: true,
						returnVal:true,
						sysparam:{source:'CM',trantype:'DP',useOn:'auditno'}
					};

					$.post( "../../../../assets/php/entry.php?"+$.param(param),
						{seldata:selrowData("#jqGrid")}, 
						function( data ) {
						}
					).fail(function(data) {
						alert('error');
					}).success(function(data){
						refreshGrid("#jqGrid",urlParam);
						$("#postedBut").hide();
						$("#cancelBut").hide();
					});
			});

			$("#cancelBut").click(function(){
				refreshGrid("#jqGrid",urlParam);
					$("#postedBut").hide();
					$("#cancelBut").hide();
			});

			//alert($("#jqGrid").find(">tbody>tr.jqgrow").filter(":last"));
			//alert($("#jqGrid tbody:first tr:nth-child(2)").attr('id'));
			//var rows = $("#jqGrid")[0].rows,lastRowDOM = rows[rows.length-1];
    		//alert($("#jqGrid").find(">tbody>tr.jqgrow:last"));


			/////////////////////////start grid pager/////////////////////////////////////////////////////////

			$("#jqGrid").jqGrid('navGrid','#jqGridPager',{	
				view:false,edit:false,add:false,del:false,search:false,
				beforeRefresh: function(){
					refreshGrid("#jqGrid",urlParam);
				},
			})/*.jqGrid('navButtonAdd',"#jqGridPager",{
				caption:"",cursor: "pointer",position: "first", 
				buttonicon:"glyphicon glyphicon-trash",
				title:"Delete Selected Row",
				onClickButton: function(){
					oper='del';
					selRowId = $("#jqGrid").jqGrid ('getGridParam', 'selrow');
					if(!selRowId){
						alert('Please select row');
						return emptyFormdata(errorField,'#formdata');
					}else{
						saveFormdata("#jqGrid","#dialogForm","#formdata",'del',saveParam,urlParam,{'itemcode':selRowId});
					}
				},
			})*/.jqGrid('navButtonAdd',"#jqGridPager",{
				caption:"",cursor: "pointer",position: "first", 
				buttonicon:"glyphicon glyphicon-info-sign",
				title:"View Selected Row",  
				onClickButton: function(){
					oper='view';
					selRowId = $("#jqGrid").jqGrid ('getGridParam', 'selrow');
					populateFormdata("#jqGrid","#dialogForm","#formdata",selRowId,'view');
				},
			}).jqGrid('navButtonAdd',"#jqGridPager",{
				caption:"",cursor: "pointer",position: "first",  
				buttonicon:"glyphicon glyphicon-edit",
				title:"Edit Selected Row",  
				onClickButton: function(){
					oper='edit';
					selRowId = $("#jqGrid").jqGrid ('getGridParam', 'selrow');
					populateFormdata("#jqGrid","#dialogForm","#formdata",selRowId,'edit');
				}, 
			}).jqGrid('navButtonAdd',"#jqGridPager",{
				caption:"",cursor: "pointer",position: "first",  
				buttonicon:"glyphicon glyphicon-plus", 
				id: 'glyphicon-plus',
				title:"Add New Row", 
				onClickButton: function(){
					oper='add';
					$( "#dialogForm" ).dialog( "open" );
					$("#formdata :input[name='source']").val("CM");
					$("#formdata :input[name='trantype']").val("DP");
					/*var datarow = generaterow();
                	var commit = $("#jqGrid").jqxGrid('addrow', null, {});
               		var rows = $('#jqGrid').jqxGrid('getrows');
                	$('#jqGrid').jqGrid('selectrow',rows.length-1 );
                	alert($('#jqGrid').jqGrid('selectrow',rows.length-1 ));*/
				},
			});

			//glyphicon glyphicon-print

			//////////////////////////////////////end grid/////////////////////////////////////////////////////////

			//////////add field into param, refresh grid if needed////////////////////////////////////////////////
			addParamField('#jqGrid',true,urlParam);
			addParamField('#jqGrid',false,saveParam,['adduser','adddate','idno']);

			//////////handle searching, its radio button and toggle ///////////////////////////////////////////////
			populateSelect('#jqGrid','#searchForm');
			searchClick('#jqGrid','#searchForm',urlParam);

			

			//////////////////////////////////////grid2/////////////////////////////////////////////////////////
			var operDetail;

			var urlParam2={
				action:'get_table_default',
				field:['compcode','source','trantype','auditno','lineno_','deptcode','category','document', 'AmtB4GST', 'GSTCode', 'amount'],
				table_name:'finance.apactdtl',
				table_id:'lineno_',
				filterCol:['auditno', 'recstatus'],
				filterVal:['', 'A'],
			}

			var saveParam2={
				action:'save_table_default',
				field:'',
				table_name:'finance.apactdtl',
				table_id:'lineno_',
				skipduplicate:true,
				lineno:{useOn:'auditno',useVal:'',useBy:'lineno_'},
				filterCol:['auditno'],
				filterVal:[''],
			}

			$("#jqGrid2").jqGrid({
				datatype: "local",
				editurl: "../../../../assets/php/entry.php?action=dpDetail_save",
				colModel: [
				 	{ label: 'compcode', name: 'compcode', width: 20, classes: 'wrap', hidden:true},
				 	{ label: 'source', name: 'source', width: 20, classes: 'wrap', hidden:true, editable:true},
				 	{ label: 'trantype', name: 'trantype', width: 20, classes: 'wrap', hidden:true, editable:true},
				 	{ label: 'auditno', name: 'auditno', width: 20, classes: 'wrap', hidden:true, editable:true},
					{ label: 'Line No', name: 'lineno_', width: 20, classes: 'wrap', hidden:true, editable:true}, //canSearch: true, checked: true},
					{ label: 'Department', name: 'deptcode', width: 25, classes: 'wrap', canSearch: true, editable: true,
								editrules:{required: true},
								edittype:'custom',	editoptions:
								    {  custom_element:deptcodeCustomEdit,
								       custom_value:galGridCustomValue 	
								    },
					},
					{ label: 'Category', name: 'category', width: 25, edittype:'text', classes: 'wrap', editable: true,
								editrules:{required: true},
								edittype:'custom',	editoptions:
								    {  custom_element:categoryCustomEdit,
								       custom_value:galGridCustomValue 	
								    },
					},
					{ label: 'Document', name: 'document', width: 29, classes: 'wrap', editable: true,
								//editrules:{required: true},
								edittype:"text",
					},
					{ label: 'Amount Before GST', name: 'AmtB4GST', width: 25, classes: 'wrap', 
								formatter:'currency', formatoptions:{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2,},
								editable: true,
								editrules:{required: true},edittype:"text",
								editoptions:{
								maxlength: 12,
								dataInit: function(element) {
									$(element).keypress(function(e){
										if ((e.which != 46 || $(this).val().indexOf('.') != -1) && (e.which < 48 || e.which > 57)) {
										//if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
											return false;
										 }
									});
								}
							},
					},
					{ label: 'GST Code', name: 'GSTCode', width: 25, edittype:'text', classes: 'wrap', editable: true,
								editrules:{required: true},
								edittype:'custom',	editoptions:
								    {  custom_element:GSTCodeCustomEdit,
								       custom_value:galGridCustomValue 	
								    },
					},
					{ label: 'Amount', name: 'amount', width: 25, classes: 'wrap', 
								formatter:'currency', formatoptions:{decimalSeparator:".", thousandsSeparator: ",", decimalPlaces: 2,},
								editable: true,
								editrules:{required: true},edittype:"text",
								editoptions:{
								maxlength: 12,
								dataInit: function(element) {
									$(element).keypress(function(e){
										if ((e.which != 46 || $(this).val().indexOf('.') != -1) && (e.which < 48 || e.which > 57)) {
										//if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
											return false;
										 }
									});
								}
							},
					},
					{ label: 'Action', name: 'action', width : 9,  formatter: "actions", editable:false,
											formatoptions: {
											    keys: true,
											    editbutton: false,
											    delbutton: true,
											    delOptions: {
											    	mtype: 'POST',
											    	onclickSubmit: function (options, rowid) {
											    			var detVa=$("#jqGrid2").jqGrid('getRowData',rowid);
											    			auditno=detVa.auditno;
                        									lineno_=detVa.lineno_;
													        //var rowData = jQuery(this).jqGrid('getRowData', rowid);
													        options.url = '../../../../assets/php/entry.php?' + jQuery.param({
													            action: 'dpDetail_save',
													            auditno: detVa.auditno,
													            lineno_: detVa.lineno_,
													            source: detVa.source,
													            trantype: detVa.trantype
													        });
													},
													afterSubmit: function (response, postdata) {
														return $('#amount').val(response.responseText);
													},
											    },
											}
					},
				],
				autowidth:true,
                multiSort: true,
				viewrecords: true,
				loadonce:false,
				width: 900,
				height: 200,
				rowNum: 30,
				//rownumbers: true,
				pager: "#jqGridPager2",
				ondblClickRow: function(rowid, iRow, iCol, e){
					$("#jqGridPager2 td[title='Edit Selected Row']").click();
				},
				gridComplete: function(){
					/*if(editedRow!=0){
						$("#jqGrid").jqGrid('setSelection',editedRow,false);
					}*/
				},
				onSelectRow:function(rowid, selected){
					$("#jqGrid2 input[name='AmtB4GST']").on('keydown',  function() { 
						delay(function(){
							if($("#jqGrid2 input[name='GSTCode']").val() == '') {
								var amntb4gst = parseFloat($("input[id*='_AmtB4GST']").val());
								var amount = amntb4gst;
								$("#jqGrid2 input[name='amount']").val(amount.toFixed(2));
							}else{
								var amntb4gst = parseFloat($("input[id*='_AmtB4GST']").val());
								var amount = amntb4gst+(amntb4gst*(rate/100));//.toFixed(2);
								//alert(amount.toFixed(2));
								$("#jqGrid2 input[name='amount']").val(amount.toFixed(2));
							}
							}, 1000 );
					});

					$('#dialog').on('dblclick',function(){
						if(selText=="#jqGrid2 input[name='GSTCode']"){
							var amntb4gst = parseFloat($("input[id*='_AmtB4GST']").val());
							var amount = amntb4gst+(amntb4gst*(rate/100));//.toFixed(2);
							$("#jqGrid2 input[name='amount']").val(amount.toFixed(2));
						}
					});
				},
				/*footerrow: true,
                loadComplete: function () {
                    var $self = $(this),
                        sum = $self.jqGrid("getCol", "amount", false, "sum");

                    $self.jqGrid("footerData", "set", {invdate: "Total:", amount: sum});
                },	*/			
			});

			//$("#jqGrid2").jqGrid('hideCol', 'action');

			///////custom input/////
			function deptcodeCustomEdit(val,opt){  		
				return $('<div class="input-group"><input id="deptcode" name="deptcode" type="text" class="form-control input-sm" data-validation="required" value="'+val+'" ><a class="input-group-addon btn btn-primary"><span class="fa fa-ellipsis-h"></span></a></div><span class="help-block"></span>');
			}

			function categoryCustomEdit(val,opt){  
				return $('<div class="input-group"><input id="category" name="category" type="text" class="form-control input-sm" data-validation="required" value="'+val+'"><a class="input-group-addon btn btn-primary"><span class="fa fa-ellipsis-h"></span></a></div><span class="help-block"></span>');
			}

			function GSTCodeCustomEdit(val,opt){  
				return $('<div class="input-group"><input id="GSTCode" name="GSTCode" type="text" class="form-control input-sm" data-validation="required" value="'+val+'"><a class="input-group-addon btn btn-primary"><span class="fa fa-ellipsis-h"></span></a></div><span class="help-block"></span>');
			}



			function galGridCustomValue (elem, operation, value){	
				if(operation == 'get') {
					return $(elem).find("input").val();
				} 
				else if(operation == 'set') {
					$('input',elem).val(value);
				}
			}

			var myEditOptions = {
		        keys: true,
		        oneditfunc: function (rowid) {
		        },
		        aftersavefunc: function (rowid, response, options) {
		           $('#amount').val(response.responseText);
		           //console.log(response);
		        },
		    };

			$("#jqGrid2").inlineNav('#jqGridPager2',{	
				add:true,
				edit:true,
				//del:true,
				addParams: { 
        			//position: "afterSelected",
        			addRowParams: myEditOptions
   				},
   				//addedrow: "last",
   				editParams: myEditOptions

			});

			$("#jqGrid2_iladd").click(function(){
				unsaved = false;
				//alert($("#amount").val());
				if( $('#formdata').isValid({requiredFields: ''}, conf, true) ) {
					saveHeader("#formdata", oper,saveParam);
					//alert($("#amount").val());
					//saveHeader("#formdata", oper,saveParam,saveParam2);
					unsaved = false;
					//amount = $("#amount").val();
					$("#formdata :input[name='amount']").val($("#amount").val());
					//alert(amount);
					$("input[id*='_auditno']").val(auditno);
					$("input[id*='_auditno']").attr('readonly','readonly');
					$("input[id*='_source']").val($("#source").val());
					$("input[id*='_trantype']").val($("#trantype").val());
					$("input[id*='_lineno_']").val($("#lineno_").val());

					dialog_deptcode=new makeDialog('sysdb.department',"#jqGrid2 input[name='deptcode']",['deptcode','description'],'Department Code','Description', '--', 'Department');
					dialog_category=new makeDialog('material.category',"#jqGrid2 input[name='category']",['catcode','description'],'Category Code','Description', '--', 'Category');
					dialog_GSTCode=new makeDialog('hisdb.taxmast',"#jqGrid2 input[name='GSTCode']",['taxcode','description','rate'],'GST Code','Description', 'Rate', 'GST Code');
					
					dialog_deptcode.handler(errorField);
					dialog_category.handler(errorField);
					dialog_GSTCode.handler(errorField);

					$("input[id*='_amount']").keydown(function(e) {
						//console.log('keydown called');
						var code = e.keyCode || e.which;
							if (code == '9') { // -->for tab
								$('#jqGrid2_ilsave').click();
								//refreshGrid("#jqGrid2",urlParam2);
								delay(function(){
									$('#jqGrid2_iladd').click();
								}, 1500 );
							}
					 });
				}else{
					$('#jqGrid2_ilcancel').click();
				}
			});

			$("#jqGrid2_iledit").click(function(){
				dialog_deptcode=new makeDialog('sysdb.department',"#jqGrid2 input[name='deptcode']",['deptcode','description'],'Department Code','Description', 'Department');
				dialog_category=new makeDialog('material.category',"#jqGrid2 input[name='category']",['catcode','description'],'Category Code','Description', 'Category');
				dialog_GSTCode=new makeDialog('hisdb.taxmast',"#jqGrid2 input[name='GSTCode']",['taxcode','description','rate'],'GST Code','Description', 'Rate', 'GST Code');

				dialog_deptcode.handler(errorField);
				dialog_category.handler(errorField);
				dialog_GSTCode.handler(errorField);
			});

			$("#jqGrid2_ilsave").click(function(){
				unsaved = false;
				
			});

			/*$('#dialog').on('dblclick', "#jqGrid2 input[name='GSTCode']",function(){
				 alert("111");
			});

			$('#GSTCode').change(function(){
				var GSTCode = $(this).val();
				var AmtB4GST =  $(this).val();
				var rate =  $(this).val();

				var amountAfterGST = AmtB4GST + (AmtB4GST * rate * 100).toFixed(2);
				$('#amount').val( amountAfterGST );
				console.log(amountAfterGST);
			})
			.change();

			$('#amount').click(function(){
				var GSTCode = $(this).val();
				var AmtB4GST =  $(this).val();
				var rate =  $(this).val();

				var amountAfterGST = AmtB4GST + (AmtB4GST * rate * 100).toFixed(2);
				$('#amount').val( amountAfterGST );
				console.log(amountAfterGST);

			});

			$("#jqGrid2").on('keydown', "input[name='GSTCode']", function(){
				var AmtB4GST =  $('#AmtB4GST').val();
				var rate =  $("#rate").val();
				var amount = AmtB4GST + (AmtB4GST * rate * 100).toFixed(2);
			});*/

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////end grid2/////////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


		///////////////////////////////start->dialogHandler part/////////////////////////////////////////////
			function makeDialog(table,id,cols,setLabel1,setLabel2,setLabel3,title){
				this.table=table;
				this.id=id;
				this.cols=cols;
				this.setLabel1=setLabel1;
				this.setLabel2=setLabel2;
				this.setLabel3=setLabel3;
				this.title=title;
				this.handler=dialogHandler;
				this.offHandler=function(){
					$( this.id+" ~ a" ).off();
				}
				this.check=checkInput;
				this.updateField=function(table,id,cols,title){
					this.table=table;
					this.id=id;
					this.cols=cols;
					this.title=title;
				}
			}

			$( "#dialog" ).dialog({
				autoOpen: false,
				width: 7/10 * $(window).width(),
				modal: true,
				open: function(){
					$("#gridDialog").jqGrid ('setGridWidth', Math.floor($("#gridDialog_c")[0].offsetWidth-$("#gridDialog_c")[0].offsetLeft));
					if(selText=='#paymode'){
						paramD.filterCol=['source', 'recstatus'];
						paramD.filterVal=['cm', 'A'];
					}else if(selText=='#cheqno'){ 
						//if()
						paramD.filterCol=['bankcode', 'stat'];
						paramD.filterVal=[$("#formdata :input[name='bankcode']").val(), 'A'];
					}else if(selText=='#payto'){
						paramD.filterCol=['recstatus'];
						paramD.filterVal=['A'];
					}else if(selText=="#jqGrid2 input[name='GSTCode']"){
						paramD.filterCol=['recstatus'];
						paramD.filterVal=['A'];
					}else if(selText=="#jqGrid2 input[name='category']"){
						paramD.filterCol=['source', 'cattype', 'recstatus'];
						paramD.filterVal=['CR', 'Other', 'A'];
					}else{
						paramD.filterCol=['recstatus'];
						paramD.filterVal=['A'];
					}
				},
				close: function( event, ui ){
					paramD.searchCol=null;
					paramD.searchVal=null;
				},
			});

			var selText,Dtable,Dcols;
			$("#gridDialog").jqGrid({
				datatype: "local",
				colModel: [
					{ label: 'Code', name: 'code', width: 200,  classes: 'pointer', canSearch:true,checked:true}, 
					{ label: 'Description', name: 'desc', width: 400, canSearch:true, classes: 'pointer',},
					{ label: 'Rate', name: 'rate', width: 400, classes: 'pointer',},
				],
				width: 500,
				viewrecords: true,
				loadonce: false,
                multiSort: true,
				rowNum: 30,
				shrinkToFit: true,
				pager: "#gridDialogPager",
				ondblClickRow: function(rowid, iRow, iCol, e){
					var data=$("#gridDialog").jqGrid ('getRowData', rowid);
					$("#gridDialog").jqGrid("clearGridData", true);
					$("#dialog").dialog( "close" );
					//rate=data.rate;
					//console.log(rate);
					$(selText).val(rowid);
					$(selText).focus();
					$(selText).parent().next().html(data['desc']);
					/*if(selText=='#payto'){
						$('#payto2').val(data['desc']);
					}else{
						$(selText).parent().next().html(data['desc']);
					}*/
					if(selText=="#jqGrid2 input[name='GSTCode']"){
						rate=data.rate;

					}
				},
				
			});

			var paramD={action:'get_table_default',table_name:'',field:'',table_id:'',filter:''};
			function dialogHandler(){
				var table=this.table,id=this.id,cols=this.cols,setLabel1=this.setLabel1,setLabel2=this.setLabel2,setLabel3=this.setLabel3,title=this.title,self=this;
				$( id+" ~ a" ).on( "click", function() {
					selText=id,Dtable=table,Dcols=cols,
					$("#gridDialog").jqGrid("clearGridData", true);
				
					$( "#gridDialog" ).jqGrid( "setLabel", "code", setLabel1);
					$( "#gridDialog" ).jqGrid( "setLabel", "desc", setLabel2);
					$( "#dialog" ).dialog( "option", "title", title );
					if(selText=='#cheqno')	{	
						$( "#dialog" ).dialog({
								autoOpen: false,
								width: 6/10 * $(window).width(),
								modal: true,
						});	
						$("#gridDialog").css( "width", "30%" );
						$("#gridDialog").jqGrid('hideCol', 'desc');
						$("#gridDialog").jqGrid('hideCol', 'rate');
					}else if (selText=="#jqGrid2 input[name='GSTCode']"){
						$( "#dialog" ).dialog({
								autoOpen: false,
								width: 7/10 * $(window).width(),
								modal: true,
						});	
						$("#gridDialog").jqGrid('showCol', 'desc');
						$("#gridDialog").jqGrid('showCol', 'rate');
					}else{
						$( "#dialog" ).dialog({
								autoOpen: false,
								width: 7/10 * $(window).width(),
								modal: true,
						});	
						$("#gridDialog").jqGrid('showCol', 'desc');
						$("#gridDialog").jqGrid('hideCol', 'rate');
					}
					$( "#dialog" ).dialog( "open" );

					paramD.table_name=table;
					paramD.field=cols;
					paramD.table_id=cols[0];

					$("#gridDialog").jqGrid('setGridParam',{datatype:'json',url:'../../../../assets/php/entry.php?'+$.param(paramD)}).trigger('reloadGrid');
					$('#Dtext').val('');$('#Dcol').html('');
					
					$.each($("#gridDialog").jqGrid('getGridParam','colModel'), function( index, value ) {

						if(selText=='#cheqno')	{
							if(value['canSearch']){
								if(value['checked']){
									$( "#Dcol" ).append("<label class='radio-inline'><input type='radio' name='dcolr' value='"+cols[index]+"' checked>"+setLabel1+"</input></label>" );
								}
							}
						}else{
							if(value['canSearch']){
								if(value['checked']){
									$( "#Dcol" ).append("<label class='radio-inline'><input type='radio' name='dcolr' value='"+cols[index]+"' checked>"+setLabel1+"</input></label>" );
								}else{
									$("#Dcol" ).append( "<label class='radio-inline'><input type='radio' name='dcolr' value='"+cols[index]+"' >"+setLabel2+"</input></label>" );
								}
							}
						}

						
					});
				});
				$(id).on("blur", function(){
					self.check();
				});
			}

			function checkInput(errorField){
				var table=this.table,id=this.id,field=this.cols,value=$( this.id ).val()
				var param={action:'input_check',table:table,field:field,value:value};
				$.get( "../../../../assets/php/entry.php?"+$.param(param), function( data ) {
					
				},'json').done(function(data) {
					if(data.msg=='success'){
						/*if((id== "#cheqno") && ($("#paymode") == 'CHEQUE')) {
							dialog_cheqno.handler(errorField);
						}*/
						if($.inArray(id,errorField)!==-1){
							errorField.splice($.inArray(id,errorField), 1);
						}
						$( id ).parent().removeClass( "has-error" ).addClass( "has-success" );
						$( id ).removeClass( "error" ).addClass( "valid" );
						$( id ).parent().siblings( ".help-block" ).html(data.row[field[1]]);
						$( id ).parent().siblings( ".help-block" ).show();
					}else if(data.msg=='fail'){
						if(id=='#payto'){
							//$( id ).parent().siblings( ".help-block" ).html(value);
							$( id ).parent().siblings( ".help-block" ).hide();
						}
						else if((id == '#cheqno') && ($('#paymode').val() != "CHEQUE")) {
							console.log((id == '#cheqno') && ($('#paymode').val() == "CHEQUE"))
							//alert("ppp");
							$( id ).parent().removeClass( "has-error" ).addClass( "has-success" );
							$( id ).removeClass( "error" ).addClass( "valid" );
								//dialog_cheqno.offHandler();
								//$( id ).parent().removeClass( "has-success" ).removeClass( "has-error" );
								//$( id ).removeClass( "valid" ).removeClass( "error" );
								//$( id ).parent().siblings( ".help-block" ).hide();
						}else{
							$( id ).parent().removeClass( "has-success" ).addClass( "has-error" );
							$( id ).removeClass( "valid" ).addClass( "error" );
							$( id ).parent().siblings( ".help-block" ).html("Invalid Code ( "+field[0]+" )");
							if($.inArray(id,errorField)===-1){
								errorField.push(id);
							}
						}
					}
				});
			}
			
			$('#Dtext').keyup(function() {
				delay(function(){
					Dsearch($('#Dtext').val(),$('#checkForm input:radio[name=dcolr]:checked').val());
				}, 500 );
			});
			
			$('#Dcol').change(function(){
				Dsearch($('#Dtext').val(),$('#checkForm input:radio[name=dcolr]:checked').val());
			});
			
			function Dsearch(Dtext,Dcol){
				paramD.searchCol=null;
				paramD.searchVal=null;
				Dtext=Dtext.trim();
				if(Dtext != ''){
					var split = Dtext.split(" "),searchCol=[],searchVal=[];
					$.each(split, function( index, value ) {
						searchCol.push(Dcol);
						searchVal.push('%'+value+'%');
					});
					paramD.searchCol=searchCol;
					paramD.searchVal=searchVal;
				}
				refreshGrid("#gridDialog",paramD);
			}
			///////////////////////////////finish->dialogHandler///part////////////////////////////////////////////
});