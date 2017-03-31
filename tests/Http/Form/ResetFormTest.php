<?php

namespace OpenCFP\Test\Http\Form;

use Mockery as m;
use OpenCFP\Http\Form\ResetForm;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResetFormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    protected function setUp()
    {
        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->getFormFactory();
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();
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

    /**
     * Test that form object correctly synchronizes the different
     * data formats, correctly sets user-submitted data for all
     * fields on form submission and correctly creates its view.
     *
     * @test
     */
    public function submitValidData()
    {
        $formData = [
            'password' => [
                'password' => 'test',
                'password2' => 'test',
            ],
            'user_id' => '42',
            'reset_code' => '123',
        ];

        $form = $this->factory
            ->createBuilder(ResetForm::class)
            ->getForm();

        // submit the data to the form directly
        $form->submit($formData);

        // Repeated password field becomes single field after form submission
        $formData['password'] = 'test';

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($formData, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    /**
     * Test that the form object correctly returns its name.
     *
     * @test
     */
    public function getFormName()
    {
        $form = $this->factory
            ->createBuilder(ResetForm::class)
            ->getForm();

        $this->assertSame('reset_form', $form->getName());
    }
}
