<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Item_Page extends Salon_Page {

	private $branch_column = 3;

	private $set_items = null;
	
	
	private $branch_datas = null;
	private $position_datas = null;
	
	

	function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
		if ($is_multi_branch ) {
			$this->set_items = array('item_name','short_name','branch_cd','minute','price','remark');
		}
		else {
			$this->set_items = array('item_name','short_name','minute','price','remark');
		}

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
		
		<?php parent::echoClientItem($this->set_items); //for only_branch?>	

		$j(document).ready(function() {
			
			<?php parent::echoSetItemLabel(); ?>	
			<?php parent::echoCommonButton();			//共通ボタン	?>
			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=item",
				<?php parent::echoDataTableLang(); ?>
				<?php //[20131110]ver 1.3.1 ソートモードにしない ↓のbSortをfalseに
 					parent::echoTableItem(array('item_name','branch_cd','display_sequence','price','remark','branch_name_table'),false,$this->is_multi_branch,"120px",true); 
				//for only_branch?>
				"bSort":false,
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Item_Init" } )
				},



				"fnDrawCallback": function () {
					<?php parent::echoEditableCommon("item"); ?>
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {	
					<?php parent::echoDataTableSelecter("name"); ?>
					<?php //[20131110]ver 1.3.1 
						$seq_col = $this->branch_column;
						if ($this->is_multi_branch) $seq_col = $this->branch_column+1; 
						parent::echoDataTableDisplaySequence($seq_col); 
						//[20131110]ver 1.3.1 ?>
					<?php if ($this->is_multi_branch ) parent::echoDataTableBranchData($this->branch_column,$this->branch_datas); ?>

				},
			});
		});


		<?php parent::echoDataTableSeqUpdateRow("item","item_cd",$this->is_multi_branch); ?>	//[20131110]ver 1.3.1 

		function fnSelectRow(target_col) {
			
			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];

			$j("#name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['name']));	
			$j("#short_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['short_name']));	
			$j("#branch_cd").val(setData['aoData'][position[0]]['_aData']['branch_cd']);	
			$j("#minute").val(setData['aoData'][position[0]]['_aData']['minute']);	
			$j("#price").val(setData['aoData'][position[0]]['_aData']['price']);	
			$j("#remark").val( htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));	
			$j("#button_update").removeAttr("disabled");
			$j("#button_clear").show();

			$j("#data_detail").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN);  ?>");



		}
		<?php parent::echoDataTableEditColumn("item"); ?>
		<?php parent::echoDataTableDeleteRow("item"); ?>

		function fnClickAddRow(operate) {
			if ( ! checkItem("data_detail") ) return false;
			var item_cd = "";
			var display_sequence = 0;
			if ( save_k1 !== ""  ) {
				var setData = target.fnSettings();
				item_cd = setData['aoData'][save_k1]['_aData']['item_cd']; 				
				display_sequence = setData['aoData'][save_k1]['_aData']['display_sequence']; 
			}
		<?php if ($this->is_multi_branch == false ) : //for only_branch ?>
			if (operate  =="inserted") $j("#branch_cd").val("<?php echo $this->get_default_brandh_cd();?>");
		<?php endif; ?>
			 $j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=item",
					dataType : "json",

					data: {
						"item_cd":item_cd,
						"no":save_k1,
						"type":operate,
						"name":$j("#name").val(),
						"short_name":$j("#short_name").val(),
						"branch_cd":$j("#branch_cd").val(),
						"minute":$j("#minute").val(),
						"price":$j("#price").val(),
						"remark":$j("#remark").val(),
						"display_sequence":display_sequence,
						"photo":'',
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Item_Edit"

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
			$j("#data_detail select").val("");
			$j("#data_detail textarea").val("");
			$j("#button_update").attr("disabled", "disabled");

			
			save_k1 = "";
			<?php parent::echo_clear_error(); ?>

		}

	<?php parent::echoCheckClinet(array('chk_required','zenkaku','lenmax','num')); ?>		
	<?php parent::echoColumnCheck(array('chk_required','lenmax','num')); ?>		



	</script>

	<h2 id="sl_admin_title"><?php _e('Menu Information',SL_DOMAIN); ?></h2>
	<div id="salon_button_div" >
	<input id="button_insert" type="button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button"/>
	</div>


	<div id="data_detail" >
		<input type="text" id="name" value="" />
		<input type="text" id="short_name" value="" />
<?php if ($this->is_multi_branch ): //for only_branch?>
		<select name="branch_cd" id="branch_cd" >
			<option value=""><?php _e('please select',SL_DOMAIN); ?></option>
		<?php
			foreach($this->branch_datas as $k1 => $d1 ) {
				echo '<option value="'.$d1['branch_cd'].'">'.$d1['name'].'</option>';
			}
		?>
		</select>
<?php else: ?>
		<input name="branch_cd" id="branch_cd" type="hidden" >
<?php endif; ?>
		<?php parent::echoMinuteSelect('minute'); ?>
		<input type="text" id="price" value="" />
		<textarea id="remark"  ></textarea>
		<div class="spacer"></div>

	</div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>
<?php 
	}	//show_page
}		//class

