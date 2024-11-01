<div class="wrap">
<h2>INSIDE Integration</h2>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
<?php settings_fields('inside'); ?>

<table class="form-table">

<tr valign="top">
<th scope="row">INSIDE Account Key:</th>
<td><input type="text" name="inside_accountkey" value="<?php echo get_option('inside_accountkey'); ?>" /></td>
</tr>

</table>

<input type="hidden" name="action" value="update" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

</form>
</div>
