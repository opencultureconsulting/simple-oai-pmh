Simple OAI-PMH 2.0 Data Provider

Overview

This project is derived from the original work of Jianfeng Li
from "The Plant Accelerator" for the "University of Adelaide".

In the original docs (you can see them on git log),
Jianfeng Li he was inspired by PHP OAI Data Provider developed
by Heinrich Stamerjohanns at University of Oldenburg.
His implementation is at http://physnet.uni-oldenburg.de/oai/.

Almost all the code was rewritten in my attempt to understand
both the OAI-PMH protocol and the original code itself.

The code changed so much that i removed the original documentation.

There is some unit tests that were written based on the official
protocol documentation at http://www.openarchives.org/OAI/openarchivesprotocol.html#ErrorConditions
and also on the Open Archives Initiative - Repository Explorer at http://re.cs.uct.ac.za/

This version of the server relies on callbacks to get the needed data
to generate the XML responses. The file oai2.php is a sample server
instantiation to allow unit tests (OAI2ServerTest.php) to be run by
PHP Unit. It has "hard-coded" data to pass the tests and validates
that the server correctly reads correctly-formatted data.
It is your responsibility to provide callbacks that provides
correctly formatted data in all cases.

Tokens are managed using files.

XML Responses are created using the DOMDocument PHP interfaces.
