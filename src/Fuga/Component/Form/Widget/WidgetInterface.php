<?php

namespace Fuga\Component\Form\Widget;

interface WidgetInterface 
{
	public function getValue($name, $entity);
	public function getSearchName($name);
	public function getStatic($name, $entity);
	public function getInput($name, $entity);
	public function getSearchInput($name, $entity);
	
}