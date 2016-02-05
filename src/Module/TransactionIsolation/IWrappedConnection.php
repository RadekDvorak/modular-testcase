<?php

namespace Damejidlo\ModularTestCase\Module\TransactionIsolation;



interface IWrappedConnection
{

	public function wrap();



	public function unwrap();

}
