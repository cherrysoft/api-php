<?php
$addressbook = $env->createSchema(array(
                     "type"=> "object",
                     "id"=> "http://example.com/addressbook.json",
                     "description"=> "AddressBook example from http://relaxng.org/compact-tutorial-20030326.html",
                     "properties"=> array(
                         "cards"=> array(
                             "type"=> "array",
                             "items"=> array(
                                 "type"=> "array",
                                 "items"=> array(
                                     "type"=> "string"
                                 ),
                                 "minItems"=> 2,
                                 "maxItems"=> 2,
                                 '$schema'=>"http://json-schema.org/draft-03/hyper-schema#"
                             ),
                             "required"=> true
                         )
                     ),
                     '$schema'=>"http://json-schema.org/draft-03/hyper-schema#"
                 ),
                 null,
                 "http://example.com/addressbook.json");


// The referral target schema, with a canonical id.
$card = $env->createSchema(array(
                     "type"=> "array",
                     "id"=> "http://example.com/subdir/card.json",
                     "description"=> "Referral target",
                     "items"=> array(
                         "type"=> "string"
                     ),
                     "minItems"=> 2,
                     "maxItems"=> 2,
                     '$schema'=>"http://json-schema.org/draft-03/hyper-schema#"
                 ),
                 null,
                 "http://example.com/subdir/card.json");


// Similar example, using $ref to factor part of the schema.
$addressbookref = $env->createSchema(array(
                     "type"=> "object",
                     "id"=> "http://example.com/addressbook_ref.json",
                     "description"=> 'Similar example, using $ref to factor part of the schema.',
                     "properties"=> array(
                         "cards"=> array(
                             "type"=> "array",
                             "items"=> array(
                                 '$ref'=> "./subdir/card.json"
                             ),
                             "required"=> true
                         )
                     ),
                     '$schema'=>"http://json-schema.org/draft-03/hyper-schema#"
                 ),
                 null,
                 "http://example.com/addressbook_ref.json");


// Similar example, using extends to factor part of the schema.
$addressbookextends = $env->createSchema(array(
                     "type"=> "object",
                     "id"=> "http://example.com/addressbook_extends.json",
                     "description"=> "Similar example, using extends to factor part of the schema.",
                     "properties"=> array(
                         "cards"=> array(
                             "type"=> "array",
                             "items"=> array(
                                 "extends"=> array(
                                     '$ref'=> "./subdir/card.json"
                                 )
                             ),
                             "required"=> true
                         )
                     ),
                     '$schema'=>"http://json-schema.org/draft-03/hyper-schema#"
                 ),
                 null,
                 "http://example.com/addressbook_extends.json");