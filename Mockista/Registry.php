<?php

namespace Mockista;

class Registry
{

	/** @var MockInterface[] */
	private $mocks = array();
	
	private $mockId = 1;

	/** @var ArgsMatcher */
	private $argsMatcher;

	public function __construct(ArgsMatcher $argsMatcher = NULL)
	{
		$this->argsMatcher = $argsMatcher;
	}

	public function setArgsMatcher(ArgsMatcher $argsMatcher = NULL)
	{
		$this->argsMatcher = $argsMatcher;
	}

	/**
	 * Create mock
	 * @param string $mocked mocked class or object
	 * @param array $methods
	 * @param ArgsMatcher $argsMatcher
	 * @return MockInterface
	 */
	public function create($mocked = NULL, array $methods = array(), ArgsMatcher $argsMatcher = NULL)
	{
		return $this->createBuilder($mocked, $methods, $argsMatcher)->getMock();
	}

	/**
	 * Create mock with user defined name
	 * @param string $name
	 * @param string $mocked
	 * @param array $methods
	 * @return MockInterface
	 */
	public function createNamed($name, $mocked = NULL, array $methods = array(), ArgsMatcher $argsMatcher = NULL)
	{
		return $this->createNamedBuilder($name, $mocked, $methods, $argsMatcher)->getMock();
	}

	/**
	 * Create mock builder
	 * @param string $mocked
	 * @param array $methods
	 * @return MockBuilder
	 */
	public function createBuilder($mocked = NULL, array $methods = array(), ArgsMatcher $argsMatcher = NULL)
	{
		if(is_object($mocked)) {
			$class = get_class($mocked);
			$name = "{$class}#{$this->mockId}";
		} else if($mocked) {
			$name = "{$mocked}#{$this->mockId}";
		} else {
			$name = "#{$this->mockId}";
		}

		return $this->createNamedBuilder($name, $mocked, $methods, $argsMatcher);
	}

	/**
	 * Create builder for named mock
	 *
	 * @param string $name user defined name
	 * @param string $mocked
	 * @param array $methods
	 * @return MockBuilder
	 */
	public function createNamedBuilder($name, $mocked = NULL, array $methods = array(), ArgsMatcher $argsMatcher = NULL)
	{
		if ($argsMatcher === NULL) {
			$argsMatcher = $this->argsMatcher;
		}
		$builder = new MockBuilder($mocked, $methods, $argsMatcher);
		$mock = $builder->getMock();
		if (isset($this->mocks[$name])) {
			throw new InvalidArgumentException("Mock with name {$name} is already registered.");
		}
		$mock->setName($name);
		$this->mocks[$name] = $mock;
		$this->mockId++;

		return $builder;
	}

	/**
	 * Get named mock
	 * @param string $name
	 * @return MockInterface
	 */
	public function getMockByName($name)
	{
		if (!isset($this->mocks[$name])) {
			throw new InvalidArgumentException("There is no mock named {$name} in the registry");
		}

		return $this->mocks[$name];
	}

	/**
	 * Get builder for named mock
	 * @param string $name
	 * @return MockBuilder
	 */
	public function getBuilderByName($name)
	{
		return MockBuilder::createFromMock($this->getMockByName($name));
	}

	/**
	 * Assert expectations on all created mocks
	 *
	 * This method should be called in tearDown method of test case.
	 */
	public function assertExpectations()
	{
		foreach ($this->mocks as $mock) {
			$mock->assertExpectations();
		}
	}

}
