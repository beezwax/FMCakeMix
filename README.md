FMCakeMix
=========

FMCakeMix is a FileMaker datasource driver for the CakePHP MVC framework.
FMCakeMix enables FileMaker databases to integrate into Cake as if they were
native SQL based sources, allowing for rapid development of FileMaker based web
solutions in a modern web application development framework.

To get more familiar with CakePHP visit: http://cakephp.org/

See the User Guide.pdf for usage information

Installation
------------

Download and follow the installation instructions from the cake website
http://cakephp.org/.

FX.php is PHP class created by Chris Hansen to speak with FileMaker via XML.
The FMCakeMix driver uses fx.php to send queries to FileMaker and is necessary
for the driver’s functionality. Install FX.php by downloading the files from
http://www.iviking.org/FX.php/ and placing the FX.php, FX_Error.php,
FX_Constants.php, and image_proxy.php files at the root of the
yourcakeinstall/vendors folder.

Because the driver uses XML to communicate with FileMaker, your FileMaker
solutions must be hosted on a version of FileMaker Server that supports web
publishing and xml access. See the FileMaker Server documentation for
instructions on enabling these features.

Install the dbo_fmcakemix.php file into
yourcakeinstall/app/models/datasources/dbo, you’ll likely have to create the
dbo directory in the datasources folder.

Credit
------

* Author: Alex Gibbons <alex_g@beezwax.net>

Thanks
------

Thanks to [Beezwax Datatools, Inc.](http://beezwax.net)

MIT License
-----------

Copyright (c) 2009 Beezwax Datatools, Inc., Alex Gibbons

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
