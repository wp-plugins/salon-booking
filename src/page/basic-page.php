<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Basic_Page extends Salon_Page {


	private $set_items = null;
	
	private $all_branch_datas = null;
	private $branch_datas = null;
	private $item_datas = null;
	private $staff_datas = null;
	
	private $current_user_branch_cd = '';
	
	

	function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
		$this->set_items = array('open_time','close_time','time_step','closed_day_check','sp_date','duplicate_cnt');
	}
	
	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}

	
	public function set_current_user_branch_cd($branch_cd) {
		$this->current_user_branch_cd = $branch_cd;
	}


	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery
		var is_show_detail = true;
		var save_closed = "<?php echo $this->branch_datas['closed']; ?>";
		var save_closed_detail = "<?php echo $this->branch_datas['memo']; ?>";
		if (save_closed_detail == "MEMO" ) save_closed_detail = "";

		var target;
		
		<?php parent::echoClientItem($this->set_items); //for only_branch?>	
		<?php parent::set_datepicker_date($this->current_user_branch_cd,null ,unserialize($this->branch_datas['sp_dates'])); ?>

		$j(document).ready(function() {
			<?php //initより前で。トリガーがきかない ?>
			<?php parent::echoClosedDetail($this->branch_datas['closed'],"closed_day"); ?>

			$j("#salon_button_div input[type=button]").addClass("sl_button");
			<?php parent::echoSetItemLabel(); ?>	

			fnDetailInit();	
			$j("#target_year").val("<?php echo date_i18n('Y'); ?>");
				
			<?php  parent::set_datepickerDefault(); ?>
			<?php  parent::set_datepicker("sp_date",true,$this->branch_datas['closed']); ?>			
			$j("#button_sp_date_insert").click(function(){
				res = fnClickAddRow('inserted') 
				if (res !== false) {
					$j(target.fnSettings().aoData).each(function (){
						$j(this.nTr).removeClass("row_selected");
					});
				}
				
			});

			$j("#button_update").click(function(){
				fnClickUpdate();
				

			});


			$j("#button_redisplay").click(function() {
				target.fnClearTable();					
				target.fnReloadAjax();
				target.fnPageChange( 'first' );		
				fnDetailInit();	
			});


			
			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slbasic",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem("",true); ?>
					 { "mData":"target_date","sTitle": "<?php _e('Date',SL_DOMAIN); ?>", "sWidth":"<?php echo Salon_Page::MIDDLE_WIDTH; ?>" }
					,{ "mData":"status_title","sTitle": "<?php _e('Irregular Open/Closing day',SL_DOMAIN); ?>" }
				],
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Basic_Init" } );
				  aoData.push( { "name": "target_year","value":$j("#target_year").val() } );
				  aoData.push( { "name": "target_branch_cd","value":<?php echo $this->current_user_branch_cd; ?> } );
				},
				"fnDrawCallback": function () {
					$j("#lists  tbody .sl_select").click(function(event) {
						fnSelectRow(this);
					});
				},
<?php	//iDisplayIndexFullがデータ上のindexでidisplayIndexがページ上のindexとなる　?>
		//aDataが実際のデータで、nRowがTrオブジェクト
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {	
					<?php  parent::echoDataTableSelecter("target_date",false); ?>
					element.append(del_box);
				}
			});

		});

<?php //basic_infは支店コードもいるのでsl_echoDataTableDeleteRowは使用しない?>		
		function fnClickDeleteRow(target_col) {
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();
			var target_date = setData['aoData'][position[0]]['_aData']['target_date']; 				
			 $j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slbasic",
					dataType : "json",
					data: 	{
						"type":"deleted",
						"target_date":target_date,
						"target_branch_cd":<?php echo $this->current_user_branch_cd; ?>,
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Basic_Edit"

					}, 
					success: function(data) {
						if (data && data.status == "Error" ) {
							alert(data.message);
						}
						else {
							var rest = target.fnDeleteRow( position[0] );
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			 });			
		}


		function fnClickAddRow() {
			var sts = checkItem("_multi_item_wrap");
<?php //ここはクローム対応 ?>
			$j("#sp_date").attr("style","width:100px;margin-right:0px;" );

			if ( ! sts  ) return false;
			
			var edit_date = $j("#sp_date").val();
			var date_array = edit_date.split("/");
			edit_date = <?php _e('date_array[2] + "/" + date_array[0] + "/" + date_array[1]',SL_DOMAIN); ?>;
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slbasic",
					dataType : "json",
					data: {
						"type":"inserted",
						"target_date":edit_date,
						"status":$j("input[name=\"sp_date_radio\"]:checked").val(),
						"target_branch_cd":<?php echo $this->current_user_branch_cd; ?>,
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Basic_Edit"
						
					},

					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							target.fnAddData( data.set_data );
								
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });			
		}
		
		<?php parent::echoTime25Check(); ?>		
		<?php parent::echoClosedDetailCheck(); ?>
		
		function fnClickUpdate() {
<?php			//sp_dateはここではチェックしない。他の画面とはちとちがう ?>
			if ( ! checkItem("data_detail","sp_date") ) return false;

			var op = $j("#open_time").val();
			if (!_fnCheckTimeStep(+$j("#time_step").val(),op.slice(-2) ) ) return false;
			var cl = $j("#close_time").val();
			if (!_fnCheckTimeStep(+$j("#time_step").val(),cl.slice(-2) ) ) return false;
						
			<?php //半休対応　?>
			if (!_fnCheckClosedDetail(+$j("#time_step").val()) ) return false;
			$j(".sl_from").triggerHandler("change");

			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slbasic",
					dataType : "json",
					data: {
						"type":"updated",
						"open_time":$j.trim($j("#open_time").val()),
						"close_time":$j.trim($j("#close_time").val()),
						"salon_closed":save_closed,
						"time_step":$j("#time_step").val(),
						"target_branch_cd":<?php echo $this->current_user_branch_cd; ?>,
						"duplicate_cnt":$j("#duplicate_cnt").val(),
						"memo":save_closed_detail,
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Basic_Edit"
					},

					success: function(data) {
						
						if (data.status == "Error" ) {
							fnDetailInit(true);
							alert(data.message);
							return false;
						}
						else {
							save_closed_detail = data.set_data.memo;
							fnDetailInit(true);
							alert(data.message);
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });			
		}



		
		function fnDetailInit(redisplay ) {
			if (!redisplay)	{
				$j("#data_detail input[type=\"text\"]").val("");
				$j("#open_time").val("<?php echo Salon_Component::formatTime($this->branch_datas['open_time']); ?>");
				$j("#close_time").val("<?php echo Salon_Component::formatTime($this->branch_datas['close_time']); ?>");
				$j("#time_step").val("<?php echo $this->branch_datas['time_step']; ?>");
				$j("#duplicate_cnt").val("<?php echo $this->branch_datas['duplicate_cnt']; ?>");
			}
			$j("#button_update").attr("disabled", false);
			$j("#sp_date_radio_close").attr("checked","checked");
			<?php //[2014/10/01]半休対応 ?>			
			var tmp = save_closed.split(",");
			var tmp_detail = save_closed_detail.split(";");
			$j("#closed_day_check input").attr("checked",false);
			for (var i=0; i < tmp.length; i++) {
				$j("#closed_day_"+tmp[i]).attr("checked",true);
				var tmp_time_array = Array();
				if (tmp_detail[i]) {
					tmp_time_array = tmp_detail[i].split(",");
				}
				else {
					tmp_time_array[0] = "<?php echo $this->branch_datas['open_time']; ?>";
					tmp_time_array[1] = "<?php echo $this->branch_datas['close_time']; ?>";
				}
				$j("#closed_day_"+tmp[i]+"_fr").val(tmp_time_array[0].slice(0,2)+":"+tmp_time_array[0].slice(-2));
				$j("#closed_day_"+tmp[i]+"_to").val(tmp_time_array[1].slice(0,2)+":"+tmp_time_array[1].slice(-2));
				
				$j("#sl_holiday_detail_wrap_"+tmp[i]).show();
			}

			<?php parent::echo_clear_error(); ?>
			
			

		}

		<?php parent::echoCheckClinet(array('chk_required','chkTime','lenmax','range','chkDate','num')); ?>

	</script>

	<?php	if ($this->is_multi_branch ) $header = '('.$this->branch_datas['name'].')';
				else $header = '';
				?>
	<h2 id="sl_admin_title"><?php echo __('Basic Information',SL_DOMAIN).$header; ?></h2>
	<div id="salon_button_div" >
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>" />
	<input id="target_year" type="text" class="sl_short_width" />
	<span id="sp_date_inline_char3"><?php _e('year',SL_DOMAIN); ?></span>
	<input id="button_redisplay" type="button" value="<?php _e('Redisplay',SL_DOMAIN); ?>"/>
	</div>
	<div id="data_detail" >
		<input id="duplicate_cnt" type="text"   />
		<input id="open_time" type="text"   />
		<input id="close_time" type="text"   />
		<?php parent::echoClosedCheck($this->branch_datas['closed'],"closed_day"); ?>
		<?php parent::echoTimeStepSelect('time_step'); ?>
<?php /*?>
		//戻すときは、check_itemのID部分も変更しているので注意
		<div id="multi_item_wrap" >
			<input type="text" id="sp_date"  />
			<INPUT type="radio"  id="sp_date_radio_open"  name="sp_date_radio" class="sl_radio" value="<?php echo Salon_Status::OPEN; ?>">
			<label for="sp_date_radio_open"><?php _e('On Business',SL_DOMAIN); ?></label>
			<INPUT type="radio" id="sp_date_radio_close"  name="sp_date_radio" class="sl_radio" value="<?php echo Salon_Status::CLOSE; ?>">
			<label for="sp_date_radio_close"><?php _e('Special Absence',SL_DOMAIN); ?></label>
			<input id="button_sp_date_insert" type="button" class="sl_button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
<?php */?>
		<div id="_multi_item_wrap" >
			<input type="text" id="sp_date" style="width:100px;margin-right:0px;"  />
			<INPUT type="radio"  id="sp_date_radio_open"  name="sp_date_radio"  style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Status::OPEN; ?>">
			<label for="sp_date_radio_open" style="margin:5px;text-align:left;width:50px;"><?php _e('On Business',SL_DOMAIN); ?></label>
			<INPUT type="radio" id="sp_date_radio_close"  name="sp_date_radio"  style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Status::CLOSE; ?>">
			<label for="sp_date_radio_close" style="margin:5px;text-align:left;width:50px;"><?php _e('Special Absence',SL_DOMAIN); ?></label>
			<input id="button_sp_date_insert" type="button" class="sl_button" value="<?php _e('Add',SL_DOMAIN); ?>" style="width:50px;margin-right:0px;"/>
		</div>
		<div class="spacer"></div>
	</div>
	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>
<?php  
	}	//show_page
}		//class

