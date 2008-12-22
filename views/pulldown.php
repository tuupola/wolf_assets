<select name="assets_folder">
  <option value="all" <?php if ('all' == $_SESSION['assets_folder']) print 'selected="selected"'; ?>>- <?php print __('latest -'); ?></option> 
  <?php foreach ($assets_folder_list as $folder): ?>
  <option value="<?php print $folder ?>" <?php if ($folder == $_SESSION['assets_folder']) print 'selected="selected"'; ?>><?php print $folder ?></option> 
  <?php endforeach; ?>
</select>