<?php

class TabularForm extends Table {

    private $Form = NULL;
    private $RowType = false;
    protected $Select = NULL;
    private $SelectedValue = NULL;

    public function __construct($caption, $css_class) {
      parent::__construct($caption, NULL, $css_class);
      $this->AddElement('id', $css_class);
    }

    public function AddForm($form) {
        $this->Form = $form;
        if ($this->Row === NULL) {
            $body = $this->Body;
            $this->Body = $form;
            $this->Body->AddChild($body);
        } else {
            $this->RowType = true;
        }
    }

    public function NewRow($css_class=NULL) {
        if ($this->RowType === true) {
            $this->Form->AddChild($this->Row);
            $this->Row = $this->Form;
            $this->RowType = false;
        }
        parent::NewRow($css_class);
    }

    public function AddText($name, $value=NULL, $maxlength=255) {
        $this->AddSelectChild();

        $css_class = ereg_replace("\[.*\]", "", $name);
        $element = new FormElement();
        $input = new FormInput('text', $name, $value, $maxlength);
        $element->AddChild($input->Build());
        $element->AddElement('class', $css_class);
        $this->AddCell($element, $css_class);
    }

    public function AddCheckbox($name, $value=NULL) {
        $this->AddSelectChild();

        $css_class = ereg_replace("\[.*\]", "", $name);
        $element = new FormElement();
        $input = new FormInput('checkbox', $name, $value);
        $element->AddChild($input->Build());
        $this->AddCell($element, $css_class);
    }

    public function AddHidden($name, $value=NULL) {
        $this->AddSelectChild();

        $element = new FormElement();
        $input = new FormInput('hidden', $name, $value);
        $element->AddChild($input->Build());
        $this->AddCell($element);
    }

    public function NewSelect($name, $value=NULL) {
        $this->AddSelectChild();

        $css_class = ereg_replace("\[.*\]", "", $name);
        $this->Select = new FormSelect($name);
        $this->SelectedValue = $value;
        $this->Select->SetAttribute('class', $css_class);
    }

    public function AddSelectOption($name, $value=NULL) {
        if ($this->SelectedValue != NULL && $value == $this->SelectedValue)
            $this->Select->AddOption($name, $value, true);
        elseif ($this->SelectedValue != NULL && $name == $this->SelectedValue)
            $this->Select->AddOption($name, $value, true);
        else
            $this->Select->AddOption($name, $value);
    }

    public function AddSubmit($value='Submit', $name='submit') {
        $this->AddSelectChild();

        $element = new FormElement();
        $input = new FormInput('submit', $name, $value);
        $element->AddChild($input->Build());
        $this->AddCell($element);
    }

    public function AddSelectChild() {
        if ($this->Select != NULL) {
           $element = new FormElement();
           $element->AddChild($this->Select);
           $this->AddCell($element);

           $this->Select = NULL;
        }
    }

    public function Build() {
        $this->Form->Validate();
        parent::Build();
    }
}

?>
