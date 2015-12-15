<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.1
 *
 * @package    WI_Volunteer_Management
 * @subpackage WI_Volunteer_Management/Public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WI_Volunteer_Management
 * @subpackage WI_Volunteer_Management/Public
 * @author     Wired Impact <info@wiredimpact.com>
 */
class WI_Volunteer_Management_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * Only load the frontend CSS if the setting is turned on to do so.
	 *
	 * @since    0.1
	 */
	public function enqueue_styles() {

		$options = new WI_Volunteer_Management_Options();
		if( $options->get_option( 'use_css' ) == 1 ){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wi-volunteer-management-public.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.1
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wi-volunteer-management-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'wivm_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

	}

	/**
	 * Register our Volunteer Opportunities post type.
	 *
	 * Register our Volunteer Opportunities post type and set the method to static so that 
	 * it can be called during activation when we need to refresh the rewrite rules.
	 */
	public static function register_post_types(){

		$labels = array(
	      'name' 				=> __( 'Volunteer Opportunities', 'wired-impact-volunteer-management' ),
	      'singular_name' 		=> __( 'Volunteer Opportunity', 'wired-impact-volunteer-management' ),
	      'add_new' 			=> __( 'Add Volunteer Opportunity', 'wired-impact-volunteer-management' ),
	      'add_new_item' 		=> __( 'Add Volunteer Opportunity', 'wired-impact-volunteer-management' ),
	      'edit_item' 			=> __( 'Edit Volunteer Opportunity', 'wired-impact-volunteer-management' ),
	      'new_item' 			=> __( 'New Volunteer Opportunity', 'wired-impact-volunteer-management' ),
	      'all_items' 			=> __( 'All Volunteer Opportunities', 'wired-impact-volunteer-management' ),
	      'view_item' 			=> __( 'View Volunteer Opportunity', 'wired-impact-volunteer-management' ),
	      'search_items' 		=> __( 'Search Volunteer Opportunities', 'wired-impact-volunteer-management' ),
	      'not_found' 			=> __( 'No volunteer opportunities found', 'wired-impact-volunteer-management' ),
	      'not_found_in_trash' 	=> __( 'No volunteer opportunities found in trash', 'wired-impact-volunteer-management' ), 
	      'parent_item_colon' 	=> __( '', 'wired-impact-volunteer-management' ),
	      'menu_name' 			=> __( 'Volunteer Mgmt', 'wired-impact-volunteer-management' )
	    );

	    $args = array(
	      'labels'            => $labels,
	      'public'            => true,
	      'show_ui'           => true,
	      'show_in_menu'      => 'wi-volunteer-management',
	      'menu_icon'         => 'dashicons-groups',
	      'capability_type'   => 'post',
	      'supports'          => array( 'title', 'editor', 'thumbnail', 'revisions'  ),
	      'rewrite'           => array( 'slug' => apply_filters( 'wivm_opp_rewrite', 'volunteer-opportunity' ), 'with_front' => false )
	    ); 
	    
	    register_post_type( 'volunteer_opp', $args );
	}

	/**
	 * Shortcode for viewing all one-time volunteer opportunities.
	 */
	public function display_one_time_volunteer_opps(){
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$args  = array(
			'post_type' => 'volunteer_opp',
			'meta_key' => '_start_date_time',
          	'orderby' => 'meta_value_num',
          	'order'   => 'ASC',
          	'meta_query' => array(
				array( //Only if one-time opp is true
					'key'     => '_one_time_opp',
					'value'   => 1, 
					'compare' => '==',
				),
				array( //Only if event is in the future
					'key'     => '_start_date_time',
					'value'   => current_time( 'timestamp' ), 
					'compare' => '>=',
				),
				'relation' => 'AND'
			),
			'paged' => $paged
		);

		return $this->display_volunteer_opp_list( 'one-time', apply_filters( $this->plugin_name . '_one_time_opp_shortcode_query', $args ) );		
	}

	/**
	 * Shortcode for viewing all flexible volunteer opportunities.
	 */
	public function display_flexible_volunteer_opps(){
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$args = array(
			'post_type' => 'volunteer_opp',
          	'meta_query' => array(
				array( //Only if one-time opp is not true
					'key'     => '_one_time_opp',
					'value'   => 1, 
					'compare' => '!=',
				),
			),
			'paged' => $paged
		);

		return $this->display_volunteer_opp_list( 'flexible', apply_filters( $this->plugin_name . '_flexible_opp_shortcode_query', $args ) );
	}


	/**
	 * Always show read more text on the list of opportunities even if there isn't enough content.
	 *
	 * We do this by displaying '' as the default read more then adding our own read more to the end
	 * of the content.
	 *
	 * @see    hide_default_read_more()
	 * @param  string $text  The shortened text that makes up the excerpt.
	 * @return string        The final excerpt with the read more link included.
	 */
	public function always_show_read_more( $text ){

		if( get_post_type() == 'volunteer_opp' ){

			$more_text = __( 'Find Out More', 'wired-impact-volunteer-management' );
			$link = sprintf( '<a href="%1$s" class="more-link">%2$s</a>',
						get_permalink( get_the_ID() ),
						apply_filters( 'wivm_read_more_text', $more_text )
					);

			return $text . '&hellip;' . $link;
		}
		
		return $text;
	}

	/**
	 * Hide the default read more text that shows when content is longer than provided number of words.
	 *
	 * We hide this since we provide our own read more text to every item in the list instead of only
	 * the longer ones.
	 *
	 * @see    always_show_read_more()
	 * @param  string $more_text Default read more text.
	 * @return string            Empty string if volunteer opportunity or default read more if not.
	 */
	public function hide_default_read_more( $more_text ){

		if( get_post_type() == 'volunteer_opp' ){
			return '';
		}

		return $more_text;
	}

	/**
	 * Displays the volunteer opportunities lists.
	 *
	 * Displays the volunteer opportunities lists for both the one-time and flexible
	 * opportunities. It also calls template files to output the majority of the HTML.
	 * 
	 * @param  string $list_type One-time or flexible volunteer opportunities
	 * @param  array $query_args The query arguments to be used in WP_Query 
	 * @return string            HTML code to be output via a shortcode.
	 */
	public function display_volunteer_opp_list( $list_type, $query_args ){
		//We must edit the main query in order to handle pagination.
		global $wp_query;
		$temp = $wp_query;
		$wp_query = new WP_Query( $query_args );

		ob_start(); ?>
		
		<div class="volunteer-opps <?php echo $list_type; ?>">

			<?php 
			$template_loader = new WI_Volunteer_Management_Template_Loader();
			if( $wp_query->have_posts() ){

				while( $wp_query->have_posts() ){
					$wp_query->the_post();
					$template_loader->get_template_part( 'opps-list', $list_type );
				}
				
			} 
			else { ?>

				<p class="no-opps"><?php _e( 'Sorry, there are no volunteer opportunities available right now.', 'wired-impact-volunteer-management' ); ?></p>

			<?php } ?>

			<div class="navigation volunteer-opps-navigation">
        		<div class="alignleft"><?php previous_posts_link('&laquo; Previous Opportunities') ?></div>
        		<div class="alignright"><?php next_posts_link('More Opportunities &raquo;') ?></div>
        	</div>

		</div><!-- .volunteer-opps -->

		<?php
		//Reset to default query 
		$wp_query = null; 
  		$wp_query = $temp;
  		wp_reset_postdata(); 

		return ob_get_clean();
	}

	/**
	 * Show the meta info and the sign up form before and after the content on a single volunteer opp.
	 *
	 * We show this info using a filter for the_content to ensures the templates will work
	 * on a number of different themes. opp-single-meta.php and opp-single-form.php templates
	 * are both used within this function.
	 *
	 * @param  string $content The content for the given post.
	 * @return string If volunteer opp then we wrap the meta and form around the post's content.
	 */
	public function show_meta_form_single( $content ){

		if( is_singular( 'volunteer_opp' ) ){

			$template_loader = new WI_Volunteer_Management_Template_Loader();
			ob_start();

			$template_loader->get_template_part( 'opp-single', 'meta' );
			echo $content;
			$template_loader->get_template_part( 'opp-single', 'form' );

			return ob_get_clean();

		}
		else {

			return $content;

		}

	}

	/**
	 * Process the AJAX request from the volunteer opportunity sign up form.
	 *
	 * @return  int|bool The user ID if everything worked, false otherwise
	 */
	public function process_volunteer_sign_up(){
		$form_fields = array();
		parse_str( $_POST['data'], $form_fields );

		//Verify our nonce.
		if( !wp_verify_nonce( $form_fields['wivm_sign_up_form_nonce_field'], 'wivm_sign_up_form_nonce' ) ) {
			_e( 'Security Check.', 'wired-impact-volunteer-management' );
			die();
		}

		$opp = new WI_Volunteer_Management_Opportunity( $form_fields['wivm_opportunity_id'] );
		if( $opp->should_allow_rvsps() == true ){

			//Add or update the new volunteer user
			$user = new WI_Volunteer_Management_Volunteer( null, $form_fields );

			//RSVP this volunteer for the opportunity
			$rsvp = new WI_Volunteer_Management_RSVP( $user->ID, $form_fields['wivm_opportunity_id'] );

			//If the person hadn't already RSVPed then send out the signup emails.
			if( $rsvp->rsvped == true ){
				$email 	= new WI_Volunteer_Management_Email( $opp, $user );
				$email->send_volunteer_signup_email();
				$email->send_admin_signup_email();
				$result = 'rsvped';
			}
			else {
				$result = 'already_rsvped';
			}

		}
		//If RSVPs have been closed
		else {
			$result = 'rsvp_closed';
		}
		
		//Return a message which tells us what messages to show on the frontend
 		echo $result; 
 		
 		die(); //Must use die() when using AJAX
	}

} //class WI_Volunteer_Management_Public