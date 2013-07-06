<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Branch_Page extends Salon_Page {

	private $branch_column = 3;

	private $set_items = null;
	
	
	private $branch_datas = null;
	private $position_datas = null;
	
	

	function __construct() {
		parent::__construct(true);
		$this->set_items = array('branch_name','zip','address','branch_tel','mail','open_time','close_time','time_step','closed_day_check','remark','duplicate_cnt');

	}
	
	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}



	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery
		
		
		var target;
		var save_k1 = "";
		var save_closed = "";
		
		<?php parent::echoClientItem($this->set_items); //for only_branch?>	
		<?php Salon_Country::echoZipTable(); //for only_branch?>	

		$j(document).ready(function() {
			
			$j("#closed_day_check input[type=checkbox]").click(function(){
				var tmp = new Array();  
				$j("#closed_day_check input[type=checkbox]").each(function (){
					if ( $j(this).is(":checked") ) {
						tmp.push( $j(this).val() );
					}
				});
				save_closed = tmp.join(",");
			});

			<?php parent::echoSetItemLabel(); ?>	
			<?php Salon_Country::echoZipFunc("zip","address");	?>
			<?php //parent::echoCommonButton();			//共通ボタン	?>
			$j("#salon_button_div input").addClass("sl_button");
			$j("#button_insert").click(function(){
				if ($j("#data_detail").is(":hidden")) {
					$j("#data_detail").show();
					return;
				}
				fnClickAddRow("inserted");
			});
			$j("#button_update").click(function(){
				fnClickAddRow("updated");
			});
			$j("#button_clear").click(function(){
				fnDetailInit(true);	
				$j(target.fnSettings().aoData).each(function (){
					$j(this.nTr).removeClass("row_selected");
				});
			});
			$j("#button_detail").click(function(){
				$j("#data_detail").toggle();
				$j("#shortcode_wrap").toggle();
				if ($j("#data_detail").is(":visible") ) $j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN);?>")
				else $j("#button_detail").val("<?php _e('show detail',SL_DOMAIN); ?>");
			});

			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=branch",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem(array('branch_name','remark'),false,true); //for only_branch?>
	


				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Branch_Init" } )
				},



				"fnDrawCallback": function () {
					<?php parent::echoEditableCommon("branch"); ?>
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {	
					<?php parent::echoDataTableSelecter("name"); ?>

				}
			});
			<?php parent::echo_clear_error(); ?>
			$j("#data_detail").hide();
			$j("#shortcode_wrap").hide();
			$j("#button_detail").val("<?php _e('show detail',SL_DOMAIN); ?>");
			
		});

		function fnSelectRow(target_col) {
			$j("#data_detail").show();
			fnDetailInit();
			
			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();
			save_k1 = position[0];
			$j("#name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['name']));	
			$j("#zip").val(setData['aoData'][position[0]]['_aData']['zip']);	
			$j("#address").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['address']));	
			$j("#tel").val(setData['aoData'][position[0]]['_aData']['tel']);	
			$j("#mail").val(setData['aoData'][position[0]]['_aData']['mail']);	
			$j("#remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));	
			$j("#open_time").val(setData['aoData'][position[0]]['_aData']['open_time']);
			$j("#close_time").val(setData['aoData'][position[0]]['_aData']['close_time']);
			$j("#time_step").val(setData['aoData'][position[0]]['_aData']['time_step']);
			$j("#duplicate_cnt").val(setData['aoData'][position[0]]['_aData']['duplicate_cnt']);
			//
			save_closed = setData['aoData'][position[0]]['_aData']['closed'];
			var tmp = setData['aoData'][position[0]]['_aData']['closed'].split(",");
			$j("#closed_day_check input").attr("checked",false);
			for (var i=0; i < tmp.length; i++) {
				$j("#closed_day_"+tmp[i]).attr("checked",true);
			}
			
			$j("#display_shortcode").val(setData['aoData'][position[0]]['_aData']['shortcode']);
			
			$j("#button_update").removeAttr("disabled");
			$j("#button_clear").show();
			$j("#data_detail").show();
			$j("#shortcode_wrap").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN);  ?>");


		}
		<?php parent::echoDataTableEditColumn("branch"); ?>
		<?php parent::echoDataTableDeleteRow("branch"); ?>

		function fnClickAddRow(operate) {
			if ( ! checkItem("data_detail") ) return false;
			var item_cd = "";
			var branch_cd = "";
			if ( save_k1 !== ""  ) {
				var setData = target.fnSettings();
				branch_cd = setData['aoData'][save_k1]['_aData']['branch_cd']; 				
			}
			var closed = "";
<?php //グローバル変数 target はどうにかならん？ ?>
			 $j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=branch", 
					dataType : "json",

					data: {
						"branch_cd":branch_cd,
						"no":save_k1,
						"type":operate,
						"name":$j("#name").val(),
						"position_cd":$j("#position_cd").val(),
						"zip":$j("#zip").val(),
						"address":$j("#address").val(),
						"tel":$j("#tel").val(),
						"mail":$j("#mail").val(),
						"open_time":$j("#open_time").val(),
						"close_time":$j("#close_time").val(),
						"time_step":$j("#time_step").val(),
						"closed":save_closed,
						"remark":$j("#remark").val(),
						"menu_func":"Branch_Edit",
						"nonce":"<?php echo $this->nonce; ?>",
						"duplicate_cnt":$j("#duplicate_cnt").val()

					},

					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
							if (operate =="inserted" ) {
								target.fnAddData( data.set_data );
							}
							else {
								target.fnUpdate( data.set_data ,parseInt(save_k1) );
							}
							fnDetailInit();
							$j(target.fnSettings().aoData).each(function (){
								$j(this.nTr).removeClass("row_selected");
							});
								
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			 });			
		}

		
		function fnDetailInit() {
			$j("#data_detail input[type=\"text\"]").val("");
			$j("#closed_day_check input").attr("checked",false);
			$j("#data_detail textarea").val("");
			$j("#button_update").attr("disabled", "disabled");
			$j("#display_shortcode").val("");
			

			$j("#duplicate_cnt").val("1");

			save_k1 = "";
			<?php parent::echo_clear_error(); ?>

		}

	<?php parent::echoCheckClinet(array('chk_required','zenkaku','chkZip','chkTel','chkMail','chkTime','chkDate','lenmax','range','reqCheck','num')); ?>		
	<?php parent::echoColumnCheck(array('chk_required','lenmax')); ?>		

	
	</script>

	<?php screen_icon(); ?>

	<h2><?php _e('Shop Information',SL_DOMAIN); ?></h2>
	
	<div id="salon_button_div" >
	<input id="button_insert" type="button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button"/>
	</div>
	
	<div id="shortcode_wrap"><h3><?php _e('Please copy and paste this tag to insert to the page',SL_DOMAIN); ?><input id="display_shortcode" /></h3></div>
	<div id="data_detail" >
		<input type="text" id="name" />
		<input type="text" id="zip"/>
		<textarea id="address" ></textarea>
		<input type="text" id="tel"/>
		<input type="text" id="mail"/>
		<input type="text" id= "duplicate_cnt"  />
		<input type="text" id="open_time"/>
		<input type="text" id="close_time"/>
		<?php parent::echoTimeStepSelect('time_step'); ?>
		<?php parent::echoClosedCheck('','closed_day'); ?>
		<textarea id="remark"  ></textarea>

		<div class="spacer"></div>
		<div id="uploadedImageView"></div>

	</div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>
<?php 
	}	//show_page
}		//class

