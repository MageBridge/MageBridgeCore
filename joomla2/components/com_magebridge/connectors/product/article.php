<?php
/**
 * Joomla! component MageBridge
 *
 * @author Yireo (info@yireo.com)
 * @package MageBridge
 * @copyright Copyright 2014
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * MageBridge Product-connector to mail a Joomla! article when a customer buys a product
 *
 * @package MageBridge
 */
class MageBridgeConnectorProductArticle extends MageBridgeConnectorProduct
{
    /*
     * Method to check whether this connector is enabled or not
     *
     * @param null
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /*
     * Method to get the HTML for a connector input-field
     *
     * @param string $value
     * @return string
     */
    public function getFormField($value = null)
    {
        // @todo: Use a modal popup
        return '<input type="text" size="4" name="article_id" value="'.(int)$value.'" />';
    }

    /*
     * Method to return the selected value from POST
     *
     * @param array $post
     * @return int
     */
    public function getFormPost($post = array())
    {
        if (!empty($post['article_id'])) {
            return $post['article_id'];
        }
        return null;
    }

    /*
     * Method to execute when the product is bought
     * 
     * @param string $article_id
     * @param JUser $user
     * @param int $status
     * @return bool
     */
    public function onPurchase($article_id = null, $user = null, $status = null)
    {
        // Load the article from the database
        $db = JFactory::getDBO();
        $db->setQuery('SELECT * FROM #__content WHERE id = '.(int)$article_id);
        $article = $db->loadObject();
        if (empty($article)) {
            return false;
        }

        // Parse the text a bit
        $article_text = $article->introtext;
        $article_text = str_replace('{name}', $user->name, $article_text);
        $article_text = str_replace('{email}', $user->email, $article_text);

        // Construct other variables
        $app = JFactory::getApplication();
        $sender = array(
            $app->getCfg('mailfrom'),
            $app->getCfg('fromname'),
        );

        // Send the mail
        jimport('joomla.mail.mail');
        $mail = JFactory::getMailer();
        $mail->setSender($sender);
        $mail->addRecipient($user->email);
        $mail->addCC($app->getCfg('mailfrom'));
        $mail->setBody($article_text);
        $mail->setSubject($article->title);
        $mail->isHTML(true);
        $mail->send();

        return true;
    }
}
