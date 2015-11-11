<?php
/**
 * Joomla! module MageBridge: Shopping Cart
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link	  http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

?>
<div id="magebridge-switcher" class="magebridge-module mod-languages">
	<ul class="lang-inline">
		<?php foreach ($languages as $language) : ?>
			<?php $image = '/media/mod_languages/images/' . $language['code'] . '.gif'; ?>
			<?php $label = $language['label']; ?>
			<?php $url = $language['url']; ?>
			<li class="lang-active" dir="ltr">
				<a href="<?php echo $url; ?>"><img src="<?php echo $image; ?>" alt="<?php echo $label; ?>"
												   title="<?php echo $label; ?>"/></a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
