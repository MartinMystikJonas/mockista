<?php

namespace Mockista;

use Mockista\ClassGenerator\ClassGenerator;
use Mockista\ClassGenerator\MethodFinder;

class MockBuilder
{

	/** @var Mock */
	private $mock;

	/** @var ArgsMatcher */
	private $argsMatcher;

	public function __construct($mocked = NULL, array $defaults = array(), ArgsMatcher $argsMatcher = NULL)
	{
		if (is_array($mocked)) {
			$defaults = $mocked;
			$mocked = NULL;
		}

		$this->mock = $this->createMock($mocked);
		$this->addMethods($defaults);
	}

	public function setArgsMatcher(ArgsMatcher $argsMatcher = NULL)
	{
		$this->argsMatcher = $argsMatcher;
	}

	public static function createFromMock($mock)
	{
		$builder = new self();
		$builder->mock = $mock;

		return $builder;
	}

	public function __call($methodName, array $args = array())
	{
		$method = $this->mock->expects($methodName);
		call_user_func_array(array($method, 'with'), $args);
		return $method;
	}

	private function createMock($mocked)
	{
	    if(is_object($mocked)) {
			$class = get_class($mocked);
			$mockista = new Mock($this->argsMatcher, $mocked);
		} else {
			$class = $mocked;
			$mockista = new Mock($this->argsMatcher);
		}

		if ($class) {
			$classGenerator = new ClassGenerator();
			$classGenerator->setMethodFinder(new MethodFinder());

			$newName = str_replace("\\", "_", $class) . '_' . mt_rand();
			$code = $classGenerator->generate($class, $newName);

			eval($code);
			$mock = new $newName();
			$mock->mockista = $mockista;
			$mock->mockista->setName($class);
		} else {
			$mock = $mockista;
		}

		return $mock;
	}

	public function addMethods(array $methods)
	{
		foreach ($methods as $key => $default) {
			if ($default instanceof \Closure) {
				$this->mock->expects($key)->andCallback($default);
			} else {
				$this->mock->expects($key)->andReturn($default);
			}
		}
	}

	/**
	 * @return Mock
	 */
	public function getMock()
	{
		return $this->mock;
	}

}
