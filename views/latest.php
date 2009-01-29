<?php foreach ($image_array as $image => $thumbnail): ?>
<a href="<?php print $image ?>" title="<?php print $image?>"><img src="<?php print $thumbnail ?>" /></a>
<?php endforeach; ?>