<?php
/*
Plugin Name: Divi Themesettings Exporter
Version: 0.1
Description: Export/Import your Divi Themesettings with no mess
Author: CoachBirgit
Author URI: http://www.coachbirgit.de
Plugin URI: https://github.com/CoachBirgit/divi-themesettings-exporter
Text Domain: dtexporter
Domain Path: /languages
*/
/*
	Backup/Restore Theme Options
	@ https://digwp.com/2014/04/backup-restore-theme-options/
	Go to "Appearance > Backup Options" to export/import theme settings
	(based on "Gantry Export and Import Options" by Hassan Derakhshandeh)
*/
class divi_backup_restore_theme_options {

	function divi_backup_restore_theme_options() {
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}
	function admin_menu() {

		$page = add_theme_page('Divi Backup', 'Divi Backup', 'manage_options', 'divi-backup', array(&$this, 'options_page'));

		add_action("load-{$page}", array(&$this, 'import_export'));
	}
	function import_export() {
		if (isset($_GET['action']) && ($_GET['action'] == 'download')) {
			header("Cache-Control: public, must-revalidate");
			header("Pragma: hack");
			header("Content-Type: text/plain");
			header('Content-Disposition: attachment; filename="theme-options-'.date("dMy").'.dat"');
			echo serialize($this->_get_options());
			die();
		}
		if (isset($_POST['upload']) && check_admin_referer('Divi_restoreOptions', 'Divi_restoreOptions')) {
			if ($_FILES["file"]["error"] > 0) {
				// error
			} else {
				$options = unserialize(file_get_contents($_FILES["file"]["tmp_name"]));
				if ($options) {
					foreach ($options as $option) {
						update_option($option->option_name, unserialize($option->option_value));
					}
				}
			}
			wp_redirect(admin_url('themes.php?page=divi-backup'));
			exit;
		}
	}
	function options_page() { ?>

		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e('Backup/Restore Theme Options','dtexporter')?></h2>
			<form action="" method="POST" enctype="multipart/form-data">
				<style>#divi-backup td { display: block; margin-bottom: 20px; }</style>
				<table id="divi-backup">
					<tr>
						<td>
							<h3><?php _e('Divi Backup/Export','dtexporter')?></h3>
							<p><?php _e('Here are the stored settings for the current theme:','dtexporter')?></p>
							<p><textarea class="widefat code" rows="20" cols="100" onclick="this.select()"><?php echo serialize($this->_get_options()); ?></textarea></p>
							<p><a href="?page=divi-backup&action=download" class="button-secondary"><?php _e('Download as file','dtexporter')?></a></p>
						</td>
						<td>
							<h3><?php e_('Restore/Import','dtexporter')?></h3>
							<p><label class="description" for="upload"><?php _e('Restore a previous backup','dtexporter')?></label></p>
							<p><input type="file" name="file" /> <input type="submit" name="upload" id="upload" class="button-primary" value="<?php e_('Upload file','dtexporter')?>" /></p>
							<?php if (function_exists('wp_nonce_field')) wp_nonce_field('Divi_restoreOptions', 'Divi_restoreOptions'); ?>
						</td>
					</tr>
				</table>
			</form>
		</div>

	<?php }
	function _display_options() {
		$options = unserialize($this->_get_options());
	}
	function _get_options() {
		global $wpdb;
		return $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name = 'et_divi'"); 
	}
}
new divi_backup_restore_theme_options();
