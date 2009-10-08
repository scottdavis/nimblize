<?php
/*
The MIT License

Copyright (c) 2007 Tiago Bastos
Copyright (c) 2009 John Bintz
Copyright (c) 2009 Scott Davis

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

require_once(dirname(__FILE__) . '/lib/base.php');
	/*
 	* Run application
 	*/
function Run($test_mode = false)
{
    try {
        Nimble::getInstance()->dispatch($test_mode);
    } catch (Exception $e) {
		if(NIMBLE_ENV == 'development' && !$test_mode) {
        ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>Error!</title>
    </head>
    <body>
        <h1>Caught exception: <?= $e->getMessage(); ?></h1>
        <h2>File: <?= $e->getFile()?></h2>
        <h2>Line: <?= $e->getLine()?></h2>
        <h3>Trace</h3>
        <pre>
        <?= $e->getTraceAsString() ?>
        </pre>
        <h3>Exception Object</h3>
        <pre>
        <?php var_dump($e); ?>
        </pre>
        <h3>Var Dump</h3>
        <pre>
        <?php debug_print_backtrace(); ?>
        </pre>
    </body>
</html>        
        <?php
		}
    }

}
?>
