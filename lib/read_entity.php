<?php

    function findAllOccurrences($string, $word) 
    {
        $positions = [];
        $offset = 0;
        
        while (($position = strpos($string, $word, $offset)) !== false) {
            $positions[] = $position;
            $offset = $position + strlen($word);
        }
        
        return $positions;
    }

    function getMethodName($string, $position)
    {
    
    	$p = $position;
        $n = 0;
    	while(true){
        	$p++;
            $n++;
        	if(substr($string,$p,1) == '(')
            {
            	break;
            }
            
        }
        
        return substr($string,$position,$n);
        
    } 

    function toThisName($string)
    {
        $thisName = substr($string, 3, 100);
        $text = str_replace('()', '', $thisName);
        $textArray = str_split($text);
        $output = '';
        foreach($textArray as $i => $w)
        {
            if(ctype_upper($w) AND $i > 0)
            {
                $output = $output.'_'.strtolower($w);
            }
            else
            {
                $output = $output.strtolower($w);
            }
        }
        return $output;
    }    


    function getEntityMethods($type, $entityString)
    {
        $positions = findAllOccurrences($entityString, $type);
        $names = array();
        foreach ($positions as $i => $p) {
            $method = getMethodName($entityString, $positions[$i]);
            $thisName = toThisName($method);
            if($type === 'get')
            {
                array_push($names, '"'.$thisName.'" => $obj->'.$method.'(),');
            }
            else 
            {
                array_push($names, '$obj->'.$method.'($data->'.$thisName.');');
            }
        }

        return $names;
    }