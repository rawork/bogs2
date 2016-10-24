<?php

namespace Fuga\Component\Twig;

class FugaExtension  extends \Twig_Extension
{

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('render', array($this, 'renderAction')),
			new \Twig_SimpleFunction('path', array($this, 'generatePath')),
			new \Twig_SimpleFunction('t', array($this, 'translate')),
			new \Twig_SimpleFunction('asset', array($this, 'asset')),
		);
	}

	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('format_date', array($this, 'formatDate')),
			new \Twig_SimpleFilter('file_size', array($this, 'fileSize')),
		);
	}

	public function renderAction($path, $options = array())
	{
		return $GLOBALS['container']->callAction($path, $options);
	}

	public function generatePath($name, $options = array(), $locale = PRJ_LOCALE)
	{
		if (isset($options['node']) && '/' == $options['node']) {
			unset($options['node']);
		}
		return ($locale != PRJ_LOCALE ? '/'.$locale : '').$GLOBALS['container']->get('routing')->getGenerator()->generate($name, $options);
	}

	public function formatDate($string, $format)
	{
		return $GLOBALS['container']->get('util')->format_date($string, $format);
	}

	public function fileSize($string)
	{
		return $GLOBALS['container']->get('util')->getSize($string);
	}

	public function translate($string)
	{
		return $GLOBALS['container']->get('translator')->t($string);
	}

	public function asset($path)
	{
		if(file_exists(PRJ_DIR.$path)){
			return PRJ_REF.$path.'?'.date('YmdHis',filemtime(PRJ_DIR.$path));
		}

		return PRJ_REF.$path;
	}

	public function getName()
	{
		return 'fuga';
	}
} 