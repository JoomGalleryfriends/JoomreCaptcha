<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/Plugins/JoomreCaptcha/trunk/joomrecaptcha.php $
// $Id: joomrecaptcha.php 3746 2012-04-08 17:53:28Z chraneco $
/******************************************************************************\
**   JoomGallery Plugin 'JoomreCaptcha'2.0                                    **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2010 - 2012  Chraneco                                      **
**   Modified for reCaptcha v2 by ericvb                                      **
**   With some code from the PHP library that handles calling reCAPTCHA       **
**   by Mike Crawford and Ben Maurer                                          **
**   Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net                     **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html                            **
\******************************************************************************/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * JoomGallery EasyCaptcha Plugin
 *
 * @package     Joomla
 * @subpackage  JoomGallery
 * @since       1.5
 */
class plgJoomGalleryJoomreCaptcha extends JPlugin
{
  /**
   * onJoomGetCaptcha method
   *
   * Method is called whenever spam protection is necessary in JoomGallery
   *
   * @param   string  $ambit  A string which determines the ambit in which the captcha will be displayed (for example 'comments')
   * @return  string  The HTML output of the captcha
   * @since   1.5
   */
  public function onJoomGetCaptcha($ambit = '')
  {
    $user = JFactory::getUser();
    if($user->get('id') && !$this->params->get('enabled_for'))
    {
      return '';
    }

    $language = JFactory::getLanguage();
    $lang     = $language->getTag();
    $lang     = substr($lang, 0, 2);
    $html     = '';

    // Default Layout
    if($this->params->get('output_layout') == '0')
    {
      $html = '<div class="jg_cmtl">
                 &nbsp;
               </div>';
    }

    $html = $html . '
        <div class="jg_cmtr">
		  <script src="https://www.google.com/recaptcha/api.js?hl='.$lang.'" async defer></script>
          <div class="g-recaptcha" data-sitekey="'.$this->params->get('publickey').'" data-theme="'.$this->params->get('theme').'"></div>
			<noscript>
			  <div>
				<div style="width: 302px; height: 422px; position: relative;">
				  <div style="width: 302px; height: 422px; position: absolute;">
					<iframe src="https://www.google.com/recaptcha/api/fallback?k='.$this->params->get('publickey').'"
							frameborder="0" scrolling="no"
							style="width: 302px; height:422px; border-style: none;">
					</iframe>
				  </div>
				</div>
				<div style="width: 300px; height: 60px; border-style: none;
							   bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px;
							   background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
				  <textarea id="g-recaptcha-response" name="g-recaptcha-response"
							   class="g-recaptcha-response"
							   style="width: 250px; height: 40px; border: 1px solid #c1c1c1;
									  margin: 10px 25px; padding: 0px; resize: none;" >
				  </textarea>
				</div>
			  </div>
			</noscript>
        </div>';

    return $html;
  }

  /**
   * onJoomCheckCaptcha method
   *
   * Method is called when a captcha should be validated
   *
   * @param   string  $ambit  A string which determines the ambit in which the captcha will be displayed (for example 'comments')
   * @return  array   An array with result information, boolean false if a check isn't necessary in the context
   * @since   1.5
   */
  public function onJoomCheckCaptcha($ambit = '')
  {
    $user = JFactory::getUser();
    if($user->get('id') && !$this->params->get('enabled_for'))
    {
      return false;
    }

    // Load the language file
    $this->loadLanguage();

	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array(
		'secret' => $this->params->get('privatekey'),
		'response' => JRequest::getVar('g-recaptcha-response')
	);
	$options = array(
		'http' => array (
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context  = stream_context_create($options);
	$verify = file_get_contents($url, false, $context);	
	$captcha_response = json_decode($verify);
	
	if($captcha_response->success == true)
    {
      $valid = true;
      $error = '';
    }
    else
    {
      $valid = false;
      $error = JText::_('PLG_JOOMGALLERY_JOOMRECAPTCHA_SECURITY_CODE_WRONG');
    }

    return array('valid' => $valid, 'error' => $error);
  }
}