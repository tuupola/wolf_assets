<form action="<?php print get_url('plugin/assets/upload') ?>" class="new_asset" enctype="multipart/form-data" method="post">
<input name="user_file" type="file" />
<input type="submit" />
<span class="small"><?php print __('upload to:') ?></span>
<select name="assets_folder">
  <?php foreach ($assets_folder_list as $folder): ?>
  <option value="<?php print $folder ?>" <?php if ($folder == $_SESSION['assets_folder']) print 'selected="selected"'; ?>><?php print $folder ?></option> 
  <?php endforeach; ?>
</select>
</form>
<p id="assets_list">
  <?php foreach ($image_array as $image => $thumbnail): ?>
  <a href="<?php print $image?>" title="<?php print $image?>"><img src="<?php print $thumbnail ?>" /></a>
  <?php endforeach; ?>
</p>
<p id="assets_tools">
  <img id="trash_can" src="/wolf/plugins/assets/images/trash.png" />
</p>
