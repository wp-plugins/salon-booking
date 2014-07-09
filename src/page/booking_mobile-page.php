<?php


	$url =   get_permalink();
	$parts = explode('/',$url);
	$addChar = "?";
	if (strpos($parts[count($parts)-1],"?") ) {
		$addChar = "&";
	}
	$url = $url.$addChar."sl_desktop=true";
	
	//スタッフデータの編集
	$edit_staff = array();
	if ($this->_is_noPreference() ) {
		$edit_staff[Salon_Default::NO_PREFERENCE]['label'] = __('Anyone',SL_DOMAIN);
		$edit_staff[Salon_Default::NO_PREFERENCE]['img']='<sapn class="slm_noimg" >'. __('Anyone',SL_DOMAIN).'</span>';
	}
	$reserve_possible_cnt = 0;
	foreach ($this->staff_datas as $k1 => $d1 ) {
		
		if ($this->config_datas['SALON_CONFIG_MAINTENANCE_INCLUDE_STAFF'] != Salon_Config::MAINTENANCE_NOT_INCLUDE_STAFF
			|| $d1['position_cd'] != Salon_Position::MAINTENANCE ) {
				
			if ($this->config_datas['SALON_CONFIG_MOBILE_NO_PHOTO'] == Salon_Config::MOBILE_NO_PHOTO || empty($d1['photo_result'][0]) ) {
				$tmp='<sapn class="slm_noimg" >'.htmlspecialchars($d1['name'],ENT_QUOTES).'</span>';
			}
			else {
				$tmp = "<img src='".$d1['photo_result'][0]['photo_resize_path']."' alt='' /></a>";
				$url = site_url();
				$url = substr($url,strpos($url,':')+1);
				$url = str_replace('/','\/',$url);
				if (is_ssl() ) {
					$tmp = preg_replace("/([hH][tT][tT][pP]:".$url.")/","https:".$url,$tmp);
				}
				else {
					$tmp = preg_replace("/([hH][tT][tT][pP][sS]:".$url.")/","http:".$url,$tmp);
				}
			}
			$edit_staff[$d1['staff_cd']]['img'] = $tmp;
			$edit_staff[$d1['staff_cd']]['label'] = htmlspecialchars($d1['name'],ENT_QUOTES);
		}
	}
	$init_target_day = date_i18n('Ymd');
	//
	$staff_holiday_class = "slm_holiday";
	$staff_holiday_set = __('Holiday',SL_DOMAIN);
	if (!$this->_is_staffSetNormal() ) {
		$staff_holiday_class = "slm_on_business";
		$staff_holiday_set = __('Bookable',SL_DOMAIN);
	}
	
?>
<div id="sl_content" role="main">
	<script type="text/javascript" charset="utf-8">
		var $j = jQuery;
		var top_pos;
		var bottom_pos;
		var today = "<?php echo $init_target_day; ?>";
		
		var target_day_from = new Date();
		var target_day_to = new Date();
		var save_item_cds = "";
		var operate = "";
		var save_id = "";
		var is_holiday= false;
		
		var save_user_login = "";
		
		
		var isTouch = ('ontouchstart' in window);
		var tap_interval = <?php echo Salon_Config::TAP_INTERVAL; ?>;

		var staff_items = new Array();

		slmSchedule.config={
					day_full:[<?php _e('"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"',SL_DOMAIN); ?>],
					day_short:[<?php _e('"Sun","Mon","Tue","Wed","Thu","Fri","Sat"',SL_DOMAIN); ?>]
		};
		
		<?php parent::echoItemFromto($this->item_datas); ?>

		$j(document).ready(function() {

			var timer;
			<?php //[2014/06/22] 	
			foreach ($this->staff_datas as $k1 => $d1 ) {
				echo 'staff_items['.$d1['staff_cd'].'] = "'.$d1['in_items'].'";';
			}
			?>	
			<?php if ($this->_is_staffSetNormal() )  : ?>			
			$j(".slm_time_li").bind({
				'touchstart': function(e) {
					var tmp_staff_cd = this.parentElement.id.split("_")[2];
					var tmp_time = +this.innerText;
					timer = setTimeout( function()
					{
						_fnAddReservation(tmp_time);
						$j("#staff_cd").val(tmp_staff_cd).change();
					}, tap_interval );
				},
				'touchmove': function(e) {
					clearTimeout( timer );
				},
				'touchend': function(e) {
					clearTimeout( timer );
				},
				'touchcancel': function(e) {
					clearTimeout( timer );
				}
			});
			<?php endif; ?>

			<?php parent::echoSetHolidayMobile($this->branch_datas,$this->working_datas,$this->target_year,$this->first_hour);	?>
			
			$j("#slm_page_login").hide();
			$j("#slm_page_regist").hide();
			var top = 	$j("#slm_main_data").outerHeight()	- $j("#slm_holiday").css("font-size").toLowerCase().replace("px","");
			$j("#slm_holiday").css("padding-top",top / 2 + "px");
			$j("#slm_holiday").height($j("#slm_main_data").outerHeight()- (top/2));			
			$j("#slm_holiday").width($j("#slm_main_data").outerWidth());	
			$j("#slm_holiday").hide();
			
			$j("#slm_exec_login").click(function(){
				
				$j("#sl_content").append('<form id="sl_form" method="post" action="<?php echo wp_login_url(get_permalink() ) ?>" ><input  id="log" name="log" type="hidden"/><input  id="pass" name="pwd" type="hidden"/></form>');
				$j("#log").val($j("#login_username").val());
				$j("#pass").val($j("#login_password").val());
				$j("#sl_form").submit();
			});

			$j("#slm_desktop").click(function(){
				$j("#sl_content").append('<form id="sl_form" method="post" action="<?php echo get_permalink();?>" data-ajax="false" ><input id="sl_desktop" name="sl_desktop" type="hidden"/></form>');
				$j("#sl_desktop").val(true);
				$j("#sl_form").submit();
			});
			$j("#slm_login").click(function(){
				$j("#slm_page_main").hide();
				$j("#slm_page_login").show();
			});
			$j("#slm_regist_button").click(function(){
				var now = new Date();
				_fnAddReservation(now.getHours()+1);
				$j('#staff_cd').prop('selectedIndex', 0).change();
			});
			$j("#slm_search").click(function(){
				var setDate = fnDayFormat(new Date($j("#slm_searchdate").val()),"%Y%m%d");
				setDayData(setDate);
			});
			$j("#slm_today").click(function(){
				setDayData(today);
			});
			$j("#slm_prev").click(function(){
				setDayData(_fnCalcDay(new Date($j("#slm_searchdate").val()),-1));
			});
			$j("#slm_next").click(function(){
				setDayData(_fnCalcDay(new Date($j("#slm_searchdate").val()),1));
				
			});
			$j("#slm_mainpage").click(function(){
				$j("#slm_page_main").show();
				$j("#slm_page_login").hide();
				$j("html,body").animate({ scrollTop: top_pos }, 'fast');
			});
			$j("#slm_mainpage_regist").click(function(){
				$j("#slm_page_main").show();
				$j("#slm_page_regist").hide();
				$j("html,body").animate({ scrollTop: top_pos }, 'fast');
			});
			$j("#slm_exec_regist").click(function(){
				_UpdateEvent();
			});
			
			$j("#slm_exec_delete").click(function() {
				if (confirm("<?php _e("This reservation delete ?",SL_DOMAIN); ?>") ) {
					operate = "deleted";				
					_UpdateEvent();
				}
			});

			$j("#start_time").change(function(){
				var start  = $j(this).val();
				if (start != -1 )	{
					target_day_from.setHours(start.substr(0,2));
					target_day_from.setMinutes(+start.substr(3,2));
					fnUpdateEndTime();
				}
			});
			
			<?php //[2014/06/22]スタッフコードにより選択を変更 ?>
			$j("#staff_cd").change(function(){
				var checkday = +fnDayFormat(target_day_from,"%Y%m%d");
				if ($j(this).val() == <?php echo Salon_Default::NO_PREFERENCE; ?> ) {
					$j("#item_cds input").parent().show();
					$j("#item_cds input").attr("disabled",false);
					$j("#item_cds :checkbox").each(function(){
						if (checkday < item_fromto[+$j(this).val()].f ||  checkday > item_fromto[+$j(this).val()].t)  {
							$j("#item_cds #slm_chk_"+$j(this).val()).attr("disabled",true);
							$j("#item_cds #slm_chk_"+$j(this).val()).parent().hide();
						}
					})
				}
				else {
					var staff_cd = $j(this).val();
					$j("#item_cds input").attr("disabled",true);
					$j("#item_cds input").parent().hide();
					var item_array = staff_items[staff_cd].split(",");
					var max_loop = item_array.length;
					for	 (var i = 0 ; i < max_loop; i++) {
						<?php //メニューの有効期間を判定する　?>
						if (item_fromto[+item_array[i]].f <= checkday && checkday <= item_fromto[+item_array[i]].t) {
							$j("#item_cds #slm_chk_"+item_array[i]).attr("disabled",false);
							$j("#item_cds #slm_chk_"+item_array[i]).parent().show();
						}
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
			<?php //[2014/06/22]スタッフコードにより選択を変更 ?>
			
			$j("#item_cds input[type=checkbox]").click(function(){
				fnUpdateEndTime();
			});

			$j(document).on('click','.slm_on_business',function(){
				var tmp_val = $j(this.children).text();
				_fnAddReservation(+tmp_val.split(":")[1]);
				$j("#staff_cd").val(tmp_val.split(":")[0]).change();
			});
				
			<?php parent::echoSetItemLabelMobile(); ?>
			<?php
				$res =  parent::echoMobileData($this->reservation_datas,$init_target_day,$this->first_hour,$this->user_inf['user_login']);
				//現状1件だが複数件でも大丈夫なように
				foreach($res as $k1 => $d1 ) {
					echo "slmSchedule._daysStaff[\"$k1\"] = $d1;";
				}
			?>

			AutoFontSize();
			<?php /*?>ヘッダがどんなかわからないのでいちづけとく<?php */?>
			top_pos = $j("#slm_main").offset().top;
			bottom_pos = top_pos + $j("#slm_main").height();
			$j("html,body").animate({ scrollTop: top_pos }, 'fast');
			
			setDayData(today);
			
		});
<?php /*?>		
		//登録ボタンを範囲外になったら消す。動きがいまいちなのでコメント
		$j(function() {
			$j(window).scroll(function () {
				var s = $j(this).scrollTop();
				var b = s + window.innerHeight;
				if (s + 50 < top_pos || b - 50 > bottom_pos) $j("#slm_regist").fadeOut('slow');
				else $j("#slm_regist").fadeIn('slow');
				
			})
		});
		
<?php */?>
		function _fnAddReservation (startHour) {
			<?php //過去は予約できないようにしとく ?>
			var chk_date = 	new Date($j("#slm_searchdate").val());
			if (startHour) { 
				chk_date.setHours(startHour); 
			}
			var now = new Date();
			if (now > chk_date ) {
				alert("<?php _e('The past times can not reserve',SL_DOMAIN); ?>");
				return;
			}
			$j("#slm_page_main").hide();
			$j("#slm_page_regist").show();
			$j("#slm_exec_delete").hide();
			$j("#slm_target_day").text($j("#slm_searchdate").val()); 
			target_day_from = new Date($j("#slm_searchdate").val());
			if (startHour) { 
				target_day_from.setHours(startHour); 
			}
			target_day_to = new Date(target_day_from.getTime());
			save_item_cds = "";
			operate = "inserted";
			save_id = "";
			save_user_login = "";
			<?php if ( is_user_logged_in() && ! $this->isSalonAdmin()) echo 'save_user_login = "'.$this->user_inf['user_login'].'"'; ?>
				
			
			$j("#start_time").val(toHHMM(target_day_from));
			$j("#item_cds input[type=checkbox]").attr("checked",false);

			$j("#start_time").trigger("change");
			<?php //名前電話メールは消さずに１度入力したのそのまま ?>
<?php /*?>				
			$j("#name").val("");
			$j("#tel").val("");
			$j("#mail").val("");
			$j("#remark").val("");
<?php */?>				
			
		}
		
		function _fnCalcDay(ymd,add) {
			var clas = Object.prototype.toString.call(ymd).slice(8, -1);
			if (clas !== 'Date') {
				return ymd;
			}
			var tmpDate = ymd.getDate();
			ymd.setDate(tmpDate + add);
			return fnDayFormat(ymd,"%Y%m%d");
		}
		
		function setDayData(yyyymmdd) {
			yyyymmdd=yyyymmdd+"";
			var yyyy = yyyymmdd.substr(0,4);
			var mm = yyyymmdd.substr(4,2);
			var dd = yyyymmdd.substr(6,2);
			var tmpDate = new Date(yyyy, +mm - 1,dd);
			
			
			
			$j("#slm_searchdate").val(fnDayFormat(tmpDate,"<?php echo __('%m/%d/%Y',SL_DOMAIN); ?>"));
			$j(".slm_tile").off("click");
			$j(".slm_tile").remove();
			$j(".slm_staff_holiday").remove();
			
			$j("#slm_searchdays").text(slmSchedule.config.day_full[tmpDate.getDay()]);
			//休みだったら
			if (slmSchedule.chkHoliday(tmpDate) ) {
				$j("#slm_holiday").show();
				$j("#slm_regist_button").hide();
				return;
			}
			else {
				$j("#slm_holiday").hide();
				$j("#slm_regist_button").show();
			}
			//予約の部分でも使用
			var each15 = $j("#slm_main_data ul li:nth-child(2)").outerWidth()/4;
			var left_start = $j("#slm_main_data ul li:first-child").outerWidth();
						
			//スタッフの出退勤
			if (slmSchedule.config.staff_holidays[yyyymmdd] ) {
				for(var staff_cd_h in slmSchedule.config.staff_holidays[yyyymmdd]) {
					var tmpH = slmSchedule.config.staff_holidays[yyyymmdd][staff_cd_h];
					for(var seqH in tmpH ) {
						var left =  Math.floor(each15 * tmpH[seqH][0] + left_start);
						var width = Math.floor(each15 * tmpH[seqH][1]);
						var height = $j("#slm_st_" + staff_cd_h).outerHeight();
						var fromH = tmpH[seqH][2].substr(0,2);
						var setH = '<div class="<?php echo $staff_holiday_class; ?> slm_staff_holiday" style="position:absolute; top:0px; height: '+height+'px; left:'+left+'px; width:'+width+'px;"><?php echo $staff_holiday_set; ?><div style="display:none">'+staff_cd_h+':'+fromH+'</div></div>';
						
						
//			<?php if (!$this->_is_staffSetNormal() )  : ?>			
//			$j("#slm_st_"+staff_cd_h).on("click",".slm_staff_holiday",function() { alert("here"); } );
//			<?php endif; ?>
						
						$j("#slm_st_"+staff_cd_h).prepend(setH);
					}
				}
			}

			if (slmSchedule._daysStaff[yyyymmdd]) {
				if (slmSchedule._daysStaff[yyyymmdd]["e"] == 0) {
					return;
				}
			}
			//初めての日付はサーバへ
			else {
				_GetEvent(yyyymmdd);
				return;		//抜けてデータを取ってきたらもう一度
			}


			for(var seq0 in slmSchedule._daysStaff[yyyymmdd]["d"]){
				for(var staff_cd in slmSchedule._daysStaff[yyyymmdd]["d"][seq0]){
					var base=+slmSchedule._daysStaff[yyyymmdd]["d"][seq0][staff_cd]["s"];
					var height = Math.floor($j("#slm_st_" + staff_cd).outerHeight()/base)-2;	//微調整
									
					for(var seq1 in slmSchedule._daysStaff[yyyymmdd]["d"][seq0][staff_cd]["d"]) {
						for(var level in slmSchedule._daysStaff[yyyymmdd]["d"][seq0][staff_cd]["d"][seq1]) {
							var tmpb = slmSchedule._daysStaff[yyyymmdd]["d"][seq0][staff_cd]["d"][seq1][level]["b"];
							var tmpd = slmSchedule._daysStaff[yyyymmdd]["d"][seq0][staff_cd]["d"][seq1][level]["d"];
							var left =  Math.floor(each15 * tmpb[0] + left_start);
							var width = Math.floor(each15 * tmpb[1]);
							var top = (+level) * height;
							var eid = 'slm_event_'+staff_cd+'_'+tmpb[2];
							slmSchedule._events[tmpb[2]]={"staff_cd":staff_cd,"from":tmpb[3],"to":tmpb[4]};
							
							var set_class = "slm_tile";
							if (tmpb[5]=="<?php echo Salon_Reservation_Status::COMPLETE; ?>") {
								set_class += " slm_myres_comp";
							}
							else if (tmpb[5]=="<?php echo Salon_Reservation_Status::TEMPORARY; ?>") {
								set_class += " slm_myres_temp";
							}
							
							var setcn = '<div id="'+eid+'" class="'+set_class+'"style="position:absolute; top:'+top+'px; height: '+height+'px; left:'+left+'px; width:'+width+'px;"><span title="'+tmpb[3]+'-'+tmpb[4]+'"/></div>';
							
							$j("#slm_st_"+staff_cd).prepend(setcn);
							
							if (tmpb[5]=="<?php echo Salon_Reservation_Status::COMPLETE; ?>") {
								slmSchedule.setEventDetail(tmpb[2],tmpd);
								$j("#"+eid).on("click",function(){
									$j("#slm_page_main").hide();
									$j("#slm_page_regist").show();
									$j("#slm_exec_delete").show();
									var ids = this.id.split("_");
									save_id = ids[3];
									var ev_tmp = slmSchedule._events[save_id];
									
									var settime = ev_tmp["from"].substr(0,2)+":"+ev_tmp["from"].substr(2,2);
									target_day_from = new Date($j("#slm_searchdate").val()+" "+settime);
									$j("#start_time").val(settime);
									save_item_cds =ev_tmp["item_cds"];
									
									var item_array = save_item_cds.split(",");
									for	 (var i = 0 ,max_loop = item_array.length; i < max_loop; i++) {
										$j("#slm_chk_"+item_array[i]).attr("checked",true);
									}
									$j("#staff_cd").val(ev_tmp["staff_cd"]).change();

									$j("#name").val(htmlspecialchars_decode(ev_tmp["name"]));
									$j("#tel").val(ev_tmp["tel"]);
									$j("#mail").val(ev_tmp["mail"]);
									$j("#remark").val(htmlspecialchars_decode(ev_tmp["remark"]));
									$j("#slm_target_day").text($j("#slm_searchdate").val()); 
									operate = "updated";
									save_user_login = ev_tmp["user_login"];
									$j("#start_time").trigger("change");
									
								});
							}



						}
					}
				}
			}
		}
		
		<?php
		parent::echoClientItemMobile(array('mobile_search_day','booking_user_login','booking_user_password','customer_name','mail_norequired','booking_tel','staff_cd','start_time','remark'));
		?> 
		<?php parent::echoDayFormat(); ?>


		function AutoFontSize(){
			var each = $j("#slm_main_data ul li:nth-child(2)").outerWidth();
<?php /*?>//each=9;<?php */?>		
			var fpar = Math.floor(each/<?php echo ($this->last_hour-$this->first_hour) +1  ?> /2*100);
<?php /*?>			
			//12pxでCSSを定義しているので
			//var fpar = (Math.floor((wpx)/(1500/100)));// 横幅px ÷ (最少幅px/100)
<?php */?>
			$j(".slm_main_line li").css("font-size",fpar+"%");
			$j(".slm_main_line li:first-child").css("font-size","100%");
		}

		function _GetEvent(targetDay) {
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=booking", 
					dataType : "json",
					data: {
						"target_day":targetDay
						,"branch_cd":<?php echo $this->branch_datas['branch_cd']; ?>						
						,"first_hour":<?php echo $this->first_hour; ?>
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Booking_Get_Mobile"
					},
					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							slmSchedule._daysStaff[targetDay] = data.set_data[targetDay];
							setDayData(targetDay)
						}
			        },
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });			
		}

		function _UpdateEvent() {
			var temp_p2 = '';
			if (operate != 'inserted') {
				temp_p2 = slmSchedule._events[save_id]['p2'];
			}
			<?php	if ($this->_is_userlogin() && is_user_logged_in() && ! $this->isSalonAdmin() ) : ?>
				var name = "<?php echo $this->user_inf['user_name']; ?>";
				<?php
				if (empty($this->user_inf['tel']) ) {
					echo 'var tel = $j("#tel").val();';
				}
				else {
					echo 'var tel = "'.$this->user_inf['tel'].'";';
				}
				if (empty($this->user_inf['user_email']) ) {
					echo 'var mail = $j("#mail").val();';
				}
				else {
					echo 'var mail = "'.$this->user_inf['user_email'].'";';
				}
				?>
			<?php else: ?>
				var name = $j("#name").val();
				var tel = $j("#tel").val();
				var mail = $j("#mail").val();
			<?php   endif; ?>
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=booking", 
					dataType : "json",
					data: {
						"staff_cd":$j("#staff_cd").val()
						,"id":save_id
						,"name":name
						,"mail": mail
						,"start_date":toYYYYMMDD(target_day_from)
						,"end_date":toYYYYMMDD(target_day_to)
						,"type":operate
						,"remark": $j("#remark").val()
						,"branch_cd":<?php echo $this->branch_datas['branch_cd']; ?>						
						,"item_cds": save_item_cds
						,"tel": tel
						,"user_login":save_user_login
						,"p2":temp_p2
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Booking_Mobile_Edit"
					},
					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							var setDate = fnDayFormat(new Date($j("#slm_searchdate").val()),"%Y%m%d");
							slmSchedule._daysStaff[setDate] = data.set_data[setDate];
							$j("#slm_mainpage_regist").trigger("click");
							setDayData(setDate);
							if (operate != "deleted")	alert(data.message);
						}
			        },
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });			
		}
		
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
			$j("#slm_price").text(price);
			target_day_to = new Date(target_day_from.getTime());
			target_day_to.setMinutes(target_day_to.getMinutes() + minute);				
			$j("#end_time").text(' - '+ toHHMM(target_day_to));
	
			save_item_cds = tmp.join(",");
		}
		
		function toYYYYMMDD( date ){
			var month = date.getMonth() + 1;
			return  [date.getFullYear(),( '0' + month ).slice( -2 ),('0' + date.getDate()).slice(-2)].join( "-" ) + " "+ ('0' + date.getHours() ).slice(-2)+ ":" + ( '0' + date.getMinutes() ).slice( -2 );
		}
		
		function toHHMM( date ) {
			return ('0'+date.getHours()).slice(-2)+ ":" + ('0'+date.getMinutes()).slice(-2);
		}
        
        
</script>


<div id="slm_main" >
    <div id="slm_page_main" >
    
		<?php if ($this->_is_userlogin() ) : ?>
        <div id="slm_header_r1" class="slm_line">
            <ul>
                <?php if (is_user_logged_in() ) : ?>
                    <li><a data-role="button"  href="<?php echo wp_logout_url(get_permalink() ); ?>" ><?php _e('Log Out',SL_DOMAIN); ?></a></li>
                <?php else : ?>
                    <li><a data-role="button" id="slm_login" href="#slm-page-login"><?php _e('Log in',SL_DOMAIN); ?></a></li>
                <?php  endif; ?>
            </ul>
        </div>
        <?php  endif; ?>
        <div id="slm_header_r2" class="slm_line" >
            <ul>
            <li><a data-role="button" id="slm_prev" ><?php _e('Prev',SL_DOMAIN); ?></a></li>
            <li><a data-role="button" id="slm_today"><?php _e('Today',SL_DOMAIN); ?></a></li>
            <li><a data-role="button" id="slm_next"><?php _e('Next',SL_DOMAIN); ?></a></li>
            </ul>                
        </div>
        <div id="slm_header_r3" class="slm_line">
            <ul>
            	<li class="slm_li_3"><input type="input" id="slm_searchdate" name="slm_searchdate" placeholder="<?php _e('MM/DD/YYYY',SL_DOMAIN); ?>"></li>
                <li class="slm_li_3"><span id="slm_searchdays"></span></li>
            </ul>
            <ul>
            	<li><a data-role="button" id="slm_search" ><?php _e('When?',SL_DOMAIN); ?></a></li>	
            </ul>
        </div>
        <div id="slm_main_data" class="slm_line slm_main_line">
            <?php
            foreach ($edit_staff as $k1 => $d1) {
                echo "<ul id=\"slm_st_{$k1}\"><li class=\"slm_first_li\">".$d1['img'].'</li>';
                for($i = +$this->first_hour ; $i < $this->last_hour ; $i++ ) {
                    
                    echo '<li class="slm_time_li"><span>'.sprintf("%02d",$i).'</span></li>';
                }
                echo '</ul>';
            }
            ?>
        	<div id="slm_holiday" class="slm_holiday" ><?php _e('Holiday',SL_DOMAIN); ?></div>
            <a  data-role="button"  id="slm_regist_button" class="slm_tran_button" href="javascript:void(0)" ><?php _e('Booking',SL_DOMAIN); ?></a></li>
        </div>
        
    </div>
    
	<?php if ($this->_is_userlogin() ) : ?>
    <div id="slm_page_login"  >
        <div id="slm_login_detail" class="slm_line" >
			<ul><li><?php _e('Reservations are avalable without log in',SL_DOMAIN); ?></li></ul>
            <ul><li><input type="text" id="login_username" value="" /></li></ul>
            <ul><li><input type="password" id="login_password" value="" /></li></ul>
        </div>
        <div id="slm_footer_r2" class="slm_line">
            <ul><li><a data-role="button" id="slm_mainpage" href="#slm-page-main"><?php _e('Close',SL_DOMAIN); ?></a></li></ul>
            <ul><li><a data-role="button" id="slm_exec_login"  href="javascript:void(0)" ><?php _e('Log in',SL_DOMAIN); ?></a></li></ul>
            
        </div>
    </div>
    <?php endif; ?>

    <div id="slm_page_regist">
        <div id="slm_regist_detail" class="slm_line" >
		<ul>
        	<li class="slm_label" ><label ><?php _e('Date',SL_DOMAIN); ?>:</label></li>
			<li><span id="slm_target_day"></span></li>
        </ul>


		<?php 
			if ($this->_is_userlogin() && is_user_logged_in() && ! $this->isSalonAdmin() ) {
					if (empty($this->user_inf['tel']) ) {
						echo '<ul><li><input type="tel" id="tel" required/></li></ul>';
					}
					if (empty($this->user_inf['user_email']) ) {
						echo '<ul><li><input type="mail" id="mail"  required/></li></ul>';
					}
			}
			else {
				echo <<<EOT
					<ul><li><input type="text" id="name"  required /></li></ul>
					<ul><li><input type="tel" id="tel" required/></li></ul>
					<ul><li><input type="mail" id="mail"  required/></li></ul>
EOT;
			}
		?>           
		<ul><li  ><select id="start_time" name="start_time" class="slm_sel" >
<?php
	
		$dt = new DateTime($this->branch_datas['open_time']);
		$last_hour = substr($this->branch_datas['close_time'],0,2).":".substr($this->branch_datas['close_time'],2,2);
		$dt_max = new DateTime($last_hour);
		$echo_data =  '';
		while($dt <= $dt_max ) {
			$echo_data .= '<option value="'.$dt->format("H:i").'" >'.$dt->format("H:i").'</option>';
			$dt->modify("+".$this->branch_datas['time_step']." minutes");
		}
		echo $echo_data;	
?>
			</select></li>
            
            <li><span id="end_time" ></span></li>

            
	    </ul>

        <ul><li class="slm_li" ><select id="staff_cd" name="staff_cd" class="slm_sel">

<?php
		$echo_data = '';
		if ($this->_is_noPreference() ) {
			$echo_data .= '<option value="'.Salon_Default::NO_PREFERENCE.'">'.__('Anyone',SL_DOMAIN).'</option>';
		}
		else {
			$echo_data .= '<option value="">'.__('select please',SL_DOMAIN).'</option>';
		}
		foreach($this->staff_datas as $k1 => $d1 ) {
			$echo_data .= '<option value="'.$d1['staff_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
		}
		echo $echo_data;
?>

        	
        </select></li></ul>




		<div id="item_cds" >
<?php
		if ($this->item_datas) {
			$echo_data = "";
/*[2014/06/22]
			for($i = 0,$loop_max = count($this->item_datas); $i < $loop_max ; $i += 2 ){
				$echo_data .= '<ul class="slm_chk">';
				for($j= 0 ; $j < 2 ; $j++ ) {
					if ( $loop_max > ($i+$j) ) {
						$d1 = $this->item_datas[$i+$j];
						$edit_price = number_format($d1['price']);
						$edit_name = htmlspecialchars($d1['short_name'],ENT_QUOTES);
						$echo_data .= <<<EOT
							<li>
							<input type="checkbox" id="slm_chk_{$d1['item_cd']}" value="{$d1['item_cd']}" />
							<input type="hidden" id="check_price_{$d1['item_cd']}" value="{$d1['price']}" />
							<input type="hidden" id="check_minute_{$d1['item_cd']}" value="{$d1['minute']}" />
							<label for="slm_chk_{$d1['item_cd']}">{$edit_name}<br>({$edit_price})</label>
							</li>
EOT;
					}
				}
				$echo_data .= "</ul>";
			}
*/			
			$echo_data .= '<ul class="slm_chk">';
			for($i = 0,$loop_max = count($this->item_datas); $i < $loop_max ; $i ++ ){
				$d1 = $this->item_datas[$i];
				$edit_price = number_format($d1['price']);
          		//$edit_name = htmlspecialchars($d1['short_name'],ENT_QUOTES);
          		$edit_name = htmlspecialchars($d1['name'],ENT_QUOTES);
				$echo_data .= <<<EOT
					<li>
					<input type="checkbox" id="slm_chk_{$d1['item_cd']}" value="{$d1['item_cd']}" />
					<input type="hidden" id="check_price_{$d1['item_cd']}" value="{$d1['price']}" />
					<input type="hidden" id="check_minute_{$d1['item_cd']}" value="{$d1['minute']}" />
					<label for="slm_chk_{$d1['item_cd']}">{$edit_name}<br>({$edit_price})</label>
					</li>
EOT;
			}
			$echo_data .= "</ul>";

			echo $echo_data;
		}
?>        
		</div>

		<ul><li><textarea id="remark"  ></textarea></li></ul>
		<ul><li class="slm_label"><label  ><?php _e('price',SL_DOMAIN); ?>:</label></li>
		<span id="slm_price"></span>
		</ul>
        	
        </div>
        <div id="slm_footer_r3" class="slm_line">
            <ul>
            <li><a data-role="button" class="slm_tran_button" id="slm_mainpage_regist" href="#slm-page-main"><?php _e('Close',SL_DOMAIN); ?></a></li>
            <li><a data-role="button" class="slm_tran_button" id="slm_exec_delete"  href="javascript:void(0)" ><?php _e('Delete',SL_DOMAIN); ?></a></li>
            <li><a data-role="button" class="slm_tran_button" id="slm_exec_regist"  href="javascript:void(0)" ><?php _e('Booking',SL_DOMAIN); ?></a></li>
            </ul>
            
        </div>
    </div>
    <div id="slm_footer" class="slm_line">
            <ul><li><a id="slm_desktop" href="javascript:void(0)" ><?php _e('Desktop',SL_DOMAIN); ?></a></li></ul>
            <ul><li><a class="footer-tel" href="tel:<?php echo $this->branch_datas['tel']; ?>" ><?php _e('Telephone Here',SL_DOMAIN); ?></a></li></ul>
    </div>
    
    
<?php /*?>
  <div data-role="footer">
    Copyright 2013-2014, Kuu
  </div>r
<?php */?>
</div>	


</div>
