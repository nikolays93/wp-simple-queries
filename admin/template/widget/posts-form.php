<?php

namespace Nikolays93\Queries;

use NikolayS93\WPAdminForm\Form as Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // disable direct access
?>

<div class="widget-panel-form">

    <legend class="screen-reader-text"><span><?php _e( 'General Settings' ) ?></span></legend>

	<?php
	$form = new Form( $global_data, array(
		'is_table'   => false,
		'admin_page' => false,
	) );
	$form->set( $active );
	$form->display();
	?>

	<?php if ( is_plugin_active( 'sf-taxonomy-thumbnail' ) || is_plugin_active( 'woocommerce' ) ) : ?>
        <!-- Thumbnail -->
        <div class="widget-panel-section">
			<?php echo self::build_section_header( $title = 'Term Thumbnail' ); ?>

            <fieldset data-fieldset-id="thumbnails"
                      class="widget-panel-settings widget-panel-fieldset settings-thumbnails">

                <legend class="screen-reader-text"><span><?php _e( 'Term Thumbnail' ) ?></span></legend>

				<?php
				$form = new WP_Admin_Forms( $thumbnail_data, false, array( 'admin_page' => false ) );
				$form->set_active( $active );
				$form->render();
				?>

            </fieldset>
        </div><!-- /.widget-panel-section -->
	<?php endif; ?>

    <!-- Template -->
    <div class="widget-panel-section">
		<?php echo self::build_section_header( $title = 'Template' ); ?>

        <fieldset data-fieldset-id="layout" class="widget-panel-settings widget-panel-fieldset settings-layout">

            <legend class="screen-reader-text"><span><?php _e( 'Template' ) ?></span></legend>
			<?php
			$form = new WP_Admin_Forms( $template_data, false, array( 'admin_page' => false ) );
			$form->set_active( $active );
			$form->render();
			?>

        </fieldset>
    </div><!-- /.widget-panel-section -->

    <!-- Query Settings -->
    <div class="widget-panel-section">
		<?php echo self::build_section_header( $title = 'Query Settings' ); ?>

        <fieldset data-fieldset-id="thumbnails"
                  class="widget-panel-settings widget-panel-fieldset settings-view-settings">

            <legend class="screen-reader-text"><span><?php _e( 'Query Settings' ) ?></span></legend>
			<?php
			$form = new WP_Admin_Forms( $query_data, false, array( 'admin_page' => false ) );
			$form->set_active( $active );
			$form->render();
			?>

        </fieldset>
    </div>

    <!-- Exclude -->
    <div class="widget-panel-section">
		<?php echo self::build_section_header( $title = 'Terms Excluded' ); ?>

        <fieldset data-fieldset-id="filters" class="widget-panel-settings widget-panel-fieldset settings-filters">

            <legend class="screen-reader-text"><span><?php _e( 'Terms Excluded' ) ?></span></legend>

			<?php
			$form = new WP_Admin_Forms( $terms_data, false, array( 'admin_page' => false ) );
			$form->set_active( $active );
			$form->render();
			?>

        </fieldset>
    </div><!-- /.widget-panel-section -->

</div><!-- .widget-panel-form -->
