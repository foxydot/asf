<?php 
if (!class_exists('MSDLocationCPT')) {
	class MSDLocationCPT {
		//Properties
		var $cpt = 'location';
		//Methods
	    /**
	    * PHP 4 Compatible Constructor
	    */
		public function MSDLocationCPT(){$this->__construct();}
	
		/**
		 * PHP 5 Constructor
		 */
		function __construct(){
			global $current_screen;
        	//"Constants" setup
        	$this->plugin_url = plugin_dir_url('msd-custom-cpt/msd-custom-cpt.php');
        	$this->plugin_path = plugin_dir_path('msd-custom-cpt/msd-custom-cpt.php');
			//Actions
			add_action( 'init', array(&$this,'register_cpt_location') );
			add_action('admin_head', array(&$this,'plugin_header'));
			add_action('admin_print_scripts', array(&$this,'add_admin_scripts') );
			add_action('admin_print_styles', array(&$this,'add_admin_styles') );
			add_action('admin_footer',array(&$this,'info_footer_hook') );
			// important: note the priority of 99, the js needs to be placed after tinymce loads
			add_action('admin_print_footer_scripts',array(&$this,'print_footer_scripts'),99);
			
			//Filters
			add_filter( 'pre_get_posts', array(&$this,'custom_query') );
			add_filter( 'enter_title_here', array(&$this,'change_default_title') );
            add_image_size('location_thumb', 300, 200, TRUE);
            add_shortcode('locations-output',array(&$this,'locations_output'));
            add_shortcode('locations-contact',array(&$this,'locations_contact'));
            		}
		
		function register_cpt_location() {
		
		    $labels = array( 
		        'name' => _x( 'Locations', 'location' ),
		        'singular_name' => _x( 'Location', 'location' ),
		        'add_new' => _x( 'Add New', 'location' ),
		        'add_new_item' => _x( 'Add New Location', 'location' ),
		        'edit_item' => _x( 'Edit Location', 'location' ),
		        'new_item' => _x( 'New Location', 'location' ),
		        'view_item' => _x( 'View Location', 'location' ),
		        'search_items' => _x( 'Search Location', 'location' ),
		        'not_found' => _x( 'No location found', 'location' ),
		        'not_found_in_trash' => _x( 'No location found in Trash', 'location' ),
		        'parent_item_colon' => _x( 'Parent Location:', 'location' ),
		        'menu_name' => _x( 'Location', 'location' ),
		    );
		
		    $args = array( 
		        'labels' => $labels,
		        'hierarchical' => false,
		        'description' => 'Location',
		        'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
		        'taxonomies' => array( 'category' ),
		        'public' => true,
		        'show_ui' => true,
		        'show_in_menu' => true,
		        'menu_position' => 20,
		        
		        'show_in_nav_menus' => true,
		        'publicly_queryable' => true,
		        'exclude_from_search' => true,
		        'has_archive' => true,
		        'query_var' => true,
		        'can_export' => true,
		        'rewrite' => array('slug'=>'location','with_front'=>false),
		        'capability_type' => 'post'
		    );
		
		    register_post_type( $this->cpt, $args );
		}
		
		function plugin_header() {
			global $post_type;
			?>
		    <?php
		}
		 
		function add_admin_scripts() {
			global $current_screen;
			if($current_screen->post_type == $this->cpt){
				wp_enqueue_script('media-upload');
				wp_enqueue_script('thickbox');
				wp_register_script('my-upload', plugin_dir_url(dirname(__FILE__)).'/js/msd-upload-file.js', array('jquery','media-upload','thickbox'),FALSE,TRUE);
				wp_enqueue_script('my-upload');
			}
		}
		
		function add_admin_styles() {
			global $current_screen;
			if($current_screen->post_type == $this->cpt){
				wp_enqueue_style('thickbox');
				wp_enqueue_style('custom_meta_css',plugin_dir_url(dirname(__FILE__)).'/css/meta.css');
			}
		}	
			
		function print_footer_scripts()
		{
			global $current_screen;
			if($current_screen->post_type == $this->cpt){
				print '<script type="text/javascript">/* <![CDATA[ */
					jQuery(function($)
					{
						var i=1;
						$(\'.customEditor textarea\').each(function(e)
						{
							var id = $(this).attr(\'id\');
			 
							if (!id)
							{
								id = \'customEditor-\' + i++;
								$(this).attr(\'id\',id);
							}
			 
							tinyMCE.execCommand(\'mceAddControl\', false, id);
			 
						});
					});
				/* ]]> */</script>';
			}
		}
		function change_default_title( $title ){
			global $current_screen;
			if  ( $current_screen->post_type == $this->cpt ) {
				return __('Location Name','location');
			} else {
				return $title;
			}
		}
		
		function info_footer_hook()
		{
			global $current_screen;
			if($current_screen->post_type == $this->cpt){
				?><script type="text/javascript">
						jQuery('#postdivrich').before(jQuery('#_contact_info_metabox'));
					</script><?php
			}
		}
		

		function custom_query( $query ) {
			if(!is_admin()){
				if($query->is_main_query() && $query->is_search){
					$searchterm = $query->query_vars['s'];
					// we have to remove the "s" parameter from the query, because it will prevent the posts from being found
					$query->query_vars['s'] = "";
					
					if ($searchterm != "") {
						$query->set('meta_value',$searchterm);
						$query->set('meta_compare','LIKE');
					};
					$query->set( 'post_type', array('post','page',$this->cpt) );
					ts_data($query);
				}
				elseif( $query->is_main_query() && $query->is_archive ) {
					$query->set( 'post_type', array('post','page',$this->cpt) );
				}
			}
		}		
        
        function locations_output( $atts ){
            $args = ( shortcode_atts( array(
                'posts_per_page'   => -1,
                'offset'           => 0,
                'category'         => '',
                'category_name'    => '',
                'orderby'          => 'menu_order',
                'order'            => 'ASC',
                'include'          => '',
                'exclude'          => '',
                'meta_key'         => '',
                'meta_value'       => '',
                'post_type'        => $this->cpt,
                'post_mime_type'   => '',
                'post_parent'      => '',
                'post_status'      => 'publish',
                'suppress_filters' => true 
              ), $atts ) );
              $posts = get_posts($args);
              $cols = floor(12/count($posts));
              $ret = '';
              foreach($posts AS $post){
                  $thumb = get_the_post_thumbnail( $post->ID, 'location_thumb' );
                  $address = get_post_meta($post->ID, '_location_address', true);
                  $ret .= '<div class="col-md-'.$cols.' col-xs-12">
                    '.$thumb.'
                    <div class="location-title">'.$post->post_title.'</div>
                    <div class="location-phone">'.$address[0]['phone'].'</div>
                  </div>';
              }
              return '<div class="row">'.$ret.'</div>';
        }	


        function locations_contact( $atts ){
            $args = ( shortcode_atts( array(
                'posts_per_page'   => -1,
                'offset'           => 0,
                'category'         => '',
                'category_name'    => '',
                'orderby'          => 'menu_order',
                'order'            => 'ASC',
                'include'          => '',
                'exclude'          => '',
                'meta_key'         => '',
                'meta_value'       => '',
                'post_type'        => $this->cpt,
                'post_mime_type'   => '',
                'post_parent'      => '',
                'post_status'      => 'publish',
                'suppress_filters' => true 
              ), $atts ) );
              $posts = get_posts($args);
              $cols = floor(12/count($posts));
              $ret = '';
              foreach($posts AS $post){
                  $address = get_post_meta($post->ID, '_location_address', true);
                  $street = $address[0]['street']?$address[0]['street'].'<br />'.$address[0]['city'].', '.$address[0]['state']:'';
                  $map = strlen($street)>0?'<div class="location-map"><a href="http://maps.google.com/?q='.str_replace('<br />', ', ', $street).'" target="_blank">Get Directions</a></div>':'';
                  $street = strlen($street)>0?'<div class="location-address">'.$street.'</div>':'';

                  $ret .= '<div class="col-md-'.$cols.' col-xs-12">
                    <div class="location-title">'.$post->post_title.'</div>
                    '.$street.'
                    '.$map.'
                    <div class="location-phone">'.$address[0]['phone'].'</div>
                  </div>';
              }
              return '<div class="row">'.$ret.'</div>';
        }   
  } //End Class
} //End if class exists statement