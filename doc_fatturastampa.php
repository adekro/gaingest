<?php

/**
 * 
 * Gain Studios - fattura, azioni headless
 * Copyright (C) 2015 B2TEAM S.r.l. - All rights reserved
 *
 * @author Luigi Rosa <luigi.rosa@b2team.com>
 *
 * 20150521 file creato
 *
 */

define('SOUNDPARK', true);
require('global.php');
require('inc_tcpdf/tcpdf.php');

if (isset($_GET['idfattura']) and is_numeric($_GET['idfattura'])) {
	$qt = $db->query("SELECT * FROM fattura WHERE idfattura='" . $b2->normalizza($_GET['idfattura']) . "'");
	if ($qt->num_rows > 0) {
		$rf = $qt->fetch_array();
	
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
		// informazioni
		$pdf->SetCreator("Gestionale Gain Studios by B2TEAM S.r.l.");
		$pdf->SetAuthor("Gestionale Gain Studios by B2TEAM S.r.l.");
		$pdf->SetTitle("Fattura $rf[numero]/$rf[anno]");
		$pdf->SetSubject("Fattura $rf[numero]/$rf[anno]");
		// nessun header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
		$linea = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 0, 'color' => array(0, 0, 0));

		//               L   T   R
		$pdf->SetMargins(15, 15, 15);
		// set default font subsetting mode
		$pdf->setFontSubsetting(true);
		$pdf->AddPage();
		// logo
		$pdf->setXY(15, 15);
		$pdf->image("static/logofattura.png", 5, 15, 100);
		
		//$pdf->setXY(160, 15);
		$pdf->SetFont('helvetica', '', 10, '', true);
		$intestazione = "<b>Gain Studios di Arosio Elvio S.a.s</b><br>";
		$intestazione .= "Viale Montegrappa 28/G - 27100 Pavia (PV)<br>";
		$intestazione .= "P.IVA / C.F. 01941490185<br>";
		$intestazione .= "Tel/Fax +39 0382 46 74161  Mobile +39 366 314 2259<br>";
		$intestazione .= "info@gainstudios.com - PEC gainstudios@pec.it<br>";
		//                             X   Y               BOR
		$pdf->writeHTMLCell(0, '', 100, 15, $intestazione, 0, 0, FALSE, TRUE, 'R');

		$spettabile = "<b>Spett.le</b><br>";
		$spettabile .= "<b>$rf[intestazione]</b><br>";
		$spettabile .= "$rf[indirizzo1]<br>";
		$spettabile .= "$rf[indirizzo2]<br>";
		$spettabile .= "$rf[indirizzo3]<br>";
		if ('' != trim($rf['cf'])) $spettabile .= "C.F. $rf[cf]   ";
		if ('' != trim($rf['piva'])) $spettabile .= "P.IVA $rf[piva]";
		//                            X   Y               BOR
		$pdf->writeHTMLCell(130, '', 15, 45, $spettabile, 0, 0, FALSE, TRUE, 'L');
		
		$destra = "<b>FATTURA N. $rf[numero]/$rf[anno]</b><br>";
		$destra .= "del " . $b2->dt2ita($rf['data']);
		$pdf->writeHTMLCell(00, '', 120, 45, $destra, 0, 0, FALSE, TRUE, 'R');

		// dettaglio
		$det = "<table border=\"0\" cellpadding=\"2\">";
		$det .= "<tr>
		          <td align=\"center\" width=\"60\"><b>Codice</b></td>
		          <td align=\"center\" width=\"25\"><b>UM</b></td>
		          <td align=\"right\" width=\"40\"><b>Qta</b></td>
		          <td align=\"center\" width=\"200\"><b>Descrizione</b></td>
		          <td align=\"right\" width=\"80\"><b>Pr.Unitario</b></td>
		          <td align=\"right\" width=\"100\"><b>Importo</b></td>
		         </tr>";
		$pdf->SetFont('helvetica', '', 10, '', true);
		$qd = $db->query("SELECT fatturarighe.codice,fatturarighe.qta,fatturarighe.descrizione,fatturarighe.prezzo,
		                         articoli.um
		                  FROM fatturarighe 
		                  LEFT JOIN articoli ON fatturarighe.codice=articoli.codice
		                  WHERE fatturarighe.idfattura='$rf[idfattura]' 
		                  ORDER BY fatturarighe.riga");
		$imponibile = 0;
		while ($rd = $qd->fetch_array()) {
			if ('\\' == substr($rd['descrizione'], 0, 1)) {
				$det .= "<tr>";
				$det .= "<td align=\"left\" width=\"505\" colspan=\"3\">" . substr($rd['descrizione'], 1) . "</td>";
				$det .= "</tr>";
			} else {
				$importo = $rd['qta'] * $rd['prezzo'];
				$imponibile += $importo;
				$det .= "<tr>
				          <td align=\"left\" width=\"60\">$rd[codice]</td>
				          <td align=\"center\" width=\"25\">$rd[um]</td>
				          <td align=\"right\" width=\"40\">$rd[qta]</td>
				          <td align=\"left\" width=\"200\"> $rd[descrizione]</td>
				          <td align=\"right\" width=\"80\">" . number_format($rd['prezzo'] / 100, 2, ',', '.') . "</td>
				          <td align=\"right\" width=\"100\">" . number_format($importo / 100, 2, ',', '.') . "</td>
				         </tr>";
			}
		}
		$det .= "</table>";
		//$pdf->writeHTMLCell(180, '', 15, 81, $det, 0, 0, FALSE, TRUE, 'L');
		$pdf->setXY(15, 80);
		$pdf->writeHTML($det, true, false, false, false, '');
		
		// piede
		$amessaggio = array();
		$ypos = 230;
		
		// pagamento
		$pdf->SetFont('helvetica', '', 9, '', true);
		$qp = $db->query("SELECT pagamento,iban,banca1,banca2 FROM pagamenti WHERE idpagamento='$rf[idpagamento]'");
		if ($qp->num_rows > 0) {
			$rp = $qp->fetch_array();
			$pagamento = "<table border=\"0\" cellpadding=\"1\">";
			$pagamento .= "<tr>
		                   <td align=\"left\" width=\"55\"><b>Pagamento:</b></td>
		                   <td align=\"left\" width=\"300\">$rp[pagamento]</td>
		                </tr>";
		  if ('' != trim($rp['iban'])) {             
				$pagamento .= "<tr>
			                   <td align=\"left\" width=\"55\"> </td>
			                   <td align=\"left\" width=\"300\">IBAN: $rp[iban]</td>
			                </tr>";
			}
		  if ('' != trim($rp['banca1'])) {             
				$pagamento .= "<tr>
			                   <td align=\"left\" width=\"55\"> </td>
			                   <td align=\"left\" width=\"300\">$rp[banca1]</td>
			                </tr>";
			}
		  if ('' != trim($rp['banca2'])) {             
				$pagamento .= "<tr>
			                   <td align=\"left\" width=\"55\"> </td>
			                   <td align=\"left\" width=\"300\">$rp[banca2]</td>
			                </tr>";
			}
			$pagamento .= "</table>";
			$pdf->setXY(15, $ypos);
			$pdf->writeHTML($pagamento, true, false, false, false, '');
		}

		// totali
		$pdf->SetFont('helvetica', '', 10, '', true);
		$ri = $db->query("SELECT percento,testofattura FROM iva WHERE idiva='$rf[idiva]'")->fetch_array();
		if ('' != trim($ri['testofattura'])) $amessaggio[] = $ri['testofattura'];
		$pie = "<table border=\"0\" cellpadding=\"2\">";
		$pie .= "<tr>
		          <td align=\"right\" width=\"405\"><b>Imponibile</b></td>
		          <td align=\"right\" width=\"100\">" . number_format($imponibile / 100, 2, ',', '.') . "</td>
		         </tr>";
		if (0 == $ri['percento']) {
			$imposta = 0;
			$pie .= "<tr>
			          <td align=\"right\" width=\"405\"><b>Esente IVA</b></td>
			          <td align=\"right\" width=\"100\"> </td>
			         </tr>";
		} else {
			$imposta = ($imponibile / 100) * ($ri['percento'] / 100);
			$pie .= "<tr>
			          <td align=\"right\" width=\"405\"><b>IVA " . number_format($ri['percento'] / 100, 2, ',', '.') . "%</b></td>
			          <td align=\"right\" width=\"100\">" . number_format($imposta / 100, 2, ',', '.') . "</td>
			         </tr>";
		}
		$pie .= "<tr>
		          <td align=\"right\" width=\"405\"><b>TOTALE FATTURA</b></td>
		          <td align=\"right\" width=\"100\"><b>" . number_format(($imponibile+$imposta) / 100, 2, ',', '.') . "</b></td>
		         </tr>";
		$pie .= "</table>";
		$pdf->setXY(15, $ypos);
		$pdf->writeHTML($pie, true, false, false, false, '');

		$amessaggio[] = "Contributo ambientale CONAI assolto ove dovuto.";
		$amessaggio[] = " ";
		$amessaggio[] = "www.gainstudios.com                www.soundpark.it";
		$pdf->setXY(15, $ypos + 22);
		$pdf->writeHTML(implode('<br>', $amessaggio), true, false, false, false, 'C');

		$pdf->Output("fattura $rf[numero]-$rf[anno].pdf", 'I');
	}
}


### END OF FILE ###
