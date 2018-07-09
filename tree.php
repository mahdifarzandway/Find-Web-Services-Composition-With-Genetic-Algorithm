<?php

require_once('node.php');

/**
 * Created by PhpStorm.
 * User: mahdifarzandway
 * Date: 4/28/2017 AD
 * Time: 12:20 PM
 */
class tree
{
    public static $node = array();
    public static $webServiceNumber = 1;
    public static $nodeNumber = 1;
    public static $arrayRightOrLeftChildrenNodes = array();

    public static function findNodeWithThisWebServiceNumber($webServiceNumberForFindNode)
    {
        for ($i = 0, $sizeOfTree = sizeof(self::$node); $i < $sizeOfTree; ++$i)
        {
            if (self::$node[$i]->getWebServiceNumber() === $webServiceNumberForFindNode) {
                return self::$node[$i];
            }
        }
    }

    public static function getPointerWithThisNodeNumber($nodeNumberForFindNode)
    {
        for ($i = 0, $sizeOfTree = sizeof(self::$node); $i < $sizeOfTree; ++$i)
        {
            if (self::$node[$i]->getNodeNumber() == $nodeNumberForFindNode) {
                return self::$node[$i];
            }
        }
    }

    public static function setWebServiceNumberAndFatherNumber(node $node, $fatherNumber, $setNodeNumber = false)
    {
        for ($i = 0, $countChildren = sizeof($node->getChildren()); $i < $countChildren; $i++) {
            $nodeNumber = $node->getChildren()[$i];
            self::setWebServiceNumberAndFatherNumber(self::$node[$nodeNumber], $node->getNodeNumber());
        }

        if ($node->getType() == 'L' or $node->getType() == 'l') {
            $node->setWebServiceNumber(self::$webServiceNumber++);
        }

        $node->setFatherNumber($fatherNumber);
    }

    public static function inputThisWebServiceInputWhichWebService(node $node)
    {
        return $node;
    }

    public static function inputThisWebServiceOutputWhichWebService(node $node)
    {
        if ($node->getType() === 's' or $node->getType() === 'S') {
            if(($node->leftChildNode() == -1 and $node->getFatherNode() == null) or ($node->leftChildNode() != -1))
            {
                self::$arrayRightOrLeftChildrenNodes = [];
                self::findLeftChildrenNodes($node->leftChildNode(),1);
                return self::$arrayRightOrLeftChildrenNodes;
            }
        }
        $fatherNode = $node->getFatherNode();
        $fatherNode->setCurrentChild($node);
        return self::inputThisWebServiceOutputWhichWebService($fatherNode);
    }

    public static function outputThisWebServiceInputWhichWebService(node $node)
    {
        if ($node->getType() === 's' or $node->getType() === 'S') {
            if(($node->rightChildNode() == -1 and $node->getFatherNode() == null) or ($node->rightChildNode() != -1))
            {
                self::$arrayRightOrLeftChildrenNodes = [];
                self::findRightChildrenNodes($node->rightChildNode(),1);
                return self::$arrayRightOrLeftChildrenNodes;
            }
        }
        $fatherNode = $node->getFatherNode();
        $fatherNode->setCurrentChild($node);
        return self::outputThisWebServiceInputWhichWebService($fatherNode);
    }

    public static function outputThisWebServiceOutputWhichWebService(node $node)
    {
        return $node;
    }

    public static function whichWebServicesCheckForSameCompany(node $node)
    {
        for ($i = 0, $sizeNodeChildren = sizeof($node->getChildren()); $i < $sizeNodeChildren; $i++) {
            $nodeNumber = $node->getChildren()[$i];
            self::whichWebServicesCheckForSameCompany(fitnesscalc::$tree[$nodeNumber]);
        }

        if ($node->getType() === 'L' or $node->getType() === 'l') {
            array_push($node->inputWebServices, $node->getWebServiceNumber());
            array_push($node->outputWebServices, $node->getWebServiceNumber());
        }

        if ($node->getType() === 's' or $node->getType() === 'S') {
            $node->inputWebServices = array_merge($node->inputWebServices, tree::$node[$node->getChildren()[0]]->inputWebServices);
            $node->outputWebServices = array_merge($node->outputWebServices, tree::$node[end($node->getChildren())]->outputWebServices);
        }

        if ($node->getType() === 'p' or $node->getType() === 'P') {
            for ($i = 0, $sizeNodeChildren = sizeof($node->getChildren()); $i < $sizeNodeChildren; $i++) {
                $node->inputWebServices = array_merge($node->inputWebServices, tree::$node[$node->getChildren()[$i]]->inputWebServices);
                $node->outputWebServices = array_merge($node->outputWebServices, tree::$node[$node->getChildren()[$i]]->outputWebServices);
            }
        }
    }

    public static function findRightChildrenNodes($node,$callPercentageNode=1)
    {
        if($node === -1)
            return [];

        if($node->getType() === 'l' or $node->getType() === 'L') {
//            $nodeAndCallPercentage = array($node,$callPercentageNode);
            array_push(self::$arrayRightOrLeftChildrenNodes,$node);
        }
        if($node->getType() === 's' or $node->getType() === 'S') {
            $childNode = self::getPointerWithThisNodeNumber($node->getChildren()[0]);
            self::findRightChildrenNodes($childNode,$callPercentageNode);
        }
        if($node->getType() === 'p' or $node->getType() === 'P') {
            for ($i = 0, $sizeNodeChildren = sizeof($node->getChildren()); $i < $sizeNodeChildren; $i++) {
                $childNode = self::getPointerWithThisNodeNumber($node->getChildren()[$i]);
                self::findRightChildrenNodes($childNode,$callPercentageNode);
            }
        }
        if($node->getType() === 'c' or $node->getType() === 'C') {
            for ($i = 0, $sizeNodeChildren = sizeof($node->getChildren()); $i < $sizeNodeChildren; $i++) {
                $childNode = self::getPointerWithThisNodeNumber($node->getChildren()[$i]);
//                self::findRightChildrenNodes($childNode,(($node->getDescription()[$i]*$callPercentageNode)/100));
                self::findRightChildrenNodes($childNode);
            }
        }
        if($node->getType() === 'o' or $node->getType() === 'O') {
            $childNode = self::getPointerWithThisNodeNumber($node->getChildren()[0]);
            self::findRightChildrenNodes($childNode,$callPercentageNode);
        }
    }

    public static function findLeftChildrenNodes($node,$callPercentageNode=1)
    {
        if($node === -1)
            return [];

        if($node->getType() === 'l' or $node->getType() === 'L') {
            //$nodeAndCallPercentage = array($node,$callPercentageNode);
            array_push(self::$arrayRightOrLeftChildrenNodes,$node);
        }
        if($node->getType() === 's' or $node->getType() === 'S') {
            $childNode = self::getPointerWithThisNodeNumber(end($node->getChildren()));
            self::findLeftChildrenNodes($childNode,$callPercentageNode);
        }
        if($node->getType() === 'p' or $node->getType() === 'P') {
            for ($i = 0, $sizeNodeChildren = sizeof($node->getChildren()); $i < $sizeNodeChildren; $i++) {
                $childNode = self::getPointerWithThisNodeNumber($node->getChildren()[$i]);
                self::findLeftChildrenNodes($childNode,$callPercentageNode);
            }
        }
        if($node->getType() === 'c' or $node->getType() === 'C') {
            for ($i = 0, $sizeNodeChildren = sizeof($node->getChildren()); $i < $sizeNodeChildren; $i++) {
                $childNode = self::getPointerWithThisNodeNumber($node->getChildren()[$i]);
//                self::findRightChildrenNodes($childNode,($node->getDescription()[$i]*$callPercentageNode));
                self::findLeftChildrenNodes($childNode);
            }
        }
        if($node->getType() === 'o' or $node->getType() === 'O') {
            $childNode = self::getPointerWithThisNodeNumber($node->getChildren()[0]);
            self::findLeftChildrenNodes($childNode,$callPercentageNode);
        }
    }


}