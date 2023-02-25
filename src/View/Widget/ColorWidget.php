<?php
declare(strict_types=1);

/**
 * ColorWidget
 */

namespace Fr3nch13\Utilities\View\Widget;

/**
 * Color Widget
 *
 * Renders the html color widget on the frontend.
 */
class ColorWidget implements \Cake\View\Widget\WidgetInterface
{
    /**
     * StringTemplate instance.
     *
     * @var \Cake\View\StringTemplate
     */
    protected $_templates;

    /**
     * Constructor.
     *
     * @param \Cake\View\StringTemplate $templates Templates list.
     */
    public function __construct($templates)
    {
        $this->_templates = $templates;
    }

    /**
     * Render a text widget or other simple widget like email/tel/number.
     *
     * This method accepts a number of keys:
     *
     * - `name` The name attribute.
     * - `val` The value attribute.
     * - `escape` Set to false to disable escaping on all attributes.
     *
     * Any other keys provided in $data will be converted into HTML attributes.
     *
     * @param array<string, mixed> $data The data to build an input with.
     * @param \Cake\View\Form\ContextInterface $context The current form context.
     * @return string
     */
    public function render(array $data, \Cake\View\Form\ContextInterface $context): string
    {
        $data += [
            'name' => '',
            'val' => null,
            'type' => 'text',
            'escape' => true,
            'templateVars' => [
                'color' => 'color',
            ],
        ];
        $data['value'] = $data['val'];
        unset($data['val']);

        return $this->_templates->format('input', [
            'name' => $data['name'],
            'type' => $data['type'],
            'templateVars' => $data['templateVars'],
            'attrs' => $this->_templates->formatAttributes(
                $data,
                ['name', 'type']
            ),
        ]);
    }

    /**
     * Makes sure an array is returned.
     *
     * @param array<string, mixed> $data The data to be properly formatted.
     * @return array<mixed> The data formatted into an array.
     */
    public function secureFields(array $data = []): array
    {
        if (!isset($data['name']) || $data['name'] === '') {
            return [];
        }

        return [$data['name']];
    }
}
