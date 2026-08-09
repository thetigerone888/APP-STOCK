<?php
// ========================================================
// MiniXLSXReader — อ่านไฟล์ .xlsx โดยไม่ต้องพึ่ง library ภายนอก
// ใช้ ZipArchive + SimpleXML ซึ่งมีอยู่แล้วใน PHP มาตรฐานของ shared hosting ส่วนใหญ่
// ========================================================
class MiniXLSXReader {
    private $zip;
    private $sharedStrings = [];
    private $dateStyleIndexes = [];

    public function __construct($filepath) {
        $this->zip = new ZipArchive();
        if ($this->zip->open($filepath) !== true) {
            throw new Exception("ไม่สามารถเปิดไฟล์ XLSX ได้");
        }
        $this->loadSharedStrings();
        $this->loadStyles();
    }

    private function loadSharedStrings() {
        $xml = $this->zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) return;
        $sx = @simplexml_load_string($xml);
        if (!$sx) return;
        foreach ($sx->si as $si) {
            if (isset($si->t)) {
                $this->sharedStrings[] = (string)$si->t;
            } else {
                $text = '';
                if (isset($si->r)) {
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                }
                $this->sharedStrings[] = $text;
            }
        }
    }

    private function loadStyles() {
        $xml = $this->zip->getFromName('xl/styles.xml');
        if ($xml === false) return;
        $sx = @simplexml_load_string($xml);
        if (!$sx) return;

        $dateNumFmtIds = [];
        if (isset($sx->numFmts)) {
            foreach ($sx->numFmts->numFmt as $nf) {
                $id = (int)$nf['numFmtId'];
                $code = (string)$nf['formatCode'];
                if (preg_match('/[dmy]{1,4}[\/\-.][dmy]{1,4}/i', $code) || stripos($code, 'yyyy') !== false) {
                    $dateNumFmtIds[$id] = true;
                }
            }
        }
        foreach (range(14, 22) as $id) $dateNumFmtIds[$id] = true; // built-in date formats
        foreach ([45, 46, 47] as $id) $dateNumFmtIds[$id] = true;

        if (isset($sx->cellXfs)) {
            $idx = 0;
            foreach ($sx->cellXfs->xf as $xf) {
                $numFmtId = (int)$xf['numFmtId'];
                $this->dateStyleIndexes[$idx] = isset($dateNumFmtIds[$numFmtId]);
                $idx++;
            }
        }
    }

    public function readSheet($sheetName) {
        $workbookXml = @simplexml_load_string($this->zip->getFromName('xl/workbook.xml'));
        if (!$workbookXml) throw new Exception("อ่าน workbook.xml ไม่ได้");

        $rId = null;
        $namespaces = $workbookXml->getNamespaces(true);
        foreach ($workbookXml->sheets->sheet as $sheet) {
            if ((string)$sheet['name'] === $sheetName) {
                $attrs = $sheet->attributes($namespaces['r']);
                $rId = (string)$attrs['id'];
                break;
            }
        }
        if (!$rId) throw new Exception("ไม่พบชีตชื่อ: $sheetName");

        $relsXml = @simplexml_load_string($this->zip->getFromName('xl/_rels/workbook.xml.rels'));
        $target = null;
        foreach ($relsXml->Relationship as $rel) {
            if ((string)$rel['Id'] === $rId) {
                $target = (string)$rel['Target'];
                break;
            }
        }
        if (!$target) throw new Exception("หา sheet target ไม่เจอ");
        $sheetPath = 'xl/' . ltrim($target, '/');

        $sheetXml = @simplexml_load_string($this->zip->getFromName($sheetPath));
        if (!$sheetXml) throw new Exception("อ่านไฟล์ชีตไม่ได้: $sheetPath");

        $rows = [];
        foreach ($sheetXml->sheetData->row as $row) {
            $rowNum = (int)$row['r'];
            $rowData = [];
            foreach ($row->c as $c) {
                $ref = (string)$c['r'];
                if (!preg_match('/([A-Z]+)(\d+)/', $ref, $m)) continue;
                $colIdx = $this->colLettersToIndex($m[1]);

                $cellType = isset($c['t']) ? (string)$c['t'] : null;
                $styleIdx = isset($c['s']) ? (int)$c['s'] : 0;

                $value = null;
                if ($cellType === 'inlineStr' && isset($c->is)) {
                    $value = (string)$c->is->t;
                } elseif (isset($c->v)) {
                    $rawVal = (string)$c->v;
                    if ($cellType === 's') {
                        $value = $this->sharedStrings[(int)$rawVal] ?? '';
                    } elseif ($cellType === 'str') {
                        $value = $rawVal;
                    } else {
                        if (!empty($this->dateStyleIndexes[$styleIdx]) && is_numeric($rawVal)) {
                            $value = $this->excelDateToDMY((float)$rawVal);
                        } else {
                            $value = is_numeric($rawVal) ? (float)$rawVal : $rawVal;
                        }
                    }
                }
                $rowData[$colIdx] = $value;
            }
            $rows[$rowNum] = $rowData;
        }
        return $rows;
    }

    private function colLettersToIndex($letters) {
        $letters = strtoupper($letters);
        $result = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $result = $result * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $result - 1;
    }

    private function excelDateToDMY($serial) {
        $unixTimestamp = ($serial - 25569) * 86400;
        return gmdate('d/m/Y', (int)$unixTimestamp);
    }

    public function close() {
        $this->zip->close();
    }
}
