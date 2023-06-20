<?php
declare(strict_types=1);

namespace Fr3nch13\Utilities\Test\TestCase\View\Widget;

use Cake\TestSuite\TestCase;
use Cake\View\Form\NullContext;
use Cake\View\StringTemplate;
use Fr3nch13\Utilities\View\Widget\ColorWidget;

/**
 * Basic input test.
 */
class ColorWidgetTest extends TestCase
{
    /**
     * @var \Cake\View\Form\NullContext
     */
    protected $context;

    /**
     * @var \Cake\View\StringTemplate
     */
    protected $templates;

    public function setUp(): void
    {
        parent::setUp();
        $templates = [
            'input' => '<input type="{{type}}" name="{{name}}"{{attrs}}>',
        ];
        $this->templates = new StringTemplate($templates);
        $this->context = new NullContext([]);
    }

    /**
     * Test render in a simple case.
     */
    public function testRenderSimple(): void
    {
        $color = new ColorWidget($this->templates);
        $result = $color->render(['name' => 'my_input'], $this->context);
        $expected = [
            'input' => ['type' => 'color', 'name' => 'my_input'],
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Test render with a value
     */
    public function testRenderWithValue(): void
    {
        $color = new ColorWidget($this->templates);
        $data = [
            'name' => 'my_input',
            'val' => 'ffffff',
        ];
        $result = $color->render($data, $this->context);
        $expected = [
            'input' => [
                'type' => 'color',
                'name' => 'my_input',
                'value' => 'ffffff',
            ],
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Test render with additional attributes.
     */
    public function testRenderAttributes(): void
    {
        $color = new ColorWidget($this->templates);
        $data = [
            'name' => 'my_input',
            'type' => 'color',
            'class' => 'form-control',
            'required' => true,
        ];
        $result = $color->render($data, $this->context);
        $expected = [
            'input' => [
                'type' => 'color',
                'name' => 'my_input',
                'class' => 'form-control',
                'required' => 'required',
            ],
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Test render with template params.
     */
    public function testRenderTemplateParams(): void
    {
        $color = new ColorWidget(new StringTemplate([
            'input' => '<input type="{{type}}" name="{{name}}"{{attrs}}><span>{{help}}</span>',
        ]));
        $data = [
            'name' => 'my_input',
            'type' => 'color',
            'class' => 'form-control',
            'required' => true,
            'templateVars' => ['help' => 'SOS'],
        ];
        $result = $color->render($data, $this->context);
        $expected = [
            'input' => [
                'type' => 'color',
                'name' => 'my_input',
                'class' => 'form-control',
                'required' => 'required',
            ],
            '<span', 'SOS', '/span',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Test that secureFields omits other keys
     */
    public function testSecureFields(): void
    {
        $color = new ColorWidget($this->templates);
        $data = [
            'name' => 'my_input',
            'something' => 'else',
        ];
        $result = $color->secureFields($data);
        $expected = [
            'my_input',
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that secureFields check for an empty name
     */
    public function testSecureFieldsEmptyName(): void
    {
        $color = new ColorWidget($this->templates);
        $data = [
            'name' => '',
            'something' => 'else',
        ];
        $result = $color->secureFields($data);
        $expected = [];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that secureFields check for missing name
     */
    public function testSecureFieldsMissingName(): void
    {
        $color = new ColorWidget($this->templates);
        $data = [
            'something' => 'else',
        ];
        $result = $color->secureFields($data);
        $expected = [];
        $this->assertEquals($expected, $result);
    }
}
