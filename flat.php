<?php
/*function flatten($array) {
    if (!is_array($array)) {
        // nothing to do if it's not an array
        return array($array);
    }

    $result = array();
    foreach ($array as $value) {
        // explode the sub-array, and add the parts
        $result = array_merge($result, flatten($value));
    }

    return $result;
}*/
function array_2_csv($array) {
$csv = array();
foreach ($array as $item=>$val) {
    if (is_array($val)) { 
        $csv[] = $this->array_2_csv($val);
        $csv[] = "\n";
    } else {
        $csv[] = "'".$val."'";
    }
}
return implode(',', $csv);
}
$arr =array(  array ('mname' => 'Peter Saunders'),array ('mname' => 'Jennifer Saunders'));
 $arr = array( 0 => '2307pas@gmail.com',
  1 => 'annekinsella@hotmail.com',
  2 => 'aseret1956@yahoo.de');
var_export($arr);
//var_export(flatten($arr));
	$output = iterator_to_array(new RecursiveIteratorIterator(
    new RecursiveArrayIterator($arr)), FALSE);
var_export($output);

$output = array_2_csv($arr);
var_export($output);
echo "\n";
echo "(".$output.")";
echo "\n";
