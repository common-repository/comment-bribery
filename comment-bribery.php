<?php
/*
Plugin Name: Comment Bribery
Plugin URI: http://cwantwm.co.uk/forum/comment-bribery-f3.html
Description: Comment Bribery
Author: Rob Holmes
Author URI: http://www.cwantwm.co.uk/rob-holmes
Version: 0.0.1
Tags: Wordpress
License: GPL2
*/

/*  Copyright 2011  Rob Holmes  (email : rob@onemanonelaptop.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$cwantwm = ABSPATH . 'wp-content/plugins/cwantwm/class.php';
if (file_exists($cwantwm)) {
	include_once($cwantwm);
} else {
function comment_bribery_admin_notice(){
    echo '<div class="updated">
       <p>Comment Bribery requires the <a href="http://wordpress.org/extend/plugins/cwantwm/">Cwantwm Plugin Framework</a> which is available from the wordpress repository.</p>
    </div>';
}
add_action('admin_notices', 'comment_bribery_admin_notice');

}

if (class_exists('Cwantwm')) {
class CommentBribery extends Cwantwm {

	// Called via the class constructor
	function plugin_construct() {
		// The name of the options table varilable
		$this->options = 'comment_bribery_options';

		// Title used on plugin options page
		$this->title = 'Comment Bribery';
	$this->name = 'comment-bribery';
		// Forum RSS feed url
		$this->forum = 'http://cwantwm.co.uk/forum/feed.php?mode=topics';
		
		$this->triggervalues = array('Disabled','5','10','15','20','25','30','35','40','45','50','55','60','65','70','75','80','85','90','95','100');
		
		// Check to see if we are sending a prize after each comment
		add_action('comment_post', array(&$this, 'check_comments'));
		add_action('wp_set_comment_status', array(&$this, 'check_comments'));
		
		$options = get_option($this->options);
		if ($options['location'] == '1') {
			add_filter('the_content', array(&$this, 'promotional_text'));
		} else if ($options['location'] == '2'){
			add_action('comment_form_before', array(&$this, 'print_promotional_text'));
		} else if ($options['location'] == '3'){
			add_action('comment_form_after', array(&$this, 'print_promotional_text'));
		}
	}

	// Define the defaults for the plugin
	function plugin_defaults () {
		return array ( 'location' => '0', 'promotional_text' => '', 'email_name' => '', 'email_from' => '', 'visibility_method' => '0', 'visibility_list' => '', 'prize_1_trigger' => '0', 'prize_1_subject' => '', 'prize_1_html' => '', 'prize_1_attachment' => '', 'prize_2_trigger' => '0', 'prize_2_subject' => '', 'prize_2_html' => '', 'prize_2_attachment' => '', 'prize_3_trigger' => '0', 'prize_3_subject' => '', 'prize_3_html' => '', 'prize_3_attachment' => '', 'prize_4_trigger' => '0', 'prize_4_subject' => '', 'prize_4_html' => '', 'prize_4_attachment' => '', 'prize_5_trigger' => '0', 'prize_5_subject' => '', 'prize_5_html' => '', 'prize_5_attachment' => '', );
	}

	// Called by the plugins generic 'admin_init' hook
	function plugin_initiate() {
	
		// Add the logical admin sections
		add_settings_section('admin_section_1', '', array(&$this, 'section_cb'), $this->page );
		add_settings_section('admin_section_2', '',  array(&$this,'section_cb'), $this->page );
		add_settings_section('admin_section_3', '',  array(&$this,'section_cb'), $this->page );
		add_settings_section('admin_section_4', '',  array(&$this,'section_cb'), $this->page );
		add_settings_section('admin_section_5', '',  array(&$this,'section_cb'), $this->page );
		add_settings_section('admin_section_6', '',  array(&$this,'section_cb'), $this->page );
		add_settings_section('admin_section_7', '',  array(&$this,'section_cb'), $this->page );
		
		// Add a metabox for each of the above sections
		add_meta_box('admin-section-1','General Settings', array(&$this, 'admin_section_builder'), $this->page, 'normal', 'core',array('section' => 'admin_section_1'));
		
		// Visibility
		$this->add_visibility();
		
		// Define the main meta boxes
		add_meta_box('admin-section-2','Prize Email #1', array(&$this, 'admin_section_builder'), $this->page, 'normal', 'core',array('section' => 'admin_section_2'));
		add_meta_box('admin-section-3','Prize Email #2', array(&$this, 'admin_section_builder'), $this->page, 'normal', 'core',array('section' => 'admin_section_3'));
		add_meta_box('admin-section-4','Prize Email #3', array(&$this, 'admin_section_builder'), $this->page, 'normal', 'core',array('section' => 'admin_section_4'));
		add_meta_box('admin-section-5','Prize Email #4', array(&$this, 'admin_section_builder'), $this->page, 'normal', 'core',array('section' => 'admin_section_5'));
		add_meta_box('admin-section-6','Prize Email #5', array(&$this, 'admin_section_builder'), $this->page, 'normal', 'core',array('section' => 'admin_section_6'));
		
		
		// Plugin specific select box options
		$locations = array('0'=>'Disabled', '1'=>'Below Content', '2'=>'Above Comments',  '3'=>'Below Comments');
		
		// Add some settings
		add_settings_field('location', 'Promotional Text Location', array(&$this, 'select'), $this->page , 'admin_section_1',
			array('id' => 'location','description' => 'Select where you would like the promotional text to appear.', 'select' => $locations, 'multiple'=>false));
		add_settings_field('promotional_text', 'Promotional Text', array(&$this, 'wysiwyg'), $this->page , 'admin_section_1',
			array('id' => 'promotional_text','description' => 'Use the wysiwyg editor above to create your promotional text.'));	
		add_settings_field('email_name', 'Email From (Name)', array(&$this, 'text'), $this->page , 'admin_section_1',
			array('id' => 'email_name','description' => 'You can specify the senders name that outgoing emails will display.', 'placeholder'=>'Your Name'));	
		add_settings_field('email_from', 'Email From (Address)', array(&$this, 'text'), $this->page , 'admin_section_1',
			array('id' => 'email_from','description' => 'You can specify the email address that outgoing emails should be sent from.', 'placeholder'=>'email@address.com'));		
		
		// Prize email #1
		add_settings_field('prize_1_trigger', ' Number of Comments', array(&$this, 'select'), $this->page , 'admin_section_2',
				array('id' => 'prize_1_trigger','description' => 'The number of approved comments a user must make before this email is sent', 'select' => $this->triggervalues, 'multiple'=>false)	);
		add_settings_field('prize_1_subject', ' Email Subject', array(&$this, 'text'), $this->page , 'admin_section_2',
				array('id' => 'prize_1_subject','description' => '','placeholder'=>'')	);
		add_settings_field('prize_1_html', ' Email Content', array(&$this, 'textarea'), $this->page , 'admin_section_2',
				array('id' => 'prize_1_html','description' => ' ')	);	
		add_settings_field('prize_1_attachment', ' Email Attachment', array(&$this, 'attachment'), $this->page , 'admin_section_2',
				array('id' => 'prize_1_attachment','description' => 'Enter a path or upload a file for the attachment.')	);	
				
		// Prize email #2		
		add_settings_field('prize_2_trigger', ' Number of Comments', array(&$this, 'select'), $this->page , 'admin_section_3',
				array('id' => 'prize_2_trigger','description' => 'The number of approved comments a user must make before this email is sent', 'select' => $this->triggervalues, 'multiple'=>false)	);
		add_settings_field('prize_2_subject', ' Email Subject', array(&$this, 'text'), $this->page , 'admin_section_3',
				array('id' => 'prize_2_subject','description' => '','placeholder'=>'')	);
		add_settings_field('prize_2_html', ' Email Content', array(&$this, 'textarea'), $this->page , 'admin_section_3',
				array('id' => 'prize_2_html','description' => ' ')	);	
		add_settings_field('prize_2_attachment', ' Email Attachment', array(&$this, 'attachment'), $this->page , 'admin_section_3',
				array('id' => 'prize_2_attachment','description' => 'Enter a path or upload a file for the attachment.')	);	
						
		// Prize email #3
		add_settings_field('prize_3_trigger', ' Number of Comments', array(&$this, 'select'), $this->page , 'admin_section_4',
				array('id' => 'prize_3_trigger','description' => 'The number of approved comments a user must make before this email is sent', 'select' => $this->triggervalues, 'multiple'=>false)	);
		add_settings_field('prize_3_subject', ' Email Subject', array(&$this, 'text'), $this->page , 'admin_section_4',
				array('id' => 'prize_3_subject','description' => '','placeholder'=>'')	);
		add_settings_field('prize_3_html', ' Email Content', array(&$this, 'textarea'), $this->page , 'admin_section_4',
				array('id' => 'prize_3_html','description' => ' ')	);	
		add_settings_field('prize_3_attachment', ' Email Attachment', array(&$this, 'attachment'), $this->page , 'admin_section_4',
				array('id' => 'prize_3_attachment','description' => 'Enter a path or upload a file for the attachment.')	);	
			
		// Prize email #4	
		add_settings_field('prize_4_trigger', ' Number of Comments', array(&$this, 'select'), $this->page , 'admin_section_5',
				array('id' => 'prize_4_trigger','description' => 'The number of approved comments a user must make before this email is sent', 'select' => $this->triggervalues, 'multiple'=>false)	);
		add_settings_field('prize_4_subject', ' Email Subject', array(&$this, 'text'), $this->page , 'admin_section_5',
				array('id' => 'prize_4_subject','description' => '','placeholder'=>'')	);
		add_settings_field('prize_4_html', ' Email Content', array(&$this, 'textarea'), $this->page , 'admin_section_5',
				array('id' => 'prize_4_html','description' => ' ')	);	
			add_settings_field('prize_4_attachment', ' Email Attachment', array(&$this, 'attachment'), $this->page , 'admin_section_5',
				array('id' => 'prize_4_attachment','description' => 'Enter a path or upload a file for the attachment.')	);	
						
		// Prize email #5
		add_settings_field('prize_5_trigger', ' Number of Comments', array(&$this, 'select'), $this->page , 'admin_section_6',
				array('id' => 'prize_5_trigger','description' => 'The number of approved comments a user must make before this email is sent', 'select' => $this->triggervalues, 'multiple'=>false)	);
		add_settings_field('prize_5_subject', ' Email Subject', array(&$this, 'text'), $this->page , 'admin_section_6',
				array('id' => 'prize_5_subject','description' => '','placeholder'=>'')	);
		add_settings_field('prize_5_html', ' Email Content', array(&$this, 'textarea'), $this->page , 'admin_section_6',
				array('id' => 'prize_5_html','description' => ' ')	);	
			add_settings_field('prize_5_attachment', ' Email Attachment', array(&$this, 'attachment'), $this->page , 'admin_section_6',
				array('id' => 'prize_5_attachment','description' => 'Enter a path or upload a file for the attachment.')	);	
						
	} // function

	function promotional_text($text) {
		$options = get_option($this->options);
		if (!$this->visible()) {return $text;}
		return  "<div id='bribery'><div id='bribery-inner'>" . $text . stripslashes($options['promotional_text']) . "</div></div>" ;
	} // function

	function print_promotional_text() {
		$options = get_option($this->options);
		if (!$this->visible()) {return;}
		print  "<div id='bribery'><div id='bribery-inner'>" . stripslashes($options['promotional_text']) . "</div></div>";
	} // function

	function check_comments($id) {
		global $wpdb;
		$options = get_option($this->options);
		
		// get the comment from the database
		$comment = get_comment($id); 
			
		if ($comment->comment_approved == '1') {
			
			// Get the details
			$email = $comment->comment_author_email;
			$name = $comment->comment_author;
			
			// If the commenter is not a user
			if ($comment->user_id != 0) {
				$countcomments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_author_email = %s AND comment_approved = '1'", $comment->comment_author_email));
			} else {
				$countcomments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE user_id = %d AND comment_approved = '1'", $comment->user_id));
			}
	
			// Loop through the five prizes
			for ($i = 1; $i <= 5; $i++) {
				print $this->triggervalues[$options['prize_' . $i. '_trigger']] . " : " . $countcomments . "<br/>";
				
				if ($this->triggervalues[$options['prize_' . $i. '_trigger']] == $countcomments) {
					// Set the headers
					$headers= "MIME-Version: 1.0\r\n" . 'From: ' . $options['email_name'] . ' <' . $options['email_from'] . ">\r\n" . "Content-Type: text/html; charset=\"" . get_option('blog_charset') . "\"\r\n";		
					
					// check if attachment is valid
					$attachments = array(str_replace(get_site_url().'/',ABSPATH ,$options['prize_' . $i. '_attachment']));
					
					if (!file_exists($attachments[0])) {
					   $attachments = array();
					} 
				
					// Send the email
					$send = wp_mail('"' . $name . '" <' . $email . '>', $options['prize_'. $i . '_subject'], $options['prize_' . $i. '_html'], $headers,  $attachments);
				
				}

			}
		}
	} // function

} // class
$commentbribery = new CommentBribery;
}
?>