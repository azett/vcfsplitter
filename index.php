<?php

/**
 * The vcfsplitter splits a bulk vCard file into single vCard files.
 *
 * @author Arvid Zimmermann
 * @see https://github.com/azett/vcfsplitter
 */
$html = '<b>Im Thunderbird-Adressbuch:</b><br>
Extras -> Exportieren -> als input.vcf speichern<br>
<br>
<b>Im Browser</b><br>
input.vcf ins Verzeichnis "input" legen, vcf-Splitter im Browser aufrufen<br>
<br>
<b>Im Samsung-Handy:</b><br>
- Einstellungen -> Speicher -> Telefonspeicher zurücksetzen -> "Kontakte" anchecken -> Telefonkennwort eingeben<br>
- vom vcf-Splitter erstellten Ordner "output" via USB aufs Handy kopieren<br>
- "Eigene Dateien" -> in Ordner "output" wechseln<br>
- "Optionen" -> "In Kontakte wiederherstellen" -> "Mehrere"<br>
- "Alle auswählen" -> GO :)<br>
<hr>
';

if (!isset($_GET ['doit'])) {
	$html .= '<a href=".?doit=true">GO</a><br>or<br><a href=".?doit=true&removephotos=true">GO (remove contact photos)</a>';
	echo $html;
	die();
}

$inputdir = 'input';
$outputdir = 'output';
$inputfile = $inputdir . DIRECTORY_SEPARATOR . 'input.vcf';
$contactEndDelimiter = 'END:VCARD';

// input file exists and is readable?
if (!file_exists($inputfile)) {
	die('Input file ' . $inputfile . ' does not exist');
}
$handle = fopen($inputfile, "r");
if (!$handle) {
	die('Input file ' . $inputfile . ' is not readable');
}
// create output dir if not existant
if (!file_exists($outputdir)) {
	mkdir($outputdir, 0700, true);
	if (!file_exists($outputdir)) {
		die('Output directory ' . $outputdir . ' could not be created');
	}
}

// output dir is a directory?
if (!is_dir($outputdir)) {
	die('Output directory ' . $outputdir . ' is not a directory');
}
// output dir is empty?
if (count(glob($outputdir . DIRECTORY_SEPARATOR . '*')) !== 0) {
	die('Output directory ' . $outputdir . ' is not empty');
}

$contactsCount = 0;
$currentFileContent = '';
$removePhotos = isset($_GET ['removephotos']) && $_GET ['removephotos'] === 'true';
// read each line of the input file
while (($line = fgets($handle)) !== false) {
	// remove contact photo if desired
	if ($removePhotos && (startsWith($line, 'PHOTO;') || startsWith($line, ' '))) {
		continue;
	}

	// collect line
	$currentFileContent .= $line;
	// end of current contact reached
	if (startsWith($line, $contactEndDelimiter)) {
		// write vcf file for contact
		file_put_contents($outputdir . DIRECTORY_SEPARATOR . $contactsCount . '.vcf', $currentFileContent);
		// jump to next contact
		$contactsCount++;
		$currentFileContent = '';
	}
}

$html .= $contactsCount . ' vcf files created';
echo $html;

fclose($handle);

function startsWith($haystack, $needle, $ignorecase = true) {
	if ($ignorecase) {
		$haystack = strtolower($haystack);
		$needle = strtolower($needle);
	}
	if ($haystack == 'end:vcard')
		echo ($haystack . ' -- ' . $needle . '<br>');
	return (substr($haystack, 0, strlen($needle)) === $needle);
}
