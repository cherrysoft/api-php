--TEST--
JsonSchema: register a schema, schema validation
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$env->createSchema(array(
	   "id"=>"Common#",
	   "type"=>"object",
	   "properties"=>array(
	       "!"=>array("type"=>"string","enum"=>array("Common"))
	   ),
	   "additionalProperties"=>false
	), null, "Common#");
$report = $env->validate(array(
		  "!" => "List",
		  "list" => array(
		    array(
		      "!" => "Text",
		      "common" => array("!"=>"NotCommon")
		    )
		  ),
		  "common" => array("!"=>"Common")
		),
		
		array(
		   "properties"=>array(
		       "!"=>array("type"=>"string","enum"=>array("List")),
		       "list"=>array(
		           "type"=>"array",
		           "items"=>array(
		               "type"=>array(
		                   array(
		                       "type"=>"object",
		                       "properties"=>array(
		                           "!"=>array("type"=>"string","enum"=>array("Music")),
		                           "common"=>array('$ref'=>"Common#")
		                       )
		                   ),
		                   array(
		                       "type"=>"object",
		                       "properties"=>array(
		                           "!"=>array("type"=>"string","enum"=>array("Text")),
		                           "common"=>array('$ref'=>"Common#")
		                       )
		                   )
		               )
		           )
		       ),
		
		       "common"=>array('$ref'=>"Common#")
		   )
		)
	);

$test->assertSchemaValidateFail(array('Instance is not a required type: [schema: {"type":"object","properties":{"!":{"type":"string","enum":["Music"]},"' .
                                      'common":{"$ref":"Common#"}}}], [schema: {"type":"object","properties":{"!":{"type":"string","enum":["Text"]}' .
                                      ',"common":{"$ref":"Common#"}}}] [schema path: #.list.0]'), $report, 'schema validate');

?>
===DONE===
--EXPECT--
===DONE===