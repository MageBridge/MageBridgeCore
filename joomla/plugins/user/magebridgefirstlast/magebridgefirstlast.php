<?php
/**
 * User Plugin for Joomla! - MageBridge First Last name
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015 Yireo
 * @license GNU Public License version 3 or later
 * @link http://www.yireo.com/software/magebridge
 */

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');
class plgUserMagebridgefirstlast extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $subject
	 * @param array $config
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	protected $allowedContext = array(
		'com_users.profile',
		'com_users.user',
		'com_users.registration',
		'com_admin.profile',
	);

	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		$context = $form->getName();
		if (!in_array($context, $this->allowedContext))
		{
			return true;
		}

		JForm::addFormPath(__DIR__ . '/form');
		$form->loadFile('form', false);

		return true;
	}

	public function onContentPrepareData($context, $data)
	{
		if (!in_array($context, $this->allowedContext))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			if (!isset($data->magebridgefirstlast) and $userId > 0)
			{
				try
				{
					$fields = $this->getFields($userId);
				}
				catch (RuntimeException $e)
				{
					$this->_subject->setError($e->getMessage());
					return false;
				}

				$data->magebridgefirstlast = array();
				foreach ($fields as $field)
				{
					$fieldName = str_replace('magebridgefirstlast.', '', $field[0]);
					$data->magebridgefirstlast[$fieldName] = json_decode($field[1], true);
					if ($data->magebridgefirstlast[$fieldName] === null)
					{
						$data->magebridgefirstlast[$fieldName] = $field[1];
					}
				}
			}
		}

		if (empty($data->magebridgefirstlast['firstname']) && empty($data->magebridgefirstlast['lastname']) && !empty($data->name))
		{
			$name = explode(' ', $data->name);
			if (count($name) >= 2)
			{
				$data->magebridgefirstlast['firstname'] = trim(array_shift($name));
				$data->magebridgefirstlast['lastname'] = trim(implode(' ', $name));
			}
		}

		if (!empty($data->magebridgefirstlast['firstname']) && !empty($data->magebridgefirstlast['lastname']) && empty($data->name))
		{
			$data->name = $data->magebridgefirstlast['firstname'].' '.$data->magebridgefirstlast['lastname'];
		}

		return true;
	}

	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId = JArrayHelper::getValue($data, 'id', 0, 'int');

		if ($userId && $result && isset($data['magebridgefirstlast']) && (count($data['magebridgefirstlast'])))
		{
			try
			{
				$this->deleteFields($userId);

				$ordering = 0;
				foreach ($data['magebridgefirstlast'] as $fieldName => $fieldValue)
				{
					$this->insertField($userId, $fieldName, $fieldValue, $ordering);
					$ordering++;
				}

			}
			catch (RuntimeException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		// @todo: Add a setting for this
		if(!empty($data['magebridgefirstlast']['firstname']) && !empty($data['magebridgefirstlast']['lastname'])) {
			$this->setUserName($data['id'], $data['magebridgefirstlast']['firstname'], $data['magebridgefirstlast']['lastname']);
		}

		return true;
	}

	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$this->deleteFields($userId);
			}
			catch (Exception $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}

	public function onUserLoad($user)
	{
		if (empty($user) || empty($user->id))
		{
			return false;
		}

		try
		{
			$fields = $this->getFields($user->id);
		}
		catch (Exception $e)
		{
			$this->_subject->setError($e->getMessage());
			return false;
		}

		foreach($fields as $field)
		{
			$fieldName = str_replace('magebridgefirstlast.', '', $field[0]);
			$fieldValue = $field[1];
			$user->set($fieldName, $fieldValue);
		}
	}

	protected function getFields($userId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$columns = array('profile_key', 'profile_value');
		$query->select($db->quoteName($columns));
		$query->from($db->quoteName('#__user_profiles'));
		$query->where($db->quoteName('profile_key').' LIKE '.$db->quote('magebridgefirstlast.%'));
		$query->where($db->quoteName('user_id').' = '.(int)$userId);
		$query->order('ordering ASC');

		$db->setQuery($query);

		$results = $db->loadRowList();
		return $results;
	}

	protected function deleteFields($userId)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__user_profiles'))
			->where($db->quoteName('user_id').' = '.(int)$userId)
			->where($db->quoteName('profile_key').' LIKE '.$db->quote('magebridgefirstlast.%'));
		$db->setQuery($query);

		$db->execute();
	}

	protected function insertField($userId, $name, $value, $ordering)
	{
		$db = JFactory::getDbo();

		$columns = array('user_id', 'profile_key', 'profile_value', 'ordering');
		$values = array($userId, $db->quote('magebridgefirstlast.'.$name), $db->quote($value), $ordering);

		$query = $db->getQuery(true)
			->insert($db->quoteName('#__user_profiles'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));
		$db->setQuery($query);

		$db->execute();
	}

	protected function setUserName($userId, $firstname, $lastname)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__users'))
			->set($db->quoteName('name').'='.$db->quote($firstname.' '.$lastname))
			->where($db->quoteName('id').'='.(int)$userId);
		$db->setQuery($query);

		$db->execute();
	}
}
