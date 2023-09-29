# Simple OAI-PMH 2.0 Data Provider

This is a stand-alone and easy to install data provider implementing the [Open Archives Initiative's Protocol for Metadata Harvesting (OAI-PMH)](https://openarchives.org/pmh/). It serves records in any metadata format from directories of XML files using the directory name as `metadataPrefix`, the filename as `identifier` and the filemtime as timestamp. 0-byte files are considered deleted records and handled accordingly. Resumption tokens are managed using files. Sets are currently not supported.

Just put the records as XML files in the data directory, adjust a few configuration settings and you are ready to go!

A demo installation can be found [here](https://demo.opencultureconsulting.com/oai_pmh/?verb=Identify).

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/7a12022611d047ad9ef9a0c3aadb986a)](https://www.codacy.com/gh/opencultureconsulting/simple-oai-pmh)

## Installation

1. Run `composer create-project opencultureconsulting/simple-oai-pmh <path>`.

2. Create a data directory in a location not publicly accessible (i. e. outside of `<path>`). Create a subdirectory inside the specified data directory for every format (i. e. `metadataPrefix`) you want to provide.

3. Copy `Configuration/Main.template.php` to `Configuration/Main.php` and edit the settings according to your preferences. Don't forget pointing `$config['dataDirectory']` to your newly created data directory.

4. Put the records into the respective directories according to their format. Each record has to be a separate XML file with its `identifier` as filename (e. g. the file *12345678.xml* can be adressed using the `identifier` *12345678*). Optionally you can maintain deletions by keeping 0-byte files for deleted records.

5. Congratulations! Now you are running your own Simple OAI-PMH 2.0 Data Provider. You can access the entry point by calling `index.php?verb=Identify` in your browser.

## Upgrading

1. Backup `Configuration/Main.php`!

2. Delete `<path>` and re-install by running `composer create-project opencultureconsulting/simple-oai-pmh <path>`.

3. Move your configuration back into `Configuration/Main.php`.

4. Congratulations! Now you are running the newest version of the Simple OAI-PMH 2.0 Data Provider. You can access the entry point by calling `index.php?verb=Identify` in your browser.

## Updating

Updating your records is just as easy with the `update.php` script! The script automatically handles deletions by maintaining 0-byte files for deleted records. Just call `php update.php` from the command line and follow the instructions. (Of course, you can simply replace the records manually as well.)

## History

This project was originally initiated in 2002 by [Heinrich Stamerjohanns](mailto:stamer@uni-oldenburg.de) at [University of Oldenburg](https://www.uni-oldenburg.de/en/). His latest implementation can still be found via the [Internet Archive's Wayback Machine](https://web.archive.org/web/*/http://physnet.uni-oldenburg.de/oai/).

It was then modified in 2011 by [Jianfeng Li](mailto:jianfeng.li@adelaide.edu.au) at [University of Adelaide](https://www.adelaide.edu.au/) for [The Plant Accelerator](https://www.plantphenomics.org.au/). The modified version can be found in the [Google Code Archive](https://code.google.com/archive/p/oai-pmh-2/).

In 2013 [Daniel Neis Araujo](mailto:danielneis@gmail.com) at [Federal University of Santa Catarina](https://en.ufsc.br/) modified the project again in order to integrate it with [Moodle](https://moodle.org/). His implementation can be found on [GitHub](https://github.com/danielneis/oai_pmh). In 2014 [Kazimierz Pietruszewski](mailto:antenna@antenna.io) provided some [further bugfixes](https://github.com/antennaio/oai_pmh).

The current implementation was derived from the latter in 2017 by [Sebastian Meyer](mailto:sebastian.meyer@opencultureconsutling.com) at [Open Culture Consulting](https://www.opencultureconsulting.com/) for the [German Literature Archive](https://www.dla-marbach.de/en/). It is a stand-alone version focused on easy deployment and file based record handling.
