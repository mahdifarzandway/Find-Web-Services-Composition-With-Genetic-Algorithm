<?php
/************************************************************************
 * / Class Individual : Genetic Algorithms
 * /
 * /************************************************************************/

require_once('fitnesscalc.php');  //supporting class file


class individual
{
    public $defaultGeneLength = 64;
    public $genes = array();  //defines an empty  array of genes arbitrary length
    public static $concreteWebService = array();
    public $fitness = 0;

    static function setConcreteWebService($concreteWebService)
    {
        individual::$concreteWebService = $concreteWebService;
    }

    static function getConcreteWebService()
    {
        return individual::$concreteWebService;
    }

    public function random()
    {
        return (float)rand() / (float)getrandmax();
    }

    // Create a random individual
    public function generateIndividual($size)
    {
        //now lets randomly load the genes (array of ascii characters)	 to the size of the array
        for ($i = 0; $i < $size; $i++) {
            $this->genes[$i] = rand(0, sizeof(individual::$concreteWebService[$i]) - 1);
        }

    }

    /* Getters and setters */
    // Use this if you want to create individuals with different gene lengths
    public function setDefaultGeneLength($length)
    {
        $this->defaultGeneLength = $length;
    }

    public function getGene($index)
    {
        return $this->genes[$index];
    }

    public function setGene($index, $value)
    {
        $this->genes[$index] = $value;
        $this->fitness = 0;
    }

    /* Public methods */
    public function size()
    {
        return count($this->genes);
    }


    public function getFitness()
    {
        if ($this->fitness === 0) {
            $this->fitness = FitnessCalc::getFitness($this);  //call static method to calculate fitness
        }
        return $this->fitness;
    }


    public function __toString()
    {
        $geneString = null;
        for ($i = 0; $i < count($this->genes); $i++) {
            $geneString .= $this->getGene($i);
        }
        return $geneString;
    }
}


?>