<?php
$set_lang = false;
if (defined ( 'WPLANG' )  ) {
	$file_name = SL_PLUGIN_DIR.'/lang/salon-page-'.WPLANG.'.php';
	if ( file_exists($file_name) ) {
		require_once($file_name);
		$set_lang = true;
	}
}
if (! $set_lang ) {
	$file_name = SL_PLUGIN_DIR.'/lang/salon-page-com.php';
	if ( file_exists($file_name) ) require_once($file_name);
	else {
		throw new Exception(Salon_Component::getMsg('E007',__FILE__.':'.__LINE__ ) );
	}
}


class Salon_Page {
	const INPUT_BOTTOM_MARGIN = 20;
	const SHORT_WIDTH = '50px';
	const MIDDLE_WIDTH = '100px';
	const LONG_WIDTH = '150px';
	
	const TARGET_DATE_PATERN = 'day';
	
	private $version = '1.0';
	protected $is_multi_branch = false;
	protected $is_salon_admin = false;
	protected $nonce = '';


	public function __construct($is_multi_branch) {
		$this->is_multi_branch = $is_multi_branch;
		$this->nonce = wp_create_nonce(session_id());
	}
	public function set_isSalonAdmin($is_salon_admin){
		$this->is_salon_admin = $is_salon_admin;
	}
	
	public function isSalonAdmin() {
		return $this->is_salon_admin;
	}
	
	public function get_default_brandh_cd() {
		return Salon_Default::BRANCH_CD;
	}


	static function getResponseType() {
		if (empty($_POST['func']) )	return Response_Type::JASON_406_RETURN;
		else return Response_Type::JASON;
	}

	static function echoInitData($datas) {
		$data_cnt = count($datas);
		//indexが歯抜けの可能性があるので降りなおす
		$i = 0;
		
		if ($datas ) {
			foreach ($datas as $k1 => $d1) {
				$i++;
				$datas[$k1]['no'] = sprintf("%03d",$i);
				$datas[$k1]['check'] = 0;	//[TODO]いらない？
			}
		}
		$jdata = array();
		$jdata['iTotalRecords'] = $data_cnt;
		$jdata['iTotalDisplayRecords'] = $data_cnt;
		$jdata['sEcho'] = 1;
		if (is_null($datas) )$datas = array();
		$jdata['aaData'] = $datas;
		echo json_encode($jdata);
	}

	static function echoClientItem($items) {
		$item_contents = Salon_Page::setItemContents();	
		echo 'var check_items = { ';
		$tmp = array();
		if (is_array($items) ){
			foreach ($items as $d1) {
				$add_class = '';
				$tmp[] ='"'.$item_contents[$d1]['id'].'": '.
						'{'.
						' "id" : "'.$item_contents[$d1]['id'].'"'.
						',"class" : "'.implode(" ",$item_contents[$d1]['check'])." ".implode(" ",$item_contents[$d1]['class']).'"'.
						',"label" : "'.$item_contents[$d1]['label'].'"'.
						',"tips" : "'.$item_contents[$d1]['tips'].'"'.
						'}';
			}
		}
		echo join(',',$tmp);
		echo '};';
		self::echoHtmlpecialchars();

	}
	

	static function echoHtmlpecialchars() {
		echo <<<EOT
			function htmlspecialchars_decode (data) {
				if (data ) {
					data = data.toString().replace(/&lt;/g, "<").replace(/&gt;/g, ">");
					data = data.replace(/&#0*39;/g, "'"); 
					data = data.replace(/&quot;/g, '"');
					data = data.replace(/&amp;/g, '&');
				}
				return data;
			}
			function htmlspecialchars (data) {
				if (data) {
					data = data.toString();
					data = data.replace(/&/g, "&amp;");
					data = data.replace(/</g, "&lt;").replace(/>/g, "&gt;");
					data = data.replace(/'/g, "&#039;");
					data = data.replace(/\"/g, "&quot;");
				}
				return data;
			}
EOT;
	}
	
	static  function echoSetItemLabel($is_Tables = true) {
		echo <<<EOT
			for(index in check_items) {
				if (check_items[index] ) {
					var ast = "";
					if (check_items[index]["class"].indexOf("chk_required") != -1) {
						ast = "<span class=\"sl_req\">*</span>";
					}
					var id = check_items[index]["id"];
					\$j("#"+id).addClass(check_items[index]["class"]);
					\$j("#"+id).before("<label id=\""+id+"_lbl\" for=\""+id+"\" >"+check_items[index]["label"]+ast+":<span class=\"small\"></span></label>");
				}
			}
EOT;
		if ($is_Tables ) {
			echo <<<EOT2
			\$j(window).bind('resize', function () {
					target.fnAdjustColumnSizing(true);
			} );
			
EOT2;
		}
	}


	static function echoCommonButton($add_operation = '"inserted"'){
		$show = __('Show Details',SL_DOMAIN);
		$hide = __('Hide Details',SL_DOMAIN);
		echo <<<EOT
			\$j("#salon_button_div input").addClass("sl_button");
			fnDetailInit();	
			\$j("#button_insert").click(function(){
				if (\$j("#data_detail").is(":hidden")) {
					\$j("#data_detail").show();
					return;
				}
				fnClickAddRow({$add_operation});
			});
			\$j("#button_update").click(function(){
				fnClickAddRow("updated");
			});
			\$j("#button_clear").click(function(){
				fnDetailInit(true);	
				\$j(target.fnSettings().aoData).each(function (){
					\$j(this.nTr).removeClass("row_selected");
				});
			});
			\$j("#button_detail").click(function(){
				\$j("#data_detail").toggle();
				if (\$j("#data_detail").is(":visible") ) \$j("#button_detail").val("{$hide}")
				else \$j("#button_detail").val("{$show}");
			});
	
			fnDetailInit();
			\$j("#data_detail").hide();
			\$j("#button_detail").val("{$show}");
	
EOT;
	}


	static function echoDataTableLang() {
		$sLengthMenu = __('Display _MENU_ records per page',SL_DOMAIN);
		$sNext = __('Next Page',SL_DOMAIN);
		$sPrevious = __('Prev Page',SL_DOMAIN);
		$sInfo = __('Showing _START_ to _END_ of _TOTAL_ records',SL_DOMAIN);
		$sSearch = __('search',SL_DOMAIN);
		$sEmptyTable = __('No data available in table',SL_DOMAIN);
		$sLoadingRecords = __('Loading...' ,SL_DOMAIN);
		$sInfoEmpty = __('Showing 0 to 0 of 0 entries' ,SL_DOMAIN);
		$sZeroRecords = __('No matching records found' ,SL_DOMAIN);
		
		echo <<<EOT
			"bAutoWidth": false,
			"bProcessing": true,
			"sScrollX": "100%",
			"bScrollCollapse": true,
			
			//"bServerSide": true,
			"oLanguage": {
			        "sLengthMenu": "{$sLengthMenu}"
			        ,"oPaginate": {
			            "sNext": "{$sNext}"
			            ,"sPrevious": "{$sPrevious}"
				    }
					,"bPaginate": false
		        	,"sInfo": "{$sInfo}"
			        ,"sSearch": "{$sSearch}："
					,"sEmptyTable":"{$sEmptyTable}"
					,"sLoadingRecords":"{$sLoadingRecords}"
					,"sInfoEmpty":"{$sInfoEmpty}"
					,"sZeroRecords":"{$sZeroRecords}"
			},	
			fnServerData: function(sSource, aoData, fnCallback, oSettings) {
				\$j.ajax({
					url: sSource,
					type: "POST",
					data: aoData,
					dataType: "json",
					success: function(data) {
						if (data === null || data.status == "Error" ) {
							if (data) alert(data.message);
							fnCallback({"iTotalRecords":0,"iTotalDisplayRecords":0,"sEcho":1,"aaData":[]});
						}
						else {
							fnCallback(data);
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
				})
			},
			
EOT;
	}


	static function echoTableItem($items,$is_only_common_part = false,$is_multi_branch = true,$operate_width = '120px',$isForceNoSort = false) {	
		$operate_title = __('Operation',SL_DOMAIN);
		echo <<<EOT
			"aoColumns": [
				{ "mData":"no","sTitle": "No" ,"sClass":"sl_select","bSearchable": false,"bSortable": false,"sWidth":"20px"},
				{ "mData":"check","sTitle": "{$operate_title}","bSortable": false,"bSearchable": false,"sWidth":"{$operate_width}"},
EOT;
	
		if ($is_only_common_part ) return;
		$item_contents = Salon_Page::setItemContents();	

	
		$tmp = array();
		foreach ($items as $d1) {
//			if ($isForceNoSort ) $sort = 'false';
//			else 		empty ($item_contents[$d1]['table']['sort'])  ? $sort = 'false' : $sort = $item_contents[$d1]['table']['sort'];
//			if ($isForceNoSort ) $search = 'false';
//			else 		empty ($item_contents[$d1]['table']['search']) ? $search = 'false' : $search = $item_contents[$d1]['table']['search'];
			empty ($item_contents[$d1]['table']['sort'])  ? $sort = 'false' : $sort = $item_contents[$d1]['table']['sort'];
			empty ($item_contents[$d1]['table']['search']) ? $search = 'false' : $search = $item_contents[$d1]['table']['search'];
			empty ($item_contents[$d1]['table']['visible']) ? $visible = 'false' : $visible = $item_contents[$d1]['table']['visible'];
			$width = '';
			if (!empty ($item_contents[$d1]['table']['width']) ) $width = ',"sWidth" : "'.$item_contents[$d1]['table']['width'].'"';
			if ($is_multi_branch == false && $d1 == 'branch_cd' ) {
				$visible = 'false';
			}
			
			$tmp[] =
					'{'.
					' "mData" : "'.$item_contents[$d1]['id'].'"'.
					',"sTitle" : "'.$item_contents[$d1]['label'].'"'.
					',"sClass" : "'.$item_contents[$d1]['table']['class'].'"'.
					$width.
					',"bSortable" : '.$sort.
					',"bSearchable" : '.$search.
					',"bVisible" : '.$visible.
					'}';
		}
		echo join(',',$tmp);
		echo '],';
	
	}
	

	public function echoEditableCommon($target_name,$add_col = "") {
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action='.$target_name;
		$submit = __('change',SL_DOMAIN);
		$cancel = __('cancel',SL_DOMAIN);
		$placeholder = __('click edit',SL_DOMAIN);
		
		$menu_func = ucwords($target_name);
		$add_char1 = '';
		if ( ! empty($add_col) ) {
			if (is_array($add_col) ) {
				foreach ($add_col  as $d1 ) {
					$add_char1 .= ',"'.$d1.'":setData["aoData"][position[0]]["_aData"]["'.$d1.'"] ';
				}
			}
			else {
				$add_char1 = ',"'.$add_col.'":setData["aoData"][position[0]]["_aData"]["'.$add_col.'"] ';
			}
		}
		//positionは行位置、列位置(表示のみ）(=tdの数)、列位置（全体=aoColumnの数）
		echo <<<EOT
				\$j("#lists tbody .sl_editable").editable("{$target_src}", {
					
					submitdata: function ( value, settings ) {
						var setData = target. fnSettings();
						var position = target.fnGetPosition( this );
						return {
							"{$target_name}_cd": setData['aoData'][position[0]]['_aData']['{$target_name}_cd']
							,"column": position[2]
							,"nonce":"{$this->nonce}"
							,"menu_func":"{$menu_func}_Col_Edit"
							$add_char1
						};
					},
					callback: function( sValue, y ) {
						var jdata = \$j.evalJSON( sValue );
						var position = target.fnGetPosition( this );
						if (jdata.status ==  "Ok" ) {
							target.fnUpdate(jdata.set_data,position[0],position[2],false);
						}
						alert(jdata.message);
					},
					
					onsubmit:function(settings,td) {
						if ( !checkColumnItem( td ) )return false;
					},
			
					onerror: function (settings, original, xhr) {
						var jdata = \$j.evalJSON( xhr.responseText );
						alert(jdata.message);
					},
					onreset: function (settings, original) {
						original.revert = htmlspecialchars(original.revert);
					},
					type : "text",
					submit : "{$submit}",
					cancel : "{$cancel}",
					placeholder : "{$placeholder}",
					"height": "20px"
				} );
				\$j("#lists  tbody .sl_select").click(function(event) {
					fnSelectRow(this);
				});
EOT;
	}

	static function echoDataTableSelecter($target_name,$is_append = true,$del_disp='',$del_msg='') {
		
		if (empty($del_disp) ) $del_disp = __('Delete ',SL_DOMAIN);
		if (empty($del_msg) ) $del_msg = __('Delete ok?',SL_DOMAIN);
		$sel_disp = __('Select',SL_DOMAIN);
		//セレクターは１列目
		echo <<<EOT
			var element = \$j("td:eq(1)", nRow);
			element.text("");
	//		var checkbox = \$j("<input>")
	//				.attr("type", "checkbox")
	//				.attr("id", "check_" + iDataIndex)
	//				.attr("value",1)
	//				.attr("checked", aData.check == 1 ? true : false)
	//				.change(function() {
	//	            	aData.check = \$j(this).val();
	//				});
	
			var sel_box = \$j("<input>")
					.attr("type","button")
					.attr("id","sl_select_btn_"+iDataIndex)
					.attr("name","sl_update_"+iDataIndex)
					.attr("value","{$sel_disp}")
					.attr("class","sl_button sl_button_short")
					.click(function(event) {
						fnSelectRow(this.parentNode);
					});
			var del_box = \$j("<input>")
					.attr("type","button")
					.attr("id","sl_delete_btn_"+iDataIndex)
					.attr("name","sl_delete_"+iDataIndex)
					.attr("class","sl_button sl_button_short")
					.attr("value","{$del_disp}")
					.click(function(event) {
						if (confirm(htmlspecialchars_decode(aData.{$target_name})+"{$del_msg}") ) {
							fnClickDeleteRow(this.parentNode);
						}
					});
EOT;
		if ($is_append) {
			echo <<<EOT2
	//		element.append(checkbox);
			element.append(sel_box);
			element.append(del_box);
EOT2;
		}
		
	}

	static function echoDataTableBranchData($target_column,$branch_datas) {
		$option_no_regist = __('not registered',SL_DOMAIN);
		echo <<<EOT
			var element_branch = \$j("td:eq({$target_column})", nRow);
			element_branch.text("");
			var selecter = \$j("<select>")
				.append(\$j("<option>").html("{$option_no_regist}").val(""))
EOT;
			if (is_array($branch_datas) ) {
				foreach($branch_datas as $k1 => $d1 ) {
					echo '.append($j("<option>").html("'.htmlspecialchars($d1['name'],ENT_QUOTES).'").val("'.$d1['branch_cd'].'"))';
				}
			}
		echo <<<EOT2
			.val(aData.branch_cd)
			.change(function(event) {
				fnUpdateColumn(this.parentNode,"branch_cd",\$j(this).val() );
			});
			element_branch.append(selecter);
EOT2;
	
	}

	static function echoDataTablePositionData($target_column,$position_datas) {
		$option_no_regist = __('not registered',SL_DOMAIN);
		$staff_cd_no_change = get_option('salon_initial_user',1);
		$set_position_name = __('MAINTENANCE',SL_DOMAIN);

		echo <<<EOT
			var element_position = \$j("td:eq({$target_column})", nRow);
			if (aData.staff_cd == {$staff_cd_no_change} ){
				element_position.text("{$set_position_name}");
			}
			else {  
				element_position.text("");
				var selecter = \$j("<select>")
					.append(\$j("<option>").html("{$option_no_regist}").val(""))
EOT;
				if (is_array($position_datas) ) {
					foreach($position_datas as $k1 => $d1 ) {
						echo '.append($j("<option>").html("'.htmlspecialchars($d1['name'],ENT_QUOTES).'").val("'.$d1['position_cd'].'"))';
					}
				}
		echo <<<EOT2
				.val(aData.position_cd)
				.change(function(event) {
					fnUpdateColumn(this.parentNode,"position_cd",\$j(this).val() );
				});
				element_position.append(selecter);
			}
EOT2;
		
	}
	public function echoDataTableEditColumn($target_name,$add_col = "") {	
		
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action='.$target_name;
		$add_char1 = '';
		if ( ! empty($add_col) ) {	//[TODO]salesとresevationのIDで使用しているが、IDはやめられないか
			$add_char1 = ',"'.$add_col.'":setData["aoData"][position[0]]["_aData"]["'.$add_col.'"] ';
		}
		$menu_func = ucwords($target_name);
		
		$check_error_msg = __('select please',SL_DOMAIN);
		
		//セレクトボックスであれば何らかの値がはいるはずなので
		//クライアント側では空データのチェックのみをいれとく
		//不正データはサーバ側でチェックする。
		echo <<<EOT
			function fnUpdateColumn(target_col,column_name,set_value) {
				if (set_value == "" ) {
					alert("{$check_error_msg}");
					return false;
				}
				var position = target.fnGetPosition( target_col );
				var setData = target.fnSettings();
				var target_cd = setData['aoData'][position[0]]['_aData']['{$target_name}_cd']; 				
				\$j.ajax({
						type: "post",
						url:  "{$target_src}",
						dataType : "json",
						data: 	{
							"{$target_name}_cd":target_cd
							,"column":position[2]
							,"value":set_value
							,"func":"update"
							,"nonce":"{$this->nonce}"
							,"menu_func":"{$menu_func}_Col_Edit"
							{$add_char1}
						}, 
						success: function(data) {
							if (data === null || data.status == "Error" ) {
								if (data) alert(data.message);
							}
							else {
								alert(data.message);
								setData['aoData'][position[0]]['_aData'][column_name] = set_value;
							}
						},
						onerror:  function(XMLHttpRequest, textStatus){
							alert (textStatus);
						}
				 });			
			}
EOT;
		
	}

	public function echoDataTableDeleteRow($target_name,$target_key_name = '',$is_delete_row = true,$add_parm = '') {
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action='.$target_name;
		if (empty($target_key_name) ) $target_key_name = $target_name;
		$menu_func = ucwords($target_name);
		if ($is_delete_row) $delete_string = 'var rest = target.fnDeleteRow( position[0] );	fnDetailInit();';
		else $delete_string  = 'target.fnUpdate( data.set_data ,position[0] );	fnDetailInit();';


		
		echo <<<EOT
			function fnClickDeleteRow(target_col) {
				var position = target.fnGetPosition( target_col );
				var setData = target.fnSettings();
				var target_cd = setData['aoData'][position[0]]['_aData']['{$target_key_name}_cd']; 				
				 \$j.ajax({
						type: "post",
						url:  "{$target_src}", 
						dataType : "json",
						data: 	{
							$add_parm
							"{$target_key_name}_cd":target_cd,
							"type":"deleted",
							"nonce":"{$this->nonce}",
							"menu_func":"{$menu_func}_Edit"
						}, 
						success: function(data) {
							if (data === null || data.status == "Error" ) {
								if (data) alert(data.message);
							}
							else {
								{$delete_string}
							}
						},
						error:  function(XMLHttpRequest, textStatus){
							alert (textStatus);
						}
				 });			
			}
EOT;
		
	}

	static function echoClosedCheck($closed_day,$tag_id){
		//closed_day は０→日カンマ区切り
		echo '<div id="'.$tag_id.'_check" class="sl_checkbox" >	';
		$closed = array(false,false,false,false,false,false,false);
		$week = array(__('sun',SL_DOMAIN),__('mon',SL_DOMAIN), __('tue',SL_DOMAIN), __('wed',SL_DOMAIN), __('thr',SL_DOMAIN), __('fri',SL_DOMAIN), __('sat',SL_DOMAIN));
		$datas = explode(',',$closed_day);
		foreach ($datas as $d1 ) {
			$closed[$d1] = true;
		}
		for ( $i = 0 ; $i < 7 ; $i++ ) {		
			echo '<input type="checkbox" id="'.$tag_id.'_'.$i.'" value="'.$i.'" '.($closed[$i] ? 'checked="checked"' :'').'/><label for="'.$tag_id.'_'.$i.'">&nbsp;'.$week[$i].'</label>';
		}
		echo '</div>';
	}

	static function echo_clear_error() {		
	//[TODO]IEだとくずれてしまうのでmargin1加算
		$default_margin = self::INPUT_BOTTOM_MARGIN;
		echo <<<EOT
				\$j("span").removeClass("error");
				for(index in check_items) {
					var id = check_items[index]["id"];
					\$j("#"+id+"_lbl").children(".small").text(check_items[index]["tips"]);
					var diff = \$j("#"+id+"_lbl").outerHeight(true) - \$j("#"+id).outerHeight(true);
					if (diff > 0 ) {
						diff += {$default_margin}+5;
						\$j("#"+id).attr("style","margin-bottom: "+diff+"px;");
						\$j("#"+id+"_lbl").children(".samll").attr("style","text-align:left;");
					}
				}
EOT;
	
	}

	static function echoItemInputCheck($item_datas,$is_noEcho = false){
		$echo_data  = '<div id="item_cds" class="sl_checkbox" >';
		if ($item_datas) {
			foreach ( $item_datas  as $k1 => $d1) {
				$edit_price = number_format($d1['price']);
				$edit_name = htmlspecialchars($d1['name'],ENT_QUOTES);
				//inputの中で価格と時間の順番は変更しない
				//[TODO]空白はいくつにする？
				$echo_data .= <<<EOT
					<input type="checkbox" id="check_{$d1['item_cd']}" value="{$d1['item_cd']}" />
					<input type="hidden" id="check_price_{$d1['item_cd']}" value="{$d1['price']}" />
					<input type="hidden" id="check_minute_{$d1['item_cd']}" value="{$d1['minute']}" />
					<label for="check_{$d1['item_cd']}">&nbsp;{$edit_name}({$edit_price})&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
EOT;
			}
		}
		$echo_data .= '</div>';
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;
		
	}
	static function echoItemInputCheckTable($item_datas,$is_noEcho = false){
		$echo_data  = '<div id="item_cds" class="sl_checkbox" >';
		if ($item_datas) {
			$echo_data .= '<table id="sl_front_items"><tbody>';
			$loop_max = count($item_datas);
			for($i = 0 ; $i < $loop_max ; $i += 2 ){
				$echo_data .= '<tr>';
				for($j= 0 ; $j < 2 ; $j++ ) {
					if ( $loop_max > ($i+$j) ) {
						$d1 = $item_datas[$i+$j];
						$edit_price = number_format($d1['price']);
						$edit_name = htmlspecialchars($d1['short_name'],ENT_QUOTES);
						$echo_data .= <<<EOT
							<td>
							<input type="checkbox" id="check_{$d1['item_cd']}" value="{$d1['item_cd']}" />
							<input type="hidden" id="check_price_{$d1['item_cd']}" value="{$d1['price']}" />
							<input type="hidden" id="check_minute_{$d1['item_cd']}" value="{$d1['minute']}" />
							</td><td>
							<label for="check_{$d1['item_cd']}">{$edit_name}({$edit_price})</label>
							</td>
EOT;
					}
				}
				$echo_data .= '</tr>';
			}
			$echo_data .= "</tbody></table>";
		}
		$echo_data .= '</div>';
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;
		
	}

	static function echoStaffSelect($target_name,$staff_datas,$is_noname_select = true,$is_noEcho = false){
		$echo_data = '<select id="'.$target_name.'">';
		if ($is_noname_select ) {
			$echo_data .= '<option value="'.Salon_Default::NO_PREFERENCE.'">'.__('Anyone',SL_DOMAIN).'</option>';
		}
		else {
			$echo_data .= '<option value="">'.__('select please',SL_DOMAIN).'</option>';
		}
		foreach($staff_datas as $k1 => $d1 ) {
			$echo_data .= '<option value="'.$d1['staff_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
		}
		$echo_data .= '</select>';
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;
		
	}

	static function echoTimeSelect($id,$open_time,$close_time,$time_step,$is_noEcho = false) {	
	
		$dt = new DateTime($open_time);
		$last_hour = substr($close_time,0,2).":".substr($close_time,2,2);
		$dt_max = new DateTime($last_hour);
		$echo_data =  '<select name="'.$id.'" id="'.$id.'">';
//		$echo_data .=   '<option value="-1" >'.__('no setting',SL_DOMAIN).'</option>';
		while($dt <= $dt_max ) {
			$echo_data .= '<option value="'.$dt->format("G:i").'" >'.$dt->format("H:i").'</option>';
			$dt->modify("+".$time_step." minutes");
		}
		$echo_data .= '</select>';
			
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;
	
			
	}
	
	static function echoDisplayErrorLable() {
		echo <<<EOT
			function fnDisplayErrorLabel(target,msg) {
			var label = \$j("#"+target).find("span");
			var set_msg = msg;
			if (label.hasClass("error") ) {
				set_msg = label.text()+" "+set_msg;
			}
			label.text(set_msg );
			label.addClass("error small");
		}
EOT;
	}


	static function echoRoleSelect($id,$is_noEcho = false) {	
	
		$echo_data =  '<select name="'.$id.'" id="'.$id.'">';
		global $wp_roles;
		foreach ($wp_roles->role_names as $k1 => $d1) {
			if ($k1 != 'subscriber' ) 
				$echo_data .=  '<option value="'.$k1.'">'._x($d1,'User role').'</option>';
		}
		$echo_data .= '</select>';
			
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;
	
			
	}

	static function echoTimeStepSelect($id,$is_noEcho = false) {	
	
		$echo_data =  '<select name="'.$id.'" id="'.$id.'">';
		$datas = array(15,30,60);
		foreach ($datas as  $d1) {
			$echo_data .=  '<option value="'.$d1.'">'.$d1.'</option>';
		}
		$echo_data .= '</select>';
			
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;
			
	}

	static function echoMinuteSelect($id,$is_noEcho = false) {	
	
		$echo_data =  '<select name="'.$id.'" id="'.$id.'">';
		$datas = array(30,60,90,120,150);
		foreach ($datas as  $d1) {
			$echo_data .=  '<option value="'.$d1.'">'.$d1.'</option>';
		}
		$echo_data .= '</select>';
			
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;
			
	}

	static function echoSearchCustomer($url = '') {
		if (empty($url) ) $url = get_bloginfo( 'wpurl' );
		$target_src = $url.'/wp-admin/admin-ajax.php?action=search';
		$check_char = __('No',SL_DOMAIN);
		echo <<<EOT
			\$j("#button_search").click(function(){
				\$j.ajax({
					type: "post",
					url:  "{$target_src}", 
					dataType : "json",
					data: {
						"type":"reservation",
						"name":\$j("#name").val(),
						"mail":\$j("#mail").val(),
						"menu_func":"Search_Page",
						"tel":\$j("#tel").val()
					},
		
					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
						}
						else {
							var mW = \$j("#sl_search").find('.modalBody').innerWidth() / 2;
							var mH = \$j("#sl_search").find('.modalBody').innerHeight() / 2;
							\$j("#sl_search").find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
							\$j("#sl_search").css({'display':'block'});
							\$j("#sl_search").animate({'opacity':'1'},'fast');
							\$j("#sl_search_result").html(data.set_data);
							if (+data.cnt > 0 ) {
								\$j("#sl_search_result tr").click(function(event) {
									if (this.children[0].innerHTML == "{$check_char}" ) return;
									\$j("#name").val(this.children[1].innerHTML);
									var tel = this.children[2].innerHTML;
									if (! tel) tel = this.children[3].innerHTML;
									\$j("#tel").val(tel);
									\$j("#mail").val(this.children[4].innerHTML);
									save_tel = tel;
									save_mail = this.children[4].innerHTML;
									save_user_login = \$j(this).find("input").val();
									fnRemoveModalResult(this.parentNode.parentNode);
								});
							}
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
				});			
			});
		\$j('#button_close1,#button_close2').click(function(){
			fnRemoveModalResult(this);
		});
EOT;
	
	}
	
	static function echoDownloadEvent($target){
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action=download';
		echo <<<EOT
			\$j("#button_download").click(function(){
				 \$j.ajax({
						type: "post",
						url:  "{$target_src}", 
						dataType : "json",
						data: {
							"type":"select",
							"target":"{$target}"
						},
		
						success: function(data) {
							if (data.status == "Error" ) {
								alert(data.message);
								return false;
							}
							else {
								var mW = \$j("#sl_download").find('.modalBody').innerWidth() / 2;
								var mH = \$j("#sl_download").find('.modalBody').innerHeight() / 2;
								\$j("#sl_download").find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
								\$j("#sl_download").css({'display':'block'});
								\$j("#sl_download").animate({'opacity':'1'},'fast');
								\$j("#sl_download_result").html(data.set_data);
								if (+data.cnt > 0 ) {
									\$j("#sl_download_result input[type=button]").click(function(event) {
										
									});
								}
							}
						},
						error:  function(XMLHttpRequest, textStatus){
							alert (textStatus);
							return false;
						}
				 });			
				
			});
			\$j('#button_close1,#button_close2').click(function(){
				fnRemoveSearchResult();
			});
		
EOT;
	}

	static function echoRemoveModal() {	
		echo <<<EOT
			function fnRemoveModalResult(element) {
				var target = \$j(element).parent().parent().parent();
				target.animate(
					{opacity:0,},
					{duration:'fast',complete:
						function() {
							\$j(element).parent().html("");
							target.css({'display':'none'});
						},
				});
			}
EOT;
	}

	static function echoDayFormat() {	
		echo <<<EOT
			function fnDayFormat(date,format) {
				edit = format;
				edit = edit.replace("%Y",date.getFullYear());
				edit = edit.replace("%m",(date.getMonth()+1<10?"0":"")+(date.getMonth()+1));
				edit = edit.replace("%d",(date.getDate()+0<10?"0":'')+date.getDate());
				return edit;
			}
EOT;
	}


	static function echoWorkingCheck($is_noEcho = false){
		$working_status_data  = array(
			Salon_Working::USUALLY => __('Regular Duty',SL_DOMAIN)
			,Salon_Working::DAY_OFF => __('Day Off',SL_DOMAIN)
//			,Salon_Working::IN => __('IN',SL_DOMAIN)
//			,Salon_Working::OUT => __('OUT',SL_DOMAIN)
			,Salon_Working::LATE_IN => __('Late In',SL_DOMAIN)
			,Salon_Working::EARLY_OUT => __('Early Out',SL_DOMAIN)
			,Salon_Working::HOLIDAY_WORK => __('Holiday Shift',SL_DOMAIN)
//			,Salon_Working::ABSENCE => __('ABSENCE',SL_DOMAIN)
		);
		
		$echo_data = '<div id="working_cds" class="sl_checkbox" >';
		foreach ($working_status_data as $k1 => $d1 ) {
			$echo_data .= '<input type="checkbox" id="check_'.$k1.'" value="'.$k1.'" />';
			$echo_data .= '<label for="check_'.$k1.'">&nbsp;'.$d1.'&nbsp;</label>';
		}
		$echo_data .= '</div>';
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;
		
	}

	static function echoDownloadFunc($branch_cd,$target){	
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action=download';
		echo <<<EOT
			function fnExecDownload(element) {
				var tmp = new Array();  
				\$j("#sl_download_result input[type=checkbox]").each(function (){
					if ( \$j(this).is(":checked") ) {
						tmp.push( \$j(this).val() );
					}
				});
				
				\$j.ajax({
					type: "post",
					url:  "{$target_src}", 
					dataType : "json",
					data: {
						"type":"exec",
						"target":"{$target}",
						"cols":tmp.join(","),
						"branch_cd":{$branch_cd},
						"menu_func":"Download_Exec"
					},
	
					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
						}
						else {
							fnRemoveModalResult("#button_exec");							
							location.href = data.redirect_url;
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			});			
		}
	
	
EOT;
	}
	


	static function set_datepicker_date ($branch_cd = null,$target_year = null,$sp_dates = null){
	//  "20130101":{type:0, title:"元日"},
	// で、cssの定義と連動させて色を変える
		echo 'var holidays = {';
		$holiday = unserialize(get_option("salon_holiday"));
		$tmp_table = array();
		foreach ($holiday as $k1 => $d1 ) {
			$tmp_table[] = '"'.$d1[0].'":{type:0,title:"'.$d1[1].'"}';
		}
		echo implode(',',$tmp_table);
		echo '};';
		//特殊な営業日・休業日の設定
		$tmp_table2 = array();
		echo "\n";
		echo 'var sp_dates = {';
		if (! empty($branch_cd) ) {
	
/*			$tmp_datas = sl_getBranchData($branch_cd,'sp_dates');
			$sp_dates = unserialize($tmp_datas['sp_dates']);
*/		
			if (empty($target_year) ) {
				$target_year = date_i18n("Y");
			}
			if ($sp_dates && count($sp_dates[$target_year]) > 0) {
				foreach ($sp_dates[$target_year] as $k1 => $d1) {
					$tmp_table2[] = '"'.$k1.'":{type:'.$d1.',title:"'.($d1== Salon_Status::OPEN ?  __('on business',SL_DOMAIN) :  __('holiday',SL_DOMAIN)).'"}';
				}
			}
			echo implode(',',$tmp_table2);
		}
		echo '};';
		
	}

	static function set_datepickerDefault($is_maxToday = false){
		$range = 'minDate: new Date()';
		if ($is_maxToday) $range = 'maxDate: new Date()';
		echo 
			'$j.datepicker.setDefaults({
					closeText: "'.__('close',SL_DOMAIN).'",
					'.__('prevText: "&#x3C;"',SL_DOMAIN).',
					'.__('nextText: "&#x3E;"',SL_DOMAIN).',
					currentText: "'.__('today',SL_DOMAIN).'",
					monthNames: ['.__('"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"',SL_DOMAIN).'],
					monthNamesShort: ['.__('"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"',SL_DOMAIN).'],
					dayNames: ['.__('"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"',SL_DOMAIN).'],
					dayNamesShort: ['.__('"Sun","Mon","Tue","Wed","Thu","Fri","Sat"',SL_DOMAIN).'],
					dayNamesMin: ['.__('"Sun","Mon","Tue","Wed","Thu","Fri","Sat"',SL_DOMAIN).'],
					weekHeader: "'.__('week',SL_DOMAIN).'",
					dateFormat: "'.__('mm/dd/yy',SL_DOMAIN).'",
					changeMonth: true,
					firstDay: 0,
					isRTL: false,
					showMonthAfterYear: true,
					showButtonPanel: true,
					'.__('yearSuffix:"" ',SL_DOMAIN).',
					'.$range.',
			});';			
		
	}

	static function set_datepicker ($tag_id,$branch_cd,$select_ok = false,$target_year = '',$closed_data = null){
		$tmp_status = Salon_Status::OPEN;
		if ($select_ok) $tmp_select = 'true';
		else $tmp_select = 'false';
		
		echo 
				'$j("#'.$tag_id.'").datepicker({
					beforeShowDay: function(day) {
					  var result = [true,"",""];
					  var holiday = holidays[$j.format.date(day, "yyyyMMdd")]
					  var sp_date = sp_dates[$j.format.date(day, "yyyyMMdd")]
					  if (sp_date) {
						if (sp_date.type == '.$tmp_status.' ) {
							result =  [true, "date-holiday3", sp_date.title];
						}
						else {
							result =  ['.$tmp_select.', "date-holiday2", sp_date.title];
						}
					  } 
					  else {
						switch (day.getDay()) {';
		if (empty($closed_data)) {
			echo 'case 0: result = [true,"date-sunday",""]; break; ';
			echo 'case 6: result = [true,"date-saturday",""]; break; ';
		}
		else {
			$datas = explode(",",$closed_data);
			foreach ($datas as $d1 ) {
				if (!empty($d1))
					echo 'case '.$d1.': result = ['.$tmp_select.', "date-holiday1","'.__('holiday',SL_DOMAIN).'"];  break; ';
			}
			
			
			if (in_array(0,$datas) == false ) echo 'case 0: result = [true,"date-sunday",""]; break; ';
			if (in_array(6,$datas) == false ) echo 'case 6: result = [true,"date-satureday",""]; break; ';
		}
		echo <<<EOT2
						default:
							result = [true, "",""];
							break;
						}
					  }
					  if (holiday) {
						result[2] =  result[2] + holiday.title;
					  } 
					  return result;
					}
				});
EOT2;
	}
	
	
	static function echoSetHoliday($branch_datas,$target_year,$is_block = true) {
			$is_todayHoliday = false;
			if (!empty($branch_datas['closed']) || $branch_datas['closed']==0 ) {
				$set_days = '['.$branch_datas['closed'].']';
				$set_html = __('Holiday',SL_DOMAIN);
				$block = 'dhx_time_block';
				if (!$is_block ) $block = '';
				echo <<<EOT
				var options = {
					days:{$set_days},
					zones:"fullday",
					type: "{$block}", 
					css: "holiday",
					html: "{$set_html}"
				};
				scheduler.addMarkedTimespan(options);
EOT;
				if (strpos($branch_datas['closed'],date('w') ) !== false ) $is_todayHoliday = true;
				//特殊な日の設定（定休日だけど営業するor営業日だけど休むなど）
				$sp_dates = unserialize($branch_datas['sp_dates']);
				$on_business_array = array();
				$holiday_array = array();
				$today_check_array = array();
				for ($i=0;$i<2;$i++) {	//指定年と＋１(年末のことを考えて）
					$tmp_year = intval($target_year) + $i;
					if ($sp_dates && !empty($sp_dates[$tmp_year])) {
						foreach ($sp_dates[$tmp_year] as $k1 => $d1) {
							$today_check_array[$k1] = $d1;
							$tmp = 'new Date('.$tmp_year.','.(string)(intval(substr($k1,4,2))-1).','.(string)(intval(substr($k1,6,2))+0).')';
							if ($d1== Salon_Status::OPEN ) {
								$on_business_array[] = $tmp;
								
							}
							elseif ($d1== Salon_Status::CLOSE ) {
								$holiday_array[] = $tmp;
							}
						}
					}
				}
				echo 'var on_business = [ '.implode(',',$on_business_array).' ];';
				echo 'var holidays = [ '.implode(',',$holiday_array).' ];';
				
				$set_on_business = __('On business',SL_DOMAIN);
				echo <<<EOT2
				for (var i=0; i<on_business.length; i++) {
					var date = on_business[i];
					var options = {
						start_date: date,
						end_date: scheduler.date.add(date, 1, "day"),
						type: "", 
						css: "on_business",
						html: "{$set_on_business}"
					};
					scheduler.addMarkedTimespan(options);
				}
	
				for (var i=0; i<holidays.length; i++) {
					var date = holidays[i];
					var options = {
						start_date: date,
						end_date: scheduler.date.add(date, 1, "day"),
						type: "{$block}", 
						css: "holiday",
						html: "{$set_html}"
					};
					scheduler.addMarkedTimespan(options);
				}
	
EOT2;

				if (isset($today_check_array[date('Ymd')]) ) {
					if ($today_check_array[date('Ymd')] == Salon_Status::OPEN ) $is_todayHoliday = false;
					else $is_todayHoliday = true;
				}
			}
			return $is_todayHoliday;
		
	}


	static function echoCheckClinet($check_patern) {
		$default_margin = self::INPUT_BOTTOM_MARGIN;
	//reqOther_tel_ZZZZとあったらidがtelとZZZZに入力があるか確認する 
	//[TODO]sl_checkboxは重いか？
	//[TODO]済 exceptはとりあえず１つだけ→カンマ区切り
		echo <<<EOT
			function checkItem(target,except ) {
				var is_error = false;
				var tmp_excepts = Array();
				if (except) {
					if (except.indexOf(",") > -1) {
						var tmp_excepts = except.split(",");
					}
					else {
						tmp_excepts.push(except);
					}
				}
				\$j("#"+target).find("input[type=text],textarea,select,.sl_checkbox").each(function(){
					var id = \$j(this).attr("id");
					if (except) {
						for(var i=0;i<tmp_excepts.length;i++){
							if ( id == tmp_excepts[i] ) return;
						}
						
					}
					var item_errors = Array();
					var cl = \$j(this).attr("class");
					if (cl) {
						var val = \$j(this).val();
EOT;
		//必須チェックがなくて、その他のチェックがある場合を考慮して			
		$check_contents = self::setCheckContents();
		$key = array_search('chk_required',$check_patern);
		if ($key !== false) {
			echo $check_contents['chk_required'];
			unset($check_patern[$key]);
		}
		$key = array_search('reqOther',$check_patern) ;
		if ($key !== false) {
			echo $check_contents['reqOther'];
			unset($check_patern[$key]);
		}
		$key = array_search('reqCheck',$check_patern);
		if ($key !== false) {
			echo $check_contents['reqCheckbox'];
			unset($check_patern[$key]);
		}
		if ( count($check_patern) > 0 ) {
			echo 'if (( item_errors.length == 0 ) && (val != "" ) ){';
			foreach ($check_patern as $d1) {
				echo $check_contents[$d1];
			}
			echo '}';
		}
	//エラーの表示部

		echo <<<EOT2
					}
					
					\$j(this).removeAttr("style");
					var label = \$j(this).prev().children(".small");
					label.removeClass("sl_coler_not_complete");
					label.removeAttr("style");
					if (  item_errors.length > 0 ) {
						label.text(item_errors.join(" "));
						label.addClass("error small");
						is_error = true;
						var label_tag = \$j(this).prev();
						var diff = label_tag.outerHeight(true) - \$j(this).outerHeight(true);
						if (diff > 0 ) {
							diff += {$default_margin}+5;
							\$j(this).attr("style","margin-bottom: "+diff+"px;");
							label.attr("style","text-align:left;");
						}
					}
					else {
						label.text(check_items[id]["tips"]);
						label.removeClass("error");
						var label_tag = \$j(this).prev();
						var diff = label_tag.outerHeight(true) - \$j(this).outerHeight(true);
						if (diff > 0 ) {
							diff += {$default_margin}+5;
							\$j(this).attr("style","margin-bottom: "+diff+"px;");
							label.attr("style","text-align:left;");
						}
					}
				});
				if ( is_error ) return false;
				else return true;
			}
EOT2;
	
	}
	

	static function echo_customize_dhtmlx(){
		echo <<<EOT
			scheduler._pre_render_events = function(evs, hold) {
				var hb = this.xy.bar_height;
				var h_old = this._colsS.heights;
				var h = this._colsS.heights = [0, 0, 0, 0, 0, 0, 0];
				var data = this._els["dhx_cal_data"][0];
				if (!this._table_view)
					evs = this._pre_render_events_line(evs, hold); 
				else
					evs = this._pre_render_events_table(evs, hold);
			
				if (this._table_view) {
					if (hold)
						this._colsS.heights = h_old;
					else {
						var evl = data.firstChild;
						if (evl.rows) {
							for (var i = 0; i < evl.rows.length; i++) {
								h[i]++;
								if ((h[i]) * hb > this._colsS.height - 22) { 
									var before_heights = new Array();	//[hisa]
									var cells = evl.rows[i].cells;
									for (var j = 0; j < cells.length; j++) {
										before_heights[j] = cells[j].childNodes[1].style.height;	//[hisa]
										cells[j].childNodes[1].style.height = h[i] * hb + "px";
										if (cells[j].childNodes[1].childNodes[0]) 
											cells[j].childNodes[1].childNodes[0].style.height = h[i] * hb + "px";
									}
									//[hisa]from
									for(var x = i+1;x < evl.rows.length; x++) {
										cells_next = evl.rows[x].cells;
										for(var y = 0 ; y < cells_next.length; y++ ) {
											if (cells_next[y].childNodes[1].childNodes[0]) {
												var tmp_height = cells_next[y].childNodes[1].childNodes[0].style.top.replace("px","");
												var tmp_before_height = before_heights[y].replace("px","");
												cells_next[y].childNodes[1].childNodes[0].style.top = h[i] * hb + Number(tmp_height) - Number(tmp_before_height)  + "px";
											}
										}
									}
									//[hisa]to
									h[i] = (h[i - 1] || 0) + cells[0].offsetHeight;
								}
								h[i] = (h[i - 1] || 0) + evl.rows[i].cells[0].offsetHeight;
							}
							h.unshift(0);
							if (evl.parentNode.offsetHeight < evl.parentNode.scrollHeight && !evl._h_fix && scheduler.xy.scroll_width) {
								//we have v-scroll, decrease last day cell
								for (var i = 0; i < evl.rows.length; i++) {
									var cell = evl.rows[i].cells[6].childNodes[0];
									var w = cell.offsetWidth - scheduler.xy.scroll_width + "px";
									cell.style.width = w;
									cell.nextSibling.style.width = w;
								}
								evl._h_fix = true;
							}
						} else {
							if (!evs.length && this._els["dhx_multi_day"][0].style.visibility == "visible")
								h[0] = -1;
							if (evs.length || h[0] == -1) {
								//shift days to have space for multiday events
								var childs = evl.parentNode.childNodes;
			
								// +1 so multiday events would have 2px from top and 2px from bottom by default
								var full_multi_day_height = (h[0] + 1) * hb + 1;
			
								var used_multi_day_height = full_multi_day_height;
								var used_multi_day_height_css = full_multi_day_height + "px";
								if (this.config.multi_day_height_limit) {
									used_multi_day_height = Math.min(full_multi_day_height, this.config.multi_day_height_limit) ;
									used_multi_day_height_css = used_multi_day_height + "px";
								}
			
								data.style.top = (this._els["dhx_cal_navline"][0].offsetHeight + this._els["dhx_cal_header"][0].offsetHeight + used_multi_day_height ) + "px";
								data.style.height = (this._obj.offsetHeight - parseInt(data.style.top, 10) - (this.xy.margin_top || 0)) + "px";
			
								var multi_day_section = this._els["dhx_multi_day"][0];
								multi_day_section.style.height = used_multi_day_height_css;
								multi_day_section.style.visibility = (h[0] == -1 ? "hidden" : "visible");
			
								// icon
								var multi_day_icon = this._els["dhx_multi_day"][1];
								multi_day_icon.style.height = used_multi_day_height_css;
								multi_day_icon.style.visibility = (h[0] == -1 ? "hidden" : "visible");
								multi_day_icon.className = h[0] ? "dhx_multi_day_icon" : "dhx_multi_day_icon_small";
								this._dy_shift = (h[0] + 1) * hb;
								h[0] = 0;
			
								if (used_multi_day_height != full_multi_day_height) {
									data.style.top = (parseInt(data.style.top) + 2) + "px";
			
									multi_day_section.style.overflowY = "auto";
									multi_day_section.style.width = (parseInt(multi_day_section.style.width) - 2) + "px";
			
									multi_day_icon.style.position = "fixed";
									multi_day_icon.style.top = "";
									multi_day_icon.style.left = "";
								}
							}
						}
					}
				}
			
				return evs;
			};
			scheduler.showCover=function(box){
				if (box){
					box.style.display="block";
			
					var scroll_top = window.pageYOffset||document.body.scrollTop||document.documentElement.scrollTop;
					var scroll_left = window.pageXOffset||document.body.scrollLeft||document.documentElement.scrollLeft;
			
					var view_height = window.innerHeight||document.documentElement.clientHeight;
			
					if(scroll_top) // if vertical scroll on window
						box.style.top=Math.round(scroll_top+Math.max((view_height-box.offsetHeight)/2, 0))+"px";
					else // vertical scroll on body
						box.style.top=Math.round(Math.max(((view_height-box.offsetHeight)/2), 0) + 9)+"px"; // +9 for compatibility with auto tests
			
					// not quite accurate but used for compatibility reasons
					var left_pos = 0;	//[hisa]
					if(document.documentElement.scrollWidth > document.body.offsetWidth) // if horizontal scroll on the window
			//			box.style.left=Math.round(scroll_left+(document.body.offsetWidth-box.offsetWidth)/2)+"px";
						left_pos=Math.round(scroll_left+(document.body.offsetWidth-box.offsetWidth)/2);
					else // horizontal scroll on the body
			//			box.style.left=Math.round((document.body.offsetWidth-box.offsetWidth)/2)+"px";
						left_pos=Math.round((document.body.offsetWidth-box.offsetWidth)/2);
					if (left_pos < 0 ) left_pos = 0; 
					box.style.left= left_pos + "px";
				}
				this.show_cover();
			};
EOT;
	}

	static function abnormal_error($msg) {
		echo '<div id="sl_error_display"><h2>'.$msg.'</h2></div>';
	}


	static function echoColumnCheck($check_patern) {

		echo <<<EOT
			function checkColumnItem(td) {		
				var input = \$j(td).find("input");
				var val = input.val();
				var cl = \$j(td).attr("class");
				var item_errors = Array();
EOT;

				$check_contents = self::setCheckContents('td');
				$key = array_search('chk_required',$check_patern);
				if ($key !== false) {
					echo $check_contents['chk_required'];
					unset($check_patern[$key]);
				}
				if ( count($check_patern) > 0 ) {
					echo 'if (( item_errors.length == 0 ) && (val != "" ) ){';
					foreach ($check_patern as $d1) {
						echo $check_contents[$d1];
					}
					echo '}';
				}

		echo <<<EOT2
				if ( item_errors.length > 0 ) {
					alert(item_errors.join(" "));
					return false;
				}

				return true;					
EOT2;
		echo '}';
	}




	static function setItemContents() {
		$item_contents = array();
		$item_contents['first_name'] =array('id' => 'first_name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax10')
		 ,'label' => __('first name',SL_DOMAIN)
		 ,'tips' => __('within 10 charactors',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable chk_required lenmax10'
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
		 
	
		$item_contents['last_name'] =array('id'=>'last_name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax10')
		 ,'label' => __('Last Name',SL_DOMAIN)
		 ,'tips' => __('within 10 charactors',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable chk_required lenmax10'
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
	
		$item_contents['branch_name'] =array('id'=>'name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax20')
		 ,'label' => __('Salon Name',SL_DOMAIN)
		 ,'tips' => __('within 10 charctors',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable chk_required lenmax20'
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
	
		$item_contents['item_name'] =array('id'=>'name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax30')
		 ,'label' => __('Menu Name',SL_DOMAIN)
		 ,'tips' => __('within 30 charctors',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable chk_required lenmax30'
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['position_name'] =array('id'=>'name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax30')
		 ,'label' => __('Position Name',SL_DOMAIN)
		 ,'tips' => __('within 30 charctors',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
	
		$item_contents['short_name'] =array('id'=>'short_name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax5')
		 ,'label' => __('Short Name',SL_DOMAIN)
		 ,'tips' => __('within 5 charctors',SL_DOMAIN));
	
		$item_contents['customer_name'] =array('id'=>'name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax30','chkSpace')
		 ,'label' => __('Name',SL_DOMAIN)
		 ,'tips' => __('space input between first-name and last-name',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable'
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['staff_name'] =array('id'=>'name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax30','chkSpace')
		 ,'label' => __('Name',SL_DOMAIN)
		 ,'tips' => __('space input between first-name and last-name',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable'
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
	
		$item_contents['branch_cd'] =array('id'=>'branch_cd'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Shop',SL_DOMAIN)
		 ,'tips' => __('select shop',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'false'
							,'search'=>'false'
							,'visible'=>'true' ));

		$item_contents['branch_name_table'] =array('id'=>'branch_name'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => ''
		 ,'tips' => ''
		 ,'table' => array(  'class'=>''
							,'width'=>''
							,'sort'=>'false'
							,'search'=>'true'
							,'visible'=>'false' ));
	
		$item_contents['position_cd'] =array('id'=>'position_cd'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Position',SL_DOMAIN)
		 ,'tips' => __('when unknown, choose staff',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'false'
							,'search'=>'false'
							,'visible'=>'true' ));


		$item_contents['position_name_table'] =array('id'=>'position_name'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => ''
		 ,'tips' => ''
		 ,'table' => array(  'class'=>''
							,'width'=>''
							,'sort'=>'false'
							,'search'=>'true'
							,'visible'=>'false' ));

		$item_contents['wp_role'] =array('id'=>'wp_role'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('wp_role',SL_DOMAIN)
		 ,'tips' => __('when unknown, choose author',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'false'
							,'search'=>'false'
							,'visible'=>'true' ));

		$item_contents['role'] =array('id'=>'role'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Role',SL_DOMAIN)
		 ,'tips' => __('check permit process',SL_DOMAIN));

		$item_contents['no_edit_remark'] =array('id'=>'remark'
		 ,'class' => array()
		 ,'check' => array( 'lenmax300')
		 ,'label' => __('Remark',SL_DOMAIN)
		 ,'tips' => __('within 300 charctors',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>''
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

	
		$item_contents['staff_cd'] =array('id'=>'staff_cd'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Representative',SL_DOMAIN)
		 ,'tips' => __('select please',SL_DOMAIN));
	
		$item_contents['zip'] =array('id'=>'zip'
		 ,'class' => array()
		 ,'check' => array( 'chkZip')
		 ,'label' => __('Zip',SL_DOMAIN)
		 ,'tips' => __('please XXXXX-XXXX format',SL_DOMAIN));
	
		$item_contents['address'] =array('id'=>'address'
		 ,'class' => array()
		 ,'check' => array( 'lenmax200')
		 ,'label' => __('Address',SL_DOMAIN)
		 ,'tips' => __('within 200 charctors',SL_DOMAIN));
	
		$item_contents['tel'] =array('id'=>'tel'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_mobile')
		 ,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('please XXXX-XXX-XXXX format',SL_DOMAIN));

		$item_contents['branch_tel'] =array('id'=>'tel'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','chkTel')
		 ,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('please XXXX-XXX-XXXX format',SL_DOMAIN));
	
		$item_contents['customer_tel'] =array('id'=>'tel'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_mobile_mail')
		 ,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('please XXXX-XXX-XXXX format',SL_DOMAIN));
	
		$item_contents['mobile'] =array('id'=>'mobile'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_tel')
		 ,'label' => __('Mobile',SL_DOMAIN)
		 ,'tips' => __('please XXXX-XXX-XXXX format',SL_DOMAIN));

		$item_contents['customer_mobile'] =array('id'=>'mobile'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_tel_mail')
		 ,'label' => __('Mobile',SL_DOMAIN)
		 ,'tips' => __('please XXXX-XXX-XXXX format',SL_DOMAIN));
	
		$item_contents['mail'] =array('id'=>'mail'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','chkMail')
		 ,'label' => __('Mail',SL_DOMAIN)
		 ,'tips' => __('please XXX@XXX.XXX format',SL_DOMAIN));

		$item_contents['booking_tel'] =array('id'=>'tel'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_mail')
		 ,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('please XXXX-XXX-XXXX format',SL_DOMAIN));

		$item_contents['mail_norequired'] =array('id'=>'mail'
		 ,'class' => array()
		 ,'check' => array( 'chkMail','reqOther_tel')
		 ,'label' => __('Mail',SL_DOMAIN)
		 ,'tips' => __('please XXX@XXX.XXX format',SL_DOMAIN));

	
		$item_contents['customer_mail'] =array('id'=>'mail'
		 ,'class' => array()
		 ,'check' => array('chkMail','reqOther_tel_mobile')
		 ,'label' => __('Mail',SL_DOMAIN)
		 ,'tips' => __('when no mail , nothing input',SL_DOMAIN));
	
		$item_contents['reserved_tel'] =array('id'=>'tel'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_mail')
		 ,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('please XXXX-XXX-XXXX format',SL_DOMAIN));

		$item_contents['reserved_mail'] =array('id'=>'mail'
		 ,'class' => array()
		 ,'check' => array( 'chkMail','reqOther_tel')
		 ,'label' => __('Mail',SL_DOMAIN)
		 ,'tips' => __('when no mail , nothing input',SL_DOMAIN));


		$item_contents['user_login'] =array('id'=>'user_login'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('User Login',SL_DOMAIN)
		 ,'tips' => __('when possible ,same mail',SL_DOMAIN));
	
		$item_contents['remark'] =array('id'=>'remark'
		 ,'class' => array()
		 ,'check' => array( 'lenmax300')
		 ,'label' => __('Remark',SL_DOMAIN)
		 ,'tips' => __('within 300 charctors',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable lenmax300'
							,'width'=>''
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
	
	
		$item_contents['target_day'] =array('id'=>'target_day'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','chkDate')
		 ,'label' => __('Date',SL_DOMAIN)
		 ,'tips' => __('please MM/DD/YYYY or MMDDYYYY format',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
	
		$item_contents['employed_day'] =array('id'=>'employed_day'
		 ,'class' => array()
		 ,'check' => array( 'chkDate')
		 ,'label' => __('Entering Day',SL_DOMAIN)
		 ,'tips' => __('please MM/DD/YYYY or MMDDYYYY format',SL_DOMAIN));
	
		$item_contents['leaved_day'] =array('id'=>'leaved_day'
		 ,'class' => array()
		 ,'check' => array( 'chkDate')
		 ,'label' => __('Leaving Day',SL_DOMAIN)
		 ,'tips' => __('please MM/DD/YYYY or MMDDYYYY format',SL_DOMAIN));
	
		$item_contents['open_time'] =array('id'=>'open_time'
		 ,'class'=>array('sl_short_width')
		 ,'check'=>array('chk_required','chkTime')
		 ,'label'=> __('Open Time',SL_DOMAIN)
		 ,'tips' => __('please HH:MM or HHMM format',SL_DOMAIN));
	
		$item_contents['close_time'] =array('id'=>'close_time'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','chkTime')
		 ,'label' => __('Close Time',SL_DOMAIN)
		 ,'tips' => __('please HH:MM or HHMM format',SL_DOMAIN));
	
		$item_contents['time_step'] =array('id'=>'time_step'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num','range1_60')
		 ,'label' => __('Unit of Time (minutes)',SL_DOMAIN)
		 ,'tips' => __('select please',SL_DOMAIN));
	
		$item_contents['minute'] =array('id'=>'minute'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('Required Time(min)',SL_DOMAIN)
		 ,'tips' => __('select please',SL_DOMAIN));
		 //{TODO]カンマを許す？
		$item_contents['price'] =array('id'=>'price'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('Price',SL_DOMAIN)
		 ,'tips' => __('please enter numeric',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable chk_required num'
							,'width'=>self::SHORT_WIDTH
							,'sort'=>'false'
							,'search'=>'false'
							,'visible'=>'true' ));
		 
		 
		$item_contents['sp_date'] =array('id'=>'sp_date'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','chkDate')
		 ,'label' => __('Irregular Open/Closing day',SL_DOMAIN)
		 ,'tips' => __('holiday but does bussiness',SL_DOMAIN));
	
		$item_contents['closed_day_check'] =array('id'=>'closed_day_check'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Regular Closing Day',SL_DOMAIN)
		 ,'tips' => __('check please',SL_DOMAIN));
	
		$item_contents['item_cds'] =array('id'=>'item_cds'
		 ,'class' => array()
		 ,'check' => array( 'reqCheckbox')
		 ,'label' => __('Menu',SL_DOMAIN)
		 ,'tips' => __('please select',SL_DOMAIN));
	
		$item_contents['button_upload'] =array('id'=>'button_upload'
		 ,'class'	=>array()
		 ,'check' => array( )
		 ,'label' => __('Photo',SL_DOMAIN)
		 ,'tips' => __('plese select [From Computer] or [Media Libraly].and click [Insert into Post].forget not to click the button either [add] or [update]',SL_DOMAIN));
	
		$item_contents['config_branch'] =array('id'=>'config_only_branch'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Number of the Shops',SL_DOMAIN)
		 ,'tips' => __('plural shops ,check here',SL_DOMAIN));
	
		$item_contents['config_user_login'] =array('id'=>'config_is_user_login'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Approval of the Login by the Clients',SL_DOMAIN)
		 ,'tips' => __('if customer login possible ,check here',SL_DOMAIN));

		$item_contents['config_log'] =array('id'=>'config_is_log_need'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Opration Log Setting',SL_DOMAIN)
		 ,'tips' => __('if write operation  to log ,check here',SL_DOMAIN));

		$item_contents['config_delete_record'] =array('id'=>'config_is_delete_record'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Automatic Deletion',SL_DOMAIN)
		 ,'tips' => __('if mask the personal information ,check here',SL_DOMAIN));

		$item_contents['config_delete_record_period'] =array('id'=>'delete_record_period'
		 ,'class'	=>array('sl_short_width')
		 ,'check' => array('num')
		 ,'label' => __('Months when to delete ',SL_DOMAIN)
		 ,'tips' => __('enter the designated months ',SL_DOMAIN));

		$item_contents['config_show_detail_msg'] =array('id'=>'config_is_show_detail_msg'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Display Details',SL_DOMAIN)
		 ,'tips' => __('when debgug, check here',SL_DOMAIN));

		$item_contents['regist_customer'] =array('id'=>'regist_customer'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Registered as a Member',SL_DOMAIN)
		 ,'tips' => __('when join as a member, check here',SL_DOMAIN));

		$item_contents['send_mail_text'] =array('id'=>'send_mail_text'
		 ,'class' => array()
		 ,'check' => array( 'lenmax200')
		 ,'label' => __('The Content of the Mail to Confirming Notice to the Client',SL_DOMAIN)
		 ,'tips' => __('within 200 charctors. {X-TO_NAME} in the content replace customer name',SL_DOMAIN));

		$item_contents['regist_mail_text'] =array('id'=>'regist_mail_text'
		 ,'class' => array()
		 ,'check' => array( 'lenmax200')
		 ,'label' => __('The Content of the Mail to respond to the Client newly registered as a Member',SL_DOMAIN)
		 ,'tips' => __('within 200 charctors.  {X-TO_NAME} in the content replace customer name',SL_DOMAIN));

		$item_contents['config_staff_holiday_set'] =array('id'=>'config_staff_holiday_normal'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Staff Holiday Settings',SL_DOMAIN)
		 ,'tips' => __('you may select “unable to enter other than when attendant”if you could register your attendance and the absence correctly in advance',SL_DOMAIN));

		$item_contents['config_name_order_set'] =array('id'=>'config_name_order_japan'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Sequence of Sur Name and Given Name',SL_DOMAIN)
		 ,'tips' => __('please select Sur Name First or Given Name first',SL_DOMAIN));

		$item_contents['config_no_prefernce'] =array('id'=>'config_is_no_preference'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('No Designation of Staff',SL_DOMAIN)
		 ,'tips' => __('if allow the reservation without nomination of a certain staff ,check here',SL_DOMAIN));

		$item_contents['maintenance_include_staff'] =array('id'=>'config_maintenance_include_staff'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Maintenance staff member include staff',SL_DOMAIN)
		 ,'tips' => __('if maintenance staff member display front form  ,check here',SL_DOMAIN));



		$item_contents['booking_user_login'] =array('id'=>'login_username'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('User Login',SL_DOMAIN)
		 ,'tips' => __('plese enter your loginid',SL_DOMAIN));

		$item_contents['booking_user_password'] =array('id'=>'login_password'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('Password',SL_DOMAIN)
		 ,'tips' => __('plese enter your password',SL_DOMAIN));

		$item_contents['time_from'] =array('id'=>'start_date'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('Start time',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['time_to'] =array('id'=>'end_date'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('End time',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['start_time'] =array('id'=>'start_time'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('Reserved time',SL_DOMAIN)
		 ,'tips' => __('please select',SL_DOMAIN));

		$item_contents['reserved_time'] =array('id'=>'reserved_time'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Date',SL_DOMAIN)
		 ,'tips' => ''
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

	
		$item_contents['in_time'] =array('id'=>'in_time'
		 ,'class'	=>array('sl_middle_width')
		 ,'check' => array('chk_required')
		 ,'label' => __('In Time',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['out_time'] =array('id'=>'out_time'
		 ,'class'	=>array('sl_middle_width')
		 ,'check' => array('chk_required')
		 ,'label' => __('Out Time',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['working_cds'] =array('id'=>'working_cds'
		 ,'class' => array()
		 ,'check' => array( 'reqCheckbox')
		 ,'label' => __('Working Status',SL_DOMAIN)
		 ,'tips' => '');
		 
		$item_contents['duplicate_cnt_staff'] =array('id'=>'duplicate_cnt'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('The Maximum Number of the Redundant Reservations',SL_DOMAIN)
		 ,'tips' => __('redundant reservations a staff can handle at the same timeframe',SL_DOMAIN));
		 
		$item_contents['duplicate_cnt'] =array('id'=>'duplicate_cnt'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('The Maximum Number of the Redundant Reservations',SL_DOMAIN)
		 ,'tips' => __('the maximum number of the reservations to coop simultaneously',SL_DOMAIN));


		$item_contents['before_day'] =array('id'=>'before_day'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('past X days',SL_DOMAIN)
		 ,'tips' => __('the range of th days extarcted from the data base of the actual performance',SL_DOMAIN));

		$item_contents['after_day'] =array('id'=>'after_day'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('X days ahead',SL_DOMAIN)
		 ,'tips' => __('the range of th days extarcted from the data base of the reservation',SL_DOMAIN));


		$item_contents['timeline_y_cnt'] =array('id'=>'timeline_y_cnt'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('Number of the staffs displayed',SL_DOMAIN)
		 ,'tips' => __('screen showing staffs for the reservation screen',SL_DOMAIN));

		$item_contents['logged_day'] =array('id'=>'logged_day'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Date',SL_DOMAIN)
		 ,'tips' => __('Logged date ',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['logged_time'] =array('id'=>'logged_time'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Time',SL_DOMAIN)
		 ,'tips' => __('Logged time',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['operation'] =array('id'=>'operation'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Operation',SL_DOMAIN)
		 ,'tips' => __('the operation to tables',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['logged_remark'] =array('id'=>'remark'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Remark',SL_DOMAIN)
		 ,'tips' => __('REMOTE_ADDR,REFERER',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
		//[20131110]Ver1.3.1
		$item_contents['display_sequence'] =array('id'=>'display_sequence'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Seq',SL_DOMAIN)
		 ,'tips' => ''
		 ,'table' => array(  'class'=>'salon_editable '
							,'width'=>'10px'
							,'sort'=>'false'
							,'search'=>'false'
							,'visible'=>'true' ));

		return $item_contents;	
	
		
	}
	
	static function serverCheck($items , &$msg) {
		
		if (wp_verify_nonce($_REQUEST['nonce'],session_id()) === false) {
			throw new Exception(Salon_Component::getMsg('E008',__function__.':'.__LINE__ ) );
		}
		if (count($items) == 0 ) return true;
		$item_contents = self::setItemContents();	
		$err_msg = array();
		foreach ($items as $d1) {
			$id = $item_contents[$d1]['id'];
			foreach ($item_contents[$d1]['check'] as $d2 ) {
				self::serverEachCheck($_POST[$id],trim($d2),$item_contents[$d1]['label'],$err_msg);

/*
				if (trim($d2) == 'chk_required') {
					if (empty($_POST[$id]) ) {
						$err_msg[] = Salon_Component::getMsg('E201',$item_contents[$d1]['label']);
						break ;
					}
				}
				if (trim($d2) == 'reqCheckbox') {
					if (empty($_POST[$id]) ) {
						$err_msg[] = Salon_Component::getMsg('E201',$item_contents[$d1]['label']);
						break ;
					}
				}
				else {
					if (empty($_POST[$id]) ) break;
					switch (trim($d2)) {
						case 'chkTime':
							if (preg_match('/^(?:\d{1,2}:\d{1,2})$|^(?:\d{4})$/', $_POST[$id], $matches) == 0 ) {
								$err_msg[] = Salon_Component::getMsg('E202',$item_contents[$d1]['label']);
							}
							break;
						case 'num':
							if (preg_match('/^\d*$/',$_POST[$id],$matches) == 0 ) {
								$err_msg[] = Salon_Component::getMsg('E203',$item_contents[$d1]['label']);
							}
							break;
						case 'hanaku':
							if (preg_match('/^[\s\w]+$/',$_POST[$id],$matches) == 0 ) {
								$err_msg[] = Salon_Component::getMsg('E204',$item_contents[$d1]['label']);
							}
							break;
						case 'chkZip':
//							if (preg_match('/^(?:\d{3}\-\d{4})$|^(?:\d{7})$/',$_POST[$id],$matches) == 0 ) {
							if (preg_match('/'.__('^\d{5}(?:[-\s]\d{4})?$',SL_DOMAIN).'/',$_POST[$id],$matches) == 0 ) {
								$err_msg[] = Salon_Component::getMsg('E205',$item_contents[$d1]['label']);
							}
							break;
						
						case 'chkTel':
							if (preg_match('/^[\d\-]{10,13}$/',$_POST[$id],$matches) == 0 ) {
								$err_msg[] = Salon_Component::getMsg('E206',$item_contents[$d1]['label']);
							}
							break;
						case 'chkMail':
//							if (preg_match('/^[^\@]+?@[\w\.\-]+\.[\w\.\-]+$/',$_POST[$id],$matches) == 0 ) {
							if (preg_match('/^[\w!#$%&\'*+\/=?^_{}\\|~-]+([\w!#$%&\'*+\/=?^_{}\\|~\.-]+)*@([\w][\w-]*\.)+[\w][\w-]*$/',$_POST[$id],$matches) == 0 ) {
								$err_msg[] = Salon_Component::getMsg('E207',$item_contents[$d1]['label']);
							}
							
							break;
						
						case 'chkDate':
							if ((preg_match('/^'.__('(?<month>\d{1,2})[\/\.\-](?<day>\d{1,2})[\/\.\-](?<year>\d{4})',SL_DOMAIN).'$/',$_POST[$id],$matches) == 0 ) && 
							   (preg_match('/^'.__('(?<month>\d{2})(?<day>\d{2})(?<year>\d{4})',SL_DOMAIN).'$/',$_POST[$id],$matches) == 0 ) ){
								$err_msg[] = Salon_Component::getMsg('E208',$item_contents[$d1]['label']);
							}
							if ( checkdate(+$matches['month'],+$matches['day'],+$matches['year']) == false ) {
								$err_msg[] = Salon_Component::getMsg('E209',$item_contents[$d1]['label']);
							}
							break;
						case 'chkSpace':
							$tmp = str_replace("　"," ",$_POST[$id]);
							if (preg_match('/^.+\s+.+$/', $tmp, $matches) == 0 ) {
								$err_msg[] = Salon_Component::getMsg('E210',$item_contents[$d1]['label']);
							}
							break;
						default:
							if (preg_match('/^lenmax(?<length>\d+)$/',trim($d2),$matches) === 1 ) {
							}
					}
				}
				
*/
			}
		}
		if (count($err_msg) > 0 ) {
			$msg = implode("\n",$err_msg);
			return false;
		}
		return true;
	}
	
	static function serverColumnCheck($target,$check_item,&$msg) {
		$item_contents = self::setItemContents();	
		$err_msg = array();
		foreach ($item_contents[trim($check_item)]['check'] as $d1 ) {
			self::serverEachCheck($target,$d1,$item_contents[trim($check_item)]['label'],$err_msg);
		}
		if (count($err_msg) > 0 ) {
			$msg = implode("\n",$err_msg);
			return false;
		}
		return true;
		
	}
	
	static function serverEachCheck($target,$check,$label,&$err_msg){
		if (trim($check) == 'chk_required') {
			if (empty($target)&& $target!=0) {
				$err_msg[] = Salon_Component::getMsg('E201',$label);
				return false;
			}
		}
		if (trim($check) == 'reqCheckbox' ) {
			if (empty($target) ) {
				$err_msg[] = Salon_Component::getMsg('E201',$label);
				return false;
			}
		}
		else {
			if (empty($target) ) return;
			switch (trim($check)) {
				case 'chkTime':
					if (preg_match('/^(?:\d{1,2}:\d{1,2})$|^(?:\d{4})$/', $target, $matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E202',$label);
					}
					break;
				case 'num':
					if (preg_match('/^\d*$/',$target,$matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E203',$label);
					}
					break;
//				case 'hankaku':
//					if (preg_match('/^[\s\w]+$/',$target,$matches) == 0 ) {
//						$err_msg[] = Salon_Component::getMsg('E204',$label);
//					}
//					break;
				case 'chkZip':
					if (preg_match('/'.__('^\d{5}(?:[-\s]\d{4})?$',SL_DOMAIN).'/',$target,$matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E205',$label);
					}
					break;
				
				case 'chkTel':
					if (preg_match('/^[\d\-]{10,13}$/',$target,$matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E206',$label);
					}
					break;
				case 'chkMail':
					if (preg_match('/^[\w!#$%&\'*+\/=?^_{}\\|~-]+([\w!#$%&\'*+\/=?^_{}\\|~\.-]+)*@([\w][\w-]*\.)+[\w][\w-]*$/',$target,$matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E207',$label);
					}
					
					break;
				
				case 'chkDate':
					if ((preg_match('/^'.__('(?<month>\d{1,2})[\/\.\-](?<day>\d{1,2})[\/\.\-](?<year>\d{4})',SL_DOMAIN).'$/',$target,$matches) == 0 ) && 
					   (preg_match('/^'.__('(?<month>\d{2})(?<day>\d{2})(?<year>\d{4})',SL_DOMAIN).'$/',$target,$matches) == 0 ) ){
						$err_msg[] = Salon_Component::getMsg('E208',$label);
					}
					elseif ( checkdate(+$matches['month'],+$matches['day'],+$matches['year']) == false ) {
						$err_msg[] = Salon_Component::getMsg('E209',$label);
					}
					break;
				case 'chkSpace':
					$tmp = str_replace("　"," ",$target);
					if (preg_match('/^.+\s+.+$/', $tmp, $matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E210',$label);
					}
					break;
				default:
					if (preg_match('/^lenmax(?<length>\d+)$/',trim($check),$matches) === 1 ) {
						if (mb_strlen($target) > +$matches['length'] ) {
							$err_msg[] = Salon_Component::getMsg('E211',array(+$matches['length'],$label));
						}
					}
			}
		}
	}
	
	static function setCheckContents($target = 'this') {
		//valにはチェックする値を、clには対象のクラスを全部格納しとく
		//当初はdetail部分だけに使用していたが、datatableでも使用するため拡張
		$check_contens = array();
		
	
		$check_contens['chk_required'] = '
						if ($j('.$target.').hasClass("chk_required") ) {
							if(val == ""){
								item_errors.push( "'.__('please enter',SL_DOMAIN).'");
							}
						}';
		$check_contens['num'] = '
							if ($j('.$target.').hasClass("num") ) {
								if( ! val.match(/^\d*$/)  ){
									item_errors.push( "'.__('please enter numeric',SL_DOMAIN).'");
								}
							}';
		//全角チェックはいらない
		$check_contens['zenkaku'] = '
							if ($j('.$target.').hasClass("zenkaku") ) {
								if( ! val.match(/^[^ -~｡-ﾟ]*$/)  ){
									item_errors.push( "'.__('please full width enter',SL_DOMAIN).'");
								}
							}';
//		$check_contens['hankaku'] = '
//							if ($j('.$target.').hasClass("hankaku") ) {
//								if( ! val.match(/^[ -~｡-ﾟ]*$/)  ){
//									item_errors.push( "'.__('please half width enter',SL_DOMAIN).'");
//								}
//							}';
		$check_contens['chkZip'] = '
							if ($j('.$target.').hasClass("chkZip") ) {
								if( ! val.match(/'.__('^\d{5}(?:[-\s]\d{4})?$',SL_DOMAIN).'/) ){
									item_errors.push( "'.__('please XXXXX-XXXX format',SL_DOMAIN).'");
								}
							}';
//								if( ! val.match(/^(?:\d{3}\-\d{4})$|^(?:\d{7})$/) ){
		//パターンで例外を考慮すると複雑になるので単純に
		//数字だけだと見えにくいのでハイフンを入れる
		$check_contens['chkTel'] = '
							if ($j('.$target.').hasClass("chkTel") ) {
								if( ! val.match(/^[\d\-]{10,13}$/) ){
									item_errors.push( "'.__('please XXXX-XXX-XXXX format',SL_DOMAIN).'");
								}
							}';
		$check_contens['chkMail'] = '
							if ($j('.$target.').hasClass("chkMail") ) {
								if( ! val.match(/^[\w!#$%&\'*+/=?^_{}\\|~-]+([\w!#$%&\'*+/=?^_{}\\|~\.-]+)*@([\w][\w-]*\.)+[\w][\w-]*$/)  ){
									item_errors.push( "'.__('please XXX@XXX.XXX format',SL_DOMAIN).'");
								}
							}';
//								if( ! val.match(/^[^\@]+?@[\w\.\-]+\.[\w\.\-]+$/)  ){
		$check_contens['chkTime'] = '
							if ($j('.$target.').hasClass("chkTime") ) {
								if( ! val.match(/^(?:[ ]?\d{1,2}:\d{1,2})$|^(?:\d{4})$/)  ){
									item_errors.push( "'.__('please HH:MM or HHMM format',SL_DOMAIN).'");
								}
							}';
	
		$check_contens['chkDate'] = '
							if ($j('.$target.').hasClass("chkDate") ) {
								if( val.match(/^'.__('(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{4})',SL_DOMAIN).'$/) || val.match(/^'.__('(\d{2})(\d{2})(\d{4})',SL_DOMAIN).'$/)  ){
									var y = '.__('RegExp.$3',SL_DOMAIN).';
									var m = '.__('RegExp.$1',SL_DOMAIN).';
									var d = '.__('RegExp.$2',SL_DOMAIN).';
									
									var di = new Date(y, m - 1, d);
									if (!(di.getFullYear() == y && di.getMonth() == m - 1 &&  di.getDate() == d) ) {
										item_errors.push( "'.__('this day not exist?',SL_DOMAIN).'");
									}  								
								}
								else {
									item_errors.push( "'.__('please MM/DD/YYYY or MMDDYYYY format',SL_DOMAIN).'");
								}
							}';
	
		$check_contens['lenmax'] = '
							if ( cl.indexOf("lenmax") != -1 ) {
								var length = cl.match(/lenmax(\d+)/) ? RegExp.$1 : Number.MAX_VALUE;
								if ( val.length > length  ) {
									item_errors.push(
										length.replace(/[A-Za-z0-9]/g, function(s) {
											return String.fromCharCode(s.charCodeAt(0) + 0xFEE0);
										})+"'.__('within charactors',SL_DOMAIN).'");
								}
							}';
		$check_contens['range'] = '
							if ( cl.indexOf("range") != -1 ) {
								cl.match(/range(\d+?)_(\d+)/);
								var minval = RegExp.$1;
								var maxval = RegExp.$2;
								if ( ( +val < +minval  ) || ( +val > +maxval ) ){
									item_errors.push(
										minval.replace(/[A-Za-z0-9]/g, function(s) {
											return String.fromCharCode(s.charCodeAt(0) + 0xFEE0);
										})+"'.__('greater than',SL_DOMAIN).'"+
										maxval.replace(/[A-Za-z0-9]/g, function(s) {
											return String.fromCharCode(s.charCodeAt(0) + 0xFEE0);
										})+"'.__('within',SL_DOMAIN).'");
								}
							}';
		$check_contens['reqOther'] = '
						if (cl.indexOf("reqOther_") != -1 ){
							if(val == ""){
								var target_item = cl.match(/reqOther_(.+)/) ? RegExp.$1 : "";
								var tmp_items = target_item.split("_");
								var is_found = false;
								for(var i = 0 ;i<tmp_items.length;i++) {
									if ($j("#"+tmp_items[i]).val() ) {
										is_found = true;
										break;
									}
								}
								if (! is_found) {
									var msg = Array();
									msg.push(check_items[id]["label"]);
									for(var i = 0 ;i<tmp_items.length;i++) {
										msg.push(check_items[tmp_items[i]]["label"]);
									}
									item_errors.push(msg.join(",")+"'.__('select one or more',SL_DOMAIN).'");
								}
							}
						}';
	
		$check_contens['reqCheckbox'] = '
						if (cl.indexOf("reqCheckbox") != -1 ){
							var is_checked = false;
//							$j('.$target.').children().filter("input[type=checkbox]").each(function(){
							$j('.$target.').find("input[type=checkbox]").each(function(){
								if ( $j('.$target.').is(":checked") ) {
									is_checked = true;
								}
								
							});
							if (is_checked == false ) {
								item_errors.push("'.__('please check',SL_DOMAIN).'");
							}
						}';

							
		$check_contens['chkSpace'] = '
						if ($j('.$target.').hasClass("chkSpace") ) {
							val = val.replace("　"," ");
							if( ! val.match(/^.+\s+.+$/) ){
								item_errors.push( "'.__('space input between first-name and last-name',SL_DOMAIN).'");
							}
						}';
							
		return $check_contens;
	
	}
	static function set_download_item () {
		if ($_POST['target'] == 'reservation' ) {
			$date = 'CONCAT (DATE_FORMAT(rs.time_from,"'.__('%m/%d/%Y',SL_DOMAIN).'")," ",DATE_FORMAT(rs.time_from, "%H:%i"),"-",DATE_FORMAT(rs.time_to, "%H:%i"))';			
			$download_items['date'] = array('id'=>'date','label'=>__('Date',SL_DOMAIN),'check'=>'checked','col'=>$date,'no_disp'=>true);
			$download_items['name'] = array('id'=>'name','label'=>__('Name',SL_DOMAIN),'check'=>'checked','col'=>'non_regist_name');
			$download_items['staff'] = array('id'=>'staff','label'=>__('Staff',SL_DOMAIN),'check'=>'checked','col'=>'st.user_login','user'=>'need');
			$download_items['item'] = array('id'=>'item','label'=>__('Menu',SL_DOMAIN),'check'=>'checked','col'=>'item_cds','item'=>'need');
			$download_items['remark'] = array('id'=>'remark','label'=>__('Wishes',SL_DOMAIN),'check'=>'','col'=>'rs.remark');
		}
		elseif ($_POST['target'] == 'sales' ) {
			$date = 'CONCAT (DATE_FORMAT(sa.time_from,"'.__('%m/%d/%Y',SL_DOMAIN).'")," ",DATE_FORMAT(sa.time_from, "%H:%i"),"-",DATE_FORMAT(sa.time_to, "%H:%i"))';			
			$download_items['date'] = array('id'=>'date','label'=>__('Date',SL_DOMAIN),'check'=>'checked','col'=>$date,'no_disp'=>true);
			$download_items['name'] = array('id'=>'name','label'=>__('Name',SL_DOMAIN),'check'=>'checked','col'=>'non_regist_name');
			$download_items['staff'] = array('id'=>'staff','label'=>__('Staff',SL_DOMAIN),'check'=>'checked','col'=>'st.user_login','user'=>'need');
			$download_items['item'] = array('id'=>'item','label'=>__('Menu',SL_DOMAIN),'check'=>'checked','col'=>'sa.item_cds','item'=>'need');
			$download_items['remark'] = array('id'=>'remark','label'=>__('Comment',SL_DOMAIN),'check'=>'','col'=>'sa.remark');
		}
		return $download_items;
	}
	static function calcTargetDate() {
		$zengo = 1;
		if ($_POST['target_date_zengo'] == 'before' ) $zengo = -1;
		$cnt = intval($_POST['target_date_num']);
		switch ($_POST['target_date_patern']) {
			case 'day':
				$target_day = Salon_Component::computeDate($cnt*$zengo);
				break;
			case 'week':
				$target_day = Salon_Component::computeDate($cnt*7*$zengo);
				break;
			case 'month':
				$target_day = Salon_Component::computeMonth($cnt*$zengo);
				break;
			case 'year':
				$target_day = Salon_Component::computeYear($cnt*$zengo);
				break;
		}
		return $target_day;
	}
	
	static function editYmdForHtml($in) {
		if (empty($in) ) return;
		$ymd = explode('-',$in);
		$edit = __('mm/dd/yyyy',SL_DOMAIN);
		$edit = str_replace('yyyy',$ymd[0],$edit);
		$edit = str_replace('mm',$ymd[1],$edit);
		$edit = str_replace('dd',$ymd[2],$edit);
		return $edit;;
	}
//[2013/11/10]Ver 1.3.1 from
	static function echoDataTableDisplaySequence($col) {
		$up_name = __('up',SL_DOMAIN);
		$down_name = __('down',SL_DOMAIN);
		//順番は引数で渡す。ここでは支店の後ろなので４
		//スタッフの場合、seqデータがNULLの場合（WPにのみ登録しているユーザ）の対処
		echo <<<EOT
			var element = \$j("td:eq({$col})", nRow);
			element.text("");
			if (aData.display_sequence) {
				var up_box = \$j("<input>")
						.attr("type","button")
						.attr("id","salon_up_btn_"+iDataIndex)
						.attr("name","salon_up_"+iDataIndex)
						.attr("value","{$up_name}")
						.attr("class","sl_button salon_button_updown")
						.click(function(event) {
							if (iDataIndex == 0 ) return;
							fnSeqUpdate(this.parentNode,iDataIndex,-1);
						});
				var down_box = \$j("<input>")
						.attr("type","button")
						.attr("id","salon_down_btn_"+iDataIndex)
						.attr("name","salon_down_"+iDataIndex)
						.attr("value","{$down_name}")
						.attr("class","sl_button salon_button_updown")
						.click(function(event) {
							if (iDataIndex == target.fnSettings().aoData.length-1) return;
							fnSeqUpdate(this.parentNode,iDataIndex,1);
						});
				element.append(up_box);
				element.append(down_box);
			}
			
EOT;
		
	}
	
	static function replaceResult($indata){
		return strtoupper(substr($indata,0,3));
	}

	public function echoDataTableSeqUpdateRow($target_name,$target_key_name,$is_multi_branch ) {
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action='.$target_name;
		if (empty($target_key_name) ) $target_key_name = $target_name;
		$menu_func = ucwords($target_name);
		$check_logic = '';
		if ($is_multi_branch) {
			$check_logic = "if (setData['aoData'][position[0]]['_aData']['branch_cd'] != setData['aoData'][position[0]+plus_minus]['_aData']['branch_cd']) return;";
		}

		echo <<<EOT
			function fnSeqUpdate(target_col,current_row,plus_minus) {
				var position = target.fnGetPosition( target_col );
				var setData = target.fnSettings();
				{$check_logic}
				var addIndex = position[0] + plus_minus;
				//スタッフの場合、歯抜けがあるので対処
				while(addIndex >= 0 && addIndex < target.fnSettings().aoData.length) {
					if (setData['aoData'][addIndex]['_aData']['display_sequence'] ) break;
					addIndex += plus_minus;
				}
				if (addIndex < 0 || addIndex ==  target.fnSettings().aoData.length) return;
				
				var source_index = setData['aoData'][position[0]]['nTr']['_DT_RowIndex'];
				var source_sequence = setData['aoData'][position[0]]['_aData']['display_sequence'];
				var target_index = setData['aoData'][addIndex]['nTr']['_DT_RowIndex'];
				var target_sequence = setData['aoData'][addIndex]['_aData']['display_sequence'];
				var source_key_id = setData['aoData'][position[0]]['_aData']['{$target_key_name}'];
				var target_key_id = setData['aoData'][addIndex]['_aData']['{$target_key_name}'];
				

				\$j.ajax({
					type: "post",
					url:  "{$target_src}",
					dataType : "json",
						data: {
							"{$target_key_name}":source_key_id + "," + target_key_id,
							"value":source_sequence + "," + target_sequence,
							"type":"updated",
							"nonce":"{$this->nonce}",
							"menu_func":"{$menu_func}_Seq_Edit"
						},
					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
							var save = setData['aoData'][position[0]];
							setData['aoData'][position[0]] = setData['aoData'][addIndex];
							setData['aoData'][position[0]]['nTr']['_DT_RowIndex'] = source_index;
							setData['aoData'][position[0]]['_aData']['display_sequence'] = source_sequence;
							setData['aoData'][addIndex] = save;
							setData['aoData'][addIndex]['nTr']['_DT_RowIndex'] = target_index;
							setData['aoData'][addIndex]['_aData']['display_sequence'] = target_sequence;
							target.fnDraw();
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						var parse_arrary = JSON.parse( XMLHttpRequest.responseText )
						alert (parse_arrary.message);
					}
					
				 });			
			}
EOT;
		
	}
//[2013/11/10]Ver 1.3.1 To
	

	
	
}