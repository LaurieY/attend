<?php
$f3=require('lib/base.php');
require('lib/bcrypt.php');

//$cry=Bcrypt::instance();
$cry=new Bcrypt;
echo( 'clas of $cry = '.get_class($cry)."\n");

$salt = 'WzAaKjMNixk4J2Nw3qeUei';
//$salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)), '+', '.'), 0, 22);
//echo('Salt is '.$salt."\n");
//echo( 'clas of $salt = '.get_class($salt)."\n");
// 2y is the bcrypt algorithm selector, see http://php.net/crypt
// 12 is the workload factor (around 300ms on my Core i7 machine), see http://php.net/crypt
$hash = crypt('laurie12', '$2y$12$' . $salt);
echo("laurie12 hash ".$hash."\n");
$hashUpper = crypt('Laurie12', '$2y$12$' . $salt);
echo("hashUpper ".$hashUpper."\n");
// we can now use the generated hash as the argument to crypt(), since it too will contain $2y$12$... with a variation of the hash. No need to store the salt anymore, just the hash is enough!
echo("\nnow should be true\n");
var_dump($hash == crypt('laurie12', $hash)); // true
echo("now should be false\n");
var_dump($hash == crypt('Laurie12', $hash)); // false
echo("secret now should be true\n");
var_dump($hash == crypt('secret', '$1$o94.Rc0.$2GSt4JJsPx63u9UVpl0r//'));
echo("now should be false\n");
var_dump($hash == crypt('secret',$hash)); // true

echo("\n");

//$hash = crypt('laurie12', '33a2c0d92da52d2e8f11c3237ae34e53d5195ab2');
echo("now should be true\n");
var_dump($hash == crypt('laurie12',$hash)); // true
echo("now should be true\n");
var_dump($hashUpper == crypt('Laurie12',$hashUpper)); // true
?>