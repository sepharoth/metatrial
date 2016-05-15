<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class SearchController extends Controller
{
    public function search(Request $request)
    {

		if(isset($_POST['condition'])) {    
		    $condition = strtolower($_POST['condition']);
		} else {
		    $condition = "";
		}
		if(isset($_POST['intervention'])) {
		        $intervention = strtolower($_POST['intervention']);
		} else {
		        $intervention = "";
		}
		if(isset($_POST['outcome'])) {        
		        $outcome =strtolower($_POST['outcome']);
		} else {
		        $outcome = "";
		}

		$GLOBALS['debugLogging'] = true;
       	$this->debugLog('Condition: '.$condition);
       	$this->debugLog('Intervention: '.$intervention);
       	$this->debugLog('Outcome: '.$outcome);
        $allXMLs = $this->getData($condition, $intervention, $outcome);
        // dd($allXMLs);
        $result = $this->analyze($allXMLs, $condition, $intervention, $outcome);
        // dd($result);
        // Save JSON somewhere public, for passing to Shiny via GET.
        $cacheKey = $this->getRequestKey($condition, $intervention, $outcome);
        //dd($cacheKey);
        $json_path = "/public/json/".$cacheKey.".json";
        //dd($result);
        \Storage::put($json_path, $result);
        $iWantToReturn = "/json/".$cacheKey.".json";
        // echo "local JSON location is " . $json_path . "\r\n";
        // echo $result;
        return $iWantToReturn;

    }

    		function debugLog($msg) {
			if (isset($GLOBALS['debugLogging']) && $GLOBALS['debugLogging']) {
				echo 'DEBUG: '.$msg.'<br>';
			}
		}
 		function endsWith($haystack, $needle) {
                // Horrible but that's apparently the way to do it in PHP...
                return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
        }       
        function getRequestKey($condition, $intervention, $outcome) {
        		$combinedKey = $condition.$intervention.$outcome;
    			if (empty(trim($condition)) || empty(trim($intervention)) || empty(trim($outcome))) {
    				header("HTTP/1.1 500 Internal Server Error");
    				throw new \Exception("All parameters need to be provided and non-empty", 1);    				
    			}
                return md5($combinedKey);
        }
        function getCacheTmpPath($name) {
                return '/storage/app/public/'.$name;
        }
        function downladAndUnzip($url, $cacheKey) {
                $this->debugLog('url: '.$url);          
                $zipFileName = 'tmp.zip';
                $this->debugLog('zipFileName: '.$zipFileName);          
                $test_response = file_get_contents($url);
                $pathToDownladDirectory = '/public/'.$cacheKey.'/';      
                //mkdir($pathToDownladDirectory, 777, true);
                \Storage::makeDirectory($pathToDownladDirectory);
                $this->debugLog('pathToDownladDirectory: '.$pathToDownladDirectory);          
                //dd($test_response);
                \Storage::put($pathToDownladDirectory.'/'.$zipFileName, $test_response);
                //At this point we want to have made the XML file
                $path = storage_path().'/app';
                $zip = new \ZipArchive();
                // $zipper = new \Chumper\Zipper\Zipper;
                $unzip_successful = $zip->open($path.$pathToDownladDirectory.$zipFileName);
                // dd($zipper->make($pathToDownladDirectory.$zipFileName));
                 if($unzip_successful === TRUE) {
                     $zip->extractTo($path.$pathToDownladDirectory);
                     $zip->close();
                     \Storage::delete($pathToDownladDirectory.'/'.$zipFileName);
                 } else {
                     header("HTTP/1.1 500 Internal Server Error");              
                     throw new \Exception('Could not unzip file '.$path.$pathToDownladDirectory.$zipFileName);    
                 }
        }
        function readAllXMLsFromDirectory($pathToDownladDirectory) {
                $allXMLs = array();
                //dd($pathToDownladDirectory);
                $files = \Storage::files('public/'.$pathToDownladDirectory);

                foreach ($files as $file) {
                        if (endsWith($file, '.xml')) {
								$this->debugLog('file: '.$file);          
                                $xmlContent = new \SimpleXMLElement(\Storage::get($file));
                                array_push($allXMLs, $xmlContent);
                        }
                }
                return $allXMLs;

        }
        function buildRequestURL($condition, $intervention, $outcome) {
                  return 'https://clinicaltrials.gov/ct2/results/download?down_stds=all&down_typ=results'.
                        '&down_flds=shown&down_fmt=plain&term='
                        .urlencode($condition) //Condition
                        .'&rslt=With&intr='
                        .urlencode($intervention) //Intervention
                        .'&outc='
                        .urlencode($outcome) //Outcome
                        .'&show_down=Y';
        }
        function getData($condition, $intervention, $outcome) {
                $cacheKey = $this->getRequestKey($condition, $intervention, $outcome);
                $this->debugLog('cacheKey: '.$cacheKey);
                $cachePath = $this->getCacheTmpPath($cacheKey);
                $XMLPath = $cacheKey;
                $this->debugLog('cachePath: '.$cachePath);
                $allXMLs = array();
                if (!file_exists($cachePath)) { 
                	$this->debugLog('Cache MISS');                   
                    $this->downladAndUnzip($this->buildRequestURL($condition, $intervention, $outcome), $cacheKey);
                    $allXMLs = $this->readAllXMLsFromDirectory($XMLPath);
                } else {
                	$this->debugLog('Cache HIT');                   
                }
                // dd($this->readAllXMLsFromDirectory($XMLPath));            
                return $this->readAllXMLsFromDirectory($XMLPath);            
        }
        function analyze($allXMLs, $condition, $intervention, $outcome) {
        	 //Integration with Andrew's Code Here
            //First we take the array of XML objects and run the main parsing function
            $output_array = array();
            //echo "<pre>";print_r($allXMLs);echo "</pre>";
            foreach ($allXMLs as $key=>$xmlObject)
            {
                array_push($output_array,$this->trialParser($xmlObject, $outcome, $condition, $intervention));
            }
            return json_encode($output_array, JSON_PRETTY_PRINT);
        }

        //This function parses an XML result for a trial. Two inputs are needed, first an XML object, and second the STRING of the outcome being measured
function trialParser ($xmlObject, $userDefinedOutcome, $condition, $intervention)
{
        //Process: a) Searching outcomes for first occurence of of the outcomes word
	//For the sake of sanity, convert the XML object to an array:
	$input = json_decode(json_encode($xmlObject), TRUE);
	//$outcome contains the matched outcome
	//The outcomes list is stored in: $input['clinical_results']['outcome_list']
	//Cycle through the outcomes list, and try to identify the first 
	foreach ($input['clinical_results']['outcome_list']['outcome'] as $key=>$value)
	{
		//So explode the subobject by the user defined outcome.
		$tempArray = explode($userDefinedOutcome, strtolower($value['title']));
		//rint_r($tempArray);
		//Now we count the number of array elements. If >1, then the term is found
		if (count($tempArray) >= 2)
		{
			//Set the $outcome variable [i.e. the one we are meeting]
			$outcome = $value; 
			break;
		}
	}	 
	//$outcome contains the matched outcome
	/*echo "<pre>"; 
	print_r($outcome);
	echo "</pre>";*/
        //Next we need to pull the desired figures from $outcome, i.e Treated VS untreated out from this
        //Take the outcome, and convert to a 2D Array
        //In 1 direction: group ID, in the other: value
	//First populate an array with groupdata
        if(isset($outcome)) {
        	$groupData = array();
        	$counter = 0;
        
        	foreach ($outcome['group_list']['group'] as $key=>$value)
        	{	
        		$groupData[$counter]['id'] = $value['@attributes']['group_id'];
        		$groupData[$counter]['title'] = $value['title'];
                        if(isset($value['description'])) {
        		        $groupData[$counter]['description'] = $value['description'];
                        }
        		$counter++;
        	}
        
                //Set a value for units
                foreach ($outcome['measure_list']['measure'] as $key=>$value)
                {
                	$globalUnits=$value['units'];
                }
        
                //Now populate it with the POPULATION SIZE data
        	foreach ($outcome['measure_list']['measure'][0]['category_list']['category']['measurement_list']['measurement'] as $key=>$value)
        	{
        		//Lookup $value['@attributes']['group_id'] in [$groupData][$counter]['id'] and set $value['@attributes']['value']
        		//Cycle through the group data
        		foreach ($groupData as $key => $groupValue) {
        
        			if ($groupData[$key]['id'] == $value['@attributes']['group_id'])
        			{
        				$groupData[$key]['datasetSize'] = $value['@attributes']['value'];
        				$groupData[$key]['units'] = $globalUnits;
        			}
        		}
        	}
        
                //Now populate it with NUMBER MEETING CONDITION data
                if(isset($outcome['measure_list']['measure'][1]['category_list']['category']['measurement_list']['measurement']) && isset($outcome['population'])) {
        	        foreach ($outcome['measure_list']['measure'][1]['category_list']['category']['measurement_list']['measurement'] as $key=>$value)
        	        {
        	        	//Lookup $value['@attributes']['group_id'] in [$groupData][$counter]['id'] and set $value['@attributes']['value']
        	        	//Cycle through the group data
        	        	foreach ($groupData as $key => $groupValue) {
        
        	        		if ($groupData[$key]['id'] == $value['@attributes']['group_id'])
        	        		{
        	        			$groupData[$key]['numberMeetingCondition'] = $value['@attributes']['value'];
        	        		}
        	        	}
        	        }
        
                        //Now we do the stastical analysis
        
        
        
        	        /*echo "<pre>";
        	        print_r($groupData);
        	        echo "</pre>";*/
        
        	        $outputArray = array();
        	        $outputArray['trialID'] = $input['id_info']['nct_id'];
        	        $outputArray['studyDesignText'] = $input['study_design'];
        	        $outputArray['eligibilityText'] = $input['eligibility']['criteria']['textblock'];
        	        $outputArray['eligibilityGender'] = $input['eligibility']['gender'];
        	        $outputArray['eligibilityMinAge'] = $input['eligibility']['minimum_age'];
        	        $outputArray['eligibilityMaxAge'] = $input['eligibility']['maximum_age'];
                        // Check blinding of trial.
                        if(strpos(strtolower($outputArray['studyDesignText']), "double blind") || strpos(strtolower($outputArray['studyDesignText']), "double-blind")) {
                            $outputArray['studyDesignBlind'] = "double";
                        } elseif(strpos(strtolower($outputArray['studyDesignText']), "single blind") || strpos(strtolower($outputArray['studyDesignText']), "single-blind")) {
                            $outputArray['studyDesignBlind'] = "single";
                        } else {
                            $outputArray['studyDesignBlind'] = "not";
                        }
                        // Pass query terms -- TODO: pass these better, only once.
                        $outputArray['conditionTerms'] = $condition;
                        $outputArray['interventionTerms'] = $intervention;
                        $outputArray['outcomeTerms'] = $userDefinedOutcome;
        	        $outputArray['outcome']=array('type'=>$outcome['type'], 'title'=>$outcome['title'], 'description'=>$outcome['description'],'time_frame'=>$outcome['time_frame'],'population'=>$outcome['population']);
        	        $outputArray['trialResults'] = $groupData;
        	        
        	        return $outputArray;
                }
        }
        //Next we need to run stats on this to work out relative risk
	//To do this, we define the group with index $group
        //Now we output the relative risk however Ali wants it.
	//This function identifies 
	//print_r($xmlObject);
	//matchOutcomeMeasures($xmlObject, $outcome);
	
	//For this, we pull in the data for the results
	//Do Statistics
	//Offer an option for user exclusion
	//Output in JSON for a forrest plot.
}


}
