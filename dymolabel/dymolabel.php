<?php
/* Copyright (C) 2015-2017  Frederic France     <frederic.france@free.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       htdocs/dymolabel/dymolabel.php
 *  \ingroup    label
 *  \brief      DymoLabel
 */

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res && file_exists("../../../../main.inc.php")) $res=@include("../../../../main.inc.php");
if (! $res && file_exists("../../../../../main.inc.php")) $res=@include("../../../../../main.inc.php");
if (! $res && preg_match('/\/nltechno([^\/]*)\//',$_SERVER["PHP_SELF"],$reg)) $res=@include("../../../../dolibarr".$reg[1]."/htdocs/main.inc.php"); // Used on dev env only
if (! $res) die("Include of main fails");
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/sendings.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/expedition/modules_expedition.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

if (! empty($conf->product->enabled) || ! empty($conf->service->enabled))  require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
if (! empty($conf->propal->enabled))   require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
if (! empty($conf->commande->enabled)) require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
if (! empty($conf->stock->enabled))    require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';

$langs->load("sendings");
$langs->load("companies");
$langs->load("bills");
$langs->load('deliveries');
$langs->load('orders');
$langs->load('stocks');
$langs->load('other');
$langs->load('propal');

$origin = GETPOST('origin','alpha')?GETPOST('origin','alpha'):'expedition';
$id = GETPOST('id','int')?GETPOST('id','int'):'';

if (empty($id)) $id  = GETPOST('origin_id','int');    // Id of order
if (empty($id)) $id  = GETPOST('object_id','int');    // Id of order
$ref = GETPOST('ref','alpha');

// Security check
$socid='';
if ($user->societe_id) $socid=$user->societe_id;
$result=restrictedArea($user,$origin,$origin_id);

$action = GETPOST('action','alpha');
$confirm = GETPOST('confirm','alpha');

$arrayofjs=array(
            '/dymolabel/js/DYMO.Label.Framework.2.0.2.js',
            '/dymolabel/js/dolidymo.js?time='.time(), //avoid cache while testing
);
$arrayofcss=array(
            //'/dymolabel/css/dymolabel.css'
);

/*
 * View
 */


llxHeader('',$langs->trans('DymoLabel'),'','',0,0,$arrayofjs,$arrayofcss);

if ($id > 0 || ! empty($ref))
{
    $commande = new Commande($db);
    if ( $commande->fetch($id,$ref) > 0)
    {
        $commande->loadExpeditions(1);

        $soc = new Societe($db);
        $soc->fetch($commande->socid);

        $author = new User($db);
        $author->fetch($commande->user_author_id);

        $array = $commande->liste_contact(-1, 'external');
        //print "<pre>".print_r($array,true)."</pre>";
        $id_contact=0;
        for ($i=0; $i<count($array); $i++) {
            if ($array[$i]['code']=='SHIPPING') $id_contact=$array[$i]['id'];
        }
        $nosoc=0;
        $contactstatic=new Contact($db);
        if ($id_contact>0) {
            $nosoc=1;
            $contactstatic->fetch($id_contact);
            //print "<pre>".print_r($contactstatic,true)."</pre>";
        }

        $head = commande_prepare_head($commande);
        dol_fiche_head($head, 'dymolabel', $langs->trans("CustomerOrder"), 0, 'order');

        print '<table class="border" width="100%">';

        // Ref
        //print '<tr><td width="18%">'.$langs->trans('Ref').'</td>';
        //print '<td colspan="3">';
        //print $form->showrefnav($commande,'ref','',1,'ref','ref');
        //print '</td></tr>';

        // Ref commande client
        print '<tr><td width="18%">'.$langs->trans('RefCustomer').'</td>';
        print '<td colspan="3">'.$commande->ref_client;
        print '</td></tr>';

        //print '<tr><td>Code Destinataire</td>';
        //print '<td><input type=text name="codedestinataire" value="'.$soc->code_client.'"></td></tr>';
        //print '<tr><td>Reference Expedition</td>';
        //print '<td><input type=text name="referenceexpedition" value="'.$commande->ref.'"></td></tr>';
        //print '<tr>';
        //print '<td>Prenom</td>';
        if ($id_contact>0) {
            $firstname = strtoupper($contactstatic->firstname);
            $firstname = strtr($firstname, "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $name = strtoupper($contactstatic->lastname);
            $name = strtr($name, "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $socname = strtoupper($soc->name);
            $socname = strtr($socname, "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $socname = ($socname==$name?'':$socname);
            $address = explode("\n", strtoupper($contactstatic->address));
            $address[0] = strtr($address[0], "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $address[1] = strtr($address[1], "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $address[2] = strtr($address[2], "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $zip = $contactstatic->zip;
            $town = $contactstatic->town;
        } else {
            $firstname = strtoupper($soc->firstname);
            $firstname = strtr($firstname, "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $name = strtoupper($soc->name);
            $name = strtr($name, "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $socname = strtoupper($soc->name);
            $socname = strtr($socname, "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $socname = ($socname==$name?'':$socname);
            $address = explode("\n", strtoupper($soc->address));
            $address[0] = strtr($address[0], "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $address[1] = strtr($address[1], "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $address[2] = strtr($address[2], "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
            $zip = $soc->zip;
            $town = $soc->town;

        }

        print '<tr><td>';
        print '<div id="projectNameDiv">';
        print '<label for="textInput">Adresse:</label><br/>';
        print '<textarea name="textInput" id="textInput"  rows="6" cols="80">';
        if (! empty($socname) && !$nosoc) print $socname."\n";
        if (! empty($name)) print $name.' '.$firstname."\n";
        print $address[0]."\n";
        print $address[1]."\n";
        if (! empty($address[2])) print $address[2]."\n";
        if (! empty($address[3])) print $address[3]."\n";
        print $zip.' ';
        print $town."\n";
        //print $soc->country_code."\n";
        print '</textarea>';
        print '</div>';
        print '</td><td>';
        print '<div>';
        print '<img id="PreviewImageSrc" src="" alt="label preview"/>';
        print '</div>'."\n";  
        print '</td></tr>';

        print '</table><br>';

    }
}

print ' <div id="printDiv">';
print ' <button id="printButton">Imprimer</button>';
print ' </div>';
print ' <br/>';


print '<div>';
print '<label for="textMarkupInput">Adresse:</label><br/>';
//$textmarkup='<font family="Arial" size="16">';
//if (! empty($socname) && !$nosoc) $textmarkup.= $socname."<br/>";
//if (! empty($name)) $textmarkup.=  $name.' '.$firstname."<br/>";
//$textmarkup.=  $address[0]."<br/>";
//if (! empty($address[1])) $textmarkup.= $address[1]."<br/>";
//if (! empty($address[2])) $textmarkup.= $address[2]."<br/>";
//if (! empty($address[3])) $textmarkup.= $address[3]."<br/>";
//$textmarkup.= '</font><font family="Arial" size="18"><b>'.$zip.' ';
//$textmarkup.= $town."</b><br/></font>";
//$doleditor = new DolEditor('textMarkupInput', $textmarkup, '', 160, 'Basic', '', false, true, true, 4, '80%');
//$doleditor->Create();
print '<textarea name="textMarkupInput" id="textMarkupInput"  rows="6" cols="80">';
print '<font family="Arial" size="16">';
if (! empty($socname) && !$nosoc) print $socname."<br/>";
if (! empty($name)) print $name.' '.$firstname."<br/>";
print $address[0]."<br/>";
if (! empty($address[1])) print $address[1]."<br/>";
if (! empty($address[2])) print $address[2]."<br/>";
if (! empty($address[3])) print $address[3]."<br/>";
print '</font><font family="Arial" size="18"><b>'.$zip.' ';
print $town."</b><br/></font>";
print '</textarea>';
print '</div>';

print '<div class="printControls">';
print ' <div id="printersDiv">';
print ' <label for="printersSelect">Imprimante:</label><br/>';
print ' <select id="printersSelect"></select>';
print ' </div>';
print '</div>';

print '<div>';
print '<button id="printTextMarkupButton">Imprimer</button>';
print '</div>';

print '<br/>';

llxFooter();
$db->close();
