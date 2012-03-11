<?php
/**
 * @package	ImpressPages
 * @copyright	Copyright (C) 2012 ImpressPages LTD.
 * @license	GNU/GPL, see ip_license.html
 */

namespace Library\IpForm;



class Form{
    const METHOD_POST = 'post';
    const METHOD_GET = 'get';

    protected $pages;
    protected $method;
    protected $action;
    protected $attributes;

    public function __construct() {
        $this->fieldsets = array();
        $this->method = self::METHOD_POST;
        $this->action = '';
        $this->pages = array();
        $this->attributes = array();
    }

    /**
     *
     * Check if data passes form validation rules
     * @param array $data - post data from user or other source.
     * @return array errors. Array key - error field name. Value - error message. Empty array means there are no errors.
     */
    public function validate($data) {
        $fields = $this->getFields();
        $errors = array();
        foreach($fields as $field) {
            $error = $field->validate($data, $field->getName());
            if ($error) {
                $errors[$field->getName()] = $error;
            }
        }
        return $errors;
    }

    /**
     *
     * Filter data array. Return only those records that are expected according to form field names.
     * @param array $data
     * @return array
     */
    public function filterValues($data) {
        $answer = array();
        $fields = $this->getFields();
        foreach($fields as $field) {
            if (isset($data[$field->getName()])) {
                $answer[$field->getName()] = $data[$field->getName()];
            }
        }
        return $answer;
    }

    public function addPage(Page $page) {
        $this->pages[] = $page;
    }

    public function addFieldset(Fieldset $fieldset) {
        if (count($this->pages) == 0) {
            $this->addPage(new Page());
        }
        end($this->pages)->addFieldset($fieldset);
    }

    /**
     *
     * Add field to last fielset. Create fieldset if does not exist.
     * @param Field $field
     */
    public function addField(Field\Field $field) {
        if (count($this->pages) == 0) {
            $this->addPage(new Page());
        }
        end($this->pages)->addField($field);
    }

    /**
     * Return all pages
     */
    public function getPages() {
        return $this->pages;
    }

    /**
     *
     * Set post method.
     * @param string $method Use \Library\IpForm\Form::METHOD_POST or \Library\IpForm\Form::METHOD_GET
     * @throws Exception
     */
    public function setMethod($method) {
        switch($method) {
            case self::METHOD_POST:
            case self::METHOD_GET:
                $this->method = $method;
                break;
            default:
                throw new Exception ('Unknown method "'.$method.'"', Exception::INCORRECT_METHOD_TYPE);
        }
    }

    public function getMethod() {
        return $this->method;
    }


    public function setAction($action) {
        $this->action = $action;
    }

    public function getAction() {
        return $this->action;
    }

    public function render(\Ip\View $view = null) {
        if (!$view) {
            $view = \Ip\View::create('view/form.php');
        }
        $view->setData(array('form' => $this));
        return $view->render();
    }

    public function getFields() {
        $pages = $this->getPages();
        $fields = array();
        foreach ($pages as $page) {
            $fields = array_merge($fields, $page->getFields());
        }
        return $fields;
    }

    /**
     *
     * Add attribute to the form
     * @param stsring $name
     * @param string $value
     */
    public function addAttribute($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function getAttributesStr() {
        $answer = '';
        foreach ($this->getAttributes() as $attributeKey => $attributeValue) {
            $answer .= ' '.htmlspecialchars($attributeKey).'="'.htmlspecialchars($attributeValue).'"';
        }
        return $answer;
    }

    public function writeToDatabase($table, $data, $additionalValues = array()) {
        if(count($this->getFields()) == 0 && count($additionalValues) == 0){
            return false;
        }
        
        $sql = 'INSERT INTO '.$table.' SET ';
        $first = true;
        foreach($this->getFields() as $key => $field){
            if($field->getDbField()){
                if(!$first) {
                    $sql .= ', ';
                }
                if (!isset($data[$field->getDbField()])) {
                    $sqlValue = 'NULL';
                } else {
                    $sqlValue = "'".mysql_real_escape_string($data[$field->getDbField()])."'";
                }
                $sql .= "`".mysql_real_escape_string($field->getDbKey())."` = ".$sqlValue." ";
                $first = false;
            }
        }

        
        if($additionalValues)
            foreach($additionalValues as $key => $additionalValue){
                if(!$first) {
                    $sql .= ', ';
                }
                if (!isset($data[$field->getDbField()])) {
                    $sqlValue = 'NULL';
                } else {
                    $sqlValue = "'".mysql_real_escape_string($data[$field->getDbField()])."'";
                }
                $sql .= "`".mysql_real_escape_string($key)."` = ".$sqlValue." ";
                $first = false;
                
            }
        }
        
        
        if(!$first){ //if exist fields
            return false;//there is no fields to be stored
        }
        
        $rs = mysql_query($sql);
        if(!$rs){
            throw new \Exception($sql." ".mysql_error());
            return false;
        }else{
            return mysql_insert_id();
        }
        
    }

    public function __toString() {
        $this->render();
    }


}