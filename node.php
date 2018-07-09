<?php

require_once('tree.php');


/**
 * Created by PhpStorm.
 * User: mahdifarzandway
 * Date: 4/7/2017 AD
 * Time: 4:33 PM
 */
class node
{
    private $type;
    private $children = array();
    private $value;
    private $nodeNumber;
    private $webServiceNumber;
    private $isLeaf;
    private $description = array();
    private $fatherNumber;
    private $currentChild;
    private $inputThisWebServiceInputWebService;
    private $inputThisWebServiceOutputWebService;
    private $outputThisWebServiceInputWebService;
    private $outputThisWebServiceOutputWebService;

    public $inputWebServices = array();
    public $outputWebServices = array();


    public function leftChildNode()
    {
        $indexCurrentChildren = array_search($this->getCurrentChild()->getNodeNumber(), $this->getChildren()) - 1;
        if ($indexCurrentChildren < 0) {
            return -1;
        }
        return tree::$node[$this->getChildren()[$indexCurrentChildren]];
    }

    public function rightChildNode()
    {
        $indexCurrentChildren = array_search($this->getCurrentChild()->getNodeNumber(), $this->getChildren()) + 1;
        if ($indexCurrentChildren === sizeof($this->getChildren())) {
            return -1;
        }
        return tree::$node[$this->getChildren()[$indexCurrentChildren]];
    }

    public function getFatherNode()
    {
        return tree::$node[$this->getFatherNumber()];
    }

    /**
     * @return mixed
     */
    public function getCurrentChild()
    {
        return $this->currentChild;
    }

    /**
     * @param mixed $currentChild
     */
    public function setCurrentChild($currentChild)
    {
        $this->currentChild = $currentChild;
    }


    /**
     * @return mixed
     */
    public function getInputThisWebServiceInputWebService()
    {
        return $this->inputThisWebServiceInputWebService;
    }

    /**
     * @param mixed $inputThisWebServiceInputWebService
     */
    public function setInputThisWebServiceInputWebService($inputThisWebServiceInputWebService)
    {
        $this->inputThisWebServiceInputWebService = $inputThisWebServiceInputWebService;
    }

    /**
     * @return mixed
     */
    public function getOutputThisWebServiceInputWebService()
    {
        return $this->outputThisWebServiceInputWebService;
    }

    /**
     * @param mixed $outputThisWebServiceInputWebService
     */
    public function setOutputThisWebServiceInputWebService($outputThisWebServiceInputWebService)
    {
        $this->outputThisWebServiceInputWebService = $outputThisWebServiceInputWebService;
    }

    /**
     * @return mixed
     */
    public function getOutputThisWebServiceOutputWebService()
    {
        return $this->outputThisWebServiceOutputWebService;
    }

    /**
     * @param mixed $outputThisWebServiceOutputWebService
     */
    public function setOutputThisWebServiceOutputWebService($outputThisWebServiceOutputWebService)
    {
        $this->outputThisWebServiceOutputWebService = $outputThisWebServiceOutputWebService;
    }


    /**
     * @return mixed
     */
    public function getInputThisWebServiceOutputWebService()
    {
        return $this->inputThisWebServiceOutputWebService;
    }

    /**
     * @param mixed $inputThisWebServiceOutputWebService
     */
    public function setInputThisWebServiceOutputWebService($inputThisWebServiceOutputWebService)
    {
        $this->inputThisWebServiceOutputWebService = $inputThisWebServiceOutputWebService;
    }

    /**
     * @return mixed
     */
    public function getFatherNumber()
    {
        return $this->fatherNumber;
    }

    /**
     * @param mixed $fatherNumber
     */
    public function setFatherNumber($fatherNumber)
    {
        $this->fatherNumber = $fatherNumber;
    }


    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getWebServiceNumber()
    {
        return $this->webServiceNumber;
    }

    /**
     * @param mixed $webServiceNumber
     */
    public function setWebServiceNumber($webServiceNumber)
    {
        $this->webServiceNumber = $webServiceNumber;
    }

    /**
     * @return mixed
     */
    public function getIsLeaf()
    {
        return $this->isLeaf;
    }

    /**
     * @param mixed $isLeaf
     */
    public function setIsLeaf($isLeaf)
    {
        $this->isLeaf = $isLeaf;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param array $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getNodeNumber()
    {
        return $this->nodeNumber;
    }

    /**
     * @param mixed $nodeNumber
     */
    public function setNodeNumber($nodeNumber)
    {
        $this->nodeNumber = $nodeNumber;
    }


}