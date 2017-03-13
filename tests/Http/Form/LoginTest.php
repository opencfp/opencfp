<?php

namespace OpenCFP\Test\Http\Form;

use Mockery as m;
use OpenCFP\Http\Form\Entity\Login as LoginEntity;
use OpenCFP\Http\Form\Login as LoginForm;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * @var FormBuilder
     */
    protected $builder;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    protected function setUp()
    {
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->getFormFactory();
        $this->dispatcher = m::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    protected function getExtensions()
    {
        $this->validator = m::mock(ValidatorInterface::class);
        $this->validator
            ->shouldReceive('validate')
            ->andReturn(new ConstraintViolationList());
        $this->validator
            ->shouldReceive('getMetadataFor')
            ->andReturn(new ClassMetadata('Symfony\Component\Form\Form'));

        return [
            new ValidatorExtension($this->validator),
        ];
    }

    public function testSubmitValidData()
    {
        $formData = [
            'email' => 'you@domain.org',
            'password' => 'test',
        ];

        $form = $this->factory
            ->createBuilder(LoginForm::class, new LoginEntity())
            ->getForm();

        $loginEntity = new LoginEntity;
        $loginEntity->setEmail($formData['email']);
        $loginEntity->setPassword($formData['password']);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($loginEntity, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
