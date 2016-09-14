<?php

namespace Fuga\Component\Exception;

interface HttpExceptionInterface
{

	public function getStatusCode();

    public function getHeaders();
}
