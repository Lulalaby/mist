<?php
declare(strict_types=1);
/**
 * Mist
 *
 * @category Theme Framework
 * @package  Mist
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv3 https://opensource.org/licenses/gpl-3.0.php
 */
namespace mist\wrapper;

use mist\MistWrapper;
use mist\MistConfig;

/**
 * MistTheme - Wrap wp theme related functions and hooks like
 * after_setup_theme or upload_mimes
 *
 * @category Theme Framework
 * @package  Mist
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv3 https://opensource.org/licenses/gpl-3.0.php
 */
class MistTheme extends MistWrapper
{
	/**
	 * Theme text domain
	 */
	public $textDomain = 'mist';
	
	/**
	 * Post type object container
	 */
	protected $postTypes = [];
	
	/**
	 * Nav menu object container
	 */
	protected $navMenus = [
		'main'
	];
	
	/**
	 * Theme support
	 */
	protected $themeSupport = [];
	
	/**
	 * Image sizes
	 */
	protected $imageSizes = [];

	/**
	 * Widget areas
	 */
	protected $widgetAreas = [];

	/**
	 * Assets
	 */
	protected $assets = [];

	/**
	 * Is child theme
	 */
	private static $isChildTheme = false;

	/**
	 * Theme URI
	 */
	private static $themeUri = '';
	
	/**
	 * Asset URI
	 */
	private static $assetUri = '';

	/**
	 * Theme Path
	 */
	private static $themePath = '';

	/**
	 * Asset Path
	 */
	private static $assetPath = '';

	/**
	 * Theme configuration
	 */
	private static $config = null;

	/**
	 * Holds all relevant theme data
	 */
	public function __construct()
	{
		self::$isChildTheme = is_child_theme();

		// load theme paths only once
		if (true === self::$isChildTheme) {
			self::$themeUri = get_stylesheet_directory_uri();
			self::$themePath = get_stylesheet_directory();
		} else {
			self::$themeUri = get_template_directory_uri();
			self::$themePath = get_template_directory();
		}

		self::$assetUri = self::$themeUri . '/assets';
		self::$assetPath = self::$themePath . '/assets';

		add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
	}
	
	/**
	 * Clean up default wp stuff
	 *
	 * @return void
	 */
	private function cleanUp(): void
	{
		// TODO: CONFIG & FILTER
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'index_rel_link');
		remove_action('wp_head', 'feed_links', 2);
		remove_action('wp_head', 'feed_links_extra', 3);
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
		remove_action('wp_head', 'wp_shortlink_wp_head', 10);

		// disable noise and throttle brutforce
		add_filter('xmlrpc_enabled', '__return_false');
	}

	/**
	 * Register and enqueue any required assets
	 * 
	 * @return void
	 */
	public function enqueueAssets(): void
	{
		self::$config->enqueueAssets();
		
		// dequeue jquery?
		if (true !== self::$config->globalConfig->wp['enqueue_jquery']) {
			$this->dequeueAssets();
		}
	}

	/**
	 * Dequeue any default assets we will never use
	 * 
	 * @return void
	 */
	private function dequeueAssets(): void
	{
		wp_deregister_script('jquery');
	}

	/**
	 * Init the theme
	 *
	 * @return void
	 */
	public function init(): void
	{
		$this->cleanUp();

		// developers need to use MistPostType to add their post types using code/filter
		$postTypes = apply_filters('mist_post_types', self::$config->registeredPosttypes());
		$this->post()->init($postTypes);
	}

	/**
	 * Initialize theme widgets
	 * 
	 * @return void
	 */
	public function initWidgets(): void
	{
		self::$config->initWidgets();
	}

	/**
	 * Initialize the theme configuration
	 *
	 * @return void
	 */
	public function initConfig(): void
	{
		self::$config = new MistConfig();
	}

	/**
	 * After theme setup
	 *
	 * @return void
	 */
	public function afterSetup(): void
	{
		self::$config->afterSetup();
	}

	/**
	 * Theme root path
	 *
	 * @return string - the theme root path
	 */
	public function rootPath(): string
	{
		return self::$themePath;
	}

	/**
	 * Theme root uri
	 *
	 * @return string - the theme root uri
	 */
	public function rootUri(): string
	{
		return self::$themeUri;
	}
	
	/**
	 * Theme asset uri
	 *
	 * @return string - the theme asset uri
	 */
	public function assetUri(): string
	{
		return self::$assetUri;
	}

	/**
	 * Theme asset path
	 *
	 * @return string - the theme asset path
	 */
	public function assetPath(): string
	{
		return self::$assetPath;
	}

	/**
	 * Is a child theme?
	 *
	 * @return bool - wether is a child theme or not
	 */
	public function isChildTheme(): bool
	{
		return self::$isChildTheme;
	}

	/**
	 * Config
	 * 
	 * @return \mist\MistConfig - the config object
	 */
	public function config(): \mist\MistConfig
	{
		return self::$config;
	}
}
