<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Mail_Page extends Salon_Page {

	private $set_items = null;

	private $config = null;

	

	public function __construct($is_multi_branch) {
		parent::__construct($is_multi_branch);
		$this->set_items = array('send_mail_text_on_mail','regist_mail_text_on_mail','mail_from_on_mail','mail_returnPath_on_mail','target_mail_patern','send_mail_subject','regist_mail_subject','information_mail_text_on_mail','information_mail_subject','mail_bcc');
	}
	  
	public function set_config_datas($config) {
		$this->config = $config;
	}
	
	public function show_page() {
?>

	<script type="text/javascript" charset="utf-8">

		var $j = jQuery;
		<?php parent::echoClientItem($this->set_items); ?>	
		$j(document).ready(function() {
			$j("#salon_button_div input[type=button]").addClass("sl_button");
			<?php parent::echoSetItemLabel(false); ?>
			for(index in check_items) {
				if (check_items[index]) {
					var diff = 0;
					var id = check_items[index]["id"];
					$j("#"+id+"_lbl").children(".small").text(check_items[index]["tips"]);
					if ($j("#"+id)[0].tagName.toUpperCase() == "TEXTAREA" ) diff = 5;
					else {
						if ( $j("#"+id).parent().hasClass("config_item_wrap") ) {
							diff = $j("#"+id+"_lbl").outerHeight(true) - $j("#"+id).parent().outerHeight(true);
						}
						else {
							diff = $j("#"+id+"_lbl").outerHeight(true) - $j("#"+id).outerHeight(true);
						}
					}
					if (diff > 0 ) {
						diff += <?php echo parent::INPUT_BOTTOM_MARGIN; ?>+5;
						$j("#"+id).attr("style","margin-bottom: "+diff+"px;");
						$j("#"+id+"_lbl").children(".small").attr("style","text-align:left;");
					}
				}
			}

            $j("#button_update").click(function()	{
				fnClickUpdate();
			});
            $j("#target_mail_patern").change(function()	{
				
				$j(".sl_mail_wrap").hide();
				$j("#sl_mail_warp_"+$j(this).val()).show();
				
				$j("#sl_mail_wrap_bcc").hide();
				if ($j(this).val() == "information" ) {
					$j("#sl_mail_wrap_bcc").show();
				}
				
			});

			$j("#send_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n', $this->config['SALON_CONFIG_SEND_MAIL_TEXT']); ?>");
			$j("#regist_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n', $this->config['SALON_CONFIG_SEND_MAIL_TEXT_USER']); ?>");
			$j("#information_mail_text").val("<?php echo str_replace(array("\r\n","\r","\n"), '\n', $this->config['SALON_CONFIG_SEND_MAIL_TEXT_INFORMATION']); ?>");

			$j("#send_mail_subject").val("<?php echo $this->config['SALON_CONFIG_SEND_MAIL_SUBJECT']; ?>");
			$j("#regist_mail_subject").val("<?php echo $this->config['SALON_CONFIG_SEND_MAIL_SUBJECT_USER']; ?>");
			$j("#information_mail_subject").val("<?php echo $this->config['SALON_CONFIG_SEND_MAIL_SUBJECT_INFORMATION']; ?>");

			$j("#mail_from").val("<?php echo $this->config['SALON_CONFIG_SEND_MAIL_FROM']; ?>");
			$j("#mail_returnPath").val("<?php echo $this->config['SALON_CONFIG_SEND_MAIL_RETURN_PATH']; ?>");
			
			$j("#mail_bcc").val("<?php echo $this->config['SALON_CONFIG_SEND_MAIL_BCC']; ?>");
			
			$j("#target_mail_patern").val("confirm").change();

							

		});


		function fnClickUpdate() {
			if ( ! checkItem("data_detail") ) return false;

			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slmail", 
					dataType : "json",
					data: {
						"config_mail_text":$j("#send_mail_text").val()						
						,"config_mail_text_user":$j("#regist_mail_text").val()						
						,"config_mail_text_information":$j("#information_mail_text").val()						
						,"config_mail_subject":$j("#send_mail_subject").val()						
						,"config_mail_subject_user":$j("#regist_mail_subject").val()						
						,"config_mail_subject_information":$j("#information_mail_subject").val()						
						,"config_mail_from":$j("#mail_from").val()	
						,"config_mail_returnPath":$j("#mail_returnPath").val()	
						,"config_mail_bcc":$j("#mail_bcc").val()	
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Mail_Edit"

					},
					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							alert(data.message);
							location.reload();
						}
			        },
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });			
		}
	
		<?php parent::echoCheckClinet(array('chk_required','num','lenmax','chkMail')); ?>		
		
	</script>

	<h2 id="sl_admin_title"><?php _e('Mail Setting',SL_DOMAIN); ?></h2>
    <div id="salon_button_div" >
	<input id="button_update" type="button" value="<?php _e('update',SL_DOMAIN); ?>"/>
	</div>
	<div id="data_detail" >
	
		
		<input type="text" id="mail_from" />
		<input type="text" id="mail_returnPath" />
		<select id="target_mail_patern" >
			<option value="confirm" ><?php _e('The Mail to Confirming Notice to the Client',SL_DOMAIN); ?></option>
			<option value="regist" ><?php _e('The Mail to respond to the Client newly registered as a Member',SL_DOMAIN); ?></option>
			<option value="information" ><?php _e('The Mail to information to the registerd staff member',SL_DOMAIN); ?></option>
		
		</select>
        <div id="sl_mail_wrap_bcc" >
        <textarea id="mail_bcc" ></textarea>
        </div>
		<div id="sl_mail_warp_confirm" class="sl_mail_wrap" >
			<input id="send_mail_subject"  />
			<textarea id="send_mail_text" class="sl_mail_area" ></textarea>
		</div>
		<div id="sl_mail_warp_regist" class="sl_mail_wrap">
			<input id="regist_mail_subject"  />
			<textarea id="regist_mail_text"  class="sl_mail_area"></textarea>
		</div>	
		<div id="sl_mail_warp_information" class="sl_mail_wrap">
			<input id="information_mail_subject"  />
			<textarea id="information_mail_text"  class="sl_mail_area"></textarea>
		</div>	
        
 


		<div class="spacer"></div>
	</div>
	

<?php  
	}	//show_page
}		//class

