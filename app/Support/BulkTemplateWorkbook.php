<?php

namespace App\Support;

use ZipArchive;

class BulkTemplateWorkbook
{
    public static function build(array $headers, array $sample, string $sheetName = 'Sheet1'): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'bulk-template-');
        if ($tmpFile === false) {
            throw new \RuntimeException('Gecici dosya olusturulamadi.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpFile);
            throw new \RuntimeException('XLSX arsivi olusturulamadi.');
        }

        $sheetRows = [
            self::buildRowXml(1, $headers),
            self::buildRowXml(2, $sample),
        ];

        $sheetName = self::escape($sheetName);

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addEmptyDir('_rels');
        $zip->addFromString('_rels/.rels', self::rootRelsXml());
        $zip->addEmptyDir('xl');
        $zip->addFromString('xl/workbook.xml', self::workbookXml($sheetName));
        $zip->addEmptyDir('xl/_rels');
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelsXml());
        $zip->addEmptyDir('xl/worksheets');
        $zip->addFromString('xl/worksheets/sheet1.xml', self::worksheetXml($sheetRows));
        $zip->addFromString('xl/styles.xml', self::stylesXml());
        $zip->close();

        $binary = file_get_contents($tmpFile);
        @unlink($tmpFile);

        if (! is_string($binary) || $binary === '') {
            throw new \RuntimeException('XLSX icerigi okunamadi.');
        }

        return $binary;
    }

    private static function buildRowXml(int $rowNumber, array $values): string
    {
        $cells = [];
        foreach (array_values($values) as $index => $value) {
            $ref = self::columnLetter($index) . $rowNumber;
            $text = self::escape((string) $value);
            $cells[] = '<c r="' . $ref . '" t="inlineStr"><is><t>' . $text . '</t></is></c>';
        }

        return '<row r="' . $rowNumber . '">' . implode('', $cells) . '</row>';
    }

    private static function columnLetter(int $index): string
    {
        $index += 1;
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = intdiv($index - 1, 26);
        }

        return $letters;
    }

    private static function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;
    }

    private static function rootRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private static function workbookXml(string $sheetName): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="{$sheetName}" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML;
    }

    private static function workbookRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    private static function worksheetXml(array $rows): string
    {
        $dimension = 'A1:' . self::columnLetter(5) . '2';
        $sheetData = implode('', $rows);

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <dimension ref="{$dimension}"/>
  <sheetViews>
    <sheetView workbookViewId="0"/>
  </sheetViews>
  <sheetFormatPr defaultRowHeight="15"/>
  <cols>
    <col min="1" max="6" width="18" customWidth="1"/>
  </cols>
  <sheetData>{$sheetData}</sheetData>
</worksheet>
XML;
    }

    private static function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="1">
    <font>
      <sz val="11"/>
      <name val="Calibri"/>
      <family val="2"/>
    </font>
  </fonts>
  <fills count="2">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
  </fills>
  <borders count="1">
    <border><left/><right/><top/><bottom/><diagonal/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
  </cellXfs>
  <cellStyles count="1">
    <cellStyle name="Normal" xfId="0" builtinId="0"/>
  </cellStyles>
</styleSheet>
XML;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
