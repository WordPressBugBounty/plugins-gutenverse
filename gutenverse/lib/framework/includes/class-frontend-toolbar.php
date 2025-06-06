<?php
/**
 * Frontend Toolbar
 *
 * @author Jegstudio
 * @since 1.4.0
 * @package gutenverse-framework
 */

namespace Gutenverse\Framework;

/**
 * Class Frontend Toolbar
 *
 * @package gutenverse-framework
 */
class Frontend_Toolbar {
	/**
	 * Template Path.
	 *
	 * @var string
	 */
	private $is_block_template;

	/**
	 * Hierarcy.
	 *
	 * @var array
	 */
	private $hierarchy;

	/**
	 * Frontend Toolbar Construct.
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'add_toolbar' ), 100 );
		add_filter( 'template_include', array( $this, 'get_loaded_template' ), 1000 );

		$hierarchies = array(
			'404',
			'archive',
			'attachment',
			'author',
			'category',
			'date',
			'embed',
			'frontpage',
			'home',
			'index',
			'page',
			'paged',
			'privacypolicy',
			'search',
			'single',
			'singular',
			'tag',
			'taxonomy',
		);

		foreach ( $hierarchies as $hierarchy ) {
			add_filter( $hierarchy . '_template', array( $this, 'register_hierarchy' ), null, 3 );
		}
	}

	/**
	 * Get Template Part.
	 *
	 * @return array
	 */
	public function get_template_parts() {
		return apply_filters( 'gutenverse_inject_template_part', array() );
	}

	/**
	 * Register Hierarchy
	 *
	 * @param string   $template  Path to the template . See locate_template() .
	 * @param string   $type      Sanitized filename without extension .
	 * @param string[] $templates A list of template candidates, in descending order of priority .
	 */
	public function register_hierarchy( $template, $type, $templates ) {
		$this->hierarchy = array(
			'type'      => $type,
			'templates' => $templates,
		);

		return $template;
	}

	/**
	 * Block Template Canvas Path.
	 */
	public function get_block_template_default_path() {
		return ABSPATH . WPINC . '/template-canvas.php';
	}

	/**
	 * Get Loaded Template.
	 *
	 * @param string $template template path.
	 *
	 * @return string
	 */
	public function get_loaded_template( $template ) {
		if ( $template === $this->get_block_template_default_path() ) {
			$this->is_block_template = true;
		}

		return $template;
	}

	/**
	 * Toolbar Items.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin Bar Instance.
	 */
	public function add_toolbar( $admin_bar ) {
		if ( is_user_logged_in() ) {
			$user  = wp_get_current_user();
			$roles = (array) $user->roles;

			if ( in_array( 'administrator', $roles, true ) || in_array( 'editor', $roles, true ) ) {

				$title = '<span><img src="' . esc_url( GUTENVERSE_FRAMEWORK_URL_PATH . '/assets/icon/icon-logo-dashboard.svg' ) . '"/>' . esc_html__( 'Gutenverse', 'gutenverse' ) . '</span>';

				$admin_bar->add_menu(
					array(
						'id'    => 'gutenverse',
						'title' => $title,
					)
				);

				if ( ! is_admin() ) {
					if ( current_theme_supports( 'block-templates' ) && $this->is_block_template ) {
						$block_template = resolve_block_template(
							$this->hierarchy['type'],
							$this->hierarchy['templates'],
							''
						);

						$this->the_toolbar( $admin_bar, $block_template );
					} else {
						$this->not_toolbar( $admin_bar );
					}

					$admin_bar->add_menu(
						array(
							'id'     => 'space',
							'parent' => 'gutenverse',
							'title'  => '',
						)
					);
				}

				$admin_bar->add_menu(
					array(
						'id'     => 'backend',
						'parent' => 'gutenverse',
						'title'  => esc_html__( 'Gutenverse Admin', 'gutenverse' ),
						'href'   => admin_url( 'admin.php?page=' . Dashboard::TYPE ),
					)
				);
				$site_url     = get_site_url();
				$active_theme = get_option( 'stylesheet' );
				if ( ! defined( 'GUTENVERSE_PRO' ) ) {
					$admin_bar->add_menu(
						array(
							'id'    => 'gutenverse-pro',
							'title' => '<span class="gutenverse-pro-right">' . esc_html__( 'Gutenverse PRO', 'gutenverse' ) . '<img src="' . esc_url( GUTENVERSE_FRAMEWORK_URL_PATH . '/assets/icon/icon-crown.svg' ) . '"/> </span>',
							'href'  => gutenverse_upgrade_pro() . '/?utm_source=gutenverse&utm_medium=admintopbar&utm_client_site=' . $site_url . '&utm_client_theme=' . $active_theme,
							'meta'  => array(
								'target' => '_blank',
							),
						)
					);
				}
			}
		}

		$this->setting_toolbar( $admin_bar, 'backend' );
	}

	/**
	 * Setting Toolbar.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin Bar Instance.
	 * @param string        $root Parent.
	 */
	public function setting_toolbar( $admin_bar, $root = 'gutenverse' ) {
		$show_theme_list = apply_filters( 'gutenverse_show_theme_list', true );

		$admin_bar->add_menu(
			array(
				'id'     => 'dashboard',
				'parent' => $root,
				'title'  => esc_html__( 'Dashboard', 'gutenverse' ),
				'href'   => admin_url( 'admin.php?page=' . Dashboard::TYPE ),
			)
		);

		if ( $show_theme_list ) {
			$admin_bar->add_menu(
				array(
					'id'     => 'theme-list',
					'parent' => $root,
					'title'  => esc_html__( 'Theme List', 'gutenverse' ),
					'href'   => admin_url( 'admin.php?page=gutenverse&path=theme-list' ),
				)
			);
		}

		$admin_bar->add_menu(
			array(
				'id'     => 'block-list',
				'parent' => $root,
				'title'  => esc_html__( 'Block List', 'gutenverse' ),
				'href'   => admin_url( 'admin.php?page=gutenverse&path=block-list' ),
			)
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'settings',
				'parent' => $root,
				'title'  => esc_html__( 'Settings', 'gutenverse' ),
				'href'   => admin_url( 'admin.php?page=gutenverse&path=settings' ),
			)
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'system',
				'parent' => $root,
				'title'  => esc_html__( 'System Status', 'gutenverse' ),
				'href'   => admin_url( 'admin.php?page=gutenverse&path=system' ),
			)
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'update-notice',
				'parent' => $root,
				'title'  => esc_html__( 'Update Notice', 'gutenverse' ),
				'href'   => admin_url( 'admin.php?page=gutenverse&path=update-notice' ),
			)
		);

		$is_wp_above_6_2 = version_compare( $GLOBALS['wp_version'], '6.2', '>=' );

		$admin_bar->add_menu(
			array(
				'id'     => 'gutenverse-site-editor',
				'parent' => 'gutenverse',
				'title'  => esc_html__( 'Template Editor', 'gutenverse' ),
			)
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'gutenverse-template',
				'parent' => 'gutenverse-site-editor',
				'title'  => esc_html__( 'All Template', 'gutenverse' ),
				'href'   => $is_wp_above_6_2 ? admin_url( 'site-editor.php?path=%2Fwp_template%2Fall' ) : admin_url( 'site-editor.php?postType=wp_template' ),
			)
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'gutenverse-template-part',
				'parent' => 'gutenverse-site-editor',
				'title'  => esc_html__( 'Template Part', 'gutenverse' ),
				'href'   => $is_wp_above_6_2 ? admin_url( 'site-editor.php?path=%2Fwp_template_part%2Fall' ) : admin_url( 'site-editor.php?postType=wp_template_part' ),
			)
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'support',
				'parent' => 'gutenverse',
				'title'  => esc_html__( 'Got Question?', 'gutenverse' ),
				'href'   => 'https://wordpress.org/support/plugin/gutenverse/',
				'meta'   => array(
					'target' => '_blank',
				),
			)
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'rate',
				'parent' => 'gutenverse',
				'title'  => esc_html__( 'Rate Us ★★★★★', 'gutenverse' ),
				'href'   => 'https://wordpress.org/support/plugin/gutenverse/reviews/#new-post',
				'meta'   => array(
					'target' => '_blank',
				),
			)
		);

		$admin_bar->add_menu(
			array(
				'id'     => 'help-documentation',
				'parent' => 'gutenverse',
				'title'  => esc_html__( 'Help/Documentation', 'gutenverse' ),
				'href'   => 'https://gutenverse.com/docs/',
				'meta'   => array(
					'target' => '_blank',
				),
			)
		);

		do_action( 'gutenverse_setting_toolbar', $admin_bar, $root );
	}

	/**
	 * Toolbar Items.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin Bar Instance.
	 */
	public function not_toolbar( $admin_bar ) {
		$admin_bar->add_menu(
			array(
				'id'     => 'no-template',
				'parent' => 'gutenverse',
				'title'  => esc_html__( 'Not a Block Themes', 'gutenverse' ),
			)
		);
	}

	/**
	 * Toolbar Items.
	 *
	 * @param \WP_Admin_Bar $admin_bar Admin Bar Instance.
	 * @param object        $block Block Template.
	 */
	public function the_toolbar( $admin_bar, $block ) {
		if ( $block ) {
			$is_wp_above_6_4 = version_compare( $GLOBALS['wp_version'], '6.4', '>=' );

			$admin_bar->add_menu(
				array(
					'id'     => 'edit-template',
					'parent' => 'gutenverse',
					'title'  => esc_html__( 'Edit Template: ', 'gutenverse' ) . '<strong>' . $block->title . '</strong>',
					'href'   => $is_wp_above_6_4 ? ( admin_url( 'site-editor.php' ) . '?postId=' . $block->id . '&postType=' . $block->type . '&canvas=edit' ) : ( admin_url( 'site-editor.php' ) . '?postType=' . $block->type . '&postId=' . $block->id ),
					'meta'   => array(
						'target' => 'blank',
					),
				)
			);

			$parts = $this->get_template_parts();

			if ( count( $parts ) > 0 ) {
				$admin_bar->add_menu(
					array(
						'id'     => 'template-part',
						'parent' => 'gutenverse',
						'title'  => esc_html__( 'Included Template Part', 'gutenverse' ),
						'href'   => admin_url( 'site-editor.php?postType=wp_template_part' ),
					)
				);

				foreach ( $parts as $part ) {
					$admin_bar->add_menu(
						array(
							'id'     => 'edit-template-' . $part['attrs']['slug'],
							'parent' => 'template-part',
							'title'  => esc_html__( 'Edit: ', 'gutenverse' ) . '<strong>' . $part['attrs']['slug'] . '</strong>',
							'href'   => $is_wp_above_6_4 ? ( admin_url( 'site-editor.php' ) . '?postId=' . $part['attrs']['theme'] . '//' . $part['attrs']['slug'] . '&postType=wp_template_part&canvas=edit' ) : ( admin_url( 'site-editor.php' ) . '?postType=wp_template_part&postId=' . $part['attrs']['theme'] . '//' . $part['attrs']['slug'] ),
							'meta'   => array(
								'target' => 'blank',
							),
						)
					);
				}
			}
		}
	}
}
