<div id="sl_content" role="main">

	<link rel="stylesheet" href="<?php echo SL_PLUGIN_URL.SALON_CSS_DIR; ?>dhtmlxscheduler.css" type="text/css" charset="utf-8">

	<script type="text/javascript" charset="utf-8">
		var $j = jQuery;
		var target_day_from = new Date();
		var target_day_to = new Date();
		var save_target_event = "";
		var save_item_cds = "";
		var all_duplicate_cnt;
		var staff_duplicates = new Array();
		var staff_items = new Array();
		var save_user_login = "";
		var save_mail = "";
		var save_tel = "";
		var save_name = "";
		var item_name = new Array();
		
		
		var is_collision_err = false;

		<?php parent::echoItemFromto($this->item_datas); ?>
		<?php parent::echoPromotionArray($this->promotion_datas); ?>
		
		$j(document).ready(function() {
			<?php parent::echoSearchCustomer($this->url); //検索画面 ?>	

			<?php $this->echo_customize_dhtmlx(); ?>
			scheduler.config.multi_day = true;
			scheduler.config.prevent_cache = true;
			scheduler.config.first_hour= <?php echo $this->first_hour; ?>;
			scheduler.config.last_hour= <?php echo $this->last_hour; ?>;
			scheduler.config.time_step = <?php echo $this->branch_datas['time_step']; ?>;
	<?php //予約の必須時間 ?>
			scheduler.config.event_duration = 60;
			scheduler.config.auto_end_date = true;
			scheduler.config.xml_date= "%Y-%m-%d %H:%i";
			scheduler.config.details_on_create=true;
			scheduler.config.details_on_dblclick=true;
	<?php //小さいメニューバーを出さない ?>
			scheduler.xy.menu_width = 0;
	
	<?php //現時点のどっど表示は出さない（位置がずれる） ?>
			scheduler.config.mark_now = false;
			scheduler.config.check_limits = false;
	<?php //locale_jaを使用しないように 
			parent::echoLocaleDef();
	?>
			
	<?php //休業日
			$is_todayholiday = parent::echoSetHoliday($this->branch_datas,$this->target_year);
			//スタッフの設定	
			$tmp_staff_index = array();
			$index = 1;
			if ($this->_is_noPreference() ) {
				echo 'var staffs=[{key:'.Salon_Default::NO_PREFERENCE.', label:"'.__('Anyone',SL_DOMAIN).'" },';
			}
			else {
				echo 'var staffs=[ ';
			}
			$comma = '';
			$reserve_possible_cnt = 0;
			foreach ($this->staff_datas as $k1 => $d1 ) {
				if ($this->config_datas['SALON_CONFIG_MAINTENANCE_INCLUDE_STAFF'] != Salon_Config::MAINTENANCE_NOT_INCLUDE_STAFF
					|| $d1['position_cd'] != Salon_Position::MAINTENANCE ) {
					//写真大きさを50pxにしとく。IEだと自動で補正してくれない？
	//				$tmp = preg_replace("/(width|height)(=\\\'\d+\\\')/","$1=\'50\'",$d1['photo']);
					$tmp = "";				
					if (!empty($d1['photo_result'][0]) ) {
						$tmp = "<a href='".$d1['photo_result'][0]['photo_path']."' rel='staff".$d1['staff_cd']."' ' class='lightbox' ><img src='".$d1['photo_result'][0]['photo_resize_path']."' alt='' width='150' height='150' class='alignnone size-thumbnail wp-image-186' /></a>";
					}
					if (!empty($d1['memo']) ) 
						//頭につける
						$tmp = "<p id='sl_intro".$d1['staff_cd']."' class='sl_intro_box'>". str_replace(array("\r\n","\r","\n"), '<br/>',htmlspecialchars($d1['memo'],ENT_QUOTES))."</p>".$tmp;
					$url = site_url();
					$url = substr($url,strpos($url,':')+1);
					$url = str_replace('/','\/',$url);
					if (is_ssl() ) {
						$tmp = preg_replace("/([hH][tT][tT][pP]:".$url.")/","https:".$url,$tmp);
					}
					else {
						$tmp = preg_replace("/([hH][tT][tT][pP][sS]:".$url.")/","http:".$url,$tmp);
					}
					echo $comma.'{key:'.$d1['staff_cd'].', label:"<div class=\'sl_staff_name\' >'.htmlspecialchars($d1['name'],ENT_QUOTES).$tmp.'</div>" }';
					$tmp_staff_index[$d1['staff_cd']] = $index;
					$index++;
					$comma = ',';
				}
			}
			echo '];';
			$reserve_possible_cnt = 0;
			foreach ($this->staff_datas as $k1 => $d1 ) {
				echo 'staff_duplicates['.$d1['staff_cd'].'] = '.$d1['duplicate_cnt'].';';
				echo 'staff_items['.$d1['staff_cd'].'] = "'.$d1['in_items'].'";';
				$reserve_possible_cnt += 1+$d1['duplicate_cnt'];
				
			}
	
	
			$timeline_array = array();
			foreach ($this->working_datas as $k1 => $d1 ) {
				$tmp = (string)(intval(substr($k1,0,4))).','.(string)(intval(substr($k1,4,2))-1).','.(string)(intval(substr($k1,6,2))+0);
				foreach ($d1 as $k2 => $d2 ) {
					$start_time = ','.(string)(intval(substr($d2['in_time'],8,2))).','.(string)(intval(substr($d2['in_time'],10,2)));
					$end_time = ','.(string)(intval(substr($d2['out_time'],8,2))).','.(string)(intval(substr($d2['out_time'],10,2)));
					$tmp_timeline = '{ "start_date":new Date('.$tmp.$start_time.'),"end_date":new Date('.$tmp.$end_time.'),"staff_cd":"'.$d2['staff_cd'].'"}';
					$timeline_array[] = $tmp_timeline;
				}
			}
			foreach ($this->item_datas as $k1 => $d1 ) {
				echo 'item_name['.$d1['item_cd'].']= "'.$d1['name'].'";';
			}


			if ($this->_is_staffSetNormal() ) {
				$tmp_css = 'holiday';
				$tmp_type = 'dhx_time_block';
				$tmp_html = __('Holiday',SL_DOMAIN);
			}
			else {
				$tmp_css = 'on_business';
				$tmp_type = '';
				$tmp_html = __('Bookable',SL_DOMAIN);
			}
				
					
			echo 'var staff_holidays = [ '.implode(',',$timeline_array).' ];';
			echo <<<EOT3
				for (var i=0; i<staff_holidays.length; i++) {
					var options = {
						start_date: staff_holidays[i].start_date,
						end_date: staff_holidays[i].end_date,
						type: "{$tmp_type}", 
						css: "{$tmp_css}",
						sections: { timeline:[staff_holidays[i].staff_cd] },
						html: "{$tmp_html}"
					};
					scheduler.addMarkedTimespan(options);
				}
EOT3;
					
			echo sprintf('all_duplicate_cnt = %d;',$reserve_possible_cnt + $this->branch_datas['duplicate_cnt']);
	?>
	
			var durations = {
				day: 24 * 60 * 60 * 1000,
				hour: 60 * 60 * 1000,
				minute: 60 * 1000
			};

			var get_formatted_duration = function(start, end) {
				var diff = end - start;
				var days = Math.floor(diff / durations.day);
				diff -= days * durations.day;
				var hours = Math.floor(diff / durations.hour);
				diff -= hours * durations.hour;
				var minutes = Math.floor(diff / durations.minute);
				var results = [];
				if (days) results.push(days + " days");
				if (hours) results.push(hours + " hours");
				if (minutes) results.push(minutes + " minutes");
				return results.join(", ");
			};
			var resize_date_format = scheduler.date.date_to_str(scheduler.config.hour_date);
			scheduler.templates.event_bar_text = function(start, end, event) {
				if (event.edit_flg == <?php echo Salon_Edit::OK; ?> )  {
					var state = scheduler.getState();
					if (state.drag_id == event.id) {
						return resize_date_format(start) + " - " + resize_date_format(end) + " (" + get_formatted_duration(start, end) + ")";
					}
				}
				return htmlspecialchars(event.text); // default
			};


	<?php //担当者画面のタブ ?>
			scheduler.locale.labels.timeline_tab = "<?php _e('Staff',SL_DOMAIN); ?>";
	<?php //section_autoheightはスタッフの人数が多い場合はfalseにする 
		  //height/dx(10人)より小さい場合はsection_autoheightをtrueにする
		  //calculate_dayはminuteだと日単位で移動しないのでカスタマイズ ?>
			scheduler.createTimelineView({
					section_autoheight: false,
					name: "timeline",
					x_unit: "minute",
					x_date: "%H",
					x_step: 60,
					x_size: <?php echo $this->last_hour - $this->first_hour; ?>,
					x_start: <?php echo $this->first_hour; ?>,
					x_length:24,
					y_unit: staffs,
					y_property:"staff_cd",
					folder_events_available: true,
					dx:50,
					dy:<?php echo self::Y_PIX/$this->config_datas['SALON_CONFIG_TIMELINE_Y_CNT']; ?>,
					render:"bar" ,
					event_dy: "full"
			});
				

	
			scheduler.init('scheduler_here',new Date("<?php echo date_i18n('Y/m/d'); ?>"),"<?php $this->_echoLoadTab(); ?>");
			scheduler.templates.event_text=function(start,end,event){
				var title_name = htmlspecialchars(event.name);
				if ((event.edit_flg == <?php echo Salon_Edit::OK; ?> ) && (title_name != '')) {
							title_name = "<?php _e('Mr/Ms %s',SL_DOMAIN); ?>".replace("%s",title_name);
				}
				return "<b>"+title_name+"</b>";
			}
			scheduler.load("<?php echo $this->url; ?>/wp-admin/admin-ajax.php?action=slbooking&menu_func=Booking_Get_Event&branch_cd=<?php echo $this->branch_datas['branch_cd']; ?>",function() {
				$j(".lightbox").colorbox();
			});
			var dp = new dataProcessor("<?php echo $this->url; ?>/wp-admin/admin-ajax.php?action=slbooking&menu_func=Booking_Edit");
			dp.init(scheduler);
			dp.defineAction("error",function(response){	
				if (response.getAttribute('sid') )	{
					var id = response.getAttribute('sid') ;
					if (response.getAttribute('func') == "inserted" ) 	scheduler.deleteEvent(id);
					else {
						if (save_target_event ) {
							save_target_event._dhx_changed = false;
							scheduler._lame_copy(scheduler._events[id],save_target_event);
							scheduler.updateEvent(id); 
						}
					}
				}
				alert(response.getAttribute("message"));
				return false;
			})
			
			dp.setTransactionMode("POST",false);
			dp.attachEvent("onBeforeUpdate",function(id,status, data){
				data.branch_cd = <?php echo $this->branch_datas['branch_cd']; ?>;
				return true;
			})

			dp.attachEvent("onAfterUpdate",function(sid,action,tid,xml_node){
				if (action == "invalid" ) {
					if (save_target_event ) {
						save_target_event._dhx_changed = false;
						scheduler._lame_copy(scheduler._events[sid],save_target_event);
						scheduler.updateEvent(save_target_event.id); 
					}
				}
				else if (action != "invalid" && action != "deleted") {
					scheduler._events[tid].type = '';
					scheduler._events[tid].edit_flg = xml_node.getAttribute("edit_flg");
					scheduler._events[tid].name = xml_node.getAttribute("name");
					scheduler._events[tid].text = _edit_text_name(xml_node.getAttribute("name"));
					scheduler._events[tid].p2 = xml_node.getAttribute("p2");
					var setAfterDate = scheduler.date.str_to_date(scheduler.config.xml_date,scheduler.config.server_utc);
					scheduler._events[tid].end_date = setAfterDate(xml_node.getAttribute("end_date"));
					if (xml_node.getAttribute("alert_msg") ) {
						alert(xml_node.getAttribute("alert_msg"));
					}

				}
				return true;
			})

			scheduler.templates.event_class=function(start,end,event){
				if (event.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) return "user_temporary"; 
				if (event.edit_flg == <?php  echo Salon_Edit::NG; ?> ) return "user_no_edit"; 
			}

			scheduler.attachEvent("onEventCreated",function(id){
				var ev = this.getEvent(id);
				ev.edit_flg = <?php  echo Salon_Edit::OK; ?>;
					<?php if ( is_user_logged_in() ) : ?>
						<?php	
						if ($this->isSalonAdmin() ) {
							$new_name = '';
							$new_mail = '';
							$new_tel = '';
							$user_login = '';
						}
						else {
							$new_name = $this->user_inf['user_name'];
							$new_mail = $this->user_inf['user_email'];
							$new_tel = $this->user_inf['tel'];
							$user_login = $this->user_inf['user_login'];
						}
						?>
						ev.name = '<?php echo $new_name; ?>';
						ev.mail = '<?php echo $new_mail; ?>';
						ev.tel = '<?php echo $new_tel; ?>';
						ev.status = <?php echo Salon_Reservation_Status::INIT; ?>;
						ev.user_login = '<?php echo $user_login; ?>';
					<?php else : ?>
						ev.name = '';
						ev.mail = '';
						ev.tel = '';
						ev.status = <?php echo Salon_Reservation_Status::TEMPORARY; ?>;
						ev.user_login = '';
					<?php endif; ?>
					ev.remark = '';
					ev.item_cds = '';
					ev.type = 'new';
					
				});
			<?php //ここがドラッグドロップ部分でのイベント ?>
			scheduler.attachEvent("onBeforeEventChanged", function(ev, native_event, is_new){
				if (!is_collision_err ) return;
				if (!is_new && ev.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) return;
				var is_check = true;
				if (ev.staff_cd) {
					is_check = checkStaffHolidayLogic(ev.staff_cd,ev.start_date,ev.end_date);
				}
<?php /*
				if ( (new Date() ) > ev.start_date ) {
					is_check = false;
					alert("<?php _e('The past times can not reserve',SL_DOMAIN); ?>");
				}
*/ ?>
				if (!_checkDeadline(ev.start_date) ) {
					is_check = false;
				}
				if ( ev.start_date > new Date(<?php echo $this->insert_max_day; ?>) ) {
					is_check = false;
					alert("<?php echo sprintf(__('The future times can not reserved. please less than %s days ',SL_DOMAIN),$this->config_datas['SALON_CONFIG_AFTER_DAY']); ?>");
				}
				
				if (scheduler._drag_event.staff_cd  && ev.staff_cd != <?php echo Salon_Default::NO_PREFERENCE; ?> && ev.staff_cd != scheduler._drag_event.staff_cd ) {
					var item_array = staff_items[ev.staff_cd].split(",");
					var set_item_array = ev.item_cds.split(",");
					var max_loop = set_item_array.length;
					for	 (var i = 0 ; i < max_loop; i++) {
						if (item_array.indexOf(set_item_array[i]) == -1) {
							is_check = false;
							alert("<?php echo _e('This staff member can not treat this menu ',SL_DOMAIN); ?>["+ item_name[set_item_array[i]] + "]");
							break;
						}
					}
				}
				
				if (this._drag_mode){
					save_target_event = scheduler._lame_clone(scheduler._drag_event);
				}
				else {
					save_target_event = "";
				}
				return is_check;
			});				
			scheduler.attachEvent("onClick",allow_own);
			scheduler.attachEvent("onDblClick",allow_own);
			function allow_own(id){
				var is_check = true;
				var ev = this.getEvent(id);
<?php /*
				if ( (new Date() ) > ev.start_date ) {
					is_check = false;
					alert("<?php _e('past data can not edit',SL_DOMAIN); ?>");
				}
*/ ?>
				if (!_checkDeadline(ev.start_date) ) {
					is_check = false;
				}
				
				<?php if ( ! $this->isSalonAdmin() ) : ?>
				else if (ev.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) {
						is_check = false;
						alert("<?php _e('tempolary data can not update',SL_DOMAIN); ?>");
				}
				<?php endif; ?>
				
				else if ( ev.edit_flg == <?php  echo Salon_Edit::NG; ?> ) {
						is_check = false;
						alert("<?php _e('this data can not edit',SL_DOMAIN); ?>");
				}
				if ( ev.start_date > new Date(<?php echo $this->insert_max_day; ?>) ) {
					is_check = false;
					alert("<?php echo sprintf(__('future data can not reserved. please less than %s days ',SL_DOMAIN),$this->config_datas['SALON_CONFIG_AFTER_DAY']); ?>");
				}
				if (is_check ) 	ev.branch_cd = <?php echo $this->branch_datas['branch_cd']; ?>;
				return is_check;
			}
<?php /*			
			scheduler.attachEvent("onBeforeViewChange",function(from_mode, from_date, to_mode, to_date) {
				console.log(from_mode + ' ' + from_date + ' ' + to_mode + ' ' + to_date);
				return true;
			});
*/ ?>
			
			scheduler.attachEvent("onViewChange", function(mode, date) {
				if (mode == "timeline" ) {
	//				$j(".lightbox").colorbox({rel:"staffs"});
					$j(".lightbox").colorbox();
				}
			});

			$j( '#login_password' ).keypress( function ( e ) {
				if ( e.which == 13 ) {
					$j("#button_login").click();
					return false;
				}
			} );

			$j("#button_login").click(function(){
				if ( ! checkItem("booking_login_div") ) return false;
				
				$j("#booking_login_div").append('<form id="sl_form" method="post" action="<?php echo wp_login_url(get_permalink() ) ?>" ><input  id="log" name="log" type="hidden"/><input  id="pass" name="pwd" type="hidden"/></form>');
				$j("#log").val($j("#login_username").val());
				$j("#pass").val($j("#login_password").val());
				$j("#sl_form").submit();
			});
			
			$j("#button_mobile").click(function(){
				$j("#booking_mobile").append('<form id="sl_form" method="post" action="<?php echo get_permalink();?>" ><input id="sl_desktop" name="sl_desktop" type="hidden"/></form>');		
				$j("#sl_desktop").val(false);
				$j("#sl_form").submit();
			});

			$j("#button_insert").click(function(){
				$j("#sl_search_result").html("");
				$j("#sl_search").hide();
				save_form();				
			});

			$j("#button_close").click(function(){
				$j("#sl_search_result").html("");
				$j("#sl_search").hide();
				close_form();				
			});

			$j("#button_delete").click(function(){
				var msg = "<?php _e("This reservation delete ?",SL_DOMAIN); ?>";
				var ev = scheduler.getEvent(scheduler.getState().lightbox_id);
				if (ev.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) {
					msg ="<?php _e('This is temporary reservation.\nContinue?',SL_DOMAIN); ?>";
				}
				if (confirm(msg) ) {			
						$j("#sl_search_result").html("");
						$j("#sl_search").hide();
						delete_booking_data();
					}
			});
			
			$j("#coupon").change(function () {
				fnUpdateEndTime();
			});
			


			$j("#item_cds input[type=checkbox]").click(function(){
				fnUpdateEndTime();
			});


			$j("#start_time").change(function(){
				var start  = $j(this).val();
				if (start != -1 )	{
					//valueは０なしで表示は０ありにしているがなぜ？
					if (start.length < 5 ) start="0"+start;
					target_day_from.setHours(start.substr(0,2));
					target_day_from.setMinutes(+start.substr(3,2));
					fnUpdateEndTime();
				}
			});


			<?php //[2014/06/22]スタッフコードにより選択を変更 ?>
			$j("#staff_cd").change(function(){
				var checkday = +fnDayFormat(target_day_from,"%Y%m%d");
				if (!$j(this).val()  ) {
					$j("#item_cds input").attr("disabled",true);
				}
				else if ( $j(this).val() == <?php echo Salon_Default::NO_PREFERENCE; ?>) {
					$j("#item_cds input").attr("disabled",false);
					$j("#item_cds :checkbox").each(function(){
						if (checkday < item_fromto[+$j(this).val()].f ||  checkday > item_fromto[+$j(this).val()].t) 
							$j("#item_cds #check_"+$j(this).val()).attr("disabled",true);
					})
				}
				else {
					var staff_cd = $j(this).val();
					$j("#item_cds input").attr("disabled",true);
					var item_array = staff_items[staff_cd].split(",");
					var max_loop = item_array.length;
					for	 (var i = 0 ; i < max_loop; i++) {
						<?php //メニューの有効期間を判定する　?>
						if (item_fromto[+item_array[i]].f <= checkday && checkday <= item_fromto[+item_array[i]].t) 
							$j("#item_cds #check_"+item_array[i]).attr("disabled",false);
					}
					$j("#item_cds :checkbox").each(function(){
						if($j(this).attr("disabled") ){
							$j(this).attr("checked",false);
						}
					})
					<?php //値段を再計算する ?>
					fnUpdateEndTime();
				}
			});
			<?php //[2014/06/22]ここまで ?>
			<?php parent::echoSetItemLabel(false); ?>	
			<?php parent::echo_clear_error(); ?>

			$j("#booking_button_div input").addClass("sl_button");
			$j("#customer_booking_form").hide();
			$j("#customer_booking_form").prependTo("body");
			$j("#price").addClass("sl_detail_out");			
			var prev = $j("#price").prev();
			$j(prev).addClass("sl_detail_out");			
			$j("#detail_out span").addClass("sl_detail_out");			
			$j("#detail_out label").addClass("sl_detail_out");			

			<?php if ($is_todayholiday) : ?>
				scheduler.setCurrentView(scheduler.date.add( scheduler.date[scheduler._mode+"_start"](scheduler._date),(1),scheduler._mode)); 
			<?php endif; ?>


		});
	
		function fnUpdateEndTime() {
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
			if ($j("#coupon") && coupons[$j("#coupon").val()]) {
				var coupon = coupons[$j("#coupon").val()];
				if (coupon.discount_patern_cd == <?php echo Salon_Discount::PERCENTAGE; ?> ) {
					price = (1 - coupon.discount/100) * price;
				}
				else {
					price -= coupon.discount;
				}
			}
			if (price < 0 ) price = 0;
			
			$j("#price").text(price);
			target_day_to = new Date(target_day_from.getTime());
			target_day_to.setMinutes(target_day_to.getMinutes() + minute);				
			$j("#end_time").text(' - '+target_day_to.getHours() + ":" + (target_day_to.getMinutes()<10?'0':'') + target_day_to.getMinutes());
	
			save_item_cds = tmp.join(",");
		}
	
		<?php Salon_Page::echoDayFormat(); ?>
	
		function fnDetailInit( ev ) {
			if (ev) {
				$j("#target_day").text(fnDayFormat(ev.start_date,"<?php echo __('%m/%d/%Y',SL_DOMAIN); ?>"));
				target_day_from = new Date(ev.start_date.getTime());
				$j("#item_cds input").attr("checked",false);
				if (ev.type) {
					$j("#button_insert").val("<?php _e('Create Reservation',SL_DOMAIN); ?>");
					$j("#button_delete").hide();
					<?php	if ($this->isSalonAdmin() ) 	echo '$j("#button_search").show();'; ?>
				}
				else {
					$j("#button_insert").val("<?php _e('Update Reservation',SL_DOMAIN); ?>");
					$j("#button_delete").show();
					<?php	if ($this->isSalonAdmin() ) echo '$j("#button_search").hide();'; ?>
				}
				save_user_login = ev.user_login;
				
				var item_array = ev.item_cds.split(",");
				var max_loop = item_array.length;
				for	 (var i = 0 ; i < max_loop; i++) {
					$j("#item_cds #check_"+item_array[i]).attr("checked",true);
				}
				$j("#name").val( htmlspecialchars_decode(ev.name) );
				$j("#mail").val( ev.mail );
				$j("#tel").val( ev.tel );
				$j("#remark").val( htmlspecialchars_decode(ev.remark) );
				$j("#staff_cd").val( ev.staff_cd ).change();
				$j("#name").attr("readonly", false);
				$j("#mail").attr("readonly", false);
				$j("#tel").attr("readonly", false);
				<?php if ( !is_user_logged_in() ||  $this->isSalonAdmin() ) : ?>
					$j("#name").focus();			
				<?php else : ?>
					$j("#name").attr("readonly", true);
					$j("#mail").attr("readonly", true);
					if (ev.tel) $j("#tel").attr("readonly", true);
					$j("#staff_cd").focus();			
				<?php endif; ?>
				$j("#start_time").val(ev.start_date.getHours()+":"+(ev.start_date.getMinutes()<10?'0':'')+ev.start_date.getMinutes());
				save_target_event = scheduler._lame_clone(ev);
				fnUpdateEndTime();
				
				$j("#rstatus").text("");
				if (ev.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) {
					$j("#rstatus").text("<?php _e('tentative',SL_DOMAIN); ?>");
				}
				else if (ev.status == <?php echo Salon_Reservation_Status::COMPLETE; ?> ) {
					$j("#rstatus").text("<?php _e('completed',SL_DOMAIN); ?>");
				}
				
				if (isNeedToCheckPromotionDate ) {
					$j("#coupon").remove();
					var target = fnDayFormat(ev.start_date,"%Y%m%d");
					var cn = '<select id="coupon"><option value="">'+"<?php _e('select please',SL_DOMAIN); ?>"+'</option>';
					for(var id in promotions) {
						if(promotions[id]['from'] == 0 && promotions[id]['to'] == 20991231) {
							cn += '<option value="'+promotions[id]['key']+'">'+promotions[id]['val']+'</option>';
						}
						else {
							if (target >= promotions[id]['from'] && target <= promotions[id]['to'] ) {
								cn += '<option value="'+promotions[id]['key']+'">'+promotions[id]['val']+'</option>';
							}
						}
					}
					$j("#coupon_lbl").after(cn);
					$j("#coupon").change(function () {
						fnUpdateEndTime();
					});
				}
				
				$j("#coupon").val(ev.coupon).change();
					
//					
//		foreach($promotion_datas as $k1 => $d1 ) {
//			$echo_data .= '<option value="'.$d1['set_code'].'">'.htmlspecialchars($d1['description'],ENT_QUOTES).'</option>';
//		}
//		$echo_data .= '</select></div>';
					//alert("check");
				<?php parent::echo_clear_error(); ?>
			}
		}
		<?php 
			if ($this->_is_editBooking() ) {
				parent::echoClientItem(array('customer_name','mail_norequired','booking_tel','staff_cd','item_cds','start_time','remark','booking_user_login','booking_user_password','regist_customer','coupon')); 
			}
			else {
				parent::echoClientItem(array('customer_name','mail','branch_tel','staff_cd','item_cds','start_time','remark','booking_user_login','booking_user_password','regist_customer','coupon')); 
			}
		?>	
		
		
		scheduler.showLightbox = function(id){
			$j("#customer_booking_form").show();
			$j("#data_detail").show();
			var ev = scheduler.getEvent(id);
			scheduler.startLightbox(id, $j("#customer_booking_form").get(0));
			fnDetailInit(ev);
			
			
		}
		scheduler.checkCollision = function(ev) {
			
			if (ev.edit_flg && (ev.edit_flg == <?php echo Salon_Edit::NG; ?> ) ) return false;
			res = checkDuplicate(ev);
			if (res ) 	res = checkStaffHoliday(ev,'','',true);
			ev.nonce = "<?php echo $this->nonce; ?>";
			is_collision_err = res;
			return res;
		}
		
		function checkHolidayLogic(from,to) {
			var global = scheduler._marked_timespans.global;
			var t_sd = scheduler.date.date_part(new Date(from));
			if ( global[t_sd.valueOf()] ) {
				if (global[t_sd.valueOf()]["dhx_time_block"]) return false;	//特別な休み
				if (global[t_sd.valueOf()]["default"]) return true;			//特別な営業日
			}
			if ( global[from.getDay()] && global[from.getDay()]["dhx_time_block"]) return false;
			if ( global[from.valueOf()] && global[from.valueOf()]["dhx_time_block"]) return false;
		}

		function checkStaffHolidayLogic(staff_cd,from,to) {
			if (staff_cd) {
				var timeline = scheduler._marked_timespans.timeline;
				var tmp_st = scheduler.date.date_part(new Date(from));
			<?php if ($this->_is_staffSetNormal() ) : ?>
				if (timeline && timeline[staff_cd]) {
					var tmp_working = timeline[staff_cd][tmp_st.valueOf()];
					if (tmp_working) {
						var tmp_working = tmp_working["dhx_time_block"];
						var zones = tmp_working[0].zones;
						if (zones) {
							for (var k=0; k<zones.length; k += 2) {
								var zone_start = zones[k];
								var zone_end = zones[k+1];
								
								var start_date = new Date(+tmp_working[0].days + zone_start*60*1000);
								var end_date = new Date(+tmp_working[0].days + zone_end*60*1000);
							}
							if (from <= to && start_date <= from && from <= end_date && start_date <= to && to <= end_date ) return false;
						}
					}
				}
			<?php else: ?>
				if (timeline && timeline[staff_cd]) {
					var tmp_working = timeline[staff_cd][tmp_st.valueOf()];
					if (tmp_working) {
						var tmp_working = tmp_working["default"];
						var zones = tmp_working[0].zones;
						if (zones) {
							for (var k=0; k<zones.length; k += 2) {
								var zone_start = zones[k];
								var zone_end = zones[k+1];
								
								var start_date = new Date(+tmp_working[0].days + zone_start*60*1000);
								var end_date = new Date(+tmp_working[0].days + zone_end*60*1000);
							}
							if (from > to || start_date > from || from >= end_date || start_date >= to || to > end_date) return false;
						}
						else return false;
					}
					else return false;
				}
				else return false;
			<?php endif; ?>			
			}
			return true;
			
		}
		
		function checkStaffHoliday(ev,from,to,isMove) {
			var day_from,day_to,staff_cd;
			if (! from) {
				day_from = ev.start_date;
				day_to = ev.end_date;
				staff_cd = ev.staff_cd;
			}
			else {
				day_from = from;
				day_to = to;
				staff_cd = $j("#staff_cd").val();
			}
			var msg = '';
			if (isMove && isMove==true) {
				if (checkHolidayLogic(day_from,day_to) == false ) {
					msg = "<?php _e('can not be reserved ',SL_DOMAIN); ?>";
				}
			}
			if (checkStaffHolidayLogic (staff_cd,day_from,day_to) == false ) {
				<?php if ($this->_is_staffSetNormal() ) : ?>
					msg = "<?php _e('today this staff can not be reserved ',SL_DOMAIN); ?>";
				<?php else: ?>			
					msg = "<?php _e('this staff can not be reserved in this time range',SL_DOMAIN); ?>";
				<?php endif; ?>			
			}
			if (msg != '' ) {
				if ( $j("#data_detail").is(":hidden")) {
					alert(msg);
				}
				else {
					var label = $j("#staff_cd").prev().children(".small");
					label.text(msg)
					label.addClass("error small");
				}
				return false;
			}
			return true;
		}

		function checkDuplicate(ev,from,to) {
			var staff_cd;
			var is_do_form = true;
			<?php //ドラッグでの起動の場合、fromはなし ?>
			if (! from) {
				from = ev.start_date;
				to = ev.end_date;
				staff_cd = ev.staff_cd;
				is_do_form = false;
			}
			else {
				staff_cd = $j("#staff_cd").val();
			}
			<?php //登録しようとしている予約の範囲の予約を全部取得する ?>
			var evs = scheduler.getEvents(from, to);
			var ev_cnt = 0;
			var staff_array = new Array();
			for (var i=0; i<evs.length; i++) {
				if (evs[i].id != ev.id) {
					ev_cnt++;
					if (staff_array[evs[i].staff_cd]) staff_array[evs[i].staff_cd] += 1;
					else staff_array[evs[i].staff_cd] = 1;
					
					if (ev.user_login && evs[i].user_login == ev.user_login) {
						if ( is_do_form) {
							var label = $j("#start_time").prev().children(".small" );
							label.text("<?php _e('your reservation is duplicated',SL_DOMAIN); ?>")
							label.addClass("error small");
							var label = $j("#name").prev().children(".small" );
							label.text("<?php _e('your reservation is duplicated',SL_DOMAIN); ?>")
							label.addClass("error small");
						}
						return false;
					}
				}
			}
			var is_error = false;
			
			if (staff_cd  != <?php echo Salon_Default::NO_PREFERENCE; ?> ) {
				if (staff_array[staff_cd] > staff_duplicates[staff_cd] ) is_error = true;
			}
			if ( ev_cnt >= all_duplicate_cnt ) is_error = true;
			
			if ( is_error && is_do_form) {
				if (ev.staff_cd == $j("#staff_cd").val()) {
					var label = $j("#start_time").prev().children(".small" );
					label.text("<?php _e('reservation_time is duplicated ',SL_DOMAIN); ?>")
					label.addClass("error small");
				}
				else {
					var label = $j("#staff_cd").prev().children(".small");
					label.text("<?php _e('this staff is reserved ',SL_DOMAIN); ?>")
					label.addClass("error small");
				}
			}
			
			return !is_error;
		}
		
		function _edit_text_name (name ) {
			var edit_name = "<?php _e('Mr/Ms %s',SL_DOMAIN); ?>";
			return edit_name.replace("%s",name);			
		}

		function save_form() {
			if ( ! checkItem("data_detail") ) return false;
			var ev = scheduler.getEvent(scheduler.getState().lightbox_id);
			<?php if ( $this->isSalonAdmin() ) : ?>
				if (ev.status == <?php echo Salon_Reservation_Status::TEMPORARY; ?> ) {
					if (!confirm("<?php _e('This is temporary reservation.\nIf you will update,this reservation is completed.\nContinue?',SL_DOMAIN); ?>") ) return false;
				}
			<?php endif; ?>
			if ( ! checkStaffHoliday(ev,target_day_from,target_day_to) ) return false;
			<?php	
			//ここでuser_loginを入れておく。そうしないとここのチェックを正常になった後で、
			//onEventAddedの中のcheckDuplicatedでエラーになり
			//eventが消されてしまい後続処理ができない 
			//新規で検索した後に名前などを一部変更した場合は、userloginをクリアする
			?>
			if (ev.type && ev.type == 'new' && save_name != $j("#name").val() || save_tel !=  $j("#tel").val() || save_mail != $j("#mail").val() ) 
				save_user_login = "";
			ev.user_login =	save_user_login;
			if ( ! checkDuplicate(ev,target_day_from,target_day_to) ) return false;
			if (!_checkDeadline(target_day_from) ) return false;

			ev.name = $j("#name").val();
			ev.text = _edit_text_name($j("#name").val());
			ev.tel =  $j("#tel").val();
			ev.mail = $j("#mail").val();
			ev.start_date = target_day_from;
			ev.end_date = target_day_to;
			ev.staff_cd = $j("#staff_cd").val();
			ev.item_cds = save_item_cds;
			ev.remark = $j("#remark").val();
			ev.coupon = $j("#coupon").val();
			scheduler.endLightbox(true, $j("#data_detail").get(0));

			
			$j("#customer_booking_form").hide();
//				$j(".lightbox").colorbox({rel:"staffs"});
				$j(".lightbox").colorbox();
			

		}
		
		
		<?php parent::echoCheckDeadline	($this->config_datas['SALON_CONFIG_RESERVE_DEADLINE']); ?>
		
				
		function delete_booking_data() {
			var ev = scheduler.getEvent(scheduler.getState().lightbox_id);
			ev.nonce = "<?php echo $this->nonce; ?>";
			scheduler.deleteEvent(ev.id);
			scheduler.endLightbox(false, $j("#data_detail").get(0));
			$j("#customer_booking_form").hide();
//				$j(".lightbox").colorbox({rel:"staffs"});
				$j(".lightbox").colorbox();
		}

		function close_form(argument) {
			scheduler.endLightbox(false, $j("#data_detail").get(0));
			$j("#customer_booking_form").hide();
//				$j(".lightbox").colorbox({rel:"staffs"});
				$j(".lightbox").colorbox();
		}
		
		<?php parent::echoCheckClinet(array('chk_required','chkMail','chkTime','lenmax','reqCheck','chkSpace','chkTel','reqOther')); ?>		
		<?php parent::echoRemoveModal(); ?>

	</script>
	<?php if (Salon_Component::isMobile(false) ) : ?>
    	<div id="booking_mobile" >
			<input type="button" value="<?php _e('Mobile Version',SL_DOMAIN); ?>" id="button_mobile"  >
    	</div>
    <?php endif; ?>
			

	<?php if ($this->_is_userlogin() ) : ?>
		<div id="booking_login_div" >
		<?php if ( is_user_logged_in() ) : ?>
			<?php if ($this->_is_editBooking() ) : ?>
				<a href="<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin" ><?php _e('admin here',SL_DOMAIN); ?></a><br>
				<a href="<?php echo wp_logout_url(get_permalink() ); ?>" ><?php _e('logout here',SL_DOMAIN); ?></a>
			<?php else : ?>
				<?php echo sprintf( __('Mr/Ms %s',SL_DOMAIN),$this->user_inf['user_name']); ?>
				<a href="<?php echo wp_logout_url(get_permalink() ); ?>" ><?php _e('logout here',SL_DOMAIN); ?></a>
			<?php endif; ?>
		<?php else : ?>
				<p><?php _e('Reservations are available without log in',SL_DOMAIN); ?></p>
				<input type="text" id="login_username" value="" />
				<input type="password" id="login_password" value="" />
				<label  >&nbsp;</label>
				<input type="button" value="<?php _e('Log in',SL_DOMAIN); ?>" id="button_login" class="sl_button"  >
		<?php endif; ?>
			<div class="spacer"></div>
		</div>
	<?php endif; ?>
	<div id="scheduler_here" class="dhx_cal_container" >
		<div class="dhx_cal_navline">
			<div class="dhx_cal_prev_button">&nbsp;</div>
			<div class="dhx_cal_next_button">&nbsp;</div>
			<div class="dhx_cal_today_button"></div>
			<div class="dhx_cal_date"></div>
			<div class="dhx_cal_tab" name="day_tab" style="right:148px;"></div>
			<div class="dhx_cal_tab" name="week_tab" style="right:84px;"></div>
			<div class="dhx_cal_tab" name="month_tab" style="right:20px;"></div>
			<div class="dhx_cal_tab" name="timeline_tab" style="right:212px;"></div>
		</div>
		<div class="dhx_cal_header"></div>
		<div class="dhx_cal_data"></div>
	</div>
	
	<div id="customer_booking_form" class="salon_form">
	<div id="data_detail" >
		<div id="detail_out">
			<label  ><?php _e('Date',SL_DOMAIN); ?>:</label>
			<span id="target_day" ></span>
			<label  ><?php _e('Status',SL_DOMAIN); ?>:</label>
		    <span id="rstatus"  ></span>
            
		</div>
<?php if ($this->_is_editBooking() ) : ?>
		<div id="multi_item_wrap" >
		<input type="text" id="name" />
		<input id="button_search" type="button" class="sl_button" value=<?php _e('Search',SL_DOMAIN); ?> />
		</div>
<?php else: ?>
		<input type="text" id="name" />
<?php endif; ?>
		<input type="text" id="tel"/>
		<input type="text" id="mail"  />

		<div id="date_time_wrap" >
				<?php parent::echoTimeSelect("start_time",$this->branch_datas['open_time'],$this->branch_datas['close_time'],$this->branch_datas['time_step']); ?>	
				<span id="end_time" ></span>
		</div>
		<?php parent::echoStaffSelect("staff_cd",$this->staff_datas,$this->_is_noPreference(),false); ?>
		<?php parent::echoItemInputCheckTable($this->item_datas); ?>
		<?php parent::echoCouponSelect("coupon",$this->promotion_datas); ?>
		<textarea id="remark"  ></textarea>
		<label ><?php _e('price',SL_DOMAIN); ?>:</label>
		<span id="price"></span>
		<div class="spacer"></div>
		<div id="booking_button_div" >
			<input type="button" value="<?php _e('Close',SL_DOMAIN); ?>" id="button_close"  >
			<input type="button" value="<?php _e('Cancel Reservation',SL_DOMAIN); ?>" id="button_delete"  >
			<input type="button" value="<?php _e('Create Reservation',SL_DOMAIN); ?>" id="button_insert"  >
		</div>			
	</div>
<?php if ($this->isSalonAdmin() ) : ?>
	<div id="sl_search" class="modal">
		<div class="modalBody">
			<div id="sl_search_result"></div>
		</div>
	</div>
<?php endif; ?>
	<div id="sl_hidden_photo_area">
<?php 
	foreach ($this->staff_datas as $k1 => $d1 ) {
		if (!empty($d1['photo_result'][0]) ) {
			for($i = 1;$i<count($d1['photo_result']);$i   ++  ){
				$tmp = "<a href='".$d1['photo_result'][$i]['photo_path']."' rel='staff".$d1['staff_cd']."' class='lightbox' ></a>";
				$url = site_url();
				$url = substr($url,strpos($url,':')+1);
				if (is_ssl() ) {
					$tmp = preg_replace("$([hH][tT][tT][pP]:".$url.")$","https:".$url,$tmp);
				}
				else {
					$tmp = preg_replace("$([hH][tT][tT][pP][sS]:".$url.")$","http:".$url,$tmp);
				}
				echo $tmp;
			}
		}
	}



?>
    </div>
	</div>
<?php 
