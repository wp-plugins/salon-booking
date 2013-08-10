<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Reservation_Page extends Salon_Page {

	private $set_items = null;
	
	private $all_branch_datas = null;
	private $branch_datas = null;
	private $item_datas = null;
	private $staff_datas = null;
	
	private $current_user_branch_cd = '';

	private $config_datas = null;
	
	
	

	function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
		$this->set_items = array('reserved_mail','reserved_tel','customer_name','target_day','staff_cd','item_cds','remark','price','regist_customer');
	}
	
	public function set_all_branch_datas ($branch_datas) {
		$this->all_branch_datas = $branch_datas;
	}

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}

	public function set_item_datas ($item_datas) {
		$this->item_datas = $item_datas;
	}
	
	public function set_current_user_branch_cd($branch_cd) {
		$this->current_user_branch_cd = $branch_cd;
	}

	public function set_staff_datas ($staff_datas) {
		$this->staff_datas = $staff_datas;
	}

	public function set_config_datas($config_datas) {
		$this->config_datas = $config_datas;
	}
	
	public function get_set_branch_cd () {
		if (empty($_POST['set_branch_cd']) ) return;
		
		return @$_POST['set_branch_cd'];
	}

	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery
		

		var target;
		var save_k1 = "";
		var save_item_cds_aft = "";
		var save_user_login = "";
		var save_mail = "";
		var save_tel = "";
		var save_p2 = "";
		
		var save_operate = "inserted";
		
		<?php parent::echoClientItem($this->set_items); //for only_branch?>	

		<?php parent::set_datepicker_date($this->current_user_branch_cd,null ,unserialize($this->branch_datas['sp_dates'])); ?>

		$j(document).ready(function() {
			
			<?php parent::echoSetItemLabel(); ?>	
			<?php parent::echoSearchCustomer(); //検索画面 ?>	
			<?php parent::echoDownloadEvent("reservation") //ダウンロード画面 From ?>	
			
			<?php  parent::set_datepicker("target_day",$this->current_user_branch_cd,false,'',$this->branch_datas['closed']); ?>			


<?php			
			if ($this->isSalonAdmin() ) { 
				$tmp_dir = SL_PLUGIN_SRC_URL;
				$tmp_action = str_replace('%7E', '~', $_SERVER['REQUEST_URI']);
				echo <<<EOT
				\$j("#branch_cd").change(function(){
					\$j("#sl_submit").html('<form id="sl_form" method="post" action="{$tmp_action}" ><input name="set_branch_cd" id="set_branch_cd" type="hidden"/></form>');
					\$j("#set_branch_cd").val(\$j("#branch_cd").val());
					\$j("#sl_form").submit();
					
				});
EOT;
			}
?>		


			<?php parent::echoCommonButton('save_operate');  ?>

			$j("#mail").change(function() {
				if (save_mail == $j("#mail").val() ) {
					$j("#button_update").attr("disabled",false);
					$j("#button_search").attr("disabled",true);
				}
				else {
					$j("#button_update").attr("disabled",true);
					$j("#button_search").attr("disabled",false);
				}
			});
			$j("#tel").change(function() {
				if (save_tel == $j("#tel").val() ) {
					$j("#button_update").attr("disabled",false);
					$j("#button_search").attr("disabled",true);
				}
				else {
					$j("#button_update").attr("disabled",true);
					$j("#button_search").attr("disabled",false);
				}
			});
			$j("#name").change(function() {
				if (save_name == $j("#name").val() ) {
					$j("#button_search").attr("disabled",true);
				}
				else {
					$j("#button_search").attr("disabled",false);
				}
			});

			$j("#item_cds input[type=checkbox]").click(function(){
				_fnSetEndTime()
			});
			$j("#time_from_aft").click(function(){
				_fnSetEndTime()
			});
			$j("#target_day").change(function(){
				_fnSetEndTime()
			});

			$j("#button_redisplay").click(function() {
				target.fnClearTable();					//テーブルデータクリア
				target.fnReloadAjax();				   //再読み込み
				target.fnPageChange( 'first' );		//ページングの最初へ移動
			});

			
			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=sales",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem(array('reserved_time','customer_name','remark')); //for only_branch?>


				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Sales_Init" } );
				  aoData.push( { "name": "target_date_num","value":$j("#target_date_number").val() } );
				  aoData.push( { "name": "target_date_patern","value":$j("#target_date_patern").val() } );
				  aoData.push( { "name": "target_date_zengo","value":"after" } );
				  aoData.push( { "name": "target_branch_cd","value":<?php echo $this->current_user_branch_cd; ?> } );
				},
				"fnDrawCallback": function () {
					$j("#lists  tbody .sl_select").click(function(event) {
						fnSelectRow(this);
					});
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {	
					<?php  parent::echoDataTableSelecter("name",false); ?>
					if (aData.status == <?php echo Salon_Reservation_Status::SALES_REGISTERD; ?> ) {
						element.append(sel_box);
					}
					else {
						element.empty();
						element.append(sel_box);
						element.append(del_box);
					}
				}
			});

		});

		function _fnSetEndTime() {
			var tmp = new Array();  
			var price = 0;
			var minute = 0;
			$j("#item_cds input[type=checkbox]").each(function (){
				if ( $j(this).is(":checked") ) {
					tmp.push( $j(this).val() );
					price += +$j(this).next().val();
					minute += +$j(this).next().next().val();
				}
			});
			$j("#price").val(price);
			if ( $j("#time_from_aft").val()  != -1  &&  $j("#target_day").val() != ""  ) {
				var dt = new Date($j("#target_day").val() + " " + $j("#time_from_aft").val() );
				dt.setMinutes(dt.getMinutes() + minute);
				$j("#time_to_aft").val(dt.getHours() + ":" + (dt.getMinutes()<10?'0':'') + dt.getMinutes());
			}
			
			save_item_cds_aft = tmp.join(",");
		}
<?php //taregt_colはtdが前提 ?>		
		function fnSelectRow(target_col) {
			fnDetailInit();
			
			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];
			$j("#name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['name']));
			save_name = htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['name']);
			$j("#mail").val(setData['aoData'][position[0]]['_aData']['email']);
<?php //[TODO]日付は原則変えないのでチェックをいれる ?>
			$j("#target_day").val(	setData['aoData'][position[0]]['_aData']['target_day']);
			$j("#status").val(setData['aoData'][position[0]]['_aData']['status']);
			$j("#time_from_aft").val(setData['aoData'][position[0]]['_aData']['time_from_bef']);
			$j("#time_to_aft").val(setData['aoData'][position[0]]['_aData']['time_to_bef']);
			$j("#staff_cd").val(setData['aoData'][position[0]]['_aData']['staff_cd_bef']);
			$j("#remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark_bef']));	
			$j("#price").val(setData['aoData'][position[0]]['_aData']['price']);
			$j("#tel").val(setData['aoData'][position[0]]['_aData']['tel']);

			save_user_login = setData['aoData'][position[0]]['_aData']['user_login']; 
			save_mail = setData['aoData'][position[0]]['_aData']['email'];
			save_tel = setData['aoData'][position[0]]['_aData']['tel'];
			save_p2 = setData['aoData'][position[0]]['_aData']['non_regist_activate_key'];
			if (save_tel === null ) save_tel ="";
			
			save_item_cds_aft = setData['aoData'][position[0]]['_aData']['item_cds_bef'];
			$j("#item_cds input[type=checkbox]").attr("checked",false);
			//selecterでやりたいが、うまくいかんのでIDにコードをくっつける
			for	 (var index in setData['aoData'][position[0]]['_aData']['item_cd_array_bef']) {
				$j("#item_cds #check_"+index).attr("checked",true);
			}
			$j("#button_update").attr("disabled",false);
			$j("#button_insert").attr("disabled",false);
			$j("#button_clear").attr("disabled",false);
			$j("#button_search").attr("disabled",false);
			
			$j("#data_detail").show();

			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN); ?>");

		}

		<?php parent::echoDataTableDeleteRow("reservation","reservation",true,'"p2":setData["aoData"][position[0]]["_aData"]["non_regist_activate_key"],'); ?>

		<?php parent::echoDisplayErrorLable(); ?>



		function fnClickAddRow(operate) {
			var check_array ;
			var is_normal = true;
			if ( ! checkItem("data_detail","time_from_aft,time_to_aft") ) is_normal = false;
			if ($j("#time_from_aft").val()  == -1) {
				fnDisplayErrorLabel("target_day_lbl","<?php _e("please input start time",SL_DOMAIN); ?>");
				return false;
			}
			if (!is_normal ) return false;

			var reservation_cd = "";
			var branch_cd = <?php echo $this->current_user_branch_cd; ?>;

			if ( save_k1 !== "" ) {
				var setData = target.fnSettings();
				reservation_cd = setData['aoData'][save_k1]['_aData']['reservation_cd']; 
				branch_cd = setData['aoData'][save_k1]['_aData']['branch_cd'];
			}
			if ( (save_mail != $j("#mail").val() ) ||
				 (save_tel != $j("#tel").val() )  ){
				save_user_login = ""; 
			}
			
			var regist_customer = $j("#regist_customer").attr("checked");

			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=reservation",
					dataType : "json",
<?php //priceに関しては今後セキュリティ上考慮する ?>
					data: {
						"reservation_cd":reservation_cd,
						"no":save_k1,
						"type":operate,
						"branch_cd":branch_cd,
						"staff_cd":$j("#staff_cd").val(),
						"target_day":$j("#target_day").val(),
						"time_from":$j("#time_from_aft").val(),
						"time_to":$j("#time_to_aft").val(),
						"item_cds":save_item_cds_aft,
						"price":$j("#price").val(),
						"remark":$j("#remark").val(),
						"user_login":save_user_login,
						"name":$j("#name").val(),
						"mail":$j("#mail").val(),
						"tel":$j("#tel").val(),
						"nonce":"<?php echo $this->nonce; ?>",
						"p2":save_p2,
						"regist_customer":regist_customer,
						"menu_func":"Reservation_Edit"
						
					},
					success: function(data) {
//						alert(data.name+" "+data.address);
//						target.fnAddData( [data.dat1, data.dat2, data.dat3] );
<?php //[TODO]redrawするが良いか ?>
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
							if (operate =="inserted" ) {
								if ($j("#regist_customer").prop("checked")) {
									alert(data.set_data.regist_msg);
									delete data.set_data.regist_msg;
								}
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

		
		function fnDetailInit( ) {
			$j("#data_detail input[type=\"text\"]").val("");
			$j("#data_detail textarea").val("");
			$j("#item_cds input").attr("checked",false);
			$j("#data_detail select").val("");
			$j("#button_update").attr("disabled", true);
			$j("#target_date_patern").children("option[value=<?php echo parent::TARGET_DATE_PATERN; ?>]").attr("selected","selected");
			
			$j("#button_search").attr("disabled",false);
			$j("#regist_customer").attr("checked", false);

			save_k1 = "";
			save_item_cds_aft = "";
			save_user_login = "";
			save_user_login = "";
			save_mail = "";
			save_tel = "";
			save_name = "";
			<?php  
					if ($this->is_multi_branch && $this->isSalonAdmin() ) echo '$j("#branch_cd").val('.$this->current_user_branch_cd.');';
			?>
			<?php parent::echo_clear_error(); ?>
		}

		<?php parent::echoRemoveModal(); ?>
		<?php parent::echoDownloadFunc($this->current_user_branch_cd,"reservation"); ?>
	
		<?php parent::echoCheckClinet(array('chk_required','zenkaku','chkMail','chkTime','chkDate','lenmax','reqOther','reqCheck','chkSpace','chkTel')); ?>		

	</script>


	<?php screen_icon(); ?>

	<h2><?php _e('Regist Reservation',SL_DOMAIN); ?>
	<?php  
			if ( $this->is_multi_branch ) {	//for only_branch
				if ($this->isSalonAdmin() ) {
					echo '(<select id="branch_cd">';
					foreach($this->all_branch_datas as $k1 => $d1 ) {
						echo '<option value="'.$d1['branch_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
					}
					echo '</select>)';
				}
				else {
					echo $this->branch_datas['name'];				
				}
			}
	?>	
	</h2>
	
	
	<div id="salon_button_div" >
	<input id="button_insert" type="button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button" />
	<input id="target_date_number" type="text" value="<?php echo $this->config_datas['SALON_CONFIG_AFTER_DAY']; ?>" class="sl_short_width"/>
	<select id="target_date_patern" >
		<option value="day" ><?php _e('day after',SL_DOMAIN); ?></option>
		<option value="week" ><?php _e('week after',SL_DOMAIN); ?></option>
		<option value="month"  ><?php _e('month after',SL_DOMAIN); ?></option>
		<option value="year" ><?php _e('year after',SL_DOMAIN); ?></option>
	
	</select>
	<input id="button_redisplay" type="button" value="<?php _e('Redisplay',SL_DOMAIN); ?>"/>
	<input id="button_download" type="button" value="<?php _e('Download',SL_DOMAIN); ?>"/>
	</div>

	<div id="data_detail" >
		<div id="multi_item_wrap" >
		<input id="mail" type="text" />
		<input id="button_search" type="button" class="sl_button" value="<?php _e('Search',SL_DOMAIN); ?>"/>
		</div>
		<input type="text" id="tel"/>
		<input type="text" id="name" value="" />
		<div id="regist_customer_wrap"  >
			<input id="regist_customer" type="checkbox"  value="<?php echo Salon_Regist_Customer::OK; ?>" />
		</div>
		<div id="date_time_wrap" >
			<input type="text" id="target_day" />
			<div id="time_sel_wrap" ><?php parent::echoTimeSelect("time_from_aft",$this->branch_datas['open_time'],$this->branch_datas['close_time'],$this->branch_datas['time_step']); ?>	
				<input type="text" id="time_to_aft" />
			</div>
		</div>
		
		<?php parent::echoStaffSelect("staff_cd",$this->staff_datas,true); ?>
		<?php parent::echoItemInputCheck($this->item_datas); ?>
		<textarea id="remark"  ></textarea>
		<input type="text" id="price" value="" />
			
		<div class="spacer"></div>
		
	</div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>
	
<div id="sl_search" class="modal">
	<div class="modalBody">
		<div id="sl_search_result"></div>
	</div>
</div>
<div id="sl_download" class="modal" >
	<div class="modalBody">
		<div id="sl_download_result"></div>
	</div>
</div>



<?php  
	if ($this->isSalonAdmin() ) echo '<div id="sl_submit" ></div>';

	}	//show_page
}		//class

