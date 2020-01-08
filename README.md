# Simple OAI-PMH 2.0 Data Provider

This is a stand-alone and easy to install data provider for the [Open Archives Initiative's Protocol for Metadata Harvesting (OAI-PMH)](https://openarchives.org/pmh/) written in [PHP](https://php.net/). It serves records in any metadata format from directories of XML files using the directory name as metadata prefix, the filename as identifier and the filemtime as datestamp. 0-byte files are considered deleted records and handled accordingly. Resumption tokens are managed using files. Sets are currently not supported.

Just put the records as XML files in the data directory, adjust a few configuration settings and you are ready to go!

A demo installation can be found [here](https://demo.opencultureconsulting.com/oai_pmh/?verb=Identify).

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/cd72907ff98f4e01beb32576319b83a2)](https://www.codacy.com/app/OCC/OAI-PMH.DataProvider)

## Installation

1. Deploy all the files to a webserver.

2. Edit `Configuration/Main.php` and adjust the settings according to your preferences.

3. Create a subdirectory inside the specified data directory for every format (i. e. `metadataPrefix`) you want to provide.

4. Put the records into the respective directories according to their format. Each record has to be a separate XML file with its `identifier` as filename (e. g. 12345678.xml). Optionally you can maintain deletions by keeping 0-byte files for deleted records.

5. Congratulations! Now you are running an OAI-PMH 2.0 compatible data provider. You can access the entry point by calling `index.php?verb=Identify` in your browser.

## Updating

Updating your records from the command line is just as easy with the `update.php` script! The script automatically handles deletions by maintaining 0-byte files for deleted records. Just call `php update.php` and follow the instructions.

## History

This project was originally initiated in 2002 by [Heinrich Stamerjohanns](mailto:stamer@uni-oldenburg.de) at [University of Oldenburg](https://www.uni-oldenburg.de/en/). His latest implementation can be still found via the [Internet Archive's Wayback Machine](https://web.archive.org/web/*/http://physnet.uni-oldenburg.de/oai/).

It was then modified in 2011 by [Jianfeng Li](mailto:jianfeng.li@adelaide.edu.au) at [University of Adelaide](https://www.adelaide.edu.au/) for [The Plant Accelerator](https://www.plantphenomics.org.au/). The modified version can be found in the [Google Code Archive](https://code.google.com/archive/p/oai-pmh-2/).

In 2013 [Daniel Neis Araujo](mailto:danielneis@gmail.com) at [Federal University of Santa Catarina](https://en.ufsc.br/) modified the project again in order to integrate it with [Moodle](https://moodle.org/). His implementation can be found on [GitHub](https://github.com/danielneis/oai_pmh). In 2014 [Kazimierz Pietruszewski](mailto:antenna@antenna.io) provided some [further bugfixes](https://github.com/antennaio/oai_pmh).

The current implementation was derived from the latter in 2017 by [Sebastian Meyer](mailto:sebastian.meyer@opencultureconsutling.com) at [Open Culture Consulting](https://www.opencultureconsulting.com/) for the [German Literature Archive](https://www.dla-marbach.de/en/). It is a stand-alone version focused on easy deployment and file based record handling.
