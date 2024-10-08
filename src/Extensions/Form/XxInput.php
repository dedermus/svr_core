<?php

namespace Svr\Core\Extensions\Form;

use OpenAdminCore\Admin\Form\Field;
use OpenAdminCore\Admin\Form\Field\Traits\HasValuePicker;
use OpenAdminCore\Admin\Form\Field\Traits\PlainInput;

/**
 *  XxInput class for custom input field
 *
 * @package Svr\Core\Extensions\Form
 */
class XxInput extends Field\Text
{
    protected $view = 'svr-core::form.xx_input';

    use PlainInput;
    use HasValuePicker;

    /**
     * @var string
     */
    protected $icon = 'icon-pencil-alt';

    /**
     * @var bool
     */
    protected $withoutIcon = false;

    private $valid_bootstrap = false;

    private $invalid_feedback = '';


    /**
     * Set custom fa-icon.
     *
     * @param string $icon
     *
     * @return $this
     */
    public function icon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set message of invalid.
     *
     * @param string $icon
     *
     * @return $this
     */
    public function valid_bootstrap()
    {
        $this->valid_bootstrap = true;

        return $this;
    }

    /**
     * Set message of invalid.
     *
     * @param string $icon
     *
     * @return $this
     */
    public function invalid_feedback($msg)
    {
        $this->invalid_feedback = $msg;

        return $this;
    }

    /**
     * Set custom fa-icon.
     *
     * @param string $icon
     *
     * @return $this
     */

    /**
     * Render this filed.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->setView('svr-core::form.xx_input');
        $this->initPlainInput();

        if (!$this->withoutIcon) {
            $this->prepend('<i class="'.$this->icon.'"></i>');
        }

        if ($this->valid_bootstrap) {

            $this->attribute('is-invalid');
            // $this->rules; // Все правила
            if ($this->rules) {
                foreach ($this->rules as $rule) {
                    $rule_key_value = explode(':', $rule);
                    match (trim($rule_key_value[0])) {
                        'min' => [
                            $this->attribute('minlength', trim($rule_key_value[1])),
                            $this->attribute('data-feedback-minlength', trans('validation.min', ['min' => trim($rule_key_value[1])])),
                        ],

                        'max' => [
                            $this->attribute('maxlength', trim($rule_key_value[1])),
                            $this->attribute('data-feedback-maxlength', trans('validation.max', ['max' => trim($rule_key_value[1])]))
                        ],
                        'required' => [
                            $this->attribute('required'),
                            $this->attribute('data-feedback-required', trans('validation.required'))
                        ],
                        'regex' => [
                            $this->attribute('pattern', trim($rule_key_value[1])),
                            $this->attribute('data-feedback-pattern', trans('validation.regex', ['regex' => trim($rule_key_value[1])]))
                        ],
                        default => $this->attribute('')
                    };
                }
            }
            $this->addVariables(['invalid_feedback'=>'']);  // Передадим в blade в переменную. По умолчанию будет пустая строка. JS её подменяет.
        }

        $this->defaultAttribute('type', 'text')
            ->defaultAttribute('id', $this->id)
            ->defaultAttribute('name', $this->elementName ?: $this->formatName($this->column))
            ->defaultAttribute('value', old($this->elementName ?: $this->column, $this->value()))
            ->defaultAttribute('class', 'form-control '.$this->getElementClassString())
            ->defaultAttribute('placeholder', $this->getPlaceholder())
            ->mountPicker()
            ->addVariables([
                'prepend' => $this->prepend,
                'append'  => $this->append,
            ]);

        return parent::render();
    }

    /**
     * Add inputmask to an elements.
     *
     * @param array $options
     *
     * @return $this
     */
    public function inputmask($options)
    {
        $options = json_encode_options($options);

        //$this->script = "$('{$this->getElementClassSelector()}').inputmask($options);";
        $this->script = "Inputmask({$options}).mask(document.querySelector(\"{$this->getElementClassSelector()}\"));";

        return $this;
    }

    /**
     * Add datalist element to Text input.
     *
     * @param array $entries
     *
     * @return $this
     */
    public function datalist($entries = [])
    {
        $this->defaultAttribute('list', "list-{$this->id}");

        $datalist = "<datalist id=\"list-{$this->id}\">";
        foreach ($entries as $k => $v) {
            $datalist .= "<option value=\"{$k}\">{$v}</option>";
        }
        $datalist .= '</datalist>';

        return $this->append($datalist);
    }

    /**
     * show no icon in font of input.
     *
     * @return $this
     */
    public function withoutIcon()
    {
        $this->withoutIcon = true;

        return $this;
    }
}
