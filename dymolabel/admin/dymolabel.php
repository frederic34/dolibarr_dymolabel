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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       dymolabel/admin/dymolabel.php
 *  \ingroup    dymolabel
 *  \brief      Page d'administration/configuration du module Dymolabel
 */

// Dolibarr environment

$res=@include '../../main.inc.php';                             // For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
    $res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php");   // Use on dev env only
if (! $res) $res=@include '../../../main.inc.php';              // For "custom" directory

// Class
dol_include_once("/core/lib/admin.lib.php");

$langs->load("admin");
$langs->load("dymolabel@dymolabel");

// Security check
if (!$user->admin) accessforbidden();

$action = GETPOST('action', 'alpha');


/*
 * Affiche page
 */

llxHeader('',$langs->trans("DymoLabelSetup"));

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("DymoLabelSetup"), $linkback, 'setup');

//print '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
//print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
//print '<input type="hidden" name="action" value="update">';

/*
 *  Parameters
 */

//print '<br /><div style="text-align:center"><input type="submit" class="button" value="'.$langs->trans('Modify').'" name="button"></div>';
//print '</form>';

print 'Rien &agrave; configurer pour le moment...<br />';



llxFooter();

$db->close();
