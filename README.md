Simple OAI-PMH 2.0 Data Provider
================================

This is a stand-alone and easy to install data provider for the [Open Archives Initiative's Protocol for Metadata Harvesting (OAI-PMH)](http://openarchives.org/pmh/) written in [PHP](http://php.net/). It serves records in any metadata format from directories of XML files using the directory name as metadata prefix, the filename as identifier and the filemtime as datestamp. 0-byte files are considered deleted records and handled accordingly. Resumption tokens are managed using files. Sets are currently not supported.

Just put the records as XML files in the data directory, adjust a few configuration settings and you are ready to go!

A demo installation can be found [here](http://demo.opencultureconsulting.com/oai_pmh/?verb=Identify).

Installation
------------

1. Deploy all the files to a webserver.

2. Edit oai2config.php and adjust the settings according to your preferences.

3. Put the records into the specified data directory. Each record has to be a separate XML file with its identifier as filename (i.e. 12345678.xml).

3a. Optionally you can maintain deletions by keeping 0-byte files in the data directory for deleted records.

4. Congratulations! Now you are running an OAI-PMH 2.0 compatible data provider.

History
-------

This project was originally initiated in 2002 by [Heinrich Stamerjohanns](mailto:stamer@uni-oldenburg.de) at [University of Oldenburg](https://www.uni-oldenburg.de/en/). His latest implementation can be still found via the [Internet Archive's Wayback Machine](https://web.archive.org/web/*/http://physnet.uni-oldenburg.de/oai/).

It was then modified in 2011 by [Jianfeng Li](mailto:jianfeng.li@adelaide.edu.au) at [University of Adelaide](http://www.adelaide.edu.au/) for [The Plant Accelerator](http://www.plantaccelerator.org.au/). The modified version can be found in the [Google Code Archive](https://code.google.com/archive/p/oai-pmh-2/).

In 2013 [Daniel Neis Araujo](mailto:danielneis@gmail.com) at [Federal University of Santa Catarina](http://en.ufsc.br/) modified the project again in order to integrate it with [Moodle](https://moodle.org/). His implementation can be found on [GitHub](https://github.com/danielneis/oai_pmh). In 2014 [Kazimierz Pietruszewski](mailto:antenna@antenna.io) provided some [further bugfixes](https://github.com/antennaio/oai_pmh).

The current implementation was derived from the latter in 2017 by [Sebastian Meyer](mailto:sebastian.meyer@opencultureconsutling.com) at [Open Culture Consulting](https://www.opencultureconsulting.com/) for the [German Literature Archive](http://www.dla-marbach.de/en/). It is a simplified stand-alone version focused on easy deployment and file based record handling.
