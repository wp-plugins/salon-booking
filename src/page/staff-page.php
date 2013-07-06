<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Staff_Page extends Salon_Page {

	private $branch_column = 0;
	private $position_column = 0;
	private $set_items = null;
	
	private $branch_datas = null;
	private $position_datas = null;
	
	private $config_datas = null;

	function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
		if ($is_multi_branch ) {
			$this->branch_column = 4;
			$this->position_column = 5;
			$this->set_items = array('first_name','last_name','branch_cd','position_cd','zip','address','tel','mobile','mail','user_login','remark','employed_day','leaved_day','button_upload','duplicate_cnt_staff');
		}
		else {
			$this->branch_column = 3;
			$this->position_column = 4;
			$this->set_items = array('first_name','last_name','position_cd','zip','address','tel','mobile','mail','user_login','remark','employed_day','leaved_day','button_upload','duplicate_cnt_staff');
		}

	}
	
	public function get_branch_column() { return $this->branch_column; }
	public function get_position_column() { return $this->position_column; }
	public function get_set_items() { return $this->set_items; }
	
	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}

	public function set_position_datas ($position_datas) {
		$this->position_datas = $position_datas;
	}

	public function set_config_datas($config_datas) {
		$this->config_datas = $config_datas;
	}


	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery
		
		var target;
		var save_k1 = "";
		var save_user_login_old = "";
		
		<?php parent::echoClientItem($this->set_items);  ?>	
		<?php Salon_Country::echoZipTable(); //for only_branch?>	

		$j(document).ready(function() {
			
			<?php parent::echoSetItemLabel(); ?>	
			<?php parent::echoUploadImage(); ?>
			<?php Salon_Country::echoZipFunc("zip","address");	?>

			
			$j("#user_login").change(function(){
				
				if ( save_user_login_old == $j("#user_login").val()  ) {
					 $j("#button_insert").attr("disabled","disabled");	
					 $j("#button_update").attr("disabled",false);	
				}
				else {
					 $j("#button_insert").attr("disabled",false);	
					 $j("#button_update").attr("disabled",true);	
				}

			});

			<?php parent::echoCommonButton();			//共通ボタン	?>
						
			
			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=staff",
				<?php parent::echoDataTableLang(); ?>
				<?php 
					if ($this->config_datas['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN )
						$seq = array('last_name','first_name','branch_cd','position_cd','remark','branch_name_table','position_name_table');
					else 
						$seq = array('first_name','last_name','branch_cd','position_cd','remark','branch_name_table','position_name_table');
					parent::echoTableItem($seq,false,$this->is_multi_branch); 
				?>


				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Staff_Init" } )
				},
				"fnDrawCallback": function () {
					<?php parent::echoEditableCommon("staff",array("ID","first_name","last_name")); ?>
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {	
					<?php parent::echoDataTableSelecter("first_name",false); ?>
					if (aData.branch_cd && aData.staff_cd != <?php echo get_option('salon_initial_user',1); ?>) {
						element.append(sel_box);
						element.append(del_box);
					}
					else {
						element.empty();
						element.append(sel_box);
					}
					<?php if ($this->is_multi_branch ) parent::echoDataTableBranchData($this->branch_column,$this->branch_datas); ?>
					
					<?php parent::echoDataTablePositionData($this->position_column,$this->position_datas); ?>
				}

			});




		});
<?php //taregt_colはtdが前提 ?>		
		function fnSelectRow(target_col) {
			fnDetailInit();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
//			var anSelected = $j(target_col.parentNode);
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];
			$j("#last_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['last_name']));	
			$j("#first_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['first_name']));	
			$j("#branch_cd").val(setData["aoData"][position[0]]["_aData"]["branch_cd"]);
			$j("#position_cd").val(setData['aoData'][position[0]]['_aData']['position_cd']);	
			$j("#address").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['address']));	
			$j("#user_login").val(setData['aoData'][position[0]]['_aData']['user_login']);	
			save_user_login_old = setData['aoData'][position[0]]['_aData']['user_login'];	
			$j("#zip").val(setData['aoData'][position[0]]['_aData']['zip']);	
			$j("#tel").val(setData['aoData'][position[0]]['_aData']['tel']);	
			$j("#mobile").val(setData['aoData'][position[0]]['_aData']['mobile']);	
			$j("#mail").val(setData['aoData'][position[0]]['_aData']['mail']);	
			$j("#employed_day").val(setData['aoData'][position[0]]['_aData']['employed_day']);	
			$j("#leaved_day").val(setData['aoData'][position[0]]['_aData']['leaved_day']);	
			$j("#remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));	
			
			$j("#duplicate_cnt").val(setData['aoData'][position[0]]['_aData']['duplicate_cnt']);	


			if ( setData['aoData'][position[0]]['_aData']['staff_cd'] ) {
				$j("#button_update").removeAttr("disabled");
				$j("#button_insert").attr("disabled","disabled");
			}
				else {
				$j("#button_insert").removeAttr("disabled");
				$j("#button_update").attr("disabled","disabled");
			}
			$j("#button_clear").show();
			
			$j("#data_detail").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN); ?>");
			if (setData['aoData'][position[0]]['_aData']['photo']){
				$j("#upload_image").val(setData['aoData'][position[0]]['_aData']['photo']);  
				$j("#uploadedImageView").html(setData['aoData'][position[0]]['_aData']['photo']);
				$j("#button_photo_delete").attr("disabled",false);
			}
			else {
				$j("#button_photo_delete").attr("disabled",true);
			}
			$j(".lightbox").colorbox({rel:"staffs"});

		}

		<?php parent::echoDataTableEditColumn("staff","ID"); ?>
		<?php parent::echoDataTableDeleteRow("staff","staff",false); ?>

		function fnClickAddRow(operate) {
			if ( ! checkItem("data_detail") ) return false;
			var staff_cd = "";
			var ID = "";
			if ( save_k1 !== "" ) {
				var setData = target.fnSettings();
				staff_cd = setData['aoData'][save_k1]['_aData']['staff_cd'];
				if ( save_user_login_old == $j("#user_login").val()  ) {
					ID = setData['aoData'][save_k1]['_aData']['ID']; 
				}
			}
		<?php if ($this->is_multi_branch == false ) : //for only_branch ?>
			if (operate  =="inserted") $j("#branch_cd").val("<?php echo $this->get_default_brandh_cd();?>");
		<?php endif; ?>
			 $j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=staff",
					dataType : "json",
//					data: 	"staff_cd="+staff_cd+"&name="+$j("#name").val()+"&address="+$j("#address").val()+"&remark="+$j("#remark").val(), 
					data: {
						"ID":ID,
						"staff_cd":staff_cd,
						"no":save_k1,
						"type":operate,
						"first_name":$j("#first_name").val(),
						"last_name":$j("#last_name").val(),
						"branch_cd":$j("#branch_cd").val(),
						"position_cd":$j("#position_cd").val(),
						"address":$j("#address").val(),
						"remark":$j("#remark").val(),
						"photo":$j("#upload_image").val(),
						"user_login":$j("#user_login").val(),
						"zip":$j("#zip").val(),
						"tel":$j("#tel").val(),
						"mobile":$j("#mobile").val(),
						"mail":$j("#mail").val(),	
						"employed_day":$j("#employed_day").val(),
						"leaved_day":$j("#leaved_day").val(),
						"menu_func":"Staff_Edit",
						"nonce":"<?php echo $this->nonce; ?>",
						"duplicate_cnt":$j("#duplicate_cnt").val()
					},

					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
							if ( (operate =="inserted")  && (save_user_login_old != $j("#user_login").val())) {
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
						alert ('<?php echo salon_component::getMsg('E401'); ?>['+textStatus+']');
					}
			 });			
		}

		
		function fnDetailInit() {
			$j("#data_detail input[type=\"text\"]").val("");
			$j("#data_detail select").val("");
			$j("#data_detail textarea").val("");

			$j("#button_update").attr("disabled", true);
			$j("#button_insert").attr("disabled", false);
			
			$j("#duplicate_cnt").val("0");
			

			$j("#uploadedImageView").html("");

			save_k1 = "";
			save_user_login_old = "";

			<?php parent::echo_clear_error(); ?>

		}


	<?php parent::echoCheckClinet(array('chk_required','zenkaku','chkZip','chkTel','chkMail','chkTime','chkDate','lenmax','reqOther','num')); ?>		
	<?php parent::echoColumnCheck(array('chk_required','lenmax')); ?>		

	</script>

	<?php screen_icon(); ?>

	<h2><?php _e('Staff Information',SL_DOMAIN); ?></h2>
	<input id="upload_image" type="hidden"  value="" />
	<div id="salon_button_div" >
	<input id="button_insert" type="button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button" />
	</div>

	<div id="data_detail" >
<?php if ($this->config_datas['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN ): ?>
		<input type="text" id="last_name" value="" />
		<input type="text" id="first_name" value="" />
<?php else: ?>
		<input type="text" id="first_name" value="" />
		<input type="text" id="last_name" value="" />
<?php endif; ?>
<?php if ($this->is_multi_branch ): //for only_branch?>
		<select name="branch_cd" id="branch_cd" >
			<option value=""><?php _e('please select',SL_DOMAIN); ?></option>
		<?php
			foreach($this->branch_datas as $k1 => $d1 ) {
				echo '<option value="'.$d1['branch_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
			}
		?>
		</select>
<?php else: ?>
		<input name="branch_cd" id="branch_cd" type="hidden" >
<?php endif; ?>
		<select name="position_cd" id="position_cd">
			<option value=""><?php _e('please select',SL_DOMAIN); ?></option>
		<?php
			foreach($this->position_datas as $k1 => $d1 ) {
				echo '<option value="'.$d1['position_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
			}
		?>
		</select>
		<input type="text" id="duplicate_cnt" />
		<input type="text" id="zip"/>
		<textarea id="address" ></textarea>
		<input type="text" id="tel"/>
		<input type="text" id="mobile"/>
		<input type="text" id="mail"/>
		<input type="text" id="user_login" value="" />
		<textarea id="remark"  ></textarea>
		<input type="text" id="employed_day" value="" />
		<input type="text" id="leaved_day" value="" />
		<div id="photo_wrap" >
		<input id="button_upload" type="button" class="sl_button" value="<?php _e('photo upload',SL_DOMAIN); ?>" />
		<input id="button_photo_delete" type="button" class="sl_button" value="<?php _e('photo delete',SL_DOMAIN); ?>" />
		</div>
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

