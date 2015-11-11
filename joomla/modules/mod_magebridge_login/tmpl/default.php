<?php
/**
 * Joomla! module MageBridge Login
 *
 * @author	Yireo (info@yireo.com)
 * @package   MageBridge
 * @copyright Copyright 2015
 * @license   GNU Public License
 * @link	  http://www.yireo.com/
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Define some variables first
$autocomplete = ($params->get('allow_autocomplete', 1) == 1) ? null : 'autocomplete="off"';
?>
<?php if ($type == 'logout_link') : ?>
	<div>
		<form action="<?php echo $component_url; ?>" method="post" name="mod-magebridge-logout"
			  id="mod-magebridge-logout">
			<?php if ($params->get('greeting')) : ?>
				<div><?php echo JText::sprintf($params->get('greeting'), $name); ?></div>
			<?php endif; ?>
			<ul>
				<?php if ($params->get('account_link', 2)) : ?>
					<li><a
						href="<?php echo $account_url; ?>"><?php echo JText::_('MOD_MAGEBRIDGE_LOGIN_SETTINGS') ?></a>
					</li><?php endif; ?>
			</ul>
			<input type="submit" name="Submit" class="button"
				   value="<?php echo JText::_('MOD_MAGEBRIDGE_LOGIN_LOGOUT') ?>"/>
			<input type="hidden" name="option" value="<?php echo $component ?>"/>
			<input type="hidden" name="task" value="<?php echo $task_logout ?>"/>
			<input type="hidden" name="return" value="<?php echo $return_url ?>"/>
			<input type="hidden" name="language" value="<?php echo JFactory::getApplication()->input->getCmd('language'); ?>"/>
			<?php echo JHTML::_('form.token'); ?>
		</form>
	</div>
<?php else : ?>
	<div>
		<form action="<?php echo $component_url; ?>" method="post" name="mod-magebridge-login"
			  id="mod-magebridge-login" <?php echo $autocomplete; ?>>
			<?php if ($params->get('text')) : ?>
				<div><?php echo JText::sprintf($params->get('text'), $name); ?></div>
			<?php endif; ?>
			<div class="username-block">
				<label for="username_login"><?php echo JText::_('MOD_MAGEBRIDGE_LOGIN_EMAIL') ?></label><br/>
				<input class="inputbox" type="text" id="username_login" size="16"
					   name="username" <?php echo $autocomplete; ?>
					   placeholder="<?php echo JText::_('MOD_MAGEBRIDGE_LOGIN_EMAIL_PLACEHOLDER'); ?>"/>
			</div>
			<div class="password-block">
				<label for="password_login"><?php echo JText::_('MOD_MAGEBRIDGE_LOGIN_PASSWORD') ?></label><br/>
				<input type="password" class="inputbox" id="password_login" size="16"
					   name="<?php echo $password_field; ?>" <?php echo $autocomplete; ?>
					   placeholder="<?php echo JText::_('MOD_MAGEBRIDGE_LOGIN_PASSWORD_PLACEHOLDER'); ?>"/>
			</div>
			<div class="login-extras">
				<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
					<label for="remember_login"><?php echo JText::_('MOD_MAGEBRIDGE_LOGIN_REMEMBER') ?></label>
					<input type="checkbox" name="remember" id="remember_login" value="yes" checked="checked"/>
				<?php endif; ?>
				<input type="submit" value="<?php echo JText::_('MOD_MAGEBRIDGE_LOGIN_LOGIN') ?>" class="button"
					   name="Login"/>
				<ul>
					<li>
						<a href="<?php echo $forgotpassword_url; ?>"><?php echo JText::_('MOD_MAGEBRIDGE_LOGIN_FORGOT_PASSWORD'); ?></a>
					</li>
					<li>
						<a href="<?php echo $createnew_url; ?>"><?php echo JText::_('MOD_MAGEBRIDGE_LOGIN_CREATE_ACCOUNT'); ?></a>
					</li>
				</ul>
				<input type="hidden" name="option" value="<?php echo $component ?>"/>
				<input type="hidden" name="task" value="<?php echo $task_login ?>"/>
				<input type="hidden" name="return" value="<?php echo $return_url ?>"/>
				<input type="hidden" name="language" value="<?php echo JFactory::getApplication()->input->getCmd('language'); ?>"/>
				<?php echo JHTML::_('form.token'); ?>
			</div>
		</form>
	</div>
<?php endif; ?>
