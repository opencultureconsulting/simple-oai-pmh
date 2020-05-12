# Simple OAI-PMH 2.0 Data Provider

This is a stand-alone and easy to install data provider implementing the [Open Archives Initiative's Protocol for Metadata Harvesting (OAI-PMH)](https://openarchives.org/pmh/). 
It serves records and sets in any metadata format from directories of XML files using the directory name as `metadataPrefix`, the filename as `identifier` and the filemtime as datestamp. 0-byte files are considered deleted records and handled accordingly. 
Resumption tokens are managed using files. 
Sets are now supported.

Just put the records as XML files in the data directory, create directory to store them by set, adjust a few configuration settings and you are ready to go!

A demo installation (original project) can be found [here](https://demo.opencultureconsulting.com/oai_pmh/?verb=Identify).

[![CodeFactor](https://www.codefactor.io/repository/github/beapi/oai_pmh/badge)](https://www.codefactor.io/repository/github/beapi/oai_pmh)

## Installation

1. Run `composer create-project beapi/oai_pmh <path>`.

2. Edit `Configuration/Main.php` and adjust the settings according to your preferences.

3. Create a subdirectory inside the specified data directory for every format (i. e. `metadataPrefix`) you want to provide.

4. Put the records into the respective directories according to their format. Optionnaly create directory to store them by set. Each record has to be a separate XML file with its `identifier` as filename (e. g. the file *12345678.xml* can be adressed using the `identifier` *12345678*). Optionally you can maintain deletions by keeping 0-byte files for deleted records.

5. Congratulations! Now you are running your own Simple OAI-PMH 2.0 Data Provider. You can access the entry point by calling `index.php?verb=Identify` in your browser.

## Upgrading

1. Backup `Configuration/Main.php` and your data directory!

2. Delete everything and re-install by running `composer create-project beapi/oai_pmh <path>`.

3. Move your configuration back into `Configuration/Main.php` and restore your data directory.

4. Congratulations! Now you are running the newest version of the Simple OAI-PMH 2.0 Data Provider. You can access the entry point by calling `index.php?verb=Identify` in your browser.

## Updating

Updating your records is just as easy with the `update.php` script! The script automatically handles deletions by maintaining 0-byte files for deleted records. Just call `php update.php` from the command line and follow the instructions. (Of course, you can simply replace the records manually as well.)

## History

This project was originally initiated in 2002 by [Heinrich Stamerjohanns](mailto:stamer@uni-oldenburg.de) at [University of Oldenburg](https://www.uni-oldenburg.de/en/). His latest implementation can still be found via the [Internet Archive's Wayback Machine](https://web.archive.org/web/*/http://physnet.uni-oldenburg.de/oai/).

It was then modified in 2011 by [Jianfeng Li](mailto:jianfeng.li@adelaide.edu.au) at [University of Adelaide](https://www.adelaide.edu.au/) for [The Plant Accelerator](https://www.plantphenomics.org.au/). The modified version can be found in the [Google Code Archive](https://code.google.com/archive/p/oai-pmh-2/).

In 2013 [Daniel Neis Araujo](mailto:danielneis@gmail.com) at [Federal University of Santa Catarina](https://en.ufsc.br/) modified the project again in order to integrate it with [Moodle](https://moodle.org/). His implementation can be found on [GitHub](https://github.com/danielneis/oai_pmh). In 2014 [Kazimierz Pietruszewski](mailto:antenna@antenna.io) provided some [further bugfixes](https://github.com/antennaio/oai_pmh).

In 2017 by [Sebastian Meyer](mailto:sebastian.meyer@opencultureconsutling.com) at [Open Culture Consulting](https://www.opencultureconsulting.com/) for the [German Literature Archive](https://www.dla-marbach.de/en/). It is a stand-alone version focused on easy deployment and file based record handling. His implementation can be found on [GitHub](https://github.com/opencultureconsulting/oai_pmh).

The current implementation was derived from the latter in 2020 by [Amaury BALMER](mailto:amaury@beapi.fr) at [Be API](https://beapi.fr/) for the [BPI](https://www.bpi.fr/). It retains the philosophy brought by Sebastian in 2019 but it provides support for Sets.