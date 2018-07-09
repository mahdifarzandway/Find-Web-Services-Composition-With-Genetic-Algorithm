<?php
/************************************************************************
 * / Class fitnesscalc : Genetic Algorithms
 * /
 * /************************************************************************/

require_once('individual.php');  //supporting class file
require_once('node.php');
require_once('tree.php');

class fitnesscalc
{
    public static $solution = array();  //empty array of arbitrary length
    public static $numberAbstractWebService = -1;
    public static $tree = array();
    public static $stack = array();
    public static $stackForComputeCorrelation = array();
    public static $stackNodeThatCheckForCorrelation = array();
    public static $sizeTree = -1;
    public static $webServiceNumber = 1;
    public static $dependency = array();
    public static $conflict = array();
    // Set a candidate solution as a byte array

    // To make it easier we can use this method to set our candidate solution with string of 0s and 1s
    static function setSolution($newSolution)
    {
        // Loop through each character of our string and save it in our string  array
        fitnesscalc::$solution = str_split($newSolution);
    }

    static function setNumberAbstractWebService($numberAbstractWebService)
    {
        fitnesscalc::$numberAbstractWebService = $numberAbstractWebService;
    }

    static function getNumberAbstractWebService()
    {
        return fitnesscalc::$numberAbstractWebService;
    }

    // Calculate individuals fitness by comparing it to our candidate solution
    // low fitness values are better,0=goal fitness is really a cost function in this instance
    static function getFitness($individual)
    {
        return fitnesscalc::calculateFitnessWithRelationFromStack($individual);

    }

    // Get optimum fitness
    static function getMaxFitness($maxFitness)
    {
        return $maxFitness;
    }

    static function dfsPostOrder(node $node)
    {
        for ($i = 0; $i < sizeof($node->getChildren()); $i++) {
            $nodeNumber = $node->getChildren()[$i];
            self::dfsPostOrder(fitnesscalc::$tree[$nodeNumber]);
        }

        if ($node->getType() == 'L' or $node->getType() == 'l') {
            array_push(fitnesscalc::$stack, fitnesscalc::$webServiceNumber++);
        }

        if ($node->getType() == 's' or $node->getType() == 'S') {
            array_push(fitnesscalc::$stack, sizeof($node->getChildren()) . '=s');
        }

        if ($node->getType() == 'p' or $node->getType() == 'P') {
            array_push(fitnesscalc::$stack, sizeof($node->getChildren()) . '=max');
        }

        if ($node->getType() == 'O' or $node->getType() == 'o') {
            array_push(fitnesscalc::$stack, sizeof($node->getChildren()) . '=o,' . $node->getDescription()[0]);
        }

        if ($node->getType() == 'C' or $node->getType() == 'c') {
            $tmp = sizeof($node->getChildren()) . '=c';
            for ($i = 0; $i < sizeof($node->getChildren()); $i++) {
                $tmp = $tmp . ',' . $node->getDescription()[$i];
            }
            array_push(fitnesscalc::$stack, $tmp);
        }

    }

    static function calculateFitnessWithRelationFromStack($individual)
    {
        $tmpStackInput = array();
        $tmpStackExec = array();
        $tmpStackOutput = array();

        for ($i = 0, $sizeOfStack = sizeof(fitnesscalc::$stack); $i < $sizeOfStack; ++$i) {
            if (!strpos(fitnesscalc::$stack[$i], "=")) {
                $webServiceNumber = fitnesscalc::$stack[$i];
//                array_push($tmpStack,  individual::$concreteWebService[$webServiceNumber][$individual->getGene($webServiceNumber)]);
                array_push($tmpStackInput, self::calculateGlobalOptimizationForThisWebServiceInput($individual, $webServiceNumber));
                array_push($tmpStackExec, self::calculateGlobalOptimizationForThisWebServiceExec($individual, $webServiceNumber));
                array_push($tmpStackOutput, self::calculateGlobalOptimizationForThisWebServiceOutput($individual, $webServiceNumber));

            } else {
                $numberPopAndOperationAndMultiple = explode("=", fitnesscalc::$stack[$i]);
                $operationAndMultiple = explode(",", $numberPopAndOperationAndMultiple[1]);
                $numberPop = $numberPopAndOperationAndMultiple[0];

                $answerOperation = 0;
                $answerOperationInput = 0;
                $answerOperationExec = 0;
                $answerOperationOutput = 0;

                if ($operationAndMultiple[0] === 's') {
                    for ($j = 0; $j < $numberPop; ++$j) {

                        $answerOperationInput += array_pop($tmpStackInput);
                        $answerOperationExec += array_pop($tmpStackExec);
                        $answerOperationOutput += array_pop($tmpStackOutput);

                    }
                } elseif ($operationAndMultiple[0] === 'max') {
                    for ($j = 0; $j < $numberPop; ++$j) {
                        $popValueInput = array_pop($tmpStackInput);
                        $popValueExec = array_pop($tmpStackExec);
                        $popValueOutput = array_pop($tmpStackOutput);
                        $popValue = $popValueInput + $popValueExec + $popValueOutput;
                        if ($answerOperation < $popValue) {
                            $answerOperation = $popValue;
                            $answerOperationInput = $popValueInput;
                            $answerOperationExec = $popValueExec;
                            $answerOperationOutput = $popValueOutput;
                        }
                    }
                } elseif ($operationAndMultiple[0] === 'o') {
                    for ($j = 0; $j < $numberPop; ++$j) {
                        $answerOperationInput += array_pop($tmpStackInput);
                        $answerOperationExec += array_pop($tmpStackExec) * $operationAndMultiple[1];
                        $answerOperationOutput += array_pop($tmpStackOutput);
                    }
                } elseif ($operationAndMultiple[0] === 'c') {
                    for ($j = 0; $j < $numberPop; ++$j) {
                        $answerOperationInput += array_pop($tmpStackInput) * ((float)$operationAndMultiple[$numberPop - $j] / (float)100);
                        $answerOperationExec += array_pop($tmpStackExec) * ((float)$operationAndMultiple[$numberPop - $j] / (float)100);
                        $answerOperationOutput += array_pop($tmpStackOutput) * ((float)$operationAndMultiple[$numberPop - $j] / (float)100);
                    }
                }
                array_push($tmpStackInput, $answerOperationInput);
                array_push($tmpStackExec, $answerOperationExec);
                array_push($tmpStackOutput, $answerOperationOutput);
            }
        }

        return (array_pop($tmpStackInput) + array_pop($tmpStackExec) + array_pop($tmpStackOutput));

    }

    static function calculateGlobalOptimizationForThisWebServiceInput(individual $individual, $webServiceNumber)
    {
        $node = tree::findNodeWithThisWebServiceNumber($webServiceNumber);

        $inputResponseTime = individual::$concreteWebService[$webServiceNumber - 1][$individual->getGene($webServiceNumber - 1)][1];

        $inputThisWebServiceOutputWebServiceNode = $node->getInputThisWebServiceOutputWebService();

        $inputCompany = individual::$concreteWebService[$webServiceNumber - 1][$individual->getGene($webServiceNumber - 1)][0];

        fitnesscalc::$stackForComputeCorrelation = [];
        fitnesscalc::$stackNodeThatCheckForCorrelation = [];
        fitnesscalc::dfsPostOrderForComputeCorrelationBefore(fitnesscalc::$tree[0],$individual, $inputCompany, $inputThisWebServiceOutputWebServiceNode);
        $inputResponseTime *= (1 - array_pop(fitnesscalc::$stackForComputeCorrelation));

        return $inputResponseTime;
    }

    static function calculateGlobalOptimizationForThisWebServiceExec(individual $individual, $webServiceNumber)
    {
        $executionResponseTime = individual::$concreteWebService[$webServiceNumber - 1][$individual->getGene($webServiceNumber - 1)][2];

        return $executionResponseTime;
    }

    static function calculateGlobalOptimizationForThisWebServiceOutput(individual $individual, $webServiceNumber)
    {
        $node = tree::findNodeWithThisWebServiceNumber($webServiceNumber);

        $outputResponseTime = individual::$concreteWebService[$webServiceNumber - 1][$individual->getGene($webServiceNumber - 1)][3];

        $outputThisWebServiceInputWebServiceNode = $node->getOutputThisWebServiceInputWebService();

        $outputCompany = individual::$concreteWebService[$webServiceNumber - 1][$individual->getGene($webServiceNumber - 1)][0];

        fitnesscalc::$stackForComputeCorrelation = [];
        fitnesscalc::$stackNodeThatCheckForCorrelation = [];
        fitnesscalc::dfsPostOrderForComputeCorrelationAfter(fitnesscalc::$tree[0],$individual, $outputCompany, $outputThisWebServiceInputWebServiceNode);
        $outputResponseTime *= (1 - array_pop(fitnesscalc::$stackForComputeCorrelation));


        return $outputResponseTime;
    }

    static function sameCompany(individual $individual, $inputFromWhichWebServiceNode, $outputFromWhichWebServiceNode)//TODO:ReWrite-Error Check This Function
    {

        $percentage = 1.0;

        if ($inputFromWhichWebServiceNode == -1 or $outputFromWhichWebServiceNode == -1) {
            return 1 - $percentage;
        }

        $webServiceNumber = $inputFromWhichWebServiceNode->inputWebServices[0];
        $inputCompany = individual::$concreteWebService[$webServiceNumber - 1][$individual->getGene($webServiceNumber - 1)][0];
        for ($i = 1, $sizeInputWebServices = sizeof($inputFromWhichWebServiceNode->inputWebServices); $i < $sizeInputWebServices; ++$i) {
            $webServiceNumber = $inputFromWhichWebServiceNode->inputWebServices[$i];
            if ($inputCompany != individual::$concreteWebService[$webServiceNumber - 1][$individual->getGene($webServiceNumber - 1)][0]) {
                return false;
            }
        }

        $webServiceNumber = $outputFromWhichWebServiceNode->outputWebServices[0];
        $outputCompany = individual::$concreteWebService[$webServiceNumber - 1][$individual->getGene($webServiceNumber - 1)][0];
        for ($i = 1, $sizeOutputWebServices = sizeof($outputFromWhichWebServiceNode->outputWebServices); $i < $sizeOutputWebServices; ++$i) {
            $webServiceNumber = $outputFromWhichWebServiceNode->outputWebServices[$i];
            if ($outputCompany != individual::$concreteWebService[$webServiceNumber - 1][$individual->getGene($webServiceNumber - 1)][0]) {
                return false;
            }
        }

        if ($inputCompany != $outputCompany) {
            return false;
        }

        return true;

    }

    static function violationDependency(individual $individual)
    {
        $count = 0;
        for ($i = 0, $sizeOfDependency = sizeof(fitnesscalc::$dependency); $i < $sizeOfDependency; ++$i) {
            $firstWebService    = explode(',',fitnesscalc::$dependency[$i][0]);
            $secondWebService   = explode(',',fitnesscalc::$dependency[$i][1]);

            if($individual->genes[$firstWebService[0]-1] === ($firstWebService[1]-1))
                if($individual->genes[$secondWebService[0]-1] != ($secondWebService[1]-1))
                    $count++;

            if($individual->genes[$secondWebService[0]-1] === ($secondWebService[1]-1))
                if($individual->genes[$firstWebService[0]-1] != ($firstWebService[1]-1))
                    $count++;

        }
        return $count;
    }

    static function violationConflict(individual $individual)
    {
        $count = 0;
        for ($i = 0, $sizeOfConflict = sizeof(fitnesscalc::$conflict); $i < $sizeOfConflict; ++$i) {
            $firstWebService    = explode(',',fitnesscalc::$conflict[$i][0]);
            $secondWebService   = explode(',',fitnesscalc::$conflict[$i][1]);

            if($individual->genes[$firstWebService[0]-1] === ($firstWebService[1]-1))
                if($individual->genes[$secondWebService[0]-1] === ($secondWebService[1]-1))
                    $count++;

        }
        return $count;
    }

    static function calculateViolation(individual $individual)
    {
        $violation = 0;
        $violationMax = sizeof(fitnesscalc::$conflict) + sizeof(fitnesscalc::$dependency);
        $sumOfViolation = fitnesscalc::violationDependency($individual) + fitnesscalc::violationConflict($individual);

        if($sumOfViolation === 0)
            $violation = 0;
        else
            $violation = (float)$sumOfViolation / (float)$violationMax;

        return $violation;
    }

    static function dfsPostOrderForComputeCorrelationBefore(node $node,individual $individual, $inputCompany, $outputFromWhichWebServiceNode)
    {
        if (empty($outputFromWhichWebServiceNode))
            return;

        for ($i = 0; $i < sizeof($node->getChildren()); $i++) {
            $nodeNumber = $node->getChildren()[$i];
            $outputFromWhichWebServiceNode = self::dfsPostOrderForComputeCorrelationBefore(fitnesscalc::$tree[$nodeNumber],$individual, $inputCompany, $outputFromWhichWebServiceNode);
        }

        if ($node->getType() == 'L' or $node->getType() == 'l') {
            if (($key = array_search($node, $outputFromWhichWebServiceNode)) !== false) {
                unset($outputFromWhichWebServiceNode[$key]);
                if ($inputCompany == individual::$concreteWebService[$node->getWebServiceNumber() - 1][$individual->getGene($node->getWebServiceNumber() - 1)][0]) {
                    array_push(fitnesscalc::$stackForComputeCorrelation, 1);
                }
                else {
                    array_push(fitnesscalc::$stackForComputeCorrelation, 0);
                }
            }
        }

        if ($node->getType() == 's' or $node->getType() == 'S') {}

        if ($node->getType() == 'p' or $node->getType() == 'P') {
            $tmp = 2000;
            for ($i = 0, $sizeChildren = sizeof($node->getChildren()); $i < $sizeChildren; ++$i) {
                $tmp2 = array_pop(fitnesscalc::$stackForComputeCorrelation);
                if ($tmp > $tmp2)
                    $tmp = $tmp2;
            }
            array_push(fitnesscalc::$stackForComputeCorrelation, $tmp);
        }

        if ($node->getType() == 'O' or $node->getType() == 'o') {}

        if ($node->getType() == 'C' or $node->getType() == 'c') {
            $tmp = 0;
            for ($i = 0, $sizeChildren = sizeof($node->getChildren()); $i < $sizeChildren; ++$i) {
                $tmp += array_pop(fitnesscalc::$stackForComputeCorrelation) * ($node->getDescription()[($sizeChildren - 1) - $i] / 100);
            }
            array_push(fitnesscalc::$stackForComputeCorrelation, $tmp);
        }
        return $outputFromWhichWebServiceNode;
    }

    static function dfsPostOrderForComputeCorrelationAfter(node $node,individual $individual, $outputCompany, $inputFromWhichWebServiceNode)
    {
        if (empty($inputFromWhichWebServiceNode)){
            return;
        }

        for ($i = 0; $i < sizeof($node->getChildren()); $i++) {
            $nodeNumber = $node->getChildren()[$i];
            $inputFromWhichWebServiceNode = self::dfsPostOrderForComputeCorrelationAfter(fitnesscalc::$tree[$nodeNumber],$individual, $outputCompany, $inputFromWhichWebServiceNode);
        }

        if ($node->getType() == 'L' or $node->getType() == 'l') {
            if (($key = array_search($node, $inputFromWhichWebServiceNode)) !== false) {
                unset($inputFromWhichWebServiceNode[$key]);
                if ($outputCompany == individual::$concreteWebService[$node->getWebServiceNumber() - 1][$individual->getGene($node->getWebServiceNumber() - 1)][0]){
                    array_push(fitnesscalc::$stackForComputeCorrelation, 1);
                }
                else {
                    array_push(fitnesscalc::$stackForComputeCorrelation, 0);
                }
            }
        }

        if ($node->getType() == 's' or $node->getType() == 'S') {}

        if ($node->getType() == 'p' or $node->getType() == 'P') {
            $tmp = 2000;
            for ($i = 0, $sizeChildren = sizeof($node->getChildren()); $i < $sizeChildren; ++$i) {
                $tmp2 = array_pop(fitnesscalc::$stackForComputeCorrelation);
                if ($tmp > $tmp2)
                    $tmp = $tmp2;
            }
            array_push(fitnesscalc::$stackForComputeCorrelation, $tmp);
        }

        if ($node->getType() == 'O' or $node->getType() == 'o') {}

        if ($node->getType() == 'C' or $node->getType() == 'c') {
            $tmp = 0;
            for ($i = 0, $sizeChildren = sizeof($node->getChildren()); $i < $sizeChildren; ++$i) {
                $tmp += array_pop(fitnesscalc::$stackForComputeCorrelation) * ($node->getDescription()[($sizeChildren - 1) - $i] / 100);
            }
            array_push(fitnesscalc::$stackForComputeCorrelation, $tmp);
        }
        return $inputFromWhichWebServiceNode;

    }

}  //end class


?>