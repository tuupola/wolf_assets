<h1><?php print __('Assets Plugin'); ?></h1>

<form action="<?php print get_url('plugin/assets/save'); ?>" method="post">
<fieldset style="padding: 0.5em;">
  <legend style="padding: 0em 0.5em 0em 0.5em; font-weight: bold;"><?php echo __('Settings'); ?></legend>
  <table class="fieldset" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td class="label">
        <?php if (count($assets_folder_list) > 1): ?> 
        <label for="assets_folder_list"><?php echo __('Asset folders'); ?>: </label>
        <?php else: ?> 
        <label for="assets_folder_list"><?php echo __('Asset folder'); ?>: </label>
        <?php endif; ?> 
      </td>
      <td class="field assets-folder">       
        <?php foreach ($assets_folder_list as $key => $folder): ?> 
        <span class="assets-folder">
        <input type="text" name="assets_folder_list[]" value="<?php print $folder ?>" />
        <a href="#"><img class="assets-folder-add" src="../frog/plugins/assets/images/add.png" alt="add" /></a>
        <?php if ($key > 0): ?>
        <a href="<?php print get_url('plugin/assets/folder/delete/' . $key); ?>"><img class="assets-folder-delete" src="../frog/plugins/assets/images/delete.png" alt="delete" /></a>
        <?php endif; ?>
        <br />
        </span>
        <?php endforeach; ?> 
      </td>
      <td class="help"><?php echo __('You can use multiple folders to categorize files.'); ?></td>
    </tr>
  </table>
</fieldset>
<br/>
<p class="buttons">
  <input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
</p>
</form>
