<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       www.data-lord.se
 * @since      1.0.0
 *
 * @package    Wcms19_Year_Book
 * @subpackage Wcms19_Year_Book/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wcms19_Year_Book
 * @subpackage Wcms19_Year_Book/includes
 * @author     Fredrik <fl@thehiveresistance.com>
 */
class Wcms19_Year_Book {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wcms19_Year_Book_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WCMS19_YEAR_BOOK_VERSION' ) ) {
			$this->version = WCMS19_YEAR_BOOK_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wcms19-year-book';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->register_filter_the_content();
		$this->add_action_init();
		$this->init_acf();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wcms19_Year_Book_Loader. Orchestrates the hooks of the plugin.
	 * - Wcms19_Year_Book_i18n. Defines internationalization functionality.
	 * - Wcms19_Year_Book_Admin. Defines all hooks for the admin area.
	 * - Wcms19_Year_Book_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcms19-year-book-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wcms19-year-book-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wcms19-year-book-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wcms19-year-book-public.php';

		/**
		 * Include Advanced Custom Fields plugin
		 * 
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/acf/acf.php';

		$this->loader = new Wcms19_Year_Book_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wcms19_Year_Book_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wcms19_Year_Book_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wcms19_Year_Book_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wcms19_Year_Book_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Add functions to be run during the 'init' hook
	 * 
	 * @since
	 */
	public function add_action_init() {
		// Add hook for registering CPT
		add_action( 'init', [$this, 'register_cpts'] );

		// Add hook for registering CT
		add_action( 'init', [$this, 'register_cts'] );
	
	}

	/**
	 * Initialize Advanced Custom Fields plugin
	 * 
	 * @since
	 */
	public function init_acf() {
		add_filter('acf/settings/url', function() {
			return plugin_dir_url(__FILE__) . 'acf/';

		});

		add_filter('acf/settings/show_admin', function() {
			return false;
		});

		// Register Field Group 'Student Details'
		if( function_exists('acf_add_local_field_group') ):

			acf_add_local_field_group(array(
				'key' => 'group_5ee86f78ca53a',
				'title' => 'Student Details',
				'fields' => array(
					array(
						'key' => 'field_5ee86fa1c5a89',
						'label' => 'Attendance',
						'name' => 'attendance',
						'type' => 'number',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '30',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => 'Attendance in percent',
						'prepend' => '',
						'append' => '%',
						'min' => 0,
						'max' => 100,
						'step' => '',
					),
					array(
						'key' => 'field_5ee87011c5a8a',
						'label' => 'Detention Hours',
						'name' => 'detention_hours',
						'type' => 'number',
						'instructions' => 'Number of hours in detention',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '30',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => 'hours',
						'min' => 0,
						'max' => '',
						'step' => '',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'wcms19yb_student',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));
			
			endif;
	}

	/**
	 * Register Custom Post Types
	 * 
	 * @since
	 */
	public function register_cpts() {
		/**
		 * Post Type: Year Book Students.
		 */

		$labels = [
			"name" => __( "Year Book Students", "wcms19-year-book" ),
			"singular_name" => __( "Year Book Student", "wcms19-year-book" ),
			"menu_name" => __( "My Year Book Students", "wcms19-year-book" ),
			"all_items" => __( "All Year Book Students", "wcms19-year-book" ),
			"add_new" => __( "Add new", "wcms19-year-book" ),
			"add_new_item" => __( "Add new Year Book Student", "wcms19-year-book" ),
			"edit_item" => __( "Edit Year Book Student", "wcms19-year-book" ),
			"new_item" => __( "New Year Book Student", "wcms19-year-book" ),
			"view_item" => __( "View Year Book Student", "wcms19-year-book" ),
			"view_items" => __( "View Year Book Students", "wcms19-year-book" ),
			"search_items" => __( "Search Year Book Students", "wcms19-year-book" ),
			"not_found" => __( "No Year Book Students found", "wcms19-year-book" ),
			"not_found_in_trash" => __( "No Year Book Students found in trash", "wcms19-year-book" ),
			"parent" => __( "Parent Year Book Student:", "wcms19-year-book" ),
			"featured_image" => __( "Featured image for this Year Book Student", "wcms19-year-book" ),
			"set_featured_image" => __( "Set featured image for this Year Book Student", "wcms19-year-book" ),
			"remove_featured_image" => __( "Remove featured image for this Year Book Student", "wcms19-year-book" ),
			"use_featured_image" => __( "Use as featured image for this Year Book Student", "wcms19-year-book" ),
			"archives" => __( "Year Book Student archives", "wcms19-year-book" ),
			"insert_into_item" => __( "Insert into Year Book Student", "wcms19-year-book" ),
			"uploaded_to_this_item" => __( "Upload to this Year Book Student", "wcms19-year-book" ),
			"filter_items_list" => __( "Filter Year Book Students list", "wcms19-year-book" ),
			"items_list_navigation" => __( "Year Book Students list navigation", "wcms19-year-book" ),
			"items_list" => __( "Year Book Students list", "wcms19-year-book" ),
			"attributes" => __( "Year Book Students attributes", "wcms19-year-book" ),
			"name_admin_bar" => __( "Year Book Student", "wcms19-year-book" ),
			"item_published" => __( "Year Book Student published", "wcms19-year-book" ),
			"item_published_privately" => __( "Year Book Student published privately.", "wcms19-year-book" ),
			"item_reverted_to_draft" => __( "Year Book Student reverted to draft.", "wcms19-year-book" ),
			"item_scheduled" => __( "Year Book Student scheduled", "wcms19-year-book" ),
			"item_updated" => __( "Year Book Student updated.", "wcms19-year-book" ),
			"parent_item_colon" => __( "Parent Year Book Student:", "wcms19-year-book" ),
		];

		$args = [
			"label" => __( "Year Book Students", "wcms19-year-book" ),
			"labels" => $labels,
			"description" => "Add students to year book",
			"public" => true,
			"publicly_queryable" => true,
			"show_ui" => true,
			"show_in_rest" => true,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"has_archive" => true,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"rewrite" => [ "slug" => "students", "with_front" => true ],
			"query_var" => true,
			"supports" => [ "title", "editor", "thumbnail", "excerpt" ],
		];

		register_post_type( "wcms19yb_student", $args );
	}

	/**
	 * Register the Custom Taxonomies
	 * 
	 * @since
	 */
	public function register_cts() {
		/**
		 * Taxonomy: Courses.
		 */

		$labels = [
			"name" => __( "Courses", "wcms19-year-book" ),
			"singular_name" => __( "Course", "wcms19-year-book" ),
			"menu_name" => __( "Courses", "wcms19-year-book" ),
			"all_items" => __( "All Courses", "wcms19-year-book" ),
			"edit_item" => __( "Edit Course", "wcms19-year-book" ),
			"view_item" => __( "View Course", "wcms19-year-book" ),
			"update_item" => __( "Update Course name", "wcms19-year-book" ),
			"add_new_item" => __( "Add new Course", "wcms19-year-book" ),
			"new_item_name" => __( "New Course name", "wcms19-year-book" ),
			"parent_item" => __( "Parent Course", "wcms19-year-book" ),
			"parent_item_colon" => __( "Parent Course:", "wcms19-year-book" ),
			"search_items" => __( "Search Courses", "wcms19-year-book" ),
			"popular_items" => __( "Popular Courses", "wcms19-year-book" ),
			"separate_items_with_commas" => __( "Separate Courses with commas", "wcms19-year-book" ),
			"add_or_remove_items" => __( "Add or remove Courses", "wcms19-year-book" ),
			"choose_from_most_used" => __( "Choose from the most used Courses", "wcms19-year-book" ),
			"not_found" => __( "No Courses found", "wcms19-year-book" ),
			"no_terms" => __( "No Courses", "wcms19-year-book" ),
			"items_list_navigation" => __( "Courses list navigation", "wcms19-year-book" ),
			"items_list" => __( "Courses list", "wcms19-year-book" ),
		];

		$args = [
			"label" => __( "Courses", "wcms19-year-book" ),
			"labels" => $labels,
			"public" => true,
			"publicly_queryable" => true,
			"hierarchical" => false,
			"show_ui" => true,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"query_var" => true,
			"rewrite" => [ 'slug' => 'wcms19yb_course', 'with_front' => true, ],
			"show_admin_column" => false,
			"show_in_rest" => true,
			"rest_base" => "wcms19yb_course",
			"rest_controller_class" => "WP_REST_Terms_Controller",
			"show_in_quick_edit" => false,
			];
		register_taxonomy( "wcms19yb_course", [ "wcms19yb_student" ], $args );
	}

	/**
	 * Register a function for the filter 'the_content'.
	 * 
	 * @since
	 */
	public function register_filter_the_content() {
		add_filter('the_content', [$this, 'filter_the_content']);
	}

	/**
	 * Function for filtering the_content
	 * 
	 * @since
	 */

	public function filter_the_content($content) {
		// 1. If post-type is 'wcms19yb_student.
		if (get_post_type() === 'wcms19yb_student') {
			// 2. Find terms for current 'wcms19yb_student' in taxonomy 'wcms19yb_course'
			$courses = get_the_term_list(get_the_ID(), 'wcms19yb_course', 'Courses: ', ', ');
			if ($courses) {
				// 3. Append <div> with terms, if any.
				$content .= '<div class="wcms19yb-courses">' . $courses . '</div>';
				// 4. Return the modified content.

				// 5. Are there custom fields?
				if (function_exists('get_field')) {
					$attendance = get_field('attendance');
					$detention_hours = get_field('detention_hours');

					$content .= '<div class="wcms19yb-student-details">';

					$content .= sprintf('<h2>%s</h2>', __('Student Details', 'wcms19-year-book'));
					// $content .= '<h2>' . __('Student Details', 'wcms19-year-book') . '</h2>';
					if ($attendance !== false) {
						// $content .= '<span class="attendance">' . __('Attendance:', 'wcms19-year-book') . '</span> ' . $attendance . '%<br>';
						$content .= sprintf('<span class="attendance">%s</span> %d &percnt;<br>', __('Attendance:', 'wcms19-year-book'), $attendance);
					}
					if ($detention_hours !== false) {
						$content .= '<span class="detention-hours">' . __('Detention:', 'wcms19-year-book') . '</span> ' . $detention_hours . ' hours<br>';
					}
					$content .= '</div>';
				}
				return $content;
			}	else {
				return $content;
			}
		}	else {
			return $content;
		}

		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wcms19_Year_Book_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
