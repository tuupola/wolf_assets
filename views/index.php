<form action="<?php print get_url('plugin/assets/upload') ?>" class="new_asset" enctype="multipart/form-data" method="post">
<input name="user_file" type="file" />
<input type="submit" />
</form>
<p class="asset_list">
  <?php foreach ($image_array as $image => $thumbnail): ?>
  <a href="<?php print $image?>"<img src="<?php print $thumbnail ?>" /></a>
  <?php endforeach; ?>
</p>
