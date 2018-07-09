<?php
/************************************************************************
 * / به نام خدا
 * / GA : Genetic Algorithms  main page
 * / ترکیب وب سرویس در حالت توالی
 * / کار روی الگوریتم ژنتیک و تغییراتی در انتخاب والدین برای بهتر شدن نتیجه
 * /وارد کردن درخت و پشتیبانی از ترکیب وب سرویس ها در حالات موازی شرطی حلقوی
 * /یک بار پیمایش درخت و نگه داری درخت اصلی در حافظه برای سریع تر کردن محاسبه تابع ارزش ترکیب وب سرویس ها
 * / تمیز تر کردن و سریع تر کردن الگوریتم ژنتیک
 * / بهینه سازی سراسری برای حالت موازی برای یک ویژگی کیفی یعنی زمان پاسخ
 * / تمیز تر کردن کد ورژن هشت
 * / حالت حلقه هم به بهینه سازی سراسری اضافه کردم الان فقط حالت شرطی پشتیبانی نمی شود
 * / حالت شرطی هم پشتیبانی میشود
 * /************************************************************************/

require_once('individual.php');  //supporting individual 
require_once('population.php');  //supporting population 
require_once('fitnesscalc.php');  //supporting fitnesscalc 
require_once('algorithm.php');  //supporting fitnesscalc
require_once('node.php');
require_once('tree.php');

algorithm::$uniformRate = 0.50;
algorithm::$mutationRate = 0.05;
algorithm::$poolSize = 30; /* crossover how many to select in each pool to breed from */
$initial_population_size = 100;        //how many random individuals are in initial population (generation 0)
algorithm::$max_generation_stagnant = 2000;  //maximum number of unchanged generations terminate loop
algorithm::$elitism = true;  //keep fittest individual  for next gen
$lowest_time_s = 100.00; //keeps track of lowest time in seconds
$generationCount = 0;
$generation_stagnant = 0;
$most_fit = 0;
$most_fit_last = 999999999;
$numberAbstractWebService = -1;

echo "\n-----------------------------------------------";
echo "\nUniformRate (crosssover point  where to break gene) :" . algorithm::$uniformRate;
echo "\nmutationRate (what % of genes change for each mutate) :" . algorithm::$mutationRate;
echo "\nPoolSize (crossover # of  individuals to select in each pool):" . algorithm::$poolSize;
echo "\nInitial population # individuals:" . $initial_population_size;
echo "\nelitism (keep best individual each generation true=1) :" . algorithm::$elitism . "\n";


echo "\nEnter few value for concrete web services :\n";

for ($i = 0; ($line != "" || $i == 0); $i++) {
    $line = readline();
    $webServices = explode("-", $line);
    for ($j = 0, $sizeWebServices = sizeof($webServices); $j < $sizeWebServices; ++$j) {
        $concreteWebService[$i][$j] =  explode(',',$webServices[$j]);
    }
    $numberAbstractWebService++;
}


if ($numberAbstractWebService < 1) {
    exit();
}

//$tree = array();
fitnesscalc::$sizeTree = readline("Enter Number of Node in Tree: ");

for ($i = 0; $i < fitnesscalc::$sizeTree; $i++) {
    tree::$node[$i] = new node();
    tree::$node[$i]->setType('L');
    tree::$node[$i]->setChildren([]);
    tree::$node[$i]->setDescription([]);
    tree::$node[$i]->setIsLeaf(true);
    tree::$node[$i]->setNodeNumber($i);
}


echo "\nEnter relation between web services for create tree :\n";
for ($i = 0; ($line != "" || $i == 0); $i++) {
    $line = readline();
    if ($line == "")
        break;
    $nodeNumber = explode("^", $line);
    $nodeTypeAndDescription = explode("=", $nodeNumber[1]);
    $nodeChildren = explode(",", $nodeTypeAndDescription[0]);
    $nodeType = explode("#", $nodeTypeAndDescription[1]);
    $nodeRepeatOrPercentage = explode("%",$nodeType[1]);

    tree::$node[$nodeNumber[0]]->setType($nodeType[0]);
    tree::$node[$nodeNumber[0]]->setChildren($nodeChildren);
    tree::$node[$nodeNumber[0]]->setIsLeaf(false);
    tree::$node[$nodeNumber[0]]->setDescription($nodeRepeatOrPercentage);
}

echo "\nEnter dependency between web services :\n";
for ($i = 0; ($line != "" || $i == 0); $i++) {
    $line = readline();
    if ($line == "")
        break;
    fitnesscalc::$dependency[$i] = explode("-", $line);
}

echo "\nEnter conflict between web services :\n";
for ($i = 0; ($line != "" || $i == 0); $i++) {
    $line = readline();
    if ($line == "")
        break;
    fitnesscalc::$conflict[$i] = explode("-", $line);
}


tree::setWebServiceNumberAndFatherNumber(tree::$node[0], -1, true);


fitnesscalc::$webServiceNumber = 1;
fitnesscalc::$tree = tree::$node;
$occasionWebServiceComposition = readline("Enter Occasion Web Service Composition(response time): ");
fitnesscalc::setNumberAbstractWebService($numberAbstractWebService);
individual::setConcreteWebService($concreteWebService);
fitnesscalc::dfsPostOrder(fitnesscalc::$tree[0]);

for($i = 0; $i < fitnesscalc::$sizeTree ; $i++) {
    if(tree::$node[$i]->getType() === 'L' or tree::$node[$i]->getType() === 'l'){
        tree::$node[$i]->setInputThisWebServiceInputWebService(tree::inputThisWebServiceInputWhichWebService(tree::$node[$i]));
        tree::$node[$i]->setInputThisWebServiceOutputWebService(tree::inputThisWebServiceOutputWhichWebService(tree::$node[$i]));
        tree::$node[$i]->setOutputThisWebServiceInputWebService(tree::outputThisWebServiceInputWhichWebService(tree::$node[$i]));
        tree::$node[$i]->setOutputThisWebServiceOutputWebService(tree::outputThisWebServiceOutputWhichWebService(tree::$node[$i]));
    }
}

//tree::whichWebServicesCheckForSameCompany(tree::$node[0]);


echo "\nMax Fitness is :" . fitnesscalc::getMaxFitness($occasionWebServiceComposition);
echo "\n-----------------------------------------------";

// Create an initial population
$time1 = microtime(true);
$myPop = new population($initial_population_size, true);
$myPopGetFittest = $myPop->getFittest();
$most_fit = $myPopGetFittest->getFitness();
// Evolve our population until we reach an optimum solution
while ($most_fit > $occasionWebServiceComposition) {
    $generationCount++;
    $myPop = algorithm::evolvePopulation($myPop); //create a new generation
    $myPopGetFittest = $myPop->getFittest();
    $most_fit = $myPopGetFittest->getFitness();
    if ($most_fit < $most_fit_last) {
        echo "\n Generation: " . $generationCount . " (Stagnant:" . $generation_stagnant . ") Fittest: " . $most_fit . "/" . $occasionWebServiceComposition;
        echo "  Best: " . $myPopGetFittest;
        $most_fit_last = $most_fit;
        $generation_stagnant = 0; //reset stagnant generation counter
    }
    else {
        $generation_stagnant++; //no improvement increment may want to end early

    }
    if ($generation_stagnant > algorithm::$max_generation_stagnant) {
        echo "\n-- Ending TOO MANY (" . algorithm::$max_generation_stagnant . ") stagnant generations unchanged. Ending APPROX solution below \n..)";
        break;
    }
}  //end of while loop

//we're done
$time2 = microtime(true);




echo "\nSolution at generation: " . $generationCount . " time: " . round($time2 - $time1, 10) . "s";
echo "\n---------------------------------------------------------\n";
echo "\nGenes   : ";
for($i=0 ; $i<sizeof($myPop->getFittest()->genes);++$i){
    echo $myPop->getFittest()->getGene($i).",";
}
echo "\nFittest: " . $most_fit . "\n";
echo "\n---------------------------------------------------------\n";

?>
