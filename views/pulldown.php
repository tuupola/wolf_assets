<select name="asset_folder">
  <option value="all" <?php if ('all' == $_SESSION['asset_folder']) print 'selected="selected"'; ?>>- <?php print __('latest -'); ?></option> 
  <?php foreach ($assets_folder_list as $folder): ?>
  <option value="<?php print $folder ?>" <?php if ($folder == $_SESSION['asset_folder']) print 'selected="selected"'; ?>><?php print $folder ?></option> 
  <?php endforeach; ?>
</select>