<?php

echo "Indexer type test<br>Available indexer types:<br>";
echo "mage<br>";
if(exec("whoami") == "apache")
    echo "shell<br>";
//else
//    phpinfo();


if(file_put_contents('test.txt', 'TEST') !== false)
{
    echo "created test file";
    
}
else
{
    echo "error writing file";
}

if(!copy('test.txt', 'in/archive/images/test.txt'))
{
    echo ("error copying file");
}
else
{
    echo "permissions are good for file copy";
}

?>
