<?php
/*
Plugin Name: GravityWP - CSS Selector
Plugin URI: https://gravitywp.com/plugins/css-selector/
Description: Easily select a Gravity Forms CSS Ready Class for your form fields.
Author: GravityWP
Version: 1.0.1
Author URI: http://gravitywp.com
License: GPL2
Text Domain: gravitywp-css-selector
Domain Path: /languages
*/

// Tribute to Brad Vincent for making the first version of this plugin https://profiles.wordpress.org/bradvin
// Tribute to Bryan Willis for making a revised version of this plugin available on Github: https://wordpress.org/support/users/codecandid/


if (class_exists( 'RGForms' )) {
    add_action('gform_editor_js', 'gwp_css_selector_render_editor_js');
}
function gwp_css_selector_render_editor_js(){
	
	$custom_start = "";
	
	$custom_css = apply_filters( 'gwp_css_selector_add_custom_css', $custom_start );

    $modal_html = "
		<div id='css_ready_modal'><style>
		#css_ready_selector,a.gwp_css_acc_link,a.gwp_css_link {text-decoration:none}#css_ready_selector {display:inline-block}#css_ready_modalh4{margin-bottom:2px}.gwp_css_accordian{display:-ms-flexbox;display:-webkit-box;display:flex;-ms-flex-direction:row;-webkit-box-orient:horizontal;-webkit-box-direction:normal;flex-direction:row;-ms-flex-wrap:wrap;flex-wrap:wrap;-ms-flex-pack:center;-webkit-box-pack:center;justify-content:center;-ms-flex-line-pack:justify;align-content:space-between;-ms-flex-align:center;-webkit-box-align:center;align-items:center;margin:5px 0}a.gwp_css_acc_link{font-weight:700;display:block;padding:5px;text-align:left;background:#d2e0eb;border:1px solid #ddd;color:#47759B}a.gwp_css_link{margin:2px;text-align:center;padding:3px; padding-left:10px;padding-right:10px;border:1px solid #aaa;background:#eee;display:inline-block;box-sizing:border-box;-ms-flex-order:0;-webkit-box-ordinal-group:1;order:0;-ms-flex:1 0 auto;-webkit-box-flex:1;flex:1 0 auto;-ms-flex-item-align:stretch;align-self:stretch}a.gwp_css_link:hover{background:#ddd}ul.gwp_css_ul{margin:0;padding:0}a.gwp_css_link_doc{margin:2px;text-align:center;padding:3px;border:1px solid #aaa;background:#eee;display:inline-block;box-sizing:border-box;-ms-flex-order:0;-webkit-box-ordinal-group:1;order:0;-ms-flex:1 0 auto;-webkit-box-flex:1;flex:1 0 auto;-ms-flex-item-align:stretch;align-self:stretch; text-decoration:none;}a.gwp_css_link_doc:hover{background:#ddd}ul.gwp_css_ul{margin:0;padding:0} ul.gwp_css_ul li{margin:2px;padding:0}.gwp_title {margin-top: 12px;margin-bottom: 10px;font-weight: bold;}</style>              
		<div class='gwp_title'>" . __( 'Select a CSS ready class', 'gravitywp-css-selector' ) . "</div>
		<ul class='gwp_css_ul'>
		" . $custom_css . "
		<li>
		  <a class='gwp_css_acc_link' href='#'>" . __( 'Two Columns (2)', 'gravitywp-css-selector' ) . "</a>
		  <div class='gwp_css_accordian'>
			<a class='gwp_css_link' href='#' rel='gf_left_half' title='gf_left_half'>" . __( 'Left Half', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' href='#' rel='gf_right_half' title='gf_right_half'>" . __( 'Right Half', 'gravitywp-css-selector' ) . "</a>
		  </div>
		</li>
		<li>
		  <a class='gwp_css_acc_link' href='#'>" . __( 'Three Columns (3)', 'gravitywp-css-selector' ) . "</a>
		  <div class='gwp_css_accordian'>
			<a class='gwp_css_link' href='#' rel='gf_left_third' title='gf_left_third'>" . __( 'Left Third', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' href='#' rel='gf_middle_third' title='gf_middle_third'>" . __( 'Middle Third', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' href='#' rel='gf_right_third' title='gf_right_third'>" . __( 'Right Third', 'gravitywp-css-selector' ) . "</a>
		  </div>
		</li>
		<li>
		  <a class='gwp_css_acc_link' href='#'>" . __( 'Four Columns (4)', 'gravitywp-css-selector' ) . "</a>
		  <div class='gwp_css_accordian'>
			<a class='gwp_css_link' href='#' rel='gf_first_quarter' title='gf_first_quarter'>" . __( '1st Quarter', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' href='#' rel='gf_second_quarter' title='gf_second_quarter'>" . __( '2nd Quarter', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' href='#' rel='gf_third_quarter' title='gf_third_quarter'>" . __( '3rd Quarter', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' href='#' rel='gf_fourth_quarter' title='gf_fourth_quarter'>" . __( '4th Quarter', 'gravitywp-css-selector' ) . "</a>
		  </div>
		</li>
		<li>
		  <a class='gwp_css_acc_link' href='#'>" . __( 'Radio Buttons & Checkboxes', 'gravitywp-css-selector' ) . "</a>
		  <div class='gwp_css_accordian'>                
			<a class='gwp_css_link' rel='gf_list_inline' title='gf_list_inline' href='#'> " . __( 'Inline', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_list_2col' title='gf_list_2col' href='#'>2 " . __( 'Columns', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_list_3col' title='gf_list_3col' href='#'>3 " . __( 'Columns', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_list_4col' title='gf_list_4col' href='#'>4 " . __( 'Columns', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_list_5col' title='gf_list_5col' href='#'>5 " . __( 'Columns', 'gravitywp-css-selector' ) . "</a>
			</div>
			<div class='gwp_css_accordian'>                   
			<a class='gwp_css_link' rel='gf_list_height_25' title='gf_list_height_25' href='#'>25px " . __( 'Height', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_list_height_50' title='gf_list_height_50' href='#'>50px " . __( 'Height', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_list_height_75' title='gf_list_height_75' href='#'>75px " . __( 'Height', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_list_height_100' title='gf_list_height_100' href='#'>100px " . __( 'Height', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_list_height_125' title='gf_list_height_125' href='#'>125px " . __( 'Height', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_list_height_150' title='gf_list_height_150' href='#'>150px " . __( 'Height', 'gravitywp-css-selector' ) . "</a>
		  </div>
		</li>
		<li>
		  <a class='gwp_css_acc_link' href='#'>" . __( 'Others', 'gravitywp-css-selector' ) . "</a>
		  <div class='gwp_css_accordian'>                   
			<a class='gwp_css_link' rel='gf_invisible' title='gf_invisible' href='#'>" . __( 'Invisible Field', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_scroll_text' title='gf_scroll_text' href='#'>" . __( 'Scrolling Paragraph Text', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_hide_ampm' title='gf_hide_ampm' href='#'>" . __( 'Hide Time am/pm', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='gf_hide_charleft' title='gf_hide_charleft' href='#'>" . __( 'Hide Character Counter', 'gravitywp-css-selector' ) . "</a>
		  </div>
		</li>
		<li>
		  <a class='gwp_css_acc_link' href='#'>" . __( 'Gravity PDF', 'gravitywp-css-selector' ) . "</a>
		  <div class='gwp_css_accordian'>                   
			<a class='gwp_css_link' rel='exclude' title='exclude' href='#'>" . __( 'Exclude from PDF', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link' rel='pagebreak' title='pagebreak' href='#'>" . __( 'Pagebreak', 'gravitywp-css-selector' ) . "</a>					
		  </div>
		</li>
		<li>
		  <a class='gwp_css_acc_link' href='#'>" . __( 'Gravity Wiz (Perks)', 'gravitywp-css-selector' ) . "</a>
		  <div class='gwp_css_accordian'>                   
			<a class='gwp_css_link' rel='copy-1-to-2' title='copy-1-to-2' href='#'>" . __( 'Copy Cat', 'gravitywp-css-selector' ) . "</a>
		  </div>
		</li>
		</ul>
		<ul class='gwp_css_ul'>
		<li>
		  <a class='gwp_css_acc_link' href='#'>" . __( 'Help', 'gravitywp-css-selector' ) . "</a>
		  <div class='gwp_css_accordian'>
			<a class='gwp_css_link_doc' href='https://gravitywp.com/plugins/css-selector' target='_blank'>" . __( 'Add custom css', 'gravitywp-css-selector' ) . "</a>
			<a class='gwp_css_link_doc' href='https://docs.gravityforms.com/css-ready-classes/' target='_blank'>" . __( 'Official Gravity Forms Documentation', 'gravitywp-css-selector' ) . "</a>
			</div>
			<div class='gwp_css_accordian'>
			<p>" . __( 'Tip: click twice, add css, close window', 'gravitywp-css-selector' ) . "</p>
			</div>
		</li>
		</ul>
		
		";
      ?>
        <script>    
          function removeTokenFromInput(input, tokenPos, seperator) {
          	var text = input.val();
          	var tokens = text.split(seperator);
          	var newText = '';
          	for (i = 0; i < tokens.length; i++) {
          		if (tokens[i].replace(' ', '').replace(seperator, '') == '') {
          			continue;
          		}
          		if (i != tokenPos) {
          			newText += (tokens[i].trim() + seperator);
          		}
          	}
          	input.val(fixTokens(newText, seperator));
          }
          function addTokenToInput(input, tokenToAdd, seperator) {
          	var text = input.val().trim();
          	if (text == '') {
          		input.val(tokenToAdd);
          	} else {
          		if (!tokenExists(input, tokenToAdd, seperator)) {
          			input.val(fixTokens(text + seperator + tokenToAdd, seperator));
          		}
          	}
          }
          function fixTokens(tokens, seperator) {
          	var text = tokens.trim();
          	var tokens = text.split(seperator);
          	var newTokens = '';
          	for (i = 0; i < tokens.length; i++) {
          		var token = tokens[i].trim().replace(seperator, '');
          		if (token == '') {
          			continue;
          		}
          		newTokens += (token + seperator);
          	}
          	return newTokens;
          }
          function tokenExists(input, tokenToCheck, seperator) {
          	var text = input.val().trim();
          	if (text == '') return false;
          	var tokens = text.split(seperator);
          	for (i = 0; i < tokens.length; i++) {
          		var token = tokens[i].trim().replace(seperator, '');
          		if (token == '') {
          			continue;
          		}
          		if (token == tokenToCheck) {
          			return true;
          		}
          	}
          	return false;
          }
          jQuery(document).bind("gform_load_field_settings", function(event, field, form) {
          	if (jQuery("#css_ready_selector").length == 0) {
          		//add some html after the CSS Class Name input
          		var $select_link = jQuery("<a id='css_ready_selector' class='thickbox' href='#TB_inline?width=500&height=550&inlineId=css_ready_modal'><span class='dashicons dashicons-text'></span></a>");
          		var $modal = jQuery("<?php echo preg_replace( '/\s*[\r\n\t]+\s*/', '', $modal_html ); ?>").hide();
          		jQuery(".css_class_setting").append($select_link).append($modal);
          		jQuery(".gwp_css_accordian").hide();
          		$select_link.click(function(e) {
          			e.preventDefault();
          			var $m = jQuery("#css_ready_modal");
          			$m.find(".gwp_css_acc_link").unbind("click").click(function(e) {
          				e.preventDefault();
          				jQuery('.gwp_css_accordian:visible').slideUp();
          				jQuery(this).parent("li:first").find(".gwp_css_accordian").slideDown();
          			});
          			var $links = $m.find(".gwp_css_link");
          			$links.unbind("click").click(function(e) {
          				e.preventDefault();
          				var css = jQuery(this).attr("rel");
          				addTokenToInput(jQuery("#field_css_class"), css, ' ');
          				SetFieldProperty('cssClass', jQuery("#field_css_class").val().trim());
          			});
          			$links.unbind("dblclick").dblclick(function(e) {
          				e.preventDefault();
          				var css = jQuery(this).attr("rel");
          				addTokenToInput(jQuery("#field_css_class"), css, ' ');
          				SetFieldProperty('cssClass', jQuery("#field_css_class").val().trim());
          				tb_remove();
          			});
          			tb_show('', this.href, false);
          		});
          	}
          });
        </script>
      <?php
}
// Translation files of the plugin
add_action('plugins_loaded', 'gwp_css_selector_load_textdomain');
function gwp_css_selector_load_textdomain() {
	load_plugin_textdomain( 'gravitywp-css-selector', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
}