<?php

namespace Navio\HospitalBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class HospitalBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
