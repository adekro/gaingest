<?php

/**
 * 
 * Gain Studios - DDT, azioni headless
 * Copyright (C) 2014 Luigi Rosa <lrosa@venus.it> - All rights reserved
 * Copyright (C) 2015-2019 B2TEAM S.r.l. <luigi.rosa@b2team.com> - All rights reserved
 *
 * 20141207 file creato
 * 20150310 causale tabellata
 * 20190202 conversione datainitaliano(), normalizza() in B2TOOLS
 *
 */

define('SOUNDPARK', true);
require('global.php');
require('inc_tcpdf/tcpdf.php');

$amezzo = array('E'=>'Cedente','S'=>'Cessionario','V'=>'Vettore');
$acontosaldo = array('C'=>'in conto','S'=>'a saldo','X'=>'Non specificato');

if (isset($_GET['idddt']) and is_numeric($_GET['idddt'])) {
	$qt = $db->query("SELECT * FROM ddt WHERE idddt='" . $b2->normalizza($_GET['idddt']) . "'");
	if ($qt->num_rows > 0) {
		$rt = $qt->fetch_array();
	
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
		// informazioni
		$pdf->SetCreator("Gestionale Gain Studios by B2TEAM S.r.l.");
		$pdf->SetAuthor("Gestionale Gain Studios by B2TEAM S.r.l.");
		$pdf->SetTitle("Documento di trasporto $rt[numero]/$rt[anno]");
		$pdf->SetSubject("Documento di trasporto $rt[numero]/$rt[anno]");
		// nessun header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
		$linea = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 0, 'color' => array(0, 0, 0));

		//               L   T   R
		$pdf->SetMargins(15, 15, 15);
		// set default font subsetting mode
		$pdf->setFontSubsetting(true);
		$pdf->AddPage();
		// intestazione societa'
		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->setXY(15, 12);
		$pdf->writeHTML("Cedente:");
		$pdf->setXY(15, 15);
		$pdf->SetFont('helvetica', '', 10, '', true);
		$intestazione = "<b>Gain Studios S.a.s</b> di Arosio Elvio<br>";
		$intestazione .= "Viale Montegrappa 28/G<br>";
		$intestazione .= "27100 - PAVIA<br>";
		$intestazione .= "P.I e C.F. 01941490185<br>";
		$intestazione .= "info@gainstudios.com<br>";
		$intestazione .= "www.gainstudios.com";
		//                           X   Y               BOR
		$pdf->writeHTMLCell(75, '', 15, 15, $intestazione, 0, 0, FALSE, TRUE, 'C');

		// intestazione ddt
		$ypos = 15;
		$pdf->SetFont('helvetica', '', 16, '', true);
		$pdf->setXY(90, $ypos);
		$pdf->writeHTML("<b>DOCUMENTO DI TRASPORTO (D.d.t.)</b>");
		$pdf->SetFont('helvetica', '', 10, '', true);
		$ypos += 5;
		$pdf->setXY(90, $ypos);
		$pdf->writeHTML("D.P.R. 472 del 14.08.1996 - D.P.R. 696 del 21.12.1996");
		$pdf->SetFont('helvetica', '', 16, '', true);
		$ypos += 7;
		$pdf->setXY(90, $ypos);
		$pdf->writeHTML("<b>N. $rt[numero]/$rt[anno]  Data " . $b2->dt2ita($rt['data']) . "</b>");
		$pdf->SetFont('helvetica', '', 14, '', true);
		$ypos += 7;
		$pdf->setXY(90, $ypos);
		$pdf->writeHTML("a mezzo " . strtolower($amezzo[$rt['mezzo']]));

		$pdf->line(15,43,195,43,$linea);

		// destinatari
		$cessionario = "$rt[cessionario1]<br>$rt[cessionario2]<br>$rt[cessionario3]<br>$rt[cessionario4]";
		$destinazione = "$rt[destinazione1]<br>$rt[destinazione2]<br>$rt[destinazione3]<br>$rt[destinazione4]";

		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->setXY(15, 43);
		$pdf->writeHTML("Cessionario:");
		$pdf->setXY(90, 43);
		$pdf->writeHTML("Luogo di destinatone (se diversio dal cessionario) e variazioni:");

		$pdf->SetFont('helvetica', '', 12, '', true);
		//                           X   Y               BOR
		$pdf->writeHTMLCell(75, '', 15, 49, $cessionario, 0, 0, FALSE, TRUE, 'L');
		$pdf->writeHTMLCell(75, '', 90, 49, $destinazione, 0, 0, FALSE, TRUE, 'L');

		$pdf->SetFont('helvetica', '', 7, '', true);
		$pdf->setXY(15, 62);
		$pdf->writeHTML("Causale del trasporto:");
		$pdf->setXY(90, 62);
		$pdf->writeHTML("Riferimenti:");
		$contosaldo = $rt['contosaldo'] == 'X' ? '' : $acontosaldo[$rt['contosaldo']];
		$pdf->SetFont('helvetica', '', 12, '', true);
		//                           X   Y               BOR
		$causale = '';
		if ($rt['idcausaleddt'] > 0) {
			$rr = $db->query("SELECT causaleddt FROM causaleddt WHERE idcausaleddt='$rt[idcausaleddt]'")->fetch_array();
			$causale = $rr['causaleddt'];
		}
		$pdf->writeHTMLCell(75, '', 15, 74, $causale, 0, 0, FALSE, TRUE, 'L');
		$pdf->writeHTMLCell(75, '', 90, 74, $rt['rifordine'] . ' ' . $contosaldo, 0, 0, FALSE, TRUE, 'L');

		$pdf->line(15,80,195,80,$linea);
		
		// dettaglio
		$det = "<table border=\"0\">";
		$det .= "<tr>
		          <td align=\"center\" width=\"50\"><b>Quantita</b></td>
		          <td align=\"center\" width=\"80\"><b>Codice</b></td>
		          <td align=\"center\" width=\"380\"><b>Descrizione dei beni</b></td>
		         </tr>";
		$pdf->SetFont('helvetica', '', 12, '', true);
		$qd = $db->query("SELECT * FROM ddtrighe WHERE idddt='$rt[idddt]' ORDER BY riga");
		while ($rd = $qd->fetch_array()) {
			if ('\\' == substr($rd['descrizione'], 0, 1)) {
				$det .= "<tr>";
				$det .= "<td align=\"left\" width=\"510\" colspan=\"3\">" . substr($rd['descrizione'], 1) . "</td>";
				$det .= "</tr>";
			} else {
				$det .= "<tr>";
				$det .= "<td align=\"right\" width=\"50\">$rd[qta]</td>";
				$det .= "<td align=\"right\" width=\"80\">$rd[codice]</td>";
				$det .= "<td align=\"left\" width=\"380\"> $rd[descrizione]</td>";
				$det .= "</tr>";
			}
		}
		$det .= "</table>";
		//$pdf->writeHTMLCell(180, '', 15, 81, $det, 0, 0, FALSE, TRUE, 'L');
		$pdf->setXY(15, 81);
		$pdf->writeHTML($det, true, false, false, false, '');

		// piede
		$ypos = 225;
		$ariga = array();
		if ('' != trim($rt['aspetto'])) $ariga[] = "Aspetto esteriore dei beni: $rt[aspetto]";
		if ('' != trim($rt['colli'])) $ariga[] = "Colli: $rt[colli]";
		if ('' != trim($rt['peso'])) $ariga[] = "Peso: $rt[peso]";
		if ('' != trim($rt['porto'])) $ariga[] = "Porto: $rt[porto]";
		if (count($ariga) > 0) {
			$pdf->setXY(15, $ypos);
			$pdf->SetFont('helvetica', '', 10, '', true);
			$pdf->writeHTML(implode(' - ', $ariga));
			$ypos += 7;
		}

		$pdf->SetFont('helvetica', '', 12, '', true);
		$pdf->setXY(15, $ypos);
		$vettori = '<table cellspacing="0" cellpadding="1" border="1">
                <tr>
                 <td align="center" width="200"><b>VETTORE</b></td>
                 <td align="center" width="150"><b>DATA E ORA DEL RITIRO</b></td>
                 <td align="center" width="160"><b>FIRME</b></td>
                </tr>
                <tr>
                 <td align="center" width="200"> </td>
                 <td align="center" width="150"> </td>
                 <td align="center" width="160"> </td>
                </tr>
                <tr>
                 <td align="center" width="200"> </td>
                 <td align="center" width="150"> </td>
                 <td align="center" width="160"> </td>
                </tr>
                </table>'; 
		$pdf->writeHTML($vettori);
		$ypos += 20;

		$ypos += 5;
		$pdf->SetFont('helvetica', '', 10, '', true);
		$pdf->setXY(15, $ypos);
		$pdf->writeHTML("Consegna/inizio trasporto a mezzo:  O cedente  O cessionario &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Data/ora: _____________________________");
		
		$ypos += 14;
		$pdf->SetFont('helvetica', '', 10, '', true);
		$pdf->setXY(15, $ypos);
		$pdf->writeHTML("Firma del conducente: __________________________         Firma del cessionario: __________________________");

		

		$pdf->Output("ddt $rt[numero]-$rt[anno].pdf", 'I');
	}
}



### END OF FILE ###
