<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
/* This file based on part of the Symfony package.
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 */


namespace snb\view;

use snb\form\FormView;
use snb\core\ConfigInterface;



/**
 * A Twig extension that adds functions to support forms
 */
class FormExtension extends \Twig_Extension
{
	protected $environment;
	protected $template;
	protected $config;



	/**
	 * @param \snb\core\ConfigInterface $config
	 */
	public function __construct(ConfigInterface $config)
	{
		$this->environment = null;
		$this->template = null;
		$this->config = $config;
	}




	/**
	 * At the moment we force the loading of a specific template.
	 * This is bad. We should be getting this from a config setting
	 * or have it injected into the class
	 * @param \Twig_Environment $environment
	 */
	public function initRuntime(\Twig_Environment $environment)
	{
		// Figure out the name of the layout script to use
		$this->environment = $environment;

		// Find the forms layout template
		$templateName = $this->config->get('snb.forms.layout', '::forms.layout.twig');
		$this->template = $this->environment->loadTemplate($templateName);
	}



	/**
	 * @return string
	 */
	function getName()
	{
		return 'forms';
	}



	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return array An array of functions
	 */
	public function getFunctions()
	{
		return array(
			'form_all'  => new \Twig_Function_Method($this, 'renderFormAll', array('is_safe' => array('html'))),
			'form_row'  => new \Twig_Function_Method($this, 'renderFormRow', array('is_safe' => array('html'))),
			'form_label'  => new \Twig_Function_Method($this, 'renderFieldLabel', array('is_safe' => array('html'))),
			'form_widget'  => new \Twig_Function_Method($this, 'renderFieldWidgets', array('is_safe' => array('html'))),
			'form_errors'  => new \Twig_Function_Method($this, 'renderFieldErrors', array('is_safe' => array('html')))
		);
	}


	/**
	 * Calls one of the blocks in the template. It tries using a block
	 * called <type>_<action>, and if that does not exist, falls back to field_<action>
	 * @param string $action
	 * @param \snb\form\FormView $data
	 * @return string
	 */
	public function render($action, FormView $data)
	{
		$blocks = $this->template->getBlocks();
		$type = $data->get('type', 'field');

		$typeAction = $type.'_'.$action;
		if (!array_key_exists($typeAction, $blocks))
		{
			$typeAction = 'field_'.$action;
		}

		ob_start();
		$this->template->displayBlock($typeAction, $data->all(), $blocks);
		$html = ob_get_clean();
		return $html;
	}


	/**
	 * Renders all the widgets in a form (well, renders the block form_rows)
	 * @param \snb\form\FormView $form
	 * @return string
	 */
	public function renderFormAll(FormView $form)
	{
		return $this->render('rows', $form);
	}


	/**
	 * @param \snb\form\FormView $form
	 * @return string
	 */
	public function renderFormRow(FormView $form)
	{
		return $this->render('row', $form);
	}


	/**
	 * @param \snb\form\FormView $field
	 * @return string
	 */
	public function renderFieldLabel(FormView $field)
	{
		return $this->render('label', $field);
	}



	/**
	 * @param \snb\form\FormView $field
	 * @return string
	 */
	public function renderFieldWidgets(FormView $field)
	{
		return $this->render('widget', $field);
	}



	/**
	 * @param \snb\form\FormView $form
	 * @return string
	 */
	public function renderFieldErrors(FormView $form)
	{
		return $this->render('errors', $form);
	}
}
