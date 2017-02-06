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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

$langs->load("products");

$id = GETPOST('id','int')?GETPOST('id','int'):'';
$ref = GETPOST('ref','alpha');

// Security check
$socid='';
if ($user->societe_id) $socid=$user->societe_id;
$result=restrictedArea($user,$origin,$origin_id);

$action = GETPOST('action','alpha');
$confirm = GETPOST('confirm','alpha');


$arrayofjs=array(
            '/dymolabel/js/DYMO.Label.Framework.2.0.2.js',
            '/dymolabel/js/productdymo.js?time='.time(), //avoid cache
);
$arrayofcss=array(
            //'/includes/jquery/plugins/jquerytreeview/jquery.treeview.css',
            //'/includes/jquery/plugins/jqTree/jqtree.css'
);

/*
 * View
 */

$form = new Form($db);

llxHeader('',$langs->trans('DymoLabel'),'','',0,0,$arrayofjs,$arrayofcss);

if ($id > 0 || ! empty($ref))
{
    $object = new Product($db);
    if ( $object->fetch($id,$ref) > 0)
    {

        $head = product_prepare_head($object);
        dol_fiche_head($head, 'dymolabel', $langs->trans("CardProduct".$object->type), 0, $object->type== Product::TYPE_SERVICE?'service':'product');
        print '<table class="border" width="100%">';

        // Ref
        print '<tr><td width="18%">'.$langs->trans('Ref').'</td>';
        print '<td colspan="3">';
        print $form->showrefnav($object,'ref','',1,'ref','ref');
        print '</td></tr>';

        print '<tr><td>';

        print '<div>';
        print '<img id="PreviewImageSrc" src="" alt="label preview"/>';
        print '</div>'."\n"; 
        
        print '</td></tr>';

        print '</table><br>'."\n";

    }
}

print '<div>';
print '<label for="barcode">Code Barre:</label><br/>';
print '<input name="barcode" id="barcode" value="'.substr($object->barcode,0,12).'" size="80">';
print '</div>'."\n";

print '<div>';
print '<label for="reference">Référence:</label><br/>';
print '<input name="reference" id="reference" value="'.$object->ref.'" size="80">';
print '</div>'."\n";

print '<div>';
print '<label for="label">Libellé:</label><br/>';
print '<input name="libelle" id="libelle" value="'.$object->label.'" size="80">';
print '</div>'."\n";

print '<div class="printControls">';
print '<div id="printersDiv">';
print '<label for="printersSelect">Imprimante:</label><br/>';
print '<select id="printersSelect"></select>';
print '</div>'."\n";
print '</div>'."\n";

print '<div>';
print '<button id="printButton">Imprimer</button>';
print '</div>'."\n";

llxFooter();

$db->close();
